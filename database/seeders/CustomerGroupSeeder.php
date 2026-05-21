<?php

namespace Database\Seeders;

use App\Models\CustomerGroup;
use Illuminate\Database\Seeder;

/**
 * Seeds the `customer_groups` table with the default grouping used to apply
 * automatic discounts at the POS.
 */
class CustomerGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            ['Default', 0],
            ['VIP', 5],
            ['Wholesale', 10],
        ];

        foreach ($groups as [$name, $discount]) {
            CustomerGroup::updateOrCreate(
                ['name' => $name],
                ['discount_percent' => $discount, 'is_active' => true]
            );
        }
    }
}
