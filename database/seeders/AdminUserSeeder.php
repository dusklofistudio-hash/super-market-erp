<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $hq = Branch::query()->where('code', 'HQ')->first();

        $admin = User::updateOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Super Admin',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'phone' => '012 000 000',
            'default_branch_id' => $hq?->id,
            'is_active' => true,
            'is_super_admin' => true,
            'locale' => 'en',
        ]);

        $superRole = Role::query()->where('slug', 'super-admin')->first();
        if ($superRole) {
            $admin->syncRoles([$superRole->id]);
        }
        if ($hq) {
            $admin->syncBranches([$hq->id]);
        }
    }
}
