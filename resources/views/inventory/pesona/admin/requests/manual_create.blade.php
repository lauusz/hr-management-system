<x-inv-pesona-app title="Input Pengambilan Manual — Inventory Pesona">
    <style>
        .pesona-manual-page {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .pesona-manual-head {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .pesona-manual-head-top {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .pesona-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            height: 34px;
            padding: 0 11px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 9px;
            background: #FFFFFF;
            color: #475569;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color .14s ease, color .14s ease, border-color .14s ease;
        }
        .pesona-back-btn:hover {
            background: #F8FAFC;
            color: #0F172A;
            border-color: #CBD5E1;
        }

        /* Layout: Mobile 1 Col -> Desktop 2 Col */
        .pesona-manual-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
            align-items: start;
        }
        .pesona-manual-left {
            display: flex;
            flex-direction: column;
            gap: 16px;
            min-width: 0;
        }
        .pesona-manual-side {
            display: flex;
            flex-direction: column;
            gap: 14px;
            min-width: 0;
        }

        /* Panels */
        .pesona-manual-panel {
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
            overflow: hidden;
        }
        .pesona-manual-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            padding: 13px 16px;
            background: #F8FAFC;
            border-bottom: 1px solid var(--pesona-border, #E2E8F0);
        }
        .pesona-manual-panel-title {
            margin: 0;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #334155;
        }
        .pesona-manual-panel-sub {
            font-size: 11.5px;
            font-weight: 600;
            color: #64748B;
        }
        .pesona-manual-panel-body {
            padding: 14px 16px;
        }

        /* Search Input inside Panel */
        .pesona-manual-search-bar {
            padding: 12px 16px;
            border-bottom: 1px solid #F1F5F9;
            background: #FFFFFF;
        }
        .pesona-manual-search-wrap {
            position: relative;
            width: 100%;
        }
        .pesona-manual-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            pointer-events: none;
        }
        .pesona-manual-search-input {
            width: 100%;
            height: 38px;
            padding: 0 64px 0 36px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 9px;
            background: #F8FAFC;
            color: #0F172A;
            font: inherit;
            font-size: 13px;
            box-sizing: border-box;
            transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
        }
        .pesona-manual-search-input:focus {
            outline: none;
            background: #FFFFFF;
            border-color: var(--pesona-primary, #28355A);
            box-shadow: 0 0 0 3px rgba(40, 53, 90, 0.10);
        }
        .pesona-manual-search-actions {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .pesona-manual-clear-btn {
            display: none;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            padding: 0;
            border: 0;
            border-radius: 6px;
            background: #E2E8F0;
            color: #475569;
            cursor: pointer;
        }
        .pesona-manual-clear-btn.is-visible {
            display: inline-flex;
        }
        .pesona-manual-spinner {
            display: none;
            width: 15px;
            height: 15px;
            border: 2px solid #CBD5E1;
            border-top-color: var(--pesona-primary, #28355A);
            border-radius: 999px;
            animation: pesonaManualSpin .65s linear infinite;
        }
        .pesona-manual-spinner.is-loading {
            display: inline-block;
        }
        @keyframes pesonaManualSpin {
            to { transform: rotate(360deg); }
        }

        /* User & Item Picker Lists */
        .pesona-manual-scroll-list {
            max-height: 260px;
            overflow-y: auto;
            transition: opacity .15s ease;
        }
        .pesona-manual-scroll-list.is-items {
            max-height: 380px;
        }
        .pesona-manual-scroll-list.is-fetching {
            opacity: .55;
            pointer-events: none;
        }
        .manual-entry {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 11px 16px;
            border-bottom: 1px solid #F1F5F9;
            transition: background-color .12s ease;
        }
        .manual-entry:last-child {
            border-bottom: 0;
        }
        .manual-entry:hover {
            background: #FAFBFD;
        }
        .manual-entry.is-chosen {
            background: var(--pesona-primary-soft, #EEF2F8);
        }
        .manual-entry-info {
            flex: 1;
            min-width: 0;
            overflow-wrap: anywhere;
        }
        .manual-entry-title {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #0F172A;
            line-height: 1.35;
        }
        .manual-entry-sub {
            display: block;
            margin-top: 2px;
            font-size: 11.5px;
            color: #64748B;
        }
        .pesona-pick-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            height: 32px;
            padding: 0 12px;
            border-radius: 8px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            background: #FFFFFF;
            color: var(--pesona-primary, #28355A);
            font: inherit;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            cursor: pointer;
            flex-shrink: 0;
            transition: background-color .14s ease, color .14s ease, border-color .14s ease;
        }
        .pesona-pick-btn:hover:not(:disabled) {
            background: var(--pesona-primary, #28355A);
            border-color: var(--pesona-primary, #28355A);
            color: #FFFFFF;
        }
        .pesona-pick-btn.is-active {
            background: var(--pesona-primary, #28355A);
            border-color: var(--pesona-primary, #28355A);
            color: #FFFFFF;
        }
        .pesona-pick-btn:disabled {
            background: #F1F5F9;
            border-color: #E2E8F0;
            color: #94A3B8;
            cursor: not-allowed;
        }

        /* Summary Side Panel */
        .pesona-selected-user-box {
            display: flex;
            flex-direction: column;
            gap: 3px;
            padding: 11px 12px;
            border-radius: 10px;
            border: 1px solid #E2E8F0;
            background: #F8FAFC;
            margin-bottom: 12px;
        }
        .pesona-selected-user-box.has-user {
            border-color: rgba(40, 53, 90, 0.22);
            background: var(--pesona-primary-soft, #EEF2F8);
        }
        .pesona-selected-user-caption {
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #64748B;
        }
        .pesona-selected-user-val {
            font-size: 13px;
            font-weight: 700;
            color: #0F172A;
            overflow-wrap: anywhere;
        }

        /* Selected Rows inside Summary */
        .pesona-manual-rows-wrap {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin: 10px 0 14px;
            max-height: 320px;
            overflow-y: auto;
        }
        .pesona-manual-empty-rows {
            padding: 16px 12px;
            border: 1.5px dashed #CBD5E1;
            border-radius: 10px;
            background: #F8FAFC;
            color: #64748B;
            font-size: 12px;
            text-align: center;
            line-height: 1.45;
        }
        .manual-row {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 11px 12px;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            background: #F8FAFC;
        }
        .manual-row-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
        }
        .manual-row-title {
            font-size: 13px;
            font-weight: 700;
            color: #0F172A;
            overflow-wrap: anywhere;
        }
        .manual-row-meta {
            margin: 2px 0 0;
            font-size: 11.5px;
            color: #64748B;
        }
        .manual-row-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding-top: 6px;
            border-top: 1px solid #E2E8F0;
        }
        .manual-qty-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
        }
        .manual-qty {
            width: 78px;
            height: 32px;
            padding: 0 8px;
            border: 1px solid #CBD5E1;
            border-radius: 7px;
            background: #FFFFFF;
            color: #0F172A;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            box-sizing: border-box;
        }
        .manual-qty:focus {
            outline: none;
            border-color: var(--pesona-primary, #28355A);
            box-shadow: 0 0 0 2px rgba(40, 53, 90, 0.12);
        }
        .remove-item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 30px;
            padding: 0 10px;
            border: 1px solid #FECACA;
            border-radius: 7px;
            background: #FFFFFF;
            color: #DC2626;
            font: inherit;
            font-size: 11.5px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color .14s ease;
        }
        .remove-item:hover {
            background: #FEF2F2;
        }

        .manual-field {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin: 10px 0;
            font-size: 12.5px;
            font-weight: 700;
            color: #334155;
        }
        .manual-field textarea {
            width: 100%;
            min-height: 74px;
            padding: 8px 10px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 9px;
            background: #FFFFFF;
            color: #0F172A;
            font: inherit;
            font-size: 13px;
            font-weight: 400;
            resize: vertical;
            box-sizing: border-box;
        }
        .manual-field textarea:focus {
            outline: none;
            border-color: var(--pesona-primary, #28355A);
            box-shadow: 0 0 0 3px rgba(40, 53, 90, 0.10);
        }
        .manual-error {
            margin: 4px 0;
            font-size: 12px;
            font-weight: 600;
            color: #B91C1C;
        }
        .manual-actions {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
            margin-top: 12px;
        }
        .pesona-manual-submit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            min-height: 40px;
            padding: 0 16px;
            border: 0;
            border-radius: 10px;
            background: var(--pesona-primary, #28355A);
            color: #FFFFFF;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color .15s ease;
        }
        .pesona-manual-submit-btn:hover {
            background: var(--pesona-primary-hover, #1E2846);
        }
        .pesona-manual-cancel-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 38px;
            padding: 0 16px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 10px;
            background: #FFFFFF;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            box-sizing: border-box;
        }
        .pesona-manual-cancel-btn:hover {
            background: #F8FAFC;
            color: #0F172A;
        }

        @media (min-width: 900px) {
            .pesona-manual-layout {
                grid-template-columns: minmax(0, 1fr) 370px;
            }
            .pesona-manual-side {
                position: sticky;
                top: 16px;
            }
        }
    </style>

    <div class="pesona-manual-page">
        {{-- Header --}}
        <div class="pesona-manual-head">
            <div class="pesona-manual-head-top">
                <a class="pesona-back-btn" href="{{ route('inventory.pesona.admin.requests.index') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                    <span>Kembali</span>
                </a>
                <h1 class="pesona-title">Input Pengambilan Manual</h1>
            </div>
            <p class="pesona-subtitle">Pengajuan dibuat atas nama pemohon dan tetap memerlukan pemeriksaan serta finalisasi. Stok belum berkurang saat pengajuan dibuat.</p>
        </div>

        {{-- Fallback GET Search Form (sinkron dengan input live search) --}}
        <form id="manual-search" method="GET" action="{{ route('inventory.pesona.admin.requests.manual.create') }}" hidden>
            <input type="hidden" name="user_q" id="manual-search-user-q" value="{{ $userSearch }}">
            <input type="hidden" name="item_q" id="manual-search-item-q" value="{{ $itemSearch }}">
        </form>

        <div class="pesona-manual-layout">
            {{-- Kolom Kiri: Pilih Pemohon & Pilih Barang --}}
            <div class="pesona-manual-left">
                {{-- Panel 1: Pilih Pemohon --}}
                <section class="pesona-manual-panel">
                    <div class="pesona-manual-panel-head">
                        <h2 class="pesona-manual-panel-title">1. Pilih Pemohon</h2>
                        <span class="pesona-manual-panel-sub">Maks. 20 hasil per pencarian</span>
                    </div>
                    <div class="pesona-manual-search-bar">
                        <div class="pesona-manual-search-wrap">
                            <svg class="pesona-manual-search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                            <input
                                id="pesonaManualUserSearch"
                                class="pesona-manual-search-input"
                                type="search"
                                maxlength="150"
                                value="{{ $userSearch }}"
                                placeholder="Cari nama atau username pemohon..."
                                autocomplete="off"
                                aria-label="Cari pemohon"
                            >
                            <div class="pesona-manual-search-actions">
                                <span id="pesonaManualUserSpinner" class="pesona-manual-spinner" aria-hidden="true"></span>
                                <button id="pesonaManualUserClear" class="pesona-manual-clear-btn {{ $userSearch !== '' ? 'is-visible' : '' }}" type="button" aria-label="Bersihkan pencarian pemohon">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="pesonaManualUserResults" class="pesona-manual-scroll-list">
                        @forelse($users as $user)
                            @php($isSelectedUser = $selectedUser && (int) $selectedUser->id === (int) $user->id)
                            <div class="manual-entry {{ $isSelectedUser ? 'is-chosen' : '' }}" data-user-entry="{{ $user->id }}">
                                <div class="manual-entry-info">
                                    <strong class="manual-entry-title">{{ $user->name }}</strong>
                                    <span class="manual-entry-sub">{{ $user->username }} · {{ $user->pt?->name ?? '—' }}</span>
                                </div>
                                <button
                                    type="button"
                                    class="pesona-pick-btn choose-user {{ $isSelectedUser ? 'is-active' : '' }}"
                                    data-id="{{ $user->id }}"
                                    data-label="{{ $user->name }} · {{ $user->username }} · {{ $user->pt?->name ?? '—' }}"
                                >
                                    {{ $isSelectedUser ? 'Terpilih' : 'Pilih' }}
                                </button>
                            </div>
                        @empty
                            <div style="padding:20px 16px;text-align:center;color:#64748B;font-size:12.5px">
                                Tidak ada pemohon yang memenuhi syarat atau sesuai pencarian.
                            </div>
                        @endforelse
                    </div>
                </section>

                {{-- Panel 2: Pilih Barang --}}
                <section class="pesona-manual-panel">
                    <div class="pesona-manual-panel-head">
                        <h2 class="pesona-manual-panel-title">2. Pilih Barang Garasi</h2>
                        <span class="pesona-manual-panel-sub">Maks. 20 hasil per pencarian</span>
                    </div>
                    <div class="pesona-manual-search-bar">
                        <div class="pesona-manual-search-wrap">
                            <svg class="pesona-manual-search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                            <input
                                id="pesonaManualItemSearch"
                                class="pesona-manual-search-input"
                                type="search"
                                maxlength="150"
                                value="{{ $itemSearch }}"
                                placeholder="Cari nama barang persediaan..."
                                autocomplete="off"
                                aria-label="Cari barang"
                            >
                            <div class="pesona-manual-search-actions">
                                <span id="pesonaManualItemSpinner" class="pesona-manual-spinner" aria-hidden="true"></span>
                                <button id="pesonaManualItemClear" class="pesona-manual-clear-btn {{ $itemSearch !== '' ? 'is-visible' : '' }}" type="button" aria-label="Bersihkan pencarian barang">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="pesonaManualItemResults" class="pesona-manual-scroll-list is-items">
                        @forelse($items as $item)
                            @php($canPick = $item->stock_qty >= $item->min_request_qty && $item->stock_qty > 0)
                            <div class="manual-entry" data-item-entry="{{ $item->id }}">
                                <div class="manual-entry-info">
                                    <strong class="manual-entry-title">{{ $item->name }}</strong>
                                    <span class="manual-entry-sub">
                                        Stok {{ $item->stock_qty }} {{ $item->unit_name }} · Minimum {{ $item->min_request_qty }} {{ $item->unit_name }}
                                        @if($item->unit_size > 1)
                                            · 1 {{ $item->unit_name }} = {{ $item->unit_size }} {{ $item->content_unit_name }}
                                        @endif
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    class="pesona-pick-btn choose-item"
                                    data-id="{{ $item->id }}"
                                    data-name="{{ $item->name }}"
                                    data-unit="{{ $item->unit_name }}"
                                    data-stock="{{ $item->stock_qty }}"
                                    data-min="{{ $item->min_request_qty }}"
                                    data-unpickable="{{ $canPick ? '0' : '1' }}"
                                    @disabled(! $canPick)
                                >
                                    {{ $canPick ? '+ Tambah' : 'Stok Kurang' }}
                                </button>
                            </div>
                        @empty
                            <div style="padding:20px 16px;text-align:center;color:#64748B;font-size:12.5px">
                                Tidak ada barang aktif yang sesuai pencarian.
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            {{-- Kolom Kanan: Ringkasan & Form Pengajuan --}}
            <aside class="pesona-manual-side">
                <form id="manual-form" class="pesona-manual-panel" method="POST" action="{{ route('inventory.pesona.admin.requests.manual.store') }}">
                    @csrf
                    <input type="hidden" name="form_token" id="manual-form-token" value="{{ $token }}">
                    <input type="hidden" name="user_id" id="manual-user-id" value="{{ is_scalar($selectedUserId) ? $selectedUserId : '' }}">

                    <div class="pesona-manual-panel-head">
                        <h2 class="pesona-manual-panel-title">3. Pilihan Pengajuan</h2>
                        <span class="pesona-manual-panel-sub" id="pesonaManualCountBadge">{{ count($rows) }} jenis barang</span>
                    </div>

                    <div class="pesona-manual-panel-body">
                        <div id="pesonaManualUserBox" class="pesona-selected-user-box {{ $selectedUser ? 'has-user' : '' }}">
                            <span class="pesona-selected-user-caption">Pemohon Terpilih</span>
                            <strong class="pesona-selected-user-val" id="manual-user-label">
                                {{ $selectedUser ? $selectedUser->name.' · '.$selectedUser->username.' · '.($selectedUser->pt?->name ?? '—') : ($selectedUserId ? 'Pemohon tidak tersedia atau tidak memenuhi syarat.' : 'Belum dipilih.') }}
                            </strong>
                        </div>
                        @error('user_id')<p class="manual-error">{{ $message }}</p>@enderror
                        @error('form_token')<p class="manual-error">{{ $message }}</p>@enderror

                        <label class="manual-field">
                            <span>Nopol Armada <span aria-hidden="true">*</span></span>
                            <select name="inv_pesona_vehicle_id" required @error('inv_pesona_vehicle_id') aria-invalid="true" @enderror>
                                <option value="">Pilih nopol armada</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" @selected((string) old('inv_pesona_vehicle_id', request()->query('inv_pesona_vehicle_id')) === (string) $vehicle->id)>{{ $vehicle->nopol }}</option>
                                @endforeach
                            </select>
                        </label>
                        @error('inv_pesona_vehicle_id')<p class="manual-error">{{ $message }}</p>@enderror
                        @if($vehicles->isEmpty())<p role="status">Belum ada nopol aktif. Hubungi admin Inventory Pesona.</p>@endif

                        <div style="display:flex;align-items:center;justify-content:space-between;font-size:11.5px;color:#64748B;font-weight:600">
                            <span>Daftar Barang</span>
                            <span>Maksimal 100 jenis barang</span>
                        </div>
                        @error('items')<p class="manual-error">{{ $message }}</p>@enderror

                        <div id="pesonaManualEmptyRows" class="pesona-manual-empty-rows" @if(count($rows) > 0) hidden @endif>
                            Belum ada barang yang ditambahkan. Klik tombol <strong>+ Tambah</strong> pada daftar barang.
                        </div>

                        <div id="manual-rows" class="pesona-manual-rows-wrap">
                            @foreach($rows as $index => $row)
                                @php($item = $selectedItems->get($row['item_id']))
                                <div class="manual-row" data-id="{{ $row['item_id'] }}">
                                    <div class="manual-row-top">
                                        <div>
                                            <strong class="manual-row-title">{{ $item?->name ?? 'Barang tidak tersedia' }}</strong>
                                            @if($item)
                                                <p class="manual-row-meta">
                                                    Stok {{ $item->stock_qty }} {{ $item->unit_name }} · Minimum {{ $item->min_request_qty }} {{ $item->unit_name }}
                                                    @if(! $item->is_active || $item->deleted_at !== null) · Tidak tersedia @endif
                                                </p>
                                            @endif
                                        </div>
                                        <button class="remove-item" type="button">Hapus</button>
                                    </div>
                                    <div class="manual-row-controls">
                                        <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $row['item_id'] }}">
                                        <label class="manual-qty-label">
                                            <span>Jumlah ({{ $item?->unit_name ?? 'unit' }})</span>
                                            <input class="manual-qty" type="number" min="1" step="1" name="items[{{ $index }}][qty]" value="{{ $row['qty'] }}" required>
                                        </label>
                                    </div>
                                    @error('items.'.$index)<p class="manual-error">{{ $message }}</p>@enderror
                                    @error('items.'.$index.'.item_id')<p class="manual-error">{{ $message }}</p>@enderror
                                    @error('items.'.$index.'.qty')<p class="manual-error">{{ $message }}</p>@enderror
                                </div>
                            @endforeach
                        </div>

                        <label class="manual-field">
                            <span>Catatan Pengajuan <span style="font-weight:400;color:#94A3B8">(Opsional)</span></span>
                            <textarea name="notes" maxlength="1000" placeholder="Keterangan pengambilan manual...">{{ is_string(old('notes')) ? old('notes') : '' }}</textarea>
                        </label>
                        @error('notes')<p class="manual-error">{{ $message }}</p>@enderror

                        <div class="manual-actions">
                            <button class="pesona-manual-submit-btn" type="submit" aria-haspopup="dialog" aria-controls="confirm-pesona-manual" @disabled($vehicles->isEmpty())>
                                Buat Pengajuan
                            </button>
                            <a class="pesona-manual-cancel-btn manual-link" href="{{ route('inventory.pesona.admin.requests.index') }}">Batal</a>
                        </div>
                    </div>
                </form>
            </aside>
        </div>
    </div>

    <x-modal id="confirm-pesona-manual" title="Buat Pengajuan Manual?" type="form" variant="warning">
        <p>Pengajuan dibuat atas nama pemohon dan tetap memerlukan pemeriksaan serta finalisasi.</p>
        <div style="display:flex;gap:8px;margin-top:14px">
            <button class="pesona-button" type="button" data-modal-close>Batal</button>
            <button class="pesona-button pesona-button-primary" type="button" id="manual-confirm">Ya, Buat Pengajuan</button>
        </div>
    </x-modal>

    <script>
        (() => {
            const form = document.getElementById('manual-form');
            const searchForm = document.getElementById('manual-search');
            const rows = document.getElementById('manual-rows');
            const emptyRows = document.getElementById('pesonaManualEmptyRows');
            const countBadge = document.getElementById('pesonaManualCountBadge');
            const userId = document.getElementById('manual-user-id');
            const userLabel = document.getElementById('manual-user-label');
            const userBox = document.getElementById('pesonaManualUserBox');
            const formTokenInput = document.getElementById('manual-form-token');

            const userSearchInput = document.getElementById('pesonaManualUserSearch');
            const userClearBtn = document.getElementById('pesonaManualUserClear');
            const userSpinner = document.getElementById('pesonaManualUserSpinner');
            const userResults = document.getElementById('pesonaManualUserResults');

            const itemSearchInput = document.getElementById('pesonaManualItemSearch');
            const itemClearBtn = document.getElementById('pesonaManualItemClear');
            const itemSpinner = document.getElementById('pesonaManualItemSpinner');
            const itemResults = document.getElementById('pesonaManualItemResults');

            const notes = form.querySelector('textarea[name="notes"]');
            const notesKey = 'inv_pesona_manual_search_notes_{{ auth()->id() }}';

            try {
                const savedNotes = sessionStorage.getItem(notesKey);
                if (savedNotes !== null) {
                    if (!notes.value) notes.value = savedNotes;
                    sessionStorage.removeItem(notesKey);
                }
            } catch (_) {}

            let next = {{ $rows ? max(array_map('intval', array_keys($rows))) + 1 : 0 }};

            function syncSelectionStates() {
                const currentUserId = String(userId.value || '');
                userResults?.querySelectorAll('.choose-user').forEach((btn) => {
                    const isMatch = String(btn.dataset.id) === currentUserId && currentUserId !== '';
                    btn.classList.toggle('is-active', isMatch);
                    btn.textContent = isMatch ? 'Terpilih' : 'Pilih';
                    btn.closest('.manual-entry')?.classList.toggle('is-chosen', isMatch);
                });

                const pickedIds = new Set(
                    Array.from(rows.querySelectorAll('.manual-row')).map((r) => String(r.dataset.id))
                );
                itemResults?.querySelectorAll('.choose-item').forEach((btn) => {
                    const unpickable = btn.dataset.unpickable === '1';
                    const isPicked = pickedIds.has(String(btn.dataset.id));
                    btn.closest('.manual-entry')?.classList.toggle('is-chosen', isPicked);
                    if (unpickable) {
                        btn.disabled = true;
                        btn.textContent = 'Stok Kurang';
                    } else if (isPicked) {
                        btn.disabled = true;
                        btn.textContent = 'Sudah Dipilih';
                    } else {
                        btn.disabled = false;
                        btn.textContent = '+ Tambah';
                    }
                });

                const rowCount = rows.querySelectorAll('.manual-row').length;
                if (emptyRows) emptyRows.hidden = rowCount > 0;
                if (countBadge) countBadge.textContent = rowCount + ' jenis barang';
            }

            userResults?.addEventListener('click', (event) => {
                const button = event.target.closest('.choose-user');
                if (!button) return;
                userId.value = button.dataset.id;
                userLabel.textContent = button.dataset.label;
                userBox?.classList.add('has-user');
                syncSelectionStates();
            });

            itemResults?.addEventListener('click', (event) => {
                const button = event.target.closest('.choose-item');
                if (!button || button.disabled) return;
                if (Array.from(rows.querySelectorAll('.manual-row')).some((row) => row.dataset.id === button.dataset.id)) {
                    return;
                }
                if (rows.querySelectorAll('.manual-row').length >= 100) {
                    alert('Maksimal 100 jenis barang.');
                    return;
                }

                const row = document.createElement('div');
                row.className = 'manual-row';
                row.dataset.id = button.dataset.id;

                const top = document.createElement('div');
                top.className = 'manual-row-top';

                const textWrap = document.createElement('div');
                const name = document.createElement('strong');
                name.className = 'manual-row-title';
                name.textContent = button.dataset.name;

                const info = document.createElement('p');
                info.className = 'manual-row-meta';
                info.textContent = `Stok ${button.dataset.stock} ${button.dataset.unit} · Minimum ${button.dataset.min} ${button.dataset.unit}`;

                textWrap.append(name, info);

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'remove-item';
                remove.textContent = 'Hapus';

                top.append(textWrap, remove);

                const controls = document.createElement('div');
                controls.className = 'manual-row-controls';

                const id = document.createElement('input');
                id.type = 'hidden';
                id.name = `items[${next}][item_id]`;
                id.value = button.dataset.id;

                const label = document.createElement('label');
                label.className = 'manual-qty-label';

                const labelSpan = document.createElement('span');
                labelSpan.textContent = `Jumlah (${button.dataset.unit})`;

                const qty = document.createElement('input');
                qty.className = 'manual-qty';
                qty.type = 'number';
                qty.min = '1';
                qty.step = '1';
                qty.required = true;
                qty.name = `items[${next++}][qty]`;
                qty.value = button.dataset.min;

                label.append(labelSpan, qty);
                controls.append(id, label);

                row.append(top, controls);
                rows.append(row);
                syncSelectionStates();
            });

            rows.addEventListener('click', (event) => {
                const button = event.target.closest('.remove-item');
                if (!button) return;
                button.closest('.manual-row')?.remove();
                syncSelectionStates();
            });

            // AJAX Live Search for Pemohon & Barang
            let searchTimer = null;
            let activeSearchController = null;

            function performLiveSearch(source) {
                const uQuery = (userSearchInput?.value || '').trim();
                const iQuery = (itemSearchInput?.value || '').trim();

                userClearBtn?.classList.toggle('is-visible', uQuery.length > 0);
                itemClearBtn?.classList.toggle('is-visible', iQuery.length > 0);

                if (activeSearchController) {
                    activeSearchController.abort();
                }
                activeSearchController = new AbortController();

                if (source === 'user') {
                    userSpinner?.classList.add('is-loading');
                    userResults?.classList.add('is-fetching');
                } else {
                    itemSpinner?.classList.add('is-loading');
                    itemResults?.classList.add('is-fetching');
                }

                const url = new URL(searchForm.action, window.location.origin);
                if (uQuery !== '') url.searchParams.set('user_q', uQuery);
                if (iQuery !== '') url.searchParams.set('item_q', iQuery);

                fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    signal: activeSearchController.signal
                })
                    .then((res) => {
                        if (!res.ok) throw new Error('Search error');
                        return res.text();
                    })
                    .then((html) => {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const nextUsers = doc.getElementById('pesonaManualUserResults');
                        const nextItems = doc.getElementById('pesonaManualItemResults');
                        const nextToken = doc.getElementById('manual-form-token');

                        if (nextUsers && userResults) {
                            userResults.innerHTML = nextUsers.innerHTML;
                        }
                        if (nextItems && itemResults) {
                            itemResults.innerHTML = nextItems.innerHTML;
                        }
                        if (nextToken && formTokenInput && nextToken.value) {
                            formTokenInput.value = nextToken.value;
                        }

                        syncSelectionStates();
                        window.history.replaceState(null, '', url.toString());
                    })
                    .catch((err) => {
                        if (err && err.name === 'AbortError') return;
                    })
                    .finally(() => {
                        userSpinner?.classList.remove('is-loading');
                        itemSpinner?.classList.remove('is-loading');
                        userResults?.classList.remove('is-fetching');
                        itemResults?.classList.remove('is-fetching');
                    });
            }

            userSearchInput?.addEventListener('input', () => {
                userClearBtn?.classList.toggle('is-visible', (userSearchInput.value || '').trim().length > 0);
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => performLiveSearch('user'), 280);
            });

            itemSearchInput?.addEventListener('input', () => {
                itemClearBtn?.classList.toggle('is-visible', (itemSearchInput.value || '').trim().length > 0);
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => performLiveSearch('item'), 280);
            });

            userClearBtn?.addEventListener('click', () => {
                if (!userSearchInput) return;
                userSearchInput.value = '';
                userSearchInput.focus();
                clearTimeout(searchTimer);
                performLiveSearch('user');
            });

            itemClearBtn?.addEventListener('click', () => {
                if (!itemSearchInput) return;
                itemSearchInput.value = '';
                itemSearchInput.focus();
                clearTimeout(searchTimer);
                performLiveSearch('item');
            });

            // Confirmation Modal
            const modal = document.getElementById('confirm-pesona-manual');
            const confirm = document.getElementById('manual-confirm');
            const trigger = form.querySelector('button[type="submit"]');
            let open = false, confirmed = false;

            function close() {
                if (!open) return;
                open = false;
                modal.style.display = 'none';
                document.body.style.overflow = '';
                trigger.focus();
            }

            form.addEventListener('submit', (event) => {
                if (confirmed) return;
                event.preventDefault();
                open = true;
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                modal.querySelector('[data-modal-close]').focus();
            });

            confirm.addEventListener('click', () => {
                confirmed = true;
                form.requestSubmit();
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal || event.target.closest('[data-modal-close]')) close();
            });

            document.addEventListener('keydown', (event) => {
                if (!open) return;
                if (event.key === 'Escape') { close(); return; }
                if (event.key !== 'Tab') return;
                const buttons = Array.from(modal.querySelectorAll('button:not([disabled])')).filter((button) => button.getClientRects().length);
                const first = buttons[0], last = buttons[buttons.length - 1];
                if (event.shiftKey && (document.activeElement === first || !modal.contains(document.activeElement))) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && (document.activeElement === last || !modal.contains(document.activeElement))) {
                    event.preventDefault();
                    first.focus();
                }
            });

            syncSelectionStates();
        })();
    </script>
</x-inv-pesona-app>
