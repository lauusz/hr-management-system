<?php

namespace App\Http\Controllers\AtkMks\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use App\Models\AtkStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function store(Request $request, AtkItem $item)
    {
        abort_unless($item->module === AtkItem::MODULE_ATK_MKS && $item->deleted_at === null, 404);
        $validated = $request->validate([
            'movement_type' => ['required', 'in:'.AtkStockMovement::TYPE_IN.','.AtkStockMovement::TYPE_OUT],
            'qty' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $warning = DB::transaction(function () use ($item, $validated, $request): ?string {
            $locked = AtkItem::query()->forModule(AtkItem::MODULE_ATK_MKS)
                ->whereNull('deleted_at')->whereKey($item->id)->lockForUpdate()->firstOrFail();
            $before = $locked->stock_qty;
            $qty = (int) $validated['qty'];
            $after = $validated['movement_type'] === AtkStockMovement::TYPE_IN ? $before + $qty : $before - $qty;

            if ($after < 0) {
                return 'Stok tidak cukup.';
            }

            $locked->update(['stock_qty' => $after]);
            AtkStockMovement::create([
                'atk_item_id' => $locked->id,
                'movement_type' => $validated['movement_type'],
                'qty' => $qty,
                'stock_before' => $before,
                'stock_after' => $after,
                'notes' => $validated['notes'] ?? ($validated['movement_type'] === AtkStockMovement::TYPE_IN ? 'Stok masuk' : 'Stok keluar'),
                'created_by' => $request->user()->id,
            ]);

            return null;
        });

        return back()->with($warning ? 'warning' : 'success', $warning ?? 'Stok berhasil diperbarui.');
    }
}

