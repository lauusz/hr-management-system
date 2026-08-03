<?php

namespace App\Http\Controllers\Ops\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtkItem;
use App\Models\AtkRequest;
use App\Models\AtkRequestItem;
use App\Models\AtkStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                    ->orWhere('user_name_snapshot', 'like', $keyword));
            })
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [AtkRequest::STATUS_PENDING])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('ops.admin.requests.index', compact('requests'));
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
