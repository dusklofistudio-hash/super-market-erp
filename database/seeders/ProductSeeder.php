<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\TaxRate;
use App\Models\Unit;
use Illuminate\Database\Seeder;

/**
 * Seeds the `products` table with a small but representative set of SKUs
 * across the main categories. Each entry is wired up to a category, brand,
 * unit and tax rate so the resulting catalogue is immediately usable from
 * the POS and the reports screens.
 *
 * Per-branch on-hand stock is seeded separately by ProductBranchStockSeeder
 * so this seeder only touches the `products` table.
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $cat = Category::query()->pluck('id', 'slug');
        $brand = Brand::query()->pluck('id', 'slug');
        $unit = Unit::query()->pluck('id', 'short_name');
        $tax = TaxRate::query()->pluck('id', 'name');

        $defaultTax = $tax['VAT 10%'] ?? $tax['No tax'] ?? null;
        $zeroTax = $tax['No tax'] ?? $defaultTax;

        $products = [
            [
                'barcode' => '8850001000018',
                'sku' => 'BEV-COKE-330',
                'name_en' => 'Coca-Cola 330ml',
                'name_kh' => 'កូកាកូឡា ៣៣០មល',
                'category_id' => $cat['beverage'] ?? null,
                'brand_id' => $brand['coca-cola'] ?? null,
                'unit_id' => $unit['bot'] ?? null,
                'tax_rate_id' => $defaultTax,
                'cost_price' => 0.50,
                'sale_price' => 1.00,
                'alert_qty' => 24,
            ],
            [
                'barcode' => '8850001000025',
                'sku' => 'BEV-PEPSI-330',
                'name_en' => 'Pepsi 330ml',
                'name_kh' => 'ប៉េប្ស៊ី ៣៣០មល',
                'category_id' => $cat['beverage'] ?? null,
                'brand_id' => $brand['pepsi'] ?? null,
                'unit_id' => $unit['bot'] ?? null,
                'tax_rate_id' => $defaultTax,
                'cost_price' => 0.48,
                'sale_price' => 1.00,
                'alert_qty' => 24,
            ],
            [
                'barcode' => '8850001000032',
                'sku' => 'BEV-WATER-1L',
                'name_en' => 'Bottled water 1L',
                'name_kh' => 'ទឹកដប ១លីត្រ',
                'category_id' => $cat['beverage'] ?? null,
                'brand_id' => $brand['generic'] ?? null,
                'unit_id' => $unit['bot'] ?? null,
                'tax_rate_id' => $zeroTax,
                'cost_price' => 0.20,
                'sale_price' => 0.50,
                'alert_qty' => 48,
            ],
            [
                'barcode' => '8850001100015',
                'sku' => 'BAK-BREAD-WH',
                'name_en' => 'White bread loaf',
                'name_kh' => 'នំប៉័ងស',
                'category_id' => $cat['bakery'] ?? null,
                'brand_id' => $brand['generic'] ?? null,
                'unit_id' => $unit['pc'] ?? null,
                'tax_rate_id' => $zeroTax,
                'cost_price' => 1.00,
                'sale_price' => 1.80,
                'alert_qty' => 10,
            ],
            [
                'barcode' => '8850001200012',
                'sku' => 'DAI-MILK-1L',
                'name_en' => 'Fresh milk 1L',
                'name_kh' => 'ទឹកដោះគោស្រស់ ១លីត្រ',
                'category_id' => $cat['dairy'] ?? null,
                'brand_id' => $brand['nestle'] ?? null,
                'unit_id' => $unit['bot'] ?? null,
                'tax_rate_id' => $defaultTax,
                'cost_price' => 1.20,
                'sale_price' => 2.10,
                'alert_qty' => 12,
            ],
            [
                'barcode' => '8850001300019',
                'sku' => 'SNK-CHIPS-50',
                'name_en' => 'Potato chips 50g',
                'name_kh' => 'បន្លែដំឡូងបារាំង ៥០ក្រាម',
                'category_id' => $cat['snacks'] ?? null,
                'brand_id' => $brand['generic'] ?? null,
                'unit_id' => $unit['pack'] ?? null,
                'tax_rate_id' => $defaultTax,
                'cost_price' => 0.60,
                'sale_price' => 1.20,
                'alert_qty' => 36,
            ],
            [
                'barcode' => '8850001400016',
                'sku' => 'GRC-RICE-5KG',
                'name_en' => 'Jasmine rice 5kg',
                'name_kh' => 'អង្ករផ្កាមរ ៥គីឡូក្រាម',
                'category_id' => $cat['grocery'] ?? null,
                'brand_id' => $brand['generic'] ?? null,
                'unit_id' => $unit['kg'] ?? null,
                'tax_rate_id' => $zeroTax,
                'cost_price' => 5.50,
                'sale_price' => 8.50,
                'alert_qty' => 5,
            ],
            [
                'barcode' => '8850001500013',
                'sku' => 'PC-SHAMPOO-200',
                'name_en' => 'Shampoo 200ml',
                'name_kh' => 'សាប៊ូកក់សក់ ២០០មល',
                'category_id' => $cat['personal-care'] ?? null,
                'brand_id' => $brand['unilever'] ?? null,
                'unit_id' => $unit['bot'] ?? null,
                'tax_rate_id' => $defaultTax,
                'cost_price' => 2.50,
                'sale_price' => 4.20,
                'alert_qty' => 8,
            ],
            [
                'barcode' => '8850001600010',
                'sku' => 'HSE-DETER-1KG',
                'name_en' => 'Laundry detergent 1kg',
                'name_kh' => 'សាប៊ូបោកគក់ ១គីឡូក្រាម',
                'category_id' => $cat['household'] ?? null,
                'brand_id' => $brand['procter-gamble'] ?? null,
                'unit_id' => $unit['pack'] ?? null,
                'tax_rate_id' => $defaultTax,
                'cost_price' => 3.20,
                'sale_price' => 5.50,
                'alert_qty' => 6,
            ],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['sku' => $p['sku']], $p + ['is_active' => true]);
        }
    }
}
