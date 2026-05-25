<?php

namespace Database\Seeders;

use App\Models\PosRegister;
use App\Models\PosSession;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the `pos_sessions` table with two sessions:
 *   - A closed session from yesterday (cashier user).
 *   - An open session for today (admin user) so the POS cart is immediately
 *     usable.
 */
class PosSessionSeeder extends Seeder
{
    public function run(): void
    {
        $register = PosRegister::query()->first();
        if (! $register) {
            return;
        }

        $cashier = User::query()->where('username', 'cashier')->first();
        $admin = User::query()->where('username', 'admin')->first();

        if ($cashier) {
            PosSession::updateOrCreate(
                ['register_id' => $register->id, 'user_id' => $cashier->id, 'opened_at' => now()->subDay()->startOfDay()->addHours(8)],
                [
                    'closed_at' => now()->subDay()->startOfDay()->addHours(17),
                    'opening_cash' => 100.0000,
                    'expected_cash' => 250.0000,
                    'closing_cash' => 248.0000,
                    'difference' => -2.0000,
                    'note' => 'Short $2 — minor discrepancy.',
                ]
            );
        }

        if ($admin) {
            PosSession::updateOrCreate(
                ['register_id' => $register->id, 'user_id' => $admin->id, 'opened_at' => now()->startOfDay()->addHours(8)],
                [
                    'closed_at' => null,
                    'opening_cash' => 100.0000,
                    'expected_cash' => 100.0000,
                    'closing_cash' => null,
                    'difference' => 0.0000,
                    'note' => 'Morning shift — currently open.',
                ]
            );
        }
    }
}
