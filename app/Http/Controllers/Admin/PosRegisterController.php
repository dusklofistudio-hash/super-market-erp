<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PosRegisterRequest;
use App\Models\Branch;
use App\Models\PosRegister;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class PosRegisterController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('PosRegisters/Index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = PosRegister::query()->select('pos_registers.*')->with('branch:id,name_en,name_kh');

        return DataTables::eloquent($query)
            ->addColumn('branch_name', fn (PosRegister $r) => $r->branch?->localized('name'))
            ->addColumn('status', fn (PosRegister $r) => $this->statusBadge((bool) $r->is_active))
            ->addColumn('action', fn (PosRegister $r) => $this->actionCell(
                route('admin.pos-registers.edit', $r),
                route('admin.pos-registers.destroy', $r),
            ))
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('PosRegisters/Form', $this->formData(null));
    }

    public function store(PosRegisterRequest $request): RedirectResponse
    {
        PosRegister::create($request->validated());
        sweetalert()->success(__('messages.success.created', ['resource' => __('messages.menu.pos_registers')]));

        return redirect()->route('admin.pos-registers.index');
    }

    public function edit(PosRegister $pos_register): Response
    {
        return Inertia::render('PosRegisters/Form', $this->formData($pos_register));
    }

    public function update(PosRegisterRequest $request, PosRegister $pos_register): RedirectResponse
    {
        $pos_register->update($request->validated());
        sweetalert()->success(__('messages.success.updated', ['resource' => __('messages.menu.pos_registers')]));

        return redirect()->route('admin.pos-registers.index');
    }

    public function destroy(PosRegister $pos_register): RedirectResponse
    {
        $pos_register->delete();
        sweetalert()->success(__('messages.success.deleted', ['resource' => __('messages.menu.pos_registers')]));

        return back();
    }

    private function formData(?PosRegister $register): array
    {
        return [
            'register' => $register,
            'branches' => Branch::query()->active()->orderBy('code')->get(['id', 'code', 'name_en', 'name_kh']),
        ];
    }
}
