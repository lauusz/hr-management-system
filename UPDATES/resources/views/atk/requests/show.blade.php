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
    <div class="atk-header atk-request-summary">
        <div>
            <h1 class="atk-title">{{ $atkRequest->request_number }}</h1>
            <div class="atk-request-summary-meta">
                <span>{{ $atkRequest->pt_name_snapshot ?? '-' }}</span>
                <span>{{ $atkRequest->user_name_snapshot }}</span>
                <span>{{ $atkRequest->created_at?->format('d/m/Y H:i') }}</span>
            </div>
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

    <div class="atk-request-item-mobile-list">
        @foreach($atkRequest->items as $item)
            @php($itemStatus = $item->status ?? 'PENDING')
            <article class="atk-request-item-card">
                <div class="atk-request-item-top">
                    <strong>{{ $item->item_name_snapshot }}</strong>
                    <span class="atk-badge atk-badge-{{ $itemStatusBadgeMap[$itemStatus] ?? 'neutral' }}">{{ $itemStatusLabel[$itemStatus] ?? $itemStatus }}</span>
                </div>
                <dl class="atk-request-item-meta">
                    <div>
                        <dt>Jumlah</dt>
                        <dd>{{ $item->qty }} {{ $item->unit_name_snapshot }}</dd>
                    </div>
                    <div>
                        <dt>Setara</dt>
                        <dd>{{ $item->qty * $item->unit_size_snapshot }} {{ $item->content_unit_name_snapshot }}</dd>
                    </div>
                </dl>
                @if($item->admin_note)
                    <div class="atk-item-note">“{{ $item->admin_note }}”</div>
                @endif
            </article>
        @endforeach
    </div>

    <div class="atk-table-wrap atk-request-item-desktop-table">
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
        .atk-request-summary {
            padding: 16px;
            border: 1px solid var(--atk-border);
            border-radius: 16px;
            background: var(--atk-surface);
            box-shadow: 0 1px 3px rgba(17, 24, 39, .04);
        }
        .atk-request-summary-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 5px 12px;
            margin-top: 8px;
            color: var(--atk-muted);
            font-size: 11px;
            font-weight: 600;
        }
        .atk-request-summary-meta span + span::before {
            content: '•';
            margin-right: 8px;
            color: #D1D5DB;
        }
        .atk-request-item-mobile-list {
            display: grid;
            gap: 12px;
        }
        .atk-request-item-card {
            padding: 16px;
            border: 1px solid var(--atk-border);
            border-radius: 16px;
            background: var(--atk-surface);
            box-shadow: 0 1px 3px rgba(17, 24, 39, .04);
        }
        .atk-request-item-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--atk-border-soft);
        }
        .atk-request-item-top strong {
            min-width: 0;
            font-size: 14px;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }
        .atk-request-item-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin: 14px 0 0;
        }
        .atk-request-item-meta dt {
            color: var(--atk-muted);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .atk-request-item-meta dd {
            margin: 4px 0 0;
            font-size: 13px;
            font-weight: 800;
        }
        .atk-request-item-desktop-table {
            display: none;
        }
        .atk-item-note {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--atk-border-soft);
            font-size: 11px;
            color: var(--error);
            font-style: italic;
            line-height: 1.4;
        }

        @media (min-width: 640px) {
            .atk-request-summary {
                padding: 0;
                border: 0;
                border-radius: 0;
                background: transparent;
                box-shadow: none;
            }
            .atk-request-item-mobile-list {
                display: none;
            }
            .atk-request-item-desktop-table {
                display: block;
            }
            .atk-request-item-desktop-table .atk-item-note {
                margin-top: 4px;
                padding-top: 0;
                border-top: 0;
            }
        }
    </style>
</x-atk-app>
