<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Seeder;

/**
 * Seeds the `sale_items` table with line items matching the two sales
 * created by SaleSeeder. Values are pre-computed and consistent with
 * the sale header totals.
 */
class SaleItemSeeder extends Seeder
{
    public function run(): void
    {
        $sal1 = Sale::query()->where('ref_no', 'SAL-0001')->first();
        $sal2 = Sale::query()->where('ref_no', 'SAL-0002')->first();
        $product = Product::query()->pluck('id', 'sku');

        if ($sal1) {
            // 2 × Coke @ 1.00 = 2.00, tax 0.20
            SaleItem::updateOrCreate(
                ['sale_id' => $sal1->id, 'product_id' => $product['BEV-COKE-330'] ?? 0],
                ['qty' => 2, 'unit_price' => 1.0000, 'tax' => 0.2000, 'subtotal' => 2.0000]
            );
            // 1 × Bread @ 1.50 = 1.50, no tax
            SaleItem::updateOrCreate(
                ['sale_id' => $sal1->id, 'product_id' => $product['BAK-BREAD-WH'] ?? 0],
                ['qty' => 1, 'unit_price' => 1.5000, 'tax' => 0.0000, 'subtotal' => 1.5000]
            );
        }

        if ($sal2) {
            // 5 × Milk @ 2.10 = 10.50, tax 0.70
            SaleItem::updateOrCreate(
                ['sale_id' => $sal2->id, 'product_id' => $product['DAI-MILK-1L'] ?? 0],
                ['qty' => 5, 'unit_price' => 2.1000, 'tax' => 0.7000, 'subtotal' => 10.5000]
            );
            // 1 × Chips @ 1.20 = 1.20, tax 0.12 (actually ~0.15, rounding for demo)
            SaleItem::updateOrCreate(
                ['sale_id' => $sal2->id, 'product_id' => $product['SNK-CHIPS-50'] ?? 0],
                ['qty' => 1, 'unit_price' => 1.2000, 'tax' => 0.1500, 'subtotal' => 1.2000]
            );
            // 1 × Water @ 0.50 = 0.50, no tax
            SaleItem::updateOrCreate(
                ['sale_id' => $sal2->id, 'product_id' => $product['BEV-WATER-1L'] ?? 0],
                ['qty' => 1, 'unit_price' => 0.5000, 'tax' => 0.0000, 'subtotal' => 0.5000]
            );
        }
    }
}
