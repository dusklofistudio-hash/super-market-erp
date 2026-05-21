<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

/**
 * Seeds the `expense_categories` table with common operating-expense
 * buckets used by the profit and expense reports.
 */
class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Rent', 'Monthly retail-space rent.'],
            ['Utilities', 'Electricity, water, internet.'],
            ['Payroll', 'Staff salaries and bonuses.'],
            ['Maintenance', 'Equipment and shop maintenance.'],
            ['Marketing', 'Advertising, promotions, signage.'],
            ['Logistics', 'Delivery fuel, courier fees.'],
            ['Miscellaneous', 'Anything not fitting another category.'],
        ];

        foreach ($categories as [$name, $description]) {
            ExpenseCategory::updateOrCreate(
                ['name' => $name],
                ['description' => $description, 'is_active' => true]
            );
        }
    }
}
