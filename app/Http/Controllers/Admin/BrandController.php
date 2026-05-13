<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Models\Brand;
use App\Support\Uploads;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class BrandController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('Brands/Index');
    }

    public function data(): JsonResponse
    {
        $query = Brand::query()->select('brands.*');

        return DataTables::eloquent($query)
            ->addColumn('status', fn (Brand $b) => $this->statusBadge((bool) $b->is_active))
            ->addColumn('action', fn (Brand $b) => $this->actionCell(
                route('admin.brands.edit', $b),
                route('admin.brands.destroy', $b),
            ))
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Brands/Form', ['brand' => null]);
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['logo'] = Uploads::store($request->file('logo'), 'brands');
        Brand::create($data);

        return redirect()->route('admin.brands.index')
            ->with('success', __('messages.success.created', ['resource' => __('messages.menu.brands')]));
    }

    public function edit(Brand $brand): Response
    {
        return Inertia::render('Brands/Form', ['brand' => $brand]);
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        if ($request->hasFile('logo')) {
            $data['logo'] = Uploads::store($request->file('logo'), 'brands');
        }
        $brand->update($data);

        return redirect()->route('admin.brands.index')
            ->with('success', __('messages.success.updated', ['resource' => __('messages.menu.brands')]));
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $brand->delete();

        return back()->with('success', __('messages.success.deleted', ['resource' => __('messages.menu.brands')]));
    }
}
