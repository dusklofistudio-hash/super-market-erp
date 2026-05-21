<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeds the `cache` table.
 *
 * Populated automatically by Laravel's database cache driver. No business
 * data to seed; this seeder exists so the project keeps a 1-to-1 mapping of
 * table to seeder.
 */
class CacheSeeder extends Seeder
{
    public function run(): void
    {
        // No-op: cache rows are created/expired by the cache driver.
    }
}
