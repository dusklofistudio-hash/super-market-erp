# Chapter 02 — Database schema

The whole business model lives in one consolidated migration file:
`database/migrations/2026_05_13_000000_create_super_market_pos_all_tables.php`.

This chapter walks through the 30 business tables (plus 11 framework
tables Laravel ships with) and explains **why** each one exists and how
the foreign keys flow together. Open the migration in a second pane and
read along.

## Why one migration?

Most Laravel apps grow one migration per change. This project keeps a
**single** migration because:

- Every table the app needs is created in one atomic transaction —
  `migrate:fresh --seed` is reliable and fast.
- Cross-table foreign keys are easier to manage in a known order.
- New installs reproduce the same schema regardless of dev history.

The downside is you cannot use this pattern after production launch
(you would lose data). For greenfield projects this is the simplest
shape; for live systems, switch to incremental migrations on day 2.

## Conventions used across every table

- Primary keys: `$table->id()` → `bigint unsigned auto_increment`.
- Timestamps: `$table->timestamps()` → `created_at`, `updated_at`.
- Soft-delete columns: not used; deletion is hard. Activity logs cover
  the audit trail.
- Money columns: `decimal(14,4)` — four decimal places handle currencies
  with sub-cent precision (KHR rounding, FX conversions later).
- Foreign keys use `restrictOnDelete()` by default. Junction tables
  (`role_permission`, `user_role`, `user_branch`) use `cascadeOnDelete`.
- Soft enums are stored as `string` and validated in FormRequests —
  this avoids painful `ALTER TYPE` migrations later.

## Table groups

### 1. Tenancy and access control

| Table              | Purpose                                            |
|--------------------|----------------------------------------------------|
| `branches`         | Physical stores. Every transaction is branch-scoped. |
| `users`            | Auth accounts. Adds `username`, `avatar`, `locale`, `active`, FK `branches.id`. |
| `roles`            | Named role like `Super Admin`, `Branch Manager`, `Cashier`. |
| `permissions`      | Granular keys like `users.view`, `products.create`. |
| `role_permission`  | M:N junction.                                       |
| `user_role`        | M:N junction (a user can have many roles).         |
| `user_branch`      | M:N junction; lets a user access multiple branches. |

### 2. Localization

| Table           | Purpose                                                  |
|-----------------|----------------------------------------------------------|
| `languages`     | Locale code + display name + `is_active` flag.           |
| `translations`  | DB-stored override on top of file-based `lang/*` arrays. |

### 3. Catalog

| Table         | Purpose                                                |
|---------------|--------------------------------------------------------|
| `categories`  | Hierarchical (self-FK `parent_id`).                    |
| `brands`      | Manufacturer name + logo.                              |
| `units`       | Unit of measure (`piece`, `kg`, `liter`, …).           |
| `tax_rates`   | Rate % + `is_inclusive` flag.                          |
| `products`    | SKU, name, cost, price, FK to category/brand/unit/tax. |
| `product_branch_stock` | `(branch_id, product_id)` → `qty`. The single source of truth for on-hand inventory. |

### 4. Parties

| Table             | Purpose                            |
|-------------------|------------------------------------|
| `suppliers`       | Who we buy from.                   |
| `customers`       | Who we sell to.                    |
| `customer_groups` | Pricing tier / loyalty bucket.     |

### 5. Procurement

| Table                 | Purpose                                |
|-----------------------|----------------------------------------|
| `purchases`           | PO header: supplier, branch, totals.   |
| `purchase_items`      | Per-line: product, qty, unit_cost.     |
| `purchase_payments`   | Supplier payment ledger.               |

### 6. Sales / POS

| Table             | Purpose                                       |
|-------------------|-----------------------------------------------|
| `pos_registers`   | Physical till at a branch.                    |
| `pos_sessions`    | Cashier's shift open → close per register.    |
| `sales`           | Sale header: branch, register, session, customer, totals. |
| `sale_items`      | Per-line: product, qty, unit_price, tax.      |
| `sale_payments`   | Cash/card/credit ledger per sale.             |

### 7. Stock operations

| Table                       | Purpose                                       |
|-----------------------------|-----------------------------------------------|
| `stock_adjustments`         | Header: branch + reason (`addition`, `subtraction`, `damage`, `loss`). |
| `stock_adjustment_items`    | Per-line: product, qty.                       |
| `stock_transfers`           | Header: from_branch → to_branch, status (`pending`, `sent`, `received`). |
| `stock_transfer_items`      | Per-line: product, qty.                       |

### 8. Expenses

| Table                | Purpose                              |
|----------------------|--------------------------------------|
| `expense_categories` | Bucket like `Utilities`, `Rent`.     |
| `expenses`           | Date, amount, category, branch, note. |

### 9. Settings + audit

| Table                    | Purpose                                       |
|--------------------------|-----------------------------------------------|
| `settings`               | Key/value config (store name, default currency, receipt footer). |
| `activity_logs`          | Append-only audit trail. `action`, `subject_type`, `subject_id`, `payload`, `ip`. |
| `password_reset_tokens`  | Laravel-standard reset token store.           |

### 10. Framework tables

`migrations`, `sessions`, `jobs`, `job_batches`, `failed_jobs`,
`cache`, `cache_locks`, `personal_access_tokens` — these are stock
Laravel and need no app-specific logic.

## Foreign-key graph (read top to bottom)

```text
branches ──┬──< users
           ├──< user_branch >── users
           ├──< product_branch_stock >── products
           ├──< pos_registers ──< pos_sessions ──< sales
           ├──< purchases ──< purchase_items ──> products
           ├──< sales ──< sale_items ──> products
           ├──< stock_adjustments ──< stock_adjustment_items ──> products
           ├──< stock_transfers (from + to) ──< stock_transfer_items
           └──< expenses ──> expense_categories

products ──> categories
        ──> brands
        ──> units
        ──> tax_rates

users ──< user_role >── roles ──< role_permission >── permissions
users ──< sales (cashier)
users ──< activity_logs
```

## Soft enum reference

Storing enums as strings avoids `ALTER TABLE` pain. Values are validated
in FormRequests. The valid sets are:

- `stock_adjustments.reason`: `addition`, `subtraction`, `damage`, `loss`
- `stock_transfers.status`: `pending`, `sent`, `received`, `cancelled`
- `purchases.status`: `draft`, `ordered`, `received`, `cancelled`
- `sales.status`: `paid`, `partial`, `unpaid`, `void`
- `users.locale`: `en`, `kh`

## How to read the migration

Open the file and notice the order: branches → users → roles →
permissions → role_permission → user_role → user_branch → languages →
translations → catalog → parties → procurement → POS → sales → stock →
expenses → settings → activity_logs.

The order matters because every foreign key references a table that is
already created. If you ever need to reorder, MySQL will refuse the
migration at the FK statement.

## Verify

Run a fresh migration and confirm 41 tables (30 business + 11
framework):

```bash
php artisan migrate:fresh
```

Then in `tinker`:

```bash
php artisan tinker --execute='
use Illuminate\Support\Facades\Schema;
$tables = DB::select("SELECT name FROM sqlite_master WHERE type = \"table\"");
echo count($tables) . " tables\n";
'
```

Expected output (SQLite reports `sqlite_sequence` plus 41 user tables):

```text
42 tables
```

On MySQL:

```sql
SHOW TABLES;
-- expected: 41 rows
```

If you see fewer tables, an FK violation killed the migration mid-way —
check `storage/logs/laravel.log` for the failing statement and confirm
the referenced table was created before the FK was added.

## Next

Chapter 03 will reuse this schema to render a permission-aware sidebar.
Before moving on, make sure `migrate:fresh` runs cleanly.
