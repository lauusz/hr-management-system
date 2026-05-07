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

    @if(session('success'))
    <div class="emp-alert emp-alert--success">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- CTA --}}
    <div class="emp-cta-bar">
        <a href="{{ route('hr.employees.create') }}" class="emp-btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Karyawan
        </a>
    </div>

    {{-- SEARCH & FILTERS --}}
    <div class="emp-filter-card">
        <form method="GET" action="{{ route('hr.employees.index') }}" class="emp-filter-form" id="filterForm">
            <div class="emp-search-row">
                <div class="emp-search-input-wrap">
                    <svg class="emp-search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari nama, username, email, atau telepon..." class="emp-search-input">
                </div>
                <button type="submit" class="emp-btn-search">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Cari
                </button>
                @if(($search ?? null) || ($ptId ?? null) || ($positionId ?? null) || ($kategori ?? null) || ($nearExpiry ?? false) || ($noLeaveBalance ?? false) || ($noShift ?? false))
                <a href="{{ route('hr.employees.index') }}" class="emp-btn-reset">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reset
                </a>
                @endif

                <button type="button" class="emp-btn-toggle-filter {{ ($ptId ?? null) || ($positionId ?? null) || ($kategori ?? null) || ($nearExpiry ?? false) || ($noLeaveBalance ?? false) || ($noShift ?? false) ? 'active' : '' }}" onclick="toggleFilterPanel()">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filter
                    @if(($ptId ?? null) || ($positionId ?? null) || ($kategori ?? null) || ($nearExpiry ?? false) || ($noLeaveBalance ?? false) || ($noShift ?? false))
                    <span class="emp-filter-badge">{{ collect([$ptId, $positionId, $kategori, $nearExpiry ? 1 : null, $noLeaveBalance ? 1 : null, $noShift ? 1 : null])->filter()->count() }}</span>
                    @endif
                </button>
            </div>

            <div class="emp-filter-panel" id="filterPanel" style="{{ ($ptId ?? null) || ($positionId ?? null) || ($kategori ?? null) || ($nearExpiry ?? false) || ($noLeaveBalance ?? false) || ($noShift ?? false) ? '' : 'display: none;' }}">
                <div class="emp-filter-grid">
                    <div class="emp-filter-group">
                        <label class="emp-filter-label">PT</label>
                        <div class="emp-select-wrap">
                            <select name="pt_id" class="emp-select">
                                <option value="">Semua PT</option>
                                @foreach($ptOptions as $pt)
                                <option value="{{ $pt->id }}" @selected(($ptId ?? '') == $pt->id)>{{ $pt->name }}</option>
                                @endforeach
                            </select>
                            <svg class="emp-select-arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <div class="emp-filter-group">
                        <label class="emp-filter-label">Jabatan</label>
                        <div class="emp-select-wrap">
                            <select name="position_id" class="emp-select">
                                <option value="">Semua Jabatan</option>
                                @foreach($positionOptions as $pos)
                                <option value="{{ $pos->id }}" @selected(($positionId ?? '') == $pos->id)>{{ $pos->name }}</option>
                                @endforeach
                            </select>
                            <svg class="emp-select-arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <div class="emp-filter-group">
                        <label class="emp-filter-label">Kategori</label>
                        <div class="emp-select-wrap">
                            <select name="kategori" class="emp-select">
                                <option value="">Semua</option>
                                <option value="TETAP" @selected(($kategori ?? '') == 'TETAP')>Karyawan Tetap</option>
                                <option value="KONTRAK" @selected(($kategori ?? '') == 'KONTRAK')>Kontrak</option>
                                <option value="MAGANG" @selected(($kategori ?? '') == 'MAGANG')>Magang</option>
                            </select>
                            <svg class="emp-select-arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <div class="emp-filter-toggles">
                        <label class="emp-toggle-chip {{ ($nearExpiry ?? false) ? 'active' : '' }}">
                            <input type="checkbox" name="near_expiry" value="1" onchange="this.form.submit()" @checked($nearExpiry ?? false) hidden>
                            <span class="emp-toggle-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            Kontrak Mau Habis
                        </label>

                        <label class="emp-toggle-chip {{ ($noLeaveBalance ?? false) ? 'active' : '' }}">
                            <input type="checkbox" name="no_leave_balance" value="1" onchange="this.form.submit()" @checked($noLeaveBalance ?? false) hidden>
                            <span class="emp-toggle-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            Belum Dapat Cuti
                        </label>

                        <label class="emp-toggle-chip {{ ($noShift ?? false) ? 'active' : '' }}">
                            <input type="checkbox" name="no_shift" value="1" onchange="this.form.submit()" @checked($noShift ?? false) hidden>
                            <span class="emp-toggle-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            Belum Ada Shift
                        </label>
                    </div>

                    <div class="emp-filter-actions">
                        <button type="submit" class="emp-btn-apply">Terapkan Filter</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- RESULTS BAR --}}
    <div class="emp-results-bar">
        <span>Menampilkan {{ $items->count() }} dari {{ $totalEmployees }} karyawan</span>
    </div>

    {{-- EMPLOYEE LIST --}}
    <div class="emp-list">
        @forelse($items as $emp)
            @php
                $statusRaw = $emp->status ?? '-';
                $statusClass = 'emp-badge--success';
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
                    $statusClass = 'emp-badge--error';
                }

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

                $joinDateFormatted = $joinDate ? \Carbon\Carbon::parse($joinDate)->translatedFormat('j F Y') : '-';
                $email = $emp->email ?? '-';
                $phone = $emp->phone ?? '-';
            @endphp

            <div class="emp-card">
                <div class="emp-card-main">
                    <div class="emp-avatar">
                        {{ substr($emp->name, 0, 1) }}
                    </div>
                    <div class="emp-info">
                        <div class="emp-info-top">
                            <a href="{{ route('hr.employees.show', $emp->id) }}" class="emp-name">{{ $emp->name }}</a>
                            <span class="emp-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>
                        <div class="emp-meta">
                            <span class="emp-meta-item">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                {{ $emp->division?->name ?? '-' }}
                            </span>
                            <span class="emp-meta-sep">•</span>
                            <span class="emp-meta-item">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                {{ $emp->position?->name ?? '-' }}
                            </span>
                        </div>
                        <div class="emp-contact">
                            <span class="emp-contact-item">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                {{ $email }}
                            </span>
                            @if($phone !== '-')
                            <span class="emp-contact-sep">|</span>
                            <span class="emp-contact-item">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $phone }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="emp-card-chips">
                    <span class="emp-chip emp-chip--pt">{{ $emp->profile?->pt?->name ?? '-' }}</span>
                    <span class="emp-chip">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $masaKerja }}
                    </span>
                    <span class="emp-chip">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ $joinDateFormatted }}
                    </span>
                    @if($nearExpiry && $emp->probation_end_label)
                    <span class="emp-chip emp-chip--warning">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Berakhir: {{ $emp->probation_end_label }}
                    </span>
                    @endif
                    <span class="emp-chip emp-chip--info">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Cuti: <strong>{{ rtrim(rtrim(sprintf('%.1f', $emp->leave_balance ?? 0), '0'), '.') }}</strong>
                    </span>
                </div>

                <div class="emp-card-actions">
                    <select class="emp-shift-select @if($emp->employeeShift?->shift_id) has-shift @endif" data-user-id="{{ $emp->id }}" onchange="updateShift(this)">
                        <option value="">- Shift -</option>
                        @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}" @selected($emp->employeeShift?->shift_id == $shift->id)>{{ $shift->name }}</option>
                        @endforeach
                    </select>
                    <a href="{{ route('hr.employees.show', $emp->id) }}" class="emp-btn-detail" aria-label="Detail karyawan">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="emp-empty">
                <div class="emp-empty-icon">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <p class="emp-empty-title">Tidak ada karyawan yang ditemukan</p>
                <p class="emp-empty-desc">Coba ubah kata kunci pencarian atau filter yang digunakan</p>
            </div>
        @endforelse
    </div>

    <div class="emp-pagination">
        <x-pagination :items="$items" />
    </div>

    <style>
        /* ========================================== */
        /* BASE & RESET                               */
        /* ========================================== */
        .emp-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .emp-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }

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
        .icon-navy  { background: rgba(10, 61, 98, 0.08);  color: var(--primary-dark); }

        /* ========================================== */
        /* CTA BAR                                    */
        /* ========================================== */
        .emp-cta-bar {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 16px;
        }
        .emp-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
            flex-shrink: 0;
            width: 100%;
        }
        .emp-btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .emp-btn-primary svg { flex-shrink: 0; }

        /* ========================================== */
        /* FILTER CARD                                */
        /* ========================================== */
        .emp-filter-card {
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            margin-bottom: 16px;
            overflow: hidden;
        }
        .emp-filter-form {
            padding: 14px 16px;
        }
        .emp-search-row {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .emp-search-input-wrap {
            position: relative;
            flex: 1;
        }
        .emp-search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            pointer-events: none;
        }
        .emp-search-input {
            width: 100%;
            padding: 10px 14px 10px 42px;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 14px;
            color: var(--text-primary);
            background: var(--white);
            transition: all 0.2s ease;
            outline: none;
            font-family: inherit;
        }
        .emp-search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .emp-search-input::placeholder { color: var(--text-light); }

        .emp-btn-search {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 16px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            white-space: nowrap;
            width: 100%;
        }
        .emp-btn-search:hover { background: var(--primary-dark); }

        .emp-btn-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 14px;
            background: var(--white);
            color: var(--text-muted);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
            width: 100%;
        }
        .emp-btn-reset:hover {
            background: var(--danger-light);
            border-color: #fecaca;
            color: var(--error);
        }

        .emp-btn-toggle-filter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 16px;
            background: var(--white);
            color: var(--text-muted);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            position: relative;
            font-family: inherit;
            width: 100%;
        }
        .emp-btn-toggle-filter:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        .emp-btn-toggle-filter.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .emp-btn-toggle-filter.active svg { stroke: #fff; }
        .emp-filter-badge {
            background: var(--error);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }
        .emp-btn-toggle-filter.active .emp-filter-badge {
            background: rgba(255,255,255,0.3);
        }

        /* ========================================== */
        /* FILTER PANEL                               */
        /* ========================================== */
        .emp-filter-panel {
            background: var(--gray-50);
            border-top: 1px solid var(--border);
            margin: 14px -16px -14px;
            padding: 14px 16px;
            animation: empSlideDown 0.2s ease;
        }
        @keyframes empSlideDown {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .emp-filter-grid {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .emp-filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .emp-filter-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .emp-select-wrap {
            position: relative;
        }
        .emp-select {
            width: 100%;
            padding: 10px 36px 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 13px;
            color: var(--text-primary);
            background: var(--white);
            cursor: pointer;
            appearance: none;
            transition: all 0.2s ease;
            outline: none;
            font-family: inherit;
        }
        .emp-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .emp-select-arrow {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: var(--text-light);
        }

        .emp-filter-toggles {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .emp-toggle-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
        }
        .emp-toggle-chip:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        .emp-toggle-chip.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .emp-toggle-chip.active .emp-toggle-icon { color: #fff; }
        .emp-toggle-icon {
            display: flex;
            align-items: center;
            color: var(--text-light);
        }

        .emp-filter-actions {
            display: flex;
            justify-content: flex-end;
        }
        .emp-btn-apply {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 20px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        .emp-btn-apply:hover { background: var(--primary-dark); }

        /* ========================================== */
        /* RESULTS BAR                                */
        /* ========================================== */
        .emp-results-bar {
            margin-bottom: 12px;
        }
        .emp-results-bar span {
            font-size: 12px;
            font-weight: 500;
            color: var(--text-muted);
        }

        /* ========================================== */
        /* EMPLOYEE LIST                              */
        /* ========================================== */
        .emp-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* ========================================== */
        /* EMPLOYEE CARD                              */
        /* ========================================== */
        .emp-card {
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            transition: all 0.2s ease;
        }
        .emp-card:hover {
            border-color: rgba(20, 93, 160, 0.25);
            box-shadow: 0 4px 12px rgba(20, 93, 160, 0.06);
        }

        /* Card Main (Identity) */
        .emp-card-main {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .emp-avatar {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .emp-info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .emp-info-top {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .emp-name {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
            text-decoration: none;
            letter-spacing: -0.01em;
            line-height: 1.3;
        }
        .emp-name:hover { color: var(--primary); }

        /* Badges */
        .emp-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            flex-shrink: 0;
        }
        .emp-badge--success {
            background: rgba(34, 197, 94, 0.1);
            color: #15803d;
        }
        .emp-badge--error {
            background: rgba(239, 68, 68, 0.1);
            color: #b91c1c;
        }

        /* Meta */
        .emp-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .emp-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-secondary);
        }
        .emp-meta-sep { color: var(--text-light); font-size: 12px; }

        /* Contact */
        .emp-contact {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .emp-contact-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            color: var(--text-muted);
        }
        .emp-contact-sep { color: var(--border); font-size: 11px; }

        /* Chips */
        .emp-card-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .emp-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 10px;
            background: var(--gray-50);
            border-radius: 8px;
            font-size: 11px;
            font-weight: 500;
            color: var(--text-secondary);
            border: 1px solid var(--border-light);
        }
        .emp-chip--pt {
            background: var(--gray-100);
            color: var(--text-muted);
            border-color: var(--border);
        }
        .emp-chip--info {
            background: rgba(59, 130, 246, 0.08);
            color: var(--info);
            border-color: rgba(59, 130, 246, 0.15);
        }
        .emp-chip--info strong { font-weight: 700; }
        .emp-chip--warning {
            background: rgba(245, 158, 11, 0.08);
            color: #a16207;
            border-color: rgba(245, 158, 11, 0.15);
        }

        /* Actions */
        .emp-card-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .emp-shift-select {
            flex: 1;
            padding: 8px 12px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 13px;
            color: var(--text-secondary);
            background: var(--white);
            cursor: pointer;
            outline: none;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        .emp-shift-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .emp-shift-select.has-shift {
            background: rgba(59, 130, 246, 0.06);
            color: var(--primary);
            border-color: rgba(59, 130, 246, 0.25);
            font-weight: 600;
        }
        .emp-btn-detail {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        .emp-btn-detail:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        /* ========================================== */
        /* EMPTY STATE                                */
        /* ========================================== */
        .emp-empty {
            text-align: center;
            padding: 48px 24px;
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .emp-empty-icon {
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
        .emp-empty-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-secondary);
            margin: 0 0 6px;
        }
        .emp-empty-desc {
            font-size: 13px;
            color: var(--text-muted);
            margin: 0 auto;
            max-width: 280px;
            line-height: 1.5;
        }

        /* ========================================== */
        /* PAGINATION                                 */
        /* ========================================== */
        .emp-pagination {
            margin-top: 24px;
        }

        /* ========================================== */
        /* SMALL MOBILE (480px and below)             */
        /* ========================================== */
        @media (max-width: 480px) {
            .emp-contact { display: none; }
            .emp-name { font-size: 14px; }
        }

        /* ========================================== */
        /* TABLET (768px+)                            */
        /* ========================================== */
        @media (min-width: 768px) {
            .emp-btn-primary {
                justify-content: center;
                width: auto;
            }

            .emp-filter-form {
                padding: 16px 20px;
            }
            .emp-search-row {
                flex-direction: row;
                align-items: center;
                flex-wrap: wrap;
            }
            .emp-search-input-wrap {
                min-width: 260px;
            }
            .emp-btn-search,
            .emp-btn-reset,
            .emp-btn-toggle-filter {
                flex-shrink: 0;
                width: auto;
            }

            .emp-filter-panel {
                margin: 16px -20px -16px;
                padding: 16px 20px;
            }
            .emp-filter-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 16px;
                align-items: end;
            }
            .emp-filter-toggles {
                grid-column: 1 / -1;
            }
            .emp-filter-actions {
                grid-column: 1 / -1;
                justify-content: flex-end;
            }

            .emp-card {
                padding: 18px 20px;
            }
            .emp-avatar {
                width: 48px;
                height: 48px;
                font-size: 18px;
            }
            .emp-info {
                gap: 5px;
            }
        }

        /* ========================================== */
        /* DESKTOP (1024px+) — 2 COLUMN GRID          */
        /* ========================================== */
        @media (min-width: 1024px) {
            .emp-list {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            .emp-empty {
                grid-column: 1 / -1;
            }

            .emp-card {
                flex-direction: column;
                align-items: stretch;
                gap: 14px;
                padding: 18px;
            }
            .emp-card-main {
                align-items: flex-start;
            }
            .emp-card-chips {
                flex-direction: row;
                max-width: none;
            }
            .emp-card-actions {
                flex-direction: row;
                align-items: center;
            }
            .emp-shift-select {
                flex: 1;
                min-width: 0;
            }
        }

        /* ========================================== */
        /* WIDE DESKTOP — keep 2-col grid comfortable  */
        /* ========================================== */
        @media (min-width: 1280px) {
            .emp-list {
                gap: 14px;
            }
            .emp-card {
                padding: 20px;
            }
        }
    </style>

    <script>
    function toggleFilterPanel() {
        const panel = document.getElementById('filterPanel');
        const btn = document.querySelector('.emp-btn-toggle-filter');

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
