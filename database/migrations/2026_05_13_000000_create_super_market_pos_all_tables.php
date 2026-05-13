<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Single consolidated migration containing every table for the Super Market
 * ERP / POS Management System. Schema is grouped by module for clarity.
 *
 * Required by the project layout:
 *   database/migrations/2026_05_13_000000_create_super_market_pos_all_tables.php
 */
return new class extends Migration
{
    public function up(): void
    {
        // -------------------------------------------------------------------
        // Laravel core / framework tables (users, cache, jobs, sessions, …)
        // -------------------------------------------------------------------
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->foreignId('default_branch_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_super_admin')->default(false);
            $table->string('locale', 8)->default('en');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // -------------------------------------------------------------------
        // Manual RBAC (no package). Users <-> roles <-> permissions <-> branches
        // -------------------------------------------------------------------
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_locked')->default(false); // protects super-admin
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->index('module');
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('user_role', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['user_id', 'role_id']);
        });

        // -------------------------------------------------------------------
        // Branches (multi-branch core) + per-user branch access
        // -------------------------------------------------------------------
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_kh')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_branch', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->primary(['user_id', 'branch_id']);
        });

        // Defer the FK on users.default_branch_id until branches exist
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('default_branch_id')->references('id')->on('branches')->nullOnDelete();
        });

        // -------------------------------------------------------------------
        // Localization
        // -------------------------------------------------------------------
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();
            $table->string('name');
            $table->string('native_name')->nullable();
            $table->enum('direction', ['ltr', 'rtl'])->default('ltr');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('language_code', 8);
            $table->string('group')->default('messages');
            $table->string('key');
            $table->text('value');
            $table->timestamps();
            $table->unique(['language_code', 'group', 'key']);
            $table->index(['language_code', 'group']);
            $table->foreign('language_code')->references('code')->on('languages')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // -------------------------------------------------------------------
        // Settings (k/v)
        // -------------------------------------------------------------------
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type', 32)->default('string'); // string|int|bool|json|file
            $table->string('group', 64)->default('general');
            $table->timestamps();
        });

        // -------------------------------------------------------------------
        // Catalog: categories, brands, units, tax rates
        // -------------------------------------------------------------------
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name_en');
            $table->string('name_kh')->nullable();
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_kh')->nullable();
            $table->string('short_name', 16);
            $table->foreignId('base_unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->decimal('conversion_factor', 15, 4)->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('rate', 8, 4)->default(0); // percent
            $table->boolean('is_inclusive')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // -------------------------------------------------------------------
        // Products + per-branch stock
        // -------------------------------------------------------------------
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->unique();
            $table->string('sku')->unique();
            $table->string('name_en');
            $table->string('name_kh')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('tax_rate_id')->nullable()->constrained('tax_rates')->nullOnDelete();
            $table->decimal('cost_price', 15, 4)->default(0);
            $table->decimal('sale_price', 15, 4)->default(0);
            $table->decimal('alert_qty', 15, 4)->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['name_en']);
        });

        Schema::create('product_branch_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->decimal('qty', 15, 4)->default(0);
            $table->decimal('reorder_qty', 15, 4)->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'branch_id']);
        });

        // -------------------------------------------------------------------
        // Parties: suppliers, customers, customer groups
        // -------------------------------------------------------------------
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('company')->nullable();
            $table->text('address')->nullable();
            $table->decimal('opening_balance', 15, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('discount_percent', 8, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('customer_group_id')->nullable()->constrained('customer_groups')->nullOnDelete();
            $table->decimal('opening_balance', 15, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // -------------------------------------------------------------------
        // Purchasing
        // -------------------------------------------------------------------
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->decimal('subtotal', 15, 4)->default(0);
            $table->decimal('tax', 15, 4)->default(0);
            $table->decimal('discount', 15, 4)->default(0);
            $table->decimal('total', 15, 4)->default(0);
            $table->decimal('paid', 15, 4)->default(0);
            $table->enum('status', ['draft', 'received', 'cancelled'])->default('draft');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('qty', 15, 4);
            $table->decimal('unit_cost', 15, 4);
            $table->decimal('tax', 15, 4)->default(0);
            $table->decimal('subtotal', 15, 4);
            $table->timestamps();
        });

        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('amount', 15, 4);
            $table->string('method', 32)->default('cash');
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // -------------------------------------------------------------------
        // Sales / POS
        // -------------------------------------------------------------------
        Schema::create('pos_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('register_id')->constrained('pos_registers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->decimal('opening_cash', 15, 4)->default(0);
            $table->decimal('expected_cash', 15, 4)->default(0);
            $table->decimal('closing_cash', 15, 4)->nullable();
            $table->decimal('difference', 15, 4)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pos_session_id')->nullable()->constrained('pos_sessions')->nullOnDelete();
            $table->dateTime('date');
            $table->decimal('subtotal', 15, 4)->default(0);
            $table->decimal('tax', 15, 4)->default(0);
            $table->decimal('discount', 15, 4)->default(0);
            $table->decimal('total', 15, 4)->default(0);
            $table->decimal('paid', 15, 4)->default(0);
            $table->enum('status', ['draft', 'completed', 'cancelled', 'returned'])->default('completed');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('qty', 15, 4);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('tax', 15, 4)->default(0);
            $table->decimal('subtotal', 15, 4);
            $table->timestamps();
        });

        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('amount', 15, 4);
            $table->string('method', 32)->default('cash');
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // -------------------------------------------------------------------
        // Inventory operations
        // -------------------------------------------------------------------
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->enum('type', ['addition', 'subtraction'])->default('addition');
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained('stock_adjustments')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('qty', 15, 4);
            $table->timestamps();
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no')->unique();
            $table->foreignId('from_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('to_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->enum('status', ['draft', 'sent', 'received', 'cancelled'])->default('draft');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('qty', 15, 4);
            $table->timestamps();
        });

        // -------------------------------------------------------------------
        // Expenses
        // -------------------------------------------------------------------
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->decimal('amount', 15, 4);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // -------------------------------------------------------------------
        // Activity log
        // -------------------------------------------------------------------
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload')->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        $tables = [
            'activity_logs',
            'expenses',
            'expense_categories',
            'stock_transfer_items',
            'stock_transfers',
            'stock_adjustment_items',
            'stock_adjustments',
            'sale_payments',
            'sale_items',
            'sales',
            'pos_sessions',
            'pos_registers',
            'purchase_payments',
            'purchase_items',
            'purchases',
            'customers',
            'customer_groups',
            'suppliers',
            'product_branch_stock',
            'products',
            'tax_rates',
            'units',
            'brands',
            'categories',
            'settings',
            'translations',
            'languages',
            'user_branch',
        ];
        foreach ($tables as $t) {
            Schema::dropIfExists($t);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['default_branch_id']);
        });

        Schema::dropIfExists('branches');
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
