<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PosSessionCloseRequest;
use App\Http\Requests\Admin\PosSessionOpenRequest;
use App\Models\PosRegister;
use App\Models\PosSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class PosSessionController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('PosSessions/Index', [
            'registers' => PosRegister::query()
                ->where('is_active', true)
                ->with('branch:id,name_en,name_kh')
                ->get(['id', 'branch_id', 'name']),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = PosSession::query()
            ->select('pos_sessions.*')
            ->with(['register:id,branch_id,name', 'register.branch:id,name_en,name_kh', 'user:id,name']);

        return DataTables::eloquent($query)
            ->addColumn('register_name', fn (PosSession $s) => $s->register?->name)
            ->addColumn('branch_name', fn (PosSession $s) => $s->register?->branch?->localized('name'))
            ->addColumn('user_name', fn (PosSession $s) => $s->user?->name)
            ->editColumn('opened_at', fn (PosSession $s) => optional($s->opened_at)?->format('Y-m-d H:i'))
            ->editColumn('closed_at', fn (PosSession $s) => $s->closed_at?->format('Y-m-d H:i') ?? '—')
            ->addColumn('state', fn (PosSession $s) => $s->closed_at
                ? '<span class="badge bg-secondary">'.e(__('messages.statuses.closed')).'</span>'
                : '<span class="badge bg-success">'.e(__('messages.statuses.open')).'</span>')
            ->rawColumns(['state'])
            ->toJson();
    }

    public function open(PosSessionOpenRequest $request): RedirectResponse
    {
        $session = PosSession::create([
            'register_id' => $request->register_id,
            'user_id' => auth()->id(),
            'opened_at' => now(),
            'opening_cash' => $request->opening_cash,
            'expected_cash' => $request->opening_cash,
            'note' => $request->note,
        ]);

        sweetalert()->success(__('messages.pos.session_opened'));

        return redirect()->route('admin.pos.register', ['session' => $session->id]);
    }

    public function close(PosSessionCloseRequest $request, PosSession $pos_session): RedirectResponse
    {
        $diff = (float) $request->closing_cash - (float) $pos_session->expected_cash;
        $pos_session->update([
            'closed_at' => now(),
            'closing_cash' => $request->closing_cash,
            'difference' => $diff,
            'note' => $request->note ?? $pos_session->note,
        ]);

        sweetalert()->success(__('messages.pos.session_closed'));

        return redirect()->route('admin.pos-sessions.index');
    }
}
