<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExpenseRequest;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class ExpenseController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('Expenses/Index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Expense::query()
            ->select('expenses.*')
            ->with(['branch:id,name_en,name_kh', 'category:id,name', 'user:id,name']);

        return DataTables::eloquent($query)
            ->addColumn('branch_name', fn (Expense $e) => $e->branch?->localized('name'))
            ->addColumn('category_name', fn (Expense $e) => $e->category?->name ?? '—')
            ->addColumn('user_name', fn (Expense $e) => $e->user?->name ?? '—')
            ->editColumn('amount', fn (Expense $e) => number_format((float) $e->amount, 2))
            ->addColumn('action', fn (Expense $e) => $this->actionCell(
                route('admin.expenses.edit', $e),
                route('admin.expenses.destroy', $e),
            ))
            ->rawColumns(['action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Expenses/Form', $this->formData(null));
    }

    public function store(ExpenseRequest $request): RedirectResponse
    {
        Expense::create([
            'ref_no' => 'EXP-'.now()->format('Ymd').'-'.strtoupper(substr(md5(uniqid('', true)), 0, 6)),
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);
        sweetalert()->success(__('messages.success.created', ['resource' => __('messages.menu.expenses')]));

        return redirect()->route('admin.expenses.index');
    }

    public function edit(Expense $expense): Response
    {
        return Inertia::render('Expenses/Form', $this->formData($expense));
    }

    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense->update($request->validated());
        sweetalert()->success(__('messages.success.updated', ['resource' => __('messages.menu.expenses')]));

        return redirect()->route('admin.expenses.index');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();
        sweetalert()->success(__('messages.success.deleted', ['resource' => __('messages.menu.expenses')]));

        return back();
    }

    private function formData(?Expense $expense): array
    {
        return [
            'expense' => $expense,
            'branches' => Branch::query()->active()->orderBy('code')->get(['id', 'code', 'name_en', 'name_kh']),
            'categories' => ExpenseCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ];
    }
}
