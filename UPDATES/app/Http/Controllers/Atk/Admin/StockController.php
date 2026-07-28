<?php

namespace App\Http\Controllers\Atk\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use App\Models\AtkStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function store(Request $request, AtkItem $item)
    {
        $validated = $request->validate([
            'movement_type' => ['required', 'in:'.AtkStockMovement::TYPE_IN.','.AtkStockMovement::TYPE_ADJUSTMENT],
            'qty' => [
                'required',
                'integer',
                $request->input('movement_type') === AtkStockMovement::TYPE_ADJUSTMENT ? 'min:0' : 'min:1',
            ],
            'unit_price' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($item, $validated, $request): void {
            $lockedItem = AtkItem::whereKey($item->id)->lockForUpdate()->firstOrFail();
            $before = $lockedItem->stock_qty;
            $after = $validated['movement_type'] === AtkStockMovement::TYPE_ADJUSTMENT
                ? (int) $validated['qty']
                : $before + (int) $validated['qty'];

            $lockedItem->update(['stock_qty' => $after]);
            $unitPrice = $validated['movement_type'] === AtkStockMovement::TYPE_IN ? ($validated['unit_price'] ?? null) : null;
            $movementQty = $validated['movement_type'] === AtkStockMovement::TYPE_ADJUSTMENT ? abs($after - $before) : (int) $validated['qty'];

            AtkStockMovement::create([
                'atk_item_id' => $lockedItem->id,
                'movement_type' => $validated['movement_type'],
                'qty' => $movementQty,
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice === null ? null : $unitPrice * $movementQty,
                'stock_before' => $before,
                'stock_after' => $after,
                // MANUAL: tidak ada source model, source_type dibiarkan NULL.
                'source_type' => null,
                'notes' => $validated['notes'] ?? ($validated['movement_type'] === AtkStockMovement::TYPE_ADJUSTMENT
                    ? 'Koreksi stok manual'
                    : 'Stok masuk manual'),
                'created_by' => $request->user()->id,
            ]);
        });

        return back()->with('success', 'Stok berhasil diperbarui.');
    }
}
