@php
    $statusLabels = ['PENDING' => 'Menunggu', 'APPROVED' => 'Disetujui', 'PARTIAL' => 'Sebagian', 'REJECTED' => 'Ditolak'];
    $statusClasses = ['APPROVED' => 'success', 'REJECTED' => 'error'];
    $itemStatusLabels = ['PENDING' => 'Menunggu pemeriksaan', 'APPROVED' => 'Disetujui', 'REJECTED' => 'Tidak diproses'];
@endphp

<x-atk-mks-app title="Detail Pengajuan ATK MKS">
    <div class="atk-mks-request-summary">
        <div>
            <h1 class="atk-mks-title">{{ $atkRequest->request_number }}</h1>
            <div class="atk-mks-request-summary-meta">
                <span>{{ $atkRequest->pt_name_snapshot ?? '-' }}</span>
                <span>{{ $atkRequest->user_name_snapshot }}</span>
                <span>{{ $atkRequest->created_at?->format('d/m/Y H:i') }}</span>
            </div>
        </div>
        <span class="atk-mks-request-badge atk-mks-request-badge-{{ $statusClasses[$atkRequest->status] ?? 'warning' }}">{{ $statusLabels[$atkRequest->status] ?? $atkRequest->status }}</span>
    </div>

    @if($atkRequest->status === 'APPROVED')
        <div class="atk-mks-alert atk-mks-alert-success">Pengajuan disetujui. Silakan ambil barang.</div>
    @elseif($atkRequest->status === 'PARTIAL')
        <div class="atk-mks-alert atk-mks-alert-warning">Sebagian barang disetujui. Lihat status setiap barang.</div>
    @endif

    @if($atkRequest->notes)
        <div class="atk-mks-card atk-mks-request-notes">
            <div class="atk-mks-request-note-label">Catatan Pengaju</div>
            <div>{{ $atkRequest->notes }}</div>
        </div>
    @endif

    <div class="atk-mks-request-item-mobile-list">
        @foreach($atkRequest->items as $item)
            @php($itemStatus = $item->status ?? 'PENDING')
            <article class="atk-mks-request-item-card">
                <div class="atk-mks-request-item-top">
                    <strong>{{ $item->item_name_snapshot }}</strong>
                    <span class="atk-mks-request-badge atk-mks-request-badge-{{ $statusClasses[$itemStatus] ?? 'warning' }}">{{ $itemStatusLabels[$itemStatus] ?? $itemStatus }}</span>
                </div>
                <dl class="atk-mks-request-item-meta">
                    <div>
                        <dt>Jumlah</dt>
                        <dd>{{ $item->qty }} {{ $item->unit_name_snapshot }}</dd>
                    </div>
                </dl>
                @if($item->admin_note)
                    <div class="atk-mks-request-item-note">“{{ $item->admin_note }}”</div>
                @endif
            </article>
        @endforeach
    </div>

    <div class="atk-mks-request-table-wrap atk-mks-request-item-desktop-table">
        <table class="atk-mks-request-table">
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
                                <div class="atk-mks-request-item-note">“{{ $item->admin_note }}”</div>
                            @endif
                        </td>
                        <td>{{ $item->qty }} {{ $item->unit_name_snapshot }}</td>
                        <td><span class="atk-mks-request-badge atk-mks-request-badge-{{ $statusClasses[$itemStatus] ?? 'warning' }}">{{ $itemStatusLabels[$itemStatus] ?? $itemStatus }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <style>
        .atk-mks-request-summary { display:flex; flex-direction:column; align-items:flex-start; justify-content:space-between; gap:14px; margin-bottom:16px; padding:16px; border:1px solid var(--atk-mks-border); border-radius:16px; background:#fff; box-shadow:0 1px 3px rgba(17,24,39,.04); }
        .atk-mks-request-summary-meta { display:flex; flex-wrap:wrap; gap:5px 12px; margin-top:8px; color:var(--atk-mks-muted); font-size:11px; font-weight:600; }
        .atk-mks-request-summary-meta span + span::before { content:'•'; margin-right:8px; color:#D1D5DB; }
        .atk-mks-request-badge { display:inline-flex; padding:5px 9px; border-radius:999px; font-size:10px; font-weight:800; white-space:nowrap; }
        .atk-mks-request-badge-success { color:#15803D; background:rgba(34,197,94,.12); }
        .atk-mks-request-badge-warning { color:#B45309; background:rgba(245,158,11,.13); }
        .atk-mks-request-badge-error { color:#B91C1C; background:rgba(239,68,68,.12); }
        .atk-mks-request-notes { margin-bottom:14px; }
        .atk-mks-request-note-label { margin-bottom:5px; color:var(--atk-mks-muted); font-size:11px; font-weight:700; }
        .atk-mks-request-item-mobile-list { display:grid; gap:12px; }
        .atk-mks-request-item-card { padding:16px; border:1px solid var(--atk-mks-border); border-radius:16px; background:#fff; box-shadow:0 1px 3px rgba(17,24,39,.04); }
        .atk-mks-request-item-top { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; padding-bottom:12px; border-bottom:1px solid #EDF4F2; }
        .atk-mks-request-item-top strong { min-width:0; font-size:14px; line-height:1.45; overflow-wrap:anywhere; }
        .atk-mks-request-item-meta { display:grid; margin:14px 0 0; }
        .atk-mks-request-item-meta dt { color:var(--atk-mks-muted); font-size:10px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; }
        .atk-mks-request-item-meta dd { margin:4px 0 0; font-size:13px; font-weight:800; }
        .atk-mks-request-item-note { margin-top:12px; padding-top:12px; border-top:1px solid #EDF4F2; color:var(--atk-mks-error); font-size:11px; font-style:italic; line-height:1.4; }
        .atk-mks-request-item-desktop-table { display:none; }
        .atk-mks-request-table-wrap { overflow-x:auto; border:1px solid var(--atk-mks-border); border-radius:16px; background:#fff; }
        .atk-mks-request-table { width:100%; min-width:620px; border-collapse:collapse; }
        .atk-mks-request-table th,.atk-mks-request-table td { padding:12px 14px; border-bottom:1px solid var(--atk-mks-border); text-align:left; font-size:13px; }
        .atk-mks-request-table th { color:var(--atk-mks-muted); background:#F4FBF8; font-size:11px; letter-spacing:.04em; text-transform:uppercase; }
        .atk-mks-request-table tr:last-child td { border-bottom:0; }

        @media (min-width:640px) {
            .atk-mks-request-summary { flex-direction:row; align-items:center; padding:0; border:0; border-radius:0; background:transparent; box-shadow:none; }
            .atk-mks-request-item-mobile-list { display:none; }
            .atk-mks-request-item-desktop-table { display:block; }
            .atk-mks-request-item-desktop-table .atk-mks-request-item-note { margin-top:4px; padding-top:0; border-top:0; }
        }
    </style>
</x-atk-mks-app>


