# Chapter 06 — Yajra server-side DataTables

Every list page in the admin (Branches, Products, Sales, Activity Logs,
…) is a Yajra-fed jQuery DataTable rendered inside a React component.
The user explicitly required server-side processing and Bootstrap 5
pagination styling.

This chapter shows the three layers you build once and then reuse on
every list page:

1. A backend pattern (controller `index()` + `data()` actions).
2. A shared `RendersDataTable` trait for action buttons and status
   badges.
3. A React `<ServerDataTable />` component that owns the jQuery
   instantiation.

## Why Yajra + jQuery inside React?

Pure React tables (TanStack Table, MUI DataGrid) are excellent but reimplementing server-side pagination, server-side search, server-side
column sort, and Bootstrap 5 pagination chrome is a lot of code. Yajra
gives you all of that out of the box if you embrace jQuery DataTables.

The React component is a thin wrapper that:

- mounts the `<table>` element,
- calls `$('#mytable').DataTable({...})` on `useEffect`,
- destroys the instance on unmount.

React owns the rest of the page (header, filters, modals); jQuery only
owns the table body.

## 1. Routes — index + data

Every list resource registers two routes:

```php
Route::get('/admin/products',       [ProductController::class, 'index'])->name('admin.products.index');
Route::get('/admin/products/data',  [ProductController::class, 'data']) ->name('admin.products.data');
```

`index` returns an Inertia page; `data` returns Yajra JSON.

## 2. Controller `index()` — render the page

```php
public function index()
{
    return Inertia::render('Products/Index', [
        'datatable_url' => route('admin.products.data'),
        'create_url'    => route('admin.products.create'),
    ]);
}
```

We pass route URLs as props rather than embedding them in JSX so the
React component stays generic.

## 3. Controller `data()` — feed Yajra

```php
public function data()
{
    $query = Product::query()
        ->with(['category:id,name', 'brand:id,name', 'unit:id,name'])
        ->select('products.*');

    return DataTables::eloquent($query)
        ->addColumn('category', fn ($p) => $p->category?->name ?? '-')
        ->addColumn('brand',    fn ($p) => $p->brand?->name ?? '-')
        ->editColumn('cost',    fn ($p) => number_format($p->cost, 2))
        ->editColumn('price',   fn ($p) => number_format($p->price, 2))
        ->addColumn('actions',  fn ($p) => $this->actionCell(
            route('admin.products.edit',   $p),
            route('admin.products.destroy', $p),
        ))
        ->rawColumns(['actions'])
        ->toJson();
}
```

Notes:

- `DataTables::eloquent($query)` enables true server-side pagination,
  search, and sort against the underlying SQL.
- `addColumn` adds a virtual column not in the DB; `editColumn`
  transforms an existing one.
- `rawColumns([...])` tells Yajra to render the HTML literally instead
  of escaping it.

## 4. The `RendersDataTable` trait

To keep action buttons consistent across modules, the trait at
`app/Http/Controllers/Admin/Concerns/RendersDataTable.php` produces
the action cell and status badge HTML:

```php
trait RendersDataTable
{
    protected function actionCell(?string $editRoute, ?string $deleteRoute): string
    {
        $html = '<div class="btn-group btn-group-sm">';
        if ($editRoute) {
            $html .= '<a href="'.$editRoute.'" class="btn btn-outline-primary smk-inertia">'
                  . e(__('messages.edit')).'</a>';
        }
        if ($deleteRoute) {
            $html .= '<button type="button" class="btn btn-outline-danger" '
                  . 'data-smk-delete="'.$deleteRoute.'">'
                  . e(__('messages.delete')).'</button>';
        }
        return $html . '</div>';
    }

    protected function statusBadge(bool $active): string
    {
        return $active
            ? '<span class="badge bg-success">'.e(__('messages.active')).'</span>'
            : '<span class="badge bg-secondary">'.e(__('messages.inactive')).'</span>';
    }
}
```

The two attributes — `smk-inertia` and `data-smk-delete` — are
captured by global jQuery handlers we register once in
`resources/js/Components/RowActions.jsx` (covered in Chapter 07).

Use the trait on any list controller:

```php
class ProductController extends Controller
{
    use RendersDataTable;
    // ...
}
```

## 5. The React `<ServerDataTable />` wrapper

`resources/js/Components/ServerDataTable.jsx`:

```jsx
import { useEffect, useRef } from 'react';
import $ from 'jquery';
import 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';

export default function ServerDataTable({ url, columns, order, search = true }) {
    const tableRef = useRef(null);

    useEffect(() => {
        const dt = $(tableRef.current).DataTable({
            processing: true,
            serverSide: true,
            ajax: { url, type: 'GET' },
            columns,
            order: order ?? [[0, 'asc']],
            searching: search,
            language: { url: '' },
            responsive: true,
            pagingType: 'simple_numbers',
        });
        return () => dt.destroy(true);
    }, [url]);

    return (
        <table ref={tableRef} className="table table-striped table-hover w-100">
            <thead>
                <tr>
                    {columns.map((c) => (
                        <th key={c.data ?? c.name}>{c.title}</th>
                    ))}
                </tr>
            </thead>
        </table>
    );
}
```

Page-level usage:

```jsx
import ServerDataTable from '@/Components/ServerDataTable';

export default function ProductsIndex({ datatable_url, create_url }) {
    return (
        <div>
            <div className="d-flex mb-3">
                <h4>Products</h4>
                <a href={create_url} className="btn btn-primary ms-auto">+ Add</a>
            </div>
            <ServerDataTable
                url={datatable_url}
                columns={[
                    { data: 'sku',      title: 'SKU' },
                    { data: 'name',     title: 'Name' },
                    { data: 'category', title: 'Category' },
                    { data: 'brand',    title: 'Brand' },
                    { data: 'price',    title: 'Price', className: 'text-end' },
                    { data: 'actions',  title: '',      orderable: false, searchable: false },
                ]}
            />
        </div>
    );
}
```

## 6. Bootstrap 5 pagination

Importing `datatables.net-bs5` swaps the default DataTables pagination
markup for Bootstrap 5 classes (`page-link`, `page-item`, `active`).
The CSS is loaded via `app.scss`:

```scss
@import "bootstrap/scss/bootstrap";
@import "datatables.net-bs5/css/dataTables.bootstrap5.css";
@import "datatables.net-responsive-bs5/css/responsive.bootstrap5.css";
```

## 7. The destroy + delete-confirm flow

The `data-smk-delete="…"` attribute on the action button is captured by
a global jQuery handler in `RowActions.jsx`. When a user clicks Delete:

1. The handler reads the URL from the attribute.
2. SweetAlert2 opens a localized confirm dialog (Chapter 07).
3. On confirm, the handler POSTs to the URL with `_method=DELETE` and
   the CSRF token.
4. On success, the handler calls `$('#table').DataTable().ajax.reload(null, false)`
   to refresh the table without losing the user's page/filter.
5. PHPFlasher renders a success toast from the redirect response.

Because we **never** issue an Inertia visit during delete, the page
does not unmount, the DataTable stays alive, and the user keeps their
filter state.

## 8. Verify

Load `/admin/products` and:

1. Check the network panel — the first row request fires to
   `/admin/products/data?draw=1&start=0&length=10` returning JSON with
   `data`, `recordsTotal`, `recordsFiltered`.
2. Type `Coke` in the search box — a new request fires with
   `search[value]=Coke` and the table shows 1 row.
3. Click the Name column header — the table re-sorts via a request with
   `order[0][column]=1&order[0][dir]=desc`.
4. Click Delete on any row — a SweetAlert2 modal opens (covered in
   Chapter 07); cancel keeps the row, confirm removes it and the table
   reloads without a full page navigation.

All four behaviors must work before Chapter 07. If the search hits the
table client-side (instant filter, same JSON), `serverSide: true` was
dropped from the config.
