<?php

namespace App\Http\Controllers\Inventory\Pesona\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvPesonaItem;
use App\Models\InvPesonaRequest;
use App\Models\InvPesonaRequestItem;
use App\Models\InvPesonaStockMovement;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class RequestApprovalController extends Controller
{
    public function index(Request $request): View
    {
        if (is_string($request->input('q'))) {
            $request->merge(['q' => trim($request->input('q'))]);
        }
        $search = $request->validate(['q' => ['nullable', 'string', 'max:150']])['q'] ?? '';
        $status = $request->query('status');
        $status = in_array($status, ['PENDING', 'APPROVED', 'REJECTED', 'PARTIAL'], true) ? $status : '';
        $requests = InvPesonaRequest::query()
            ->withCount('items')
            ->with(['items:id,inv_pesona_request_id,item_name_snapshot,qty,unit_name_snapshot'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('request_number', 'like', '%'.$search.'%')
                        ->orWhere('user_name_snapshot', 'like', '%'.$search.'%')
                        ->orWhere('pt_name_snapshot', 'like', '%'.$search.'%');
                });
            })
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [InvPesonaRequest::STATUS_PENDING])
            ->orderByDesc('created_at')->orderByDesc('id')->paginate(15)
            ->appends(['q' => $search, 'status' => $status]);
        $hasRequests = $requests->total() > 0 || (($search !== '' || $status !== '') && InvPesonaRequest::query()->exists());

        return view('inventory.pesona.admin.requests.index', compact('requests', 'search', 'status', 'hasRequests'));
    }

    public function show(InvPesonaRequest $pesonaRequest): View
    {
        $pesonaRequest->load(['approvedBy:id,name', 'rejectedBy:id,name', 'items.item', 'items.reviewedBy:id,name']);

        return view('inventory.pesona.admin.requests.show', compact('pesonaRequest'));
    }

    public function review(Request $request, InvPesonaRequest $pesonaRequest, InvPesonaRequestItem $requestItem): RedirectResponse
    {
        abort_unless((string) $requestItem->inv_pesona_request_id === (string) $pesonaRequest->id, 404);
        if (is_string($request->input('admin_note'))) {
            $request->merge(['admin_note' => trim($request->input('admin_note'))]);
        }
        $data = $request->validate([
            'status' => ['required', 'in:APPROVED,REJECTED'],
            'admin_note' => ['nullable', 'string', 'max:1000', 'required_if:status,REJECTED'],
        ], ['admin_note.required_if' => 'Alasan penolakan wajib diisi.']);

        try {
            DB::transaction(function () use ($pesonaRequest, $requestItem, $request, $data): void {
                $header = InvPesonaRequest::query()->whereKey($pesonaRequest->id)->lockForUpdate()->firstOrFail();
                $this->ensurePending($header);
                $detail = $header->items()->whereKey($requestItem->id)->lockForUpdate()->firstOrFail();
                if ($data['status'] === InvPesonaRequestItem::STATUS_APPROVED) {
                    $item = InvPesonaItem::query()->whereKey($detail->inv_pesona_item_id)->lockForUpdate()->first();
                    $this->ensureAvailable($detail, $item, $detail->qty);
                }
                $detail->update([
                    'status' => $data['status'],
                    'admin_note' => $data['status'] === InvPesonaRequestItem::STATUS_REJECTED ? $data['admin_note'] : null,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                ]);
            });
        } catch (\Throwable $exception) {
            if ($exception instanceof ValidationException || $exception instanceof HttpExceptionInterface || $exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            report($exception);

            return back()->withInput($request->only('status', 'admin_note', 'review_form_id'))->with('error', 'Pemeriksaan gagal disimpan. Silakan coba lagi.');
        }

        return redirect()->route('inventory.pesona.admin.requests.show', $pesonaRequest)->with('success', 'Hasil pemeriksaan barang disimpan. Stok belum berubah.');
    }

    public function finalize(Request $request, InvPesonaRequest $pesonaRequest): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $pesonaRequest): void {
                $header = InvPesonaRequest::query()->whereKey($pesonaRequest->id)->lockForUpdate()->firstOrFail();
                $this->ensurePending($header);
                $details = $header->items()->orderBy('id')->lockForUpdate()->get();
                if ($details->isEmpty()) {
                    throw ValidationException::withMessages(['finalize' => 'Pengajuan tidak memiliki detail barang.']);
                }
                $max = min(PHP_INT_MAX, 4294967295);
                $totals = [];
                foreach ($details as $detail) {
                    if (! in_array($detail->status, [InvPesonaRequestItem::STATUS_APPROVED, InvPesonaRequestItem::STATUS_REJECTED], true)) {
                        throw ValidationException::withMessages(['finalize' => 'Semua barang harus diperiksa dan memiliki status yang valid.']);
                    }
                    if ($detail->qty < 1 || $detail->qty > $max) {
                        throw ValidationException::withMessages(['finalize' => 'Jumlah barang '.$detail->item_name_snapshot.' tidak valid.']);
                    }
                    if ($detail->status === InvPesonaRequestItem::STATUS_APPROVED) {
                        $id = $detail->inv_pesona_item_id;
                        $previous = $totals[$id] ?? 0;
                        if ($detail->qty > $max - $previous) {
                            throw ValidationException::withMessages(['finalize' => 'Jumlah total '.$detail->item_name_snapshot.' melebihi batas.']);
                        }
                        $totals[$id] = $previous + $detail->qty;
                    }
                }
                $ids = array_keys($totals);
                sort($ids, SORT_NUMERIC);
                $items = InvPesonaItem::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                foreach ($details as $detail) {
                    if ($detail->status === InvPesonaRequestItem::STATUS_APPROVED) {
                        $this->ensureAvailable($detail, $items->get($detail->inv_pesona_item_id), $totals[$detail->inv_pesona_item_id]);
                    }
                }
                foreach ($details as $detail) {
                    if ($detail->status !== InvPesonaRequestItem::STATUS_APPROVED) {
                        continue;
                    }
                    $item = $items->get($detail->inv_pesona_item_id);
                    $before = $item->stock_qty;
                    $item->stock_qty = $before - $detail->qty;
                    $item->save();
                    InvPesonaStockMovement::query()->create([
                        'inv_pesona_item_id' => $item->id, 'movement_type' => InvPesonaStockMovement::TYPE_OUT,
                        'qty' => $detail->qty, 'unit_price' => null, 'total_price' => null,
                        'stock_before' => $before, 'stock_after' => $item->stock_qty,
                        'source_type' => InvPesonaRequest::class, 'source_id' => $header->id,
                        'notes' => 'Pengajuan '.$header->request_number, 'created_by' => $request->user()->id,
                    ]);
                }
                $approved = $details->where('status', InvPesonaRequestItem::STATUS_APPROVED)->count();
                $status = $approved === 0 ? InvPesonaRequest::STATUS_REJECTED : ($approved === $details->count() ? InvPesonaRequest::STATUS_APPROVED : InvPesonaRequest::STATUS_PARTIAL);
                $header->update([
                    'status' => $status,
                    'approved_by' => $approved ? $request->user()->id : null,
                    'approved_at' => $approved ? now() : null,
                    'rejected_by' => $approved ? null : $request->user()->id,
                    'rejected_at' => $approved ? null : now(),
                ]);
            });
        } catch (\Throwable $exception) {
            if ($exception instanceof ValidationException || $exception instanceof HttpExceptionInterface || $exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            report($exception);

            return back()->with('error', 'Finalisasi gagal disimpan. Silakan coba lagi.');
        }

        return redirect()->route('inventory.pesona.admin.requests.show', $pesonaRequest)->with('success', 'Pengajuan berhasil difinalisasi.');
    }

    private function ensurePending(InvPesonaRequest $header): void
    {
        if ($header->status !== InvPesonaRequest::STATUS_PENDING) {
            throw ValidationException::withMessages(['finalize' => 'Pengajuan sudah diproses dan tidak dapat diubah.']);
        }
    }

    private function ensureAvailable(InvPesonaRequestItem $detail, ?InvPesonaItem $item, int $needed): void
    {
        if (! $item || $item->deleted_at !== null || ! $item->is_active) {
            throw ValidationException::withMessages(['item' => 'Barang '.$detail->item_name_snapshot.' tidak tersedia atau tidak aktif.']);
        }
        if ($item->unit_name !== $detail->unit_name_snapshot || $item->unit_size !== $detail->unit_size_snapshot || $item->content_unit_name !== $detail->content_unit_name_snapshot) {
            throw ValidationException::withMessages(['item' => 'Satuan barang '.$detail->item_name_snapshot.' tidak sesuai dengan pengajuan.']);
        }
        if ($needed < 1 || $needed > min(PHP_INT_MAX, 4294967295) || $item->stock_qty < $needed) {
            throw ValidationException::withMessages(['item' => 'Jumlah atau stok barang '.$detail->item_name_snapshot.' tidak mencukupi.']);
        }
    }
}
