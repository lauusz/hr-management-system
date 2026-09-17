<?php

namespace App\Http\Controllers\Ops\Admin;

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
        $requests = AtkRequest::query()
            ->forModule(AtkRequest::MODULE_OPS)
            ->with('items')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $keyword = '%'.$request->string('q').'%';
                $query->where(fn ($query) => $query->where('request_number', 'like', $keyword)
                    ->orWhere('user_name_snapshot', 'like', $keyword)
                    ->orWhere('pt_name_snapshot', 'like', $keyword));
            })
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [AtkRequest::STATUS_PENDING])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('ops.admin.requests.index', compact('requests'));
    }

    public function createManual()
    {
        $users = User::query()->active()->with('profile.pt')->orderBy('name')->get();
        $items = AtkItem::query()->forModule(AtkItem::MODULE_OPS)->available()->orderBy('name')->get();

        return view('ops.admin.requests.manual-create', compact('users', 'items'));
    }

    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')->where('status', User::STATUS_ACTIVE)],
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

        $items = AtkItem::query()->forModule(AtkItem::MODULE_OPS)->available()
            ->whereIn('id', $quantities->keys())->get()->keyBy('id');

        if ($items->count() !== $quantities->count()) {
            return back()->withErrors(['quantities' => 'Terdapat barang yang tidak aktif atau tidak ditemukan.'])->withInput();
        }

        $rows = $quantities->map(fn (int $qty, int|string $itemId) => [
            'item' => $items->get((int) $itemId),
            'qty' => $qty,
        ])->values();
        $user = User::query()->active()->findOrFail($validated['user_id']);
        $opsRequest = AtkRequest::createPending($user, $rows, $validated['notes'] ?? null, AtkRequest::MODULE_OPS);

        return redirect()->route('v2.ops.admin.requests.show', $opsRequest)
            ->with('success', 'Pengambilan manual berhasil dibuat. Silakan periksa setiap barang.');
    }

    public function show(AtkRequest $atkRequest)
    {
        $this->ensureOpsRequest($atkRequest);
        $atkRequest->load('items.item', 'user');

        return view('ops.admin.requests.show', compact('atkRequest'));
    }

    public function reviewItem(Request $request, AtkRequest $atkRequest, AtkRequestItem $requestItem)
    {
        $this->ensureOpsRequest($atkRequest);
        abort_unless($requestItem->atk_request_id === $atkRequest->id, 404);

        if ($atkRequest->status !== AtkRequest::STATUS_PENDING) {
            return back()->with('warning', 'Pengajuan sudah selesai.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:'.AtkRequestItem::STATUS_APPROVED.','.AtkRequestItem::STATUS_REJECTED],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['status'] === AtkRequestItem::STATUS_REJECTED && trim((string) ($validated['admin_note'] ?? '')) === '') {
            return back()->withErrors(['admin_note' => 'Alasan wajib diisi.']);
        }

        if ($validated['status'] === AtkRequestItem::STATUS_APPROVED) {
            $item = AtkItem::query()->forModule(AtkItem::MODULE_OPS)->available()->find($requestItem->atk_item_id);

            if (! $item || $item->stock_qty < $requestItem->qty) {
                return back()->with('warning', 'Stok tidak cukup.');
            }
        }

        $requestItem->update([
            'status' => $validated['status'],
            'admin_note' => $validated['status'] === AtkRequestItem::STATUS_REJECTED ? $validated['admin_note'] : null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', $validated['status'] === AtkRequestItem::STATUS_APPROVED ? 'Barang disetujui.' : 'Barang ditolak.');
    }

    public function reject(Request $request, AtkRequest $atkRequest)
    {
        $this->ensureOpsRequest($atkRequest);

        if ($atkRequest->status !== AtkRequest::STATUS_PENDING) {
            return back()->with('warning', 'Pengajuan sudah selesai.');
        }

        $validated = $request->validate(['admin_note' => ['required', 'string', 'max:1000']]);

        try {
            DB::transaction(function () use ($validated, $request, $atkRequest): void {
                $lockedRequest = AtkRequest::query()->forModule(AtkRequest::MODULE_OPS)
                    ->whereKey($atkRequest->id)->lockForUpdate()->firstOrFail();

                if ($lockedRequest->status !== AtkRequest::STATUS_PENDING) {
                    throw new \RuntimeException('Pengajuan baru saja diselesaikan.');
                }

                $lockedRequest->update([
                    'status' => AtkRequest::STATUS_REJECTED,
                    'rejected_by' => $request->user()->id,
                    'rejected_at' => now(),
                    'admin_note' => $validated['admin_note'],
                ]);
                $lockedRequest->items()->update([
                    'status' => AtkRequestItem::STATUS_REJECTED,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                    'admin_note' => $validated['admin_note'],
                ]);
            });
        } catch (\RuntimeException $exception) {
            return redirect()->route('v2.ops.admin.requests.show', $atkRequest)->with('warning', $exception->getMessage());
        }

        return redirect()->route('v2.ops.admin.requests.show', $atkRequest)->with('success', 'Pengajuan ditolak.');
    }

    public function approveAll(Request $request, AtkRequest $atkRequest)
    {
        $this->ensureOpsRequest($atkRequest);

        if ($atkRequest->status !== AtkRequest::STATUS_PENDING) {
            return back()->with('warning', 'Pengajuan sudah selesai.');
        }

        try {
            DB::transaction(function () use ($request, $atkRequest): void {
                $lockedRequest = AtkRequest::query()->forModule(AtkRequest::MODULE_OPS)
                    ->whereKey($atkRequest->id)->lockForUpdate()->firstOrFail();

                if ($lockedRequest->status !== AtkRequest::STATUS_PENDING) {
                    throw new \RuntimeException('Pengajuan baru saja diselesaikan.');
                }

                $lockedRequest->load('items');
                $required = $lockedRequest->items
                    ->whereIn('status', [AtkRequestItem::STATUS_PENDING, AtkRequestItem::STATUS_APPROVED])
                    ->groupBy('atk_item_id')
                    ->map(fn ($rows) => $rows->sum('qty'));

                foreach ($required as $itemId => $qty) {
                    $item = AtkItem::query()->forModule(AtkItem::MODULE_OPS)->available()
                        ->whereKey($itemId)->lockForUpdate()->first();

                    if (! $item || $item->stock_qty < $qty) {
                        throw new \RuntimeException('Ada barang dengan stok tidak cukup.');
                    }
                }

                $lockedRequest->items()->where('status', AtkRequestItem::STATUS_PENDING)->update([
                    'status' => AtkRequestItem::STATUS_APPROVED,
                    'admin_note' => null,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                ]);
            });
        } catch (\RuntimeException $exception) {
            return redirect()->route('v2.ops.admin.requests.show', $atkRequest)->with('warning', $exception->getMessage());
        }

        return redirect()->route('v2.ops.admin.requests.show', $atkRequest)
            ->with('success', 'Semua barang disetujui. Selesaikan pemeriksaan untuk menyimpan.');
    }

    public function finalize(Request $request, AtkRequest $atkRequest)
    {
        $this->ensureOpsRequest($atkRequest);

        if ($atkRequest->status !== AtkRequest::STATUS_PENDING) {
            return back()->with('warning', 'Pengajuan sudah selesai.');
        }

        try {
            $status = DB::transaction(function () use ($request, $atkRequest): string {
                $lockedRequest = AtkRequest::query()->forModule(AtkRequest::MODULE_OPS)
                    ->whereKey($atkRequest->id)->lockForUpdate()->firstOrFail();

                if ($lockedRequest->status !== AtkRequest::STATUS_PENDING) {
                    throw new \RuntimeException('Pengajuan baru saja diselesaikan.');
                }

                $lockedRequest->load('items');
                if ($lockedRequest->items->contains(fn ($item) => $item->status === AtkRequestItem::STATUS_PENDING)) {
                    throw new \RuntimeException('Masih ada barang yang belum diperiksa.');
                }

                $approvedRows = $lockedRequest->items->where('status', AtkRequestItem::STATUS_APPROVED);
                $required = $approvedRows->groupBy('atk_item_id')->map(fn ($rows) => $rows->sum('qty'));
                $lockedItems = [];

                foreach ($required as $itemId => $qty) {
                    $item = AtkItem::query()->forModule(AtkItem::MODULE_OPS)->available()
                        ->whereKey($itemId)->lockForUpdate()->firstOrFail();
                    if ($item->stock_qty < $qty) {
                        throw new \RuntimeException('Stok '.$item->name.' tidak cukup.');
                    }
                    $lockedItems[$item->id] = $item;
                }

                foreach ($approvedRows as $row) {
                    $item = $lockedItems[$row->atk_item_id];
                    $before = $item->stock_qty;
                    $after = $before - $row->qty;
                    $item->update(['stock_qty' => $after]);
                    $item->stock_qty = $after;
                    AtkStockMovement::create([
                        'atk_item_id' => $item->id,
                        'movement_type' => AtkStockMovement::TYPE_OUT,
                        'qty' => $row->qty,
                        'stock_before' => $before,
                        'stock_after' => $after,
                        'source_type' => AtkStockMovement::SOURCE_REQUEST,
                        'source_id' => $lockedRequest->id,
                        'notes' => 'Pengajuan '.$lockedRequest->request_number,
                        'created_by' => $request->user()->id,
                    ]);
                }

                $status = $lockedRequest->refreshStatusFromItems();
                $update = ['status' => $status];
                if ($approvedRows->isNotEmpty()) {
                    $update += ['approved_by' => $request->user()->id, 'approved_at' => now()];
                } else {
                    $update += ['rejected_by' => $request->user()->id, 'rejected_at' => now()];
                }
                $lockedRequest->update($update);

                return $status;
            });
        } catch (\RuntimeException $exception) {
            return back()->with('warning', $exception->getMessage());
        }

        $message = match ($status) {
            AtkRequest::STATUS_APPROVED => 'Pengajuan disetujui.',
            AtkRequest::STATUS_REJECTED => 'Pengajuan ditolak.',
            AtkRequest::STATUS_PARTIAL => 'Pengajuan selesai sebagian.',
            default => 'Pengajuan selesai.',
        };

        return back()->with('success', $message);
    }

    private function ensureOpsRequest(AtkRequest $atkRequest): void
    {
        abort_unless($atkRequest->module === AtkRequest::MODULE_OPS, 404);
    }
}
