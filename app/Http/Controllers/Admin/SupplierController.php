<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('Suppliers/Index');
    }

    public function data(): JsonResponse
    {
        $query = Supplier::query()->select('suppliers.*');

        return DataTables::eloquent($query)
            ->addColumn('status', fn (Supplier $s) => $this->statusBadge((bool) $s->is_active))
            ->addColumn('action', fn (Supplier $s) => $this->actionCell(
                route('admin.suppliers.edit', $s),
                route('admin.suppliers.destroy', $s),
            ))
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Suppliers/Form', ['supplier' => null]);
    }

    public function store(SupplierRequest $r): RedirectResponse
    {
        Supplier::create($r->validated());

        return redirect()->route('admin.suppliers.index')
            ->with('success', __('messages.success.created', ['resource' => __('messages.menu.suppliers')]));
    }

    public function edit(Supplier $supplier): Response
    {
        return Inertia::render('Suppliers/Form', ['supplier' => $supplier]);
    }

    public function update(SupplierRequest $r, Supplier $supplier): RedirectResponse
    {
        $supplier->update($r->validated());

        return redirect()->route('admin.suppliers.index')
            ->with('success', __('messages.success.updated', ['resource' => __('messages.menu.suppliers')]));
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return back()->with('success', __('messages.success.deleted', ['resource' => __('messages.menu.suppliers')]));
    }
}
