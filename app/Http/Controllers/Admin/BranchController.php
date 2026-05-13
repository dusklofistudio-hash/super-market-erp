<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BranchRequest;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class BranchController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('Branches/Index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Branch::query()->with('manager:id,name')->select('branches.*');

        return DataTables::eloquent($query)
            ->addColumn('manager_name', fn (Branch $b) => $b->manager?->name)
            ->addColumn('status', fn (Branch $b) => $this->statusBadge((bool) $b->is_active))
            ->addColumn('action', fn (Branch $b) => $this->actionCell(
                route('admin.branches.edit', $b),
                route('admin.branches.destroy', $b),
            ))
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Branches/Form', [
            'branch' => null,
            'managers' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(BranchRequest $request): RedirectResponse
    {
        Branch::create($request->validated());
        sweetalert()->success(__('messages.success.created', ['resource' => __('messages.menu.branches')]));

        return redirect()->route('admin.branches.index')
            ->with('success', __('messages.success.created', ['resource' => __('messages.menu.branches')]));
    }

    public function edit(Branch $branch): Response
    {
        return Inertia::render('Branches/Form', [
            'branch' => $branch,
            'managers' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(BranchRequest $request, Branch $branch): RedirectResponse
    {
        $branch->update($request->validated());
        sweetalert()->success(__('messages.success.updated', ['resource' => __('messages.menu.branches')]));

        return redirect()->route('admin.branches.index')
            ->with('success', __('messages.success.updated', ['resource' => __('messages.menu.branches')]));
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $branch->delete();
        sweetalert()->success(__('messages.success.deleted', ['resource' => __('messages.menu.branches')]));

        return back()->with('success', __('messages.success.deleted', ['resource' => __('messages.menu.branches')]));
    }
}
