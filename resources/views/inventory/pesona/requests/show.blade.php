@php
    $statusLabels = [
        'PENDING' => 'Menunggu Pemeriksaan',
        'APPROVED' => 'Disetujui',
        'REJECTED' => 'Ditolak',
        'PARTIAL' => 'Disetujui Sebagian',
    ];
    $statusBadgeMap = [
        'PENDING' => 'warning',
        'APPROVED' => 'success',
        'REJECTED' => 'error',
        'PARTIAL' => 'warning',
    ];
    $itemLabels = [
        'PENDING' => 'Belum Diperiksa',
        'APPROVED' => 'Disetujui',
        'REJECTED' => 'Ditolak',
    ];
    $itemBadgeMap = [
        'PENDING' => 'neutral',
        'APPROVED' => 'success',
        'REJECTED' => 'error',
    ];
    $isPesonaAdmin = \App\Models\InvPesonaAccessUser::query()
        ->where('user_id', auth()->id())
        ->where('is_admin', true)
        ->exists();
@endphp
<x-inv-pesona-app title="Detail Pengambilan — Inventory Pesona">
    <div class="pesona-show-topbar">
        <a class="pesona-btn pesona-back-link" href="{{ route('inventory.pesona.requests.index') }}">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            <span>Kembali ke Riwayat</span>
        </a>

        @if($isPesonaAdmin)
            <a class="pesona-btn pesona-back-link" href="{{ route('inventory.pesona.admin.requests.show', $pesonaRequest) }}">
                <span>Panel Pemeriksaan Admin</span>
            </a>
        @endif
    </div>

    <div class="pesona-card pesona-request-summary">
        <div class="pesona-summary-row">
            <div>
                <h1 class="pesona-title">{{ $pesonaRequest->request_number }}</h1>
                <div class="pesona-request-summary-meta">
                    <span>{{ $pesonaRequest->user_name_snapshot }}</span>
                    <span>{{ $pesonaRequest->pt_name_snapshot ?? '—' }}</span>
                    <span>Nopol: {{ $pesonaRequest->nopol_snapshot ?? 'Belum dicatat' }}</span>
                    <span>{{ $pesonaRequest->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }}</span>
                </div>
            </div>
            <span class="pesona-badge pesona-badge-{{ $statusBadgeMap[$pesonaRequest->status] ?? 'neutral' }}">
                {{ $statusLabels[$pesonaRequest->status] ?? 'Status Tidak Dikenal' }}
            </span>
        </div>

        @if($pesonaRequest->notes !== null && $pesonaRequest->notes !== '')
            <div class="pesona-summary-note">
                <span class="pesona-summary-note-label">Catatan Pengaju:</span>
                <span class="pesona-note-pre">{{ $pesonaRequest->notes }}</span>
            </div>
        @endif

        @if($pesonaRequest->admin_note !== null && $pesonaRequest->admin_note !== '')
            <div class="pesona-summary-note">
                <span class="pesona-summary-note-label">Catatan Admin:</span>
                <span class="pesona-note-pre">{{ $pesonaRequest->admin_note }}</span>
            </div>
        @endif

        @if($pesonaRequest->approved_at || $pesonaRequest->approved_by)
            <div class="pesona-summary-note">
                <span class="pesona-summary-note-label">Persetujuan:</span>
                <span>{{ $pesonaRequest->approved_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }} · {{ $pesonaRequest->approvedBy?->name ?? '—' }}</span>
            </div>
        @endif

        @if($pesonaRequest->rejected_at || $pesonaRequest->rejected_by)
            <div class="pesona-summary-note">
                <span class="pesona-summary-note-label">Penolakan:</span>
                <span>{{ $pesonaRequest->rejected_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }} · {{ $pesonaRequest->rejectedBy?->name ?? '—' }}</span>
            </div>
        @endif

        @if($pesonaRequest->status === 'PENDING')
            <div class="pesona-pending-banner">
                Pengambilan belum difinalisasi oleh admin. Hasil pemeriksaan barang masih dapat berubah.
            </div>
        @endif
    </div>

    @if($pesonaRequest->items->isEmpty())
        <div class="pesona-card pesona-empty-items">Detail barang tidak tersedia.</div>
    @else
        {{-- Mobile Card List (< 640px) --}}
        <div class="pesona-request-item-mobile-list">
            @foreach($pesonaRequest->items as $item)
                @php $itemStatus = $item->status ?? 'PENDING'; @endphp
                <article class="pesona-request-item-card">
                    <div class="pesona-request-item-top">
                        <strong>{{ $item->item_name_snapshot }}</strong>
                        <span class="pesona-badge pesona-badge-{{ $itemBadgeMap[$itemStatus] ?? 'neutral' }}">
                            {{ $itemLabels[$itemStatus] ?? 'Status Tidak Dikenal' }}
                        </span>
                    </div>
                    <div class="pesona-request-item-meta">
                        <span>Jumlah: <strong>{{ $item->qty }} {{ $item->unit_name_snapshot }}</strong></span>
                        <span>Setara: <strong>{{ number_format((int) $item->qty * (int) $item->unit_size_snapshot, 0, ',', '.') }} {{ $item->content_unit_name_snapshot }}</strong></span>
                    </div>
                    @if($item->unit_size_snapshot > 1)
                        <div class="pesona-item-submeta">Konversi: 1 {{ $item->unit_name_snapshot }} = {{ $item->unit_size_snapshot }} {{ $item->content_unit_name_snapshot }}</div>
                    @endif
                    @if($item->admin_note !== null && $item->admin_note !== '')
                        <div class="pesona-item-note">Catatan admin: <span class="pesona-note-pre">{{ $item->admin_note }}</span></div>
                    @endif
                    @if($item->reviewed_at || $item->reviewed_by)
                        <div class="pesona-item-submeta">
                            Diperiksa: {{ $item->reviewed_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }} · {{ $item->reviewedBy?->name ?? '—' }}
                        </div>
                    @endif
                </article>
            @endforeach
        </div>

        {{-- Desktop Table (>= 640px) --}}
        <div class="pesona-table-wrap pesona-request-item-desktop-table">
            <table class="pesona-table pesona-show-table">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Jumlah</th>
                        <th>Setara</th>
                        <th>Pemeriksaan</th>
                        <th style="width:1%;white-space:nowrap">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pesonaRequest->items as $item)
                        @php $itemStatus = $item->status ?? 'PENDING'; @endphp
                        <tr>
                            <td>
                                <strong class="pesona-item-name">{{ $item->item_name_snapshot }}</strong>
                                @if($item->unit_size_snapshot > 1)
                                    <div class="pesona-item-submeta">1 {{ $item->unit_name_snapshot }} = {{ $item->unit_size_snapshot }} {{ $item->content_unit_name_snapshot }}</div>
                                @endif
                                @if($item->admin_note !== null && $item->admin_note !== '')
                                    <div class="pesona-item-note">Ket: <span class="pesona-note-pre">{{ $item->admin_note }}</span></div>
                                @endif
                            </td>
                            <td><strong>{{ $item->qty }} {{ $item->unit_name_snapshot }}</strong></td>
                            <td class="pesona-muted-cell">{{ number_format((int) $item->qty * (int) $item->unit_size_snapshot, 0, ',', '.') }} {{ $item->content_unit_name_snapshot }}</td>
                            <td class="pesona-muted-cell">
                                @if($item->reviewed_at || $item->reviewed_by)
                                    {{ $item->reviewed_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }} · {{ $item->reviewedBy?->name ?? '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <span class="pesona-badge pesona-badge-{{ $itemBadgeMap[$itemStatus] ?? 'neutral' }}">
                                    {{ $itemLabels[$itemStatus] ?? 'Status Tidak Dikenal' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <style>
        .pesona-show-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }
        .pesona-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            font: inherit;
            cursor: pointer;
            transition: background-color .15s ease, color .15s ease, border-color .15s ease;
        }
        .pesona-back-link {
            height: 34px;
            min-height: 34px;
            padding: 0 12px;
            font-size: 12.5px;
            font-weight: 600;
            border-radius: 9px;
            background: var(--pesona-surface);
            color: var(--pesona-text-secondary);
            border: 1px solid var(--pesona-border);
        }
        .pesona-back-link:hover {
            background: var(--pesona-primary-soft);
            color: var(--pesona-primary);
            border-color: rgba(40, 53, 90, 0.2);
        }

        .pesona-card {
            background: var(--pesona-surface);
            border: 1px solid var(--pesona-border);
            border-radius: 12px;
            padding: 14px 16px;
            box-shadow: 0 1px 3px rgba(17, 24, 39, 0.05);
        }
        .pesona-request-summary {
            margin-bottom: 12px;
        }
        .pesona-summary-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .pesona-request-summary .pesona-title {
            font-size: 16.5px;
            color: var(--pesona-primary);
        }
        .pesona-request-summary-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 8px;
            margin-top: 3px;
            color: var(--pesona-muted);
            font-size: 12px;
        }
        .pesona-request-summary-meta span + span::before {
            content: '·';
            margin-right: 8px;
            color: #D1D5DB;
        }
        .pesona-summary-note {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid var(--pesona-border-soft);
            font-size: 12.5px;
            color: var(--pesona-text-secondary);
            overflow-wrap: anywhere;
        }
        .pesona-summary-note-label {
            color: var(--pesona-muted);
            font-weight: 600;
            margin-right: 4px;
        }
        .pesona-note-pre {
            white-space: pre-wrap;
        }
        .pesona-pending-banner {
            margin-top: 10px;
            padding: 9px 12px;
            border-radius: 9px;
            background: #FEF3C7;
            border: 1px solid #FDE68A;
            color: #92400E;
            font-size: 12px;
            font-weight: 600;
        }

        .pesona-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }
        .pesona-badge-success { color: #15803D; background: rgba(34, 197, 94, .12); }
        .pesona-badge-warning { color: #B45309; background: rgba(245, 158, 11, .14); }
        .pesona-badge-error { color: #B91C1C; background: rgba(239, 68, 68, .12); }
        .pesona-badge-neutral { color: #4B5563; background: #F3F4F6; }

        .pesona-empty-items {
            text-align: center;
            padding: 28px 16px;
            color: var(--pesona-muted);
            font-size: 13px;
        }

        /* ========== ITEMS (Mobile-First Cards -> Desktop Table) ========== */
        .pesona-request-item-mobile-list {
            display: grid;
            gap: 8px;
        }
        .pesona-request-item-card {
            padding: 12px 14px;
            border: 1px solid var(--pesona-border);
            border-radius: 10px;
            background: var(--pesona-surface);
            box-shadow: 0 1px 2px rgba(17, 24, 39, 0.04);
        }
        .pesona-request-item-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
        }
        .pesona-request-item-top strong {
            font-size: 13px;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }
        .pesona-request-item-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px 16px;
            margin-top: 6px;
            font-size: 12px;
            color: var(--pesona-muted);
        }
        .pesona-request-item-meta strong {
            color: var(--pesona-text);
        }
        .pesona-item-submeta {
            margin-top: 4px;
            font-size: 11.5px;
            color: var(--pesona-muted);
        }
        .pesona-item-note {
            margin-top: 5px;
            padding: 6px 8px;
            border-radius: 7px;
            background: var(--pesona-bg);
            font-size: 11.5px;
            color: var(--pesona-text-secondary);
            overflow-wrap: anywhere;
        }

        .pesona-request-item-desktop-table {
            display: none;
            overflow-x: auto;
            background: var(--pesona-surface);
            border: 1px solid var(--pesona-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(17, 24, 39, 0.05);
        }
        .pesona-table {
            width: 100%;
            border-collapse: collapse;
        }
        .pesona-show-table th,
        .pesona-show-table td {
            padding: 11px 16px;
            border-bottom: 1px solid var(--pesona-border-soft);
            text-align: left;
            font-size: 13px;
            vertical-align: middle;
        }
        .pesona-show-table th {
            padding: 10px 16px;
            color: var(--pesona-muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            background: var(--pesona-primary-softer);
            border-bottom: 1px solid var(--pesona-border);
        }
        .pesona-show-table tr:last-child td {
            border-bottom: 0;
        }
        .pesona-item-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--pesona-text);
        }
        .pesona-muted-cell {
            color: var(--pesona-muted);
            font-size: 12.5px;
        }

        @media (min-width: 640px) {
            .pesona-request-item-mobile-list {
                display: none;
            }
            .pesona-request-item-desktop-table {
                display: block;
            }
        }
    </style>
</x-inv-pesona-app>
