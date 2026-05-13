<?php

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerGroupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
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
});
