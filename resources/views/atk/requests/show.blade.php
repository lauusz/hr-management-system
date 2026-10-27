@php
    $statusBadgeMap = [
        'PENDING' => 'warning',
        'APPROVED' => 'success',
        'REJECTED' => 'error',
        'PARTIAL' => 'warning',
    ];
    $itemStatusBadgeMap = [
        'PENDING' => 'neutral',
        'APPROVED' => 'success',
        'REJECTED' => 'error',
    ];
    $itemStatusLabel = [
        'PENDING' => 'Menunggu pemeriksaan',
        'APPROVED' => 'Disetujui',
        'REJECTED' => 'Tidak diproses',
    ];
@endphp
<x-atk-app title="Detail Pengajuan ATK">
    <div class="atk-show-topbar">
        <a class="atk-btn atk-back-link" href="{{ route('v2.atk.requests.index') }}">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            <span>Kembali</span>
        </a>

        @if(auth()->user()->canManageAtk())
            <a class="atk-btn atk-back-link" href="{{ route('v2.atk.admin.requests.show', $atkRequest) }}">
                <span>Panel Admin</span>
            </a>
        @endif
    </div>

    <div class="atk-card atk-request-summary">
        <div class="atk-summary-row">
            <div>
                <h1 class="atk-title">{{ $atkRequest->request_number }}</h1>
                <div class="atk-request-summary-meta">
                    <span>{{ $atkRequest->user_name_snapshot }}</span>
                    <span>{{ $atkRequest->pt_name_snapshot ?? '-' }}</span>
                    <span>{{ $atkRequest->created_at?->format('d/m/Y H:i') ?? '-' }}</span>
                </div>
            </div>
            <span class="atk-badge atk-badge-{{ $statusBadgeMap[$atkRequest->status] ?? 'neutral' }}">
                {{ $atkRequest->status }}
            </span>
        </div>

        @if($atkRequest->notes)
            <div class="atk-summary-note">
                <span class="atk-summary-note-label">Catatan Pengaju:</span>
                <span>{{ $atkRequest->notes }}</span>
            </div>
        @endif
    </div>

    {{-- Mobile List --}}
    <div class="atk-request-item-mobile-list">
        @foreach($atkRequest->items as $item)
            @php($itemStatus = $item->status ?? 'PENDING')
            <article class="atk-request-item-card">
                <div class="atk-request-item-top">
                    <strong>{{ $item->item_name_snapshot }}</strong>
                    <span class="atk-badge atk-badge-{{ $itemStatusBadgeMap[$itemStatus] ?? 'neutral' }}">
                        {{ $itemStatusLabel[$itemStatus] ?? $itemStatus }}
                    </span>
                </div>
                <div class="atk-request-item-meta">
                    <span>Jumlah: <strong>{{ $item->qty }} {{ $item->unit_name_snapshot }}</strong></span>
                    <span>Setara: <strong>{{ $item->qty * $item->unit_size_snapshot }} {{ $item->content_unit_name_snapshot }}</strong></span>
                </div>
                @if($item->admin_note)
                    <div class="atk-item-note">Ket: {{ $item->admin_note }}</div>
                @endif
            </article>
        @endforeach
    </div>

    {{-- Desktop Table --}}
    <div class="atk-table-wrap atk-request-item-desktop-table">
        <table class="atk-table atk-show-table">
            <thead>
                <tr>
                    <th>Barang</th>
                    <th>Jumlah</th>
                    <th>Setara</th>
                    <th style="width:1%;white-space:nowrap">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($atkRequest->items as $item)
                    @php($itemStatus = $item->status ?? 'PENDING')
                    <tr>
                        <td>
                            <strong class="atk-item-name">{{ $item->item_name_snapshot }}</strong>
                            @if($item->admin_note)
                                <div class="atk-item-note">Ket: {{ $item->admin_note }}</div>
                            @endif
                        </td>
                        <td>{{ $item->qty }} {{ $item->unit_name_snapshot }}</td>
                        <td class="atk-muted-cell">{{ $item->qty * $item->unit_size_snapshot }} {{ $item->content_unit_name_snapshot }}</td>
                        <td>
                            <span class="atk-badge atk-badge-{{ $itemStatusBadgeMap[$itemStatus] ?? 'neutral' }}">
                                {{ $itemStatusLabel[$itemStatus] ?? $itemStatus }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <style>
        .atk-show-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 10px;
        }
        .atk-back-link {
            height: 32px;
            min-height: 32px;
            padding: 0 11px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 8px;
            background: var(--atk-surface);
            color: var(--atk-text-secondary);
            border: 1px solid var(--atk-border);
        }
        .atk-back-link:hover {
            background: #F9FAFB;
            color: var(--atk-text);
        }

        /* ========== SUMMARY ========== */
        .atk-request-summary {
            padding: 12px 16px;
            margin-bottom: 10px;
        }
        .atk-summary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .atk-request-summary .atk-title {
            font-size: 16px;
        }
        .atk-request-summary-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 8px;
            margin-top: 2px;
            color: var(--atk-muted);
            font-size: 12px;
        }
        .atk-request-summary-meta span + span::before {
            content: '·';
            margin-right: 8px;
            color: #D1D5DB;
        }
        .atk-summary-note {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid var(--atk-border-soft);
            font-size: 12.5px;
            color: var(--atk-text-secondary);
        }
        .atk-summary-note-label {
            color: var(--atk-muted);
            font-weight: 600;
            margin-right: 4px;
        }

        /* ========== ITEMS ========== */
        .atk-request-item-mobile-list {
            display: grid;
            gap: 8px;
        }
        .atk-request-item-card {
            padding: 12px;
            border: 1px solid var(--atk-border);
            border-radius: 10px;
            background: var(--atk-surface);
        }
        .atk-request-item-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
        }
        .atk-request-item-top strong {
            font-size: 13px;
            line-height: 1.35;
        }
        .atk-request-item-meta {
            display: flex;
            gap: 16px;
            margin-top: 6px;
            font-size: 12px;
            color: var(--atk-muted);
        }
        .atk-request-item-meta strong {
            color: var(--atk-text);
        }

        .atk-request-item-desktop-table {
            display: none;
        }
        .atk-show-table {
            min-width: 0;
        }
        .atk-show-table th {
            background: #F9FAFB;
            padding: 9px 14px;
        }
        .atk-show-table td {
            padding: 10px 14px;
            vertical-align: middle;
        }
        .atk-item-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--atk-text);
        }
        .atk-muted-cell {
            color: var(--atk-muted);
        }
        .atk-item-note {
            margin-top: 3px;
            font-size: 11.5px;
            color: var(--atk-muted);
        }

        @media (min-width: 640px) {
            .atk-request-item-mobile-list {
                display: none;
            }
            .atk-request-item-desktop-table {
                display: block;
            }
        }
    </style>
</x-atk-app>
