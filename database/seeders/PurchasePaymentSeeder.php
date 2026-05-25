<?php

namespace Database\Seeders;

use App\Models\Purchase;
use App\Models\PurchasePayment;
use Illuminate\Database\Seeder;

/**
 * Seeds the `purchase_payments` table. Only the fully-paid purchase
 * (PUR-0001 / received) has a payment row — the draft (PUR-0002) has no
 * payment yet.
 */
class PurchasePaymentSeeder extends Seeder
{
    public function run(): void
    {
        $pur1 = Purchase::query()->where('ref_no', 'PUR-0001')->first();

        if ($pur1) {
            PurchasePayment::updateOrCreate(
                ['purchase_id' => $pur1->id, 'date' => $pur1->date->toDateString()],
                [
                    'amount' => 66.0000,
                    'method' => 'cash',
                    'reference' => null,
                    'note' => 'Full payment on receipt.',
                ]
            );
        }
    }
}
