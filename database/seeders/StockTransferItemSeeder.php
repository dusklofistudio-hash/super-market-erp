<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Database\Seeder;

/**
 * Seeds the `stock_transfer_items` table with line items matching the
 * transfers created by StockTransferSeeder.
 */
class StockTransferItemSeeder extends Seeder
{
    public function run(): void
    {
        $trf1 = StockTransfer::query()->where('ref_no', 'TRF-0001')->first();
        $trf2 = StockTransfer::query()->where('ref_no', 'TRF-0002')->first();
        $product = Product::query()->pluck('id', 'sku');

        if ($trf1) {
            StockTransferItem::updateOrCreate(
                ['stock_transfer_id' => $trf1->id, 'product_id' => $product['BEV-COKE-330'] ?? 0],
                ['qty' => 24]
            );
            StockTransferItem::updateOrCreate(
                ['stock_transfer_id' => $trf1->id, 'product_id' => $product['BEV-PEPSI-330'] ?? 0],
                ['qty' => 12]
            );
        }

        if ($trf2) {
            StockTransferItem::updateOrCreate(
                ['stock_transfer_id' => $trf2->id, 'product_id' => $product['GRC-RICE-5KG'] ?? 0],
                ['qty' => 4]
            );
        }
    }
}
