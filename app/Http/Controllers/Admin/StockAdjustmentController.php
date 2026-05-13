<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StockAdjustmentRequest;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class StockAdjustmentController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('StockAdjustments/Index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = StockAdjustment::query()
            ->select('stock_adjustments.*')
            ->with(['branch:id,name_en,name_kh', 'user:id,name'])
            ->withCount('items');

        return DataTables::eloquent($query)
            ->addColumn('branch_name', fn (StockAdjustment $a) => $a->branch?->localized('name'))
            ->addColumn('user_name', fn (StockAdjustment $a) => $a->user?->name ?? '—')
            ->addColumn('type_badge', fn (StockAdjustment $a) => $a->type === 'addition'
                ? '<span class="badge bg-success">'.e(__('messages.statuses.addition')).'</span>'
                : '<span class="badge bg-warning text-dark">'.e(__('messages.statuses.subtraction')).'</span>')
            ->addColumn('action', fn (StockAdjustment $a) => $this->actionCell(
                null,
                route('admin.stock-adjustments.destroy', $a),
            ))
            ->rawColumns(['type_badge', 'action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('StockAdjustments/Form', $this->formData());
    }

    public function store(StockAdjustmentRequest $request, StockService $stock): RedirectResponse
    {
        DB::transaction(function () use ($request, $stock) {
            $adj = StockAdjustment::create([
                'ref_no' => 'ADJ-'.now()->format('Ymd').'-'.strtoupper(substr(md5(uniqid('', true)), 0, 6)),
                'branch_id' => $request->branch_id,
                'user_id' => auth()->id(),
                'date' => $request->date,
                'type' => $request->type,
                'reason' => $request->reason,
            ]);
            $sign = $request->type === 'addition' ? 1 : -1;
            foreach ($request->validated()['items'] as $row) {
                $adj->items()->create([
                    'product_id' => $row['product_id'],
                    'qty' => $row['qty'],
                ]);
                $stock->applyDelta((int) $adj->branch_id, (int) $row['product_id'], $sign * (float) $row['qty']);
            }
        });

        sweetalert()->success(__('messages.success.created', ['resource' => __('messages.menu.stock_adjustments')]));

        return redirect()->route('admin.stock-adjustments.index');
    }

    public function destroy(StockAdjustment $stock_adjustment, StockService $stock): RedirectResponse
    {
        DB::transaction(function () use ($stock_adjustment, $stock) {
            $sign = $stock_adjustment->type === 'addition' ? -1 : 1;
            foreach ($stock_adjustment->items as $item) {
                $stock->applyDelta((int) $stock_adjustment->branch_id, (int) $item->product_id, $sign * (float) $item->qty);
            }
            $stock_adjustment->delete();
        });

        sweetalert()->success(__('messages.success.deleted', ['resource' => __('messages.menu.stock_adjustments')]));

        return back();
    }

    private function formData(): array
    {
        return [
            'branches' => Branch::query()->active()->orderBy('code')->get(['id', 'code', 'name_en', 'name_kh']),
            'products' => Product::query()
                ->where('is_active', true)
                ->orderBy('name_en')
                ->get(['id', 'barcode', 'sku', 'name_en', 'name_kh']),
        ];
    }
}
