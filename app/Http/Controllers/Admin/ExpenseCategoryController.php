<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class ExpenseCategoryController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('ExpenseCategories/Index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = ExpenseCategory::query()->select('expense_categories.*');

        return DataTables::eloquent($query)
            ->addColumn('status', fn (ExpenseCategory $c) => $this->statusBadge((bool) $c->is_active))
            ->addColumn('action', fn (ExpenseCategory $c) => $this->actionCell(
                route('admin.expense-categories.edit', $c),
                route('admin.expense-categories.destroy', $c),
            ))
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('ExpenseCategories/Form', ['category' => null]);
    }

    public function store(ExpenseCategoryRequest $request): RedirectResponse
    {
        ExpenseCategory::create($request->validated());
        sweetalert()->success(__('messages.success.created', ['resource' => __('messages.menu.expense_categories')]));

        return redirect()->route('admin.expense-categories.index');
    }

    public function edit(ExpenseCategory $expense_category): Response
    {
        return Inertia::render('ExpenseCategories/Form', ['category' => $expense_category]);
    }

    public function update(ExpenseCategoryRequest $request, ExpenseCategory $expense_category): RedirectResponse
    {
        $expense_category->update($request->validated());
        sweetalert()->success(__('messages.success.updated', ['resource' => __('messages.menu.expense_categories')]));

        return redirect()->route('admin.expense-categories.index');
    }

    public function destroy(ExpenseCategory $expense_category): RedirectResponse
    {
        $expense_category->delete();
        sweetalert()->success(__('messages.success.deleted', ['resource' => __('messages.menu.expense_categories')]));

        return back();
    }
}
