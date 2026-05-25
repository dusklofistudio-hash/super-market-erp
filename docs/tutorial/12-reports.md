# Chapter 12 — Reports

The user asked for read-only reports that tell management what the
business is doing. Each report is one controller action + one React
page + one server-side aggregation query.

| Report                | Source tables                                    | What it answers                                     |
|-----------------------|--------------------------------------------------|-----------------------------------------------------|
| Sales Summary         | `sales`                                          | How many sales today/this month, what total amount  |
| Stock by Branch       | `product_branch_stock`, `products`, `branches`   | What's on shelves at each branch, with low-stock pin |
| Profit                | `sales`, `purchases`, `expenses`                 | Revenue − COGS − expenses for a date range          |
| Expenses by Category  | `expenses`, `expense_categories`                 | Where is money leaking                              |

All four endpoints are read-only: no FormRequest, no transactions, just
aggregations.

## 1. Routes

```php
Route::middleware(['auth', 'permission:reports.view'])->group(function () {
    Route::get('/admin/reports/sales-summary',  [ReportController::class, 'salesSummary']) ->name('admin.reports.sales-summary');
    Route::get('/admin/reports/stock-by-branch',[ReportController::class, 'stockByBranch'])->name('admin.reports.stock-by-branch');
    Route::get('/admin/reports/profit',         [ReportController::class, 'profit'])       ->name('admin.reports.profit');
    Route::get('/admin/reports/expenses',       [ReportController::class, 'expenses'])     ->name('admin.reports.expenses');
});
```

## 2. Sales summary

```php
public function salesSummary(Request $request): Response
{
    $from = $request->date('from') ?? now()->startOfDay();
    $to   = $request->date('to')   ?? now()->endOfDay();

    $rows = Sale::query()
        ->whereBetween('date', [$from, $to])
        ->selectRaw('DATE(date) as d, COUNT(*) as sales_count, SUM(total) as total')
        ->groupByRaw('DATE(date)')
        ->orderBy('d')
        ->get();

    return Inertia::render('Reports/SalesSummary', [
        'rows' => $rows,
        'from' => $from->toDateString(),
        'to'   => $to->toDateString(),
    ]);
}
```

The page renders a date-range picker (`flatpickr` from Chapter 07) +
a chart + a table.

## 3. Stock by branch

```php
public function stockByBranch(Request $request): Response
{
    $threshold = (float) ($request->input('low_stock', 5));

    $rows = DB::table('product_branch_stock as s')
        ->join('products as p', 'p.id', '=', 's.product_id')
        ->join('branches as b', 'b.id', '=', 's.branch_id')
        ->select([
            's.qty', 'p.sku', 'p.name_en as product', 'b.name_en as branch',
            DB::raw('CASE WHEN s.qty < ' . $threshold . ' THEN 1 ELSE 0 END as is_low'),
        ])
        ->orderBy('b.name_en')
        ->orderBy('p.name_en')
        ->get();

    return Inertia::render('Reports/StockByBranch', [
        'rows'      => $rows,
        'threshold' => $threshold,
    ]);
}
```

React renders the table with a red badge on `is_low=1` rows.

## 4. Profit

```php
public function profit(Request $request): Response
{
    $from = $request->date('from') ?? now()->startOfMonth();
    $to   = $request->date('to')   ?? now()->endOfMonth();

    $sales     = (float) Sale::whereBetween('date', [$from, $to])->sum('total');
    $purchases = (float) Purchase::whereBetween('date', [$from, $to])->sum('total');
    $expenses  = (float) Expense::whereBetween('date', [$from, $to])->sum('amount');

    return Inertia::render('Reports/Profit', [
        'from'      => $from->toDateString(),
        'to'        => $to->toDateString(),
        'sales'     => $sales,
        'purchases' => $purchases,
        'expenses'  => $expenses,
        'profit'    => $sales - $purchases - $expenses,
    ]);
}
```

The page surfaces all four numbers with a sign-aware color (red when
negative). Watch the sign: this formula was the canary for catching a
flipped-sign bug during Phase 2 testing.

## 5. Expenses by category

```php
public function expenses(Request $request): Response
{
    $from = $request->date('from') ?? now()->startOfMonth();
    $to   = $request->date('to')   ?? now()->endOfMonth();

    $rows = DB::table('expenses as e')
        ->join('expense_categories as c', 'c.id', '=', 'e.expense_category_id')
        ->whereBetween('e.date', [$from, $to])
        ->selectRaw('c.name as category, SUM(e.amount) as total')
        ->groupBy('c.name')
        ->orderByDesc('total')
        ->get();

    return Inertia::render('Reports/Expenses', ['rows' => $rows]);
}
```

## 6. CSV export pattern

Add an export button per report that calls the same controller with
`?export=csv`:

```php
if ($request->boolean('export')) {
    return response()->streamDownload(function () use ($rows) {
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Date', 'Sales count', 'Total']);
        foreach ($rows as $r) fputcsv($out, [$r->d, $r->sales_count, $r->total]);
        fclose($out);
    }, 'sales-summary.csv', ['Content-Type' => 'text/csv']);
}
```

## 7. Pagination strategy

These report queries can grow to thousands of rows. If you expect more
than ~500 rows, switch from `Inertia::render` with raw rows to a
Yajra DataTable like Chapter 06. Profit / Sales Summary stay aggregated
so they remain tiny; Stock by Branch and Expenses can blow up.

## Verify

```bash
curl -sb cookies.txt http://127.0.0.1:8000/admin/reports/sales-summary \
    -H "X-Inertia: true" -H "Accept: application/json" | jq '.props.rows | length'
```

Returns a non-negative integer. For a fresh seed, `Sales Summary` may
return 1-2 rows depending on the seeded sales fixture.

Browser smoke test:

1. `/admin/reports/sales-summary` — shows date picker and a row per
   day with at least one sale.
2. `/admin/reports/stock-by-branch` — shows seeded products with HQ +
   BR01 stock columns.
3. `/admin/reports/profit` — shows Sales, Purchases, Expenses, and a
   correctly-signed Profit number.
4. `/admin/reports/expenses` — shows aggregated buckets per category.

If Profit shows a positive number when sales < purchases + expenses,
the sign is flipped — fix the formula before relying on the report.
