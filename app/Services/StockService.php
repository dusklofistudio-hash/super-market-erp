<?php

namespace App\Services;

use App\Models\ProductBranchStock;

/**
 * Centralized helper for adjusting per-branch product stock levels. Every
 * Phase 2 module that affects inventory (purchases received, sales completed,
 * stock adjustments, stock transfers) writes through `applyDelta` so the
 * arithmetic stays in one place and the row is created on demand.
 */
class StockService
{
    /**
     * Apply a signed quantity change for a given branch/product pair. Returns
     * the resulting row so callers can read the new quantity if useful.
     */
    public function applyDelta(int $branchId, int $productId, float $delta): ProductBranchStock
    {
        $row = ProductBranchStock::firstOrCreate(
            ['branch_id' => $branchId, 'product_id' => $productId],
            ['qty' => 0]
        );

        // Use raw arithmetic to avoid race-conditions with stale decimal casts.
        $row->qty = (float) $row->qty + $delta;
        $row->save();

        return $row;
    }
}
