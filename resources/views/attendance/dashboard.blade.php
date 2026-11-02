<x-app title="Presensi Hari Ini">
    <x-slot name="header">
        <div class="attendance-header-bar">
            <div class="section-header-inline">
                <div class="section-icon" aria-hidden="true">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="section-title">Presensi Hari Ini</h1>
                    <p class="section-subtitle">Catat dan pantau kehadiran harian Anda</p>
                </div>
            </div>
            <a href="{{ route('attendance.history') }}" class="attendance-history-link" title="Buka Riwayat Absensi">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Riwayat Absensi</span>
                <svg class="attendance-history-link__arrow" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </x-slot>

    @php
        $events = $events ?? collect();
        $clockInEvents = $events->where('event_type', App\Models\AttendanceEvent::CLOCK_IN);
        $clockOutEvents = $events->where('event_type', App\Models\AttendanceEvent::CLOCK_OUT);
        $latestAttendanceEvent = $latestAttendanceEvent ?? null;
        $isAttendanceCooldownActive = $latestAttendanceEvent?->occurred_at->gt(now()->subMinutes(15));
        $cooldownLabel = $latestAttendanceEvent?->event_type === App\Models\AttendanceEvent::CLOCK_IN ? 'Clock In' : 'Clock Out';
        $cooldownMessage = $latestAttendanceEvent ? "Anda sudah {$cooldownLabel} pada pukul {$latestAttendanceEvent->local_occurred_at->format('H:i')}." : null;
    @endphp

    <div class="attendance-page">
        <section class="attendance-status-panel" aria-labelledby="attendance-status-title">
            <header class="attendance-status-panel__header">
                <h2 id="attendance-status-title">Status Hari Ini</h2>
                <span class="attendance-status-badge">{{ $events->count() }} catatan</span>
            </header>
            <div class="attendance-status-panel__body">
                @forelse($events as $event)
                    <div class="attendance-event">
                        <div class="attendance-event-row">
                            <strong>{{ $event->event_type === App\Models\AttendanceEvent::CLOCK_IN ? 'Clock In' : 'Clock Out' }}</strong>
                            <span>{{ $event->local_occurred_at->format('H:i') }} · {{ $event->type === 'DINAS_LUAR' ? 'Dinas Luar' : 'WFO' }}</span>
                        </div>
                        @if (filled($event->hrd_note))
                            <aside class="attendance-hrd-note" aria-label="Catatan dari HR">
                                <strong>Catatan dari HR</strong>
                                <p>{{ $event->hrd_note }}</p>
                            </aside>
                        @endif
                    </div>
                @empty
                    <div class="attendance-status-empty">
                        <p class="attendance-status-panel__description">Belum ada catatan absensi hari ini.</p>
                        <a href="{{ route('attendance.history') }}" class="attendance-status-empty__link">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Buka Riwayat Absensi</span>
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                @endforelse
            </div>
            @if($events->isNotEmpty())
                <footer class="attendance-status-panel__footer">
                    <a href="{{ route('attendance.history') }}" class="attendance-status-footer-link">
                        <span>Lihat seluruh riwayat absensi</span>
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </footer>
            @endif
        </section>

        <nav class="attendance-action-dock attendance-action-dock--sticky" aria-label="Aksi presensi">
            <a href="{{ route('attendance.clockIn.form') }}" class="attendance-action attendance-action--clock-in" @if($isAttendanceCooldownActive) data-attendance-cooldown data-attendance-cooldown-until="{{ $latestAttendanceEvent->occurred_at->copy()->addMinutes(15)->toIso8601String() }}" data-attendance-cooldown-message="{{ $cooldownMessage }}" @endif>
                <span><strong>Clock In</strong><small>{{ $clockInEvents->isNotEmpty() ? 'Terakhir '.$clockInEvents->first()->local_occurred_at->format('H:i') : 'Catat jam masuk kerja' }}</small></span>
                <span aria-hidden="true">›</span>
            </a>
            <a href="{{ route('attendance.clockOut.form') }}" class="attendance-action attendance-action--clock-out" @if($isAttendanceCooldownActive) data-attendance-cooldown data-attendance-cooldown-until="{{ $latestAttendanceEvent->occurred_at->copy()->addMinutes(15)->toIso8601String() }}" data-attendance-cooldown-message="{{ $cooldownMessage }}" @endif>
                <span><strong>Clock Out</strong><small>{{ $clockOutEvents->isNotEmpty() ? 'Terakhir '.$clockOutEvents->first()->local_occurred_at->format('H:i') : 'Catat jam pulang kerja' }}</small></span>
                <span aria-hidden="true">›</span>
            </a>
        </nav>
    </div>

    <x-modal id="attendance-cooldown-modal" title="Absensi Sudah Tercatat" variant="info" type="info" cancelLabel="Tutup">
        <p id="attendance-cooldown-message" style="margin:0; color:var(--text-muted);"></p>
    </x-modal>

    <style>
        .attendance-header-bar { display:flex; align-items:center; justify-content:space-between; width:100%; flex-wrap:wrap; gap:12px; }
        .section-header-inline { display:flex; align-items:center; gap:12px; }
        .section-icon { width:38px; height:38px; display:grid; place-items:center; border-radius:10px; background:#eef3f8; color:var(--primary-dark); flex-shrink:0; }
        .section-title { margin:0; color:var(--text-primary); font-size:1.0625rem; font-weight:700; line-height:1.25; }
        .section-subtitle { margin:3px 0 0; color:var(--text-muted); font-size:.75rem; }

        /* Button Riwayat Absensi - High-Contrast Solid Brand Accent */
        .attendance-history-link {
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:9px 18px;
            min-height:38px;
            border-radius:11px;
            background:linear-gradient(135deg, #0A3D62 0%, #145DA0 100%);
            color:#ffffff;
            font-size:.8125rem;
            font-weight:600;
            text-decoration:none;
            border:1px solid rgba(255, 255, 255, 0.18);
            box-shadow:0 2px 8px rgba(10, 61, 98, 0.24), 0 1px 2px rgba(10, 61, 98, 0.16);
            transition:all .18s ease;
            flex-shrink:0;
            box-sizing:border-box;
        }
        .attendance-history-link:hover {
            background:linear-gradient(135deg, #08314f 0%, #0f4c85 100%);
            box-shadow:0 4px 14px rgba(10, 61, 98, 0.32), 0 2px 4px rgba(10, 61, 98, 0.2);
            transform:translateY(-1px);
            color:#ffffff;
        }
        .attendance-history-link:active {
            transform:translateY(0);
            box-shadow:0 2px 4px rgba(10, 61, 98, 0.2);
        }
        .attendance-history-link:focus-visible {
            outline:2px solid var(--primary);
            outline-offset:2px;
        }
        .attendance-history-link svg {
            flex-shrink:0;
        }
        .attendance-history-link__arrow {
            transition:transform .15s ease;
            opacity:.85;
        }
        .attendance-history-link:hover .attendance-history-link__arrow {
            transform:translateX(2px);
            opacity:1;
        }

        .attendance-status-empty { display:flex; flex-direction:column; align-items:flex-start; gap:8px; padding:6px 0; }
        .attendance-status-empty__link { display:inline-flex; align-items:center; gap:6px; font-size:.8125rem; font-weight:600; color:var(--primary); text-decoration:none; transition:color .15s ease; }
        .attendance-status-empty__link:hover { color:var(--primary-dark); text-decoration:underline; }
        .attendance-status-panel__footer { padding:10px 20px; border-top:1px solid var(--border-light); background:#fafbfd; display:flex; justify-content:flex-end; }
        .attendance-status-footer-link { display:inline-flex; align-items:center; gap:5px; font-size:.8125rem; font-weight:600; color:var(--primary); text-decoration:none; transition:color .15s ease; }
        .attendance-status-footer-link:hover { color:var(--primary-dark); text-decoration:underline; }

        .attendance-page { width:100%; max-width:860px; margin:0 auto; padding-bottom:112px; display:grid; gap:16px; }
        .attendance-status-panel, .attendance-action-dock { border:1px solid var(--border); border-radius:18px; background:var(--white); overflow:hidden; }
        .attendance-status-panel__header { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px 20px; border-bottom:1px solid var(--border-light); }
        .attendance-status-panel__header h2 { margin:0; font-size:.9375rem; color:var(--text-primary); }
        .attendance-status-badge { padding:6px 10px; border-radius:999px; color:#27704a; background:#eef7f1; font-size:.6875rem; font-weight:700; }
        .attendance-status-panel__body { padding:14px 20px; }
        .attendance-event { padding:12px 0; border-bottom:1px solid var(--border-light); }
        .attendance-event:last-child { border-bottom:0; }
        .attendance-event-row { display:flex; align-items:center; justify-content:space-between; gap:12px; font-size:.875rem; }
        .attendance-event-row strong { color:var(--text-primary); }
        .attendance-event-row span, .attendance-status-panel__description { color:var(--text-muted); margin:0; }
        .attendance-hrd-note { margin-top:10px; padding:10px 12px; border:1px solid #fde68a; border-left:3px solid #d97706; border-radius:10px; background:#fffbeb; color:#78350f; }
        .attendance-hrd-note strong { display:block; font-size:.72rem; font-weight:700; }
        .attendance-hrd-note p { margin:4px 0 0; font-size:.8rem; line-height:1.45; overflow-wrap:anywhere; }
        .attendance-action-dock { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; padding:12px; }
        .attendance-action-dock--sticky { position:fixed; z-index:40; bottom:max(16px, env(safe-area-inset-bottom)); left:calc(50% + (var(--sidebar-width) / 2)); width:min(860px, calc(100vw - var(--sidebar-width) - 48px)); transform:translateX(-50%); border:1px solid rgba(148,163,184,.35); box-shadow:0 12px 30px rgba(15,23,42,.16); }
        .attendance-action { display:flex; align-items:center; justify-content:space-between; min-height:72px; padding:14px 16px; border-radius:14px; color:var(--white); text-decoration:none; }
        .attendance-action--clock-in { background:#2f6b4f; }
        .attendance-action--clock-in:hover, .attendance-action--clock-in:focus-visible { background:#25563f; }
        .attendance-action--clock-out { background:#914b4b; }
        .attendance-action--clock-out:hover, .attendance-action--clock-out:focus-visible { background:#783d3d; }
        .attendance-action strong, .attendance-action small { display:block; }
        .attendance-action small { margin-top:3px; opacity:.78; font-size:.75rem; }
        @media (max-width: 767px) {
            .attendance-page { padding-bottom:128px; }
            .attendance-action-dock--sticky { right:12px; left:12px; width:auto; transform:none; }
        }
        @media (max-width: 640px) {
            .topbar:has(.attendance-header-bar) { align-items:flex-start; }
            .attendance-header-bar { flex:1; min-width:0; flex-direction:column; align-items:flex-start; gap:10px; }
            .attendance-header-bar .section-header-inline { width:100%; min-width:0; }
            .attendance-header-bar .section-header-inline > div:last-child { min-width:0; }
            .section-icon { width:34px; height:34px; }
            .section-title { font-size:.9375rem; }
            .attendance-history-link { align-self:flex-end; padding:7px 12px; font-size:.75rem; gap:6px; min-height:34px; border-radius:9px; }
        }
    </style>

    <script>
        document.querySelectorAll('[data-attendance-cooldown]').forEach((action) => {
            action.addEventListener('click', (event) => {
                if (new Date(action.dataset.attendanceCooldownUntil).getTime() <= Date.now()) return;

                event.preventDefault();
                document.getElementById('attendance-cooldown-message').textContent = action.dataset.attendanceCooldownMessage;
                document.getElementById('attendance-cooldown-modal').style.display = 'flex';
            });
        });
    </script>
</x-app>
