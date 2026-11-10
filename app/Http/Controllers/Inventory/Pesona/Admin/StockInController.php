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

class StockInController extends Controller
{
    public function create(InvPesonaItem $item, PesonaUnitSnapshot $snapshot): View
    {
        abort_if($item->deleted_at !== null, 404);

        $unitSignature = $snapshot->sign($item);

        return view('inventory.pesona.admin.stock_in.create', compact('item', 'unitSignature'));
    }

    public function store(Request $request, InvPesonaItem $item, PesonaUnitSnapshot $snapshot): RedirectResponse
    {
        abort_if($item->deleted_at !== null, 404);
        if ($request->input('unit_price') === '') {
            $request->merge(['unit_price' => null]);
        }

        $maxStock = min(PHP_INT_MAX, 4294967295);
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:'.$maxStock],
            'unit_price' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'unit_snapshot' => ['required', 'string', 'size:64'],
        ], [
            'unit_snapshot.required' => 'Informasi satuan tidak tersedia. Muat ulang halaman.',
            'unit_snapshot.string' => 'Informasi satuan tidak valid. Muat ulang halaman.',
            'unit_snapshot.size' => 'Informasi satuan tidak valid. Muat ulang halaman.',
        ]);
        $qty = (int) $validated['qty'];
        $unitPrice = ($validated['unit_price'] ?? null) === null ? null : (int) $validated['unit_price'];

        try {
            $itemName = DB::transaction(function () use ($request, $item, $validated, $qty, $unitPrice, $maxStock, $snapshot): string {
                $current = InvPesonaItem::query()->lockForUpdate()->findOrFail($item->id);
                abort_if($current->deleted_at !== null, 404);
                if (! $snapshot->matches($current, $validated['unit_snapshot'])) {
                    throw ValidationException::withMessages(['unit_snapshot' => 'Satuan barang telah berubah sejak halaman dibuka. Muat ulang halaman dan periksa kembali jumlah serta harga.']);
                }

                $before = $current->stock_qty;
                if ($qty > $maxStock - $before) {
                    throw ValidationException::withMessages(['qty' => 'Jumlah masuk menyebabkan stok melebihi batas yang diperbolehkan.']);
                }
                if ($unitPrice !== null && $unitPrice > intdiv(PHP_INT_MAX, $qty)) {
                    throw ValidationException::withMessages(['unit_price' => 'Total pembelian melebihi batas yang dapat dihitung.']);
                }

                $after = $before + $qty;
                $total = $unitPrice === null ? null : $qty * $unitPrice;
                $current->update(['stock_qty' => $after]);
                InvPesonaStockMovement::create([
                    'inv_pesona_item_id' => $current->id,
                    'movement_type' => InvPesonaStockMovement::TYPE_IN,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $total,
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'source_type' => null,
                    'source_id' => null,
                    'notes' => $validated['notes'] ?? 'Stok masuk manual',
                    'created_by' => $request->user()->id,
                ]);

                return $current->name;
            });
        } catch (\Throwable $exception) {
            if ($exception instanceof ValidationException || $exception instanceof HttpExceptionInterface || $exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            report($exception);

            return back()->withInput($request->only('qty', 'unit_price', 'notes', 'unit_snapshot'))
                ->with('error', 'Barang masuk gagal disimpan. Silakan coba lagi.');
        }

        return redirect()->route('inventory.pesona.admin.items.index')
            ->with('success', "Barang masuk {$itemName} sebanyak {$qty} berhasil dicatat.");
    }
}
