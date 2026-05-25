<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

/**
 * Seeds the `units` table with common units of measure used by the
 * supermarket. Conversion factor stays at 1 (base unit) for the
 * primary list; downstream conversions can be added by editing the
 * unit row in the admin UI.
 */
class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['Piece', 'ដុំ', 'pc'],
            ['Kilogram', 'គីឡូក្រាម', 'kg'],
            ['Gram', 'ក្រាម', 'g'],
            ['Liter', 'លីត្រ', 'L'],
            ['Milliliter', 'មីលីលីត្រ', 'ml'],
            ['Box', 'ប្រអប់', 'box'],
            ['Pack', 'កញ្ចប់', 'pack'],
            ['Bottle', 'ដប', 'bot'],
        ];

        foreach ($units as [$en, $kh, $short]) {
            Unit::updateOrCreate(
                ['short_name' => $short],
                ['name_en' => $en, 'name_kh' => $kh, 'conversion_factor' => 1, 'is_active' => true]
            );
        }
    }
}
