<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Permission;
use App\Models\PosRegister;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\Translation;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Full end-to-end audit of every write flow.
 *
 *   php artisan audit:e2e
 *
 * Exit code 0 if every assertion passes; 2 if any assertion fails.
 */
class AuditE2E extends Command
{
    protected $signature = 'audit:e2e';

    protected $description = 'Run a full end-to-end functional audit against the live app';

    private int $pass = 0;

    private int $fail = 0;

    private array $failures = [];

    private function check(string $name, bool $ok, string $detail = ''): void
    {
        if ($ok) {
            $this->pass++;
            $this->info("  PASS  $name");
        } else {
            $this->fail++;
            $this->failures[] = "$name — $detail";
            $this->error("  FAIL  $name — $detail");
        }
    }

    /**
     * Run an internal HTTP request through the kernel with the current session.
     */
    private function hit(string $method, string $uri, array $params = []): Response
    {
        $kernel = app(Kernel::class);
        $session = app('session.store');
        $token = $session->token();
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $params = array_merge(['_token' => $token], $params);
        }
        $req = Request::create($uri, $method, $params);
        $req->setLaravelSession($session);
        $req->headers->set('Accept', 'text/html');
        $req->headers->set('X-CSRF-TOKEN', $token);

        return $kernel->handle($req);
    }

    public function handle(): int
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $cashier = User::where('email', 'cashier@example.com')->firstOrFail();
        auth()->login($admin);
        $this->line('>> Authenticated as '.$admin->email."\n");

        // -------------------------------------------------------------------
        // 1. Branch CRUD
        // -------------------------------------------------------------------
        $this->line('>> 1. Branch CRUD');
        $before = Branch::count();
        $resp = $this->hit('POST', '/admin/branches', [
            'code' => 'TST'.substr(uniqid(), -4),
            'name_en' => 'Test Branch',
            'name_kh' => 'សាខា​សាកល្បង',
            'phone' => '012000999',
            'address' => '123 Test St',
            'is_active' => 1,
        ]);
        $this->check('Branch.store returns 302', $resp->getStatusCode() === 302, 'got '.$resp->getStatusCode());
        $this->check('Branch.store inserted row', Branch::count() === $before + 1);
        $newBranch = Branch::latest('id')->first();

        $resp = $this->hit('PUT', '/admin/branches/'.$newBranch->id, [
            'code' => $newBranch->code,
            'name_en' => 'Updated',
            'name_kh' => $newBranch->name_kh,
            'is_active' => 1,
        ]);
        $this->check('Branch.update returns 302', $resp->getStatusCode() === 302);
        $this->check('Branch.update changed name', Branch::find($newBranch->id)->name_en === 'Updated');

        $resp = $this->hit('DELETE', '/admin/branches/'.$newBranch->id);
        $this->check('Branch.destroy returns 302', $resp->getStatusCode() === 302);
        $this->check('Branch.destroy removed row', Branch::find($newBranch->id) === null);

        // -------------------------------------------------------------------
        // 2. Product CRUD
        // -------------------------------------------------------------------
        $this->line("\n>> 2. Product CRUD");
        $before = Product::count();
        $unique = substr(uniqid(), -6);
        $resp = $this->hit('POST', '/admin/products', [
            'barcode' => 'BAR'.$unique,
            'sku' => 'TST'.$unique,
            'name_en' => 'Test Product',
            'name_kh' => 'ផលិតផល',
            'category_id' => Category::first()->id,
            'brand_id' => Brand::first()->id,
            'unit_id' => Unit::first()->id,
            'cost_price' => '1.00',
            'sale_price' => '2.00',
            'alert_qty' => '5',
            'is_active' => 1,
        ]);
        $this->check('Product.store returns 302', $resp->getStatusCode() === 302, 'got '.$resp->getStatusCode());
        $this->check('Product.store inserted row', Product::count() === $before + 1);

        // -------------------------------------------------------------------
        // 3. Purchase (stock increment)
        // -------------------------------------------------------------------
        $this->line("\n>> 3. Purchase store + stock increment");
        $hq = Branch::where('code', 'HQ')->first();
        $coke = Product::where('sku', 'BEV-COKE-330')->first();
        $supplier = Supplier::first();
        $beforeQty = (float) DB::table('product_branch_stock')
            ->where(['branch_id' => $hq->id, 'product_id' => $coke->id])->value('qty');

        $resp = $this->hit('POST', '/admin/purchases', [
            'branch_id' => $hq->id,
            'supplier_id' => $supplier->id,
            'date' => now()->toDateString(),
            'discount' => '0',
            'note' => 'audit',
            'items' => [
                ['product_id' => $coke->id, 'qty' => 10, 'unit_cost' => 0.50, 'tax' => 0],
            ],
        ]);
        $this->check('Purchase.store returns 302', $resp->getStatusCode() === 302, 'got '.$resp->getStatusCode());
        $afterQty = (float) DB::table('product_branch_stock')
            ->where(['branch_id' => $hq->id, 'product_id' => $coke->id])->value('qty');
        $this->check('Purchase incremented stock by 10', abs($afterQty - $beforeQty - 10) < 0.001,
            "before=$beforeQty after=$afterQty");

        // -------------------------------------------------------------------
        // 4. POS checkout (stock decrement)
        // -------------------------------------------------------------------
        $this->line("\n>> 4. POS checkout + stock decrement");
        $register = PosRegister::where('branch_id', $hq->id)->first();
        $session = PosSession::where('register_id', $register->id)->whereNull('closed_at')->first()
            ?? PosSession::create([
                'register_id' => $register->id,
                'user_id' => $admin->id,
                'opened_at' => now(),
                'opening_cash' => 0,
            ]);
        $customer = Customer::first();
        $beforeQty = (float) DB::table('product_branch_stock')
            ->where(['branch_id' => $hq->id, 'product_id' => $coke->id])->value('qty');

        $resp = $this->hit('POST', '/admin/pos/checkout', [
            'branch_id' => $hq->id,
            'pos_session_id' => $session->id,
            'customer_id' => $customer->id,
            'date' => now()->toDateString(),
            'discount' => '0',
            'paid' => '2.00',
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $coke->id, 'qty' => 2, 'unit_price' => 1.00, 'tax' => 0, 'discount' => 0],
            ],
        ]);
        $this->check('POS.checkout returns 302', $resp->getStatusCode() === 302, 'got '.$resp->getStatusCode());
        $afterQty = (float) DB::table('product_branch_stock')
            ->where(['branch_id' => $hq->id, 'product_id' => $coke->id])->value('qty');
        $this->check('Sale decremented stock by 2', abs($beforeQty - $afterQty - 2) < 0.001,
            "before=$beforeQty after=$afterQty");

        // -------------------------------------------------------------------
        // 5. Stock adjustment
        // -------------------------------------------------------------------
        $this->line("\n>> 5. Stock adjustment (addition)");
        $beforeQty = (float) DB::table('product_branch_stock')
            ->where(['branch_id' => $hq->id, 'product_id' => $coke->id])->value('qty');
        $resp = $this->hit('POST', '/admin/stock-adjustments', [
            'branch_id' => $hq->id,
            'date' => now()->toDateString(),
            'type' => 'addition',
            'reason' => 'audit test',
            'items' => [
                ['product_id' => $coke->id, 'qty' => 5, 'note' => 'audit'],
            ],
        ]);
        $this->check('Adjustment.store returns 302', $resp->getStatusCode() === 302, 'got '.$resp->getStatusCode());
        $afterQty = (float) DB::table('product_branch_stock')
            ->where(['branch_id' => $hq->id, 'product_id' => $coke->id])->value('qty');
        $this->check('Addition adjustment +5', abs($afterQty - $beforeQty - 5) < 0.001,
            "before=$beforeQty after=$afterQty");

        // -------------------------------------------------------------------
        // 6. Stock transfer
        // -------------------------------------------------------------------
        $this->line("\n>> 6. Stock transfer (sent → received)");
        $br01 = Branch::where('code', 'BR01')->first();
        $hqBefore = (float) DB::table('product_branch_stock')
            ->where(['branch_id' => $hq->id, 'product_id' => $coke->id])->value('qty');
        $br01Before = (float) DB::table('product_branch_stock')
            ->where(['branch_id' => $br01->id, 'product_id' => $coke->id])->value('qty');

        $resp = $this->hit('POST', '/admin/stock-transfers', [
            'from_branch_id' => $hq->id,
            'to_branch_id' => $br01->id,
            'date' => now()->toDateString(),
            'note' => 'audit transfer',
            'items' => [
                ['product_id' => $coke->id, 'qty' => 4],
            ],
        ]);
        $this->check('Transfer.store returns 302', $resp->getStatusCode() === 302, 'got '.$resp->getStatusCode());
        $hqMid = (float) DB::table('product_branch_stock')
            ->where(['branch_id' => $hq->id, 'product_id' => $coke->id])->value('qty');
        $this->check('Transfer decrements HQ by 4', abs($hqBefore - $hqMid - 4) < 0.001, "before=$hqBefore mid=$hqMid");

        $newTransfer = StockTransfer::latest('id')->first();
        $resp = $this->hit('POST', '/admin/stock-transfers/'.$newTransfer->id.'/receive');
        $this->check('Transfer.receive returns 302', $resp->getStatusCode() === 302, 'got '.$resp->getStatusCode());
        $br01After = (float) DB::table('product_branch_stock')
            ->where(['branch_id' => $br01->id, 'product_id' => $coke->id])->value('qty');
        $this->check('Transfer.receive increments BR01 by 4', abs($br01After - $br01Before - 4) < 0.001,
            "before=$br01Before after=$br01After");
        $this->check('Transfer status now received', StockTransfer::find($newTransfer->id)->status === 'received');

        // -------------------------------------------------------------------
        // 7. Expense
        // -------------------------------------------------------------------
        $this->line("\n>> 7. Expense");
        $before = Expense::count();
        $resp = $this->hit('POST', '/admin/expenses', [
            'branch_id' => $hq->id,
            'category_id' => ExpenseCategory::first()->id,
            'date' => now()->toDateString(),
            'amount' => '50.00',
            'note' => 'audit',
        ]);
        $this->check('Expense.store returns 302', $resp->getStatusCode() === 302, 'got '.$resp->getStatusCode());
        $this->check('Expense.store inserted row', Expense::count() === $before + 1);

        // -------------------------------------------------------------------
        // 8. User create with role + branch pivots
        // -------------------------------------------------------------------
        $this->line("\n>> 8. User create + pivots");
        $before = User::count();
        $role = Role::where('slug', 'cashier')->first();
        $u2 = substr(uniqid('u'), -6);
        $resp = $this->hit('POST', '/admin/users', [
            'name' => 'Audit User',
            'username' => 'audit-'.$u2,
            'email' => 'audit-'.$u2.'@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'default_branch_id' => $hq->id,
            'is_active' => 1,
            'is_super_admin' => 0,
            'roles' => [$role->id],
            'branches' => [$hq->id],
            'locale' => 'en',
        ]);
        $this->check('User.store returns 302', $resp->getStatusCode() === 302, 'got '.$resp->getStatusCode());
        $this->check('User.store inserted row', User::count() === $before + 1);
        $newUser = User::latest('id')->first();
        $this->check('User.store wired user_role pivot',
            DB::table('user_role')->where(['user_id' => $newUser->id, 'role_id' => $role->id])->exists());
        $this->check('User.store wired user_branch pivot',
            DB::table('user_branch')->where(['user_id' => $newUser->id, 'branch_id' => $hq->id])->exists());

        // -------------------------------------------------------------------
        // 9. Activity logs
        // -------------------------------------------------------------------
        $this->line("\n>> 9. Activity log recording");
        $logs = ActivityLog::query()
            ->where('created_at', '>=', now()->subMinutes(5))
            ->pluck('action')->unique()->toArray();
        $this->check('Log: purchase.created', in_array('purchase.created', $logs), 'logs='.implode(',', $logs));
        $this->check('Log: sale.completed', in_array('sale.completed', $logs));
        $this->check('Log: stock_transfer.sent', in_array('stock_transfer.sent', $logs));
        $this->check('Log: stock_transfer.received', in_array('stock_transfer.received', $logs));
        $this->check('Log: stock_adjustment.created', in_array('stock_adjustment.created', $logs));

        // -------------------------------------------------------------------
        // 10. RBAC enforcement
        // -------------------------------------------------------------------
        $this->line("\n>> 10. RBAC enforcement");
        auth()->logout();
        auth()->login($cashier);
        $resp = $this->hit('GET', '/admin/users');
        $this->check('Cashier blocked on /admin/users (403)', $resp->getStatusCode() === 403, 'got '.$resp->getStatusCode());
        $resp = $this->hit('GET', '/admin/pos/register');
        $this->check('Cashier allowed on /admin/pos/register (200)', $resp->getStatusCode() === 200, 'got '.$resp->getStatusCode());
        $resp = $this->hit('GET', '/admin/reports/profit');
        $this->check('Cashier blocked on /admin/reports/profit (403)', $resp->getStatusCode() === 403, 'got '.$resp->getStatusCode());

        // -------------------------------------------------------------------
        // 11. Role + Permission CRUD
        // -------------------------------------------------------------------
        $this->line("\n>> 11. Role CRUD");
        auth()->login($admin);
        $before = Role::count();
        $resp = $this->hit('POST', '/admin/roles', [
            'name' => 'audit-role-'.substr(uniqid(), -4),
            'description' => 'audit',
            'permissions' => Permission::pluck('id')->take(3)->all(),
        ]);
        $this->check('Role.store returns 302', $resp->getStatusCode() === 302, 'got '.$resp->getStatusCode());
        $this->check('Role.store inserted row', Role::count() === $before + 1);
        $newRole = Role::latest('id')->first();
        $this->check('Role.store wired role_permission pivot',
            DB::table('role_permission')->where('role_id', $newRole->id)->count() === 3);

        // -------------------------------------------------------------------
        // 12. POS Session open + close
        // -------------------------------------------------------------------
        $this->line("\n>> 12. POS Session open + close");
        $resp = $this->hit('POST', '/admin/pos-sessions/open', [
            'register_id' => $register->id,
            'opening_cash' => 50.00,
        ]);
        $this->check('PosSession.open returns 302', $resp->getStatusCode() === 302, 'got '.$resp->getStatusCode());
        $newSession = PosSession::latest('id')->first();
        $this->check('PosSession open (closed_at null)', $newSession->closed_at === null);

        $resp = $this->hit('POST', '/admin/pos-sessions/'.$newSession->id.'/close', [
            'closing_cash' => 50.00,
            'note' => 'audit close',
        ]);
        $this->check('PosSession.close returns 302', $resp->getStatusCode() === 302, 'got '.$resp->getStatusCode());
        $this->check('PosSession closed (closed_at set)',
            PosSession::find($newSession->id)->closed_at !== null);

        // -------------------------------------------------------------------
        // 13. Settings save
        // -------------------------------------------------------------------
        $this->line("\n>> 13. Settings save");
        $resp = $this->hit('PUT', '/admin/settings', [
            'company_name' => 'Audit Co.',
            'company_email' => 'audit@example.com',
            'company_phone' => '012 999 999',
            'company_address' => 'Audit Address',
            'default_currency' => 'USD',
            'default_currency_symbol' => '$',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
        ]);
        $this->check('Settings.update returns 302', $resp->getStatusCode() === 302, 'got '.$resp->getStatusCode());
        $this->check('Settings company_name persisted',
            Setting::get('company_name') === 'Audit Co.');

        // -------------------------------------------------------------------
        // 14. Translation save
        // -------------------------------------------------------------------
        $this->line("\n>> 14. Translation save");
        $before = Translation::count();
        $resp = $this->hit('POST', '/admin/translations', [
            'language_code' => 'en',
            'group' => 'audit',
            'key' => 'audit.test.'.substr(uniqid(), -4),
            'value' => 'Audit English',
        ]);
        $this->check('Translation.store returns 302', $resp->getStatusCode() === 302, 'got '.$resp->getStatusCode());
        $this->check('Translation.store inserted row',
            Translation::count() === $before + 1);

        // -------------------------------------------------------------------
        // 15. Profile save (avatar + locale)
        // -------------------------------------------------------------------
        $this->line("\n>> 15. Profile update");
        $resp = $this->hit('PUT', '/admin/profile', [
            'name' => $admin->name,
            'username' => $admin->username,
            'email' => $admin->email,
            'phone' => '012 333 444',
            'locale' => 'kh',
        ]);
        $this->check('Profile.update returns 302', $resp->getStatusCode() === 302, 'got '.$resp->getStatusCode());
        $admin->refresh();
        $this->check('Profile locale changed to kh', $admin->locale === 'kh');

        // -------------------------------------------------------------------
        // 16. Public password reset flow
        // -------------------------------------------------------------------
        $this->line("\n>> 16. Public password reset flow");
        auth()->logout();
        $resp = $this->hit('GET', '/forgot-password');
        $this->check('Forgot password page 200', $resp->getStatusCode() === 200, 'got '.$resp->getStatusCode());

        $resp = $this->hit('POST', '/forgot-password', ['email' => 'admin@example.com']);
        $this->check('Forgot password POST redirects (302)', $resp->getStatusCode() === 302, 'got '.$resp->getStatusCode());
        $token = DB::table('password_reset_tokens')->where('email', 'admin@example.com')->first();
        $this->check('Reset token row created', $token !== null);

        // -------------------------------------------------------------------
        // 17. Locale switch endpoint (no-refresh KH/EN)
        // -------------------------------------------------------------------
        $this->line("\n>> 17. Locale switch endpoint");
        auth()->login($admin);
        $resp = $this->hit('POST', '/admin/locale', ['locale' => 'kh']);
        $this->check('Locale switch returns 200 or 302',
            in_array($resp->getStatusCode(), [200, 302]), 'got '.$resp->getStatusCode());
        $this->check('Session locale = kh', session('locale') === 'kh');

        // -------------------------------------------------------------------
        // Summary
        // -------------------------------------------------------------------
        $this->line("\n========================================");
        $this->line("PASSED: {$this->pass}");
        $this->line("FAILED: {$this->fail}");
        $this->line('========================================');
        if ($this->fail) {
            $this->error('Failures:');
            foreach ($this->failures as $f) {
                $this->error("  - $f");
            }

            return 2;
        }

        return 0;
    }
}
