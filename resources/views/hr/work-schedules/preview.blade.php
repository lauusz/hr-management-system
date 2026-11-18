<x-app title="Detail Jadwal Kerja">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy" aria-hidden="true">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Detail Jadwal Kerja</h1>
            </div>
        </div>
    </x-slot>

    @php
        $monthLabel = \Carbon\Carbon::parse($month . '-01')->locale('id')->translatedFormat('F Y');
        $isInactive = $aktif === 'nonaktif';
        $uniqueEmployees = $rows->pluck('user_id')->unique()->count();
        $uniqueDates = $rows->pluck('work_date')->map->toDateString()->unique()->count();
        $scopeQuery = ['month' => $month, 'group_id' => $groupId];
    @endphp

    @if(session('success'))
        <div class="lm-alert lm-alert--success alert-success" role="status">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div role="alert" class="lm-alert lm-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="10" stroke-width="2"/>
                <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" stroke-linecap="round"/>
                <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif
    @if(! $lifecycleReady)
        <div role="alert" class="lm-alert lm-alert--error">
            <span>Edit dan Nonaktifkan belum tersedia.</span>
        </div>
    @endif

    {{-- Top Navigation & Filter Bar --}}
    <div class="ws-top-bar">
        <a class="ws-back-btn" href="{{ route('hr.work-schedules.index', ['month' => $month]) }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali</span>
        </a>

        <div class="ws-top-right">
            <span class="ws-pattern-filter" role="group" aria-label="Filter status jadwal">
                <a class="ws-pattern-filter__item {{ $isInactive ? '' : 'is-current' }}" href="{{ route('hr.work-schedules.preview', $scopeQuery) }}">Aktif</a>
                <a class="ws-pattern-filter__item {{ $isInactive ? 'is-current' : '' }}" href="{{ route('hr.work-schedules.preview', $scopeQuery + ['aktif' => 'nonaktif']) }}">Nonaktif</a>
            </span>
        </div>
    </div>

    {{-- Main Group Card --}}
    <div class="ws-card">
        <div class="ws-card-header">
            <div class="ws-group-Summary">
                <div class="ws-group-title-row">
                    <h2 class="ws-card-title">{{ $groupName }}</h2>
                    <span class="ws-period-badge">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ $monthLabel }}
                    </span>
                    @if($isInactive)
                        <span class="lm-badge lm-badge--holiday">Nonaktif</span>
                    @endif
                </div>

                <div class="ws-quick-stats">
                    <span class="ws-stat-chip">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <strong>{{ $uniqueEmployees }}</strong> Karyawan
                    </span>
                    <span class="ws-stat-chip">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <strong>{{ $uniqueDates }}</strong> Hari
                    </span>
                    <span class="ws-stat-chip">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                        <strong>{{ count($blocks) }}</strong> Pola
                    </span>
                </div>
            </div>

            @if($lifecycleReady && ! $isInactive && $scopeCount > 0)
                <div class="ws-action-group">
                    <a class="ws-action-btn ws-action-btn--edit" href="{{ route('hr.work-schedules.edit', $scopeQuery) }}">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Edit Semua</span>
                    </a>
                    <button type="button" class="ws-action-btn ws-action-btn--danger" data-ws-deactivate
                        data-group-id="{{ $groupId }}" data-ids="" data-fingerprint="{{ $scopeFingerprint }}"
                        data-title="{{ $groupName }} · {{ $monthLabel }}"
                        data-employees="{{ $scopeEmployees }}" data-dates="{{ $scopeDates }}" data-records="{{ $scopeCount }}">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        <span>Nonaktifkan Semua</span>
                    </button>
                </div>
            @endif
        </div>

        <div class="ws-pattern-list">
            @forelse($blocks as $block)
                @php
                    $sample = $block['sample'];
                    $time = $sample->start_time ? substr($sample->start_time, 0, 5).'–'.substr($sample->end_time, 0, 5) : '—';
                @endphp
                <article class="ws-pattern-card {{ $isInactive ? 'is-inactive' : '' }}">
                    <div class="ws-pattern-body">
                        {{-- Row 1: Date & Shift Info --}}
                        <div class="ws-pattern-top">
                            <div class="ws-pattern-date-wrap">
                                <span class="ws-date-icon-box" aria-hidden="true">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <span class="ws-pattern-dates">{{ $block['label'] }}</span>
                                <span class="ws-days-count">{{ $block['date_count'] }} hari</span>
                            </div>

                            <div class="ws-pattern-shift">
                                <span class="ws-shift-pill">{{ $sample->shift_name }}</span>
                                @if(! $sample->is_holiday)
                                    <span class="ws-time-badge">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $time }} {{ $sample->is_wita ? 'WITA' : 'WIB' }}
                                    </span>
                                @endif
                                <span class="lm-badge {{ $sample->is_holiday ? 'lm-badge--holiday' : 'lm-badge--success' }}">
                                    {{ $sample->is_holiday ? 'Libur' : 'Kerja' }}{{ $sample->is_overnight ? ' · Lintas Hari' : '' }}
                                </span>
                            </div>
                        </div>

                        {{-- Row 2: Employee Chips --}}
                        <div class="ws-pattern-users" aria-label="Daftar karyawan">
                            @foreach($block['names'] as $empName)
                                <span class="ws-user-chip">
                                    <span class="ws-user-chip__avatar" aria-hidden="true">{{ strtoupper(substr($empName, 0, 1)) }}</span>
                                    <span class="ws-user-chip__name">{{ $empName }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    @if($lifecycleReady && ! $isInactive)
                        <div class="ws-action-group ws-pattern-actions">
                            <a class="ws-action-btn ws-action-btn--edit"
                                href="{{ route('hr.work-schedules.edit', $scopeQuery + ['ids' => implode(',', $block['ids'])]) }}"
                                aria-label="Edit Jadwal {{ $block['label'] }}">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span>Edit</span>
                            </a>
                            <button type="button" class="ws-action-btn ws-action-btn--danger" data-ws-deactivate
                                data-group-id="{{ $groupId }}" data-ids="{{ implode(',', $block['ids']) }}" data-fingerprint="{{ $block['fingerprint'] }}"
                                data-title="{{ $groupName }} · {{ $block['label'] }}"
                                data-employees="{{ count($block['names']) }}" data-dates="{{ $block['date_count'] }}" data-records="{{ $block['record_count'] }}"
                                aria-label="Nonaktifkan {{ $block['label'] }}">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                <span>Nonaktifkan</span>
                            </button>
                        </div>
                    @endif
                </article>
            @empty
                <div class="ws-empty-state">
                    {{ $isInactive ? 'Tidak ada jadwal nonaktif.' : 'Belum ada jadwal aktif.' }}
                </div>
            @endforelse
        </div>
    </div>

    @if($lifecycleReady)
        @include('hr.work-schedules._deactivate_dialog', ['month' => $month, 'return' => 'preview'])
    @endif

    <style>
        /* ========================================== */
        /* SECTION HEADER                             */
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
        .icon-navy {
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark, #0a3d62);
        }
        .section-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary, #0f172a);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }

        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .lm-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 14px;
            font-size: 0.8125rem;
            font-weight: 600;
        }
        .lm-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #15803d;
        }
        .lm-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        /* ========================================== */
        /* TOP BAR                                    */
        /* ========================================== */
        .ws-top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .ws-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 13px;
            border-radius: 9px;
            border: 1.5px solid var(--border, #cbd5e1);
            background: var(--white, #fff);
            color: var(--text-secondary, #334155);
            font-size: 0.8125rem;
            font-weight: 600;
            text-decoration: none;
            box-sizing: border-box;
            transition: all 0.15s ease;
        }
        .ws-back-btn:hover {
            border-color: var(--primary, #145da0);
            color: var(--primary, #145da0);
            background: var(--gray-50, #f8fafc);
        }
        .ws-top-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .ws-pattern-filter {
            display: inline-flex;
            background: #f1f5f9;
            padding: 3px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }
        .ws-pattern-filter__item {
            padding: 5px 14px;
            border-radius: 7px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #475569;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .ws-pattern-filter__item:hover:not(.is-current) {
            color: #0f172a;
        }
        .ws-pattern-filter__item.is-current {
            background: var(--primary, #145da0);
            color: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.12);
        }

        /* ========================================== */
        /* MAIN CARD & HEADER                         */
        /* ========================================== */
        .ws-card {
            background: var(--white, #fff);
            border: 1px solid var(--border-light, #e2e8f0);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .ws-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-light, #e2e8f0);
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.9), rgba(255, 255, 255, 1));
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }
        .ws-group-Summary {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 0;
        }
        .ws-group-title-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .ws-card-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--text-primary, #0f172a);
        }
        .ws-period-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary, #145da0);
            font-size: 0.75rem;
            font-weight: 700;
        }
        .ws-quick-stats {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .ws-stat-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .ws-stat-chip strong {
            color: #0f172a;
            font-weight: 800;
        }
        .ws-stat-chip svg {
            color: #64748b;
            flex-shrink: 0;
        }

        /* ========================================== */
        /* PATTERN LIST & ITEMS                       */
        /* ========================================== */
        .ws-pattern-list {
            display: flex;
            flex-direction: column;
            divide-y: 1px solid #f1f5f9;
        }
        .ws-pattern-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s ease;
        }
        .ws-pattern-card:last-child {
            border-bottom: none;
        }
        .ws-pattern-card:hover {
            background: #fafbfc;
        }
        .ws-pattern-card.is-inactive {
            background: #f8fafc;
            opacity: 0.82;
        }
        .ws-pattern-body {
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex: 1 1 360px;
            min-width: 0;
        }
        .ws-pattern-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px 16px;
            flex-wrap: wrap;
        }
        .ws-pattern-date-wrap {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .ws-date-icon-box {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary, #145da0);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .ws-pattern-dates {
            font-size: 0.9rem;
            font-weight: 800;
            color: #0f172a;
        }
        .ws-days-count {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #475569;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .ws-pattern-shift {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }
        .ws-shift-pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 8px;
            background: rgba(20, 93, 160, 0.09);
            color: var(--primary, #145da0);
            font-size: 0.75rem;
            font-weight: 700;
        }
        .ws-time-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 9px;
            border-radius: 8px;
            background: #f1f5f9;
            color: #0f172a;
        }
        .lm-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .lm-badge--success {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
        }
        .lm-badge--holiday {
            background: rgba(245, 158, 11, 0.14);
            color: #b45309;
        }

        /* Employee Chips */
        .ws-pattern-users {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }
        .ws-user-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px 4px 5px;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 0.76rem;
            font-weight: 600;
            color: #1e293b;
        }
        .ws-user-chip__avatar {
            width: 20px;
            height: 20px;
            border-radius: 999px;
            background: rgba(20, 93, 160, 0.12);
            color: var(--primary, #145da0);
            font-size: 0.65rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .ws-user-chip__name {
            line-height: 1.2;
        }

        /* ========================================== */
        /* ACTION BUTTONS                             */
        /* ========================================== */
        .ws-action-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .ws-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            min-height: 34px;
            padding: 6px 12px;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.76rem;
            font-weight: 600;
            line-height: 1.35;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            box-sizing: border-box;
            border: 1.5px solid transparent;
            transition: all 0.15s ease;
        }
        .ws-action-btn svg {
            flex-shrink: 0;
        }
        .ws-action-btn--edit {
            background: var(--white, #fff);
            border-color: var(--border, #cbd5e1);
            color: var(--text-secondary, #334155);
        }
        .ws-action-btn--edit:hover {
            background: var(--gray-50, #f8fafc);
            border-color: var(--primary, #145da0);
            color: var(--primary, #145da0);
        }
        .ws-action-btn--danger {
            background: #fef2f2;
            border-color: #fecaca;
            color: #dc2626;
        }
        .ws-action-btn--danger:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #b91c1c;
        }

        /* Dialog buttons (used by _deactivate_dialog) */
        .ws-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 38px;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.8125rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.18s ease;
            border: none;
            text-decoration: none;
            white-space: nowrap;
            box-sizing: border-box;
        }
        .ws-btn--outline {
            background: var(--white, #fff);
            color: var(--text-secondary, #334155);
            border: 1.5px solid var(--border, #cbd5e1);
        }
        .ws-btn--outline:hover {
            border-color: var(--primary, #145da0);
            color: var(--primary, #145da0);
        }
        .ws-btn--submit {
            background: #dc2626;
            color: #fff;
        }
        .ws-btn--submit:hover {
            background: #b91c1c;
        }

        .ws-empty-state {
            padding: 36px 20px;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted, #64748b);
        }

        @media (min-width: 768px) {
            .section-icon {
                width: 40px;
                height: 40px;
                border-radius: 12px;
            }
            .section-title {
                font-size: 1.2rem;
            }
        }
    </style>
</x-app>
