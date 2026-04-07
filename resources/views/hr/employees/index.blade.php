<x-app title="Daftar Karyawan">

    @if(session('success'))
    <div class="alert alert-success">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div class="header-text">
            <p class="page-subtitle">{{ $totalEmployees }} karyawan terdaftar</p>
        </div>
        <a href="{{ route('hr.employees.create') }}" class="btn-add-header">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Karyawan
        </a>
    </div>

    {{-- SEARCH & FILTERS --}}
    <div class="card card-search">
        <form method="GET" action="{{ route('hr.employees.index') }}" class="search-form">
            <div class="search-row">
                <div class="search-input-wrapper">
                    <svg class="search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari nama, username, email, atau telepon..." class="search-input">
                </div>
                <button type="submit" class="btn-search">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Cari
                </button>
                @if(($search ?? null) || ($ptId ?? null) || ($positionId ?? null) || ($kategori ?? null) || ($nearExpiry ?? false))
                <a href="{{ route('hr.employees.index') }}" class="btn-reset">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reset
                </a>
                @endif
            </div>

            <div class="filter-row">
                <select name="pt_id" class="filter-select">
                    <option value="">Semua PT</option>
                    @foreach($ptOptions as $pt)
                    <option value="{{ $pt->id }}" @selected(($ptId ?? '') == $pt->id)>{{ $pt->name }}</option>
                    @endforeach
                </select>

                <select name="position_id" class="filter-select">
                    <option value="">Semua Jabatan</option>
                    @foreach($positionOptions as $pos)
                    <option value="{{ $pos->id }}" @selected(($positionId ?? '') == $pos->id)>{{ $pos->name }}</option>
                    @endforeach
                </select>

                <select name="kategori" class="filter-select">
                    <option value="">Semua Kategori</option>
                    <option value="Karyawan Tetap" @selected(($kategori ?? '') == 'Karyawan Tetap')>Karyawan Tetap</option>
                    <option value="Karyawan Kontrak" @selected(($kategori ?? '') == 'Karyawan Kontrak')>Karyawan Kontrak</option>
                </select>

                <label class="filter-checkbox">
                    <input type="checkbox" name="near_expiry" value="1" onchange="this.form.submit()" @checked($nearExpiry ?? false)>
                    <span class="checkbox-custom"></span>
                    Kontrak Mau Habis
                </label>
            </div>
        </form>
    </div>

    {{-- EMPLOYEE LIST --}}
    <div class="card">
        <div class="list-header">
            <span class="result-count">Menampilkan {{ $items->count() }} dari {{ $totalEmployees }} karyawan</span>
        </div>

        <div class="employee-list">
            @forelse($items as $emp)
                @php
                    // Status
                    $statusRaw = $emp->status ?? '-';
                    $statusClass = 'status-active';
                    $statusLabel = 'Aktif';

                    if ($statusRaw === 'INACTIVE') {
                        $labelMap = [
                            'RESIGN' => 'Resign',
                            'HABIS_KONTRAK' => 'Habis Kontrak',
                            'PHK' => 'PHK',
                            'PENSIUN' => 'Pensiun',
                            'MENINGGAL' => 'Meninggal',
                            'LAINNYA' => 'Nonaktif',
                        ];
                        $code = $emp->profile?->exit_reason_code;
                        $statusLabel = $code ? ($labelMap[$code] ?? $code) : 'Nonaktif';
                        $statusClass = 'status-inactive';
                    }

                    // Masa Kerja
                    $joinDate = $emp->profile?->tgl_bergabung;
                    $masaKerja = '-';
                    if ($joinDate) {
                        $start = \Carbon\Carbon::parse($joinDate)->startOfDay();
                        $end = \Carbon\Carbon::today();
                        if ($end->greaterThanOrEqualTo($start)) {
                            $diff = $start->diff($end);
                            $parts = [];
                            if ($diff->y > 0) $parts[] = $diff->y . ' thn';
                            if ($diff->m > 0) $parts[] = $diff->m . ' bln';
                            if (empty($parts)) $parts[] = $diff->d . ' hari';
                            $masaKerja = implode(' ', array_slice($parts, 0, 2));
                        }
                    }

                    // Join date formatted
                    $joinDateFormatted = $joinDate ? \Carbon\Carbon::parse($joinDate)->format('d M Y') : '-';

                    // Contact info
                    $email = $emp->email ?? '-';
                    $phone = $emp->phone ?? '-';
                @endphp

                <div class="employee-card">
                    <div class="card-left">
                        <div class="avatar">
                            {{ substr($emp->name, 0, 1) }}
                        </div>
                        <div class="employee-info">
                            <a href="{{ route('hr.employees.show', $emp->id) }}" class="employee-name">
                                {{ $emp->name }}
                            </a>
                            <div class="employee-meta">
                                <span class="meta-item">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    {{ $emp->division?->name ?? '-' }}
                                </span>
                                <span class="meta-separator">•</span>
                                <span class="meta-item">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    {{ $emp->position?->name ?? '-' }}
                                </span>
                            </div>
                            <div class="employee-contact">
                                <span class="contact-item">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    {{ $email }}
                                </span>
                                @if($phone !== '-')
                                <span class="contact-separator">|</span>
                                <span class="contact-item">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $phone }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-right">
                        <div class="right-top">
                            <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            <span class="pt-badge">{{ $emp->profile?->pt?->name ?? '-' }}</span>
                        </div>
                        <div class="right-bottom">
                            <div class="info-chip">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $masaKerja }}
                            </div>
                            <div class="info-chip">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ $joinDateFormatted }}
                            </div>
                            <div class="info-chip info-chip-leave">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Cuti: <strong>{{ $emp->leave_balance ?? 0 }}</strong>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('hr.employees.show', $emp->id) }}" class="btn-detail">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <p>Tidak ada karyawan yang ditemukan</p>
                    <span>Coba ubah kata kunci pencarian atau filter yang digunakan</span>
                </div>
            @endforelse
        </div>
    </div>

    <div style="margin-top: 20px;">
        <x-pagination :items="$items" />
    </div>

    <style>
        :root {
            --navy: #1e4a8d;
            --navy-dark: #163a75;
            --bg-page: #f8fafc;
            --white: #ffffff;
            --border: #e5e7eb;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
        }

        * { box-sizing: border-box; }

        /* --- ALERT --- */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        /* --- PAGE HEADER --- */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 16px;
        }

        .header-text { flex: 1; }

        .page-title {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .page-subtitle {
            margin: 4px 0 0;
            font-size: 13px;
            color: var(--text-secondary);
        }

        .btn-add-header {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: var(--navy);
            color: var(--white);
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
            flex-shrink: 0;
        }

        .btn-add-header:hover { background: var(--navy-dark); }

        /* --- SEARCH CARD --- */
        .card-search {
            margin-bottom: 16px;
        }

        .search-form {
            padding: 16px 20px;
        }

        .search-row {
            display: flex;
            gap: 10px;
            margin-bottom: 12px;
        }

        .search-input-wrapper {
            flex: 1;
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .search-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            color: var(--text-primary);
            background: var(--white);
            transition: border-color 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--navy);
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
        }

        .search-input::placeholder { color: var(--text-muted); }

        .btn-search {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: var(--navy);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }

        .btn-search:hover { background: var(--navy-dark); }

        .btn-reset {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 12px 16px;
            background: var(--white);
            color: var(--text-secondary);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-reset:hover {
            border-color: var(--text-muted);
            color: var(--text-primary);
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            color: var(--text-primary);
            background: var(--white);
            cursor: pointer;
            min-width: 130px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--navy);
        }

        .filter-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-checkbox:hover {
            border-color: var(--navy);
            color: var(--navy);
        }

        .filter-checkbox input { display: none; }

        .checkbox-custom {
            width: 16px;
            height: 16px;
            border: 1px solid var(--border);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .filter-checkbox input:checked + .checkbox-custom {
            background: var(--navy);
            border-color: var(--navy);
        }

        .filter-checkbox input:checked + .checkbox-custom::after {
            content: '';
            width: 6px;
            height: 10px;
            border: 2px solid white;
            border-top: none;
            border-left: none;
            transform: rotate(45deg) translateY(-1px);
        }

        /* --- LIST CARD --- */
        .card {
            background: var(--white);
            border-radius: 14px;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .list-header {
            padding: 12px 20px;
            background: #f9fafb;
            border-bottom: 1px solid var(--border);
        }

        .result-count {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .employee-list {
            display: flex;
            flex-direction: column;
        }

        /* --- EMPLOYEE CARD --- */
        .employee-card {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }

        .employee-card:last-child { border-bottom: none; }
        .employee-card:hover { background: #fafbfc; }

        .card-left {
            display: flex;
            align-items: center;
            gap: 14px;
            flex: 1;
            min-width: 0;
        }

        .avatar {
            width: 48px;
            height: 48px;
            background: var(--navy);
            color: var(--white);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .employee-info { min-width: 0; flex: 1; }

        .employee-name {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
            text-decoration: none;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .employee-name:hover { color: var(--navy); }

        .employee-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 4px;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .meta-separator { color: var(--text-muted); }

        .employee-contact {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 4px;
            flex-wrap: wrap;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            color: var(--text-muted);
        }

        .contact-separator { color: var(--border); }

        /* --- RIGHT SECTION --- */
        .card-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
            flex-shrink: 0;
        }

        .right-top {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .right-bottom {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .pt-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            background: #f3f4f6;
            color: var(--text-secondary);
            border: 1px solid var(--border);
        }

        .info-chip {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            background: #f9fafb;
            border-radius: 6px;
            font-size: 11px;
            color: var(--text-secondary);
        }

        .info-chip-leave {
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .info-chip-leave strong {
            font-weight: 700;
        }

        /* --- DETAIL BUTTON --- */
        .btn-detail {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .btn-detail:hover {
            background: var(--navy);
            border-color: var(--navy);
            color: var(--white);
        }

        /* --- EMPTY STATE --- */
        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: #9ca3af;
        }

        .empty-state p {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0 0 4px;
        }

        .empty-state span {
            font-size: 13px;
            color: var(--text-muted);
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 1024px) {
            .employee-card {
                padding: 14px 16px;
            }

            .card-left {
                gap: 12px;
            }

            .avatar {
                width: 44px;
                height: 44px;
                font-size: 16px;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }

            .btn-add-header {
                justify-content: center;
            }

            .search-row {
                flex-direction: column;
            }

            .btn-search, .btn-reset {
                width: 100%;
                justify-content: center;
            }

            .filter-row {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-select, .filter-checkbox {
                width: 100%;
            }

            .employee-card {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
                padding: 16px;
            }

            .card-left {
                width: 100%;
            }

            .card-right {
                width: 100%;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                padding-top: 12px;
                border-top: 1px solid var(--border);
            }

            .btn-detail {
                position: absolute;
                right: 16px;
                top: 50%;
                transform: translateY(-50%);
                width: 40px;
                height: 40px;
            }

            .employee-card {
                position: relative;
                padding-right: 60px;
            }

            .right-bottom {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 480px) {
            .page-title { font-size: 18px; }

            .employee-contact { display: none; }

            .employee-name { font-size: 14px; }

            .info-chip { font-size: 10px; }
        }
    </style>

</x-app>
