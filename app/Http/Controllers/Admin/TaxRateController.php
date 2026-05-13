<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TaxRateRequest;
use App\Models\TaxRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class TaxRateController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('TaxRates/Index');
    }

    public function data(): JsonResponse
    {
        $query = TaxRate::query()->select('tax_rates.*');

        return DataTables::eloquent($query)
            ->addColumn('status', fn (TaxRate $t) => $this->statusBadge((bool) $t->is_active))
            ->addColumn('inclusive', fn (TaxRate $t) => $t->is_inclusive ? __('messages.yes') : __('messages.no'))
            ->addColumn('action', fn (TaxRate $t) => $this->actionCell(
                route('admin.tax-rates.edit', $t),
                route('admin.tax-rates.destroy', $t),
            ))
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('TaxRates/Form', ['tax_rate' => null]);
    }

    public function store(TaxRateRequest $request): RedirectResponse
    {
        TaxRate::create($request->validated());

        return redirect()->route('admin.tax-rates.index')
            ->with('success', __('messages.success.created', ['resource' => __('messages.menu.tax_rates')]));
    }

    public function edit(TaxRate $taxRate): Response
    {
        return Inertia::render('TaxRates/Form', ['tax_rate' => $taxRate]);
    }

    public function update(TaxRateRequest $request, TaxRate $taxRate): RedirectResponse
    {
        $taxRate->update($request->validated());

        return redirect()->route('admin.tax-rates.index')
            ->with('success', __('messages.success.updated', ['resource' => __('messages.menu.tax_rates')]));
    }

    public function destroy(TaxRate $taxRate): RedirectResponse
    {
        $taxRate->delete();

        return back()->with('success', __('messages.success.deleted', ['resource' => __('messages.menu.tax_rates')]));
    }
}
