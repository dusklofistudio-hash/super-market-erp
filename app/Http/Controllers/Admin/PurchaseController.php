<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RendersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PurchasePaymentRequest;
use App\Http\Requests\Admin\PurchaseRequest;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\ActivityLogger;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class PurchaseController extends Controller
{
    use RendersDataTable;

    public function index(): Response
    {
        return Inertia::render('Purchases/Index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Purchase::query()
            ->select('purchases.*')
            ->with(['branch:id,name_en,name_kh', 'supplier:id,name']);

        return DataTables::eloquent($query)
            ->addColumn('branch_name', fn (Purchase $p) => $p->branch?->localized('name'))
            ->addColumn('supplier_name', fn (Purchase $p) => $p->supplier?->name ?? '—')
            ->editColumn('total', fn (Purchase $p) => number_format((float) $p->total, 2))
            ->editColumn('paid', fn (Purchase $p) => number_format((float) $p->paid, 2))
            ->addColumn('balance', fn (Purchase $p) => number_format((float) $p->total - (float) $p->paid, 2))
            ->addColumn('status_badge', fn (Purchase $p) => $this->purchaseStatusBadge($p->status))
            ->addColumn('action', function (Purchase $p) {
                $show = route('admin.purchases.show', $p);
                $edit = route('admin.purchases.edit', $p);
                $delete = route('admin.purchases.destroy', $p);
                $html = '<div class="btn-group btn-group-sm">'
                    .'<a href="'.$show.'" class="btn btn-outline-secondary smk-inertia">'.e(__('messages.view')).'</a>';
                if ($p->status !== 'received') {
                    $html .= '<a href="'.$edit.'" class="btn btn-outline-primary smk-inertia">'.e(__('messages.edit')).'</a>';
                }
                $html .= '<button type="button" class="btn btn-outline-danger" data-smk-delete="'.$delete.'">'.e(__('messages.delete')).'</button>';
                $html .= '</div>';

                return $html;
            })
            ->rawColumns(['status_badge', 'action'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Purchases/Form', $this->formData(null));
    }

    public function store(PurchaseRequest $request, StockService $stock, ActivityLogger $logger): RedirectResponse
    {
        $purchase = DB::transaction(function () use ($request, $stock) {
            $totals = $this->computeTotals($request->validated()['items'], (float) ($request->discount ?? 0));

            $purchase = Purchase::create([
                'ref_no' => $this->nextRef('PUR'),
                'branch_id' => $request->branch_id,
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id(),
                'date' => $request->date,
                'subtotal' => $totals['subtotal'],
                'tax' => $totals['tax'],
                'discount' => (float) ($request->discount ?? 0),
                'total' => $totals['total'],
                'paid' => 0,
                'status' => 'received',
                'note' => $request->note,
            ]);

            foreach ($request->validated()['items'] as $row) {
                $line = (float) $row['qty'] * (float) $row['unit_cost'];
                $purchase->items()->create([
                    'product_id' => $row['product_id'],
                    'qty' => $row['qty'],
                    'unit_cost' => $row['unit_cost'],
                    'tax' => $row['tax'] ?? 0,
                    'subtotal' => $line + ((float) ($row['tax'] ?? 0)),
                ]);
                $stock->applyDelta((int) $purchase->branch_id, (int) $row['product_id'], (float) $row['qty']);
            }

            return $purchase;
        });

        $logger->log('purchase.created', $purchase, ['ref_no' => $purchase->ref_no, 'total' => (float) $purchase->total]);

        sweetalert()->success(__('messages.success.created', ['resource' => __('messages.menu.purchases')]));

        return redirect()->route('admin.purchases.show', $purchase)
            ->with('success', __('messages.success.created', ['resource' => __('messages.menu.purchases')]));
    }

    public function show(Purchase $purchase): Response
    {
        $purchase->load(['branch', 'supplier', 'user', 'items.product', 'payments']);

        return Inertia::render('Purchases/Show', ['purchase' => $purchase]);
    }

    public function edit(Purchase $purchase): Response
    {
        $purchase->load('items.product');

        return Inertia::render('Purchases/Form', $this->formData($purchase));
    }

    public function update(PurchaseRequest $request, Purchase $purchase, StockService $stock, ActivityLogger $logger): RedirectResponse
    {
        DB::transaction(function () use ($request, $purchase, $stock) {
            // Reverse existing stock impact for this purchase.
            foreach ($purchase->items as $old) {
                $stock->applyDelta((int) $purchase->branch_id, (int) $old->product_id, -1 * (float) $old->qty);
            }
            $purchase->items()->delete();

            $totals = $this->computeTotals($request->validated()['items'], (float) ($request->discount ?? 0));

            $purchase->update([
                'branch_id' => $request->branch_id,
                'supplier_id' => $request->supplier_id,
                'date' => $request->date,
                'subtotal' => $totals['subtotal'],
                'tax' => $totals['tax'],
                'discount' => (float) ($request->discount ?? 0),
                'total' => $totals['total'],
                'note' => $request->note,
            ]);

            foreach ($request->validated()['items'] as $row) {
                $line = (float) $row['qty'] * (float) $row['unit_cost'];
                $purchase->items()->create([
                    'product_id' => $row['product_id'],
                    'qty' => $row['qty'],
                    'unit_cost' => $row['unit_cost'],
                    'tax' => $row['tax'] ?? 0,
                    'subtotal' => $line + ((float) ($row['tax'] ?? 0)),
                ]);
                $stock->applyDelta((int) $purchase->branch_id, (int) $row['product_id'], (float) $row['qty']);
            }
        });

        $logger->log('purchase.updated', $purchase, ['ref_no' => $purchase->ref_no, 'total' => (float) $purchase->total]);

        sweetalert()->success(__('messages.success.updated', ['resource' => __('messages.menu.purchases')]));

        return redirect()->route('admin.purchases.show', $purchase)
            ->with('success', __('messages.success.updated', ['resource' => __('messages.menu.purchases')]));
    }

    public function destroy(Purchase $purchase, StockService $stock, ActivityLogger $logger): RedirectResponse
    {
        $payload = ['ref_no' => $purchase->ref_no, 'total' => (float) $purchase->total];
        DB::transaction(function () use ($purchase, $stock) {
            foreach ($purchase->items as $item) {
                $stock->applyDelta((int) $purchase->branch_id, (int) $item->product_id, -1 * (float) $item->qty);
            }
            $purchase->delete();
        });
        $logger->log('purchase.deleted', $purchase, $payload);

        sweetalert()->success(__('messages.success.deleted', ['resource' => __('messages.menu.purchases')]));

        return back()->with('success', __('messages.success.deleted', ['resource' => __('messages.menu.purchases')]));
    }

    public function addPayment(PurchasePaymentRequest $request, Purchase $purchase): RedirectResponse
    {
        DB::transaction(function () use ($request, $purchase) {
            $purchase->payments()->create($request->validated());
            $purchase->update(['paid' => (float) $purchase->paid + (float) $request->amount]);
        });

        sweetalert()->success(__('messages.success.created', ['resource' => __('messages.payment')]));

        return back()->with('success', __('messages.success.created', ['resource' => __('messages.payment')]));
    }

    private function formData(?Purchase $purchase): array
    {
        return [
            'purchase' => $purchase,
            'branches' => Branch::query()->active()->orderBy('code')->get(['id', 'code', 'name_en', 'name_kh']),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']),
            'products' => Product::query()
                ->where('is_active', true)
                ->orderBy('name_en')
                ->get(['id', 'barcode', 'sku', 'name_en', 'name_kh', 'cost_price', 'sale_price']),
        ];
    }

    private function computeTotals(array $items, float $discount): array
    {
        $subtotal = 0;
        $tax = 0;
        foreach ($items as $row) {
            $subtotal += (float) $row['qty'] * (float) $row['unit_cost'];
            $tax += (float) ($row['tax'] ?? 0);
        }
        $total = max(0, $subtotal + $tax - $discount);

        return compact('subtotal', 'tax', 'total');
    }

    private function nextRef(string $prefix): string
    {
        return $prefix.'-'.now()->format('Ymd').'-'.strtoupper(substr(md5(uniqid('', true)), 0, 6));
    }

    private function purchaseStatusBadge(string $status): string
    {
        $map = [
            'draft' => 'secondary',
            'received' => 'success',
            'cancelled' => 'danger',
        ];
        $cls = $map[$status] ?? 'secondary';

        return '<span class="badge bg-'.$cls.'">'.e(__('messages.statuses.'.$status)).'</span>';
    }
}
