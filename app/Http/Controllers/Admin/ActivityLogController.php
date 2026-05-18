<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('ActivityLogs/Index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = ActivityLog::query()
            ->select('activity_logs.*')
            ->with('user:id,name,username');

        return DataTables::eloquent($query)
            ->editColumn('created_at', fn (ActivityLog $a) => optional($a->created_at)?->format('Y-m-d H:i:s'))
            ->addColumn('user_name', fn (ActivityLog $a) => $a->user?->name ?? '—')
            ->addColumn('subject', fn (ActivityLog $a) => $a->subject_type
                ? class_basename($a->subject_type).'#'.$a->subject_id
                : '—')
            ->editColumn('payload', fn (ActivityLog $a) => $a->payload
                ? '<code class="small">'.e(json_encode($a->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)).'</code>'
                : '—')
            ->rawColumns(['payload'])
            ->toJson();
    }
}
