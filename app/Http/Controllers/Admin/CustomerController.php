<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerRequest;
use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('Customers/Index');
    }

    public function data(): JsonResponse
    {
        $query = Customer::query()->with('group:id,name')->select('customers.*');

        return DataTables::eloquent($query)
            ->addColumn('group_name', fn (Customer $c) => $c->group?->name)
            ->addColumn('status', fn (Customer $c) => $this->statusBadge((bool) $c->is_active))
            ->addColumn('action', fn (Customer $c) => $this->actionCell(
                route('admin.customers.edit', $c),
                route('admin.customers.destroy', $c),
            ))
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Customers/Form', [
            'customer' => null,
            'groups' => CustomerGroup::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(CustomerRequest $r): RedirectResponse
    {
        Customer::create($r->validated());

        return redirect()->route('admin.customers.index')
            ->with('success', __('messages.success.created', ['resource' => __('messages.menu.customers')]));
    }

    public function edit(Customer $customer): Response
    {
        return Inertia::render('Customers/Form', [
            'customer' => $customer,
            'groups' => CustomerGroup::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(CustomerRequest $r, Customer $customer): RedirectResponse
    {
        $customer->update($r->validated());

        return redirect()->route('admin.customers.index')
            ->with('success', __('messages.success.updated', ['resource' => __('messages.menu.customers')]));
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return back()->with('success', __('messages.success.deleted', ['resource' => __('messages.menu.customers')]));
    }
}
