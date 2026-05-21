<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\PosRegister;
use Illuminate\Database\Seeder;

/**
 * Seeds the `pos_registers` table. Each branch gets one register so the
 * POS screen can be opened immediately after seeding.
 */
class PosRegisterSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::query()->get();

        foreach ($branches as $index => $branch) {
            PosRegister::updateOrCreate(
                ['branch_id' => $branch->id, 'name' => 'Register '.($index + 1)],
                ['is_active' => true]
            );
        }
    }
}
