<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the `purchases` table with two sample purchase orders:
 *   - PUR-0001: received purchase at HQ (beverages from ABC Trading)
 *   - PUR-0002: draft purchase at BR01 (bakery from KH Distribution)
 *
 * Line items and payments are seeded by PurchaseItemSeeder and
 * PurchasePaymentSeeder respectively to keep one seeder per table.
 *
 * NOTE: stock impact is pre-computed in ProductBranchStockSeeder. These
 * seeders do NOT call StockService so that seeding is always additive and
 * idempotent.
 */
class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $hq = Branch::query()->where('code', 'HQ')->value('id');
        $br01 = Branch::query()->where('code', 'BR01')->value('id');
        $sup1 = Supplier::query()->where('code', 'SUP-0001')->value('id');
        $sup2 = Supplier::query()->where('code', 'SUP-0002')->value('id');
        $admin = User::query()->where('username', 'admin')->value('id');

        Purchase::updateOrCreate(['ref_no' => 'PUR-0001'], [
            'branch_id' => $hq,
            'supplier_id' => $sup1,
            'user_id' => $admin,
            'date' => now()->subDays(3)->toDateString(),
            'subtotal' => 60.0000,
            'tax' => 6.0000,
            'discount' => 0.0000,
            'total' => 66.0000,
            'paid' => 66.0000,
            'status' => 'received',
            'note' => 'Weekly beverage restock for HQ.',
        ]);

        Purchase::updateOrCreate(['ref_no' => 'PUR-0002'], [
            'branch_id' => $br01,
            'supplier_id' => $sup2,
            'user_id' => $admin,
            'date' => now()->subDay()->toDateString(),
            'subtotal' => 25.0000,
            'tax' => 0.0000,
            'discount' => 0.0000,
            'total' => 25.0000,
            'paid' => 0.0000,
            'status' => 'draft',
            'note' => 'Bakery and dairy order — pending approval.',
        ]);
    }
}
