<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeds the `failed_jobs` table.
 *
 * Populated automatically by Laravel's queue when a job throws an exception
 * after exhausting its retry budget. No business data to seed.
 */
class FailedJobSeeder extends Seeder
{
    public function run(): void
    {
        // No-op: queue worker writes failed-job rows at runtime.
    }
}
