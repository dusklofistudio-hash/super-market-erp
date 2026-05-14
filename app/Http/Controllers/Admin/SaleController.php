<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class SaleController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('Sales/Index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Sale::query()
            ->select('sales.*')
            ->with(['branch:id,name_en,name_kh', 'customer:id,name', 'user:id,name']);

        return DataTables::eloquent($query)
            ->addColumn('branch_name', fn (Sale $s) => $s->branch?->localized('name'))
            ->addColumn('customer_name', fn (Sale $s) => $s->customer?->name ?? '—')
            ->addColumn('cashier_name', fn (Sale $s) => $s->user?->name ?? '—')
            ->editColumn('date', fn (Sale $s) => optional($s->date)?->format('Y-m-d H:i'))
            ->editColumn('total', fn (Sale $s) => number_format((float) $s->total, 2))
            ->editColumn('paid', fn (Sale $s) => number_format((float) $s->paid, 2))
            ->addColumn('balance', fn (Sale $s) => number_format((float) $s->total - (float) $s->paid, 2))
            ->addColumn('status_badge', fn (Sale $s) => $this->saleStatusBadge($s->status))
            ->addColumn('action', function (Sale $s) {
                $show = route('admin.sales.show', $s);
                $delete = route('admin.sales.destroy', $s);

                return '<div class="btn-group btn-group-sm">'
                    .'<a href="'.$show.'" class="btn btn-outline-secondary smk-inertia">'.e(__('messages.view')).'</a>'
                    .'<button type="button" class="btn btn-outline-danger" data-smk-delete="'.$delete.'">'.e(__('messages.delete')).'</button>'
                    .'</div>';
            })
            ->rawColumns(['status_badge', 'action'])
            ->toJson();
    }

    public function show(Sale $sale): Response
    {
        $sale->load(['branch', 'customer', 'user', 'items.product', 'payments']);

        return Inertia::render('Sales/Show', ['sale' => $sale]);
    }

    public function destroy(Sale $sale, StockService $stock): RedirectResponse
    {
        DB::transaction(function () use ($sale, $stock) {
            foreach ($sale->items as $item) {
                // Reverse stock decrement.
                $stock->applyDelta((int) $sale->branch_id, (int) $item->product_id, (float) $item->qty);
            }
            $sale->delete();
        });

        sweetalert()->success(__('messages.success.deleted', ['resource' => __('messages.menu.sales')]));

        return back();
    }

    private function saleStatusBadge(string $status): string
    {
        $map = [
            'draft' => 'secondary',
            'completed' => 'success',
            'cancelled' => 'danger',
            'returned' => 'warning',
        ];
        $cls = $map[$status] ?? 'secondary';

        return '<span class="badge bg-'.$cls.'">'.e(__('messages.statuses.'.$status)).'</span>';
    }
}
