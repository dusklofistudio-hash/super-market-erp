<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeds the `cache_locks` table.
 *
 * Populated automatically by Laravel's atomic-lock implementation on top of
 * the database cache driver. No business data to seed.
 */
class CacheLockSeeder extends Seeder
{
    public function run(): void
    {
        // No-op: lock rows are created/released by the cache driver.
    }
}
