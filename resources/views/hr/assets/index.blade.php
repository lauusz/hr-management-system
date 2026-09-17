<x-app title="Inventaris Karyawan">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Inventaris Karyawan</h1>
                <p class="section-subtitle">Catat laptop, perangkat, dan fasilitas yang sedang dipegang karyawan</p>
            </div>
        </div>
    </x-slot>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="asm-alert asm-alert--success">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="asm-alert asm-alert--error">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Card: Daftar Asset --}}
    <div class="asm-card">
        <div class="asm-card-header">
            <div class="asm-card-title">
                <span>Daftar Asset</span>
                <span class="asm-card-count">{{ $assets->total() }}</span>
            </div>
            <a href="{{ route('hr.assets.create') }}" class="asm-btn asm-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Asset
            </a>
        </div>

        {{-- Filter & Pencarian --}}
        <div class="asm-filter-bar">
            <form class="asm-filter-form" method="GET" action="{{ route('hr.assets.index') }}">
                <div class="asm-search-wrap">
                    <svg class="asm-search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input class="asm-search-input" type="search" name="q" value="{{ request('q') }}" placeholder="Cari kode, nama, serial, hostname, email laptop" autocomplete="off" aria-label="Cari asset">
                </div>
                <select class="asm-select" name="status" aria-label="Filter status">
                    <option value="">Semua status</option>
                    @foreach(['AVAILABLE' => 'Available', 'ASSIGNED' => 'Assigned', 'SERVICE' => 'Service', 'LOST' => 'Lost', 'DISPOSAL' => 'Disposal'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="asm-btn asm-btn-primary" type="submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filter
                </button>
                @if(request()->filled('q') || request()->filled('status'))
                <a class="asm-btn asm-btn-reset" href="{{ route('hr.assets.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reset
                </a>
                @endif
            </form>
        </div>

        @if($assets->isEmpty())
        <div class="asm-empty">
            <div class="asm-empty-icon">
                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="asm-empty-title">Belum Ada Asset</h3>
            <p class="asm-empty-desc">
                @if(request()->filled('q') || request()->filled('status'))
                    Tidak ada asset yang sesuai dengan pencarian atau filter yang dipilih.
                @else
                    Belum ada data asset yang tercatat. Tambahkan asset pertama melalui tombol "Tambah Asset".
                @endif
            </p>
        </div>
        @else
        <div class="asm-table-wrap">
            <table class="asm-table">
                <thead>
                    <tr>
                        <th class="asm-col-no">#</th>
                        <th>Kode</th>
                        <th>Asset</th>
                        <th>Kategori</th>
                        <th>Pemegang</th>
                        <th>Status</th>
                        <th class="asm-col-action"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assets as $i => $asset)
                    <tr>
                        <td class="asm-cell-muted asm-cell-center">{{ ($assets->firstItem() ?? 1) + $i }}</td>
                        <td>
                            <span class="asm-code">{{ $asset->asset_code }}</span>
                        </td>
                        <td>
                            <div class="asm-asset-cell">
                                @if($asset->photo_path)
                                    <button type="button" data-image-viewer-src="{{ asset('storage/'.$asset->photo_path) }}" data-image-viewer-alt="Foto asset {{ $asset->name }}" class="asm-thumb-btn" title="Lihat foto asset">
                                        <img class="asm-thumb" src="{{ asset('storage/'.$asset->photo_path) }}" alt="Foto {{ $asset->name }}">
                                    </button>
                                @else
                                    <div class="asm-thumb-placeholder">{{ substr($asset->name, 0, 1) }}</div>
                                @endif
                                <div class="asm-asset-info">
                                    <span class="asm-asset-name">{{ $asset->name }}</span>
                                    <span class="asm-asset-detail">{{ $asset->serial_number ?: '-' }} · {{ $asset->hostname ?: '-' }} · {{ $asset->email_laptop ?: '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($asset->category)
                                <span class="asm-badge asm-badge-category">{{ $asset->category->name }}</span>
                            @else
                                <span class="asm-cell-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $asset->currentUser->name ?? '-' }}</td>
                        <td>
                            <span class="asm-badge {{ $asset->asset_status === 'ASSIGNED' ? 'asm-badge-success' : ($asset->asset_status === 'AVAILABLE' ? 'asm-badge-neutral' : 'asm-badge-warning') }}">{{ $asset->asset_status }}</span>
                        </td>
                        <td class="asm-actions-cell">
                            <a href="{{ route('hr.assets.show', $asset) }}" class="asm-icon-btn" title="Detail">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <a href="{{ route('hr.assets.edit', $asset) }}" class="asm-icon-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if($assets->hasPages())
    <div class="asm-pagination-wrap">
        <x-pagination :items="$assets" preserve-query />
    </div>
    @endif

    <style>
        /* ========================================== */
        /* SECTION HEADER (x-slot)                    */
        /* ========================================== */
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .section-icon svg {
            width: 16px;
            height: 16px;
        }
        .section-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy { background: rgba(10, 61, 98, 0.08); color: var(--primary-dark); }

        /* ========================================== */
        /* FLASH MESSAGES                             */
        /* ========================================== */
        .asm-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 0.8125rem;
            font-weight: 600;
        }
        .asm-alert svg { flex-shrink: 0; }
        .asm-alert--success {
            background: rgba(34, 197, 94, 0.08);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.25);
        }
        .asm-alert--error {
            background: rgba(239, 68, 68, 0.08);
            color: #b91c1c;
            border: 1px solid rgba(239, 68, 68, 0.25);
        }

        /* ========================================== */
        /* CARD                                       */
        /* ========================================== */
        .asm-card {
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            max-width: 100%;
        }
        .asm-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border-light);
            background: var(--gray-50);
        }
        .asm-card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .asm-card-count {
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
            font-size: 0.6875rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 9999px;
            line-height: 1.4;
        }

        /* ========================================== */
        /* FILTER & SEARCH                            */
        /* ========================================== */
        .asm-filter-bar {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border-light);
        }
        .asm-filter-form {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }
        .asm-search-wrap {
            position: relative;
            display: flex;
            align-items: center;
            flex: 1 1 260px;
            min-width: 220px;
        }
        .asm-search-icon {
            position: absolute;
            left: 12px;
            color: var(--text-light);
            pointer-events: none;
        }
        .asm-search-input {
            width: 100%;
            padding: 9px 12px 9px 36px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.8125rem;
            font-family: inherit;
            color: var(--text-primary);
            background: var(--white);
            transition: all 0.2s ease;
        }
        .asm-search-input::placeholder { color: var(--text-light); }
        .asm-search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.12);
        }
        .asm-select {
            padding: 9px 12px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.8125rem;
            font-family: inherit;
            color: var(--text-primary);
            background: var(--white);
            transition: all 0.2s ease;
            min-width: 150px;
        }
        .asm-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(20, 93, 160, 0.12);
        }

        /* ========================================== */
        /* BUTTONS                                    */
        /* ========================================== */
        .asm-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 9px 16px;
            border-radius: 10px;
            font-size: 0.8125rem;
            font-weight: 600;
            font-family: inherit;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .asm-btn svg { width: 15px; height: 15px; }
        .asm-btn-primary {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
        }
        .asm-btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .asm-btn-reset {
            background: var(--white);
            color: var(--text-secondary);
            border: 1.5px solid var(--border);
        }
        .asm-btn-reset:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* ========================================== */
        /* TABLE                                      */
        /* ========================================== */
        .asm-table-wrap { overflow-x: auto; }
        .asm-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 820px;
        }
        .asm-table th {
            text-align: left;
            padding: 13px 16px;
            font-size: 0.6875rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--white);
            border-bottom: 1px solid var(--border-light);
        }
        .asm-table td {
            padding: 13px 16px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
            font-size: 0.8125rem;
        }
        .asm-table tr:last-child td { border-bottom: none; }
        .asm-table tbody tr:hover td { background: var(--gray-50); }
        .asm-col-no { width: 48px; text-align: center; }
        .asm-col-action { width: 96px; }

        /* ========================================== */
        /* ASSET CELL                                 */
        /* ========================================== */
        .asm-code {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 0.8125rem;
        }
        .asm-asset-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .asm-thumb-btn {
            border: 0;
            padding: 0;
            background: transparent;
            cursor: pointer;
            flex-shrink: 0;
        }
        .asm-thumb {
            width: 42px;
            height: 42px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--gray-50);
            display: block;
        }
        .asm-thumb-placeholder {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
            font-size: 0.9rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            text-transform: uppercase;
        }
        .asm-asset-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }
        .asm-asset-name {
            font-weight: 600;
            color: var(--text-primary);
        }
        .asm-asset-detail {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* ========================================== */
        /* BADGES                                     */
        /* ========================================== */
        .asm-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 0.6875rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .asm-badge-success { color: #15803d; background: rgba(34, 197, 94, 0.12); }
        .asm-badge-warning { color: #b45309; background: rgba(245, 158, 11, 0.13); }
        .asm-badge-neutral { color: #4b5563; background: var(--gray-100); }
        .asm-badge-category { color: var(--primary); background: rgba(20, 93, 160, 0.08); text-transform: none; font-weight: 700; }

        /* ========================================== */
        /* CELL HELPERS                               */
        /* ========================================== */
        .asm-cell-muted { color: var(--text-muted); }
        .asm-cell-center { text-align: center; }

        /* ========================================== */
        /* ACTIONS                                    */
        /* ========================================== */
        .asm-actions-cell { text-align: right; white-space: nowrap; }
        .asm-icon-btn {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s ease;
            vertical-align: middle;
        }
        .asm-icon-btn:hover { background: var(--gray-50); color: var(--primary); }
        .asm-icon-btn svg { width: 16px; height: 16px; }

        /* ========================================== */
        /* EMPTY STATE                                */
        /* ========================================== */
        .asm-empty {
            text-align: center;
            padding: 48px 24px;
        }
        .asm-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 16px;
            background: var(--gray-50);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
        }
        .asm-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin: 0 0 6px;
        }
        .asm-empty-desc {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin: 0 auto;
            max-width: 340px;
            line-height: 1.5;
        }

        /* ========================================== */
        /* PAGINATION                                 */
        /* ========================================== */
        .asm-pagination-wrap {
            margin-top: 16px;
            display: flex;
            justify-content: center;
        }

        /* ========================================== */
        /* MOBILE RESPONSIVE                          */
        /* ========================================== */
        @media (max-width: 640px) {
            .asm-card-header {
                flex-direction: column;
                align-items: stretch;
            }
            .asm-btn-primary { width: 100%; }
            .asm-filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            .asm-search-wrap { min-width: 0; }
            .asm-select { width: 100%; }
            .asm-btn-reset { width: 100%; }
            .asm-table th, .asm-table td { padding: 12px 12px; }
        }
    </style>
</x-app>
