<x-app title="Presensi Hari Ini">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon" aria-hidden="true">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Presensi Hari Ini</h1>
                <p class="section-subtitle">Catat dan pantau kehadiran harian Anda</p>
            </div>
        </div>
    </x-slot>

    @php
        $hasTodayClockIn = (bool) ($attendance?->clock_in_at);
        $hasTodayClockOut = (bool) ($attendance?->clock_out_at);
        $hasActiveAttendance = (bool) $activeAttendance;
        $canClockIn = ! $hasTodayClockIn && ! $hasActiveAttendance;
        $canClockOut = $hasActiveAttendance && ! $activeAttendance->clock_out_at;
    @endphp

    <div class="attendance-page">
        <div class="attendance-content">
            @if($previousIncompleteAttendance)
                <section class="attendance-warning" aria-labelledby="previous-attendance-title">
                    <div class="attendance-warning__heading">
                        <span class="attendance-warning__icon" aria-hidden="true">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </span>
                        <h2 id="previous-attendance-title">Absensi sebelumnya belum selesai</h2>
                        <span class="attendance-warning__badge">
                            {{ $previousIncompleteAttendance->completion_status === 'MISSED_CLOCK_OUT' ? 'Terlewat' : 'Berjalan' }}
                        </span>
                    </div>
                    <div class="attendance-warning__details">
                        <span>{{ $previousIncompleteAttendance->date->translatedFormat('d M Y') }}</span>
                        <span>Masuk {{ $previousIncompleteAttendance->clock_in_at->format('H:i') }}</span>
                        <span>{{ $previousIncompleteAttendance->completion_status_label }}</span>
                    </div>
                </section>
            @endif

            <section class="attendance-status-panel" aria-labelledby="attendance-status-title">
                <div class="attendance-status-panel__header">
                    <div class="attendance-status-panel__heading">
                        <span class="attendance-status-panel__heading-icon" aria-hidden="true">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <h2 id="attendance-status-title">Status Kehadiran</h2>
                    </div>

                    @if($hasActiveAttendance)
                        <span class="attendance-status-badge attendance-status-badge--active">Berjalan</span>
                    @elseif($hasTodayClockOut)
                        <span class="attendance-status-badge attendance-status-badge--complete">Selesai</span>
                    @else
                        <span class="attendance-status-badge">Belum Presensi</span>
                    @endif
                </div>

                <div class="attendance-status-panel__body">
                    <div class="attendance-status-panel__clock" aria-hidden="true">
                        @if($hasTodayClockOut)
                            <svg width="34" height="34" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @else
                            <svg width="34" height="34" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                    </div>

                    @if($hasActiveAttendance)
                        <p class="attendance-status-panel__title">Sedang bekerja</p>
                        <p class="attendance-status-panel__description">
                            Clock in tercatat pukul {{ $activeAttendance->clock_in_at->format('H:i') }}.
                        </p>
                        <div class="attendance-time-summary">
                            <div>
                                <span>Jam masuk</span>
                                <strong>{{ $activeAttendance->clock_in_at->format('H:i') }}</strong>
                            </div>
                            <div>
                                <span>Jam pulang</span>
                                <strong>--:--</strong>
                            </div>
                        </div>
                    @elseif($hasTodayClockOut)
                        <p class="attendance-status-panel__title">Presensi selesai</p>
                        <p class="attendance-status-panel__description">Kehadiran hari ini sudah tercatat lengkap.</p>
                        <div class="attendance-time-summary">
                            <div>
                                <span>Jam masuk</span>
                                <strong>{{ $attendance->clock_in_at->format('H:i') }}</strong>
                            </div>
                            <div>
                                <span>Jam pulang</span>
                                <strong>{{ $attendance->clock_out_at->format('H:i') }}</strong>
                            </div>
                        </div>
                    @else
                        <p class="attendance-status-panel__title">Belum presensi</p>
                        <p class="attendance-status-panel__description">Silakan clock in saat tiba di lokasi kerja.</p>
                    @endif
                </div>
            </section>
        </div>

        <nav class="attendance-action-dock" aria-label="Aksi presensi">
            <div class="attendance-action-dock__inner">
                @if($canClockIn)
                    <a href="{{ route('attendance.clockIn.form') }}" class="attendance-action attendance-action--primary">
                @else
                    <span class="attendance-action attendance-action--disabled" aria-disabled="true">
                @endif
                    <span class="attendance-action__icon" aria-hidden="true">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                    </span>
                    <span class="attendance-action__copy">
                        <strong>Clock In</strong>
                        <small>
                            @if($hasTodayClockIn)
                                Sudah tercatat {{ $attendance->clock_in_at->format('H:i') }}
                            @elseif($hasActiveAttendance)
                                Sudah tercatat {{ $activeAttendance->clock_in_at->format('H:i') }}
                            @else
                                Catat jam masuk kerja
                            @endif
                        </small>
                    </span>
                    <svg class="attendance-action__arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                @if($canClockIn)
                    </a>
                @else
                    </span>
                @endif

                @if($canClockOut)
                    <a href="{{ route('attendance.clockOut.form') }}" class="attendance-action attendance-action--primary">
                @else
                    <span class="attendance-action attendance-action--disabled" aria-disabled="true">
                @endif
                    <span class="attendance-action__icon" aria-hidden="true">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </span>
                    <span class="attendance-action__copy">
                        <strong>Clock Out</strong>
                        <small>
                            @if($hasTodayClockOut)
                                Sudah tercatat {{ $attendance->clock_out_at->format('H:i') }}
                            @elseif($canClockOut)
                                Catat jam pulang kerja
                            @else
                                Tersedia setelah clock in
                            @endif
                        </small>
                    </span>
                    <svg class="attendance-action__arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                @if($canClockOut)
                    </a>
                @else
                    </span>
                @endif
            </div>
        </nav>
    </div>

    <style>
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-icon {
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 9px;
            background: #eef3f8;
            color: var(--primary-dark);
        }

        .section-title {
            margin: 0;
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.25;
        }

        .section-subtitle {
            margin: 2px 0 0;
            color: var(--text-muted);
            font-size: .75rem;
            line-height: 1.35;
        }

        .attendance-page {
            width: 100%;
            max-width: 860px;
            margin: 0 auto;
        }

        .attendance-content {
            display: grid;
            gap: 16px;
        }

        .attendance-warning,
        .attendance-status-panel,
        .attendance-action-dock {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 16px;
        }

        .attendance-warning {
            padding: 18px 20px;
            border-color: #f4cf99;
            background: #fffdfa;
        }

        .attendance-warning__heading {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .attendance-warning__icon {
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            color: #d97706;
        }

        .attendance-warning h2 {
            flex: 1;
            margin: 0;
            color: var(--text-primary);
            font-size: .9375rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .attendance-warning__badge,
        .attendance-status-badge {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: .6875rem;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
        }

        .attendance-warning__badge {
            background: #fff0e8;
            color: #c2410c;
        }

        .attendance-warning__details {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 18px;
            margin: 12px 0 0 32px;
            color: var(--text-muted);
            font-size: .75rem;
            font-weight: 500;
        }

        .attendance-warning__details span:not(:last-child)::after {
            content: '·';
            margin-left: 18px;
            color: #cbd5e1;
        }

        .attendance-status-panel {
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .03);
        }

        .attendance-status-panel__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 17px 20px;
            border-bottom: 1px solid var(--border-light);
        }

        .attendance-status-panel__heading {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .attendance-status-panel__heading-icon {
            display: grid;
            place-items: center;
            color: var(--primary);
        }

        .attendance-status-panel__heading h2 {
            margin: 0;
            color: var(--text-primary);
            font-size: .9375rem;
            font-weight: 700;
        }

        .attendance-status-badge {
            background: #f6f3ec;
            color: #9a6700;
        }

        .attendance-status-badge--active {
            background: #eef4fb;
            color: var(--primary-dark);
        }

        .attendance-status-badge--complete {
            background: #eef4f1;
            color: #27704a;
        }

        .attendance-status-panel__body {
            min-height: 320px;
            padding: 42px 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .attendance-status-panel__clock {
            width: 68px;
            height: 68px;
            display: grid;
            place-items: center;
            margin-bottom: 22px;
            border-radius: 50%;
            background: #f7f9fb;
            color: var(--primary-dark);
        }

        .attendance-status-panel__title {
            margin: 0;
            color: var(--text-primary);
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .attendance-status-panel__description {
            max-width: 430px;
            margin: 10px 0 0;
            color: var(--text-muted);
            font-size: .875rem;
            line-height: 1.6;
        }

        .attendance-time-summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(110px, 1fr));
            width: min(100%, 360px);
            margin-top: 24px;
            border-top: 1px solid var(--border-light);
        }

        .attendance-time-summary div {
            display: grid;
            gap: 5px;
            padding: 18px 16px 0;
        }

        .attendance-time-summary div + div {
            border-left: 1px solid var(--border-light);
        }

        .attendance-time-summary span {
            color: var(--text-muted);
            font-size: .6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .attendance-time-summary strong {
            color: var(--text-primary);
            font-size: 1.25rem;
            font-weight: 700;
        }

        .attendance-action-dock {
            margin-top: 20px;
            padding: 14px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
        }

        .attendance-action-dock__inner {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .attendance-action {
            min-height: 72px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border: 1px solid transparent;
            border-radius: 13px;
            text-decoration: none;
            transition: transform .15s ease, background-color .15s ease, border-color .15s ease;
        }

        .attendance-action--primary {
            background: var(--primary-dark);
            color: var(--white);
            box-shadow: 0 4px 12px rgba(10, 61, 98, .16);
        }

        .attendance-action--primary:hover {
            background: #08334f;
            transform: translateY(-1px);
        }

        .attendance-action--primary:focus-visible {
            outline: 3px solid rgba(20, 93, 160, .3);
            outline-offset: 3px;
        }

        .attendance-action--disabled {
            border-color: var(--border-light);
            background: #f8fafc;
            color: #aab3bf;
            cursor: not-allowed;
        }

        .attendance-action__icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 10px;
            background: rgba(255, 255, 255, .12);
        }

        .attendance-action--disabled .attendance-action__icon {
            background: #f0f3f6;
        }

        .attendance-action__copy {
            display: grid;
            gap: 2px;
            min-width: 0;
        }

        .attendance-action__copy strong {
            font-size: .9375rem;
            font-weight: 700;
            line-height: 1.25;
        }

        .attendance-action__copy small {
            font-size: .71875rem;
            font-weight: 500;
            line-height: 1.35;
        }

        .attendance-action__arrow {
            flex: 0 0 auto;
            margin-left: auto;
            opacity: .72;
        }

        .attendance-action--disabled .attendance-action__arrow {
            opacity: .45;
        }

        @media (max-width: 767px) {
            .attendance-page {
                padding-bottom: calc(210px + env(safe-area-inset-bottom));
            }

            .attendance-warning {
                padding: 16px;
            }

            .attendance-warning__heading {
                align-items: flex-start;
                gap: 10px;
            }

            .attendance-warning h2 {
                font-size: .875rem;
            }

            .attendance-warning__badge {
                min-height: 26px;
                padding-inline: 9px;
                font-size: .625rem;
            }

            .attendance-warning__details {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
                margin: 12px 0 0 30px;
            }

            .attendance-warning__details span:not(:last-child)::after {
                content: none;
            }

            .attendance-status-panel__header {
                padding: 15px 16px;
            }

            .attendance-status-panel__body {
                min-height: 290px;
                padding: 34px 20px;
            }

            .attendance-status-panel__clock {
                width: 60px;
                height: 60px;
                margin-bottom: 18px;
            }

            .attendance-status-panel__title {
                font-size: 1.0625rem;
            }

            .attendance-status-panel__description {
                font-size: .8125rem;
            }

            .attendance-time-summary {
                margin-top: 20px;
            }

            .attendance-action-dock {
                position: fixed;
                right: 0;
                bottom: 0;
                left: 0;
                z-index: 40;
                margin: 0;
                padding: 20px 24px max(24px, env(safe-area-inset-bottom));
                border-width: 1px 0 0;
                border-radius: 24px 24px 0 0;
                background: rgba(255, 255, 255, .98);
                box-shadow: 0 -8px 24px rgba(15, 23, 42, .08);
            }

            .attendance-action-dock__inner {
                grid-template-columns: 1fr;
                gap: 16px;
                max-width: 520px;
                margin: 0 auto;
            }

            .attendance-action {
                position: relative;
                min-height: 64px;
                justify-content: center;
                padding: 10px 72px;
            }

            .attendance-action__icon {
                position: absolute;
                left: 16px;
            }

            .attendance-action__copy small,
            .attendance-action__arrow {
                display: none;
            }
        }

        @media (max-width: 359px) {
            .section-subtitle {
                display: none;
            }

            .attendance-warning__heading {
                flex-wrap: wrap;
            }

            .attendance-warning__badge {
                margin-left: 30px;
            }

            .attendance-status-badge {
                padding-inline: 8px;
            }
        }

        @media (min-width: 768px) {
            .section-title {
                font-size: 1.125rem;
            }

            .section-subtitle {
                font-size: .8125rem;
            }

            .attendance-status-panel__body {
                min-height: 360px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .attendance-action {
                transition: none;
            }
        }
    </style>
</x-app>
