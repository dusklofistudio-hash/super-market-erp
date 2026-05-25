<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the `users` table with the baseline accounts needed to operate the
 * system:
 *
 *  - `admin`     — Super admin, full access (matches the AdminUserSeeder
 *                  that shipped with Phase 1).
 *  - `manager`   — Branch manager at HQ.
 *  - `cashier`   — POS operator at HQ.
 *
 * Roles and branches are wired up by UserRoleSeeder and UserBranchSeeder
 * respectively so that each seeder still targets exactly one table.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $hq = Branch::query()->where('code', 'HQ')->first();

        User::updateOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Super Admin',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'phone' => '012 000 000',
            'default_branch_id' => $hq?->id,
            'is_active' => true,
            'is_super_admin' => true,
            'locale' => 'en',
        ]);

        User::updateOrCreate(['email' => 'manager@example.com'], [
            'name' => 'Branch Manager',
            'username' => 'manager',
            'password' => Hash::make('password'),
            'phone' => '012 111 111',
            'default_branch_id' => $hq?->id,
            'is_active' => true,
            'is_super_admin' => false,
            'locale' => 'en',
        ]);

        User::updateOrCreate(['email' => 'cashier@example.com'], [
            'name' => 'Cashier',
            'username' => 'cashier',
            'password' => Hash::make('password'),
            'phone' => '012 222 222',
            'default_branch_id' => $hq?->id,
            'is_active' => true,
            'is_super_admin' => false,
            'locale' => 'kh',
        ]);
    }
}
