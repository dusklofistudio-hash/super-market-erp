<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CustomerGroup;
use App\Models\TaxRate;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $cats = [
            ['Grocery', 'គ្រឿងបរិក្ខារ'],
            ['Beverage', 'ភេសជ្ជៈ'],
            ['Bakery', 'នំប៉័ង'],
            ['Dairy', 'ផលិតផលទឹកដោះ'],
            ['Snacks', 'ស្រែ'],
        ];
        foreach ($cats as [$en, $kh]) {
            Category::updateOrCreate(
                ['slug' => Str::slug($en)],
                ['name_en' => $en, 'name_kh' => $kh, 'is_active' => true]
            );
        }

        $brands = ['Coca-Cola', 'Pepsi', 'Nestlé', 'Unilever', 'Generic'];
        foreach ($brands as $b) {
            Brand::updateOrCreate(
                ['slug' => Str::slug($b)],
                ['name' => $b, 'is_active' => true]
            );
        }

        $units = [
            ['Piece', 'ដុំ', 'pc'],
            ['Kilogram', 'គីឡូក្រាម', 'kg'],
            ['Liter', 'លីត្រ', 'L'],
            ['Box', 'ប្រអប់', 'box'],
        ];
        foreach ($units as [$en, $kh, $short]) {
            Unit::updateOrCreate(
                ['short_name' => $short],
                ['name_en' => $en, 'name_kh' => $kh, 'conversion_factor' => 1, 'is_active' => true]
            );
        }

        TaxRate::updateOrCreate(
            ['name' => 'VAT 10%'],
            ['rate' => 10, 'is_inclusive' => false, 'is_active' => true]
        );

        CustomerGroup::updateOrCreate(
            ['name' => 'Default'],
            ['discount_percent' => 0, 'is_active' => true]
        );
        CustomerGroup::updateOrCreate(
            ['name' => 'VIP'],
            ['discount_percent' => 5, 'is_active' => true]
        );
    }
}
