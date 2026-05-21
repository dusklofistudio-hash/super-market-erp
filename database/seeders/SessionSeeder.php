<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeds the `sessions` table.
 *
 * Populated automatically by Laravel's database session driver on every
 * request. No business data to seed; this seeder exists so the project keeps
 * a 1-to-1 mapping of table to seeder.
 */
class SessionSeeder extends Seeder
{
    public function run(): void
    {
        // No-op: session rows are created/garbage-collected by Laravel.
    }
}
