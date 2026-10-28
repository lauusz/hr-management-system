<x-app title="Presensi Hari Ini">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon" aria-hidden="true">◷</div>
            <div>
                <h1 class="section-title">Presensi Hari Ini</h1>
                <p class="section-subtitle">Catat dan pantau kehadiran harian Anda</p>
            </div>
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
                    <p class="attendance-status-panel__description">Belum ada catatan absensi hari ini.</p>
                @endforelse
            </div>
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
        .section-header-inline { display:flex; align-items:center; gap:10px; }
        .section-icon { width:32px; height:32px; display:grid; place-items:center; border-radius:9px; background:#eef3f8; color:var(--primary-dark); font-weight:700; }
        .section-title { margin:0; color:var(--text-primary); font-size:1rem; font-weight:700; }
        .section-subtitle { margin:2px 0 0; color:var(--text-muted); font-size:.75rem; }
        .attendance-page { width:100%; max-width:860px; margin:0 auto; padding-bottom:112px; display:grid; gap:16px; }
        .attendance-status-panel, .attendance-action-dock { border:1px solid var(--border); border-radius:18px; background:var(--white); overflow:hidden; }
        .attendance-status-panel__header { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px 20px; border-bottom:1px solid var(--border-light); }
        .attendance-status-panel__header h2 { margin:0; font-size:.9375rem; color:var(--text-primary); }
        .attendance-status-badge { padding:6px 10px; border-radius:999px; color:#27704a; background:#eef7f1; font-size:.6875rem; font-weight:700; }
        .attendance-status-panel__body { padding:8px 20px; }
        .attendance-event { padding:12px 0; border-bottom:1px solid var(--border-light); }
        .attendance-event:last-child { border-bottom:0; }
        .attendance-event-row { display:flex; align-items:center; justify-content:space-between; gap:12px; font-size:.875rem; }
        .attendance-event-row strong { color:var(--text-primary); }
        .attendance-event-row span, .attendance-status-panel__description { color:var(--text-muted); }
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
