<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $super = Role::updateOrCreate(['slug' => 'super-admin'], [
            'name' => 'Super admin',
            'description' => 'Full unrestricted access to every module.',
            'is_locked' => true,
        ]);

        $admin = Role::updateOrCreate(['slug' => 'admin'], [
            'name' => 'Administrator',
            'description' => 'Full operational access to administration screens.',
            'is_locked' => false,
        ]);

        $cashier = Role::updateOrCreate(['slug' => 'cashier'], [
            'name' => 'Cashier',
            'description' => 'POS / sales access only.',
            'is_locked' => false,
        ]);

        // Super-admin holds every permission for completeness, but the runtime
        // check in HasRolesAndPermissions short-circuits on $user->is_super_admin
        // anyway.
        $all = Permission::query()->pluck('id')->all();
        $super->syncPermissions($all);

        // Admin: every permission except sensitive ones (we keep them too).
        $admin->syncPermissions($all);

        // Cashier: POS operator — needs to use the register, see products /
        // customers, create new walk-in customers and view sales history.
        $cashier->syncPermissions(
            Permission::query()->whereIn('slug', [
                'products.view',
                'customers.view',
                'customers.create',
                'customers.edit',
                'pos.use',
                'sales.view',
                'sales.create',
                'pos_sessions.view',
                'pos_sessions.create',
                'pos_sessions.edit',
            ])->pluck('id')->all()
        );
    }
}
