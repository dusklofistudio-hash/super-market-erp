<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeds the `jobs` table.
 *
 * Populated automatically by Laravel's database queue driver when jobs are
 * dispatched. No business data to seed.
 */
class JobSeeder extends Seeder
{
    public function run(): void
    {
        // No-op: queue worker manages this table at runtime.
    }
}
