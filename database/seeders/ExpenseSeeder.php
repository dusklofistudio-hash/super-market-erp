<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the `expenses` table with a small set of recent expense entries
 * so the profit and expense-by-category reports have data to render.
 */
class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $hq = Branch::query()->where('code', 'HQ')->value('id');
        $admin = User::query()->where('username', 'admin')->value('id');
        $rent = ExpenseCategory::query()->where('name', 'Rent')->value('id');
        $util = ExpenseCategory::query()->where('name', 'Utilities')->value('id');
        $payroll = ExpenseCategory::query()->where('name', 'Payroll')->value('id');

        $rows = [
            [
                'ref_no' => 'EXP-0001',
                'category_id' => $rent,
                'date' => now()->startOfMonth()->toDateString(),
                'amount' => 800.0000,
                'note' => 'Monthly retail rent (HQ).',
            ],
            [
                'ref_no' => 'EXP-0002',
                'category_id' => $util,
                'date' => now()->subDays(5)->toDateString(),
                'amount' => 120.5000,
                'note' => 'Electricity bill.',
            ],
            [
                'ref_no' => 'EXP-0003',
                'category_id' => $payroll,
                'date' => now()->subDay()->toDateString(),
                'amount' => 350.0000,
                'note' => 'Cashier salary advance.',
            ],
        ];

        foreach ($rows as $row) {
            Expense::updateOrCreate(['ref_no' => $row['ref_no']], $row + [
                'branch_id' => $hq,
                'user_id' => $admin,
            ]);
        }
    }
}
