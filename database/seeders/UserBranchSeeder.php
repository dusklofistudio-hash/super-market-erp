<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the `user_branch` pivot table to grant each baseline user access to
 * the appropriate branches:
 *   - admin    → all branches
 *   - manager  → HQ only
 *   - cashier  → HQ only
 */
class UserBranchSeeder extends Seeder
{
    public function run(): void
    {
        $allBranchIds = Branch::query()->pluck('id')->all();
        $hq = Branch::query()->where('code', 'HQ')->first();

        $accessMap = [
            'admin@example.com' => $allBranchIds,
            'manager@example.com' => $hq ? [$hq->id] : [],
            'cashier@example.com' => $hq ? [$hq->id] : [],
        ];

        foreach ($accessMap as $email => $branchIds) {
            $user = User::query()->where('email', $email)->first();
            if (! $user) {
                continue;
            }

            foreach ($branchIds as $branchId) {
                DB::table('user_branch')->updateOrInsert(
                    ['user_id' => $user->id, 'branch_id' => $branchId],
                    []
                );
            }
        }
    }
}
