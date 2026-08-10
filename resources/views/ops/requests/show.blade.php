@php
    $statusLabels = ['PENDING' => 'Menunggu', 'APPROVED' => 'Disetujui', 'PARTIAL' => 'Sebagian', 'REJECTED' => 'Ditolak'];
    $statusClasses = ['APPROVED' => 'success', 'REJECTED' => 'error'];
    $itemStatusLabels = ['PENDING' => 'Menunggu pemeriksaan', 'APPROVED' => 'Disetujui', 'REJECTED' => 'Tidak diproses'];
@endphp

<x-ops-app title="Detail Pengajuan OPS">
    <div class="ops-request-summary">
        <div>
            <h1 class="ops-title">{{ $atkRequest->request_number }}</h1>
            <div class="ops-request-summary-meta">
                <span>{{ $atkRequest->pt_name_snapshot ?? '-' }}</span>
                <span>{{ $atkRequest->user_name_snapshot }}</span>
                <span>{{ $atkRequest->created_at?->format('d/m/Y H:i') }}</span>
            </div>
        </div>
        <span class="ops-request-badge ops-request-badge-{{ $statusClasses[$atkRequest->status] ?? 'warning' }}">{{ $statusLabels[$atkRequest->status] ?? $atkRequest->status }}</span>
    </div>

    @if($atkRequest->status === 'APPROVED')
        <div class="ops-alert ops-alert-success">Pengajuan disetujui. Silakan ambil barang.</div>
    @elseif($atkRequest->status === 'PARTIAL')
        <div class="ops-alert ops-alert-warning">Sebagian barang disetujui. Lihat status setiap barang.</div>
    @endif

    @if($atkRequest->notes)
        <div class="ops-card ops-request-notes">
            <div class="ops-request-note-label">Catatan Pengaju</div>
            <div>{{ $atkRequest->notes }}</div>
        </div>
    @endif

    <div class="ops-request-item-mobile-list">
        @foreach($atkRequest->items as $item)
            @php($itemStatus = $item->status ?? 'PENDING')
            <article class="ops-request-item-card">
                <div class="ops-request-item-top">
                    <strong>{{ $item->item_name_snapshot }}</strong>
                    <span class="ops-request-badge ops-request-badge-{{ $statusClasses[$itemStatus] ?? 'warning' }}">{{ $itemStatusLabels[$itemStatus] ?? $itemStatus }}</span>
                </div>
                <dl class="ops-request-item-meta">
                    <div>
                        <dt>Jumlah</dt>
                        <dd>{{ $item->qty }} {{ $item->unit_name_snapshot }}</dd>
                    </div>
                </dl>
                @if($item->admin_note)
                    <div class="ops-request-item-note">“{{ $item->admin_note }}”</div>
                @endif
            </article>
        @endforeach
    </div>

    <div class="ops-request-table-wrap ops-request-item-desktop-table">
        <table class="ops-request-table">
            <thead>
                <tr><th>Barang</th><th>Jumlah</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach($atkRequest->items as $item)
                    @php($itemStatus = $item->status ?? 'PENDING')
                    <tr>
                        <td>
                            <strong>{{ $item->item_name_snapshot }}</strong>
                            @if($item->admin_note)
                                <div class="ops-request-item-note">“{{ $item->admin_note }}”</div>
                            @endif
                        </td>
                        <td>{{ $item->qty }} {{ $item->unit_name_snapshot }}</td>
                        <td><span class="ops-request-badge ops-request-badge-{{ $statusClasses[$itemStatus] ?? 'warning' }}">{{ $itemStatusLabels[$itemStatus] ?? $itemStatus }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <style>
        .ops-request-summary { display:flex; flex-direction:column; align-items:flex-start; justify-content:space-between; gap:14px; margin-bottom:16px; padding:16px; border:1px solid var(--ops-border); border-radius:16px; background:#fff; box-shadow:0 1px 3px rgba(17,24,39,.04); }
        .ops-request-summary-meta { display:flex; flex-wrap:wrap; gap:5px 12px; margin-top:8px; color:var(--ops-muted); font-size:11px; font-weight:600; }
        .ops-request-summary-meta span + span::before { content:'•'; margin-right:8px; color:#D1D5DB; }
        .ops-request-badge { display:inline-flex; padding:5px 9px; border-radius:999px; font-size:10px; font-weight:800; white-space:nowrap; }
        .ops-request-badge-success { color:#15803D; background:rgba(34,197,94,.12); }
        .ops-request-badge-warning { color:#B45309; background:rgba(245,158,11,.13); }
        .ops-request-badge-error { color:#B91C1C; background:rgba(239,68,68,.12); }
        .ops-request-notes { margin-bottom:14px; }
        .ops-request-note-label { margin-bottom:5px; color:var(--ops-muted); font-size:11px; font-weight:700; }
        .ops-request-item-mobile-list { display:grid; gap:12px; }
        .ops-request-item-card { padding:16px; border:1px solid var(--ops-border); border-radius:16px; background:#fff; box-shadow:0 1px 3px rgba(17,24,39,.04); }
        .ops-request-item-top { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; padding-bottom:12px; border-bottom:1px solid #EDF4F2; }
        .ops-request-item-top strong { min-width:0; font-size:14px; line-height:1.45; overflow-wrap:anywhere; }
        .ops-request-item-meta { display:grid; margin:14px 0 0; }
        .ops-request-item-meta dt { color:var(--ops-muted); font-size:10px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; }
        .ops-request-item-meta dd { margin:4px 0 0; font-size:13px; font-weight:800; }
        .ops-request-item-note { margin-top:12px; padding-top:12px; border-top:1px solid #EDF4F2; color:var(--ops-error); font-size:11px; font-style:italic; line-height:1.4; }
        .ops-request-item-desktop-table { display:none; }
        .ops-request-table-wrap { overflow-x:auto; border:1px solid var(--ops-border); border-radius:16px; background:#fff; }
        .ops-request-table { width:100%; min-width:620px; border-collapse:collapse; }
        .ops-request-table th,.ops-request-table td { padding:12px 14px; border-bottom:1px solid var(--ops-border); text-align:left; font-size:13px; }
        .ops-request-table th { color:var(--ops-muted); background:#F4FBF8; font-size:11px; letter-spacing:.04em; text-transform:uppercase; }
        .ops-request-table tr:last-child td { border-bottom:0; }

        @media (min-width:640px) {
            .ops-request-summary { flex-direction:row; align-items:center; padding:0; border:0; border-radius:0; background:transparent; box-shadow:none; }
            .ops-request-item-mobile-list { display:none; }
            .ops-request-item-desktop-table { display:block; }
            .ops-request-item-desktop-table .ops-request-item-note { margin-top:4px; padding-top:0; border-top:0; }
        }
    </style>
</x-ops-app>
