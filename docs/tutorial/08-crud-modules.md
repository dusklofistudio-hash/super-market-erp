# Chapter 08 — The CRUD module pattern

All 17 master-data modules (Branches, Products, Categories, Brands,
Units, Tax Rates, Suppliers, Customers, Customer Groups, Users, Roles,
Permissions, Languages, Translations, Settings, Expense Categories,
Activity Logs) share the same shape. Once you implement one, the rest
is copy-rename-tweak.

This chapter walks through **Branches** end-to-end as the template.

## The seven files per module

```text
app/Http/Controllers/Admin/BranchController.php   # actions
app/Http/Requests/Admin/BranchRequest.php         # validation
app/Models/Branch.php                             # eloquent
resources/js/Pages/Branches/Index.jsx             # list + DataTable
resources/js/Pages/Branches/Form.jsx              # create + edit
database/seeders/BranchSeeder.php                 # initial data
routes/web.php                                    # 6-7 route entries
```

The migration row already exists from Chapter 02.

## 1. The migration row

From the consolidated migration:

```php
Schema::create('branches', function (Blueprint $t) {
    $t->id();
    $t->string('code')->unique();
    $t->string('name');
    $t->string('phone')->nullable();
    $t->string('address')->nullable();
    $t->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
    $t->boolean('is_active')->default(true);
    $t->timestamps();
});
```

## 2. The Eloquent model

```php
// app/Models/Branch.php
class Branch extends Model
{
    protected $fillable = ['code', 'name', 'phone', 'address', 'manager_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function manager() {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function scopeActive($q) {
        return $q->where('is_active', true);
    }
}
```

## 3. The FormRequest

```php
// app/Http/Requests/Admin/BranchRequest.php
class BranchRequest extends FormRequest
{
    public function authorize(): bool { return true; }   // covered by middleware

    public function rules(): array
    {
        $id = $this->route('branch')?->id;
        return [
            'code'       => ['required', 'string', 'max:32', "unique:branches,code,$id"],
            'name'       => ['required', 'string', 'max:191'],
            'phone'      => ['nullable', 'string', 'max:64'],
            'address'    => ['nullable', 'string', 'max:255'],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'is_active'  => ['boolean'],
        ];
    }
}
```

The `unique:` rule deliberately ignores the current row's id so editing
without changing the code does not fail validation.

## 4. The controller (the canonical shape)

```php
class BranchController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('Branches/Index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Branch::query()->with('manager:id,name')->select('branches.*');

        return DataTables::eloquent($query)
            ->addColumn('manager_name', fn (Branch $b) => $b->manager?->name)
            ->addColumn('status',       fn (Branch $b) => $this->statusBadge((bool) $b->is_active))
            ->addColumn('action',       fn (Branch $b) => $this->actionCell(
                route('admin.branches.edit', $b),
                route('admin.branches.destroy', $b),
            ))
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Branches/Form', [
            'branch'   => null,
            'managers' => User::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(BranchRequest $request): RedirectResponse
    {
        Branch::create($request->validated());
        sweetalert()->success(__('messages.success.created'));
        return redirect()->route('admin.branches.index')
            ->with('success', __('messages.success.created'));
    }

    public function edit(Branch $branch): Response
    {
        return Inertia::render('Branches/Form', [
            'branch'   => $branch,
            'managers' => User::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(BranchRequest $request, Branch $branch): RedirectResponse
    {
        $branch->update($request->validated());
        return redirect()->route('admin.branches.index')
            ->with('success', __('messages.success.updated'));
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $branch->delete();
        return back()->with('success', __('messages.success.deleted'));
    }
}
```

Six action methods + one `data()` JSON feed = the full module on the
backend.

## 5. The routes

```php
// routes/web.php
Route::middleware(['auth', 'permission:branches.view'])->group(function () {
    Route::get   ('/admin/branches',                [BranchController::class, 'index']) ->name('admin.branches.index');
    Route::get   ('/admin/branches/data',           [BranchController::class, 'data'])  ->name('admin.branches.data');
});

Route::middleware(['auth', 'permission:branches.create'])->group(function () {
    Route::get ('/admin/branches/create',           [BranchController::class, 'create'])->name('admin.branches.create');
    Route::post('/admin/branches',                  [BranchController::class, 'store']) ->name('admin.branches.store');
});

Route::middleware(['auth', 'permission:branches.update'])->group(function () {
    Route::get  ('/admin/branches/{branch}/edit',   [BranchController::class, 'edit'])  ->name('admin.branches.edit');
    Route::match(['put','patch'], '/admin/branches/{branch}',
                                                    [BranchController::class, 'update'])->name('admin.branches.update');
});

Route::middleware(['auth', 'permission:branches.delete'])->group(function () {
    Route::delete('/admin/branches/{branch}',       [BranchController::class, 'destroy'])->name('admin.branches.destroy');
});
```

## 6. `Pages/Branches/Index.jsx`

```jsx
import ServerDataTable from '@/Components/ServerDataTable';
import { route } from 'ziggy-js';
import { Can } from '@/lib/PermissionProvider';
import { useI18n } from '@/lib/I18nProvider';

export default function BranchesIndex() {
    const { t } = useI18n();
    return (
        <div>
            <div className="d-flex align-items-center mb-3">
                <h4 className="mb-0">{t('nav.branches')}</h4>
                <Can permission="branches.create">
                    <a href={route('admin.branches.create')} className="btn btn-primary ms-auto">
                        + {t('common.create')}
                    </a>
                </Can>
            </div>
            <ServerDataTable
                url={route('admin.branches.data')}
                columns={[
                    { data: 'code',         title: t('fields.code') },
                    { data: 'name',         title: t('fields.name') },
                    { data: 'manager_name', title: t('fields.manager') },
                    { data: 'phone',        title: t('fields.phone') },
                    { data: 'status',       title: t('fields.status') },
                    { data: 'action',       title: '', orderable: false, searchable: false },
                ]}
            />
        </div>
    );
}
```

## 7. `Pages/Branches/Form.jsx`

```jsx
import { useForm } from '@inertiajs/react';
import { route } from 'ziggy-js';
import { useI18n } from '@/lib/I18nProvider';

export default function BranchForm({ branch, managers }) {
    const { t } = useI18n();
    const editing = !!branch?.id;
    const form = useForm({
        code:       branch?.code       ?? '',
        name:       branch?.name       ?? '',
        phone:      branch?.phone      ?? '',
        address:    branch?.address    ?? '',
        manager_id: branch?.manager_id ?? '',
        is_active:  branch?.is_active  ?? true,
    });

    const submit = (e) => {
        e.preventDefault();
        const action = editing
            ? form.put(route('admin.branches.update', branch.id))
            : form.post(route('admin.branches.store'));
    };

    return (
        <form onSubmit={submit} className="card p-3" style={{ maxWidth: 720 }}>
            <h4>{editing ? t('common.edit') : t('common.create')} {t('nav.branches')}</h4>

            <div className="mb-3">
                <label>{t('fields.code')}</label>
                <input value={form.data.code}
                       onChange={(e) => form.setData('code', e.target.value)}
                       className="form-control" />
                {form.errors.code && <small className="text-danger">{form.errors.code}</small>}
            </div>

            <div className="mb-3">
                <label>{t('fields.name')}</label>
                <input value={form.data.name}
                       onChange={(e) => form.setData('name', e.target.value)}
                       className="form-control" />
                {form.errors.name && <small className="text-danger">{form.errors.name}</small>}
            </div>

            <div className="mb-3">
                <label>{t('fields.manager')}</label>
                <select data-tom-select className="form-select"
                        value={form.data.manager_id ?? ''}
                        onChange={(e) => form.setData('manager_id', e.target.value || null)}>
                    <option value="">--</option>
                    {managers.map((m) => (
                        <option key={m.id} value={m.id}>{m.name}</option>
                    ))}
                </select>
            </div>

            <div className="form-check mb-3">
                <input type="checkbox" id="is_active" className="form-check-input"
                       checked={form.data.is_active}
                       onChange={(e) => form.setData('is_active', e.target.checked)} />
                <label htmlFor="is_active" className="form-check-label">
                    {t('fields.active')}
                </label>
            </div>

            <div className="d-flex gap-2">
                <button type="submit" className="btn btn-primary" disabled={form.processing}>
                    {t('common.save')}
                </button>
                <a href={route('admin.branches.index')} className="btn btn-link">
                    {t('common.cancel')}
                </a>
            </div>
        </form>
    );
}
```

Inertia's `useForm` hook handles dirty tracking, error binding, CSRF,
and submission. The `data-tom-select` attribute is picked up by the
global initializer from Chapter 07.

## 8. The seeder

```php
// database/seeders/BranchSeeder.php
class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::firstOrCreate(
            ['code' => 'HQ'],
            ['name' => 'Headquarters', 'phone' => '012-345-678', 'address' => '#1, Main St', 'is_active' => true],
        );
        Branch::firstOrCreate(
            ['code' => 'BR01'],
            ['name' => 'Branch 01', 'phone' => '012-000-001', 'address' => '#10, Side St', 'is_active' => true],
        );
    }
}
```

`firstOrCreate` makes the seeder idempotent — re-running it does not
duplicate rows.

## 9. Replicate to every other master-data module

For each of the remaining 16 master-data modules, copy this seven-file
pattern and rename. Keep the same naming conventions:

- Controller method names: `index`, `data`, `create`, `store`, `edit`,
  `update`, `destroy`.
- Page filenames: `Index.jsx`, `Form.jsx`.
- Route names: `admin.<module>.index`, etc.

Skipping or renaming conventions makes it impossible to share the
`RendersDataTable` trait, `ServerDataTable` component, and global
delete handler.

## Verify

After implementing Branches:

1. `GET /admin/branches` renders an Inertia page with the DataTable.
2. The table shows two seeded rows (`HQ`, `BR01`).
3. Clicking `+ Create` opens `Form.jsx` with empty fields.
4. Submitting valid data redirects to `index` with a success toast.
5. Submitting invalid data (e.g. duplicate `code`) shows red error text
   under the field.
6. Edit → save → toast → redirect works.
7. Delete on `BR01` → SweetAlert modal → confirm → row disappears with
   a toast; HQ delete is still possible but seeded as the default
   branch in later chapters, so prefer to keep it.

All seven behaviors must work for Branches before applying the pattern
to the remaining 16 modules.
