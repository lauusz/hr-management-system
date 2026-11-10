<?php

namespace App\Http\Controllers\Inventory\Pesona\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvPesonaItem;
use App\Models\InvPesonaStockMovement;
use App\Services\Inventory\PesonaUnitSnapshot;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class StockAdjustmentController extends Controller
{
    public function create(InvPesonaItem $item, PesonaUnitSnapshot $snapshot): View
    {
        abort_if($item->deleted_at !== null, 404);

        $unitSignature = $snapshot->sign($item);

        return view('inventory.pesona.admin.stock_adjustments.create', compact('item', 'unitSignature'));
    }

    public function store(Request $request, InvPesonaItem $item, PesonaUnitSnapshot $snapshot): RedirectResponse
    {
        abort_if($item->deleted_at !== null, 404);
        if (is_string($request->input('notes'))) {
            $request->merge(['notes' => trim($request->input('notes'))]);
        }
        $maxStock = min(PHP_INT_MAX, 4294967295);
        $validated = $request->validate([
            'actual_stock' => ['required', 'integer', 'min:0', 'max:'.$maxStock],
            'expected_stock' => ['required', 'integer', 'min:0', 'max:'.$maxStock],
            'notes' => ['required', 'string', 'max:1000'],
            'unit_snapshot' => ['required', 'string', 'size:64'],
        ], [
            'unit_snapshot.required' => 'Informasi satuan tidak tersedia. Muat ulang halaman.',
            'unit_snapshot.string' => 'Informasi satuan tidak valid. Muat ulang halaman.',
            'unit_snapshot.size' => 'Informasi satuan tidak valid. Muat ulang halaman.',
        ]);

        try {
            $result = DB::transaction(function () use ($request, $item, $validated, $snapshot): array {
                $current = InvPesonaItem::query()->lockForUpdate()->findOrFail($item->id);
                abort_if($current->deleted_at !== null, 404);
                if (! $snapshot->matches($current, $validated['unit_snapshot'])) {
                    throw ValidationException::withMessages(['unit_snapshot' => 'Satuan barang telah berubah sejak halaman dibuka. Muat ulang halaman dan periksa kembali stok aktual.']);
                }
                $before = $current->stock_qty;
                if ($before !== (int) $validated['expected_stock']) {
                    throw ValidationException::withMessages(['expected_stock' => 'Stok telah berubah sejak halaman dibuka. Muat ulang halaman dan periksa kembali stok aktual.']);
                }
                $after = (int) $validated['actual_stock'];
                if ($before === $after) {
                    return ['changed' => false, 'name' => $current->name, 'stock' => $after];
                }

                $current->update(['stock_qty' => $after]);
                InvPesonaStockMovement::create([
                    'inv_pesona_item_id' => $current->id,
                    'movement_type' => InvPesonaStockMovement::TYPE_ADJUSTMENT,
                    'qty' => $after > $before ? $after - $before : $before - $after,
                    'unit_price' => null,
                    'total_price' => null,
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'source_type' => null,
                    'source_id' => null,
                    'notes' => $validated['notes'],
                    'created_by' => $request->user()->id,
                ]);

                return ['changed' => true, 'name' => $current->name, 'stock' => $after];
            });
        } catch (\Throwable $exception) {
            if ($exception instanceof ValidationException || $exception instanceof HttpExceptionInterface || $exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            report($exception);

            return back()->withInput($request->only('actual_stock', 'expected_stock', 'notes', 'unit_snapshot'))
                ->with('error', 'Koreksi stok gagal disimpan. Silakan coba lagi.');
        }

        if (! $result['changed']) {
            return redirect()->route('inventory.pesona.admin.stock-movements.index', ['item_id' => $item->id])
                ->with('warning', 'Stok aktual sama dengan stok saat ini. Tidak ada perubahan.');
        }

        return redirect()->route('inventory.pesona.admin.stock-movements.index', ['item_id' => $item->id])
            ->with('success', "Koreksi stok {$result['name']} berhasil. Stok baru: {$result['stock']}.");
    }
}
