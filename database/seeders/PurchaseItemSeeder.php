<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Database\Seeder;

/**
 * Seeds the `purchase_items` table with line items tied to PUR-0001 and
 * PUR-0002. Values are pre-computed and consistent with the purchase
 * header totals in PurchaseSeeder.
 */
class PurchaseItemSeeder extends Seeder
{
    public function run(): void
    {
        $pur1 = Purchase::query()->where('ref_no', 'PUR-0001')->first();
        $pur2 = Purchase::query()->where('ref_no', 'PUR-0002')->first();
        $product = Product::query()->pluck('id', 'sku');

        if ($pur1) {
            // 60 × Coke @ 0.50 = 30, tax 3.00
            PurchaseItem::updateOrCreate(
                ['purchase_id' => $pur1->id, 'product_id' => $product['BEV-COKE-330'] ?? 0],
                ['qty' => 60, 'unit_cost' => 0.5000, 'tax' => 3.0000, 'subtotal' => 30.0000]
            );
            // 60 × Pepsi @ 0.50 = 30, tax 3.00
            PurchaseItem::updateOrCreate(
                ['purchase_id' => $pur1->id, 'product_id' => $product['BEV-PEPSI-330'] ?? 0],
                ['qty' => 60, 'unit_cost' => 0.5000, 'tax' => 3.0000, 'subtotal' => 30.0000]
            );
        }

        if ($pur2) {
            // 10 × Bread @ 1.00 = 10, no tax
            PurchaseItem::updateOrCreate(
                ['purchase_id' => $pur2->id, 'product_id' => $product['BAK-BREAD-WH'] ?? 0],
                ['qty' => 10, 'unit_cost' => 1.0000, 'tax' => 0.0000, 'subtotal' => 10.0000]
            );
            // 12 × Milk @ 1.25 = 15, no tax
            PurchaseItem::updateOrCreate(
                ['purchase_id' => $pur2->id, 'product_id' => $product['DAI-MILK-1L'] ?? 0],
                ['qty' => 12, 'unit_cost' => 1.2500, 'tax' => 0.0000, 'subtotal' => 15.0000]
            );
        }
    }
}
