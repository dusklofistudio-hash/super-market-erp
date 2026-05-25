<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Orchestrates all per-table seeders in foreign-key-safe order.
 *
 * Each table in the consolidated migration
 *   database/migrations/2026_05_13_000000_create_super_market_pos_all_tables.php
 * has its own dedicated seeder class — none of the seeders below touch
 * more than one table.
 *
 * Framework-runtime tables (sessions, cache, jobs, etc.) keep a no-op
 * stub seeder so the 1-to-1 mapping holds even though no business data
 * is written into them.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // --- Framework-runtime tables (stubs / no-ops) ---
            PasswordResetTokenSeeder::class,
            SessionSeeder::class,
            CacheSeeder::class,
            CacheLockSeeder::class,
            JobSeeder::class,
            JobBatchSeeder::class,
            FailedJobSeeder::class,

            // --- Localization (no FK deps) ---
            LanguageSeeder::class,
            TranslationSeeder::class,

            // --- Settings ---
            SettingSeeder::class,

            // --- RBAC catalogue (roles + permissions) ---
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,

            // --- Branches before users (FK on users.default_branch_id) ---
            BranchSeeder::class,

            // --- Users + pivots ---
            UserSeeder::class,
            UserRoleSeeder::class,
            UserBranchSeeder::class,

            // --- Catalog: taxonomies first, then products, then stock ---
            CategorySeeder::class,
            BrandSeeder::class,
            UnitSeeder::class,
            TaxRateSeeder::class,
            ProductSeeder::class,
            ProductBranchStockSeeder::class,

            // --- Parties ---
            SupplierSeeder::class,
            CustomerGroupSeeder::class,
            CustomerSeeder::class,

            // --- Purchasing ---
            PurchaseSeeder::class,
            PurchaseItemSeeder::class,
            PurchasePaymentSeeder::class,

            // --- POS infra ---
            PosRegisterSeeder::class,
            PosSessionSeeder::class,

            // --- Sales (depends on customers, pos_sessions) ---
            SaleSeeder::class,
            SaleItemSeeder::class,
            SalePaymentSeeder::class,

            // --- Stock operations ---
            StockAdjustmentSeeder::class,
            StockAdjustmentItemSeeder::class,
            StockTransferSeeder::class,
            StockTransferItemSeeder::class,

            // --- Expenses ---
            ExpenseCategorySeeder::class,
            ExpenseSeeder::class,

            // --- Activity audit log ---
            ActivityLogSeeder::class,
        ]);
    }
}
