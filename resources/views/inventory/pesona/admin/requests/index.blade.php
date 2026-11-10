@php
    $labels = [
        'PENDING' => 'Menunggu Pemeriksaan',
        'APPROVED' => 'Disetujui',
        'PARTIAL' => 'Disetujui Sebagian',
        'REJECTED' => 'Ditolak',
    ];
    $shortLabels = [
        '' => 'Semua',
        'PENDING' => 'Menunggu',
        'APPROVED' => 'Disetujui',
        'PARTIAL' => 'Sebagian',
        'REJECTED' => 'Ditolak',
    ];
    $badgeTone = static fn (string $st): string => match ($st) {
        'APPROVED' => 'approved',
        'REJECTED' => 'rejected',
        'PARTIAL' => 'partial',
        default => 'pending',
    };
    $hasActiveFilter = $search !== '' || $status !== '';
@endphp
<x-inv-pesona-app title="Approval Pengambilan — Inventory Pesona">
    <style>
        .pesona-adm-req-page {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Header */
        .pesona-adm-req-head {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .pesona-adm-req-head-main {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .pesona-adm-req-title-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .pesona-adm-req-count {
            display: inline-flex;
            align-items: center;
            height: 24px;
            padding: 0 10px;
            border-radius: 999px;
            background: var(--pesona-primary-soft, #EEF2F8);
            color: var(--pesona-primary, #28355A);
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }
        .pesona-adm-req-manual-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            width: 100%;
            min-height: 40px;
            padding: 0 16px;
            border-radius: 10px;
            background: var(--pesona-primary, #28355A);
            color: #FFFFFF;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            box-sizing: border-box;
            box-shadow: 0 1px 2px rgba(40, 53, 90, 0.14);
            transition: background-color .15s ease;
        }
        .pesona-adm-req-manual-btn:hover {
            background: var(--pesona-primary-hover, #1E2846);
        }

        /* Filter & AJAX Search Toolbar */
        .pesona-adm-req-toolbar {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 12px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .pesona-adm-req-search-wrap {
            position: relative;
            width: 100%;
            min-width: 0;
        }
        .pesona-adm-req-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            pointer-events: none;
        }
        .pesona-adm-req-search-input {
            width: 100%;
            height: 40px;
            padding: 0 68px 0 38px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 10px;
            background: #F8FAFC;
            color: var(--pesona-text, #0F172A);
            font: inherit;
            font-size: 13.5px;
            box-sizing: border-box;
            transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
        }
        .pesona-adm-req-search-input::placeholder {
            color: #94A3B8;
        }
        .pesona-adm-req-search-input:focus {
            outline: none;
            background: #FFFFFF;
            border-color: var(--pesona-primary, #28355A);
            box-shadow: 0 0 0 3px rgba(40, 53, 90, 0.10);
        }
        .pesona-adm-req-search-input::-webkit-search-cancel-button {
            display: none;
        }
        .pesona-adm-req-search-actions {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .pesona-adm-req-clear-btn {
            display: none;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            padding: 0;
            border: 0;
            border-radius: 6px;
            background: #E2E8F0;
            color: #475569;
            cursor: pointer;
            transition: background-color .15s ease, color .15s ease;
        }
        .pesona-adm-req-clear-btn.is-visible {
            display: inline-flex;
        }
        .pesona-adm-req-clear-btn:hover {
            background: #CBD5E1;
            color: #0F172A;
        }
        .pesona-adm-req-spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid #CBD5E1;
            border-top-color: var(--pesona-primary, #28355A);
            border-radius: 999px;
            animation: pesonaAdmReqSpin .65s linear infinite;
        }
        .pesona-adm-req-spinner.is-loading {
            display: inline-block;
        }
        @keyframes pesonaAdmReqSpin {
            to { transform: rotate(360deg); }
        }

        .pesona-adm-req-select {
            width: 100%;
            height: 40px;
            padding: 0 32px 0 12px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 10px;
            background: #F8FAFC;
            color: #0F172A;
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            box-sizing: border-box;
            cursor: pointer;
        }
        .pesona-adm-req-select:focus {
            outline: none;
            background: #FFFFFF;
            border-color: var(--pesona-primary, #28355A);
            box-shadow: 0 0 0 3px rgba(40, 53, 90, 0.10);
        }

        /* Status Pills (Desktop) */
        .pesona-adm-status-pills {
            display: none;
            align-items: center;
            gap: 3px;
            padding: 4px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 10px;
            background: #F8FAFC;
        }
        .pesona-adm-status-pill {
            height: 30px;
            padding: 0 11px;
            border: 0;
            border-radius: 7px;
            background: transparent;
            color: #64748B;
            font: inherit;
            font-size: 12.5px;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
            transition: background-color .15s ease, color .15s ease;
        }
        .pesona-adm-status-pill:hover {
            color: #0F172A;
            background: #EEF2F6;
        }
        .pesona-adm-status-pill.is-active {
            background: var(--pesona-primary, #28355A);
            color: #FFFFFF;
            box-shadow: 0 1px 2px rgba(40, 53, 90, 0.18);
        }

        .pesona-adm-req-reset {
            display: none;
            align-items: center;
            justify-content: center;
            gap: 5px;
            height: 40px;
            padding: 0 13px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 10px;
            background: #FFFFFF;
            color: #475569;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            box-sizing: border-box;
        }
        .pesona-adm-req-reset.is-visible {
            display: inline-flex;
        }
        .pesona-adm-req-reset:hover {
            background: #F8FAFC;
            color: #0F172A;
            border-color: #CBD5E1;
        }

        /* Results Container */
        .pesona-adm-req-results {
            transition: opacity .15s ease;
        }
        .pesona-adm-req-results.is-fetching {
            opacity: .55;
            pointer-events: none;
        }

        /* Status Badges */
        .pesona-req-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            height: 24px;
            padding: 0 9px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 700;
            white-space: nowrap;
            line-height: 1;
        }
        .pesona-req-badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            flex-shrink: 0;
        }
        .pesona-req-badge-pending {
            background: #FFFBEB;
            color: #92400E;
            border: 1px solid #FDE68A;
        }
        .pesona-req-badge-pending .pesona-req-badge-dot { background: #F59E0B; }
        .pesona-req-badge-approved {
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }
        .pesona-req-badge-approved .pesona-req-badge-dot { background: #10B981; }
        .pesona-req-badge-partial {
            background: #EEF2FF;
            color: #3730A3;
            border: 1px solid #C7D2FE;
        }
        .pesona-req-badge-partial .pesona-req-badge-dot { background: #6366F1; }
        .pesona-req-badge-rejected {
            background: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }
        .pesona-req-badge-rejected .pesona-req-badge-dot { background: #EF4444; }

        /* Action Buttons */
        .pesona-req-act-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            height: 34px;
            padding: 0 12px;
            border-radius: 9px;
            font-size: 12.5px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            box-sizing: border-box;
            transition: background-color .14s ease, border-color .14s ease, color .14s ease;
        }
        .pesona-req-act-btn-primary {
            background: var(--pesona-primary, #28355A);
            border: 1px solid var(--pesona-primary, #28355A);
            color: #FFFFFF;
        }
        .pesona-req-act-btn-primary:hover {
            background: var(--pesona-primary-hover, #1E2846);
            border-color: var(--pesona-primary-hover, #1E2846);
        }
        .pesona-req-act-btn-secondary {
            background: #FFFFFF;
            border: 1px solid var(--pesona-border, #E2E8F0);
            color: #334155;
        }
        .pesona-req-act-btn-secondary:hover {
            background: #F8FAFC;
            border-color: #CBD5E1;
            color: #0F172A;
        }

        /* Mobile Cards (< 768px) */
        .pesona-m-req-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .pesona-m-req-card {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 14px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .pesona-m-req-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #F1F5F9;
        }
        .pesona-m-req-no {
            display: inline-block;
            font-size: 13.5px;
            font-weight: 800;
            color: var(--pesona-primary, #28355A);
            text-decoration: none;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        .pesona-m-req-no:hover {
            text-decoration: underline;
        }
        .pesona-m-req-time {
            display: block;
            margin-top: 2px;
            font-size: 11.5px;
            color: #64748B;
            font-weight: 500;
        }
        .pesona-m-req-person {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 6px;
        }
        .pesona-m-req-person strong {
            font-size: 13.5px;
            font-weight: 700;
            color: #0F172A;
        }
        .pesona-m-req-person span {
            font-size: 12px;
            font-weight: 600;
            color: #64748B;
        }
        .pesona-m-req-items-box {
            padding: 8px 10px;
            border-radius: 9px;
            background: #F8FAFC;
            border: 1px solid #F1F5F9;
            font-size: 12px;
            color: #334155;
            line-height: 1.45;
        }
        .pesona-m-req-items-box strong {
            color: #0F172A;
            font-weight: 700;
        }
        .pesona-m-req-card .pesona-req-act-btn {
            width: 100%;
            height: 38px;
        }

        /* Desktop Table (>= 768px) */
        .pesona-d-req-table-wrap {
            display: none;
            overflow-x: auto;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .pesona-d-req-table {
            width: 100%;
            min-width: 780px;
            border-collapse: collapse;
            font-size: 13px;
        }
        .pesona-d-req-table th {
            padding: 11px 16px;
            background: #F8FAFC;
            border-bottom: 1px solid var(--pesona-border, #E2E8F0);
            color: #64748B;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            text-align: left;
            white-space: nowrap;
        }
        .pesona-d-req-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
            color: #0F172A;
        }
        .pesona-d-req-table tbody tr:last-child td {
            border-bottom: 0;
        }
        .pesona-d-req-table tbody tr:hover {
            background: #FAFBFD;
        }
        .pesona-d-req-date {
            display: block;
            font-weight: 700;
            color: #0F172A;
            white-space: nowrap;
        }
        .pesona-d-req-no {
            display: inline-block;
            margin-top: 2px;
            font-size: 11.5px;
            font-weight: 700;
            color: var(--pesona-primary, #28355A);
            text-decoration: none;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        .pesona-d-req-no:hover {
            text-decoration: underline;
        }
        .pesona-d-req-user {
            font-weight: 700;
            color: #0F172A;
        }
        .pesona-d-req-pt {
            color: #64748B;
            font-size: 12.5px;
            font-weight: 500;
        }
        .pesona-d-req-items {
            max-width: 280px;
        }
        .pesona-d-req-items-count {
            font-size: 12.5px;
            font-weight: 700;
            color: #0F172A;
        }
        .pesona-d-req-items-preview {
            margin-top: 2px;
            font-size: 11.5px;
            color: #64748B;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Empty State */
        .pesona-adm-req-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 44px 20px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 14px;
            background: #FFFFFF;
            text-align: center;
        }
        .pesona-adm-req-empty-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--pesona-primary-soft, #EEF2F8);
            color: var(--pesona-primary, #28355A);
        }
        .pesona-adm-req-empty-title {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: #0F172A;
        }
        .pesona-adm-req-empty-sub {
            margin: 0;
            max-width: 400px;
            font-size: 13px;
            color: #64748B;
            line-height: 1.5;
        }

        @media (min-width: 640px) {
            .pesona-adm-req-head {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
            .pesona-adm-req-manual-btn {
                width: auto;
            }
        }

        @media (min-width: 768px) {
            .pesona-adm-req-toolbar {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 10px;
            }
            .pesona-adm-req-search-wrap {
                width: auto;
                flex: 1 1 240px;
            }
            .pesona-adm-req-select {
                display: none;
            }
            .pesona-adm-status-pills {
                display: inline-flex;
            }
            .pesona-m-req-list {
                display: none;
            }
            .pesona-d-req-table-wrap {
                display: block;
            }
        }
    </style>

    <div class="pesona-adm-req-page">
        {{-- Header --}}
        <div class="pesona-adm-req-head">
            <div class="pesona-adm-req-head-main">
                <div class="pesona-adm-req-title-row">
                    <h1 class="pesona-title">Approval Pengambilan</h1>
                    <span class="pesona-adm-req-count" id="pesonaAdminReqCount">{{ $requests->total() }} pengajuan</span>
                </div>
                <p class="pesona-subtitle">Periksa dan finalisasi pengajuan pengambilan barang persediaan garasi PT Pesona.</p>
            </div>
            <a class="pesona-adm-req-manual-btn" href="{{ route('inventory.pesona.admin.requests.manual.create') }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                <span>Input Pengambilan Manual</span>
            </a>
        </div>

        {{-- Filter & AJAX Live Search Toolbar --}}
        <form id="pesonaAdminReqFilterForm" class="pesona-adm-req-toolbar" method="GET" action="{{ route('inventory.pesona.admin.requests.index') }}">
            <div class="pesona-adm-status-pills" role="group" aria-label="Filter status pengajuan">
                @foreach($shortLabels as $statusVal => $statusLabel)
                    <button
                        type="button"
                        class="pesona-adm-status-pill {{ $status === $statusVal ? 'is-active' : '' }}"
                        data-status-pill="{{ $statusVal }}"
                    >
                        {{ $statusLabel }}
                    </button>
                @endforeach
            </div>

            <div class="pesona-adm-req-search-wrap">
                <svg class="pesona-adm-req-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <input
                    id="pesonaAdminReqSearch"
                    class="pesona-adm-req-search-input"
                    type="search"
                    name="q"
                    maxlength="150"
                    value="{{ $search }}"
                    placeholder="Cari nomor pengajuan, nama pemohon, atau PT..."
                    autocomplete="off"
                    aria-label="Cari nomor, pemohon, atau PT"
                >
                <div class="pesona-adm-req-search-actions">
                    <span id="pesonaAdminReqSpinner" class="pesona-adm-req-spinner" aria-hidden="true"></span>
                    <button
                        id="pesonaAdminReqClear"
                        class="pesona-adm-req-clear-btn {{ $search !== '' ? 'is-visible' : '' }}"
                        type="button"
                        aria-label="Bersihkan pencarian"
                        title="Bersihkan pencarian"
                    >
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <select id="pesonaAdminReqStatus" class="pesona-adm-req-select" name="status" aria-label="Filter status pengajuan">
                <option value="" @selected($status === '')>Semua Status</option>
                @foreach($labels as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <a
                id="pesonaAdminReqReset"
                class="pesona-adm-req-reset {{ $hasActiveFilter ? 'is-visible' : '' }}"
                href="{{ route('inventory.pesona.admin.requests.index') }}"
            >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                    <path d="M3 3v5h5"/>
                </svg>
                <span>Reset</span>
            </a>
        </form>

        {{-- Results Area --}}
        <div id="pesonaAdminReqResults" class="pesona-adm-req-results">
            @if($requests->isEmpty())
                <div class="pesona-adm-req-empty">
                    <span class="pesona-adm-req-empty-icon" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <use href="#pesona-icon-approval"/>
                        </svg>
                    </span>
                    @if(! $hasRequests)
                        <p class="pesona-adm-req-empty-title">Belum Ada Pengajuan</p>
                        <p class="pesona-adm-req-empty-sub">Belum ada permintaan pengambilan barang yang masuk pada antrean pemeriksaan.</p>
                    @elseif($requests->total() === 0)
                        <p class="pesona-adm-req-empty-title">Tidak Ada Pengajuan yang Sesuai</p>
                        <p class="pesona-adm-req-empty-sub">Tidak ada pengajuan yang sesuai dengan pencarian atau filter yang dipilih.</p>
                        <a class="pesona-adm-req-reset is-visible" href="{{ route('inventory.pesona.admin.requests.index') }}" data-async-reset>Reset Filter</a>
                    @else
                        <p class="pesona-adm-req-empty-title">Tidak Ada Pengajuan pada Halaman Ini</p>
                    @endif
                </div>
            @else
                {{-- Mobile Cards (< 768px) --}}
                <div class="pesona-m-req-list">
                    @foreach($requests as $entry)
                        @php
                            $loadedItems = $entry->relationLoaded('items') ? $entry->items : collect();
                            $previewText = $loadedItems->take(2)->map(fn ($it) => $it->item_name_snapshot . ' (' . $it->qty . ' ' . $it->unit_name_snapshot . ')')->implode(', ');
                            if ($entry->items_count > 2 && $previewText !== '') {
                                $previewText .= ', +' . ($entry->items_count - 2) . ' lainnya';
                            }
                        @endphp
                        <article class="pesona-m-req-card">
                            <div class="pesona-m-req-top">
                                <div>
                                    <a class="pesona-m-req-no" href="{{ route('inventory.pesona.admin.requests.show', $entry) }}">{{ $entry->request_number }}</a>
                                    <time class="pesona-m-req-time">{{ $entry->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }}</time>
                                </div>
                                <span class="pesona-req-badge pesona-req-badge-{{ $badgeTone($entry->status) }}">
                                    <span class="pesona-req-badge-dot"></span>
                                    {{ $labels[$entry->status] ?? 'Status Tidak Dikenal' }}
                                </span>
                            </div>

                            <div class="pesona-m-req-person">
                                <strong>{{ $entry->user_name_snapshot }}</strong>
                                <span>{{ $entry->pt_name_snapshot ?? '—' }}</span>
                                <span>Nopol: {{ $entry->nopol_snapshot ?? 'Belum dicatat' }}</span>
                            </div>

                            <div class="pesona-m-req-items-box">
                                <strong>{{ $entry->items_count }} baris barang</strong>
                                @if($previewText !== '')
                                    <span style="color:#64748B"> · {{ $previewText }}</span>
                                @endif
                            </div>

                            <a
                                class="pesona-req-act-btn {{ $entry->status === 'PENDING' ? 'pesona-req-act-btn-primary' : 'pesona-req-act-btn-secondary' }}"
                                href="{{ route('inventory.pesona.admin.requests.show', $entry) }}"
                            >
                                {{ $entry->status === 'PENDING' ? 'Periksa Pengambilan' : 'Lihat Detail' }}
                            </a>
                        </article>
                    @endforeach
                </div>

                {{-- Desktop Table (>= 768px) --}}
                <div class="pesona-d-req-table-wrap">
                    <table class="pesona-d-req-table">
                        <thead>
                            <tr>
                                <th>Waktu &amp; Nomor</th>
                                <th>Pemohon</th>
                                <th>PT</th>
                                <th>Ringkasan Barang</th>
                                <th>Status</th>
                                <th style="text-align:right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $entry)
                                @php
                                    $loadedItems = $entry->relationLoaded('items') ? $entry->items : collect();
                                    $previewText = $loadedItems->take(3)->map(fn ($it) => $it->item_name_snapshot . ' (' . $it->qty . ' ' . $it->unit_name_snapshot . ')')->implode(', ');
                                    if ($entry->items_count > 3 && $previewText !== '') {
                                        $previewText .= ', +' . ($entry->items_count - 3) . ' lainnya';
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <span class="pesona-d-req-date">{{ $entry->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }}</span>
                                        <a class="pesona-d-req-no" href="{{ route('inventory.pesona.admin.requests.show', $entry) }}">{{ $entry->request_number }}</a>
                                    </td>
                                    <td>
                                        <span class="pesona-d-req-user">{{ $entry->user_name_snapshot }}</span>
                                    </td>
                                    <td>
                                        <span class="pesona-d-req-pt">{{ $entry->pt_name_snapshot ?? '—' }}</span>
                                        <span class="pesona-d-req-pt">Nopol: {{ $entry->nopol_snapshot ?? 'Belum dicatat' }}</span>
                                    </td>
                                    <td class="pesona-d-req-items">
                                        <div class="pesona-d-req-items-count">{{ $entry->items_count }} baris barang</div>
                                        @if($previewText !== '')
                                            <div class="pesona-d-req-items-preview" title="{{ $previewText }}">{{ $previewText }}</div>
                                        @endif
                                    </td>
                                    <td style="white-space:nowrap">
                                        <span class="pesona-req-badge pesona-req-badge-{{ $badgeTone($entry->status) }}">
                                            <span class="pesona-req-badge-dot"></span>
                                            {{ $labels[$entry->status] ?? 'Status Tidak Dikenal' }}
                                        </span>
                                    </td>
                                    <td style="text-align:right;white-space:nowrap">
                                        <a
                                            class="pesona-req-act-btn {{ $entry->status === 'PENDING' ? 'pesona-req-act-btn-primary' : 'pesona-req-act-btn-secondary' }}"
                                            href="{{ route('inventory.pesona.admin.requests.show', $entry) }}"
                                        >
                                            {{ $entry->status === 'PENDING' ? 'Periksa Pengambilan' : 'Lihat Detail' }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <x-pagination :items="$requests" />
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('pesonaAdminReqFilterForm');
            const searchInput = document.getElementById('pesonaAdminReqSearch');
            const clearBtn = document.getElementById('pesonaAdminReqClear');
            const spinner = document.getElementById('pesonaAdminReqSpinner');
            const statusSelect = document.getElementById('pesonaAdminReqStatus');
            const statusPills = Array.from(document.querySelectorAll('[data-status-pill]'));
            const resetBtn = document.getElementById('pesonaAdminReqReset');
            const resultsWrap = document.getElementById('pesonaAdminReqResults');
            const countBadge = document.getElementById('pesonaAdminReqCount');

            if (!form || !resultsWrap) return;

            let debounceTimer = null;
            let activeController = null;

            function syncUIControls() {
                const qVal = (searchInput?.value || '').trim();
                const stVal = statusSelect?.value || '';

                clearBtn?.classList.toggle('is-visible', qVal.length > 0);
                statusPills.forEach((pill) => {
                    pill.classList.toggle('is-active', (pill.getAttribute('data-status-pill') || '') === stVal);
                });
                resetBtn?.classList.toggle('is-visible', qVal !== '' || stVal !== '');
            }

            function buildFilterUrl() {
                const url = new URL(form.action, window.location.origin);
                const qVal = (searchInput?.value || '').trim();
                const stVal = statusSelect?.value || '';

                if (qVal !== '') url.searchParams.set('q', qVal);
                if (stVal !== '') url.searchParams.set('status', stVal);

                return url;
            }

            function loadResults(targetUrl) {
                if (activeController) {
                    activeController.abort();
                }
                activeController = new AbortController();

                spinner?.classList.add('is-loading');
                resultsWrap.classList.add('is-fetching');

                fetch(targetUrl.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    signal: activeController.signal
                })
                    .then((res) => {
                        if (!res.ok) throw new Error('Network error');
                        return res.text();
                    })
                    .then((html) => {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const nextResults = doc.getElementById('pesonaAdminReqResults');
                        const nextCount = doc.getElementById('pesonaAdminReqCount');

                        if (!nextResults) {
                            window.location.href = targetUrl.toString();
                            return;
                        }

                        resultsWrap.innerHTML = nextResults.innerHTML;
                        if (nextCount && countBadge) {
                            countBadge.textContent = nextCount.textContent;
                        }

                        window.history.replaceState(null, '', targetUrl.toString());
                    })
                    .catch((err) => {
                        if (err && err.name === 'AbortError') return;
                        window.location.href = targetUrl.toString();
                    })
                    .finally(() => {
                        spinner?.classList.remove('is-loading');
                        resultsWrap.classList.remove('is-fetching');
                    });
            }

            function triggerFilter() {
                syncUIControls();
                loadResults(buildFilterUrl());
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                clearTimeout(debounceTimer);
                triggerFilter();
            });

            searchInput?.addEventListener('input', function () {
                syncUIControls();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(triggerFilter, 260);
            });

            clearBtn?.addEventListener('click', function () {
                if (!searchInput) return;
                searchInput.value = '';
                searchInput.focus();
                clearTimeout(debounceTimer);
                triggerFilter();
            });

            statusSelect?.addEventListener('change', function () {
                clearTimeout(debounceTimer);
                triggerFilter();
            });

            statusPills.forEach((pill) => {
                pill.addEventListener('click', function () {
                    const value = pill.getAttribute('data-status-pill') || '';
                    if (statusSelect) statusSelect.value = value;
                    clearTimeout(debounceTimer);
                    triggerFilter();
                });
            });

            resetBtn?.addEventListener('click', function (event) {
                event.preventDefault();
                if (searchInput) searchInput.value = '';
                if (statusSelect) statusSelect.value = '';
                clearTimeout(debounceTimer);
                triggerFilter();
            });

            resultsWrap.addEventListener('click', function (event) {
                const resetLink = event.target.closest('[data-async-reset]');
                if (resetLink) {
                    event.preventDefault();
                    if (searchInput) searchInput.value = '';
                    if (statusSelect) statusSelect.value = '';
                    clearTimeout(debounceTimer);
                    triggerFilter();
                    return;
                }

                const pageLink = event.target.closest('nav[aria-label="Pagination"] a, .hrd-pagination a');
                if (!pageLink) return;
                event.preventDefault();
                loadResults(new URL(pageLink.href, window.location.origin));
            });

            syncUIControls();
        })();
    </script>
</x-inv-pesona-app>
