# Chapter 10 — Stock operations (`StockService::applyDelta`)

The user wanted one place to mutate inventory so every report tells the
same story. This chapter explains the central `StockService`, the two
"manual" stock entry points (adjustments and transfers), and how they
plug into the same `product_branch_stock` table that POS and Purchases
write to.

## 1. The canonical service

```php
// app/Services/StockService.php
class StockService
{
    public function applyDelta(int $branchId, int $productId, float $delta): ProductBranchStock
    {
        $row = ProductBranchStock::firstOrCreate(
            ['branch_id' => $branchId, 'product_id' => $productId],
            ['qty' => 0],
        );

        // Raw arithmetic so we don't fight Eloquent's decimal cast on read.
        $row->qty = (float) $row->qty + $delta;
        $row->save();

        return $row;
    }
}
```

Why this shape:

- **Single mutation API** — purchase receive, POS checkout, stock
  adjustment, transfer sent, transfer received all funnel through this
  one method. Switching to an event-sourced inventory ledger later
  becomes a one-file change.
- **`firstOrCreate`** — initializes the row at qty=0 the first time a
  product appears at a branch, so seeders don't need an O(branches×products)
  cross-join.
- **Signed delta** — positive = stock in, negative = stock out. Callers
  control the sign.

## 2. Stock adjustments (manual)

Use case: damaged goods, lost items, ad-hoc corrections.

Tables:

```text
stock_adjustments       (id, branch_id, reason, ref_no, date, note, created_by)
stock_adjustment_items  (id, stock_adjustment_id, product_id, qty)
```

`reason` is one of `addition`, `subtraction`, `damage`, `loss`. Items
are always positive integers; the sign of the delta to `StockService`
is derived from the reason.

```php
// app/Http/Controllers/Admin/StockAdjustmentController.php
public function store(StockAdjustmentRequest $request, StockService $stock, ActivityLogger $logger): RedirectResponse
{
    $adj = DB::transaction(function () use ($request, $stock) {
        $adj = StockAdjustment::create([
            'branch_id'  => $request->branch_id,
            'reason'     => $request->reason,
            'ref_no'     => $this->generateRef('ADJ', $request->branch_id),
            'date'       => $request->date ?? now()->toDateString(),
            'note'       => $request->note,
            'created_by' => auth()->id(),
        ]);

        $sign = in_array($request->reason, ['addition'], true) ? +1 : -1;

        foreach ($request->validated()['items'] as $row) {
            $adj->items()->create([
                'product_id' => $row['product_id'],
                'qty'        => $row['qty'],
            ]);
            $stock->applyDelta(
                (int) $adj->branch_id,
                (int) $row['product_id'],
                $sign * (float) $row['qty'],
            );
        }

        return $adj;
    });

    $logger->log('stock.adjusted', $adj, ['reason' => $adj->reason, 'ref_no' => $adj->ref_no]);

    return redirect()->route('admin.stock-adjustments.show', $adj)
        ->with('success', __('messages.success.created'));
}
```

## 3. Stock transfers (branch ↔ branch)

Tables:

```text
stock_transfers       (id, from_branch_id, to_branch_id, status, ref_no, date, note, created_by, received_by, received_at)
stock_transfer_items  (id, stock_transfer_id, product_id, qty)
```

`status` is one of `pending`, `sent`, `received`, `cancelled`. The
flow:

1. **Create + send** — the source branch records the transfer. Stock at
   `from_branch` decreases immediately; stock at `to_branch` does NOT
   increase yet (the goods are in transit).
2. **Receive** — the destination branch confirms receipt. Stock at
   `to_branch` increases.

```php
public function store(StockTransferRequest $request, StockService $stock, ActivityLogger $logger): RedirectResponse
{
    $transfer = DB::transaction(function () use ($request, $stock) {
        $t = StockTransfer::create([
            'from_branch_id' => $request->from_branch_id,
            'to_branch_id'   => $request->to_branch_id,
            'status'         => 'sent',
            'ref_no'         => $this->generateRef('TRN', $request->from_branch_id),
            'date'           => now()->toDateString(),
            'created_by'     => auth()->id(),
        ]);

        foreach ($request->validated()['items'] as $row) {
            $t->items()->create([
                'product_id' => $row['product_id'],
                'qty'        => $row['qty'],
            ]);
            $stock->applyDelta((int) $t->from_branch_id, (int) $row['product_id'], -1 * (float) $row['qty']);
        }

        return $t;
    });

    $logger->log('transfer.sent', $transfer, ['ref_no' => $transfer->ref_no]);

    return redirect()->route('admin.stock-transfers.show', $transfer)
        ->with('success', __('messages.success.created'));
}

public function receive(StockTransfer $transfer, StockService $stock, ActivityLogger $logger): RedirectResponse
{
    abort_unless($transfer->status === 'sent', 422, 'Transfer is not in sent state');

    DB::transaction(function () use ($transfer, $stock) {
        foreach ($transfer->items as $row) {
            $stock->applyDelta((int) $transfer->to_branch_id, (int) $row->product_id, +1 * (float) $row->qty);
        }
        $transfer->update([
            'status'      => 'received',
            'received_by' => auth()->id(),
            'received_at' => now(),
        ]);
    });

    $logger->log('transfer.received', $transfer, ['ref_no' => $transfer->ref_no]);

    return back()->with('success', __('messages.success.updated'));
}
```

The "Receive" action is a button on the transfer show page wrapped in a
SweetAlert confirm so a cashier cannot click it by accident:

```jsx
<button className="btn btn-success"
        data-smk-confirm={route('admin.stock-transfers.receive', transfer.id)}
        data-smk-confirm-method="POST">
    Mark as received
</button>
```

`data-smk-confirm` is handled by the same global jQuery glue as
`data-smk-delete`, just with a different "Are you sure?" title.

## 4. Why no triggers / no observers

A common temptation is to write an Eloquent model observer like:

```php
class SaleItem extends Model {
    protected static function booted() {
        static::created(fn ($i) => StockService::applyDelta(...));
    }
}
```

Resist this. Three reasons:

1. **Hidden side effects** — readers of `Sale::create()` cannot tell
   the stock is also moving.
2. **Test difficulty** — mocking observers in `php artisan audit:e2e`
   is more work than calling the service explicitly.
3. **Double-apply risk** — if you later need to seed historical data
   without changing stock (data migrations), an observer makes that
   impossible.

The controllers explicitly call `StockService::applyDelta` for every
write. Auditing the calls is grep-able:

```bash
rg 'applyDelta' app/Http/Controllers
```

## Verify

Run the audit suite that exercises every entry point in sequence:

```bash
php artisan audit:e2e --only=stock
```

Expected: 8 assertions pass — purchase increment, POS decrement,
adjustment add, adjustment subtract, transfer sent, transfer received,
no double-apply, atomic on throw.

Or do it manually:

```bash
php artisan tinker --execute='
use App\Models\Product;
use Illuminate\Support\Facades\DB;
$id = Product::where("sku","BEV-COKE-330")->first()->id;
function show($id) {
    foreach (DB::table("product_branch_stock")
        ->where("product_id",$id)->orderBy("branch_id")->get() as $r)
        echo "  branch_id=$r->branch_id qty=$r->qty\n";
}
show($id);
'
```

After a 3-unit transfer from HQ → BR01, expect HQ to drop by 3 and
BR01 to rise by 3 once received.
