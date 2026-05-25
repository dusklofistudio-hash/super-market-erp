---
name: testing-super-market-erp
description: Boot, seed, and test the Super Market ERP (Laravel 12 + Inertia/React + Yajra DataTables + manual RBAC + KH/EN no-refresh i18n) end-to-end. Use when verifying POS sales, stock deltas, RBAC, SweetAlert delete confirms, activity logs, forgot password, or any admin CRUD flow.
---

# Testing Super Market ERP end-to-end

## Boot the app

```bash
cd /home/ubuntu/repos/super-market-erp
php artisan migrate:fresh --seed     # 41 dedicated seeders, takes ~10s
php artisan serve --host=127.0.0.1 --port=8000 &   # background
```

The Vite dev server is NOT required for testing — JS/CSS are pre-built and served from `public/build/`. If you change frontend code, `npm run build` before reloading.

## Seeded credentials

| Username  | Password   | Role         | Sidebar surface |
|-----------|------------|--------------|-----------------|
| `admin`   | `password` | Super Admin  | All 30 modules + Reports + Administration |
| `manager` | `password` | Branch Manager | Most CRUD modules; no Users/Roles/Permissions |
| `cashier` | `password` | Cashier      | Only Dashboard, Products (read), Customers, POS, Sessions, Sales |

All three log in via `/login` (username OR email field).

## RBAC verification snippet

Cashier should be hard-blocked from admin pages. Quickest proof:

```bash
# After login as cashier, hit /admin/users → expect 403 'Missing permission: users.view'
curl -i -b cookies.txt http://127.0.0.1:8000/admin/users | head -3
```

In the browser, the 403 page is bare HTML with `<h1>403</h1>` + `Missing permission: <key>`.

## POS register quirk (often trips up testing)

The POS cart at `/admin/pos/register` will show **"No active session"** unless you pass `?session=N`:

```
# WRONG — shows empty cart with disabled Complete sale button
http://127.0.0.1:8000/admin/pos/register

# RIGHT — picks up open session #2 and enables the cart
http://127.0.0.1:8000/admin/pos/register?session=2
```

Find the open session id via:

```bash
php artisan tinker --execute='
use App\Models\PosSession;
foreach (PosSession::whereNull("closed_at")->get() as $s) {
    echo "session #$s->id register_id=$s->register_id\n";
}'
```

If no open session exists, open one at `/admin/pos-sessions/create` before the POS register page will be usable.

## POS sale stock-delta verification

The canonical assertion for a POS sale's correctness is: did `product_branch_stock.qty` change by exactly the cart qty (negative delta)?

```bash
php artisan tinker --execute='
use App\Models\Product;
use Illuminate\Support\Facades\DB;
$p = Product::where("sku","BEV-COKE-330")->first();
echo DB::table("product_branch_stock")
  ->where(["branch_id"=>1,"product_id"=>$p->id])->value("qty")
  ."\n";'
```

A 2× Coke sale at HQ should drop the value by exactly 2.

Ready-to-use SKUs in the seeded catalog:

- `BEV-COKE-330` — Coca-Cola 330ml
- `BEV-PEPSI-330` — Pepsi 330ml
- `BEV-WATER-1L` — Bottled water 1L
- `GRO-RICE-5KG` — Jasmine rice 5kg
- `BAK-BREAD-LOAF` — White bread loaf
- `SNK-CHIPS-50G` — Potato chips 50g
- `DAI-MILK-1L` — Fresh milk 1L
- `PER-SHAMPOO-200ML` — Shampoo 200ml
- `HOM-DETERGENT-1KG` — Laundry detergent 1kg

## KH/EN no-refresh switch test

The language pill is in the top-right header (`EN` / `KH`). Click it → dropdown → ខ្មែរ. The fix that landed in PR #3 makes the **sidebar + React content** swap in place via the `smk:locale-changed` custom DOM event.

What to assert:
- URL stays exactly `/admin` (no full reload, no progress bar)
- Pill changes from `EN` to `KH`
- Sidebar section labels turn Khmer (e.g. `Operations` → `ប្រតិបត្តិការ`)
- Dashboard `h4` (welcome) turns Khmer
- The user-dropdown rows `My profile` / `Logout` also localize (added in PR #5)

If only the pill changes but everything else stays English → it's the regression PR #2/#3 fixed.

## SweetAlert2 delete-confirm test

Clicking the red **Delete** button on any row in any DataTable opens a SweetAlert2 modal:

- Title: `Delete confirmation` (EN) / `បញ្ជាក់ការលុប` (KH)
- Primary: red `Yes, delete` / `យល់ព្រម លុប`
- Secondary: gray `Cancel` / `បោះបង់`

The delete handler is registered globally as a `window.smkBindRowActions` side effect imported in `resources/js/app.jsx`. If the modal does NOT open and a native `confirm()` opens instead, the import was tree-shaken — search for `import './Components/RowActions'` in `app.jsx`.

BR01 deletes cleanly (FKs use ON DELETE CASCADE). HQ delete may fail if there are sales/sessions referencing it — that's expected.

## Yajra DataTable filter test

Every list page (e.g. `/admin/products`, `/admin/branches`) uses a Yajra server-side DataTable. To prove the filter is server-side, not client-side:

- Type `Coke` in the search box → table shrinks to 1 row
- Footer reads `Showing 1 to 1 of 1 entries (filtered from 12 total entries)`
- Network panel shows a request to `/admin/products/data?search[value]=Coke`

## Activity Logs viewer

`/admin/activity-logs` shows an audit trail of every login, purchase, sale, transfer, adjustment, etc. The newest row after a sale should show `action=sale.completed` with `payload={"ref_no":"SAL-...","total":...,"paid":...}`.

The logger lives in `app/Services/ActivityLogger.php` and is invoked from controllers + the auth listener.

## Forgot password flow

Public route `/forgot-password` (not under `/admin`). Logged-in users are redirected to `/admin` by the `guest` middleware — log out first.

Submit `admin@example.com` → page re-renders with translated success banner → `password_reset_tokens` row upserts (Laravel's broker uses `updateOrCreate`, so the row's `created_at` is the proof of the write):

```bash
php artisan tinker --execute='
use Illuminate\Support\Facades\DB;
foreach (DB::table("password_reset_tokens")->get() as $r) {
    echo "email=$r->email created_at=$r->created_at\n";
}'
```

The `/admin/forgot-password/done` URL was a dead route returning 500 — it was removed in PR #7. Don't link to it.

## Logout quirk

`/logout` is **POST-only** (CSRF protected). Don't navigate to it via the URL bar — click the Logout button in the user-dropdown instead, or POST with the CSRF token.

## Three audit suites (use these as local CI)

These run quickly and catch regressions without browser interaction:

```bash
php artisan audit:e2e            # 51 write-flow assertions (CRUD + stock + RBAC + activity logs)
bash tests/audit_smoke.sh        # 93 admin GET routes, all expect 200
bash tests/audit_datatables.sh   # 23 Yajra AJAX endpoints, all expect valid JSON shape
vendor/bin/pint --test           # PHP code style
npm run build                    # JSX compile
```

Run these BEFORE you start a browser recording to catch any environment-level issues first.

## Login-state preservation between tests

The session cookie is `super-market-erp-session`. If you need to switch between admin and cashier mid-test:

1. Click user-dropdown → Logout (POST, CSRF-safe)
2. Login form re-renders at `/login`
3. Login as the new user

Direct URL navigation to a protected page while logged in as the wrong role gives a clean 403, not a redirect — that's a feature, not a bug.

## Devin Secrets Needed

None — the app runs entirely locally with seeded data. All credentials (admin/password etc.) are local test accounts seeded by `database/seeders/`. No external API keys required.
