<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeds the `password_reset_tokens` table.
 *
 * This table is managed at runtime by the Laravel password broker — rows are
 * inserted whenever a user clicks "Forgot password" and deleted when the
 * reset link is consumed. There is no business data to seed; the seeder is
 * kept in place so the project keeps a 1-to-1 mapping of table to seeder.
 */
class PasswordResetTokenSeeder extends Seeder
{
    public function run(): void
    {
        // No-op: rows are created/deleted automatically by the password
        // broker (see App\Http\Controllers\Auth\PasswordResetLinkController).
    }
}
