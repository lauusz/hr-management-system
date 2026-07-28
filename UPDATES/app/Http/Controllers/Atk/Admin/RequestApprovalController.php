<?php

namespace App\Http\Controllers\Atk\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use App\Models\AtkRequest;
use App\Models\AtkRequestItem;
use App\Models\AtkStockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RequestApprovalController extends Controller
{
    public function index(Request $request)
    {
        $requests = AtkRequest::with('items')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $keyword = '%'.$request->string('q')->toString().'%';
                $query->where(function ($query) use ($keyword): void {
                    $query->where('request_number', 'like', $keyword)
                        ->orWhere('user_name_snapshot', 'like', $keyword)
                        ->orWhere('pt_name_snapshot', 'like', $keyword);
                });
            })
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [AtkRequest::STATUS_PENDING])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('atk.admin.requests.index', compact('requests'));
    }

    public function createManual()
    {
        $users = User::query()
            ->active()
            ->with('profile.pt')
            ->orderBy('name')
            ->get();
        $items = AtkItem::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('atk.admin.requests.manual-create', compact('users', 'items'));
    }

    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where('status', User::STATUS_ACTIVE),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
            'quantities' => ['required', 'array'],
            'quantities.*' => ['nullable', 'integer', 'min:1'],
        ]);

        $quantities = collect($validated['quantities'])
            ->filter(fn ($qty) => $qty !== null && (int) $qty > 0)
            ->map(fn ($qty) => (int) $qty);

        if ($quantities->isEmpty()) {
            return back()->withErrors(['quantities' => 'Pilih minimal satu barang.'])->withInput();
        }

        $items = AtkItem::query()
            ->where('is_active', true)
            ->whereIn('id', $quantities->keys())
            ->get()
            ->keyBy('id');

        if ($items->count() !== $quantities->count()) {
            return back()->withErrors(['quantities' => 'Terdapat barang yang tidak aktif atau tidak ditemukan.'])->withInput();
        }

        $rows = $quantities->map(function (int $qty, int|string $itemId) use ($items): array {
            return ['item' => $items->get((int) $itemId), 'qty' => $qty];
        })->values();
        $user = User::query()->active()->findOrFail($validated['user_id']);
        $atkRequest = AtkRequest::createPending($user, $rows, $validated['notes'] ?? null);

        return redirect()
            ->route('v2.atk.admin.requests.show', $atkRequest)
            ->with('success', 'Pengambilan manual berhasil dibuat. Silakan review setiap item.');
    }

    public function show(AtkRequest $atkRequest)
    {
        $atkRequest->load('items.item', 'user');

        return view('atk.admin.requests.show', compact('atkRequest'));
    }

    public function approve(Request $request, AtkRequest $atkRequest)
    {
        // Guard UX cepat: jika sudah tidak pending, arahkan ke halaman dengan warning.
        // Cek otoritatif tetap di dalam transaksi dengan lock (cegah double-approve konkuren).
        if ($atkRequest->status !== AtkRequest::STATUS_PENDING) {
            return back()->with('warning', 'Pengajuan ini sudah diproses.');
        }

        $autoRejectedMessage = null;
        $approvedItemIds = [];

        try {
            DB::transaction(function () use ($request, $atkRequest, &$autoRejectedMessage, &$approvedItemIds): void {
                // Lock baris request untuk mencegah 2 admin approve paralel lolos bersamaan.
                $lockedRequest = AtkRequest::whereKey($atkRequest->id)->lockForUpdate()->firstOrFail();

                // Re-check status di dalam transaksi: bila sudah diproses transaksi lain, batal.
                if ($lockedRequest->status !== AtkRequest::STATUS_PENDING) {
                    throw new \RuntimeException('Pengajuan ini baru saja diproses.');
                }

                $lockedRequest->load('items');

                $lockedItems = [];
                $requiredQtyByItem = $lockedRequest->items
                    ->groupBy('atk_item_id')
                    ->map(fn ($items) => $items->sum('qty'));

                foreach ($requiredQtyByItem as $itemId => $requiredQty) {
                    $item = AtkItem::whereKey($itemId)->lockForUpdate()->firstOrFail();

                    if ($item->stock_qty < $requiredQty) {
                        $autoRejectedMessage = 'Pengajuan otomatis ditolak karena stok '.$item->name.' tidak cukup.';

                        $lockedRequest->update([
                            'status' => AtkRequest::STATUS_REJECTED,
                            'rejected_by' => $request->user()->id,
                            'rejected_at' => now(),
                            'admin_note' => $autoRejectedMessage,
                        ]);

                        return;
                    }

                    $lockedItems[$item->id] = $item;
                }

                foreach ($lockedRequest->items as $requestItem) {
                    $item = $lockedItems[$requestItem->atk_item_id];
                    $before = $item->stock_qty;
                    $after = $before - $requestItem->qty;

                    $item->update(['stock_qty' => $after]);
                    $item->stock_qty = $after;
                    $approvedItemIds[] = $item->id;

                    // Tandai item sebagai APPROVED agar konsisten dengan flow review per-item.
                    $requestItem->update([
                        'status' => AtkRequestItem::STATUS_APPROVED,
                        'reviewed_by' => $request->user()->id,
                        'reviewed_at' => now(),
                    ]);

                    AtkStockMovement::create([
                        'atk_item_id' => $item->id,
                        'movement_type' => AtkStockMovement::TYPE_OUT,
                        'qty' => $requestItem->qty,
                        'stock_before' => $before,
                        'stock_after' => $after,
                        'source_type' => AtkStockMovement::SOURCE_REQUEST,
                        'source_id' => $lockedRequest->id,
                        'notes' => 'Approve '.$lockedRequest->request_number,
                        'created_by' => $request->user()->id,
                    ]);
                }

                $lockedRequest->update([
                    'status' => AtkRequest::STATUS_APPROVED,
                    'approved_by' => $request->user()->id,
                    'approved_at' => now(),
                ]);
            });
        } catch (\RuntimeException $exception) {
            return redirect()
                ->route('v2.atk.admin.requests.show', $atkRequest)
                ->with('warning', $exception->getMessage());
        }

        if ($autoRejectedMessage !== null) {
            return redirect()
                ->route('v2.atk.admin.requests.show', $atkRequest)
                ->with('warning', $autoRejectedMessage);
        }

        $autoRejectedCount = $this->rejectPendingRequestsWithoutStock($approvedItemIds, $request->user()->id);
        $message = 'Pengajuan disetujui. Stok sudah dikurangi.';

        if ($autoRejectedCount > 0) {
            $message .= ' '.$autoRejectedCount.' pengajuan lain otomatis ditolak karena stok tidak cukup.';
        }

        return redirect()
            ->route('v2.atk.admin.requests.show', $atkRequest)
            ->with('success', $message);
    }

    private function rejectPendingRequestsWithoutStock(array $itemIds, int $adminId): int
    {
        $itemIds = array_values(array_unique($itemIds));

        if ($itemIds === []) {
            return 0;
        }

        $requestIds = AtkRequestItem::query()
            ->join('atk_items', 'atk_request_items.atk_item_id', '=', 'atk_items.id')
            ->join('atk_requests', 'atk_request_items.atk_request_id', '=', 'atk_requests.id')
            ->where('atk_requests.status', AtkRequest::STATUS_PENDING)
            ->whereIn('atk_request_items.atk_item_id', $itemIds)
            ->whereColumn('atk_request_items.qty', '>', 'atk_items.stock_qty')
            ->pluck('atk_request_items.atk_request_id')
            ->unique()
            ->values();

        if ($requestIds->isEmpty()) {
            return 0;
        }

        return AtkRequest::query()
            ->whereIn('id', $requestIds)
            ->where('status', AtkRequest::STATUS_PENDING)
            ->update([
                'status' => AtkRequest::STATUS_REJECTED,
                'rejected_by' => $adminId,
                'rejected_at' => now(),
                'admin_note' => 'Ditolak otomatis karena stok tidak cukup.',
            ]);
    }

    public function reject(Request $request, AtkRequest $atkRequest)
    {
        if ($atkRequest->status !== AtkRequest::STATUS_PENDING) {
            return back()->with('warning', 'Pengajuan ini sudah diproses.');
        }

        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:1000'],
        ]);

        try {
            DB::transaction(function () use ($validated, $request, $atkRequest): void {
                $lockedRequest = AtkRequest::whereKey($atkRequest->id)->lockForUpdate()->firstOrFail();

                if ($lockedRequest->status !== AtkRequest::STATUS_PENDING) {
                    throw new \RuntimeException('Pengajuan ini baru saja diproses.');
                }

                $lockedRequest->update([
                    'status' => AtkRequest::STATUS_REJECTED,
                    'rejected_by' => $request->user()->id,
                    'rejected_at' => now(),
                    'admin_note' => $validated['admin_note'],
                ]);

                // Tandai semua item sebagai REJECTED agar konsisten dengan flow review per-item.
                $lockedRequest->items()->update([
                    'status' => AtkRequestItem::STATUS_REJECTED,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                    'admin_note' => $validated['admin_note'],
                ]);
            });
        } catch (\RuntimeException $exception) {
            return redirect()
                ->route('v2.atk.admin.requests.show', $atkRequest)
                ->with('warning', $exception->getMessage());
        }

        return redirect()
            ->route('v2.atk.admin.requests.show', $atkRequest)
            ->with('success', 'Pengajuan ditolak.');
    }

    /**
     * Review satu item request: tandai APPROVED atau REJECTED.
     * Tidak mengurangi stok — stok hanya berkurang saat finalize().
     * Hanya boleh dipanggil saat parent request masih PENDING.
     */
    public function reviewItem(Request $request, AtkRequest $atkRequest, AtkRequestItem $requestItem)
    {
        if ((int) $requestItem->atk_request_id !== (int) $atkRequest->id) {
            return back()->with('warning', 'Item tidak termasuk dalam pengajuan ini.');
        }

        if ($atkRequest->status !== AtkRequest::STATUS_PENDING) {
            return back()->with('warning', 'Pengajuan ini sudah difinalisasi.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', [AtkRequestItem::STATUS_APPROVED, AtkRequestItem::STATUS_REJECTED])],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        // Catatan wajib saat item ditolak / tidak diproses.
        if ($validated['status'] === AtkRequestItem::STATUS_REJECTED && trim((string) $validated['admin_note']) === '') {
            return back()
                ->withErrors(['admin_note' => 'Alasan wajib diisi untuk item yang tidak diproses.'])
                ->withInput();
        }

        if ($validated['status'] === AtkRequestItem::STATUS_APPROVED) {
            $item = AtkItem::query()
                ->select(['id', 'name', 'stock_qty'])
                ->find($requestItem->atk_item_id);

            if (! $item || $item->stock_qty < $requestItem->qty) {
                $availableStock = $item?->stock_qty ?? 0;
                $itemName = $item?->name ?? $requestItem->item_name_snapshot;

                return back()->with('warning', 'Stok '.$itemName.' tidak cukup ('.$availableStock.' tersedia, '.$requestItem->qty.' diminta). Tandai item ini sebagai Tidak diproses atau tunggu restock.');
            }
        }

        $requestItem->update([
            'status' => $validated['status'],
            'admin_note' => $validated['status'] === AtkRequestItem::STATUS_REJECTED ? $validated['admin_note'] : null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $label = $validated['status'] === AtkRequestItem::STATUS_APPROVED ? 'disetujui' : 'ditandai tidak diproses';

        return redirect()
            ->route('v2.atk.admin.requests.show', $atkRequest)
            ->with('success', 'Item "'.$requestItem->item_name_snapshot.'" '.$label.'.');
    }

    /**
     * Finalisasi review: lock request + item, validasi semua item sudah direview,
     * kurangi stok hanya untuk item APPROVED, lalu update status header.
     */
    public function finalize(Request $request, AtkRequest $atkRequest)
    {
        if ($atkRequest->status !== AtkRequest::STATUS_PENDING) {
            return back()->with('warning', 'Pengajuan ini sudah difinalisasi.');
        }

        try {
            $finalStatus = DB::transaction(function () use ($request, $atkRequest): string {
                // Lock baris request — cegah 2 admin finalisasi paralel.
                $lockedRequest = AtkRequest::whereKey($atkRequest->id)->lockForUpdate()->firstOrFail();

                if ($lockedRequest->status !== AtkRequest::STATUS_PENDING) {
                    throw new \RuntimeException('Pengajuan ini baru saja difinalisasi.');
                }

                $lockedRequest->load('items');

                // Validasi: tidak boleh ada item yang masih PENDING.
                if ($lockedRequest->items->contains(fn ($item) => $item->status === AtkRequestItem::STATUS_PENDING)) {
                    throw new \RuntimeException('Masih ada item yang belum direview.');
                }

                $approvedItems = $lockedRequest->items->where('status', AtkRequestItem::STATUS_APPROVED);

                // Validasi stok untuk semua item approved sebelum mengurangi apapun.
                // Group by atk_item_id karena satu item bisa muncul beberapa baris.
                $requiredQtyByItem = $approvedItems
                    ->groupBy('atk_item_id')
                    ->map(fn ($items) => $items->sum('qty'));

                $lockedItems = [];
                foreach ($requiredQtyByItem as $itemId => $requiredQty) {
                    $item = AtkItem::whereKey($itemId)->lockForUpdate()->firstOrFail();

                    if ($item->stock_qty < $requiredQty) {
                        throw new \RuntimeException('Stok '.$item->name.' tidak cukup ('.$item->stock_qty.' tersedia, '.$requiredQty.' diminta).');
                    }

                    $lockedItems[$item->id] = $item;
                }

                // Semua validasi lolos — aman mengurangi stok untuk item approved.
                foreach ($approvedItems as $requestItem) {
                    $item = $lockedItems[$requestItem->atk_item_id];
                    $before = $item->stock_qty;
                    $after = $before - $requestItem->qty;

                    $item->update(['stock_qty' => $after]);
                    $item->stock_qty = $after;

                    AtkStockMovement::create([
                        'atk_item_id' => $item->id,
                        'movement_type' => AtkStockMovement::TYPE_OUT,
                        'qty' => $requestItem->qty,
                        'stock_before' => $before,
                        'stock_after' => $after,
                        'source_type' => AtkStockMovement::SOURCE_REQUEST,
                        'source_id' => $lockedRequest->id,
                        'notes' => 'Approve '.$lockedRequest->request_number,
                        'created_by' => $request->user()->id,
                    ]);
                }

                // Rangkum status header dari status item.
                $status = $lockedRequest->refreshStatusFromItems();

                $update = ['status' => $status];

                // Isi approved/rejected metadata sesuai hasil rekap.
                if ($approvedItems->isNotEmpty()) {
                    $update['approved_by'] = $request->user()->id;
                    $update['approved_at'] = now();
                } else {
                    // Semua rejected — catat sebagai rejection di header.
                    $update['rejected_by'] = $request->user()->id;
                    $update['rejected_at'] = now();
                }

                $lockedRequest->update($update);

                return $status;
            });
        } catch (\RuntimeException $exception) {
            return redirect()
                ->route('v2.atk.admin.requests.show', $atkRequest)
                ->with('warning', $exception->getMessage());
        }

        $message = match ($finalStatus) {
            AtkRequest::STATUS_APPROVED => 'Pengajuan disetujui. Stok sudah dikurangi.',
            AtkRequest::STATUS_REJECTED => 'Pengajuan ditolak. Semua item tidak diproses.',
            AtkRequest::STATUS_PARTIAL => 'Pengajuan selesai dengan hasil sebagian (PARTIAL). Stok hanya berkurang untuk item yang disetujui.',
            default => 'Pengajuan difinalisasi.',
        };

        return redirect()
            ->route('v2.atk.admin.requests.show', $atkRequest)
            ->with('success', $message);
    }
}
