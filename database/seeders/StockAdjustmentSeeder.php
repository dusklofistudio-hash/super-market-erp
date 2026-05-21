<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the `stock_adjustments` table with two sample entries:
 *   - ADJ-0001: addition (damaged stock found in warehouse — counted back in)
 *   - ADJ-0002: subtraction (expired goods removed from shelf)
 *
 * Line items are seeded by StockAdjustmentItemSeeder.
 */
class StockAdjustmentSeeder extends Seeder
{
    public function run(): void
    {
        $hq = Branch::query()->where('code', 'HQ')->value('id');
        $admin = User::query()->where('username', 'admin')->value('id');

        StockAdjustment::updateOrCreate(['ref_no' => 'ADJ-0001'], [
            'branch_id' => $hq,
            'user_id' => $admin,
            'date' => now()->subDays(2)->toDateString(),
            'type' => 'addition',
            'reason' => 'Warehouse recount — 5 cases of water found in back storage.',
        ]);

        StockAdjustment::updateOrCreate(['ref_no' => 'ADJ-0002'], [
            'branch_id' => $hq,
            'user_id' => $admin,
            'date' => now()->subDay()->toDateString(),
            'type' => 'subtraction',
            'reason' => 'Expired bread removed from display shelf.',
        ]);
    }
}
