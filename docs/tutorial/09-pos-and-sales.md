# Chapter 09 — POS and sales

The point-of-sale flow is the highest-value runtime path in the whole
ERP. A cashier opens a session, scans items into a cart, takes payment,
prints/saves a receipt, and the stock on hand drops by the sold
quantity — all inside one DB transaction so a crash leaves no
half-state.

This chapter walks through the four moving parts:

1. POS registers — physical tills.
2. POS sessions — cashier shifts open → close.
3. The cart UI (`/admin/pos/register`).
4. The checkout transaction that writes `sales`, `sale_items`,
   `sale_payments`, decrements stock, and writes an activity log row.

## 1. Tables involved

```text
pos_registers     (id, branch_id, name, is_active)
pos_sessions      (id, register_id, branch_id, cashier_id, opened_at, closed_at, opening_cash, expected_cash)
sales             (id, ref_no, branch_id, customer_id, user_id, pos_session_id, date, subtotal, tax, discount, total, paid, status, note)
sale_items        (id, sale_id, product_id, qty, unit_price, tax, subtotal)
sale_payments     (id, sale_id, date, amount, method)
product_branch_stock (branch_id, product_id, qty)   -- decremented on checkout
```

## 2. POS registers (`PosRegisterController`)

A register is just a named till tied to a branch. Standard CRUD module
following the Chapter 08 pattern. Seed one register per branch:

```php
PosRegister::firstOrCreate(
    ['branch_id' => $hq->id, 'name' => 'Main Register'],
    ['is_active' => true],
);
```

## 3. POS sessions (`PosSessionController`)

A session represents a cashier's shift. Open a session before any
sale; close it at end of shift to reconcile cash.

```php
// Open
public function open(Request $request, PosRegister $register)
{
    $session = PosSession::create([
        'register_id'   => $register->id,
        'branch_id'     => $register->branch_id,
        'cashier_id'    => auth()->id(),
        'opened_at'     => now(),
        'opening_cash'  => $request->opening_cash ?? 0,
        'expected_cash' => $request->opening_cash ?? 0,
    ]);
    return redirect()->route('admin.pos.register', ['session' => $session->id]);
}

// Close
public function close(Request $request, PosSession $session)
{
    $session->update([
        'closed_at'    => now(),
        'closing_cash' => $request->closing_cash,
        'note'         => $request->note,
    ]);
    return redirect()->route('admin.pos-sessions.show', $session);
}
```

The crucial invariant: a sale must reference an OPEN session
(`closed_at IS NULL`). Validation enforces it on checkout.

## 4. The cart UI

Route:

```php
Route::middleware(['auth', 'permission:pos.access'])
    ->get('/admin/pos/register', [PosController::class, 'register'])
    ->name('admin.pos.register');
```

Controller (abridged from `app/Http/Controllers/Admin/PosController.php`):

```php
public function register(Request $request): Response
{
    $sessionId = $request->query('session');
    $session = $sessionId
        ? PosSession::with(['register:id,branch_id,name', 'register.branch:id,name_en,name_kh'])->find($sessionId)
        : null;

    return Inertia::render('Pos/Register', [
        'session'   => $session,
        'products'  => Product::active()->orderBy('name_en')
                          ->get(['id', 'barcode', 'sku', 'name_en', 'name_kh', 'sale_price']),
        'customers' => Customer::active()->orderBy('name')
                          ->get(['id', 'code', 'name', 'phone']),
    ]);
}
```

The page receives the product catalog up front (under ~10k SKUs is fine
to ship as a single payload; beyond that, fetch on demand).

`resources/js/Pages/Pos/Register.jsx` is a React state machine with
three columns:

| Left                            | Center                       | Right                           |
|---------------------------------|------------------------------|---------------------------------|
| Searchable product grid (click to add) | Cart line items + qty + remove | Customer picker, totals, payment, "Complete sale" button |

Skeleton:

```jsx
const [cart, setCart] = useState([]);
const add = (product) => setCart(c => upsert(c, product));
const setQty = (id, qty) => setCart(c => c.map(r => r.product_id === id ? { ...r, qty } : r));
const remove = (id) => setCart(c => c.filter(r => r.product_id !== id));

const subtotal = sum(cart.map(r => r.qty * r.unit_price));
const tax      = sum(cart.map(r => r.tax ?? 0));
const total    = subtotal + tax - discount;

const submit = () => {
    router.post(route('admin.pos.checkout'), {
        branch_id:      session.branch_id,
        pos_session_id: session.id,
        customer_id:    customer?.id,
        items:          cart,
        discount,
        paid,
        payment_method: 'cash',
    }, { preserveScroll: true });
};
```

If `?session=` is missing or the session is closed, the React component
shows a "No active session" empty state with a button to open one.

## 5. The checkout transaction

The single most important method in the whole ERP:

```php
public function checkout(SaleRequest $request, StockService $stock, ActivityLogger $logger): RedirectResponse
{
    $sale = DB::transaction(function () use ($request, $stock) {
        $items = $request->validated()['items'];
        $totals = $this->computeTotals($items, (float) ($request->discount ?? 0));

        $sale = Sale::create([
            'ref_no'         => 'SAL-' . now()->format('Ymd') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 6)),
            'branch_id'      => $request->branch_id,
            'customer_id'    => $request->customer_id,
            'user_id'        => auth()->id(),
            'pos_session_id' => $request->pos_session_id,
            'date'           => now(),
            'subtotal'       => $totals['subtotal'],
            'tax'            => $totals['tax'],
            'discount'       => (float) ($request->discount ?? 0),
            'total'          => $totals['total'],
            'paid'           => (float) $request->paid,
            'status'         => 'completed',
            'note'           => $request->note,
        ]);

        foreach ($items as $row) {
            $sale->items()->create([
                'product_id' => $row['product_id'],
                'qty'        => $row['qty'],
                'unit_price' => $row['unit_price'],
                'tax'        => $row['tax'] ?? 0,
                'subtotal'   => ($row['qty'] * $row['unit_price']) + ($row['tax'] ?? 0),
            ]);
            // STOCK DECREMENT via the central service
            $stock->applyDelta((int) $sale->branch_id, (int) $row['product_id'], -1 * (float) $row['qty']);
        }

        if ((float) $request->paid > 0) {
            $sale->payments()->create([
                'date'   => now()->toDateString(),
                'amount' => (float) $request->paid,
                'method' => $request->payment_method,
            ]);
        }

        // Reconcile session expected cash
        if ($sale->pos_session_id) {
            $cash = $sale->payments()->where('method', 'cash')->sum('amount');
            PosSession::where('id', $sale->pos_session_id)
                ->increment('expected_cash', (float) $cash);
        }

        return $sale;
    });

    $logger->log('sale.completed', $sale, [
        'ref_no' => $sale->ref_no,
        'total'  => (float) $sale->total,
        'paid'   => (float) $sale->paid,
    ]);

    return redirect()->route('admin.sales.show', $sale)
        ->with('success', __('messages.success.created'));
}
```

Five guarantees this method gives you:

1. **Atomicity** — wrapped in `DB::transaction()`. If anything throws,
   no stock changes, no sale row, no payments.
2. **Centralised stock arithmetic** — every decrement goes through
   `StockService::applyDelta` (Chapter 10).
3. **Idempotent ref_no** — date-prefixed + 6-char random tail so two
   concurrent sales in the same second cannot collide.
4. **Session reconciliation** — `expected_cash` increments by the cash
   amount so end-of-day reconciliation is `closing_cash - expected_cash`.
5. **Audit trail** — `ActivityLogger` writes a row to `activity_logs`
   (Chapter 13).

## 6. The SaleRequest validator

```php
public function rules(): array
{
    return [
        'branch_id'         => ['required', 'exists:branches,id'],
        'pos_session_id'    => ['required', 'exists:pos_sessions,id'],
        'customer_id'       => ['nullable', 'exists:customers,id'],
        'discount'          => ['nullable', 'numeric', 'min:0'],
        'paid'              => ['required', 'numeric', 'min:0'],
        'payment_method'    => ['required', 'in:cash,card,credit,other'],
        'items'             => ['required', 'array', 'min:1'],
        'items.*.product_id'=> ['required', 'exists:products,id'],
        'items.*.qty'       => ['required', 'numeric', 'min:0.01'],
        'items.*.unit_price'=> ['required', 'numeric', 'min:0'],
        'items.*.tax'       => ['nullable', 'numeric', 'min:0'],
        'note'              => ['nullable', 'string', 'max:500'],
    ];
}
```

Additionally enforce that the session is OPEN via a custom rule:

```php
public function withValidator($v)
{
    $v->after(function ($v) {
        $s = PosSession::find($this->pos_session_id);
        if ($s && $s->closed_at) {
            $v->errors()->add('pos_session_id', 'Session is closed; open a new one.');
        }
    });
}
```

## 7. The receipt page

`SaleController::show` renders `Pages/Sales/Show.jsx` with line items,
totals, and customer info. Print styling lives in `resources/sass/_print.scss`
behind `@media print` so plain Bootstrap modal print works.

## Verify

```bash
php artisan tinker --execute='
use App\Models\Product;
use Illuminate\Support\Facades\DB;
$id = Product::where("sku","BEV-COKE-330")->first()->id;
echo "Before: " . DB::table("product_branch_stock")
    ->where(["branch_id"=>1,"product_id"=>$id])->value("qty") . "\n";
'
```

Then in the browser, log in as admin, visit `/admin/pos/register?session=2`,
add 2× Coke 330ml, click Complete sale. Re-run the tinker query:

```text
Before: 147
After:  145
```

Delta = -2 = matches cart quantity. If the delta differs, walk through
the checkout method again — the most common bugs are:

- Stock delta applied OUTSIDE the transaction (leaks on rollback).
- `applyDelta` called with the wrong sign (positive instead of negative).
- A duplicate stock write in a model observer competing with the
  service.
