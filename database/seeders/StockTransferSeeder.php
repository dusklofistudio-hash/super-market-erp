<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the `stock_transfers` table with two sample transfers:
 *   - TRF-0001: sent from HQ to BR01 (in transit)
 *   - TRF-0002: received at BR01 from HQ (completed)
 *
 * Line items are seeded by StockTransferItemSeeder.
 */
class StockTransferSeeder extends Seeder
{
    public function run(): void
    {
        $hq = Branch::query()->where('code', 'HQ')->value('id');
        $br01 = Branch::query()->where('code', 'BR01')->value('id');
        $admin = User::query()->where('username', 'admin')->value('id');

        StockTransfer::updateOrCreate(['ref_no' => 'TRF-0001'], [
            'from_branch_id' => $hq,
            'to_branch_id' => $br01,
            'user_id' => $admin,
            'date' => now()->subDay()->toDateString(),
            'status' => 'sent',
            'note' => 'Resupply BR01 with bestsellers.',
        ]);

        StockTransfer::updateOrCreate(['ref_no' => 'TRF-0002'], [
            'from_branch_id' => $hq,
            'to_branch_id' => $br01,
            'user_id' => $admin,
            'date' => now()->subDays(4)->toDateString(),
            'status' => 'received',
            'note' => 'Initial branch stocking — completed.',
        ]);
    }
}
