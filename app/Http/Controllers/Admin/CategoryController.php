<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Support\Uploads;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('Categories/Index');
    }

    public function data(): JsonResponse
    {
        $query = Category::query()->with('parent:id,name_en')->select('categories.*');

        return DataTables::eloquent($query)
            ->addColumn('parent_name', fn (Category $c) => $c->parent?->name_en)
            ->addColumn('status', fn (Category $c) => $this->statusBadge((bool) $c->is_active))
            ->addColumn('action', fn (Category $c) => $this->actionCell(
                route('admin.categories.edit', $c),
                route('admin.categories.destroy', $c),
            ))
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Categories/Form', [
            'category' => null,
            'parents' => Category::query()->orderBy('name_en')->get(['id', 'name_en']),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name_en']);
        $data['image'] = Uploads::store($request->file('image'), 'categories');
        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', __('messages.success.created', ['resource' => __('messages.menu.categories')]));
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('Categories/Form', [
            'category' => $category,
            'parents' => Category::query()->where('id', '!=', $category->id)->orderBy('name_en')->get(['id', 'name_en']),
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name_en']);
        if ($request->hasFile('image')) {
            $data['image'] = Uploads::store($request->file('image'), 'categories');
        }
        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', __('messages.success.updated', ['resource' => __('messages.menu.categories')]));
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', __('messages.success.deleted', ['resource' => __('messages.menu.categories')]));
    }
}
