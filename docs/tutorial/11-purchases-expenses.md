# Chapter 11 — Purchases and expenses

Purchases bring stock IN (the mirror of sales) and supplier payments
track what we owe. Expenses cover anything else that costs money but is
not a product purchase (rent, utilities, salaries).

## 1. Purchases

### Tables

```text
purchases           (id, ref_no, supplier_id, branch_id, status, date, subtotal, tax, discount, total, paid, note, created_by)
purchase_items      (id, purchase_id, product_id, qty, unit_cost, tax, subtotal)
purchase_payments   (id, purchase_id, date, amount, method, note)
```

`status` ∈ `draft`, `ordered`, `received`, `cancelled`. Stock only
moves when the status transitions to `received`.

### Controller flow

```php
// app/Http/Controllers/Admin/PurchaseController.php
public function store(PurchaseRequest $request, StockService $stock, ActivityLogger $logger): RedirectResponse
{
    $purchase = DB::transaction(function () use ($request, $stock) {
        $totals = $this->computeTotals($request->items, (float) ($request->discount ?? 0));

        $purchase = Purchase::create([
            'ref_no'      => 'PUR-' . now()->format('Ymd') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 6)),
            'supplier_id' => $request->supplier_id,
            'branch_id'   => $request->branch_id,
            'status'      => 'received',
            'date'        => $request->date ?? now()->toDateString(),
            'subtotal'    => $totals['subtotal'],
            'tax'         => $totals['tax'],
            'discount'    => (float) ($request->discount ?? 0),
            'total'       => $totals['total'],
            'paid'        => (float) ($request->paid ?? 0),
            'note'        => $request->note,
            'created_by'  => auth()->id(),
        ]);

        foreach ($request->validated()['items'] as $row) {
            $purchase->items()->create([
                'product_id' => $row['product_id'],
                'qty'        => $row['qty'],
                'unit_cost'  => $row['unit_cost'],
                'tax'        => $row['tax'] ?? 0,
                'subtotal'   => ($row['qty'] * $row['unit_cost']) + ($row['tax'] ?? 0),
            ]);
            // Stock INCREMENT — purchases bring goods in
            $stock->applyDelta((int) $purchase->branch_id, (int) $row['product_id'], +1 * (float) $row['qty']);
        }

        if ((float) ($request->paid ?? 0) > 0) {
            $purchase->payments()->create([
                'date'   => now()->toDateString(),
                'amount' => (float) $request->paid,
                'method' => $request->payment_method ?? 'cash',
            ]);
        }

        return $purchase;
    });

    $logger->log('purchase.received', $purchase, [
        'ref_no'      => $purchase->ref_no,
        'supplier_id' => $purchase->supplier_id,
        'total'       => (float) $purchase->total,
    ]);

    return redirect()->route('admin.purchases.show', $purchase)
        ->with('success', __('messages.success.created'));
}
```

Key contract: **purchase store = +stock**, mirror image of sale checkout's
**−stock**. Both go through `StockService::applyDelta`.

### Validation

```php
public function rules(): array
{
    return [
        'supplier_id'        => ['required', 'exists:suppliers,id'],
        'branch_id'          => ['required', 'exists:branches,id'],
        'date'               => ['nullable', 'date'],
        'discount'           => ['nullable', 'numeric', 'min:0'],
        'paid'               => ['nullable', 'numeric', 'min:0'],
        'payment_method'     => ['nullable', 'in:cash,card,credit,other'],
        'items'              => ['required', 'array', 'min:1'],
        'items.*.product_id' => ['required', 'exists:products,id'],
        'items.*.qty'        => ['required', 'numeric', 'min:0.01'],
        'items.*.unit_cost'  => ['required', 'numeric', 'min:0'],
        'items.*.tax'        => ['nullable', 'numeric', 'min:0'],
        'note'               => ['nullable', 'string', 'max:500'],
    ];
}
```

### Purchase payments (additional supplier payments)

After the initial sale-day payment, the supplier may be paid later in
installments. A small endpoint creates a `purchase_payments` row and
recomputes the `purchases.paid` total:

```php
public function addPayment(Request $request, Purchase $purchase): RedirectResponse
{
    $request->validate([
        'amount' => ['required', 'numeric', 'min:0.01'],
        'date'   => ['required', 'date'],
        'method' => ['required', 'in:cash,card,credit,other'],
    ]);

    DB::transaction(function () use ($purchase, $request) {
        $purchase->payments()->create($request->only(['amount', 'date', 'method']));
        $purchase->paid = $purchase->payments()->sum('amount');
        $purchase->save();
    });

    return back()->with('success', __('messages.success.created'));
}
```

## 2. Expenses

### Tables

```text
expense_categories (id, name, is_active)
expenses           (id, expense_category_id, branch_id, date, amount, payee, note, created_by)
```

No stock interaction — expenses only affect the reports' profit
calculation.

### Controller (standard CRUD module — Chapter 08 pattern)

```php
public function store(ExpenseRequest $request, ActivityLogger $logger): RedirectResponse
{
    $expense = Expense::create($request->validated() + ['created_by' => auth()->id()]);
    $logger->log('expense.recorded', $expense, [
        'amount'      => (float) $expense->amount,
        'category_id' => (int) $expense->expense_category_id,
    ]);
    return redirect()->route('admin.expenses.index')
        ->with('success', __('messages.success.created'));
}
```

### Validation

```php
public function rules(): array
{
    return [
        'expense_category_id' => ['required', 'exists:expense_categories,id'],
        'branch_id'           => ['required', 'exists:branches,id'],
        'date'                => ['required', 'date'],
        'amount'              => ['required', 'numeric', 'min:0.01'],
        'payee'               => ['nullable', 'string', 'max:191'],
        'note'                => ['nullable', 'string', 'max:500'],
    ];
}
```

## 3. Forms reuse the line-items editor

Both Purchases and Stock Adjustments need a multi-line item editor. We
share a single React component `resources/js/Components/LineItemsEditor.jsx`:

```jsx
export default function LineItemsEditor({ items, setItems, products, costField = 'unit_price' }) {
    const addRow = () => setItems([...items, { product_id: null, qty: 1, [costField]: 0 }]);
    const setRow = (i, patch) => setItems(items.map((r, idx) => idx === i ? { ...r, ...patch } : r));
    const removeRow = (i) => setItems(items.filter((_, idx) => idx !== i));
    // ... render a table with Tom Select for product, qty input, cost input, remove button
}
```

Purchases passes `costField="unit_cost"`; POS passes `costField="unit_price"`.
The component handles both vocabularies; the form translation files
provide the right column header per page.

## 4. Verify

```bash
php artisan tinker --execute='
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
echo Purchase::count() . " purchase rows\n";
echo DB::table("purchase_payments")->count() . " purchase_payments rows\n";
echo DB::table("expenses")->count() . " expenses rows\n";
'
```

End-to-end through the browser:

1. Visit `/admin/purchases/create`, pick supplier=Acme, branch=HQ,
   add 5× Coke at unit_cost 1.00, paid=5, click Save.
2. Visit `/admin/products` and confirm Coke at HQ rose by 5.
3. Visit `/admin/activity-logs` and find a `purchase.received` row.
4. Visit `/admin/expenses/create`, pick category=Utilities,
   amount=42.00, save. Check Reports → Profit reflects the −42.00.
