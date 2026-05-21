<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the `activity_logs` table with a few representative entries so the
 * Activity Logs viewer has data to render after a fresh install. Real
 * entries are written at runtime by App\Services\ActivityLogger.
 */
class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('username', 'admin')->first();
        if (! $admin) {
            return;
        }

        $entries = [
            [
                'action' => 'auth.login',
                'subject' => $admin,
                'payload' => ['via' => 'username'],
                'created_at' => now()->subDays(3),
            ],
            [
                'action' => 'purchases.create',
                'subject' => Purchase::query()->where('ref_no', 'PUR-0001')->first(),
                'payload' => ['ref_no' => 'PUR-0001', 'total' => 66.0],
                'created_at' => now()->subDays(3)->addMinutes(15),
            ],
            [
                'action' => 'sales.create',
                'subject' => Sale::query()->where('ref_no', 'SAL-0001')->first(),
                'payload' => ['ref_no' => 'SAL-0001', 'total' => 3.7],
                'created_at' => now()->subDay()->setHour(10)->setMinute(15),
            ],
            [
                'action' => 'stock_adjustments.create',
                'subject' => StockAdjustment::query()->where('ref_no', 'ADJ-0001')->first(),
                'payload' => ['ref_no' => 'ADJ-0001', 'type' => 'addition'],
                'created_at' => now()->subDays(2),
            ],
            [
                'action' => 'stock_transfers.create',
                'subject' => StockTransfer::query()->where('ref_no', 'TRF-0001')->first(),
                'payload' => ['ref_no' => 'TRF-0001', 'status' => 'sent'],
                'created_at' => now()->subDay(),
            ],
        ];

        foreach ($entries as $entry) {
            $subject = $entry['subject'] ?? null;
            $created = $entry['created_at'];

            ActivityLog::query()->updateOrCreate(
                [
                    'user_id' => $admin->id,
                    'action' => $entry['action'],
                    'subject_type' => $subject ? get_class($subject) : null,
                    'subject_id' => $subject?->getKey(),
                ],
                [
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'DatabaseSeeder',
                    'payload' => $entry['payload'],
                    'created_at' => $created,
                    'updated_at' => $created,
                ]
            );
        }
    }
}
