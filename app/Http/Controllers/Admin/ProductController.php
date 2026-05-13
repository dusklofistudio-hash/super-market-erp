<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\TaxRate;
use App\Models\Unit;
use App\Support\Uploads;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('Products/Index');
    }

    public function data(): JsonResponse
    {
        $query = Product::query()
            ->with(['category:id,name_en', 'brand:id,name', 'unit:id,name_en'])
            ->select('products.*');

        return DataTables::eloquent($query)
            ->addColumn('category_name', fn (Product $p) => $p->category?->name_en)
            ->addColumn('brand_name', fn (Product $p) => $p->brand?->name)
            ->addColumn('unit_name', fn (Product $p) => $p->unit?->name_en)
            ->addColumn('status', fn (Product $p) => $this->statusBadge((bool) $p->is_active))
            ->addColumn('action', fn (Product $p) => $this->actionCell(
                route('admin.products.edit', $p),
                route('admin.products.destroy', $p),
            ))
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Products/Form', [
            'product' => null,
            'lookups' => $this->lookups(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['image'] = Uploads::store($request->file('image'), 'products');
        Product::create($data);

        return redirect()->route('admin.products.index')
            ->with('success', __('messages.success.created', ['resource' => __('messages.menu.products')]));
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('Products/Form', [
            'product' => $product,
            'lookups' => $this->lookups(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = Uploads::store($request->file('image'), 'products');
        }
        $product->update($data);

        return redirect()->route('admin.products.index')
            ->with('success', __('messages.success.updated', ['resource' => __('messages.menu.products')]));
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return back()->with('success', __('messages.success.deleted', ['resource' => __('messages.menu.products')]));
    }

    protected function lookups(): array
    {
        return [
            'categories' => Category::query()->orderBy('name_en')->get(['id', 'name_en']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
            'units' => Unit::query()->orderBy('name_en')->get(['id', 'name_en']),
            'tax_rates' => TaxRate::query()->orderBy('name')->get(['id', 'name', 'rate']),
        ];
    }
}
