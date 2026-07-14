@php
    $statusBadgeMap = [
        'PENDING' => 'warning',
        'APPROVED' => 'success',
        'REJECTED' => 'error',
        'PARTIAL' => 'warning',
    ];
    $itemStatusBadgeMap = [
        'PENDING' => 'warning',
        'APPROVED' => 'success',
        'REJECTED' => 'error',
    ];
    $itemStatusLabel = [
        'PENDING' => 'Menunggu review',
        'APPROVED' => 'Disetujui',
        'REJECTED' => 'Tidak diproses',
    ];
@endphp
<x-atk-app title="Detail Pengajuan ATK">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">{{ $atkRequest->request_number }}</h1>
            <p class="atk-subtitle">{{ $atkRequest->pt_name_snapshot ?? '-' }} - {{ $atkRequest->user_name_snapshot }}</p>
        </div>
        <span class="atk-badge atk-badge-{{ $statusBadgeMap[$atkRequest->status] ?? 'warning' }}">{{ $atkRequest->status }}</span>
    </div>
    @if($atkRequest->status === 'APPROVED')
        <div class="atk-alert atk-alert-success">Pengajuan disetujui. Silakan ambil barang.</div>
    @elseif($atkRequest->status === 'PARTIAL')
        <div class="atk-alert atk-alert-warning">Pengajuan selesai sebagian. Sebagian barang tidak diproses — lihat detail tiap item.</div>
    @endif

    @if($atkRequest->notes)
        <div class="atk-card" style="margin-bottom:14px">
            <div class="atk-product-meta">Catatan Pengaju</div>
            <div>{{ $atkRequest->notes }}</div>
        </div>
    @endif

    <div class="atk-table-wrap">
        <table class="atk-table">
            <thead><tr><th>Barang</th><th>Qty</th><th>Setara</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($atkRequest->items as $item)
                    @php($itemStatus = $item->status ?? 'PENDING')
                    <tr>
                        <td>
                            <strong>{{ $item->item_name_snapshot }}</strong>
                            @if($item->admin_note)
                                <div class="atk-item-note">“{{ $item->admin_note }}”</div>
                            @endif
                        </td>
                        <td>{{ $item->qty }} {{ $item->unit_name_snapshot }}</td>
                        <td>{{ $item->qty * $item->unit_size_snapshot }} {{ $item->content_unit_name_snapshot }}</td>
                        <td>
                            <span class="atk-badge atk-badge-{{ $itemStatusBadgeMap[$itemStatus] ?? 'neutral' }}">{{ $itemStatusLabel[$itemStatus] ?? $itemStatus }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <style>
        .atk-item-note {
            margin-top: 4px;
            font-size: 11px;
            color: var(--error);
            font-style: italic;
            line-height: 1.4;
        }
    </style>
</x-atk-app>
