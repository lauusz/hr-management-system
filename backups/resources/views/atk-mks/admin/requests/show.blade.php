@php
    $statusLabels = ['PENDING'=>'Menunggu','APPROVED'=>'Disetujui','REJECTED'=>'Ditolak','PARTIAL'=>'Sebagian'];
    $itemStatusLabels = ['PENDING'=>'Menunggu pemeriksaan','APPROVED'=>'Disetujui','REJECTED'=>'Tidak diproses'];
    $pendingCount = $atkRequest->items->where('status', 'PENDING')->count();
    $insufficientApprovedCount = $atkRequest->items
        ->filter(fn ($item) => ($item->status ?? 'PENDING') === 'APPROVED' && (($item->item?->stock_qty ?? 0) < $item->qty))
        ->count();
    $isPending = $atkRequest->status === 'PENDING';
@endphp
<x-atk-mks-app title="Periksa Pengajuan ATK MKS">
    <div class="atk-mks-header atk-mks-admin-review-header">
        <div>
            <h1 class="atk-mks-title">{{ $atkRequest->request_number }}</h1>
            <p class="atk-mks-subtitle">{{ $atkRequest->user_name_snapshot }} - {{ $atkRequest->pt_name_snapshot ?? '-' }}</p>
        </div>
        <span class="atk-mks-badge atk-mks-request-status-{{ strtolower($atkRequest->status) }}">{{ $statusLabels[$atkRequest->status] ?? $atkRequest->status }}</span>
    </div>

    <div class="atk-mks-actions atk-mks-admin-back-actions">
        <a class="atk-mks-btn atk-mks-btn-soft atk-mks-btn-sm" href="{{ route('v2.atk-mks.admin.requests.index') }}">Kembali</a>
    </div>
    @if($atkRequest->notes)
        <div class="atk-mks-card atk-mks-admin-note"><strong>Catatan Pengaju</strong><p>{{ $atkRequest->notes }}</p></div>
    @endif

    <div class="atk-mks-card atk-mks-admin-review-panel">
        <div class="atk-mks-admin-review-table-wrap">
            <table class="atk-mks-admin-review-table">
                <thead><tr><th>Barang</th><th>Jumlah</th><th>Stok Saat Ini</th><th>Status Barang</th><th>{{ $isPending ? 'Aksi Pemeriksaan' : 'Keterangan Pemeriksaan' }}</th></tr></thead>
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
                                default => 'Menunggu pemeriksaan admin.',
                            };
                        @endphp
                        <tr class="atk-mks-admin-review-item">
                            <td data-label="Barang">
                                <strong>{{ $requestItem->item_name_snapshot }}</strong>
                                @if($requestItem->admin_note)<div class="atk-mks-item-note">“{{ $requestItem->admin_note }}”</div>@endif
                            </td>
                            <td data-label="Jumlah">{{ $requestItem->qty }} {{ $requestItem->unit_name_snapshot }}</td>
                            <td data-label="Stok Saat Ini">{{ $stockQty }} {{ $requestItem->unit_name_snapshot }}</td>
                            <td data-label="Status Item"><span class="atk-mks-badge atk-mks-item-status-{{ strtolower($itemStatus) }}">{{ $itemStatusLabels[$itemStatus] ?? $itemStatus }}</span></td>
                            <td data-label="{{ $isPending ? 'Aksi Review' : 'Keterangan Review' }}">
                                @if($isPending && $itemStatus === 'PENDING')
                                    <div class="atk-mks-item-actions">
                                        @if($isInsufficient)
                                            <button class="atk-mks-btn atk-mks-btn-soft atk-mks-btn-sm" type="button" disabled>Stok tidak cukup</button>
                                        @else
                                            <form method="POST" action="{{ route('v2.atk-mks.admin.requests.items.review', [$atkRequest, $requestItem]) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="APPROVED">
                                                <button class="atk-mks-btn atk-mks-btn-primary atk-mks-btn-sm" type="submit">Setujui</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('v2.atk-mks.admin.requests.items.review', [$atkRequest, $requestItem]) }}" class="atk-mks-item-reject-form">
                                            @csrf
                                            <input type="hidden" name="status" value="REJECTED">
                                            <input class="atk-mks-input atk-mks-item-note-input" name="admin_note" placeholder="Alasan (wajib)" maxlength="1000" required>
                                            <button class="atk-mks-btn atk-mks-btn-soft atk-mks-btn-sm" type="submit">Tidak Diproses</button>
                                        </form>
                                    </div>
                                    @if($isInsufficient)<div class="atk-mks-inline-warning">Stok kurang. Tandai Tidak Diproses atau tunggu stok tersedia.</div>@endif
                                @else
                                    <div class="atk-mks-review-note"><strong>{{ $reviewSummary }}</strong>@if($requestItem->reviewed_at)<span>Direview {{ $requestItem->reviewed_at->format('d M Y H:i') }}</span>@endif</div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($isPending)
            <div class="atk-mks-actions atk-mks-admin-final-actions">
                <form method="POST" action="{{ route('v2.atk-mks.admin.requests.approve-all', $atkRequest) }}">
                    @csrf
                    <button class="atk-mks-btn atk-mks-btn-soft" type="submit" @disabled($pendingCount === 0)>Setujui Semua</button>
                </form>
                <form method="POST" action="{{ route('v2.atk-mks.admin.requests.reject', $atkRequest) }}" class="atk-mks-admin-reject-all-form">
                    @csrf
                    <input class="atk-mks-input" name="admin_note" placeholder="Alasan tolak semua (wajib)" required maxlength="1000">
                    <button class="atk-mks-btn atk-mks-btn-danger" type="submit">Tolak Semua</button>
                </form>
                <form method="POST" action="{{ route('v2.atk-mks.admin.requests.finalize', $atkRequest) }}">
                    @csrf
                    <button class="atk-mks-btn atk-mks-btn-primary" type="submit" @disabled($pendingCount > 0 || $insufficientApprovedCount > 0)>Selesaikan Pemeriksaan</button>
                </form>
            </div>
            @if($pendingCount > 0 || $insufficientApprovedCount > 0)
                <p class="atk-mks-finalize-hint">{{ $pendingCount > 0 ? $pendingCount.' barang belum diperiksa.' : 'Ada barang disetujui yang stoknya tidak cukup.' }}</p>
            @endif
        @endif
    </div>

    @if($atkRequest->admin_note && !$isPending)
        <div class="atk-mks-card atk-mks-admin-note"><strong>Catatan Admin</strong><p>{{ $atkRequest->admin_note }}</p></div>
    @endif

    <style>
        .atk-mks-admin-review-header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
        .atk-mks-admin-back-actions { margin-bottom:14px; }
        .atk-mks-admin-review-help,.atk-mks-admin-note { margin-bottom:14px; }
        .atk-mks-admin-note p { margin:6px 0 0; }
        .atk-mks-admin-review-table-wrap { overflow-x:auto; border:1px solid var(--atk-mks-border); border-radius:14px; }
        .atk-mks-admin-review-table { width:100%; min-width:840px; border-collapse:collapse; font-size:12px; }
        .atk-mks-admin-review-table th,.atk-mks-admin-review-table td { padding:12px; border-bottom:1px solid var(--atk-mks-border); text-align:left; vertical-align:top; }
        .atk-mks-admin-review-table th { color:var(--atk-mks-muted); font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
        .atk-mks-admin-review-table tbody tr:last-child td { border-bottom:0; }
        .atk-mks-request-status-approved,.atk-mks-item-status-approved { color:var(--atk-mks-dark); background:var(--atk-mks-soft); }
        .atk-mks-request-status-rejected,.atk-mks-item-status-rejected { color:#991B1B; background:#FEE2E2; }
        .atk-mks-request-status-pending,.atk-mks-request-status-partial,.atk-mks-item-status-pending { color:#92400E; background:#FEF3C7; }
        .atk-mks-item-note { margin-top:4px; color:var(--atk-mks-error); font-size:11px; font-style:italic; }
        .atk-mks-item-actions,.atk-mks-item-reject-form,.atk-mks-admin-reject-all-form { display:flex; align-items:center; gap:6px; }
        .atk-mks-item-note-input { width:180px; min-height:36px; }
        .atk-mks-btn-sm { min-height:36px; padding:0 12px; }
        .atk-mks-inline-warning { margin-top:6px; color:#B45309; font-size:11px; font-weight:700; }
        .atk-mks-review-note { display:grid; gap:2px; min-width:160px; }
        .atk-mks-review-note span { color:var(--atk-mks-muted); font-size:11px; }
        .atk-mks-admin-final-actions { justify-content:flex-end; margin-top:14px; }
        .atk-mks-admin-reject-all-form .atk-mks-input { width:240px; }
        .atk-mks-finalize-hint { margin:8px 0 0; color:var(--atk-mks-muted); font-size:11px; text-align:right; }
        @media (max-width:639px) {
            .atk-mks-admin-review-panel { padding:14px; border:1px solid var(--atk-mks-border); border-radius:16px; background:#fff; }
            .atk-mks-admin-review-header { align-items:flex-start; }
            .atk-mks-admin-review-table-wrap { overflow:visible; border:0; }
            .atk-mks-admin-review-table { display:block; min-width:0; }
            .atk-mks-admin-review-table thead { display:none; }
            .atk-mks-admin-review-table tbody { display:grid; gap:12px; }
            .atk-mks-admin-review-item { display:block; padding:14px; border:1px solid var(--atk-mks-border); border-radius:14px; }
            .atk-mks-admin-review-item td { display:grid; grid-template-columns:minmax(90px,.7fr) minmax(0,1fr); gap:10px; padding:8px 0; border:0; }
            .atk-mks-admin-review-item td::before { content:attr(data-label); color:var(--atk-mks-muted); font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
            .atk-mks-admin-review-item td:first-child,.atk-mks-admin-review-item td:last-child { display:block; }
            .atk-mks-admin-review-item td:first-child { padding-top:0; padding-bottom:12px; border-bottom:1px solid var(--atk-mks-border); }
            .atk-mks-admin-review-item td:first-child::before,.atk-mks-admin-review-item td:last-child::before { display:block; margin-bottom:6px; }
            .atk-mks-item-actions,.atk-mks-item-reject-form { flex-direction:column; align-items:stretch; width:100%; }
            .atk-mks-item-actions form,.atk-mks-item-actions .atk-mks-btn,.atk-mks-item-note-input { width:100%; }
            .atk-mks-admin-final-actions,.atk-mks-admin-final-actions form,.atk-mks-admin-final-actions .atk-mks-input,.atk-mks-admin-final-actions .atk-mks-btn { width:100%; }
            .atk-mks-admin-reject-all-form { display:grid; }
            .atk-mks-finalize-hint { text-align:left; }
        }
    </style>
</x-atk-mks-app>


