<?php

namespace App\Http\Controllers\Atk\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkNeedRequest;
use App\Models\AtkStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NeedRequestController extends Controller
{
    public function index()
    {
        $needRequests = AtkNeedRequest::with('item')
            ->latest()
            ->paginate(20);

        // Daftar item aktif untuk dropdown "pilih item existing" pada proses DONE.
        $items = AtkItem::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'unit_name', 'stock_qty']);

        $categories = AtkCategory::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('atk.admin.need_requests.index', compact('needRequests', 'items', 'categories'));
    }

    public function process(Request $request, AtkNeedRequest $needRequest)
    {
        // Guard dobel-proses: hanya need-request PENDING yang boleh diproses.
        if ($needRequest->status !== AtkNeedRequest::STATUS_PENDING) {
            return redirect()
                ->route('v2.atk.admin.need-requests.index')
                ->with('warning', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:'.AtkNeedRequest::STATUS_DONE.','.AtkNeedRequest::STATUS_REJECTED],
            'admin_note' => ['nullable', 'string', 'max:1000'],
            // Field untuk DONE (qty aktual + restock target).
            'qty' => ['nullable', 'integer', 'min:1'],
            'unit_price' => ['nullable', 'integer', 'min:0'],
            'existing_item_id' => ['nullable', 'exists:atk_items,id'],
            'new_item_name' => ['nullable', 'string', 'max:150'],
            'new_item_unit_name' => ['nullable', 'string', 'max:30'],
            'new_item_category_id' => ['nullable', 'exists:atk_categories,id'],
        ]);

        // Branch REJECTED: hanya catat status, tidak sentuh stok.
        if ($validated['status'] === AtkNeedRequest::STATUS_REJECTED) {
            $needRequest->update([
                'status' => AtkNeedRequest::STATUS_REJECTED,
                'processed_by' => $request->user()->id,
                'processed_at' => now(),
                'admin_note' => $validated['admin_note'] ?? null,
            ]);

            return redirect()
                ->route('v2.atk.admin.need-requests.index')
                ->with('success', 'Pengajuan barang ditolak.');
        }

        // Branch DONE: validasi tambahan + transaksi stok.
        // Saat DONE, qty aktual wajib diisi.
        if (empty($validated['qty'])) {
            return back()->withInput()->withErrors(
                ['qty' => 'Jumlah aktual wajib diisi saat menandai pengajuan selesai.']
            );
        }

        // Resolve target item:
        // - Jika need-request punya atk_item_id (barang katalog), pakai item itu.
        // - Jika non-katalog (NULL), admin wajib pilih existing ATAU create baru.
        $isNonCatalog = $needRequest->atk_item_id === null;
        $wantsNewItem = $isNonCatalog && ! empty($validated['new_item_name']);

        if ($isNonCatalog && empty($validated['existing_item_id']) && ! $wantsNewItem) {
            return back()->withInput()->withErrors(
                ['existing_item_id' => 'Pilih barang katalog atau isi detail barang baru.']
            );
        }
        if ($isNonCatalog && ! empty($validated['existing_item_id']) && $wantsNewItem) {
            return back()->withInput()->withErrors(
                ['existing_item_id' => 'Pilih salah satu: barang katalog ATAU barang baru, jangan keduanya.']
            );
        }

        $qtyActual = (int) $validated['qty'];
        $unitPrice = $validated['unit_price'] ?? null;
        $admin = $request->user();

        DB::transaction(function () use ($needRequest, $validated, $qtyActual, $unitPrice, $admin, $wantsNewItem): void {
            // 1. Tentukan item tujuan.
            if ($wantsNewItem) {
                $item = AtkItem::create([
                    'atk_category_id' => $validated['new_item_category_id'] ?? null,
                    'name' => $validated['new_item_name'],
                    'unit_name' => $validated['new_item_unit_name'] ?? $needRequest->unit_name,
                    'stock_qty' => 0,
                    'minimum_stock' => 0,
                    'min_request_qty' => 1,
                    'is_active' => true,
                    'created_by' => $admin->id,
                ]);
                $itemId = $item->id;
            } else {
                $itemId = $validated['existing_item_id'] ?? $needRequest->atk_item_id;
            }

            // 2. Lock item & hitung before/after.
            $lockedItem = AtkItem::whereKey($itemId)->lockForUpdate()->firstOrFail();
            $before = $lockedItem->stock_qty;
            $after = $before + $qtyActual;
            $lockedItem->update(['stock_qty' => $after]);

            // 3. Catat movement IN dengan source NEED_REQUEST.
            AtkStockMovement::create([
                'atk_item_id' => $lockedItem->id,
                'movement_type' => AtkStockMovement::TYPE_IN,
                'qty' => $qtyActual,
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice === null ? null : $unitPrice * $qtyActual,
                'stock_before' => $before,
                'stock_after' => $after,
                'source_type' => AtkStockMovement::SOURCE_NEED_REQUEST,
                'source_id' => $needRequest->id,
                'notes' => $validated['admin_note'] ?? 'Restock via need-request',
                'created_by' => $admin->id,
            ]);

            // 4. Update need-request. atk_item_id disimpan agar audit trail tahu
            //    item mana yang dipakai (terutama untuk item baru yang baru dibuat).
            $needRequest->update([
                'status' => AtkNeedRequest::STATUS_DONE,
                'atk_item_id' => $lockedItem->id,
                'processed_by' => $admin->id,
                'processed_at' => now(),
                'admin_note' => $validated['admin_note'] ?? null,
            ]);
        });

        return redirect()
            ->route('v2.atk.admin.need-requests.index')
            ->with('success', 'Stok berhasil ditambahkan dan pengajuan diselesaikan.');
    }
}
