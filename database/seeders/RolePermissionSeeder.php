<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds the `role_permission` pivot table. Mirrors the role catalogue from
 * RoleSeeder and the permission catalogue from PermissionSeeder.
 *
 * Super-admin and admin both receive every permission; cashier receives the
 * minimal POS-operator subset.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $super = Role::query()->where('slug', 'super-admin')->first();
        $admin = Role::query()->where('slug', 'admin')->first();
        $cashier = Role::query()->where('slug', 'cashier')->first();

        $allIds = Permission::query()->pluck('id')->all();

        if ($super) {
            $super->syncPermissions($allIds);
        }

        if ($admin) {
            $admin->syncPermissions($allIds);
        }

        if ($cashier) {
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
}
