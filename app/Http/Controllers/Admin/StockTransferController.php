<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StockTransferRequest;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class StockTransferController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('StockTransfers/Index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = StockTransfer::query()
            ->select('stock_transfers.*')
            ->with(['fromBranch:id,name_en,name_kh', 'toBranch:id,name_en,name_kh', 'user:id,name']);

        return DataTables::eloquent($query)
            ->addColumn('from_name', fn (StockTransfer $t) => $t->fromBranch?->localized('name'))
            ->addColumn('to_name', fn (StockTransfer $t) => $t->toBranch?->localized('name'))
            ->addColumn('user_name', fn (StockTransfer $t) => $t->user?->name ?? '—')
            ->addColumn('status_badge', fn (StockTransfer $t) => $this->transferStatusBadge($t->status))
            ->addColumn('action', function (StockTransfer $t) {
                $show = route('admin.stock-transfers.show', $t);
                $delete = route('admin.stock-transfers.destroy', $t);

                return '<div class="btn-group btn-group-sm">'
                    .'<a href="'.$show.'" class="btn btn-outline-secondary smk-inertia">'.e(__('messages.view')).'</a>'
                    .'<button type="button" class="btn btn-outline-danger" data-smk-delete="'.$delete.'">'.e(__('messages.delete')).'</button>'
                    .'</div>';
            })
            ->rawColumns(['status_badge', 'action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('StockTransfers/Form', $this->formData());
    }

    public function store(StockTransferRequest $request, StockService $stock): RedirectResponse
    {
        $transfer = DB::transaction(function () use ($request, $stock) {
            $t = StockTransfer::create([
                'ref_no' => 'TRN-'.now()->format('Ymd').'-'.strtoupper(substr(md5(uniqid('', true)), 0, 6)),
                'from_branch_id' => $request->from_branch_id,
                'to_branch_id' => $request->to_branch_id,
                'user_id' => auth()->id(),
                'date' => $request->date,
                'status' => 'sent',
                'note' => $request->note,
            ]);
            foreach ($request->validated()['items'] as $row) {
                $t->items()->create(['product_id' => $row['product_id'], 'qty' => $row['qty']]);
                // Sending: deduct from source branch now. Receiving will add to
                // destination branch when receive() runs.
                $stock->applyDelta((int) $t->from_branch_id, (int) $row['product_id'], -1 * (float) $row['qty']);
            }

            return $t;
        });

        sweetalert()->success(__('messages.success.created', ['resource' => __('messages.menu.stock_transfers')]));

        return redirect()->route('admin.stock-transfers.show', $transfer);
    }

    public function show(StockTransfer $stock_transfer): Response
    {
        $stock_transfer->load(['fromBranch', 'toBranch', 'user', 'items.product']);

        return Inertia::render('StockTransfers/Show', ['transfer' => $stock_transfer]);
    }

    public function receive(StockTransfer $stock_transfer, StockService $stock): RedirectResponse
    {
        if ($stock_transfer->status !== 'sent') {
            return back();
        }
        DB::transaction(function () use ($stock_transfer, $stock) {
            foreach ($stock_transfer->items as $item) {
                $stock->applyDelta((int) $stock_transfer->to_branch_id, (int) $item->product_id, (float) $item->qty);
            }
            $stock_transfer->update(['status' => 'received']);
        });

        sweetalert()->success(__('messages.transfer_received'));

        return back();
    }

    public function destroy(StockTransfer $stock_transfer, StockService $stock): RedirectResponse
    {
        DB::transaction(function () use ($stock_transfer, $stock) {
            // Reverse based on current status.
            if ($stock_transfer->status === 'sent') {
                foreach ($stock_transfer->items as $item) {
                    $stock->applyDelta((int) $stock_transfer->from_branch_id, (int) $item->product_id, (float) $item->qty);
                }
            } elseif ($stock_transfer->status === 'received') {
                // Sent moved out of from_branch, then received moved in to to_branch.
                foreach ($stock_transfer->items as $item) {
                    $stock->applyDelta((int) $stock_transfer->from_branch_id, (int) $item->product_id, (float) $item->qty);
                    $stock->applyDelta((int) $stock_transfer->to_branch_id, (int) $item->product_id, -1 * (float) $item->qty);
                }
            }
            $stock_transfer->delete();
        });

        sweetalert()->success(__('messages.success.deleted', ['resource' => __('messages.menu.stock_transfers')]));

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

    private function transferStatusBadge(string $status): string
    {
        $map = [
            'draft' => 'secondary',
            'sent' => 'info',
            'received' => 'success',
            'cancelled' => 'danger',
        ];
        $cls = $map[$status] ?? 'secondary';

        return '<span class="badge bg-'.$cls.'">'.e(__('messages.statuses.'.$status)).'</span>';
    }
}
