@php
    $stockTabs = [
        'all' => 'Semua',
        'available' => 'Tersedia',
        'low' => 'Tipis',
        'out' => 'Habis',
    ];
    $hasActiveFilter = $filters['q'] !== '' || $filters['status'] !== 'all' || $filters['stock'] !== 'all';
@endphp
<x-inv-pesona-app title="Master Barang — Inventory Pesona">
    <style>
        .pesona-items-page {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Header */
        .pesona-items-head {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .pesona-items-head-main {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .pesona-items-title-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .pesona-items-total-pill {
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
        .pesona-items-create-btn {
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
            transition: background-color .15s ease, transform .1s ease;
        }
        .pesona-items-create-btn:hover {
            background: var(--pesona-primary-hover, #1E2846);
        }

        /* Toolbar & Filters */
        .pesona-items-toolbar {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 12px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .pesona-items-search-wrap {
            position: relative;
            width: 100%;
            min-width: 0;
        }
        .pesona-items-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            pointer-events: none;
        }
        .pesona-items-search-input {
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
        .pesona-items-search-input::placeholder {
            color: #94A3B8;
        }
        .pesona-items-search-input:focus {
            outline: none;
            background: #FFFFFF;
            border-color: var(--pesona-primary, #28355A);
            box-shadow: 0 0 0 3px rgba(40, 53, 90, 0.10);
        }
        .pesona-items-search-input::-webkit-search-cancel-button {
            display: none;
        }
        .pesona-items-search-actions {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .pesona-items-clear-btn {
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
        .pesona-items-clear-btn.is-visible {
            display: inline-flex;
        }
        .pesona-items-clear-btn:hover {
            background: #CBD5E1;
            color: #0F172A;
        }
        .pesona-items-spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid #CBD5E1;
            border-top-color: var(--pesona-primary, #28355A);
            border-radius: 999px;
            animation: pesonaItemsSpin .65s linear infinite;
        }
        .pesona-items-spinner.is-loading {
            display: inline-block;
        }
        @keyframes pesonaItemsSpin {
            to { transform: rotate(360deg); }
        }

        .pesona-items-filter-group {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }
        .pesona-items-select {
            width: 100%;
            height: 40px;
            padding: 0 32px 0 12px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 10px;
            background: #F8FAFC;
            color: var(--pesona-text, #0F172A);
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            box-sizing: border-box;
            cursor: pointer;
            transition: border-color .15s ease, background-color .15s ease;
        }
        .pesona-items-select:focus {
            outline: none;
            background: #FFFFFF;
            border-color: var(--pesona-primary, #28355A);
            box-shadow: 0 0 0 3px rgba(40, 53, 90, 0.10);
        }

        /* Segmented Stock Pills (Desktop) */
        .pesona-stock-pills {
            display: none;
            align-items: center;
            gap: 3px;
            padding: 4px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 10px;
            background: #F8FAFC;
        }
        .pesona-stock-pill {
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
            transition: background-color .15s ease, color .15s ease, box-shadow .15s ease;
        }
        .pesona-stock-pill:hover {
            color: #0F172A;
            background: #EEF2F6;
        }
        .pesona-stock-pill.is-active {
            background: var(--pesona-primary, #28355A);
            color: #FFFFFF;
            box-shadow: 0 1px 2px rgba(40, 53, 90, 0.18);
        }

        .pesona-items-reset-btn {
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
            transition: background-color .15s ease, color .15s ease, border-color .15s ease;
        }
        .pesona-items-reset-btn.is-visible {
            display: inline-flex;
        }
        .pesona-items-reset-btn:hover {
            background: #F8FAFC;
            color: #0F172A;
            border-color: #CBD5E1;
        }

        /* Results Container */
        .pesona-items-results-wrap {
            transition: opacity .15s ease;
        }
        .pesona-items-results-wrap.is-fetching {
            opacity: .55;
            pointer-events: none;
        }

        /* Badges */
        .pesona-badge {
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
        .pesona-badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            flex-shrink: 0;
        }
        .pesona-badge-available {
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }
        .pesona-badge-available .pesona-badge-dot { background: #10B981; }
        .pesona-badge-low {
            background: #FFFBEB;
            color: #92400E;
            border: 1px solid #FDE68A;
        }
        .pesona-badge-low .pesona-badge-dot { background: #F59E0B; }
        .pesona-badge-out {
            background: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }
        .pesona-badge-out .pesona-badge-dot { background: #EF4444; }
        .pesona-badge-active {
            background: #F0FDF4;
            color: #166534;
            border: 1px solid #BBF7D0;
        }
        .pesona-badge-active .pesona-badge-dot { background: #22C55E; }
        .pesona-badge-inactive {
            background: #F1F5F9;
            color: #475569;
            border: 1px solid #E2E8F0;
        }
        .pesona-badge-inactive .pesona-badge-dot { background: #94A3B8; }

        /* Action Buttons */
        .pesona-act-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            height: 32px;
            padding: 0 10px;
            border-radius: 8px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            background: #FFFFFF;
            color: #334155;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            box-sizing: border-box;
            transition: background-color .14s ease, border-color .14s ease, color .14s ease;
        }
        .pesona-act-btn:hover {
            background: #F8FAFC;
            border-color: #CBD5E1;
            color: #0F172A;
        }
        .pesona-act-btn-stockin {
            background: var(--pesona-primary-soft, #EEF2F8);
            border-color: rgba(40, 53, 90, 0.18);
            color: var(--pesona-primary, #28355A);
            font-weight: 700;
        }
        .pesona-act-btn-stockin:hover {
            background: var(--pesona-primary, #28355A);
            border-color: var(--pesona-primary, #28355A);
            color: #FFFFFF;
        }

        /* Thumbnail */
        .pesona-item-thumb {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid #E2E8F0;
            background: #F8FAFC;
        }
        .pesona-item-thumb-placeholder {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #94A3B8;
            background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%);
        }

        /* Mobile-First Cards (< 768px) */
        .pesona-m-items-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .pesona-m-item-card {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 14px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .pesona-m-item-card.is-inactive {
            background: #FAFAFC;
            opacity: .85;
        }
        .pesona-m-item-top {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            min-width: 0;
        }
        .pesona-m-item-body {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .pesona-m-item-title-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
        }
        .pesona-item-name-link {
            color: var(--pesona-text, #0F172A);
            font-size: 14px;
            font-weight: 700;
            line-height: 1.35;
            text-decoration: none;
            overflow-wrap: anywhere;
        }
        .pesona-item-name-link:hover {
            color: var(--pesona-primary, #28355A);
            text-decoration: underline;
        }
        .pesona-item-desc {
            margin: 0;
            color: #64748B;
            font-size: 12px;
            line-height: 1.45;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .pesona-m-item-metrics {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            padding: 10px 12px;
            border-radius: 10px;
            background: #F8FAFC;
            border: 1px solid #F1F5F9;
        }
        .pesona-m-metric {
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-width: 0;
        }
        .pesona-m-metric-label {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #64748B;
        }
        .pesona-m-metric-val {
            font-size: 12.5px;
            font-weight: 700;
            color: #0F172A;
        }
        .pesona-m-metric-sub {
            font-size: 11px;
            color: #64748B;
        }
        .pesona-m-item-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            padding-top: 2px;
        }
        .pesona-m-item-actions .pesona-act-btn {
            height: 36px;
            font-size: 12.5px;
        }

        /* Desktop Table (>= 768px) */
        .pesona-d-table-wrap {
            display: none;
            overflow-x: auto;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .pesona-d-table {
            width: 100%;
            min-width: 820px;
            border-collapse: collapse;
            font-size: 13px;
        }
        .pesona-d-table th {
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
        .pesona-d-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
            color: #0F172A;
        }
        .pesona-d-table tbody tr:last-child td {
            border-bottom: 0;
        }
        .pesona-d-table tbody tr:hover {
            background: #FAFBFD;
        }
        .pesona-d-table tbody tr.is-inactive {
            background: #FAFAFC;
            opacity: .8;
        }
        .pesona-d-item-cell {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 220px;
            max-width: 340px;
        }
        .pesona-d-item-text {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .pesona-d-unit-cell {
            display: flex;
            flex-direction: column;
            gap: 2px;
            white-space: nowrap;
        }
        .pesona-d-unit-main {
            font-weight: 700;
            color: #0F172A;
        }
        .pesona-d-unit-sub {
            font-size: 11.5px;
            color: #64748B;
        }
        .pesona-d-stock-cell {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
            white-space: nowrap;
        }
        .pesona-d-actions {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
            white-space: nowrap;
        }

        /* Empty State */
        .pesona-items-empty {
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
        .pesona-items-empty-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--pesona-primary-soft, #EEF2F8);
            color: var(--pesona-primary, #28355A);
        }
        .pesona-items-empty-title {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: #0F172A;
        }
        .pesona-items-empty-sub {
            margin: 0;
            max-width: 400px;
            font-size: 13px;
            color: #64748B;
            line-height: 1.5;
        }

        @media (min-width: 640px) {
            .pesona-items-head {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
            .pesona-items-create-btn {
                width: auto;
            }
        }

        @media (min-width: 768px) {
            .pesona-items-toolbar {
                flex-direction: row;
                align-items: center;
                flex-wrap: wrap;
                gap: 10px;
            }
            .pesona-items-search-wrap {
                width: auto;
                flex: 1 1 220px;
            }
            .pesona-items-filter-group {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .pesona-items-select-status {
                width: auto;
                min-width: 150px;
            }
            .pesona-items-select-stock {
                display: none;
            }
            .pesona-stock-pills {
                display: inline-flex;
            }
            .pesona-m-items-list {
                display: none;
            }
            .pesona-d-table-wrap {
                display: block;
            }
        }
    </style>

    <div class="pesona-items-page">
        {{-- Header --}}
        <div class="pesona-items-head">
            <div class="pesona-items-head-main">
                <div class="pesona-items-title-row">
                    <h1 class="pesona-title">Master Barang</h1>
                    <span class="pesona-items-total-pill" id="pesonaItemsTotalCount">{{ $items->total() }} barang</span>
                </div>
                <p class="pesona-subtitle">Kelola data barang, konversi satuan ambil, penerimaan stok masuk, dan koreksi stok garasi PT Pesona.</p>
            </div>
            <a class="pesona-items-create-btn" href="{{ route('inventory.pesona.admin.items.create') }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                <span>Tambah Barang</span>
            </a>
        </div>

        {{-- Filter & AJAX Live Search Toolbar --}}
        <form id="pesonaAdminItemsFilterForm" class="pesona-items-toolbar" method="GET" action="{{ route('inventory.pesona.admin.items.index') }}">
            <div class="pesona-items-search-wrap">
                <svg class="pesona-items-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <input
                    id="pesonaAdminItemsSearch"
                    class="pesona-items-search-input"
                    type="search"
                    name="q"
                    maxlength="150"
                    value="{{ $filters['q'] }}"
                    placeholder="Cari nama barang..."
                    autocomplete="off"
                    aria-label="Cari nama barang"
                >
                <div class="pesona-items-search-actions">
                    <span id="pesonaAdminItemsSpinner" class="pesona-items-spinner" aria-hidden="true"></span>
                    <button
                        id="pesonaAdminItemsClear"
                        class="pesona-items-clear-btn {{ $filters['q'] !== '' ? 'is-visible' : '' }}"
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

            <div class="pesona-items-filter-group">
                <select id="pesonaAdminItemsStatus" class="pesona-items-select pesona-items-select-status" name="status" aria-label="Status barang">
                    <option value="all" @selected($filters['status'] === 'all')>Semua Status</option>
                    <option value="active" @selected($filters['status'] === 'active')>Status: Aktif</option>
                    <option value="inactive" @selected($filters['status'] === 'inactive')>Status: Nonaktif</option>
                </select>

                <select id="pesonaAdminItemsStock" class="pesona-items-select pesona-items-select-stock" name="stock" aria-label="Status stok">
                    <option value="all" @selected($filters['stock'] === 'all')>Semua Stok</option>
                    <option value="available" @selected($filters['stock'] === 'available')>Stok: Tersedia</option>
                    <option value="low" @selected($filters['stock'] === 'low')>Stok: Tipis</option>
                    <option value="out" @selected($filters['stock'] === 'out')>Stok: Habis</option>
                </select>

                <div class="pesona-stock-pills" role="group" aria-label="Filter status stok">
                    @foreach($stockTabs as $stockValue => $stockLabel)
                        <button
                            type="button"
                            class="pesona-stock-pill {{ $filters['stock'] === $stockValue ? 'is-active' : '' }}"
                            data-stock-pill="{{ $stockValue }}"
                        >
                            {{ $stockLabel }}
                        </button>
                    @endforeach
                </div>
            </div>

            <a
                id="pesonaAdminItemsReset"
                class="pesona-items-reset-btn {{ $hasActiveFilter ? 'is-visible' : '' }}"
                href="{{ route('inventory.pesona.admin.items.index') }}"
            >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                    <path d="M3 3v5h5"/>
                </svg>
                <span>Reset</span>
            </a>
        </form>

        {{-- Results Area --}}
        <div id="pesonaAdminItemsResults" class="pesona-items-results-wrap">
            @if($items->isEmpty())
                <div class="pesona-items-empty">
                    <span class="pesona-items-empty-icon" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                            <path d="m3.3 7 8.7 5 8.7-5M12 22V12"/>
                        </svg>
                    </span>
                    <p class="pesona-items-empty-title">
                        {{ $hasActiveFilter ? 'Tidak Ada Barang yang Cocok' : 'Belum Ada Data Barang' }}
                    </p>
                    <p class="pesona-items-empty-sub">
                        {{ $hasActiveFilter ? 'Tidak ada barang yang sesuai dengan kata kunci pencarian atau filter yang dipilih.' : 'Belum ada barang untuk ditampilkan pada halaman ini. Tambahkan barang baru untuk mulai mencatat stok.' }}
                    </p>
                </div>
            @else
                {{-- Mobile Cards (< 768px) --}}
                <div class="pesona-m-items-list">
                    @foreach($items as $item)
                        @php
                            $isOut = $item->stock_qty <= 0;
                            $isLow = ! $isOut && $item->minimum_stock > 0 && $item->stock_qty <= $item->minimum_stock;
                        @endphp
                        <article class="pesona-m-item-card {{ ! $item->is_active ? 'is-inactive' : '' }}">
                            <div class="pesona-m-item-top">
                                @if($item->image_path)
                                    <img class="pesona-item-thumb" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}" alt="Foto {{ $item->name }}" loading="lazy">
                                @else
                                    <span class="pesona-item-thumb pesona-item-thumb-placeholder" aria-label="Tanpa foto">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                            <path d="m3.3 7 8.7 5 8.7-5M12 22V12"/>
                                        </svg>
                                    </span>
                                @endif
                                <div class="pesona-m-item-body">
                                    <div class="pesona-m-item-title-row">
                                        <a class="pesona-item-name-link" href="{{ route('inventory.pesona.admin.items.edit', $item) }}">
                                            {{ $item->name }}
                                        </a>
                                        <span class="pesona-badge {{ $item->is_active ? 'pesona-badge-active' : 'pesona-badge-inactive' }}">
                                            <span class="pesona-badge-dot"></span>
                                            {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                    @if($item->description)
                                        <p class="pesona-item-desc">{{ $item->description }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="pesona-m-item-metrics">
                                <div class="pesona-m-metric">
                                    <span class="pesona-m-metric-label">Stok Saat Ini</span>
                                    <div>
                                        @if($isOut)
                                            <span class="pesona-badge pesona-badge-out">
                                                <span class="pesona-badge-dot"></span>
                                                Habis (0 {{ $item->unit_name }})
                                            </span>
                                        @elseif($isLow)
                                            <span class="pesona-badge pesona-badge-low">
                                                <span class="pesona-badge-dot"></span>
                                                Tipis ({{ $item->stock_qty }} {{ $item->unit_name }})
                                            </span>
                                        @else
                                            <span class="pesona-badge pesona-badge-available">
                                                <span class="pesona-badge-dot"></span>
                                                {{ $item->stock_qty }} {{ $item->unit_name }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="pesona-m-metric-sub">Batas tipis: {{ $item->minimum_stock }} {{ $item->unit_name }}</span>
                                </div>
                                <div class="pesona-m-metric">
                                    <span class="pesona-m-metric-label">Satuan Ambil</span>
                                    <span class="pesona-m-metric-val">
                                        {{ $item->unit_name }}
                                        @if($item->unit_size > 1)
                                            <span style="font-weight:500;color:#64748B">(1 {{ $item->unit_name }} = {{ $item->unit_size }} {{ $item->content_unit_name }})</span>
                                        @endif
                                    </span>
                                    <span class="pesona-m-metric-sub">Min. ambil: {{ $item->min_request_qty }} {{ $item->unit_name }}</span>
                                </div>
                            </div>

                            <div class="pesona-m-item-actions">
                                <a class="pesona-act-btn pesona-act-btn-stockin" href="{{ route('inventory.pesona.admin.stock-in.create', $item) }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M12 5v14M5 12h14"/>
                                    </svg>
                                    <span>Barang Masuk</span>
                                </a>
                                <a class="pesona-act-btn" href="{{ route('inventory.pesona.admin.stock-adjustment.create', $item) }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/>
                                        <line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/>
                                        <line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/>
                                        <line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/>
                                    </svg>
                                    <span>Koreksi Stok</span>
                                </a>
                                <a class="pesona-act-btn" href="{{ route('inventory.pesona.admin.stock-movements.index', ['item_id' => $item->id]) }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M7 20V4m0 0L3 8m4-4 4 4M17 4v16m0 0-4-4m4 4 4-4"/>
                                    </svg>
                                    <span>Riwayat Stok</span>
                                </a>
                                <a class="pesona-act-btn" href="{{ route('inventory.pesona.admin.items.edit', $item) }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                    </svg>
                                    <span>Edit</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{-- Desktop Table (>= 768px) --}}
                <div class="pesona-d-table-wrap">
                    <table class="pesona-d-table">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Satuan Ambil</th>
                                <th>Stok</th>
                                <th>Batas Stok Tipis</th>
                                <th>Status</th>
                                <th style="text-align:right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                @php
                                    $isOut = $item->stock_qty <= 0;
                                    $isLow = ! $isOut && $item->minimum_stock > 0 && $item->stock_qty <= $item->minimum_stock;
                                @endphp
                                <tr class="{{ ! $item->is_active ? 'is-inactive' : '' }}">
                                    <td>
                                        <div class="pesona-d-item-cell">
                                            @if($item->image_path)
                                                <img class="pesona-item-thumb" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}" alt="Foto {{ $item->name }}" loading="lazy">
                                            @else
                                                <span class="pesona-item-thumb pesona-item-thumb-placeholder" aria-label="Tanpa foto">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                                        <path d="m3.3 7 8.7 5 8.7-5M12 22V12"/>
                                                    </svg>
                                                </span>
                                            @endif
                                            <div class="pesona-d-item-text">
                                                <a class="pesona-item-name-link" href="{{ route('inventory.pesona.admin.items.edit', $item) }}">
                                                    {{ $item->name }}
                                                </a>
                                                @if($item->description)
                                                    <p class="pesona-item-desc">{{ $item->description }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="pesona-d-unit-cell">
                                            <span class="pesona-d-unit-main">{{ $item->unit_name }}</span>
                                            @if($item->unit_size > 1)
                                                <span class="pesona-d-unit-sub">1 {{ $item->unit_name }} = {{ $item->unit_size }} {{ $item->content_unit_name }}</span>
                                            @endif
                                            <span class="pesona-d-unit-sub">Min. ambil: {{ $item->min_request_qty }} {{ $item->unit_name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="pesona-d-stock-cell">
                                            @if($isOut)
                                                <span class="pesona-badge pesona-badge-out">
                                                    <span class="pesona-badge-dot"></span>
                                                    Habis · 0 {{ $item->unit_name }}
                                                </span>
                                            @elseif($isLow)
                                                <span class="pesona-badge pesona-badge-low">
                                                    <span class="pesona-badge-dot"></span>
                                                    Tipis · {{ $item->stock_qty }} {{ $item->unit_name }}
                                                </span>
                                            @else
                                                <span class="pesona-badge pesona-badge-available">
                                                    <span class="pesona-badge-dot"></span>
                                                    {{ $item->stock_qty }} {{ $item->unit_name }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="white-space:nowrap;color:#475569;font-weight:600">
                                        {{ $item->minimum_stock }} {{ $item->unit_name }}
                                    </td>
                                    <td style="white-space:nowrap">
                                        <span class="pesona-badge {{ $item->is_active ? 'pesona-badge-active' : 'pesona-badge-inactive' }}">
                                            <span class="pesona-badge-dot"></span>
                                            {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td style="text-align:right">
                                        <div class="pesona-d-actions">
                                            <a class="pesona-act-btn pesona-act-btn-stockin" href="{{ route('inventory.pesona.admin.stock-in.create', $item) }}">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M12 5v14M5 12h14"/>
                                                </svg>
                                                <span>Barang Masuk</span>
                                            </a>
                                            <a class="pesona-act-btn" href="{{ route('inventory.pesona.admin.stock-adjustment.create', $item) }}">
                                                <span>Koreksi Stok</span>
                                            </a>
                                            <a class="pesona-act-btn" href="{{ route('inventory.pesona.admin.stock-movements.index', ['item_id' => $item->id]) }}">
                                                <span>Riwayat Stok</span>
                                            </a>
                                            <a class="pesona-act-btn" href="{{ route('inventory.pesona.admin.items.edit', $item) }}">
                                                <span>Edit</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <x-pagination :items="$items" />
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('pesonaAdminItemsFilterForm');
            const searchInput = document.getElementById('pesonaAdminItemsSearch');
            const clearBtn = document.getElementById('pesonaAdminItemsClear');
            const spinner = document.getElementById('pesonaAdminItemsSpinner');
            const statusSelect = document.getElementById('pesonaAdminItemsStatus');
            const stockSelect = document.getElementById('pesonaAdminItemsStock');
            const stockPills = Array.from(document.querySelectorAll('[data-stock-pill]'));
            const resetBtn = document.getElementById('pesonaAdminItemsReset');
            const resultsWrap = document.getElementById('pesonaAdminItemsResults');
            const totalBadge = document.getElementById('pesonaItemsTotalCount');

            if (!form || !resultsWrap) return;

            let debounceTimer = null;
            let activeController = null;

            function syncUIControls() {
                const qVal = (searchInput?.value || '').trim();
                const statusVal = statusSelect?.value || 'all';
                const stockVal = stockSelect?.value || 'all';

                clearBtn?.classList.toggle('is-visible', qVal.length > 0);
                stockPills.forEach((pill) => {
                    pill.classList.toggle('is-active', pill.getAttribute('data-stock-pill') === stockVal);
                });
                resetBtn?.classList.toggle('is-visible', qVal !== '' || statusVal !== 'all' || stockVal !== 'all');
            }

            function buildFilterUrl() {
                const url = new URL(form.action, window.location.origin);
                const qVal = (searchInput?.value || '').trim();
                const statusVal = statusSelect?.value || 'all';
                const stockVal = stockSelect?.value || 'all';

                if (qVal !== '') url.searchParams.set('q', qVal);
                if (statusVal !== 'all') url.searchParams.set('status', statusVal);
                if (stockVal !== 'all') url.searchParams.set('stock', stockVal);

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
                    .then((response) => {
                        if (!response.ok) throw new Error('Network error');
                        return response.text();
                    })
                    .then((html) => {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const nextResults = doc.getElementById('pesonaAdminItemsResults');
                        const nextTotal = doc.getElementById('pesonaItemsTotalCount');

                        if (!nextResults) {
                            window.location.href = targetUrl.toString();
                            return;
                        }

                        resultsWrap.innerHTML = nextResults.innerHTML;
                        if (nextTotal && totalBadge) {
                            totalBadge.textContent = nextTotal.textContent;
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
                debounceTimer = setTimeout(triggerFilter, 300);
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

            stockSelect?.addEventListener('change', function () {
                clearTimeout(debounceTimer);
                triggerFilter();
            });

            stockPills.forEach((pill) => {
                pill.addEventListener('click', function () {
                    const value = pill.getAttribute('data-stock-pill') || 'all';
                    if (stockSelect) stockSelect.value = value;
                    clearTimeout(debounceTimer);
                    triggerFilter();
                });
            });

            resetBtn?.addEventListener('click', function (event) {
                event.preventDefault();
                if (searchInput) searchInput.value = '';
                if (statusSelect) statusSelect.value = 'all';
                if (stockSelect) stockSelect.value = 'all';
                clearTimeout(debounceTimer);
                triggerFilter();
            });

            resultsWrap.addEventListener('click', function (event) {
                const pageLink = event.target.closest('nav[aria-label="Pagination"] a, .hrd-pagination a');
                if (!pageLink) return;
                event.preventDefault();
                loadResults(new URL(pageLink.href, window.location.origin));
            });

            syncUIControls();
        })();
    </script>
</x-inv-pesona-app>
