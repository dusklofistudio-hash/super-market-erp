<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerGroupRequest;
use App\Models\CustomerGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class CustomerGroupController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('CustomerGroups/Index');
    }

    public function data(): JsonResponse
    {
        $query = CustomerGroup::query()->select('customer_groups.*');

        return DataTables::eloquent($query)
            ->addColumn('status', fn (CustomerGroup $g) => $this->statusBadge((bool) $g->is_active))
            ->addColumn('action', fn (CustomerGroup $g) => $this->actionCell(
                route('admin.customer-groups.edit', $g),
                route('admin.customer-groups.destroy', $g),
            ))
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('CustomerGroups/Form', ['customer_group' => null]);
    }

    public function store(CustomerGroupRequest $r): RedirectResponse
    {
        CustomerGroup::create($r->validated());

        return redirect()->route('admin.customer-groups.index')
            ->with('success', __('messages.success.created', ['resource' => __('messages.menu.customer_groups')]));
    }

    public function edit(CustomerGroup $customerGroup): Response
    {
        return Inertia::render('CustomerGroups/Form', ['customer_group' => $customerGroup]);
    }

    public function update(CustomerGroupRequest $r, CustomerGroup $customerGroup): RedirectResponse
    {
        $customerGroup->update($r->validated());

        return redirect()->route('admin.customer-groups.index')
            ->with('success', __('messages.success.updated', ['resource' => __('messages.menu.customer_groups')]));
    }

    public function destroy(CustomerGroup $customerGroup): RedirectResponse
    {
        $customerGroup->delete();

        return back()->with('success', __('messages.success.deleted', ['resource' => __('messages.menu.customer_groups')]));
    }
}
