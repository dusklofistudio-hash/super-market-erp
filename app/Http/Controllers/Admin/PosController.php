<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaleRequest;
use App\Models\Customer;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\Sale;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Cart UI for the cashier. Lists products with quick search, lets the cashier
 * scan/select items, picks a customer, and submits a Sale within an open
 * PosSession.
 */
class PosController extends Controller
{
    public function register(Request $request): Response
    {
        $sessionId = $request->query('session');
        $session = $sessionId
            ? PosSession::with(['register:id,branch_id,name', 'register.branch:id,name_en,name_kh'])->find($sessionId)
            : null;

        return Inertia::render('Pos/Register', [
            'session' => $session,
            'products' => Product::query()
                ->where('is_active', true)
                ->orderBy('name_en')
                ->get(['id', 'barcode', 'sku', 'name_en', 'name_kh', 'sale_price']),
            'customers' => Customer::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'phone']),
        ]);
    }

    public function checkout(SaleRequest $request, StockService $stock): RedirectResponse
    {
        $sale = DB::transaction(function () use ($request, $stock) {
            $items = $request->validated()['items'];
            $totals = $this->computeTotals($items, (float) ($request->discount ?? 0));

            $sale = Sale::create([
                'ref_no' => 'SAL-'.now()->format('Ymd').'-'.strtoupper(substr(md5(uniqid('', true)), 0, 6)),
                'branch_id' => $request->branch_id,
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'pos_session_id' => $request->pos_session_id,
                'date' => now(),
                'subtotal' => $totals['subtotal'],
                'tax' => $totals['tax'],
                'discount' => (float) ($request->discount ?? 0),
                'total' => $totals['total'],
                'paid' => (float) $request->paid,
                'status' => 'completed',
                'note' => $request->note,
            ]);

            foreach ($items as $row) {
                $line = (float) $row['qty'] * (float) $row['unit_price'];
                $sale->items()->create([
                    'product_id' => $row['product_id'],
                    'qty' => $row['qty'],
                    'unit_price' => $row['unit_price'],
                    'tax' => $row['tax'] ?? 0,
                    'subtotal' => $line + ((float) ($row['tax'] ?? 0)),
                ]);
                $stock->applyDelta((int) $sale->branch_id, (int) $row['product_id'], -1 * (float) $row['qty']);
            }

            if ((float) $request->paid > 0) {
                $sale->payments()->create([
                    'date' => now()->toDateString(),
                    'amount' => (float) $request->paid,
                    'method' => $request->payment_method,
                ]);
            }

            if ($sale->pos_session_id) {
                $cash = $sale->payments()->where('method', 'cash')->sum('amount');
                PosSession::where('id', $sale->pos_session_id)
                    ->increment('expected_cash', (float) $cash);
            }

            return $sale;
        });

        sweetalert()->success(__('messages.success.created', ['resource' => __('messages.menu.sales')]));

        return redirect()->route('admin.sales.show', $sale);
    }

    private function computeTotals(array $items, float $discount): array
    {
        $subtotal = 0;
        $tax = 0;
        foreach ($items as $row) {
            $subtotal += (float) $row['qty'] * (float) $row['unit_price'];
            $tax += (float) ($row['tax'] ?? 0);
        }
        $total = max(0, $subtotal + $tax - $discount);

        return compact('subtotal', 'tax', 'total');
    }
}
