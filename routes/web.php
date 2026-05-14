<?php

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerGroupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpenseCategoryController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\PosRegisterController;
use App\Http\Controllers\Admin\PosSessionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\StockTransferController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\TaxRateController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Locale switching: available to authenticated AND guest users so that the
// login page can also toggle KH/EN without a refresh.
Route::post('/admin/locale', [LocaleController::class, 'switch'])->name('admin.locale.switch');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Branches
    Route::middleware('permission:branches.view')->group(function () {
        Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('branches/data', [BranchController::class, 'data'])->name('branches.data');
    });
    Route::middleware('permission:branches.create')->group(function () {
        Route::get('branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
    });
    Route::middleware('permission:branches.edit')->group(function () {
        Route::get('branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::put('branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
    });
    Route::middleware('permission:branches.delete')->group(function () {
        Route::delete('branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
    });

    // Users
    Route::middleware('permission:users.view')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/data', [UserController::class, 'data'])->name('users.data');
    });
    Route::middleware('permission:users.create')->group(function () {
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
    });
    Route::middleware('permission:users.edit')->group(function () {
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    });
    Route::middleware('permission:users.delete')->group(function () {
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Roles
    Route::middleware('permission:roles.view')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/data', [RoleController::class, 'data'])->name('roles.data');
    });
    Route::middleware('permission:roles.create')->group(function () {
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
    });
    Route::middleware('permission:roles.edit')->group(function () {
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });
    Route::middleware('permission:roles.delete')->group(function () {
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // Permissions (read + sync only)
    Route::middleware('permission:permissions.view')->group(function () {
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('permissions/data', [PermissionController::class, 'data'])->name('permissions.data');
    });

    // Languages
    Route::middleware('permission:languages.view')->group(function () {
        Route::get('languages', [LanguageController::class, 'index'])->name('languages.index');
        Route::get('languages/data', [LanguageController::class, 'data'])->name('languages.data');
    });
    Route::middleware('permission:languages.create')->group(function () {
        Route::get('languages/create', [LanguageController::class, 'create'])->name('languages.create');
        Route::post('languages', [LanguageController::class, 'store'])->name('languages.store');
    });
    Route::middleware('permission:languages.edit')->group(function () {
        Route::get('languages/{language}/edit', [LanguageController::class, 'edit'])->name('languages.edit');
        Route::put('languages/{language}', [LanguageController::class, 'update'])->name('languages.update');
    });
    Route::middleware('permission:languages.delete')->group(function () {
        Route::delete('languages/{language}', [LanguageController::class, 'destroy'])->name('languages.destroy');
    });

    // Translations
    Route::middleware('permission:translations.view')->group(function () {
        Route::get('translations', [TranslationController::class, 'index'])->name('translations.index');
        Route::get('translations/data', [TranslationController::class, 'data'])->name('translations.data');
    });
    Route::middleware('permission:translations.edit')->group(function () {
        Route::get('translations/create', [TranslationController::class, 'create'])->name('translations.create');
        Route::post('translations', [TranslationController::class, 'store'])->name('translations.store');
        Route::get('translations/{translation}/edit', [TranslationController::class, 'edit'])->name('translations.edit');
        Route::put('translations/{translation}', [TranslationController::class, 'update'])->name('translations.update');
        Route::delete('translations/{translation}', [TranslationController::class, 'destroy'])->name('translations.destroy');
    });

    // Settings
    Route::middleware('permission:settings.view')->group(function () {
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    });
    Route::middleware('permission:settings.edit')->group(function () {
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });

    // Catalog --------------------------------------------------------------
    foreach (
        [
            ['categories', CategoryController::class, 'category'],
            ['brands', BrandController::class, 'brand'],
            ['units', UnitController::class, 'unit'],
            ['tax-rates', TaxRateController::class, 'tax_rate'],
            ['products', ProductController::class, 'product'],
            ['suppliers', SupplierController::class, 'supplier'],
            ['customers', CustomerController::class, 'customer'],
            ['customer-groups', CustomerGroupController::class, 'customer_group'],
        ] as [$slug, $controller, $param]
    ) {
        $base = str_replace('-', '_', $slug);
        Route::middleware("permission:$base.view")->group(function () use ($slug, $controller) {
            Route::get("$slug", [$controller, 'index'])->name("$slug.index");
            Route::get("$slug/data", [$controller, 'data'])->name("$slug.data");
        });
        Route::middleware("permission:$base.create")->group(function () use ($slug, $controller) {
            Route::get("$slug/create", [$controller, 'create'])->name("$slug.create");
            Route::post("$slug", [$controller, 'store'])->name("$slug.store");
        });
        Route::middleware("permission:$base.edit")->group(function () use ($slug, $controller, $param) {
            Route::get("$slug/{{$param}}/edit", [$controller, 'edit'])->name("$slug.edit");
            Route::put("$slug/{{$param}}", [$controller, 'update'])->name("$slug.update");
        });
        Route::middleware("permission:$base.delete")->group(function () use ($slug, $controller, $param) {
            Route::delete("$slug/{{$param}}", [$controller, 'destroy'])->name("$slug.destroy");
        });
    }

    // ---------------------------------------------------------------
    // Phase 2 — Operations
    // ---------------------------------------------------------------

    // Purchases (CRUD + show + add payment)
    Route::middleware('permission:purchases.view')->group(function () {
        Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('purchases/data', [PurchaseController::class, 'data'])->name('purchases.data');
        Route::get('purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    });
    Route::middleware('permission:purchases.create')->group(function () {
        Route::get('purchases/create/new', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::post('purchases/{purchase}/payments', [PurchaseController::class, 'addPayment'])->name('purchases.payments.add');
    });
    Route::middleware('permission:purchases.edit')->group(function () {
        Route::get('purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
        Route::put('purchases/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update');
    });
    Route::middleware('permission:purchases.delete')->group(function () {
        Route::delete('purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');
    });

    // POS registers (full CRUD)
    Route::middleware('permission:pos_registers.view')->group(function () {
        Route::get('pos-registers', [PosRegisterController::class, 'index'])->name('pos-registers.index');
        Route::get('pos-registers/data', [PosRegisterController::class, 'data'])->name('pos-registers.data');
    });
    Route::middleware('permission:pos_registers.create')->group(function () {
        Route::get('pos-registers/create', [PosRegisterController::class, 'create'])->name('pos-registers.create');
        Route::post('pos-registers', [PosRegisterController::class, 'store'])->name('pos-registers.store');
    });
    Route::middleware('permission:pos_registers.edit')->group(function () {
        Route::get('pos-registers/{pos_register}/edit', [PosRegisterController::class, 'edit'])->name('pos-registers.edit');
        Route::put('pos-registers/{pos_register}', [PosRegisterController::class, 'update'])->name('pos-registers.update');
    });
    Route::middleware('permission:pos_registers.delete')->group(function () {
        Route::delete('pos-registers/{pos_register}', [PosRegisterController::class, 'destroy'])->name('pos-registers.destroy');
    });

    // POS sessions (open/close/index/data only)
    Route::middleware('permission:pos_sessions.view')->group(function () {
        Route::get('pos-sessions', [PosSessionController::class, 'index'])->name('pos-sessions.index');
        Route::get('pos-sessions/data', [PosSessionController::class, 'data'])->name('pos-sessions.data');
    });
    Route::middleware('permission:pos_sessions.create')->group(function () {
        Route::post('pos-sessions/open', [PosSessionController::class, 'open'])->name('pos-sessions.open');
    });
    Route::middleware('permission:pos_sessions.edit')->group(function () {
        Route::post('pos-sessions/{pos_session}/close', [PosSessionController::class, 'close'])->name('pos-sessions.close');
    });

    // POS register UI + checkout
    Route::middleware('permission:pos.use')->group(function () {
        Route::get('pos/register', [PosController::class, 'register'])->name('pos.register');
        Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    });

    // Sales (index + data + show + destroy)
    Route::middleware('permission:sales.view')->group(function () {
        Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('sales/data', [SaleController::class, 'data'])->name('sales.data');
        Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    });
    Route::middleware('permission:sales.delete')->group(function () {
        Route::delete('sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');
    });

    // Stock adjustments
    Route::middleware('permission:stock_adjustments.view')->group(function () {
        Route::get('stock-adjustments', [StockAdjustmentController::class, 'index'])->name('stock-adjustments.index');
        Route::get('stock-adjustments/data', [StockAdjustmentController::class, 'data'])->name('stock-adjustments.data');
    });
    Route::middleware('permission:stock_adjustments.create')->group(function () {
        Route::get('stock-adjustments/create', [StockAdjustmentController::class, 'create'])->name('stock-adjustments.create');
        Route::post('stock-adjustments', [StockAdjustmentController::class, 'store'])->name('stock-adjustments.store');
    });
    Route::middleware('permission:stock_adjustments.delete')->group(function () {
        Route::delete('stock-adjustments/{stock_adjustment}', [StockAdjustmentController::class, 'destroy'])->name('stock-adjustments.destroy');
    });

    // Stock transfers
    Route::middleware('permission:stock_transfers.view')->group(function () {
        Route::get('stock-transfers', [StockTransferController::class, 'index'])->name('stock-transfers.index');
        Route::get('stock-transfers/data', [StockTransferController::class, 'data'])->name('stock-transfers.data');
        Route::get('stock-transfers/{stock_transfer}', [StockTransferController::class, 'show'])->name('stock-transfers.show');
    });
    Route::middleware('permission:stock_transfers.create')->group(function () {
        Route::get('stock-transfers/create/new', [StockTransferController::class, 'create'])->name('stock-transfers.create');
        Route::post('stock-transfers', [StockTransferController::class, 'store'])->name('stock-transfers.store');
    });
    Route::middleware('permission:stock_transfers.edit')->group(function () {
        Route::post('stock-transfers/{stock_transfer}/receive', [StockTransferController::class, 'receive'])->name('stock-transfers.receive');
    });
    Route::middleware('permission:stock_transfers.delete')->group(function () {
        Route::delete('stock-transfers/{stock_transfer}', [StockTransferController::class, 'destroy'])->name('stock-transfers.destroy');
    });

    // Expense categories (full CRUD)
    Route::middleware('permission:expense_categories.view')->group(function () {
        Route::get('expense-categories', [ExpenseCategoryController::class, 'index'])->name('expense-categories.index');
        Route::get('expense-categories/data', [ExpenseCategoryController::class, 'data'])->name('expense-categories.data');
    });
    Route::middleware('permission:expense_categories.create')->group(function () {
        Route::get('expense-categories/create', [ExpenseCategoryController::class, 'create'])->name('expense-categories.create');
        Route::post('expense-categories', [ExpenseCategoryController::class, 'store'])->name('expense-categories.store');
    });
    Route::middleware('permission:expense_categories.edit')->group(function () {
        Route::get('expense-categories/{expense_category}/edit', [ExpenseCategoryController::class, 'edit'])->name('expense-categories.edit');
        Route::put('expense-categories/{expense_category}', [ExpenseCategoryController::class, 'update'])->name('expense-categories.update');
    });
    Route::middleware('permission:expense_categories.delete')->group(function () {
        Route::delete('expense-categories/{expense_category}', [ExpenseCategoryController::class, 'destroy'])->name('expense-categories.destroy');
    });

    // Expenses (full CRUD)
    Route::middleware('permission:expenses.view')->group(function () {
        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('expenses/data', [ExpenseController::class, 'data'])->name('expenses.data');
    });
    Route::middleware('permission:expenses.create')->group(function () {
        Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    });
    Route::middleware('permission:expenses.edit')->group(function () {
        Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    });
    Route::middleware('permission:expenses.delete')->group(function () {
        Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });

    // Reports
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('reports/sales-summary', [ReportController::class, 'salesSummary'])->name('reports.sales-summary');
        Route::get('reports/stock-by-branch', [ReportController::class, 'stockByBranch'])->name('reports.stock-by-branch');
        Route::get('reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
        Route::get('reports/expenses', [ReportController::class, 'expenses'])->name('reports.expenses');
    });
});
