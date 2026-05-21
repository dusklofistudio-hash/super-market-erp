<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the `brands` table with a small set of well-known FMCG brands so the
 * product catalogue has realistic data out of the box.
 */
class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['Coca-Cola', 'Beverages by The Coca-Cola Company'],
            ['Pepsi', 'Beverages by PepsiCo'],
            ['Nestlé', 'Foods and beverages by Nestlé S.A.'],
            ['Unilever', 'Household goods by Unilever plc'],
            ['Procter & Gamble', 'Consumer goods by P&G'],
            ['Generic', 'No-name / private label products'],
        ];

        foreach ($brands as [$name, $description]) {
            Brand::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description, 'is_active' => true]
            );
        }
    }
}
