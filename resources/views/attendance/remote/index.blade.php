<x-app title="Dinas Luar">
    <x-slot name="header">
        <div class="remote-header">
            <span class="remote-header__icon" aria-hidden="true">▣</span>
            <div><h1>Dinas Luar</h1><p>Catat tugas di luar kantor</p></div>
        </div>
    </x-slot>

    @php($events = $events ?? collect())

    <div class="remote-dashboard">
        <section class="remote-status">
            <header><h2>Catatan Dinas Luar</h2><span>{{ $events->count() }} terakhir</span></header>
            <div class="remote-status__body">
                @forelse($events as $event)
                    <div class="remote-event-row">
                        <strong>{{ $event->event_type === App\Models\AttendanceEvent::CLOCK_IN ? 'Clock In' : 'Clock Out' }}</strong>
                        <span>{{ $event->occurred_at->translatedFormat('d M Y · H:i') }}</span>
                    </div>
                @empty
                    <p>Belum ada catatan dinas luar.</p>
                @endforelse
            </div>
        </section>

        <nav class="remote-actions" aria-label="Aksi dinas luar">
            <a href="{{ route('remote-attendance.purpose') }}" class="remote-action">Mulai Dinas</a>
            <a href="{{ route('remote-attendance.clockOut.form') }}" class="remote-action">Selesai Tugas</a>
        </nav>
    </div>

    <style>
        .remote-header { display:flex; align-items:center; gap:10px; }
        .remote-header__icon { width:34px; height:34px; display:grid; place-items:center; border-radius:10px; background:#eef3f8; color:var(--primary-dark); }
        .remote-header h1 { margin:0; font-size:1rem; }.remote-header p { margin:2px 0 0; color:var(--text-muted); font-size:.75rem; }
        .remote-dashboard { width:100%; max-width:860px; margin:0 auto; display:grid; gap:16px; }
        .remote-status,.remote-actions { border:1px solid var(--border); border-radius:18px; background:var(--white); overflow:hidden; }
        .remote-status header { display:flex; justify-content:space-between; align-items:center; padding:16px 20px; border-bottom:1px solid var(--border-light); }
        .remote-status h2 { margin:0; font-size:.9375rem; }.remote-status header span { color:var(--text-muted); font-size:.75rem; }
        .remote-status__body { padding:8px 20px; }.remote-status__body > p { color:var(--text-muted); }
        .remote-event-row { display:flex; justify-content:space-between; gap:12px; padding:12px 0; border-bottom:1px solid var(--border-light); }
        .remote-event-row:last-child { border-bottom:0; }.remote-event-row span { color:var(--text-muted); font-size:.875rem; }
        .remote-actions { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; padding:12px; }
        .remote-action { padding:16px; border-radius:14px; background:var(--primary-dark); color:#fff; text-align:center; text-decoration:none; font-weight:700; }
        @media (max-width:640px) { .remote-actions { grid-template-columns:1fr; } }
    </style>
</x-app>
