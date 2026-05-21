<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SalePayment;
use Illuminate\Database\Seeder;

/**
 * Seeds the `sale_payments` table. Both sample sales are fully paid so each
 * has exactly one payment row.
 */
class SalePaymentSeeder extends Seeder
{
    public function run(): void
    {
        $sales = Sale::query()->whereIn('ref_no', ['SAL-0001', 'SAL-0002'])->get();

        foreach ($sales as $sale) {
            SalePayment::updateOrCreate(
                ['sale_id' => $sale->id, 'date' => $sale->date->toDateString()],
                [
                    'amount' => $sale->total,
                    'method' => 'cash',
                    'reference' => null,
                    'note' => null,
                ]
            );
        }
    }
}
