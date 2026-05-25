<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use Illuminate\Database\Seeder;

/**
 * Seeds the `stock_adjustment_items` table with line items matching the
 * adjustments created by StockAdjustmentSeeder.
 */
class StockAdjustmentItemSeeder extends Seeder
{
    public function run(): void
    {
        $adj1 = StockAdjustment::query()->where('ref_no', 'ADJ-0001')->first();
        $adj2 = StockAdjustment::query()->where('ref_no', 'ADJ-0002')->first();
        $product = Product::query()->pluck('id', 'sku');

        if ($adj1) {
            StockAdjustmentItem::updateOrCreate(
                ['stock_adjustment_id' => $adj1->id, 'product_id' => $product['BEV-WATER-1L'] ?? 0],
                ['qty' => 60]
            );
        }

        if ($adj2) {
            StockAdjustmentItem::updateOrCreate(
                ['stock_adjustment_id' => $adj2->id, 'product_id' => $product['BAK-BREAD-WH'] ?? 0],
                ['qty' => 3]
            );
        }
    }
}
