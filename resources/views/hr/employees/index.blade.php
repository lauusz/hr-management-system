<x-app title="Daftar Karyawan">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Daftar Karyawan</h1>
                <p class="section-subtitle">{{ $totalEmployees }} karyawan terdaftar</p>
            </div>
        </div>
    </x-slot>

    @php
        $activeFilterCount = collect([
            $ptId ?? null,
            $positionId ?? null,
            $kategori ?? null,
            ($nearExpiry ?? false) ? 1 : null,
            ($noLeaveBalance ?? false) ? 1 : null,
            ($noShift ?? false) ? 1 : null,
        ])->filter()->count();
        $hasAnyFilter = $activeFilterCount > 0 || filled($search ?? null);
    @endphp

    @if(session('success'))
    <div class="emp-alert emp-alert--success">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- TOOLBAR: pencarian, filter, tambah --}}
    <div class="emp-toolbar">
        <form method="GET" action="{{ route('hr.employees.index') }}" id="filterForm">
            <div class="emp-toolbar-row">
                <div class="emp-search-input-wrap">
                    <svg class="emp-search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Cari nama, username, email, atau telepon..." autocomplete="off" class="emp-search-input" aria-label="Cari karyawan" data-employee-live-search>
                </div>

                <button type="button" class="emp-btn-toggle-filter {{ $activeFilterCount ? 'active' : '' }}" onclick="toggleFilterPanel()" aria-controls="filterPanel">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filter
                    @if($activeFilterCount)
                    <span class="emp-filter-badge">{{ $activeFilterCount }}</span>
                    @endif
                </button>

                @if($hasAnyFilter)
                <a href="{{ route('hr.employees.index') }}" class="emp-btn-reset">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reset
                </a>
                @endif

                <a href="{{ route('hr.employees.create') }}" class="emp-btn-primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Karyawan
                </a>
            </div>

            {{-- Filter: dropdown langsung diterapkan saat berubah --}}
            <div class="emp-filter-panel {{ $activeFilterCount ? 'is-open' : '' }}" id="filterPanel">
                <div class="emp-select-wrap">
                    <select name="pt_id" class="emp-select" aria-label="Filter PT" onchange="this.form.submit()">
                        <option value="">Semua PT</option>
                        @foreach($ptOptions as $pt)
                        <option value="{{ $pt->id }}" @selected(($ptId ?? '') == $pt->id)>{{ $pt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="emp-select-wrap">
                    <select name="position_id" class="emp-select" aria-label="Filter jabatan" onchange="this.form.submit()">
                        <option value="">Semua Jabatan</option>
                        @foreach($positionOptions as $pos)
                        <option value="{{ $pos->id }}" @selected(($positionId ?? '') == $pos->id)>{{ $pos->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="emp-select-wrap">
                    <select name="kategori" class="emp-select" aria-label="Filter kategori" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <option value="TETAP" @selected(($kategori ?? '') == 'TETAP')>Karyawan Tetap</option>
                        <option value="KONTRAK" @selected(($kategori ?? '') == 'KONTRAK')>Kontrak</option>
                        <option value="MAGANG" @selected(($kategori ?? '') == 'MAGANG')>Magang</option>
                    </select>
                </div>

                <div class="emp-filter-toggles">
                    <label class="emp-toggle-chip {{ ($nearExpiry ?? false) ? 'active' : '' }}">
                        <input type="checkbox" name="near_expiry" value="1" onchange="this.form.submit()" @checked($nearExpiry ?? false) hidden>
                        Kontrak Mau Habis
                    </label>
                    <label class="emp-toggle-chip {{ ($noLeaveBalance ?? false) ? 'active' : '' }}">
                        <input type="checkbox" name="no_leave_balance" value="1" onchange="this.form.submit()" @checked($noLeaveBalance ?? false) hidden>
                        Belum Dapat Cuti
                    </label>
                    <label class="emp-toggle-chip {{ ($noShift ?? false) ? 'active' : '' }}">
                        <input type="checkbox" name="no_shift" value="1" onchange="this.form.submit()" @checked($noShift ?? false) hidden>
                        Belum Ada Shift
                    </label>
                </div>
            </div>
        </form>
    </div>

    <div data-employee-results aria-live="polite" aria-busy="false">
        <div class="emp-results-bar" data-employee-results-bar>
            @if($items->total())
            <span>Menampilkan <strong>{{ $items->firstItem() }}–{{ $items->lastItem() }}</strong> dari <strong>{{ $items->total() }}</strong> karyawan</span>
            @else
            <span>Tidak ada hasil</span>
            @endif
        </div>

        <div class="emp-table-card">
            <div class="emp-table-scroll">
                <table class="emp-table">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Jabatan / Divisi</th>
                            <th>PT</th>
                            <th>Masa Kerja</th>
                            <th>Kategori</th>
                            <th class="emp-th--num">Sisa Cuti</th>
                            <th><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody data-employee-list>
                    @forelse($items as $emp)
                        @include('hr.employees.partials.employee-row', ['emp' => $emp])
                    @empty
                        <tr class="emp-row-empty">
                            <td colspan="7">
                                <p class="emp-empty-title">Tidak ada karyawan yang ditemukan</p>
                                <p class="emp-empty-desc">Coba ubah kata kunci pencarian atau filter yang digunakan</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="emp-pagination" data-employee-pagination>
            <x-pagination :items="$items" />
        </div>
    </div>

    <div class="emp-leave-modal" id="leaveBalanceHistoryModal" hidden>
        <div class="emp-leave-modal__backdrop" data-leave-history-close></div>
        <section class="emp-leave-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="leaveBalanceHistoryTitle">
            <div class="emp-leave-modal__header">
                <div>
                    <p class="emp-leave-modal__eyebrow">Riwayat cuti</p>
                    <h2 id="leaveBalanceHistoryTitle">Memuat riwayat...</h2>
                </div>
                <button type="button" class="emp-leave-modal__close" data-leave-history-close aria-label="Tutup riwayat cuti">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-width="2" d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>
            <div class="emp-leave-modal__body" data-leave-history-body></div>
        </section>
    </div>

    <style>
        /* ========== HEADER (x-slot) ========== */
        .section-header-inline { display: flex; align-items: center; gap: 10px; }
        .section-icon {
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .section-icon svg { width: 16px; height: 16px; }
        .section-title {
            margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-primary);
            letter-spacing: -0.01em; line-height: 1.25;
        }
        .section-subtitle { margin: 0; font-size: 0.8125rem; color: var(--text-muted); font-weight: 500; line-height: 1.35; }
        .icon-navy { background: rgba(10, 61, 98, 0.08); color: var(--primary-dark); }

        .sr-only {
            position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
            overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;
        }

        /* ========== ALERT ========== */
        .emp-alert {
            display: flex; align-items: center; gap: 10px; padding: 10px 14px;
            border-radius: 10px; margin-bottom: 12px; font-size: 13px; font-weight: 500;
        }
        .emp-alert--success { background: rgba(34, 197, 94, 0.08); border: 1px solid rgba(34, 197, 94, 0.25); color: #16a34a; }

        /* ========== TOOLBAR ========== */
        .emp-toolbar {
            background: var(--white); border: 1px solid var(--border); border-radius: 12px;
            padding: 10px; margin-bottom: 10px; box-sizing: border-box;
        }
        .emp-toolbar-row { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .emp-search-input-wrap { position: relative; flex: 1 1 100%; min-width: 0; }
        .emp-search-icon {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            color: var(--text-light); pointer-events: none;
        }
        .emp-search-input {
            width: 100%; box-sizing: border-box; height: 38px; padding: 0 12px 0 36px;
            border: 1.5px solid var(--border); border-radius: 10px; font-size: 13px;
            color: var(--text-primary); background: var(--white); outline: none; font-family: inherit;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .emp-search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.1); }
        .emp-search-input::placeholder { color: var(--text-light); }

        .emp-btn-toggle-filter,
        .emp-btn-reset,
        .emp-btn-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            height: 38px; padding: 0 14px; box-sizing: border-box; border-radius: 10px;
            font-size: 13px; font-weight: 600; white-space: nowrap; text-decoration: none;
            font-family: inherit; cursor: pointer; flex: 1 1 auto; transition: all .15s ease;
        }
        .emp-btn-toggle-filter { background: var(--white); color: var(--text-muted); border: 1.5px solid var(--border); }
        .emp-btn-toggle-filter:hover { border-color: var(--primary); color: var(--primary); }
        .emp-btn-toggle-filter.active { background: var(--primary); border-color: var(--primary); color: #fff; }
        .emp-filter-badge {
            background: var(--error); color: #fff; font-size: 10px; font-weight: 700;
            padding: 1px 6px; border-radius: 10px; min-width: 18px; text-align: center;
        }
        .emp-btn-toggle-filter.active .emp-filter-badge { background: rgba(255,255,255,0.3); }
        .emp-btn-reset { background: var(--white); color: var(--text-muted); border: 1.5px solid var(--border); }
        .emp-btn-reset:hover { background: var(--danger-light); border-color: #fecaca; color: var(--error); }
        .emp-btn-primary {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff; border: none;
            box-shadow: 0 2px 8px rgba(10, 61, 98, 0.2);
        }
        .emp-btn-primary:hover { box-shadow: 0 4px 14px rgba(10, 61, 98, 0.3); }

        /* ========== FILTER PANEL ========== */
        .emp-filter-panel {
            display: none; grid-template-columns: 1fr; gap: 8px;
            margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--border-light);
        }
        .emp-filter-panel.is-open { display: grid; }
        .emp-select {
            width: 100%; box-sizing: border-box; height: 36px; padding: 0 10px;
            border: 1.5px solid var(--border); border-radius: 10px; font-size: 13px;
            color: var(--text-primary); background: var(--white); cursor: pointer; outline: none; font-family: inherit;
        }
        .emp-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.1); }
        .emp-filter-toggles { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
        .emp-toggle-chip {
            display: inline-flex; align-items: center; height: 32px; padding: 0 12px; box-sizing: border-box;
            background: var(--white); border: 1.5px solid var(--border); border-radius: 9999px;
            font-size: 12px; font-weight: 500; color: var(--text-muted); cursor: pointer; user-select: none;
            transition: all .15s ease;
        }
        .emp-toggle-chip:hover { border-color: var(--primary); color: var(--primary); }
        .emp-toggle-chip.active { background: var(--primary); border-color: var(--primary); color: #fff; }

        /* ========== RESULTS ========== */
        .emp-results-bar { margin: 4px 2px 8px; font-size: 12px; color: var(--text-muted); }
        .emp-results-bar strong { color: var(--text-secondary); font-weight: 700; }
        [data-employee-results] { transition: opacity .15s ease; }
        [data-employee-results].is-loading { opacity: .55; }

        /* ========== TABLE (mobile-first: baris sebagai kartu) ========== */
        .emp-table-card { background: transparent; }
        .emp-table { width: 100%; border-collapse: collapse; }
        .emp-table thead { display: none; }
        .emp-table tbody { display: flex; flex-direction: column; gap: 8px; }
        .emp-row {
            display: grid; grid-template-columns: 1fr 1fr; gap: 8px 12px;
            background: var(--white); border: 1px solid var(--border); border-radius: 12px;
            padding: 12px; box-sizing: border-box;
        }
        .emp-row.is-inactive { background: var(--gray-100); }
        .emp-td { display: block; min-width: 0; }
        .emp-td[data-label]::before {
            content: attr(data-label); display: block; font-size: 10px; font-weight: 600;
            text-transform: uppercase; letter-spacing: .04em; color: var(--text-light); margin-bottom: 2px;
        }
        .emp-td--name { grid-column: 1 / -1; }
        .emp-td--action { grid-column: 1 / -1; }

        .emp-name-line { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .emp-name {
            font-size: 14px; font-weight: 700; color: var(--text-primary); text-decoration: none; line-height: 1.3;
        }
        .emp-name:hover { color: var(--primary); text-decoration: underline; }
        .emp-main-text { font-size: 13px; font-weight: 500; color: var(--text-secondary); line-height: 1.35; overflow-wrap: anywhere; }
        .emp-sub { font-size: 11.5px; color: var(--text-muted); line-height: 1.35; margin-top: 1px; overflow-wrap: anywhere; }

        .emp-badge {
            display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 9999px;
            font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; flex-shrink: 0;
        }
        .emp-badge--success { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .emp-badge--error { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }

        .emp-contract.is-near { color: #a16207; font-weight: 600; }
        .emp-contract.is-expired { color: #b91c1c; font-weight: 600; }

        .emp-leave {
            display: inline-block; min-width: 32px; padding: 2px 8px; box-sizing: border-box; text-align: center;
            border-radius: 6px; font-size: 13px; font-weight: 700;
            background: rgba(59, 130, 246, 0.08); color: var(--info);
        }
        .emp-leave.is-zero { background: var(--gray-50); color: var(--text-light); }
        .emp-leave--button { border: 0; font-family: inherit; cursor: pointer; }
        .emp-leave--button:hover { background: rgba(20, 93, 160, 0.16); }
        .emp-leave--button:focus-visible { outline: 3px solid rgba(20, 93, 160, 0.3); outline-offset: 2px; }

        .emp-leave-modal { position: fixed; z-index: 1100; inset: 0; display: grid; place-items: center; padding: 16px; }
        .emp-leave-modal[hidden] { display: none; }
        .emp-leave-modal__backdrop { position: absolute; inset: 0; background: rgba(15, 23, 42, 0.58); backdrop-filter: brightness(.72); }
        .emp-leave-modal__dialog { position: relative; width: min(100%, 620px); max-height: min(760px, calc(100vh - 32px)); overflow: hidden; border-radius: 16px; background: var(--white); box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25); }
        .emp-leave-modal__header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 18px 20px; border-bottom: 1px solid var(--border-light); }
        .emp-leave-modal__eyebrow { margin: 0 0 3px; color: var(--text-muted); font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
        .emp-leave-modal__header h2 { margin: 0; color: var(--text-primary); font-size: 18px; line-height: 1.3; }
        .emp-leave-modal__close { display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; flex: 0 0 auto; padding: 0; border: 1px solid var(--border); border-radius: 8px; background: var(--white); color: var(--text-muted); cursor: pointer; }
        .emp-leave-modal__close:hover { color: var(--error); border-color: #fecaca; background: var(--danger-light); }
        .emp-leave-modal__body { max-height: calc(min(760px, 100vh - 32px) - 85px); overflow-y: auto; padding: 16px 20px 20px; }
        .emp-leave-summary { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; padding: 12px; border-radius: 10px; background: var(--gray-50); }
        .emp-leave-summary span { color: var(--text-muted); font-size: 12px; }
        .emp-leave-summary strong { color: var(--primary); font-size: 17px; }
        .emp-ledger-list { display: grid; gap: 10px; }
        .emp-ledger-row { border: 1px solid var(--border-light); border-radius: 10px; padding: 12px; }
        .emp-ledger-row__top, .emp-ledger-row__bottom { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; }
        .emp-ledger-row__title { color: var(--text-primary); font-size: 13px; font-weight: 700; }
        .emp-ledger-row__effect { flex: 0 0 auto; font-size: 13px; font-weight: 700; }
        .emp-ledger-row__effect.is-minus { color: var(--error); }.emp-ledger-row__effect.is-plus { color: #15803d; }
        .emp-ledger-row__meta, .emp-ledger-row__note { margin-top: 6px; color: var(--text-muted); font-size: 12px; line-height: 1.45; }
        .emp-ledger-row__reference { margin-top: 6px; color: var(--primary); font-size: 12px; font-weight: 600; }
        .emp-leave-empty, .emp-leave-error { margin: 0; padding: 20px 4px; color: var(--text-muted); font-size: 13px; text-align: center; }
        .emp-leave-error { color: var(--error); }

        .emp-btn-detail {
            display: inline-flex; align-items: center; justify-content: center; width: 100%;
            height: 34px; padding: 0 12px; box-sizing: border-box; border-radius: 8px;
            border: 1.5px solid var(--border); background: var(--white); color: var(--text-secondary);
            font-size: 12.5px; font-weight: 600; text-decoration: none; transition: all .15s ease;
        }
        .emp-btn-detail:hover { background: var(--primary); border-color: var(--primary); color: #fff; }

        .emp-row-empty {
            display: block; background: var(--white); border: 1px solid var(--border); border-radius: 12px;
        }
        .emp-row-empty td { display: block; text-align: center; padding: 36px 20px; }
        .emp-empty-title { font-size: 14px; font-weight: 600; color: var(--text-secondary); margin: 0 0 4px; }
        .emp-empty-desc { font-size: 12.5px; color: var(--text-muted); margin: 0; }

        .emp-pagination { margin-top: 16px; }

        /* ========== TABLET (640px+) ========== */
        @media (min-width: 640px) {
            .emp-search-input-wrap { flex: 1 1 260px; }
            .emp-btn-toggle-filter, .emp-btn-reset, .emp-btn-primary { flex: 0 0 auto; }
            .emp-filter-panel { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .emp-filter-toggles { grid-column: 1 / -1; }
            .emp-row { grid-template-columns: repeat(5, minmax(0, 1fr)) auto; align-items: start; }
            .emp-td--name { grid-column: 1 / -1; }
            .emp-td--action { grid-column: auto; align-self: center; }
            .emp-btn-detail { width: auto; }
        }

        /* ========== DESKTOP (1024px+): tabel padat ========== */
        @media (min-width: 1024px) {
            .emp-filter-panel { display: grid; grid-template-columns: repeat(3, minmax(0, 200px)) 1fr; align-items: center; }
            .emp-filter-toggles { grid-column: auto; justify-content: flex-end; }
            .emp-btn-toggle-filter { display: none; }

            .emp-table-card {
                background: var(--white); border: 1px solid var(--border); border-radius: 12px;
                box-shadow: 0 1px 2px rgba(0,0,0,0.03);
            }
            .emp-table-scroll { overflow-x: auto; border-radius: 12px; }
            .emp-table { display: table; min-width: 980px; }
            .emp-table thead { display: table-header-group; }
            .emp-table tbody { display: table-row-group; }
            .emp-table th {
                padding: 9px 12px; text-align: left; white-space: nowrap;
                font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em;
                color: var(--text-muted); background: var(--gray-50); border-bottom: 1px solid var(--border);
            }
            .emp-th--num { text-align: center !important; }
            .emp-row {
                display: table-row; background: transparent; border: none; border-radius: 0; padding: 0;
            }
            .emp-row:hover { background: rgba(20, 93, 160, 0.03); }
            .emp-row.is-inactive { background: var(--gray-100); }
            .emp-row + .emp-row .emp-td { border-top: 1px solid var(--border-light); }
            .emp-td { display: table-cell; padding: 9px 12px; vertical-align: middle; }
            .emp-td[data-label]::before { display: none; }
            .emp-td--name { min-width: 220px; max-width: 300px; }
            .emp-sub--contact { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px; }
            .emp-td--num { text-align: center; }
            .emp-td--action { width: 1%; white-space: nowrap; }
            .emp-btn-detail { width: auto; }

            .emp-row-empty { display: table-row; border: none; }
            .emp-row-empty td { display: table-cell; }
        }
    </style>

    <script>
    (function () {
        const form = document.getElementById('filterForm');
        const input = form?.querySelector('[data-employee-live-search]');
        const results = document.querySelector('[data-employee-results]');
        let timer;
        let request;

        if (!form || !input || !results) return;

        async function loadEmployeeResults() {
            const url = new URL(form.action, window.location.origin);
            const params = new URLSearchParams(new FormData(form));
            params.delete('page');
            url.search = params.toString();

            request?.abort();
            const currentRequest = new AbortController();
            request = currentRequest;
            results.classList.add('is-loading');
            results.setAttribute('aria-busy', 'true');

            try {
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: currentRequest.signal,
                });
                if (!response.ok) throw new Error('Pencarian karyawan gagal');

                const documentResult = new DOMParser().parseFromString(await response.text(), 'text/html');
                const selectors = [
                    '[data-employee-results-bar]',
                    '[data-employee-list]',
                    '[data-employee-pagination]',
                ];

                selectors.forEach((selector) => {
                    const current = document.querySelector(selector);
                    const next = documentResult.querySelector(selector);
                    if (!current || !next) throw new Error('Hasil pencarian tidak lengkap');
                    current.innerHTML = next.innerHTML;
                });

                window.history.replaceState(null, '', url);
            } catch (error) {
                if (error.name !== 'AbortError') console.error(error);
            } finally {
                if (request === currentRequest) {
                    results.classList.remove('is-loading');
                    results.setAttribute('aria-busy', 'false');
                }
            }
        }

        input.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(loadEmployeeResults, 350);
        });
    })();

    (function () {
        const modal = document.getElementById('leaveBalanceHistoryModal');
        const title = document.getElementById('leaveBalanceHistoryTitle');
        const body = modal?.querySelector('[data-leave-history-body]');
        let trigger;
        let request;

        if (!modal || !title || !body) return;

        const formatDays = (value) => `${Number(value).toLocaleString('id-ID', { maximumFractionDigits: 2 })} hari`;

        function closeModal() {
            request?.abort();
            modal.hidden = true;
            body.replaceChildren();
            trigger?.focus();
        }

        function addText(parent, tag, className, value) {
            const element = document.createElement(tag);
            element.className = className;
            element.textContent = value;
            parent.append(element);
            return element;
        }

        function renderHistory(data) {
            title.textContent = data.employee.name;
            const summary = document.createElement('div');
            summary.className = 'emp-leave-summary';
            addText(summary, 'span', '', 'Sisa cuti');
            addText(summary, 'strong', '', formatDays(data.employee.leave_balance));

            const list = document.createElement('div');
            list.className = 'emp-ledger-list';
            if (!data.transactions.length) {
                addText(list, 'p', 'emp-leave-empty', 'Belum ada riwayat saldo cuti.');
            }

            data.transactions.forEach((transaction) => {
                const row = document.createElement('article');
                row.className = 'emp-ledger-row';
                const top = document.createElement('div');
                top.className = 'emp-ledger-row__top';
                addText(top, 'span', 'emp-ledger-row__title', transaction.label);
                addText(top, 'span', `emp-ledger-row__effect ${transaction.effect === '-' ? 'is-minus' : transaction.effect === '+' ? 'is-plus' : ''}`, transaction.effect ? `${transaction.effect} ${formatDays(transaction.amount)}` : formatDays(transaction.amount));
                row.append(top);
                addText(row, 'div', 'emp-ledger-row__meta', `${transaction.occurred_at} · Dari ${formatDays(transaction.balance_before)} ke ${formatDays(transaction.balance_after)}`);
                if (transaction.leave_request) {
                    addText(row, 'div', 'emp-ledger-row__reference', `${transaction.leave_request.label} · ${transaction.leave_request.period}`);
                    if (transaction.leave_request.hr_note) {
                        addText(row, 'div', 'emp-ledger-row__note', `Catatan HRD: ${transaction.leave_request.hr_note}`);
                    }
                }
                list.append(row);
            });

            body.replaceChildren(summary, list);
        }

        document.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-leave-balance-history-url]');
            if (button) {
                trigger = button;
                title.textContent = button.dataset.leaveBalanceEmployee;
                body.replaceChildren();
                addText(body, 'p', 'emp-leave-empty', 'Memuat riwayat...');
                modal.hidden = false;
                request?.abort();
                request = new AbortController();

                try {
                    const response = await fetch(button.dataset.leaveBalanceHistoryUrl, {
                        headers: { Accept: 'application/json' },
                        signal: request.signal,
                    });
                    if (!response.ok) throw new Error('Riwayat belum bisa dimuat. Coba lagi.');
                    renderHistory(await response.json());
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        body.replaceChildren();
                        addText(body, 'p', 'emp-leave-error', error.message);
                    }
                }
                return;
            }

            if (event.target.closest('[data-leave-history-close]')) closeModal();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.hidden) closeModal();
        });
    })();

    function toggleFilterPanel() {
        const panel = document.getElementById('filterPanel');
        const btn = document.querySelector('.emp-btn-toggle-filter');
        const isOpen = panel.classList.toggle('is-open');
        btn.classList.toggle('active', isOpen);
    }

    </script>

</x-app>
