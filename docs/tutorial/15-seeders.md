# Chapter 15 — Seeders (one per table)

A clean `php artisan migrate:fresh --seed` should produce a fully-usable
sandbox: admin/manager/cashier accounts, two branches, a dozen products
with stock at HQ + BR01, a couple of sample purchases and sales, and
matching activity log rows.

The user asked for **one seeder per migration table**. This chapter
explains the structure, the FK-safe call order, and how to keep it
idempotent so devs can re-run `db:seed` without duplicates.

## 1. The principle: 1 table → 1 seeder

```text
database/seeders/
├── DatabaseSeeder.php              (orchestrator)
├── BranchSeeder.php                → branches
├── BrandSeeder.php                 → brands
├── CategorySeeder.php              → categories
├── ProductSeeder.php               → products
├── ProductBranchStockSeeder.php    → product_branch_stock
├── ...
└── ActivityLogSeeder.php           → activity_logs
```

Why split this way:

- Tracing "where does this seeded row come from?" is a `grep` of the
  table name across `database/seeders/`.
- Each seeder is small enough to read top-to-bottom.
- You can `php artisan db:seed --class=ProductSeeder` to reseed just
  one table during dev.
- Framework runtime tables (sessions/cache/jobs/...) keep a no-op stub
  seeder so the 1-to-1 mapping holds even though no business rows go
  in them. The stubs are convenient hooks if you ever need to insert
  test data into those tables.

## 2. The orchestrator

`DatabaseSeeder::run()` calls every seeder in a strict order that
respects foreign keys. Excerpt:

```php
$this->call([
    // --- Framework-runtime tables (stubs / no-ops) ---
    PasswordResetTokenSeeder::class,
    SessionSeeder::class, CacheSeeder::class, CacheLockSeeder::class,
    JobSeeder::class, JobBatchSeeder::class, FailedJobSeeder::class,

    // --- Localization ---
    LanguageSeeder::class, TranslationSeeder::class,

    // --- Settings ---
    SettingSeeder::class,

    // --- RBAC catalogue ---
    RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class,

    // --- Branches before users (FK on users.default_branch_id) ---
    BranchSeeder::class,

    // --- Users + pivots ---
    UserSeeder::class, UserRoleSeeder::class, UserBranchSeeder::class,

    // --- Catalog ---
    CategorySeeder::class, BrandSeeder::class, UnitSeeder::class,
    TaxRateSeeder::class, ProductSeeder::class, ProductBranchStockSeeder::class,

    // --- Parties ---
    SupplierSeeder::class, CustomerGroupSeeder::class, CustomerSeeder::class,

    // --- Procurement ---
    PurchaseSeeder::class, PurchaseItemSeeder::class, PurchasePaymentSeeder::class,

    // --- POS infra ---
    PosRegisterSeeder::class, PosSessionSeeder::class,

    // --- Sales ---
    SaleSeeder::class, SaleItemSeeder::class, SalePaymentSeeder::class,

    // --- Stock operations ---
    StockAdjustmentSeeder::class, StockAdjustmentItemSeeder::class,
    StockTransferSeeder::class, StockTransferItemSeeder::class,

    // --- Expenses ---
    ExpenseCategorySeeder::class, ExpenseSeeder::class,

    // --- Audit log ---
    ActivityLogSeeder::class,
]);
```

Order rules:

1. Parents before children (`Branch` → `User`, `Category` → `Product`).
2. Junction seeders after both sides exist (`UserRole` after `User`
   and `Role`).
3. Operational data (sales, purchases) after their inputs (products,
   customers, suppliers, POS sessions).
4. Activity logs last — they reference everything else.

## 3. Idempotency

Every seeder must use `firstOrCreate` or `updateOrCreate` so running
`db:seed` a second time does nothing destructive:

```php
// Good — idempotent
Branch::firstOrCreate(
    ['code' => 'HQ'],
    ['name' => 'Headquarters', 'is_active' => true],
);

// Bad — duplicates rows on each run
Branch::create(['code' => 'HQ', 'name' => 'Headquarters']);
```

For seeders that write multiple rows (e.g. permissions), loop with
`firstOrCreate`:

```php
foreach ($slugs as $slug) {
    Permission::firstOrCreate(['slug' => $slug],
        ['name' => Str::headline($slug), 'group' => $group]);
}
```

## 4. The three seeded users

`UserSeeder` creates exactly three users with `password` as the
password (bcrypt'd by the `password` cast):

```php
$admin = User::firstOrCreate(['username' => 'admin'], [
    'name'              => 'Super Admin',
    'email'             => 'admin@example.com',
    'password'          => 'password',
    'is_super_admin'    => true,
    'is_active'         => true,
    'locale'            => 'en',
    'default_branch_id' => Branch::where('code', 'HQ')->value('id'),
]);
$manager = User::firstOrCreate(['username' => 'manager'], [...]);
$cashier = User::firstOrCreate(['username' => 'cashier'], [...]);
```

`UserRoleSeeder` then attaches roles:

```php
$admin->roles()->syncWithoutDetaching([Role::where('slug', 'super-admin')->value('id')]);
$manager->roles()->syncWithoutDetaching([Role::where('slug', 'branch-manager')->value('id')]);
$cashier->roles()->syncWithoutDetaching([Role::where('slug', 'cashier')->value('id')]);
```

## 5. Initial product stock

`ProductBranchStockSeeder` writes a row per (branch, product) pair with
a seed quantity so the POS screen has something to sell:

```php
$branches = Branch::all();
foreach (Product::all() as $product) {
    foreach ($branches as $branch) {
        ProductBranchStock::firstOrCreate(
            ['branch_id' => $branch->id, 'product_id' => $product->id],
            ['qty' => $branch->code === 'HQ' ? 150 : 50],
        );
    }
}
```

That is why the smoke test in Chapter 09 expects HQ to start at 147 for
Coke 330ml — 150 seeded minus a few sample sales.

## 6. The framework stub seeders

These exist purely so every migration table has a 1-to-1 file:

```php
class SessionSeeder extends Seeder
{
    public function run(): void
    {
        // No-op — Laravel writes sessions at runtime, not at seed time.
    }
}
```

They cost nothing and give you a hook if you ever need to inject
fixture sessions (rare).

## 7. Run the seeders

```bash
php artisan migrate:fresh --seed       # destroys and recreates everything
php artisan db:seed                    # re-runs against existing schema (idempotent)
php artisan db:seed --class=ProductSeeder    # reseed one table only
```

`migrate:fresh --seed` should complete in a few seconds on SQLite.

## Verify

```bash
php artisan tinker --execute='
use Illuminate\Support\Facades\DB;
foreach (["users","roles","permissions","branches","products",
          "product_branch_stock","sales","sale_items","purchases",
          "activity_logs"] as $t) {
    echo str_pad($t,28) . DB::table($t)->count() . "\n";
}
'
```

Expected rough counts (numbers will vary):

```text
users                       3
roles                       3
permissions                 60+
branches                    2
products                    12
product_branch_stock        24
sales                       1-5
sale_items                  1-10
purchases                   1-5
activity_logs               5+
```

If counts are zero for `users`, `roles`, or `branches`, the seed order
broke or a `firstOrCreate` is missing — read the seeder file and add
the idempotency guard.

Then log in:

| User    | Password   | Expected outcome                |
|---------|------------|---------------------------------|
| admin   | password   | Full sidebar access             |
| manager | password   | No Users / Roles / Permissions  |
| cashier | password   | Only POS, Sales, Customers, Products |

All three must succeed before Chapter 16's audit suites can run.
