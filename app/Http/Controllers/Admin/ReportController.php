<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\ProductBranchStock;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Read-only report pages. Each accepts a date range and optional branch
 * filter, computes the summary in-process, and ships the rows to the
 * matching React page.
 */
class ReportController extends Controller
{
    public function salesSummary(Request $request): Response
    {
        [$from, $to, $branchId] = $this->filters($request);

        $rows = Sale::query()
            ->whereBetween('date', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'completed')
            ->selectRaw('DATE(date) as day, COUNT(*) as count, SUM(total) as total, SUM(paid) as paid')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return Inertia::render('Reports/SalesSummary', [
            'rows' => $rows,
            'filters' => compact('from', 'to', 'branchId') + ['branches' => $this->branches()],
            'totals' => [
                'count' => (int) $rows->sum('count'),
                'total' => (float) $rows->sum('total'),
                'paid' => (float) $rows->sum('paid'),
            ],
        ]);
    }

    public function stockByBranch(Request $request): Response
    {
        $branchId = $request->integer('branch_id') ?: null;

        $rows = ProductBranchStock::query()
            ->with(['product:id,sku,barcode,name_en,name_kh,alert_qty', 'branch:id,name_en,name_kh'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('branch_id')
            ->limit(2000)
            ->get();

        return Inertia::render('Reports/StockByBranch', [
            'rows' => $rows,
            'filters' => compact('branchId') + ['branches' => $this->branches()],
        ]);
    }

    public function profit(Request $request): Response
    {
        [$from, $to, $branchId] = $this->filters($request);

        $salesTotal = Sale::query()
            ->whereBetween('date', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'completed')
            ->sum('total');

        $purchaseTotal = Purchase::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'received')
            ->sum('total');

        $expenseTotal = Expense::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('amount');

        return Inertia::render('Reports/Profit', [
            'filters' => compact('from', 'to', 'branchId') + ['branches' => $this->branches()],
            'rows' => [
                'sales' => (float) $salesTotal,
                'purchases' => (float) $purchaseTotal,
                'expenses' => (float) $expenseTotal,
                'profit' => (float) $salesTotal - (float) $purchaseTotal - (float) $expenseTotal,
            ],
        ]);
    }

    public function expenses(Request $request): Response
    {
        [$from, $to, $branchId] = $this->filters($request);

        $rows = Expense::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with(['category:id,name', 'branch:id,name_en,name_kh'])
            ->orderBy('date', 'desc')
            ->get();

        $byCategory = $rows
            ->groupBy(fn ($e) => $e->category?->name ?? '—')
            ->map(fn ($items) => (float) $items->sum('amount'));

        return Inertia::render('Reports/Expenses', [
            'rows' => $rows,
            'byCategory' => $byCategory,
            'filters' => compact('from', 'to', 'branchId') + ['branches' => $this->branches()],
            'total' => (float) $rows->sum('amount'),
        ]);
    }

    private function filters(Request $request): array
    {
        $from = $request->date('from') ?: Carbon::now()->startOfMonth();
        $to = $request->date('to') ?: Carbon::now()->endOfMonth();
        $branchId = $request->integer('branch_id') ?: null;

        return [$from, $to, $branchId];
    }

    private function branches()
    {
        return Branch::query()->active()->orderBy('code')->get(['id', 'code', 'name_en', 'name_kh']);
    }
}
