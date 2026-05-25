<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the `user_role` pivot table to assign each baseline user to the
 * appropriate role(s):
 *   - admin    → super-admin
 *   - manager  → admin
 *   - cashier  → cashier
 */
class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        $assignments = [
            'admin@example.com' => 'super-admin',
            'manager@example.com' => 'admin',
            'cashier@example.com' => 'cashier',
        ];

        foreach ($assignments as $email => $roleSlug) {
            $user = User::query()->where('email', $email)->first();
            $role = Role::query()->where('slug', $roleSlug)->first();
            if (! $user || ! $role) {
                continue;
            }

            DB::table('user_role')->updateOrInsert(
                ['user_id' => $user->id, 'role_id' => $role->id],
                []
            );
        }
    }
}
