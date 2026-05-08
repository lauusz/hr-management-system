<x-app title="Presensi Hari Ini">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
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

    <div class="attendance-page">

        {{-- ============================================ --}}
        {{-- GREETING HERO                                --}}
        {{-- ============================================ --}}
        <div class="attendance-hero">
            <div class="attendance-hero__content">
                @php
                    $hour = now()->hour;
                    $greeting = $hour < 12 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
                @endphp
                <p class="attendance-hero__greeting">{{ $greeting }}, <span class="attendance-hero__name">{{ auth()->user()->name }}</span></p>
                <p class="attendance-hero__hint">
                    @if($attendance && $attendance->clock_out_at)
                        Presensi hari ini telah selesai. Semoga harimu menyenangkan!
                    @elseif($attendance && $attendance->clock_in_at)
                        Kamu sudah clock in. Jangan lupa clock out saat pulang nanti.
                    @else
                        Jangan lupa catat kehadiranmu hari ini.
                    @endif
                </p>
            </div>
            <div class="attendance-hero__icon">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- STATUS CARD                                  --}}
        {{-- ============================================ --}}
        <div class="attendance-card">
            <div class="attendance-card__header">
                <div class="attendance-card__header-left">
                    <div class="attendance-card__header-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="attendance-card__title">Status Kehadiran</h2>
                </div>
                @if($attendance)
                    <span class="attendance-badge {{ $attendance->status === 'TERLAMBAT' ? 'attendance-badge--error' : 'attendance-badge--success' }}">
                        {{ $attendance->status === 'HADIR' ? 'Hadir' : ($attendance->status === 'TERLAMBAT' ? 'Terlambat' : $attendance->status) }}
                    </span>
                @else
                    <span class="attendance-badge attendance-badge--warning">Belum Presensi</span>
                @endif
            </div>

            <div class="attendance-card__body">
                @if(!$attendance)
                    <div class="attendance-empty">
                        <div class="attendance-empty__icon">
                            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="attendance-empty__title">Belum Ada Presensi</p>
                        <p class="attendance-empty__desc">Anda belum melakukan clock-in hari ini. Tap tombol Clock In di bawah untuk memulai.</p>
                    </div>
                @else
                    <div class="attendance-summary">
                        <div class="attendance-summary__item">
                            <span class="attendance-summary__label">Jam Masuk</span>
                            <span class="attendance-summary__value {{ $attendance->clock_in_at ? 'attendance-summary__value--primary' : 'attendance-summary__value--muted' }}">
                                {{ $attendance->clock_in_at ? $attendance->clock_in_at->format('H:i') : '--:--' }}
                            </span>
                        </div>
                        <div class="attendance-summary__divider"></div>
                        <div class="attendance-summary__item">
                            <span class="attendance-summary__label">Jam Pulang</span>
                            <span class="attendance-summary__value {{ $attendance->clock_out_at ? 'attendance-summary__value--primary' : 'attendance-summary__value--muted' }}">
                                {{ $attendance->clock_out_at ? $attendance->clock_out_at->format('H:i') : '--:--' }}
                            </span>
                        </div>
                    </div>

                    @if($attendance->late_minutes > 0)
                        @php
                            $m = $attendance->late_minutes;
                            $hours = intdiv($m, 60);
                            $minutes = $m % 60;
                            $lateLabel = ($hours > 0 ? $hours.'j ' : '') . $minutes.'m';
                        @endphp
                        <div class="attendance-extra attendance-extra--error">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Keterlambatan <strong>{{ $lateLabel }}</strong></span>
                        </div>
                    @endif

                    @if(($attendance->early_leave_minutes ?? 0) > 0)
                        @php
                            $m = $attendance->early_leave_minutes;
                            $hours = intdiv($m, 60);
                            $minutes = $m % 60;
                            $earlyLabel = ($hours > 0 ? $hours.'j ' : '') . $minutes.'m';
                        @endphp
                        <div class="attendance-extra attendance-extra--warning">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Pulang Awal <strong>{{ $earlyLabel }}</strong></span>
                        </div>
                    @endif

                    @if(($attendance->overtime_minutes ?? 0) > 0)
                        @php
                            $m = $attendance->overtime_minutes;
                            $hours = intdiv($m, 60);
                            $minutes = $m % 60;
                            $otLabel = ($hours > 0 ? $hours.'j ' : '') . $minutes.'m';
                        @endphp
                        <div class="attendance-extra attendance-extra--info">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span>Lembur <strong>+ {{ $otLabel }}</strong></span>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- PRIMARY ACTIONS                              --}}
        {{-- ============================================ --}}
        <div class="attendance-actions">
            <a href="{{ route('attendance.clockIn.form') }}" class="attendance-action {{ $attendance && $attendance->clock_in_at ? 'attendance-action--done' : 'attendance-action--in' }}">
                <div class="attendance-action__icon">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <div class="attendance-action__text">
                    <span class="attendance-action__title">Clock In</span>
                    <span class="attendance-action__desc">
                        @if($attendance && $attendance->clock_in_at)
                            Sudah tercatat {{ $attendance->clock_in_at->format('H:i') }}
                        @else
                            Catat jam masuk kerja
                        @endif
                    </span>
                </div>
                <div class="attendance-action__arrow">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            <a href="{{ route('attendance.clockOut.form') }}"
               class="attendance-action {{ (!$attendance || !$attendance->clock_in_at || $attendance->clock_out_at) ? 'attendance-action--disabled' : 'attendance-action--out' }}">
                <div class="attendance-action__icon">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <div class="attendance-action__text">
                    <span class="attendance-action__title">Clock Out</span>
                    <span class="attendance-action__desc">
                        @if(!$attendance || !$attendance->clock_in_at)
                            Tersedia setelah clock in
                        @elseif($attendance->clock_out_at)
                            Sudah tercatat {{ $attendance->clock_out_at->format('H:i') }}
                        @else
                            Catat jam pulang kerja
                        @endif
                    </span>
                </div>
                <div class="attendance-action__arrow">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        </div>

        {{-- ============================================ --}}
        {{-- RINGKASAN HARI INI                           --}}
        {{-- ============================================ --}}
        <div class="attendance-card attendance-card--subtle">
            <div class="attendance-card__header">
                <div class="attendance-card__header-left">
                    <div class="attendance-card__header-icon attendance-card__header-icon--muted">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <h2 class="attendance-card__title">Ringkasan Hari Ini</h2>
                </div>
            </div>
            <div class="attendance-card__body">
                <div class="attendance-detail-grid">
                    <div class="attendance-detail">
                        <span class="attendance-detail__label">Jadwal Shift</span>
                        <span class="attendance-detail__value">
                            @if($attendance && ($attendance->normal_start_time || $attendance->normal_end_time))
                                {{ $attendance->normal_start_time ? $attendance->normal_start_time->format('H:i') : '-' }} - {{ $attendance->normal_end_time ? $attendance->normal_end_time->format('H:i') : '-' }}
                            @else
                                <span class="attendance-detail__empty">Belum ada data</span>
                            @endif
                        </span>
                    </div>
                    <div class="attendance-detail">
                        <span class="attendance-detail__label">Status</span>
                        <span class="attendance-detail__value">
                            @if($attendance)
                                <span class="attendance-detail__status {{ $attendance->clock_out_at ? 'attendance-detail__status--success' : 'attendance-detail__status--warning' }}">
                                    {{ $attendance->clock_out_at ? 'Selesai' : 'Berlangsung' }}
                                </span>
                            @else
                                <span class="attendance-detail__empty">Menunggu presensi</span>
                            @endif
                        </span>
                    </div>
                    <div class="attendance-detail">
                        <span class="attendance-detail__label">Lokasi</span>
                        <span class="attendance-detail__value">
                            @if($attendance && $attendance->location)
                                {{ $attendance->location->name }}
                            @elseif($attendance && $attendance->type === 'DINAS_LUAR')
                                Dinas Luar
                            @else
                                <span class="attendance-detail__empty">Belum ditentukan</span>
                            @endif
                        </span>
                    </div>
                    <div class="attendance-detail">
                        <span class="attendance-detail__label">Tipe</span>
                        <span class="attendance-detail__value">
                            @if($attendance)
                                {{ $attendance->type === 'DINAS_LUAR' ? 'Dinas Luar' : 'WFO' }}
                            @else
                                <span class="attendance-detail__empty">-</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- PANDUAN SINGKAT                              --}}
        {{-- ============================================ --}}
        <div class="attendance-guide">
            <div class="attendance-guide__header">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Panduan Singkat</span>
            </div>
            <ul class="attendance-guide__list">
                <li>Lakukan <strong>Clock In</strong> saat tiba di lokasi kerja.</li>
                <li>Lakukan <strong>Clock Out</strong> saat selesai bekerja.</li>
                <li>Pastikan lokasi dan foto wajah sesuai ketentuan.</li>
            </ul>
        </div>

    </div>

    <style>
        /* ============================================= */
        /* HEADER SLOT (x-slot)                          */
        /* ============================================= */
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
            color: var(--text-primary, #111827);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy  { background: rgba(10, 61, 98, 0.08);  color: var(--primary-dark, #0A3D62); }

        /* ============================================= */
        /* PAGE CONTAINER                                */
        /* ============================================= */
        .attendance-page {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding-bottom: 24px;
        }

        /* ============================================= */
        /* HERO                                          */
        /* ============================================= */
        .attendance-hero {
            position: relative;
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62) 0%, var(--primary, #145DA0) 60%, var(--primary-light, #1E81B0) 100%);
            border-radius: var(--radius-xl, 16px);
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.10);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px;
        }

        .attendance-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 60% at 90% 10%, rgba(212, 175, 55, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .attendance-hero__content {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
        }

        .attendance-hero__greeting {
            font-size: 0.9375rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.35;
            letter-spacing: -0.01em;
            margin: 0;
        }

        .attendance-hero__name {
            font-weight: 800;
        }

        .attendance-hero__hint {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.82);
            font-weight: 500;
            line-height: 1.4;
            margin: 0;
        }

        .attendance-hero__icon {
            position: relative;
            z-index: 1;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.9);
            flex-shrink: 0;
        }

        /* ============================================= */
        /* CARD                                          */
        /* ============================================= */
        .attendance-card {
            background: var(--white, #fff);
            border-radius: var(--radius-xl, 16px);
            border: 1px solid var(--border, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            overflow: hidden;
        }

        .attendance-card--subtle {
            background: linear-gradient(180deg, var(--gray-100, #F8FAFC) 0%, var(--white, #fff) 100%);
        }

        .attendance-card__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light, #F3F4F6);
        }

        .attendance-card__header-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .attendance-card__header-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: rgba(34, 197, 94, 0.1);
            color: var(--success, #22C55E);
        }

        .attendance-card__header-icon--muted {
            background: rgba(107, 114, 128, 0.08);
            color: var(--text-muted, #6B7280);
        }

        .attendance-card__title {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            line-height: 1.3;
            margin: 0;
        }

        .attendance-card__body {
            padding: 16px;
        }

        /* ============================================= */
        /* BADGES                                        */
        /* ============================================= */
        .attendance-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            flex-shrink: 0;
            white-space: nowrap;
        }

        .attendance-badge--success {
            background: rgba(34, 197, 94, 0.1);
            color: #15803d;
        }

        .attendance-badge--warning {
            background: rgba(245, 158, 11, 0.1);
            color: #a16207;
        }

        .attendance-badge--error {
            background: rgba(239, 68, 68, 0.1);
            color: #b91c1c;
        }

        /* ============================================= */
        /* EMPTY STATE                                   */
        /* ============================================= */
        .attendance-empty {
            text-align: center;
            padding: 24px 16px;
        }

        .attendance-empty__icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 12px;
            background: var(--gray-100, #F8FAFC);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--warning, #F59E0B);
        }

        .attendance-empty__title {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            margin: 0 0 4px;
        }

        .attendance-empty__desc {
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            margin: 0 auto;
            max-width: 280px;
            line-height: 1.5;
        }

        /* ============================================= */
        /* SUMMARY (IN CARD)                             */
        /* ============================================= */
        .attendance-summary {
            display: flex;
            align-items: center;
            gap: 1px;
            background: var(--border-light, #F3F4F6);
            border-radius: var(--radius-lg, 12px);
            overflow: hidden;
            margin-bottom: 12px;
        }

        .attendance-summary__item {
            flex: 1;
            background: var(--white, #fff);
            padding: 14px 8px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            min-width: 0;
        }

        .attendance-summary__divider {
            width: 1px;
            align-self: stretch;
            background: var(--border-light, #F3F4F6);
            flex-shrink: 0;
        }

        .attendance-summary__label {
            font-size: 0.625rem;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            line-height: 1.2;
        }

        .attendance-summary__value {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-primary, #111827);
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        .attendance-summary__value--primary {
            color: var(--primary, #145DA0);
        }

        .attendance-summary__value--muted {
            color: var(--gray-300, #D1D5DB);
        }

        /* ============================================= */
        /* EXTRA INFO (LATE / EARLY / OT)                */
        /* ============================================= */
        .attendance-extra {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: var(--radius-lg, 12px);
            font-size: 0.8125rem;
            font-weight: 500;
            line-height: 1.4;
        }

        .attendance-extra + .attendance-extra {
            margin-top: 8px;
        }

        .attendance-extra svg {
            flex-shrink: 0;
        }

        .attendance-extra--error {
            background: rgba(239, 68, 68, 0.06);
            color: var(--error, #EF4444);
            border: 1px solid rgba(239, 68, 68, 0.12);
        }

        .attendance-extra--warning {
            background: rgba(245, 158, 11, 0.06);
            color: var(--warning, #F59E0B);
            border: 1px solid rgba(245, 158, 11, 0.12);
        }

        .attendance-extra--info {
            background: rgba(59, 130, 246, 0.06);
            color: var(--info, #3B82F6);
            border: 1px solid rgba(59, 130, 246, 0.12);
        }

        .attendance-extra strong {
            font-weight: 700;
        }

        /* ============================================= */
        /* ACTION BUTTONS                                */
        /* ============================================= */
        .attendance-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .attendance-action {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            border-radius: var(--radius-xl, 16px);
            text-decoration: none;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
            border: 1.5px solid transparent;
        }

        .attendance-action__icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .attendance-action__text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
            flex: 1;
        }

        .attendance-action__title {
            font-size: 0.9375rem;
            font-weight: 700;
            line-height: 1.25;
        }

        .attendance-action__desc {
            font-size: 0.8125rem;
            font-weight: 500;
            line-height: 1.35;
        }

        .attendance-action__arrow {
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }

        /* Clock In — Active */
        .attendance-action--in {
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #fff;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
        }

        .attendance-action--in .attendance-action__icon {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }

        .attendance-action--in .attendance-action__desc {
            color: rgba(255,255,255,0.8);
        }

        .attendance-action--in .attendance-action__arrow {
            color: rgba(255,255,255,0.7);
        }

        .attendance-action--in:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }

        .attendance-action--in:hover .attendance-action__arrow {
            color: #fff;
            transform: translateX(2px);
        }

        /* Clock In — Done */
        .attendance-action--done {
            background: var(--white, #fff);
            border-color: var(--border, #E5E7EB);
            color: var(--text-primary, #111827);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .attendance-action--done .attendance-action__icon {
            background: rgba(34, 197, 94, 0.1);
            color: var(--success, #22C55E);
        }

        .attendance-action--done .attendance-action__desc {
            color: var(--success, #22C55E);
        }

        .attendance-action--done .attendance-action__arrow {
            color: var(--gray-300, #D1D5DB);
        }

        .attendance-action--done:hover {
            border-color: rgba(34, 197, 94, 0.3);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.08);
        }

        /* Clock Out — Active */
        .attendance-action--out {
            background: var(--white, #fff);
            border-color: var(--border, #E5E7EB);
            color: var(--text-primary, #111827);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .attendance-action--out .attendance-action__icon {
            background: rgba(239, 68, 68, 0.08);
            color: var(--error, #EF4444);
        }

        .attendance-action--out .attendance-action__desc {
            color: var(--text-muted, #6B7280);
        }

        .attendance-action--out .attendance-action__arrow {
            color: var(--gray-300, #D1D5DB);
            transition: all 0.2s ease;
        }

        .attendance-action--out:hover {
            border-color: rgba(239, 68, 68, 0.25);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.08);
            transform: translateY(-1px);
        }

        .attendance-action--out:hover .attendance-action__arrow {
            color: var(--error, #EF4444);
            transform: translateX(2px);
        }

        /* Clock Out — Disabled */
        .attendance-action--disabled {
            background: var(--gray-50, #F5F7FA);
            border-color: var(--border, #E5E7EB);
            color: var(--text-muted, #6B7280);
            pointer-events: none;
            opacity: 0.85;
        }

        .attendance-action--disabled .attendance-action__icon {
            background: var(--gray-100, #F8FAFC);
            color: var(--gray-300, #D1D5DB);
        }

        .attendance-action--disabled .attendance-action__title {
            color: var(--gray-400, #9CA3AF);
        }

        .attendance-action--disabled .attendance-action__desc {
            color: var(--gray-400, #9CA3AF);
        }

        .attendance-action--disabled .attendance-action__arrow {
            color: var(--gray-300, #D1D5DB);
        }

        /* ============================================= */
        /* DETAIL GRID (RINGKASAN)                       */
        /* ============================================= */
        .attendance-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px 12px;
        }

        .attendance-detail {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .attendance-detail__label {
            font-size: 0.625rem;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            line-height: 1.2;
        }

        .attendance-detail__value {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
            line-height: 1.35;
            word-break: break-word;
        }

        .attendance-detail__empty {
            color: var(--gray-400, #9CA3AF);
            font-weight: 500;
        }

        .attendance-detail__status {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .attendance-detail__status--success {
            background: rgba(34, 197, 94, 0.1);
            color: #15803d;
        }

        .attendance-detail__status--warning {
            background: rgba(245, 158, 11, 0.1);
            color: #a16207;
        }

        /* ============================================= */
        /* GUIDE / PANDUAN                               */
        /* ============================================= */
        .attendance-guide {
            background: var(--white, #fff);
            border: 1px solid var(--border, #E5E7EB);
            border-radius: var(--radius-xl, 16px);
            padding: 14px 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .attendance-guide__header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            margin-bottom: 10px;
        }

        .attendance-guide__header svg {
            flex-shrink: 0;
            color: var(--primary, #145DA0);
        }

        .attendance-guide__list {
            margin: 0;
            padding: 0 0 0 18px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .attendance-guide__list li {
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
            line-height: 1.5;
            font-weight: 500;
        }

        .attendance-guide__list li strong {
            color: var(--text-primary, #111827);
            font-weight: 600;
        }

        /* ============================================= */
        /* TABLET & DESKTOP (768px+)                     */
        /* ============================================= */
        @media (min-width: 768px) {
            .attendance-page {
                gap: 16px;
                max-width: 720px;
                margin: 0 auto;
            }

            .attendance-hero {
                padding: 20px 24px;
                border-radius: var(--radius-2xl, 20px);
            }

            .attendance-hero__greeting {
                font-size: 1.125rem;
            }

            .attendance-hero__hint {
                font-size: 0.8125rem;
            }

            .attendance-hero__icon {
                width: 52px;
                height: 52px;
            }

            .attendance-hero__icon svg {
                width: 28px;
                height: 28px;
            }

            .attendance-card__header {
                padding: 16px 20px;
            }

            .attendance-card__body {
                padding: 20px;
            }

            .attendance-empty {
                padding: 32px 20px;
            }

            .attendance-empty__icon {
                width: 64px;
                height: 64px;
            }

            .attendance-summary__value {
                font-size: 1.5rem;
            }

            .attendance-actions {
                flex-direction: row;
                gap: 12px;
            }

            .attendance-action {
                flex: 1;
                padding: 18px;
            }

            .attendance-action__icon {
                width: 48px;
                height: 48px;
            }

            .attendance-action__title {
                font-size: 1rem;
            }

            .attendance-detail-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 16px;
            }

            .attendance-guide {
                padding: 16px 20px;
            }

            .attendance-guide__list li {
                font-size: 0.8125rem;
            }
        }
    </style>

</x-app>
