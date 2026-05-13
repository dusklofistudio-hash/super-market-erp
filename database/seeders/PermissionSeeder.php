<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the full permission catalog for the system. The catalog drives every
 * `permission:<slug>` route middleware and the React `can()` helper. Add
 * additional modules here as the system grows.
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'branches' => 'Branches',
            'users' => 'Users',
            'roles' => 'Roles',
            'permissions' => 'Permissions',
            'languages' => 'Languages',
            'translations' => 'Translations',
            'settings' => 'Settings',
            'categories' => 'Categories',
            'brands' => 'Brands',
            'units' => 'Units',
            'tax_rates' => 'Tax rates',
            'products' => 'Products',
            'suppliers' => 'Suppliers',
            'customers' => 'Customers',
            'customer_groups' => 'Customer groups',
        ];

        // Most modules have full CRUD; "permissions" / "settings" / "translations"
        // only need read / edit.
        $actionsByModule = collect($modules)->mapWithKeys(fn ($_l, $m) => [$m => ['view', 'create', 'edit', 'delete']])->all();
        $actionsByModule['permissions'] = ['view'];
        $actionsByModule['settings'] = ['view', 'edit'];
        $actionsByModule['translations'] = ['view', 'edit'];

        foreach ($modules as $module => $label) {
            foreach ($actionsByModule[$module] as $action) {
                $slug = "$module.$action";
                Permission::query()->updateOrCreate(['slug' => $slug], [
                    'module' => $module,
                    'name' => "$label · ".Str::ucfirst($action),
                    'description' => "Allows $action on $label",
                ]);
            }
        }
    }
}
