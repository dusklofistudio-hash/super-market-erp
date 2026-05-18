<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Lightweight wrapper around the activity_logs table. Modules call
 * `app(ActivityLogger::class)->log(...)` to record a structured event.
 * The payload column stores a JSON array of relevant attributes so the
 * admin viewer can render a "what changed" summary.
 */
class ActivityLogger
{
    public function __construct(protected Request $request) {}

    public function log(string $action, ?Model $subject = null, array $payload = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $this->request->user()?->id,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'ip_address' => $this->request->ip(),
            'user_agent' => substr((string) $this->request->userAgent(), 0, 1000),
            'payload' => $payload,
        ]);
    }
}
