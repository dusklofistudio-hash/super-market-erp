<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\PosSession;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the `sales` table with two sample transactions:
 *   - SAL-0001: completed walk-in sale at HQ (yesterday — closed session)
 *   - SAL-0002: completed sale to a named customer at HQ (today — open
 *     session)
 *
 * Line items and payments are seeded by SaleItemSeeder and
 * SalePaymentSeeder respectively.
 */
class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $hq = Branch::query()->where('code', 'HQ')->value('id');
        $admin = User::query()->where('username', 'admin')->value('id');
        $cashier = User::query()->where('username', 'cashier')->value('id');
        $walkin = Customer::query()->where('code', 'CUS-0000')->value('id');
        $sokDara = Customer::query()->where('code', 'CUS-0001')->value('id');

        $closedSession = PosSession::query()->whereNotNull('closed_at')->value('id');
        $openSession = PosSession::query()->whereNull('closed_at')->value('id');

        Sale::updateOrCreate(['ref_no' => 'SAL-0001'], [
            'branch_id' => $hq,
            'customer_id' => $walkin,
            'user_id' => $cashier,
            'pos_session_id' => $closedSession,
            'date' => now()->subDay()->setHour(10)->setMinute(15),
            'subtotal' => 3.5000,
            'tax' => 0.2000,
            'discount' => 0.0000,
            'total' => 3.7000,
            'paid' => 3.7000,
            'status' => 'completed',
            'note' => null,
        ]);

        Sale::updateOrCreate(['ref_no' => 'SAL-0002'], [
            'branch_id' => $hq,
            'customer_id' => $sokDara,
            'user_id' => $admin,
            'pos_session_id' => $openSession,
            'date' => now()->setHour(9)->setMinute(30),
            'subtotal' => 12.5000,
            'tax' => 0.8500,
            'discount' => 0.0000,
            'total' => 13.3500,
            'paid' => 13.3500,
            'status' => 'completed',
            'note' => 'VIP customer purchase.',
        ]);
    }
}
