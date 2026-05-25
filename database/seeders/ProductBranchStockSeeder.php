<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBranchStock;
use Illuminate\Database\Seeder;

/**
 * Seeds the `product_branch_stock` table with realistic opening on-hand
 * quantities for each (branch, product) pair so the dashboard, products
 * page and reports all have data to render immediately.
 *
 * HQ holds the bulk of the stock; BR01 carries a smaller fraction. Both
 * branches have non-zero rows for every product so the "stock by branch"
 * report has data to render.
 */
class ProductBranchStockSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::query()->get()->keyBy('code');
        $hq = $branches->get('HQ');
        $br01 = $branches->get('BR01');

        if (! $hq || ! $br01) {
            return;
        }

        // Per-product opening stock (HQ qty, BR01 qty, reorder qty).
        $stocks = [
            'BEV-COKE-330' => [120, 36, 24],
            'BEV-PEPSI-330' => [96, 24, 24],
            'BEV-WATER-1L' => [240, 60, 48],
            'BAK-BREAD-WH' => [30, 10, 10],
            'DAI-MILK-1L' => [48, 12, 12],
            'SNK-CHIPS-50' => [80, 20, 36],
            'GRC-RICE-5KG' => [25, 8, 5],
            'PC-SHAMPOO-200' => [40, 10, 8],
            'HSE-DETER-1KG' => [30, 8, 6],
        ];

        foreach ($stocks as $sku => [$hqQty, $br01Qty, $reorder]) {
            $product = Product::query()->where('sku', $sku)->first();
            if (! $product) {
                continue;
            }

            ProductBranchStock::updateOrCreate(
                ['product_id' => $product->id, 'branch_id' => $hq->id],
                ['qty' => $hqQty, 'reorder_qty' => $reorder]
            );

            ProductBranchStock::updateOrCreate(
                ['product_id' => $product->id, 'branch_id' => $br01->id],
                ['qty' => $br01Qty, 'reorder_qty' => $reorder]
            );
        }
    }
}
