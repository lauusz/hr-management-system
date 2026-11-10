<x-inv-pesona-app title="Riwayat Stok — Inventory Pesona">
    <style>
        .pesona-mov-shell {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Header */
        .pesona-mov-header {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .pesona-mov-title-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }
        .pesona-mov-count-pill {
            display: inline-flex;
            align-items: center;
            min-height: 26px;
            padding: 2px 10px;
            border-radius: 999px;
            background: rgba(40, 53, 90, 0.08);
            color: var(--pesona-primary);
            font-size: 12px;
            font-weight: 800;
        }
        .pesona-mov-header-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .pesona-mov-btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 40px;
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px solid #CBD5E1;
            background: #FFFFFF;
            color: #1E293B;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }
        .pesona-mov-btn-secondary:hover {
            background: #F8FAFC;
            border-color: #94A3B8;
        }

        /* Color Legend & Quick Filter Bar */
        .pesona-mov-legend-bar {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 12px 16px;
            border: 1px solid var(--pesona-border);
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .pesona-mov-legend-label {
            font-size: 12.5px;
            font-weight: 700;
            color: #475569;
        }
        .pesona-mov-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .pesona-mov-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 34px;
            padding: 5px 13px;
            border-radius: 999px;
            border: 1.5px solid #E2E8F0;
            background: #F8FAFC;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .pesona-mov-pill:hover {
            border-color: #CBD5E1;
            background: #F1F5F9;
        }
        .pesona-mov-pill.is-active-all {
            background: var(--pesona-primary);
            border-color: var(--pesona-primary);
            color: #FFFFFF;
        }
        .pesona-mov-pill.is-active-in {
            background: #DCFCE7;
            border-color: #16A34A;
            color: #14532D;
            box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.15);
        }
        .pesona-mov-pill.is-active-out {
            background: #FEE2E2;
            border-color: #DC2626;
            color: #7F1D1D;
            box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.15);
        }
        .pesona-mov-pill.is-active-adj {
            background: #FFFFFF;
            border-color: #475569;
            color: #0F172A;
            box-shadow: 0 0 0 2px rgba(71, 85, 105, 0.15);
        }
        .pesona-mov-swatch {
            width: 11px;
            height: 11px;
            border-radius: 3px;
            flex-shrink: 0;
        }
        .pesona-mov-swatch.is-in {
            background: #22C55E;
            border: 1px solid #15803D;
        }
        .pesona-mov-swatch.is-out {
            background: #EF4444;
            border: 1px solid #B91C1C;
        }
        .pesona-mov-swatch.is-adj {
            background: #FFFFFF;
            border: 1.5px solid #64748B;
        }

        /* Filter Toolbar */
        .pesona-mov-toolbar {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 14px 16px;
            border: 1px solid var(--pesona-border);
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .pesona-mov-filter-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .pesona-mov-field {
            display: flex;
            flex-direction: column;
            gap: 5px;
            min-width: 0;
        }
        .pesona-mov-field-label {
            font-size: 12px;
            font-weight: 700;
            color: #334155;
        }
        .pesona-mov-search-wrap {
            position: relative;
            width: 100%;
        }
        .pesona-mov-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            pointer-events: none;
        }
        .pesona-mov-input,
        .pesona-mov-select {
            width: 100%;
            min-height: 38px;
            padding: 7px 11px;
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            background: #F8FAFC;
            color: #0F172A;
            font: inherit;
            font-size: 13px;
            box-sizing: border-box;
            transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
        }
        .pesona-mov-input.has-icon {
            padding-left: 36px;
        }
        .pesona-mov-input.has-clear {
            padding-right: 34px;
        }
        .pesona-mov-input:focus,
        .pesona-mov-select:focus {
            outline: none;
            background: #FFFFFF;
            border-color: var(--pesona-primary);
            box-shadow: 0 0 0 3px rgba(40, 53, 90, 0.12);
        }
        .pesona-mov-field-error {
            color: #B91C1C;
            font-size: 11.5px;
            font-weight: 600;
        }

        /* Searchable Item Combobox */
        .pesona-item-combo {
            position: relative;
            width: 100%;
        }
        .pesona-item-combo-clear {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 22px;
            height: 22px;
            border: 0;
            border-radius: 6px;
            background: #E2E8F0;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 0;
            transition: background-color 0.15s ease, color 0.15s ease;
        }
        .pesona-item-combo-clear:hover {
            background: #CBD5E1;
            color: #0F172A;
        }
        .pesona-item-dropdown {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            z-index: 60;
            max-height: 280px;
            overflow-y: auto;
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
            border-radius: 12px;
            box-shadow: 0 12px 28px -6px rgba(15, 23, 42, 0.18), 0 4px 10px -4px rgba(15, 23, 42, 0.08);
            padding: 5px;
            display: none;
        }
        .pesona-item-dropdown.is-open {
            display: block;
        }
        .pesona-item-option {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 8px 10px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #0F172A;
            font: inherit;
            font-size: 12.5px;
            text-align: left;
            cursor: pointer;
            box-sizing: border-box;
            transition: background-color 0.12s ease;
        }
        .pesona-item-option:hover,
        .pesona-item-option.is-highlighted {
            background: #F1F5F9;
        }
        .pesona-item-option.is-selected {
            background: rgba(40, 53, 90, 0.08);
            color: var(--pesona-primary);
            font-weight: 800;
        }
        .pesona-item-option-main {
            min-width: 0;
            flex: 1;
        }
        .pesona-item-option-name {
            font-weight: 700;
            color: #0F172A;
            line-height: 1.3;
            overflow-wrap: anywhere;
        }
        .pesona-item-option-sub {
            font-size: 11px;
            color: #64748B;
            margin-top: 2px;
        }
        .pesona-item-option-badge {
            flex-shrink: 0;
            padding: 2px 7px;
            border-radius: 6px;
            background: #F1F5F9;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }
        .pesona-item-option-badge.is-inactive {
            background: #FEE2E2;
            color: #991B1B;
        }
        .pesona-item-dropdown-status {
            padding: 12px 10px;
            text-align: center;
            font-size: 12px;
            color: #64748B;
        }

        .pesona-mov-filter-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding-top: 6px;
            border-top: 1px dashed #E2E8F0;
        }
        .pesona-mov-filter-hint {
            font-size: 12px;
            color: #64748B;
        }
        .pesona-mov-filter-btns {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .pesona-mov-reset-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 34px;
            padding: 5px 12px;
            border-radius: 8px;
            border: 1px solid #CBD5E1;
            background: #FFFFFF;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }
        .pesona-mov-reset-btn:hover {
            background: #F8FAFC;
            color: #0F172A;
        }

        /* Results Container */
        .pesona-mov-results-wrap {
            transition: opacity 0.18s ease;
        }
        .pesona-mov-results-wrap.is-loading {
            opacity: 0.55;
            pointer-events: none;
        }

        /* Status & Activity Badges */
        .pesona-mov-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            min-height: 24px;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 800;
            white-space: nowrap;
            line-height: 1.2;
        }
        .pesona-mov-badge.is-in {
            background: #16A34A;
            color: #FFFFFF;
        }
        .pesona-mov-badge.is-out {
            background: #DC2626;
            color: #FFFFFF;
        }
        .pesona-mov-badge.is-adj {
            background: #F1F5F9;
            color: #1E293B;
            border: 1px solid #94A3B8;
        }

        /* Unified 2-Line Cell Structure */
        .pesona-mov-cell-primary {
            font-size: 13.5px;
            font-weight: 800;
            color: #0F172A;
            line-height: 1.35;
        }
        .pesona-mov-cell-primary.is-in {
            color: #14532D;
        }
        .pesona-mov-cell-primary.is-out {
            color: #7F1D1D;
        }
        .pesona-mov-cell-primary.is-adj {
            color: #0F172A;
        }
        .pesona-mov-cell-secondary {
            margin-top: 3px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            line-height: 1.35;
        }
        .pesona-mov-cell-secondary strong {
            color: #0F172A;
            font-weight: 700;
        }
        .pesona-mov-nowrap {
            white-space: nowrap;
        }

        /* Origin Link */
        .pesona-mov-origin-link {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: var(--pesona-primary);
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .pesona-mov-origin-link:hover {
            text-decoration: underline;
        }
        .pesona-mov-nopol-tag {
            display: inline-flex;
            align-items: center;
            padding: 2px 7px;
            border-radius: 6px;
            background: rgba(15, 23, 42, 0.06);
            border: 1px solid rgba(15, 23, 42, 0.12);
            color: #1E293B;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        /* Mobile Cards (Default < 900px) */
        .pesona-mov-cards {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .pesona-mov-card {
            border-radius: 14px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            box-sizing: border-box;
            overflow: hidden;
        }
        .pesona-mov-card.row-in {
            background-color: #ECFDF5;
            border: 1px solid #86EFAC;
            border-left: 5px solid #16A34A;
        }
        .pesona-mov-card.row-out {
            background-color: #FEF2F2;
            border: 1px solid #FCA5A5;
            border-left: 5px solid #DC2626;
        }
        .pesona-mov-card.row-adj {
            background-color: #FFFFFF;
            border: 1px solid #CBD5E1;
            border-left: 5px solid #64748B;
        }

        .pesona-mov-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }
        .pesona-mov-card-head-left {
            min-width: 0;
            flex: 1;
        }
        .pesona-mov-card-item {
            font-size: 14px;
            font-weight: 800;
            color: #0F172A;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }
        .pesona-mov-card-time {
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
            margin-top: 2px;
        }
        .pesona-mov-card-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 10px 12px;
            padding: 11px 12px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(15, 23, 42, 0.07);
            box-sizing: border-box;
        }
        .pesona-mov-card-cell {
            min-width: 0;
            overflow-wrap: anywhere;
        }
        .pesona-mov-card-cell.is-full {
            grid-column: 1 / -1;
            padding-top: 8px;
            border-top: 1px dashed rgba(15, 23, 42, 0.1);
        }
        .pesona-mov-card-cell-label {
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748B;
            margin-bottom: 3px;
        }
        .pesona-mov-card-inline-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
        }
        .pesona-mov-card-note {
            padding: 9px 11px;
            border-radius: 9px;
            background: rgba(255, 255, 255, 0.78);
            border: 1px dashed rgba(15, 23, 42, 0.15);
            font-size: 12px;
            line-height: 1.45;
            color: #334155;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        /* Desktop Table (Hidden on Mobile, Shown >= 900px) */
        .pesona-mov-table-wrap {
            display: none;
            overflow-x: auto;
            border: 1px solid #CBD5E1;
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        }
        .pesona-mov-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .pesona-mov-table th {
            padding: 11px 14px;
            background: #F8FAFC;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
            border-bottom: 1px solid #CBD5E1;
            white-space: nowrap;
        }
        .pesona-mov-table td {
            padding: 12px 14px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            vertical-align: middle;
            color: #0F172A;
        }
        .pesona-mov-table tbody tr:last-child td {
            border-bottom: 0;
        }

        /* Full-Row Color Highlight on Desktop Table */
        .pesona-mov-table tbody tr.row-in td {
            background-color: #ECFDF5;
            border-bottom-color: #A7F3D0;
        }
        .pesona-mov-table tbody tr.row-in:hover td {
            background-color: #D1FAE5;
        }
        .pesona-mov-table tbody tr.row-in td:first-child {
            box-shadow: inset 4px 0 0 #16A34A;
        }

        .pesona-mov-table tbody tr.row-out td {
            background-color: #FEF2F2;
            border-bottom-color: #FECACA;
        }
        .pesona-mov-table tbody tr.row-out:hover td {
            background-color: #FEE2E2;
        }
        .pesona-mov-table tbody tr.row-out td:first-child {
            box-shadow: inset 4px 0 0 #DC2626;
        }

        .pesona-mov-table tbody tr.row-adj td {
            background-color: #FFFFFF;
            border-bottom-color: #E2E8F0;
        }
        .pesona-mov-table tbody tr.row-adj:hover td {
            background-color: #F8FAFC;
        }
        .pesona-mov-table tbody tr.row-adj td:first-child {
            box-shadow: inset 4px 0 0 #94A3B8;
        }

        .pesona-mov-col-time {
            width: 110px;
            white-space: nowrap;
        }
        .pesona-mov-col-item {
            min-width: 190px;
        }
        .pesona-mov-col-activity {
            width: 150px;
            white-space: nowrap;
        }
        .pesona-mov-col-stock {
            width: 165px;
            white-space: nowrap;
        }
        .pesona-mov-col-price {
            width: 155px;
            white-space: nowrap;
        }
        .pesona-mov-col-actor {
            width: 155px;
            white-space: nowrap;
        }
        .pesona-mov-col-note {
            min-width: 210px;
        }
        .pesona-mov-note-text {
            font-size: 12.5px;
            color: #334155;
            line-height: 1.45;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        /* Empty State */
        .pesona-mov-empty {
            padding: 44px 20px;
            border: 1px dashed #CBD5E1;
            border-radius: 14px;
            background: #FFFFFF;
            text-align: center;
        }
        .pesona-mov-empty-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 12px;
            border-radius: 12px;
            background: #F1F5F9;
            color: #64748B;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pesona-mov-empty-title {
            margin: 0 0 4px;
            font-size: 15px;
            font-weight: 800;
            color: #0F172A;
        }
        .pesona-mov-empty-sub {
            margin: 0;
            font-size: 13px;
            color: #64748B;
        }

        @media (min-width: 640px) {
            .pesona-mov-header {
                flex-direction: row;
                align-items: flex-start;
                justify-content: space-between;
            }
            .pesona-mov-legend-bar {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
            .pesona-mov-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 900px) {
            .pesona-mov-filter-grid {
                grid-template-columns: 1.8fr 1.6fr 1.2fr 1fr 1fr;
            }
            .pesona-mov-cards {
                display: none;
            }
            .pesona-mov-table-wrap {
                display: block;
            }
        }
    </style>

    @php
        $hasActiveFilter = $filters['q'] !== ''
            || $filters['item_id'] !== ''
            || $filters['movement_type'] !== ''
            || $filters['date_from'] !== ''
            || $filters['date_to'] !== '';
        $selectedItemDisplay = $selectedItem
            ? $selectedItem->name.($selectedItem->unit_name ? ' ('.$selectedItem->unit_name.')' : '')
            : '';
        $initialItemsPayload = $items->map(fn ($item): array => [
            'id' => $item->id,
            'name' => $item->name,
            'unit_name' => $item->unit_name,
            'stock' => (int) ($item->stock_qty ?? 0),
            'is_active' => (bool) $item->is_active && $item->deleted_at === null,
        ])->values();
    @endphp

    <div class="pesona-mov-shell">
        {{-- Page Header --}}
        <div class="pesona-mov-header">
            <div>
                <div class="pesona-mov-title-row">
                    <h1 class="pesona-title">Riwayat Keluar-Masuk Stok</h1>
                    <span class="pesona-mov-count-pill" id="pesona-mov-total-pill">{{ $movements->total() }} catatan</span>
                </div>
                <p class="pesona-subtitle">
                    Catatan lengkap barang masuk (pembelian baru), barang keluar (diambil karyawan), dan perubahan stok manual di garasi PT Pesona.
                </p>
            </div>
            <div class="pesona-mov-header-actions">
                <a class="pesona-mov-btn-secondary" href="{{ route('inventory.pesona.admin.items.index') }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                    Ke Master Barang
                </a>
            </div>
        </div>

        {{-- Color Guide & Quick Filter Pills --}}
        <div class="pesona-mov-legend-bar" aria-label="Petunjuk warna baris dan filter cepat">
            <span class="pesona-mov-legend-label">Klik warna untuk memfilter jenis aktivitas:</span>
            <div class="pesona-mov-pills" id="pesona-mov-type-pills">
                <button
                    type="button"
                    class="pesona-mov-pill {{ $filters['movement_type'] === '' ? 'is-active-all' : '' }}"
                    data-type=""
                >
                    Semua Riwayat
                </button>
                <button
                    type="button"
                    class="pesona-mov-pill {{ $filters['movement_type'] === 'IN' ? 'is-active-in' : '' }}"
                    data-type="IN"
                >
                    <span class="pesona-mov-swatch is-in" aria-hidden="true"></span>
                    Hijau = Barang Masuk
                </button>
                <button
                    type="button"
                    class="pesona-mov-pill {{ $filters['movement_type'] === 'OUT' ? 'is-active-out' : '' }}"
                    data-type="OUT"
                >
                    <span class="pesona-mov-swatch is-out" aria-hidden="true"></span>
                    Merah = Barang Keluar
                </button>
                <button
                    type="button"
                    class="pesona-mov-pill {{ $filters['movement_type'] === 'ADJUSTMENT' ? 'is-active-adj' : '' }}"
                    data-type="ADJUSTMENT"
                >
                    <span class="pesona-mov-swatch is-adj" aria-hidden="true"></span>
                    Putih = Edit Stok Manual
                </button>
            </div>
        </div>

        {{-- Filter Form --}}
        <form
            id="pesona-mov-filter-form"
            class="pesona-mov-toolbar"
            method="GET"
            action="{{ route('inventory.pesona.admin.stock-movements.index') }}"
        >
            <div class="pesona-mov-filter-grid">
                <label class="pesona-mov-field">
                    <span class="pesona-mov-field-label">Cari Barang / Petugas / Keterangan</span>
                    <div class="pesona-mov-search-wrap">
                        <svg class="pesona-mov-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input
                            type="search"
                            id="pesona-mov-q"
                            name="q"
                            class="pesona-mov-input has-icon"
                            maxlength="200"
                            value="{{ old('q', $filters['q']) }}"
                            placeholder="Ketik nama barang, petugas, nomor pengambilan, atau catatan..."
                            autocomplete="off"
                        >
                    </div>
                    @error('q') <span class="pesona-mov-field-error">{{ $message }}</span> @enderror
                </label>

                {{-- Pilih Barang (Live Database Search Combobox) --}}
                <div class="pesona-mov-field">
                    <label class="pesona-mov-field-label" for="pesona-mov-item-search">Pilih Barang (Cari di Database)</label>
                    <div class="pesona-item-combo" id="pesona-item-combo">
                        <input
                            type="hidden"
                            id="pesona-mov-item"
                            name="item_id"
                            value="{{ old('item_id', $filters['item_id']) }}"
                        >
                        <svg class="pesona-mov-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        </svg>
                        <input
                            type="text"
                            id="pesona-mov-item-search"
                            class="pesona-mov-input has-icon has-clear"
                            value="{{ $selectedItemDisplay }}"
                            placeholder="Semua Barang (ketik untuk cari...)"
                            autocomplete="off"
                            role="combobox"
                            aria-expanded="false"
                            aria-controls="pesona-mov-item-dropdown"
                            @error('item_id') aria-invalid="true" @enderror
                        >
                        <button
                            type="button"
                            id="pesona-mov-item-clear"
                            class="pesona-item-combo-clear"
                            title="Tampilkan Semua Barang"
                            aria-label="Hapus pilihan barang"
                            style="{{ $selectedItemDisplay !== '' ? '' : 'display:none;' }}"
                        >
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                        <div
                            id="pesona-mov-item-dropdown"
                            class="pesona-item-dropdown"
                            role="listbox"
                            aria-label="Hasil pencarian barang"
                        ></div>
                    </div>
                    @error('item_id') <span class="pesona-mov-field-error">{{ $message }}</span> @enderror
                </div>

                <label class="pesona-mov-field">
                    <span class="pesona-mov-field-label">Jenis Aktivitas</span>
                    <select id="pesona-mov-type" name="movement_type" class="pesona-mov-select">
                        <option value="" @selected($filters['movement_type'] === '')>Semua Aktivitas</option>
                        <option value="IN" @selected($filters['movement_type'] === 'IN')>Barang Masuk (Stok Bertambah)</option>
                        <option value="OUT" @selected($filters['movement_type'] === 'OUT')>Barang Keluar (Diambil)</option>
                        <option value="ADJUSTMENT" @selected($filters['movement_type'] === 'ADJUSTMENT')>Edit Stok Manual (Penyesuaian)</option>
                    </select>
                    @error('movement_type') <span class="pesona-mov-field-error">{{ $message }}</span> @enderror
                </label>

                <label class="pesona-mov-field">
                    <span class="pesona-mov-field-label">Dari Tanggal</span>
                    <input
                        type="date"
                        id="pesona-mov-from"
                        name="date_from"
                        class="pesona-mov-input"
                        value="{{ old('date_from', $filters['date_from']) }}"
                        @error('date_from') aria-invalid="true" @enderror
                    >
                    @error('date_from') <span class="pesona-mov-field-error">{{ $message }}</span> @enderror
                </label>

                <label class="pesona-mov-field">
                    <span class="pesona-mov-field-label">Sampai Tanggal</span>
                    <input
                        type="date"
                        id="pesona-mov-to"
                        name="date_to"
                        class="pesona-mov-input"
                        value="{{ old('date_to', $filters['date_to']) }}"
                        @error('date_to') aria-invalid="true" @enderror
                    >
                    @error('date_to') <span class="pesona-mov-field-error">{{ $message }}</span> @enderror
                </label>
            </div>

            <div class="pesona-mov-filter-actions">
                <span class="pesona-mov-filter-hint">
                    Pencarian dan filter di atas otomatis memperbarui daftar di bawah.
                </span>
                <div class="pesona-mov-filter-btns">
                    <a
                        id="pesona-mov-reset"
                        class="pesona-mov-reset-btn"
                        href="{{ route('inventory.pesona.admin.stock-movements.index') }}"
                        style="{{ $hasActiveFilter ? '' : 'display:none;' }}"
                    >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                        Reset Filter
                    </a>
                    <noscript>
                        <button class="pesona-button" type="submit">Terapkan Filter</button>
                    </noscript>
                </div>
            </div>
        </form>

        {{-- Results Area (Updated via AJAX) --}}
        <div id="pesona-movements-results" class="pesona-mov-results-wrap" data-total="{{ $movements->total() }}">
            @if($movements->isEmpty())
                <div class="pesona-mov-empty">
                    <div class="pesona-mov-empty-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                            <polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>
                        </svg>
                    </div>
                    <p class="pesona-mov-empty-title">Belum Ada Riwayat Keluar-Masuk Stok</p>
                    <p class="pesona-mov-empty-sub">Tidak ditemukan catatan perubahan stok yang cocok dengan pencarian atau filter Anda.</p>
                </div>
            @else
                {{-- Mobile Cards (< 900px) --}}
                <div class="pesona-mov-cards">
                    @foreach($movements as $movement)
                        @php
                            $unit = $movement->item?->unit_name ?? '';
                            $itemName = $movement->item?->name ?? ('Barang tidak tersedia (ID '.$movement->inv_pesona_item_id.')');
                            $rowClass = match ($movement->movement_type) {
                                'IN' => 'row-in',
                                'OUT' => 'row-out',
                                default => 'row-adj',
                            };
                            $badgeClass = match ($movement->movement_type) {
                                'IN' => 'is-in',
                                'OUT' => 'is-out',
                                default => 'is-adj',
                            };
                            $typeLabel = match ($movement->movement_type) {
                                'IN' => 'Barang Masuk',
                                'OUT' => 'Barang Keluar',
                                'ADJUSTMENT' => 'Edit Manual',
                                default => 'Perubahan Stok',
                            };

                            if ($movement->movement_type === 'IN') {
                                $qtySign = '+'.$movement->qty;
                            } elseif ($movement->movement_type === 'OUT') {
                                $qtySign = '-'.$movement->qty;
                            } else {
                                if ($movement->stock_after > $movement->stock_before) {
                                    $qtySign = '+'.$movement->qty;
                                } elseif ($movement->stock_after < $movement->stock_before) {
                                    $qtySign = '-'.$movement->qty;
                                } else {
                                    $qtySign = '0';
                                }
                            }

                            $price = $movement->getRawOriginal('unit_price');
                            $total = $movement->getRawOriginal('total_price');
                            $formatRupiah = fn ($value) => $value === null ? null : 'Rp'.preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', (string) $value);
                            $formattedPrice = $formatRupiah($price);
                            $formattedTotal = $formatRupiah($total);

                            $requestRef = null;
                            if ($movement->source_type === \App\Models\InvPesonaRequest::class && $movement->source_id !== null) {
                                $requestRef = $requestSources->get($movement->source_id);
                            }
                            $requestNumber = is_object($requestRef) ? $requestRef->request_number : (is_string($requestRef) ? $requestRef : null);
                            $requesterName = is_object($requestRef) ? $requestRef->user_name_snapshot : null;

                            $dateFormatted = $movement->created_at?->timezone(config('app.timezone'))->format('d/m/Y') ?? '—';
                            $timeFormatted = $movement->created_at?->timezone(config('app.timezone'))->format('H:i') ?? '';
                        @endphp

                        <article class="pesona-mov-card {{ $rowClass }}">
                            <div class="pesona-mov-card-top">
                                <div class="pesona-mov-card-head-left">
                                    <div class="pesona-mov-card-item">{{ $itemName }}</div>
                                    <div class="pesona-mov-card-time">{{ $dateFormatted }}@if($timeFormatted !== '') · {{ $timeFormatted }} WIB @endif</div>
                                </div>
                                <span class="pesona-mov-badge {{ $badgeClass }}" style="flex-shrink:0;">{{ $typeLabel }}</span>
                            </div>

                            <div class="pesona-mov-card-grid">
                                <div class="pesona-mov-card-cell">
                                    <div class="pesona-mov-card-cell-label">Perubahan Stok</div>
                                    <div class="pesona-mov-cell-primary {{ $badgeClass }}">{{ $qtySign }} {{ $unit }}</div>
                                    <div class="pesona-mov-cell-secondary">
                                        Awal <strong>{{ $movement->stock_before }}</strong> → Akhir <strong>{{ $movement->stock_after }} {{ $unit }}</strong>
                                    </div>
                                </div>
                                <div class="pesona-mov-card-cell">
                                    <div class="pesona-mov-card-cell-label">Harga Beli</div>
                                    @if($formattedTotal !== null || $formattedPrice !== null)
                                        <div class="pesona-mov-cell-primary">{{ $formattedTotal ?? '—' }}</div>
                                        <div class="pesona-mov-cell-secondary">@ {{ $formattedPrice ?? '—' }} / {{ $unit !== '' ? $unit : 'satuan' }}</div>
                                    @elseif($movement->movement_type === 'IN')
                                        <div class="pesona-mov-cell-primary" style="color:#64748B;">—</div>
                                        <div class="pesona-mov-cell-secondary">Harga tidak dicatat</div>
                                    @else
                                        <div class="pesona-mov-cell-primary" style="color:#64748B;">—</div>
                                        <div class="pesona-mov-cell-secondary">Bukan pembelian</div>
                                    @endif
                                </div>
                                <div class="pesona-mov-card-cell is-full">
                                    <div class="pesona-mov-card-cell-label">Petugas / Pengambil</div>
                                    <div class="pesona-mov-card-inline-row">
                                        <span class="pesona-mov-cell-primary">{{ $movement->createdBy?->name ?? 'Admin Gudang' }}</span>
                                        <span class="pesona-mov-cell-secondary" style="margin-top:0;">
                                            @if($requesterName)
                                                · Diambil: <strong>{{ $requesterName }}</strong>
                                            @else
                                                · Pencatat stok
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="pesona-mov-card-cell is-full">
                                    <div class="pesona-mov-card-cell-label">Asal Pencatatan</div>
                                    @if($requestNumber !== null)
                                        <a class="pesona-mov-origin-link" href="{{ route('inventory.pesona.admin.requests.show', $movement->source_id) }}">
                                            {{ $requestNumber }}
                                        </a>
                                        <div class="pesona-mov-cell-secondary">Pengambilan Barang · Nopol: <strong>{{ $requestRef->nopol_snapshot ?? 'Belum dicatat' }}</strong></div>
                                    @elseif($movement->movement_type === 'IN')
                                        <div class="pesona-mov-cell-primary">Input Barang Masuk</div>
                                        <div class="pesona-mov-cell-secondary">Penerimaan stok</div>
                                    @else
                                        <div class="pesona-mov-cell-primary">Edit Stok Manual</div>
                                        <div class="pesona-mov-cell-secondary">Penyesuaian stok</div>
                                    @endif
                                </div>
                            </div>

                            @if($movement->notes)
                                <div class="pesona-mov-card-note"><strong>Keterangan:</strong> {{ $movement->notes }}</div>
                            @endif
                        </article>
                    @endforeach
                </div>

                {{-- Desktop Table (>= 900px) --}}
                <div class="pesona-mov-table-wrap">
                    <table class="pesona-mov-table">
                        <thead>
                            <tr>
                                <th class="pesona-mov-col-time">Waktu</th>
                                <th class="pesona-mov-col-item">Nama Barang</th>
                                <th class="pesona-mov-col-activity">Aktivitas</th>
                                <th class="pesona-mov-col-stock">Perubahan Stok</th>
                                <th class="pesona-mov-col-price">Harga Beli</th>
                                <th class="pesona-mov-col-actor">Petugas</th>
                                <th class="pesona-mov-col-note">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($movements as $movement)
                                @php
                                    $unit = $movement->item?->unit_name ?? '';
                                    $itemName = $movement->item?->name ?? ('Barang tidak tersedia (ID '.$movement->inv_pesona_item_id.')');
                                    $rowClass = match ($movement->movement_type) {
                                        'IN' => 'row-in',
                                        'OUT' => 'row-out',
                                        default => 'row-adj',
                                    };
                                    $badgeClass = match ($movement->movement_type) {
                                        'IN' => 'is-in',
                                        'OUT' => 'is-out',
                                        default => 'is-adj',
                                    };
                                    $typeLabel = match ($movement->movement_type) {
                                        'IN' => 'Barang Masuk',
                                        'OUT' => 'Barang Keluar',
                                        'ADJUSTMENT' => 'Edit Manual',
                                        default => 'Perubahan Stok',
                                    };

                                    if ($movement->movement_type === 'IN') {
                                        $qtySign = '+'.$movement->qty;
                                    } elseif ($movement->movement_type === 'OUT') {
                                        $qtySign = '-'.$movement->qty;
                                    } else {
                                        if ($movement->stock_after > $movement->stock_before) {
                                            $qtySign = '+'.$movement->qty;
                                        } elseif ($movement->stock_after < $movement->stock_before) {
                                            $qtySign = '-'.$movement->qty;
                                        } else {
                                            $qtySign = '0';
                                        }
                                    }

                                    $price = $movement->getRawOriginal('unit_price');
                                    $total = $movement->getRawOriginal('total_price');
                                    $formatRupiah = fn ($value) => $value === null ? null : 'Rp'.preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', (string) $value);
                                    $formattedPrice = $formatRupiah($price);
                                    $formattedTotal = $formatRupiah($total);

                                    $requestRef = null;
                                    if ($movement->source_type === \App\Models\InvPesonaRequest::class && $movement->source_id !== null) {
                                        $requestRef = $requestSources->get($movement->source_id);
                                    }
                                    $requestNumber = is_object($requestRef) ? $requestRef->request_number : (is_string($requestRef) ? $requestRef : null);
                                    $requesterName = is_object($requestRef) ? $requestRef->user_name_snapshot : null;

                                    $dateFormatted = $movement->created_at?->timezone(config('app.timezone'))->format('d/m/Y') ?? '—';
                                    $timeFormatted = $movement->created_at?->timezone(config('app.timezone'))->format('H:i') ?? '';
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    {{-- 1. Waktu --}}
                                    <td class="pesona-mov-col-time">
                                        <div class="pesona-mov-cell-primary">{{ $dateFormatted }}</div>
                                        <div class="pesona-mov-cell-secondary">{{ $timeFormatted !== '' ? $timeFormatted.' WIB' : '—' }}</div>
                                    </td>

                                    {{-- 2. Nama Barang --}}
                                    <td class="pesona-mov-col-item">
                                        <div class="pesona-mov-cell-primary">{{ $itemName }}</div>
                                        <div class="pesona-mov-cell-secondary">Satuan: <strong>{{ $unit !== '' ? $unit : '—' }}</strong></div>
                                    </td>

                                    {{-- 3. Aktivitas --}}
                                    <td class="pesona-mov-col-activity">
                                        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                            <span class="pesona-mov-badge {{ $badgeClass }}">{{ $typeLabel }}</span>
                                            @if($requestNumber !== null)
                                                <span class="pesona-mov-nopol-tag">Nopol: {{ $requestRef->nopol_snapshot ?? 'Belum dicatat' }}</span>
                                            @endif
                                        </div>
                                        <div class="pesona-mov-cell-secondary pesona-mov-nowrap">
                                            @if($requestNumber !== null)
                                                <a class="pesona-mov-origin-link" href="{{ route('inventory.pesona.admin.requests.show', $movement->source_id) }}" style="white-space:nowrap;">{{ $requestNumber }}</a>
                                            @elseif($movement->movement_type === 'IN')
                                                Input Barang Masuk
                                            @elseif($movement->movement_type === 'ADJUSTMENT')
                                                Edit Stok Manual
                                            @else
                                                Referensi tidak tersedia
                                            @endif
                                        </div>
                                    </td>

                                    {{-- 4. Perubahan Stok --}}
                                    <td class="pesona-mov-col-stock">
                                        <div class="pesona-mov-cell-primary {{ $badgeClass }} pesona-mov-nowrap">
                                            {{ $qtySign }} {{ $unit }}
                                        </div>
                                        <div class="pesona-mov-cell-secondary pesona-mov-nowrap">
                                            Awal <strong>{{ $movement->stock_before }}</strong> → Akhir <strong>{{ $movement->stock_after }} {{ $unit }}</strong>
                                        </div>
                                    </td>

                                    {{-- 5. Harga Beli --}}
                                    <td class="pesona-mov-col-price">
                                        @if($formattedTotal !== null || $formattedPrice !== null)
                                            <div class="pesona-mov-cell-primary pesona-mov-nowrap">{{ $formattedTotal ?? '—' }}</div>
                                            <div class="pesona-mov-cell-secondary pesona-mov-nowrap">@ {{ $formattedPrice ?? '—' }} / {{ $unit !== '' ? $unit : 'satuan' }}</div>
                                        @elseif($movement->movement_type === 'IN')
                                            <div class="pesona-mov-cell-primary" style="color:#64748B;">—</div>
                                            <div class="pesona-mov-cell-secondary pesona-mov-nowrap">Harga tidak dicatat</div>
                                        @else
                                            <div class="pesona-mov-cell-primary" style="color:#64748B;">—</div>
                                            <div class="pesona-mov-cell-secondary pesona-mov-nowrap">Bukan pembelian</div>
                                        @endif
                                    </td>

                                    {{-- 6. Petugas --}}
                                    <td class="pesona-mov-col-actor">
                                        <div class="pesona-mov-cell-primary">{{ $movement->createdBy?->name ?? 'Admin Gudang' }}</div>
                                        @if($requesterName)
                                            <div class="pesona-mov-cell-secondary">Diambil: <strong>{{ $requesterName }}</strong></div>
                                        @else
                                            <div class="pesona-mov-cell-secondary">Pencatat stok</div>
                                        @endif
                                    </td>

                                    {{-- 7. Keterangan --}}
                                    <td class="pesona-mov-col-note">
                                        <div class="pesona-mov-note-text">{{ $movement->notes ?? '—' }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div style="margin-top: 14px;">
                <x-pagination :items="$movements" />
            </div>
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('pesona-mov-filter-form');
            const qInput = document.getElementById('pesona-mov-q');
            const itemHidden = document.getElementById('pesona-mov-item');
            const itemSearchInput = document.getElementById('pesona-mov-item-search');
            const itemClearBtn = document.getElementById('pesona-mov-item-clear');
            const itemDropdown = document.getElementById('pesona-mov-item-dropdown');
            const itemCombo = document.getElementById('pesona-item-combo');
            const typeSelect = document.getElementById('pesona-mov-type');
            const fromInput = document.getElementById('pesona-mov-from');
            const toInput = document.getElementById('pesona-mov-to');
            const resetBtn = document.getElementById('pesona-mov-reset');
            const pillsWrap = document.getElementById('pesona-mov-type-pills');
            const totalPill = document.getElementById('pesona-mov-total-pill');
            let resultsBox = document.getElementById('pesona-movements-results');

            if (!form || !resultsBox) return;

            let debounceTimer = null;
            let activeController = null;
            let itemSearchTimer = null;
            let itemSearchController = null;
            let selectedItemLabel = itemSearchInput ? itemSearchInput.value : '';
            const initialItems = @json($initialItemsPayload);

            function escapeHtml(str) {
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function syncPillsAndReset() {
                const currentType = typeSelect ? typeSelect.value : '';
                if (pillsWrap) {
                    pillsWrap.querySelectorAll('[data-type]').forEach(function (btn) {
                        const btnType = btn.getAttribute('data-type') || '';
                        btn.classList.remove('is-active-all', 'is-active-in', 'is-active-out', 'is-active-adj');
                        if (btnType === currentType) {
                            if (currentType === 'IN') btn.classList.add('is-active-in');
                            else if (currentType === 'OUT') btn.classList.add('is-active-out');
                            else if (currentType === 'ADJUSTMENT') btn.classList.add('is-active-adj');
                            else btn.classList.add('is-active-all');
                        }
                    });
                }

                if (itemClearBtn) {
                    const hasItem = (itemHidden && itemHidden.value !== '') || (itemSearchInput && itemSearchInput.value.trim() !== '');
                    itemClearBtn.style.display = hasItem ? '' : 'none';
                }

                if (resetBtn) {
                    const hasAny = (qInput && qInput.value.trim() !== '')
                        || (itemHidden && itemHidden.value !== '')
                        || currentType !== ''
                        || (fromInput && fromInput.value !== '')
                        || (toInput && toInput.value !== '');
                    resetBtn.style.display = hasAny ? '' : 'none';
                }
            }

            function buildUrl(customUrl) {
                if (customUrl) return customUrl;
                const params = new URLSearchParams();
                if (qInput && qInput.value.trim() !== '') params.set('q', qInput.value.trim());
                if (itemHidden && itemHidden.value !== '') params.set('item_id', itemHidden.value);
                if (typeSelect && typeSelect.value !== '') params.set('movement_type', typeSelect.value);
                if (fromInput && fromInput.value !== '') params.set('date_from', fromInput.value);
                if (toInput && toInput.value !== '') params.set('date_to', toInput.value);
                const qs = params.toString();
                return form.action + (qs ? '?' + qs : '');
            }

            function fetchResults(targetUrl, pushHistory) {
                const url = buildUrl(targetUrl);
                syncPillsAndReset();

                if (activeController) {
                    activeController.abort();
                }
                activeController = new AbortController();
                resultsBox.classList.add('is-loading');

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: activeController.signal
                })
                    .then(function (res) {
                        if (!res.ok) throw new Error('Network error');
                        return res.text();
                    })
                    .then(function (html) {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const nextResults = doc.getElementById('pesona-movements-results');
                        if (nextResults) {
                            resultsBox.replaceWith(nextResults);
                            resultsBox = nextResults;
                            const nextTotal = nextResults.getAttribute('data-total');
                            if (totalPill && nextTotal !== null) {
                                totalPill.textContent = nextTotal + ' catatan';
                            }
                        }
                        if (pushHistory !== false && window.history && window.history.replaceState) {
                            window.history.replaceState({}, '', url);
                        }
                    })
                    .catch(function (err) {
                        if (err.name !== 'AbortError') {
                            resultsBox.classList.remove('is-loading');
                        }
                    });
            }

            /* Live Database Item Search Combobox */
            function openItemDropdown() {
                if (!itemDropdown || !itemSearchInput) return;
                itemDropdown.classList.add('is-open');
                itemSearchInput.setAttribute('aria-expanded', 'true');
            }

            function closeItemDropdown() {
                if (!itemDropdown || !itemSearchInput) return;
                itemDropdown.classList.remove('is-open');
                itemSearchInput.setAttribute('aria-expanded', 'false');
            }

            function selectItemOption(id, label) {
                if (!itemHidden || !itemSearchInput) return;
                itemHidden.value = id;
                selectedItemLabel = label;
                itemSearchInput.value = label;
                closeItemDropdown();
                fetchResults();
            }

            function renderItemOptions(items, keyword) {
                if (!itemDropdown) return;
                const currentId = itemHidden ? String(itemHidden.value) : '';
                let html = '';

                if (keyword === '') {
                    html += '<button type="button" class="pesona-item-option ' + (currentId === '' ? 'is-selected' : '') + '" data-item-id="" data-item-label="">'
                        + '<div class="pesona-item-option-main">'
                        + '<div class="pesona-item-option-name">Semua Barang</div>'
                        + '<div class="pesona-item-option-sub">Tampilkan riwayat seluruh barang</div>'
                        + '</div>'
                        + '</button>';
                }

                if (!items || items.length === 0) {
                    html += '<div class="pesona-item-dropdown-status">Barang tidak ditemukan di database.</div>';
                } else {
                    items.forEach(function (it) {
                        const isSel = String(it.id) === currentId;
                        const label = it.name + (it.unit_name ? ' (' + it.unit_name + ')' : '');
                        const badgeClass = it.is_active ? '' : 'is-inactive';
                        const badgeText = it.is_active
                            ? ('Stok: ' + it.stock + ' ' + (it.unit_name || ''))
                            : 'Nonaktif';

                        html += '<button type="button" class="pesona-item-option ' + (isSel ? 'is-selected' : '') + '" data-item-id="' + escapeHtml(it.id) + '" data-item-label="' + escapeHtml(label) + '">'
                            + '<div class="pesona-item-option-main">'
                            + '<div class="pesona-item-option-name">' + escapeHtml(it.name) + '</div>'
                            + '<div class="pesona-item-option-sub">Satuan: ' + escapeHtml(it.unit_name || '—') + '</div>'
                            + '</div>'
                            + '<span class="pesona-item-option-badge ' + badgeClass + '">' + escapeHtml(badgeText) + '</span>'
                            + '</button>';
                    });
                }

                itemDropdown.innerHTML = html;
                openItemDropdown();
            }

            function fetchDatabaseItems(keyword) {
                if (!itemDropdown) return;
                if (itemSearchController) {
                    itemSearchController.abort();
                }

                if (keyword === '' && Array.isArray(initialItems) && initialItems.length > 0) {
                    renderItemOptions(initialItems, '');
                    return;
                }

                itemSearchController = new AbortController();
                itemDropdown.innerHTML = '<div class="pesona-item-dropdown-status">Mencari barang di database...</div>';
                openItemDropdown();

                const url = form.action + '?ajax_items=1&item_q=' + encodeURIComponent(keyword);
                fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: itemSearchController.signal
                })
                    .then(function (res) {
                        if (!res.ok) throw new Error('Network error');
                        return res.json();
                    })
                    .then(function (data) {
                        renderItemOptions(data.items || [], keyword);
                    })
                    .catch(function (err) {
                        if (err.name !== 'AbortError') {
                            const lower = keyword.toLowerCase();
                            const fallback = (initialItems || []).filter(function (it) {
                                return String(it.name || '').toLowerCase().includes(lower)
                                    || String(it.unit_name || '').toLowerCase().includes(lower);
                            });
                            renderItemOptions(fallback, keyword);
                        }
                    });
            }

            if (itemSearchInput) {
                itemSearchInput.addEventListener('focus', function () {
                    const kw = (itemHidden && itemHidden.value !== '' && itemSearchInput.value === selectedItemLabel)
                        ? ''
                        : itemSearchInput.value.trim();
                    fetchDatabaseItems(kw);
                });

                itemSearchInput.addEventListener('input', function () {
                    const kw = itemSearchInput.value.trim();
                    if (kw === '') {
                        if (itemHidden && itemHidden.value !== '') {
                            itemHidden.value = '';
                            selectedItemLabel = '';
                            fetchResults();
                        }
                    }
                    syncPillsAndReset();
                    clearTimeout(itemSearchTimer);
                    itemSearchTimer = setTimeout(function () {
                        fetchDatabaseItems(kw);
                    }, 200);
                });

                itemSearchInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        closeItemDropdown();
                        itemSearchInput.value = selectedItemLabel;
                    }
                });
            }

            if (itemDropdown) {
                itemDropdown.addEventListener('click', function (e) {
                    const opt = e.target.closest('[data-item-id]');
                    if (!opt) return;
                    selectItemOption(
                        opt.getAttribute('data-item-id') || '',
                        opt.getAttribute('data-item-label') || ''
                    );
                });
            }

            if (itemClearBtn) {
                itemClearBtn.addEventListener('click', function () {
                    selectItemOption('', '');
                });
            }

            document.addEventListener('click', function (e) {
                if (itemCombo && !itemCombo.contains(e.target)) {
                    closeItemDropdown();
                    if (itemSearchInput && itemHidden) {
                        if (itemHidden.value !== '') {
                            itemSearchInput.value = selectedItemLabel;
                        } else {
                            itemSearchInput.value = '';
                        }
                        syncPillsAndReset();
                    }
                }
            });

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                fetchResults();
            });

            if (qInput) {
                qInput.addEventListener('input', function () {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function () {
                        fetchResults();
                    }, 280);
                });
            }

            [typeSelect, fromInput, toInput].forEach(function (el) {
                if (!el) return;
                el.addEventListener('change', function () {
                    fetchResults();
                });
            });

            if (pillsWrap) {
                pillsWrap.addEventListener('click', function (e) {
                    const pill = e.target.closest('[data-type]');
                    if (!pill || !typeSelect) return;
                    typeSelect.value = pill.getAttribute('data-type') || '';
                    fetchResults();
                });
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (qInput) qInput.value = '';
                    if (itemHidden) itemHidden.value = '';
                    if (itemSearchInput) itemSearchInput.value = '';
                    selectedItemLabel = '';
                    if (typeSelect) typeSelect.value = '';
                    if (fromInput) fromInput.value = '';
                    if (toInput) toInput.value = '';
                    closeItemDropdown();
                    fetchResults(form.action);
                });
            }

            document.addEventListener('click', function (e) {
                const link = e.target.closest('#pesona-movements-results a[href]');
                if (!link) return;
                if (link.classList.contains('pesona-mov-origin-link')) return;
                const href = link.getAttribute('href');
                if (!href || href.startsWith('#')) return;
                e.preventDefault();
                fetchResults(href);
            });
        })();
    </script>
</x-inv-pesona-app>
