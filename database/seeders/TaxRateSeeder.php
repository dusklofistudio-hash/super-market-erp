<?php

namespace Database\Seeders;

use App\Models\TaxRate;
use Illuminate\Database\Seeder;

/**
 * Seeds the `tax_rates` table with the most common Cambodian VAT rates plus
 * a zero-rate entry for tax-exempt items.
 */
class TaxRateSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            ['name' => 'No tax', 'rate' => 0, 'is_inclusive' => false],
            ['name' => 'VAT 10%', 'rate' => 10, 'is_inclusive' => false],
            ['name' => 'VAT 10% (inclusive)', 'rate' => 10, 'is_inclusive' => true],
        ];

        foreach ($rates as $row) {
            TaxRate::updateOrCreate(
                ['name' => $row['name']],
                $row + ['is_active' => true]
            );
        }
    }
}
