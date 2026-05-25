# Chapter 16 — Testing and audit

Without CI configured on the repo, we lean on three local audit suites
that you run after **every** change. Combined they exercise:

- Code style (`pint`)
- Frontend compile (`npm run build`)
- PHP syntax (`php -l`)
- 93 admin GET routes (`tests/audit_smoke.sh`)
- 23 Yajra DataTable JSON endpoints (`tests/audit_datatables.sh`)
- 51 write-flow assertions (`php artisan audit:e2e`)
- 8 manual browser e2e tests (Chapter 16.4)

If all of those pass, the app is safe to ship.

## 1. The `audit:e2e` Artisan command

Lives at `app/Console/Commands/AuditE2E.php`. Run with:

```bash
php artisan audit:e2e
```

The command:

1. Boots the HTTP kernel in-process (no curl, no second server).
2. Logs in as admin via session cookies.
3. Walks every write flow:
   - Branch / Product / Role / User / Supplier / Customer / Expense /
     Translation / PosRegister / PosSession / Settings / Profile CRUD.
   - Purchase → stock increment by qty.
   - POS checkout → stock decrement by qty.
   - Stock adjustment → stock changed by qty.
   - Stock transfer sent → HQ stock decrement.
   - Stock transfer received → BR01 stock increment + status flip.
   - Activity log row created for each write.
4. Switches to cashier and asserts 403 on Users + Reports.
5. Submits forgot-password and asserts `password_reset_tokens` write.
6. Switches locale and asserts `session('locale')` write.

Each check prints a single line, and the command exits 0 if all 51
pass, 2 otherwise. Sample output:

```text
[OK]   branch.crud
[OK]   product.crud
[OK]   purchase.received.stock_increment  qty_after=10
[OK]   sale.completed.stock_decrement     qty_after=8
[OK]   transfer.sent.stock_decrement      qty_after=5
[OK]   transfer.received.stock_increment  qty_after=3
[OK]   rbac.cashier.users_blocked         status=403
[OK]   forgot.password.token_created      rows=1
[OK]   locale.switch.session_written      locale=kh
...
51 passed / 0 failed
```

## 2. `tests/audit_smoke.sh`

A bash script that logs in as admin via cookie file and hits every
admin URL with `curl`, asserting HTTP 200.

```bash
#!/usr/bin/env bash
set -euo pipefail

BASE="http://127.0.0.1:8000"
COOKIES=$(mktemp)
trap 'rm -f "$COOKIES"' EXIT

# Get CSRF and login
curl -s -c "$COOKIES" -b "$COOKIES" "$BASE/login" >/dev/null
TOKEN=$(grep XSRF-TOKEN "$COOKIES" | awk '{print $NF}')
curl -s -b "$COOKIES" -c "$COOKIES" -X POST "$BASE/login" \
     -H "X-XSRF-TOKEN: $TOKEN" \
     -d "username=admin&password=password" >/dev/null

# 93 admin GETs
URLS=(
    "/admin"
    "/admin/branches" "/admin/branches/create"
    "/admin/products" "/admin/products/create"
    # ...
)

FAILED=0
for u in "${URLS[@]}"; do
    code=$(curl -s -o /dev/null -w "%{http_code}" -b "$COOKIES" "$BASE$u")
    if [[ "$code" != "200" ]]; then
        echo "FAIL $u → $code"
        FAILED=$((FAILED+1))
    fi
done

echo "$((${#URLS[@]} - FAILED)) / ${#URLS[@]} routes returned 200"
exit $FAILED
```

Run with:

```bash
bash tests/audit_smoke.sh
```

Expected:

```text
93 / 93 routes returned 200
```

## 3. `tests/audit_datatables.sh`

Identical login flow, but hits the JSON `/admin/<module>/data` endpoints
and asserts that the response is JSON with `data`, `recordsTotal`, and
`recordsFiltered` fields.

```bash
URLS=(
    "/admin/branches/data"
    "/admin/products/data"
    "/admin/activity-logs/data"
    # ... 23 in total
)

for u in "${URLS[@]}"; do
    body=$(curl -s -b "$COOKIES" "$BASE$u")
    if ! echo "$body" | jq -e '.data and .recordsTotal != null and .recordsFiltered != null' >/dev/null; then
        echo "FAIL $u"
        exit 1
    fi
done

echo "23 / 23 datatables returned valid JSON"
```

## 4. Browser e2e tests

The audit suites cover server-side behavior; UI bugs (SweetAlert wired
incorrectly, sidebar not localizing) need a browser pass. The
`.agents/skills/testing-super-market-erp/SKILL.md` skill in this repo
captures the eight tests you should run after any significant change:

1. Admin login → dashboard chrome.
2. KH/EN no-refresh switch (sidebar + h4 swap in place).
3. Products list Yajra server-side filter.
4. SweetAlert2 delete confirm (Cancel keeps, Yes deletes).
5. POS sale stock decrement (147 → 145 for 2× Coke).
6. Activity log row recorded for the sale.
7. Cashier RBAC: sidebar restricted + `/admin/users` 403.
8. `/forgot-password` page renders + token row written.

Each test has concrete pass/fail criteria; the skill walks through
exact button labels and expected DOM changes.

## 5. PHPUnit (optional)

The framework ships `phpunit/phpunit` and a `tests/` directory. We did
not add module-level tests because the audit command above covers the
same behavior with less code. If you need to retrofit unit tests:

```bash
php artisan make:test StockServiceTest --unit
```

Then test `StockService::applyDelta`:

```php
public function test_apply_delta_increments_stock(): void
{
    $svc = app(StockService::class);
    Branch::factory()->create(['id' => 1]);
    $product = Product::factory()->create();

    $row = $svc->applyDelta(1, $product->id, 5);
    $this->assertSame(5.0, (float) $row->qty);

    $svc->applyDelta(1, $product->id, -2);
    $this->assertSame(3.0, (float) ProductBranchStock::where('product_id', $product->id)->value('qty'));
}
```

Run with `php artisan test`.

## 6. The combined local CI command

Add a script in `composer.json`:

```json
"scripts": {
    "audit": [
        "vendor/bin/pint --test",
        "npm run build",
        "find app -name '*.php' -print0 | xargs -0 -n1 php -l > /dev/null",
        "@php artisan audit:e2e",
        "bash tests/audit_smoke.sh",
        "bash tests/audit_datatables.sh"
    ]
}
```

Run before every commit:

```bash
composer audit
```

This is the closest you can get to a real CI pipeline without spinning
up GitHub Actions. If you later add GitHub Actions, configure it to
run the same command on every PR.

## Verify

```bash
composer audit
```

Expected output ends with:

```text
pint:    52 files, 0 style violations
build:   built in 1.8s
phplint: no syntax errors detected
audit:   51 passed / 0 failed
smoke:   93 / 93 routes returned 200
dt:      23 / 23 datatables returned valid JSON
```

If any line breaks, fix it before shipping.
