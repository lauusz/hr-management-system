@php
    $statusLabels = ['PENDING'=>'Menunggu','APPROVED'=>'Disetujui','REJECTED'=>'Ditolak','PARTIAL'=>'Sebagian'];
    $itemStatusLabels = ['PENDING'=>'Menunggu review','APPROVED'=>'Disetujui','REJECTED'=>'Tidak diproses'];
    $pendingCount = $atkRequest->items->where('status', 'PENDING')->count();
    $approvedCount = $atkRequest->items->where('status', 'APPROVED')->count();
    $rejectedCount = $atkRequest->items->where('status', 'REJECTED')->count();
    $insufficientApprovedCount = $atkRequest->items
        ->filter(fn ($item) => ($item->status ?? 'PENDING') === 'APPROVED' && (($item->item?->stock_qty ?? 0) < $item->qty))
        ->count();
    $isPending = $atkRequest->status === 'PENDING';
@endphp
<x-ops-app title="Review Pengajuan OPS">
    <div class="ops-header ops-admin-review-header">
        <div>
            <h1 class="ops-title">{{ $atkRequest->request_number }}</h1>
            <p class="ops-subtitle">{{ $atkRequest->user_name_snapshot }} - {{ $atkRequest->pt_name_snapshot ?? '-' }}</p>
        </div>
        <span class="ops-badge ops-request-status-{{ strtolower($atkRequest->status) }}">{{ $statusLabels[$atkRequest->status] ?? $atkRequest->status }}</span>
    </div>

    <div class="ops-actions ops-admin-back-actions">
        <a class="ops-btn ops-btn-soft" href="{{ route('v2.ops.admin.requests.index') }}">Kembali ke Daftar</a>
    </div>
    @if($atkRequest->notes)
        <div class="ops-card ops-admin-note"><strong>Catatan Pengaju</strong><p>{{ $atkRequest->notes }}</p></div>
    @endif

    <div class="ops-card ops-admin-review-panel">
        <div class="ops-admin-review-table-wrap">
            <table class="ops-admin-review-table">
                <thead><tr><th>Barang</th><th>Qty</th><th>Stok Saat Ini</th><th>Status Item</th><th>{{ $isPending ? 'Aksi Review' : 'Keterangan Review' }}</th></tr></thead>
                <tbody>
                    @foreach($atkRequest->items as $requestItem)
                        @php
                            $stockQty = $requestItem->item?->stock_qty ?? 0;
                            $isInsufficient = $stockQty < $requestItem->qty;
                            $itemStatus = $requestItem->status ?? 'PENDING';
                            $reviewSummary = match (true) {
                                $itemStatus === 'APPROVED' && $isPending && $isInsufficient => 'Perlu ditinjau ulang, stok tidak cukup.',
                                $itemStatus === 'APPROVED' => 'Stok dikurangi saat finalisasi.',
                                $itemStatus === 'REJECTED' => 'Tidak mengurangi stok.',
                                default => 'Menunggu review admin.',
                            };
                        @endphp
                        <tr class="ops-admin-review-item">
                            <td data-label="Barang">
                                <strong>{{ $requestItem->item_name_snapshot }}</strong>
                                @if($requestItem->admin_note)<div class="ops-item-note">“{{ $requestItem->admin_note }}”</div>@endif
                            </td>
                            <td data-label="Jumlah">{{ $requestItem->qty }} {{ $requestItem->unit_name_snapshot }}</td>
                            <td data-label="Stok Saat Ini">{{ $stockQty }} {{ $requestItem->unit_name_snapshot }}</td>
                            <td data-label="Status Item"><span class="ops-badge ops-item-status-{{ strtolower($itemStatus) }}">{{ $itemStatusLabels[$itemStatus] ?? $itemStatus }}</span></td>
                            <td data-label="{{ $isPending ? 'Aksi Review' : 'Keterangan Review' }}">
                                @if($isPending && $itemStatus === 'PENDING')
                                    <div class="ops-item-actions">
                                        @if($isInsufficient)
                                            <button class="ops-btn ops-btn-soft ops-btn-sm" type="button" disabled>Stok tidak cukup</button>
                                        @else
                                            <form method="POST" action="{{ route('v2.ops.admin.requests.items.review', [$atkRequest, $requestItem]) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="APPROVED">
                                                <button class="ops-btn ops-btn-primary ops-btn-sm" type="submit">Setujui</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('v2.ops.admin.requests.items.review', [$atkRequest, $requestItem]) }}" class="ops-item-reject-form">
                                            @csrf
                                            <input type="hidden" name="status" value="REJECTED">
                                            <input class="ops-input ops-item-note-input" name="admin_note" placeholder="Alasan (wajib)" maxlength="1000" required>
                                            <button class="ops-btn ops-btn-soft ops-btn-sm" type="submit">Tidak Diproses</button>
                                        </form>
                                    </div>
                                    @if($isInsufficient)<div class="ops-inline-warning">Stok kurang. Tandai Tidak Diproses atau tunggu stok tersedia.</div>@endif
                                @else
                                    <div class="ops-review-note"><strong>{{ $reviewSummary }}</strong>@if($requestItem->reviewed_at)<span>Direview {{ $requestItem->reviewed_at->format('d M Y H:i') }}</span>@endif</div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="ops-review-summary">
            <span><small>Menunggu</small><b class="ops-badge ops-item-status-pending">{{ $pendingCount }}</b></span>
            <span><small>Disetujui</small><b class="ops-badge ops-item-status-approved">{{ $approvedCount }}</b></span>
            <span><small>Tidak diproses</small><b class="ops-badge ops-item-status-rejected">{{ $rejectedCount }}</b></span>
        </div>

        @if($isPending)
            <div class="ops-actions ops-admin-final-actions">
                <form method="POST" action="{{ route('v2.ops.admin.requests.reject', $atkRequest) }}" class="ops-admin-reject-all-form">
                    @csrf
                    <input class="ops-input" name="admin_note" placeholder="Alasan tolak semua (wajib)" required maxlength="1000">
                    <button class="ops-btn ops-btn-danger" type="submit">Tolak Semua</button>
                </form>
                <form method="POST" action="{{ route('v2.ops.admin.requests.finalize', $atkRequest) }}">
                    @csrf
                    <button class="ops-btn ops-btn-primary" type="submit" @disabled($pendingCount > 0 || $insufficientApprovedCount > 0)>Selesaikan Review</button>
                </form>
            </div>
            @if($pendingCount > 0 || $insufficientApprovedCount > 0)
                <p class="ops-finalize-hint">{{ $pendingCount > 0 ? $pendingCount.' barang belum direview.' : 'Ada barang disetujui yang stoknya tidak cukup.' }}</p>
            @endif
        @endif
    </div>

    @if($atkRequest->admin_note && !$isPending)
        <div class="ops-card ops-admin-note"><strong>Catatan Admin</strong><p>{{ $atkRequest->admin_note }}</p></div>
    @endif

    <style>
        .ops-admin-review-header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
        .ops-admin-back-actions { margin-bottom:14px; }
        .ops-admin-review-help,.ops-admin-note { margin-bottom:14px; }
        .ops-admin-note p { margin:6px 0 0; }
        .ops-admin-review-table-wrap { overflow-x:auto; border:1px solid var(--ops-border); border-radius:14px; }
        .ops-admin-review-table { width:100%; min-width:840px; border-collapse:collapse; font-size:12px; }
        .ops-admin-review-table th,.ops-admin-review-table td { padding:12px; border-bottom:1px solid var(--ops-border); text-align:left; vertical-align:top; }
        .ops-admin-review-table th { color:var(--ops-muted); font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
        .ops-admin-review-table tbody tr:last-child td { border-bottom:0; }
        .ops-request-status-approved,.ops-item-status-approved { color:var(--ops-dark); background:var(--ops-soft); }
        .ops-request-status-rejected,.ops-item-status-rejected { color:#991B1B; background:#FEE2E2; }
        .ops-request-status-pending,.ops-request-status-partial,.ops-item-status-pending { color:#92400E; background:#FEF3C7; }
        .ops-item-note { margin-top:4px; color:var(--ops-error); font-size:11px; font-style:italic; }
        .ops-item-actions,.ops-item-reject-form,.ops-admin-reject-all-form { display:flex; align-items:center; gap:6px; }
        .ops-item-note-input { width:180px; min-height:36px; }
        .ops-btn-sm { min-height:36px; padding:0 12px; }
        .ops-inline-warning { margin-top:6px; color:#B45309; font-size:11px; font-weight:700; }
        .ops-review-note { display:grid; gap:2px; min-width:160px; }
        .ops-review-note span { color:var(--ops-muted); font-size:11px; }
        .ops-review-summary { display:flex; flex-wrap:wrap; gap:16px; margin-top:12px; padding-top:12px; border-top:1px solid var(--ops-border); }
        .ops-review-summary span { display:inline-flex; align-items:center; gap:6px; }
        .ops-review-summary small { color:var(--ops-muted); font-weight:700; }
        .ops-admin-final-actions { justify-content:flex-end; margin-top:14px; }
        .ops-admin-reject-all-form .ops-input { width:240px; }
        .ops-finalize-hint { margin:8px 0 0; color:var(--ops-muted); font-size:11px; text-align:right; }
        @media (max-width:639px) {
            .ops-admin-review-header,.ops-admin-review-panel { padding:14px; border:1px solid var(--ops-border); border-radius:16px; background:#fff; }
            .ops-admin-review-header { align-items:flex-start; }
            .ops-admin-back-actions,.ops-admin-back-actions .ops-btn { width:100%; }
            .ops-admin-review-table-wrap { overflow:visible; border:0; }
            .ops-admin-review-table { display:block; min-width:0; }
            .ops-admin-review-table thead { display:none; }
            .ops-admin-review-table tbody { display:grid; gap:12px; }
            .ops-admin-review-item { display:block; padding:14px; border:1px solid var(--ops-border); border-radius:14px; }
            .ops-admin-review-item td { display:grid; grid-template-columns:minmax(90px,.7fr) minmax(0,1fr); gap:10px; padding:8px 0; border:0; }
            .ops-admin-review-item td::before { content:attr(data-label); color:var(--ops-muted); font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
            .ops-admin-review-item td:first-child,.ops-admin-review-item td:last-child { display:block; }
            .ops-admin-review-item td:first-child { padding-top:0; padding-bottom:12px; border-bottom:1px solid var(--ops-border); }
            .ops-admin-review-item td:first-child::before,.ops-admin-review-item td:last-child::before { display:block; margin-bottom:6px; }
            .ops-item-actions,.ops-item-reject-form { flex-direction:column; align-items:stretch; width:100%; }
            .ops-item-actions form,.ops-item-actions .ops-btn,.ops-item-note-input { width:100%; }
            .ops-review-summary { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:8px; }
            .ops-review-summary span { flex-direction:column; padding:8px 4px; border-radius:10px; background:var(--ops-soft); text-align:center; }
            .ops-admin-final-actions,.ops-admin-final-actions form,.ops-admin-final-actions .ops-input,.ops-admin-final-actions .ops-btn { width:100%; }
            .ops-admin-reject-all-form { display:grid; }
            .ops-finalize-hint { text-align:left; }
        }
    </style>
</x-ops-app>
