<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

/**
 * Seeds the `suppliers` table with a small set of trade partners so the
 * purchases module has data immediately after `migrate:fresh --seed`.
 */
class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'code' => 'SUP-0001',
                'name' => 'ABC Trading Co., Ltd.',
                'phone' => '023 100 200',
                'email' => 'sales@abc-trading.example',
                'company' => 'ABC Trading Co., Ltd.',
                'address' => '#12, St. 271, Phnom Penh',
                'opening_balance' => 0,
            ],
            [
                'code' => 'SUP-0002',
                'name' => 'KH Distribution',
                'phone' => '023 300 400',
                'email' => 'orders@kh-distribution.example',
                'company' => 'KH Distribution Pte. Ltd.',
                'address' => '#5, St. 110, Phnom Penh',
                'opening_balance' => 0,
            ],
            [
                'code' => 'SUP-0003',
                'name' => 'Mekong Foods',
                'phone' => '023 500 600',
                'email' => 'hello@mekong-foods.example',
                'company' => 'Mekong Foods Co., Ltd.',
                'address' => '#78, St. 271, Phnom Penh',
                'opening_balance' => 0,
            ],
        ];

        foreach ($suppliers as $row) {
            Supplier::updateOrCreate(
                ['code' => $row['code']],
                $row + ['is_active' => true]
            );
        }
    }
}
