<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UnitRequest;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class UnitController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('Units/Index');
    }

    public function data(): JsonResponse
    {
        $query = Unit::query()->with('baseUnit:id,name_en')->select('units.*');

        return DataTables::eloquent($query)
            ->addColumn('base_unit_name', fn (Unit $u) => $u->baseUnit?->name_en)
            ->addColumn('status', fn (Unit $u) => $this->statusBadge((bool) $u->is_active))
            ->addColumn('action', fn (Unit $u) => $this->actionCell(
                route('admin.units.edit', $u),
                route('admin.units.destroy', $u),
            ))
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Units/Form', [
            'unit' => null,
            'base_units' => Unit::query()->whereNull('base_unit_id')->orderBy('name_en')->get(['id', 'name_en']),
        ]);
    }

    public function store(UnitRequest $request): RedirectResponse
    {
        Unit::create($request->validated());

        return redirect()->route('admin.units.index')
            ->with('success', __('messages.success.created', ['resource' => __('messages.menu.units')]));
    }

    public function edit(Unit $unit): Response
    {
        return Inertia::render('Units/Form', [
            'unit' => $unit,
            'base_units' => Unit::query()->whereNull('base_unit_id')->where('id', '!=', $unit->id)->orderBy('name_en')->get(['id', 'name_en']),
        ]);
    }

    public function update(UnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return redirect()->route('admin.units.index')
            ->with('success', __('messages.success.updated', ['resource' => __('messages.menu.units')]));
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();

        return back()->with('success', __('messages.success.deleted', ['resource' => __('messages.menu.units')]));
    }
}
