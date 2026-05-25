<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds the `roles` table with the three baseline roles for the system:
 *   - super-admin → locked, gets every permission and bypasses checks
 *   - admin       → full operational access
 *   - cashier     → POS / sales only
 *
 * The role↔permission pivot is populated by RolePermissionSeeder so that
 * each seeder targets exactly one table.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(['slug' => 'super-admin'], [
            'name' => 'Super admin',
            'description' => 'Full unrestricted access to every module.',
            'is_locked' => true,
        ]);

        Role::updateOrCreate(['slug' => 'admin'], [
            'name' => 'Administrator',
            'description' => 'Full operational access to administration screens.',
            'is_locked' => false,
        ]);

        Role::updateOrCreate(['slug' => 'cashier'], [
            'name' => 'Cashier',
            'description' => 'POS / sales access only.',
            'is_locked' => false,
        ]);
    }
}
