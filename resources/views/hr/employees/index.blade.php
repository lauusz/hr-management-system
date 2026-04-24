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
        <form method="GET" action="{{ route('hr.employees.index') }}" class="search-form" id="filterForm">
            <div class="search-row">
                <div class="search-input-wrapper">
                    <svg class="search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari nama, username, email, atau telepon..." class="search-input">
                </div>
                <button type="submit" class="btn-search">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Cari
                </button>
                @if(($search ?? null) || ($ptId ?? null) || ($positionId ?? null) || ($kategori ?? null) || ($nearExpiry ?? false) || ($noLeaveBalance ?? false) || ($noShift ?? false))
                <a href="{{ route('hr.employees.index') }}" class="btn-reset">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reset
                </a>
                @endif

                <button type="button" class="btn-toggle-filter {{ ($ptId ?? null) || ($positionId ?? null) || ($kategori ?? null) || ($nearExpiry ?? false) || ($noLeaveBalance ?? false) || ($noShift ?? false) ? 'active' : '' }}" onclick="toggleFilterPanel()">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filter
                    @if(($ptId ?? null) || ($positionId ?? null) || ($kategori ?? null) || ($nearExpiry ?? false) || ($noLeaveBalance ?? false) || ($noShift ?? false))
                    <span class="filter-badge">{{ collect([$ptId, $positionId, $kategori, $nearExpiry ? 1 : null, $noLeaveBalance ? 1 : null, $noShift ? 1 : null])->filter()->count() }}</span>
                    @endif
                </button>
            </div>

            <div class="filter-panel" id="filterPanel" style="{{ ($ptId ?? null) || ($positionId ?? null) || ($kategori ?? null) || ($nearExpiry ?? false) || ($noLeaveBalance ?? false) || ($noShift ?? false) ? '' : 'display: none;' }}">
                <div class="filter-row">
                    <div class="filter-group">
                        <label class="filter-label">PT</label>
                        <div class="select-wrapper">
                            <select name="pt_id" class="filter-select modern">
                                <option value="">Semua PT</option>
                                @foreach($ptOptions as $pt)
                                <option value="{{ $pt->id }}" @selected(($ptId ?? '') == $pt->id)>{{ $pt->name }}</option>
                                @endforeach
                            </select>
                            <svg class="select-arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Jabatan</label>
                        <div class="select-wrapper">
                            <select name="position_id" class="filter-select modern">
                                <option value="">Semua Jabatan</option>
                                @foreach($positionOptions as $pos)
                                <option value="{{ $pos->id }}" @selected(($positionId ?? '') == $pos->id)>{{ $pos->name }}</option>
                                @endforeach
                            </select>
                            <svg class="select-arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Kategori</label>
                        <div class="select-wrapper">
                            <select name="kategori" class="filter-select modern">
                                <option value="">Semua</option>
                                <option value="TETAP" @selected(($kategori ?? '') == 'TETAP')>Karyawan Tetap</option>
                                <option value="KONTRAK" @selected(($kategori ?? '') == 'KONTRAK')>Kontrak</option>
                                <option value="MAGANG" @selected(($kategori ?? '') == 'MAGANG')>Magang</option>
                            </select>
                            <svg class="select-arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <div class="filter-toggles">
                        <label class="toggle-chip {{ ($nearExpiry ?? false) ? 'active' : '' }}">
                            <input type="checkbox" name="near_expiry" value="1" onchange="this.form.submit()" @checked($nearExpiry ?? false) hidden>
                            <span class="toggle-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            Kontrak Mau Habis
                        </label>

                        <label class="toggle-chip {{ ($noLeaveBalance ?? false) ? 'active' : '' }}">
                            <input type="checkbox" name="no_leave_balance" value="1" onchange="this.form.submit()" @checked($noLeaveBalance ?? false) hidden>
                            <span class="toggle-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            Belum Dapat Cuti
                        </label>

                        <label class="toggle-chip {{ ($noShift ?? false) ? 'active' : '' }}">
                            <input type="checkbox" name="no_shift" value="1" onchange="this.form.submit()" @checked($noShift ?? false) hidden>
                            <span class="toggle-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            Belum Ada Shift
                        </label>
                    </div>

                    <button type="submit" class="btn-apply-filter">
                        Terapkan Filter
                    </button>
                </div>
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
                    $joinDateFormatted = $joinDate ? \Carbon\Carbon::parse($joinDate)->translatedFormat('j F Y') : '-';

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
                            @if($nearExpiry && $emp->probation_end_label)
                            <div class="info-chip info-chip-expiry">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Berakhir: {{ $emp->probation_end_label }}
                            </div>
                            @endif
                            <div class="info-chip info-chip-leave">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Cuti: <strong>{{ rtrim(rtrim(sprintf('%.1f', $emp->leave_balance ?? 0), '0'), '.') }}</strong>
                            </div>
                            <div class="shift-selector">
                                <select class="shift-dropdown @if($emp->employeeShift?->shift_id) has-shift @endif" data-user-id="{{ $emp->id }}" onchange="updateShift(this)">
                                    <option value="">- Shift -</option>
                                    @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}" @selected($emp->employeeShift?->shift_id == $shift->id)>{{ $shift->name }}</option>
                                    @endforeach
                                </select>
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

        /* --- TOGGLE FILTER BUTTON --- */
        .btn-toggle-filter {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 12px 18px;
            background: var(--white);
            color: var(--text-secondary);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
            position: relative;
        }

        .btn-toggle-filter:hover {
            border-color: var(--navy);
            color: var(--navy);
        }

        .btn-toggle-filter.active {
            background: var(--navy);
            border-color: var(--navy);
            color: var(--white);
        }

        .btn-toggle-filter.active svg {
            stroke: var(--white);
        }

        .filter-badge {
            background: #ef4444;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }

        .btn-toggle-filter.active .filter-badge {
            background: rgba(255,255,255,0.3);
        }

        /* --- FILTER PANEL --- */
        .filter-panel {
            background: #f9fafb;
            border-top: 1px solid var(--border);
            padding: 16px 20px;
            margin-top: 12px;
            border-radius: 0 0 10px 10px;
            animation: slideDown 0.2s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 160px;
        }

        .filter-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .select-wrapper {
            position: relative;
        }

        .filter-select.modern {
            width: 100%;
            padding: 10px 36px 10px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            color: var(--text-primary);
            background: var(--white);
            cursor: pointer;
            appearance: none;
            transition: all 0.2s;
        }

        .filter-select.modern:focus {
            outline: none;
            border-color: var(--navy);
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
        }

        .select-arrow {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: var(--text-muted);
        }

        /* --- TOGGLE CHIPS --- */
        .filter-toggles {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .toggle-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 20px;
            font-size: 12px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
            user-select: none;
        }

        .toggle-chip:hover {
            border-color: var(--navy);
            color: var(--navy);
        }

        .toggle-chip.active {
            background: var(--navy);
            border-color: var(--navy);
            color: var(--white);
        }

        .toggle-chip.active .toggle-icon {
            color: var(--white);
        }

        .toggle-icon {
            display: flex;
            align-items: center;
            color: var(--text-muted);
        }

        /* --- APPLY BUTTON --- */
        .btn-apply-filter {
            padding: 10px 20px;
            background: var(--navy);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-left: auto;
        }

        .btn-apply-filter:hover {
            background: var(--navy-dark);
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

        .info-chip-expiry {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }

        /* --- SHIFT SELECTOR --- */
        .shift-selector {
            display: inline-flex;
            align-items: center;
        }

        .shift-dropdown {
            padding: 4px 8px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 11px;
            color: var(--text-secondary);
            background: #fff;
            cursor: pointer;
            min-width: 90px;
        }

        .shift-dropdown:focus {
            outline: none;
            border-color: var(--navy);
        }

        .shift-dropdown.has-shift {
            background: #eff6ff;
            color: #1e40af;
            border-color: #bfdbfe;
            font-weight: 600;
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

            .btn-toggle-filter {
                width: 100%;
                justify-content: center;
            }

            .filter-panel {
                padding: 16px;
            }

            .filter-row {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                width: 100%;
            }

            .filter-toggles {
                width: 100%;
            }

            .toggle-chip {
                flex: 1;
                justify-content: center;
            }

            .btn-apply-filter {
                width: 100%;
                margin-left: 0;
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

            .shift-selector {
                width: 100%;
                margin-top: 8px;
            }

            .shift-dropdown {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .page-title { font-size: 18px; }

            .employee-contact { display: none; }

            .employee-name { font-size: 14px; }

            .info-chip { font-size: 10px; }
        }
    </style>

    <script>
    function toggleFilterPanel() {
        const panel = document.getElementById('filterPanel');
        const btn = document.querySelector('.btn-toggle-filter');

        if (panel.style.display === 'none') {
            panel.style.display = '';
            btn.classList.add('active');
        } else {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    }

    function updateShift(select) {
        const userId = select.dataset.userId;
        const shiftId = select.value;

        select.disabled = true;

        fetch(`/hr/employees/${userId}/shift-inline`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || document.querySelector('input[name=_token]')?.value,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ shift_id: shiftId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (shiftId) {
                    select.classList.add('has-shift');
                } else {
                    select.classList.remove('has-shift');
                }
            }
        })
        .catch(err => {
            alert('Gagal menyimpan shift');
            location.reload();
        })
        .finally(() => {
            select.disabled = false;
        });
    }
    </script>

</x-app>
