<x-app title="Dinas Luar">
    <x-slot name="header">
        <div class="remote-header">
            <span class="remote-header__icon" aria-hidden="true">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 6V4a2 2 0 012-2h2a2 2 0 012 2v2m4 0H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2zM3 12h18M9 12v2h6v-2"/>
                </svg>
            </span>
            <div>
                <h1>Dinas Luar</h1>
                <p>Catat tugas di luar kantor</p>
            </div>
        </div>
    </x-slot>

    @php
        $hasRemoteToday = (bool) ($todayAttendance && $todayAttendance->type === 'DINAS_LUAR');
        $isRemoteActive = (bool) $activeRemoteAttendance;
        $isRemoteDone = $hasRemoteToday && (bool) $todayAttendance->clock_out_at;
        $hasOtherActiveAttendance = (bool) ($activeAttendance && ! $activeRemoteAttendance);
        $canStart = ! $activeAttendance && ! ($todayAttendance && $todayAttendance->clock_in_at);
        $canFinish = $isRemoteActive && ! $activeRemoteAttendance->clock_out_at;
    @endphp

    <div class="remote-dashboard remote-dashboard--viewport-fit {{ $previousIncompleteAttendance ? 'remote-dashboard--has-warning' : '' }}">
        <div class="remote-dashboard__content">
            @if($previousIncompleteAttendance)
                <section class="remote-warning" aria-label="Absensi sebelumnya belum selesai">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <strong>Absensi sebelumnya belum selesai</strong>
                        <span>{{ $previousIncompleteAttendance->date->translatedFormat('d M Y') }} · Masuk {{ $previousIncompleteAttendance->clock_in_at->format('H:i') }}</span>
                    </div>
                </section>
            @endif

            <section class="remote-status" aria-labelledby="remote-status-title">
                <header class="remote-status__header">
                    <div>
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            <circle cx="12" cy="12" r="9" stroke-width="2"/>
                        </svg>
                        <h2 id="remote-status-title">Status Hari Ini</h2>
                    </div>
                    <span class="remote-status__badge {{ $isRemoteActive ? 'is-active' : ($isRemoteDone ? 'is-done' : '') }}">
                        {{ $isRemoteActive ? 'Berjalan' : ($isRemoteDone ? 'Selesai' : 'Belum Mulai') }}
                    </span>
                </header>

                <div class="remote-status__body">
                    <span class="remote-status__illustration" aria-hidden="true">
                        @if($isRemoteDone)
                            <svg width="38" height="38" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5 13l4 4L19 7"/>
                                <circle cx="12" cy="12" r="9" stroke-width="1.7"/>
                            </svg>
                        @else
                            <svg width="38" height="38" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 6V4a2 2 0 012-2h2a2 2 0 012 2v2m4 0H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2zM3 12h18"/>
                            </svg>
                        @endif
                    </span>

                    @if($hasOtherActiveAttendance)
                        <h3>Presensi kantor sedang berjalan</h3>
                        <p>Selesaikan presensi kantor sebelum memulai dinas luar.</p>
                    @elseif($isRemoteActive)
                        <h3>Dinas luar sedang berjalan</h3>
                        <p>Mulai pukul {{ $activeRemoteAttendance->clock_in_at->format('H:i') }}. Selesaikan saat tugas Anda berakhir.</p>
                        @if($activeRemoteAttendance->notes)
                            <p class="remote-status__notes">{{ $activeRemoteAttendance->notes }}</p>
                        @endif
                    @elseif($isRemoteDone)
                        <h3>Dinas luar selesai</h3>
                        <p>{{ $todayAttendance->clock_in_at->format('H:i') }}–{{ $todayAttendance->clock_out_at->format('H:i') }} · Data sudah tercatat.</p>
                    @else
                        <h3>Belum ada dinas luar</h3>
                        <p>Mulai saat Anda tiba di lokasi tugas.</p>
                    @endif

                    <div class="remote-status__radius">
                        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11z"/>
                            <circle cx="12" cy="10" r="2.5" stroke-width="2"/>
                        </svg>
                        Tanpa batas radius kantor
                    </div>
                </div>
            </section>
        </div>

        <nav class="remote-actions" aria-label="Aksi dinas luar">
            <div class="remote-actions__inner">
                @if($canStart)
                    <a href="{{ route('remote-attendance.purpose') }}" class="remote-action remote-action--primary">
                @else
                    <span class="remote-action remote-action--disabled" aria-disabled="true">
                @endif
                    <span class="remote-action__icon" aria-hidden="true">
                        <svg width="23" height="23" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                    </span>
                    <strong>Mulai Dinas</strong>
                @if($canStart)</a>@else</span>@endif

                @if($canFinish)
                    <a href="{{ route('remote-attendance.clockOut.form') }}" class="remote-action remote-action--primary">
                @else
                    <span class="remote-action remote-action--disabled" aria-disabled="true">
                @endif
                    <span class="remote-action__icon" aria-hidden="true">
                        <svg width="23" height="23" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </span>
                    <strong>Selesai Tugas</strong>
                @if($canFinish)</a>@else</span>@endif
            </div>
        </nav>
    </div>

    <style>
        .remote-header { display:flex; align-items:center; gap:10px; }
        .remote-header__icon { width:34px; height:34px; display:grid; place-items:center; flex:0 0 auto; border-radius:10px; background:#eef3f8; color:var(--primary-dark); }
        .remote-header h1 { margin:0; font-size:1rem; line-height:1.25; font-weight:700; }
        .remote-header p { margin:2px 0 0; color:var(--text-muted); font-size:.75rem; }
        .main-content { overflow:hidden; }
        .content-wrapper { height:100vh; height:100dvh; display:flex; flex-direction:column; overflow:hidden; }
        .topbar { flex:0 0 auto; }
        .remote-dashboard { width:100%; max-width:860px; min-height:0; flex:1 1 auto; display:flex; flex-direction:column; margin:0 auto; }
        .remote-dashboard__content { min-height:0; flex:1 1 auto; display:flex; flex-direction:column; gap:14px; }
        .remote-warning { display:flex; align-items:flex-start; gap:10px; padding:14px 16px; border:1px solid #f4cf99; border-radius:14px; background:#fffdfa; color:#d97706; }
        .remote-warning div { display:grid; gap:3px; }
        .remote-warning strong { color:var(--text-primary); font-size:.8125rem; line-height:1.35; }
        .remote-warning span { color:var(--text-muted); font-size:.6875rem; }
        .remote-status { min-height:0; flex:1 1 auto; display:flex; flex-direction:column; overflow:hidden; border:1px solid var(--border); border-radius:18px; background:var(--white); box-shadow:0 1px 2px rgba(15,23,42,.03); }
        .remote-status__header { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:17px 20px; border-bottom:1px solid var(--border-light); }
        .remote-status__header div { display:flex; align-items:center; gap:10px; color:var(--primary); }
        .remote-status__header h2 { margin:0; color:var(--text-primary); font-size:.9375rem; font-weight:700; }
        .remote-status__badge { padding:7px 11px; border-radius:999px; background:#f3f4f6; color:#6b7280; font-size:.6875rem; font-weight:700; white-space:nowrap; }
        .remote-status__badge.is-active { background:#eef4fb; color:var(--primary-dark); }
        .remote-status__badge.is-done { background:#eef7f1; color:#27704a; }
        .remote-status__body { min-height:0; flex:1 1 auto; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:clamp(18px,4vh,38px) 24px clamp(16px,3vh,26px); text-align:center; }
        .remote-status__illustration { width:72px; height:72px; display:grid; place-items:center; margin-bottom:22px; border-radius:50%; background:#f5f8fb; color:var(--primary-dark); }
        .remote-status__body h3 { margin:0; color:var(--text-primary); font-size:1.25rem; font-weight:700; line-height:1.3; }
        .remote-status__body > p { max-width:440px; margin:10px 0 0; color:var(--text-muted); font-size:.875rem; line-height:1.55; }
        .remote-status__notes { padding:10px 14px; border-radius:10px; background:#f8fafc; color:var(--text-secondary) !important; }
        .remote-status__radius { width:100%; display:flex; align-items:center; gap:8px; margin-top:auto; padding-top:28px; color:var(--text-muted); font-size:.75rem; text-align:left; }
        .remote-actions { flex:0 0 auto; margin-top:18px; padding:14px; border:1px solid var(--border); border-radius:18px; background:var(--white); box-shadow:0 8px 24px rgba(15,23,42,.08); }
        .remote-actions__inner { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
        .remote-action { position:relative; min-height:68px; display:flex; align-items:center; justify-content:center; padding:12px 60px; border:1px solid transparent; border-radius:14px; color:var(--white); text-decoration:none; }
        .remote-action--primary { background:var(--primary-dark); box-shadow:0 5px 14px rgba(10,61,98,.18); }
        .remote-action--primary:hover { background:#08334f; }
        .remote-action--primary:focus-visible { outline:3px solid rgba(20,93,160,.28); outline-offset:3px; }
        .remote-action--disabled { border-color:var(--border-light); background:#f8fafc; color:#aab3bf; cursor:not-allowed; }
        .remote-action__icon { position:absolute; left:16px; width:40px; height:40px; display:grid; place-items:center; border-radius:10px; background:rgba(255,255,255,.12); }
        .remote-action--disabled .remote-action__icon { background:#f0f3f6; }
        .remote-action strong { font-size:.9375rem; }
        @media (max-width:767px) {
            .content-wrapper { padding-bottom:max(12px,env(safe-area-inset-bottom)); }
            .remote-status__body { padding:clamp(16px,3vh,30px) 20px clamp(14px,2.5vh,22px); }
            .remote-actions { margin-top:12px; padding:14px 8px 0; border-width:1px 0 0; border-radius:0; background:transparent; box-shadow:none; }
            .remote-actions__inner { grid-template-columns:1fr; gap:14px; max-width:520px; margin:0 auto; }
            .remote-action { min-height:62px; }
        }
        @media (max-width:359px), (max-height:700px) and (max-width:767px) {
            .remote-dashboard__content { gap:10px; }
            .remote-warning { padding:10px 12px; }
            .remote-status__header { padding:12px 14px; }
            .remote-status__body { padding:14px 16px 12px; }
            .remote-status__illustration { width:58px; height:58px; margin-bottom:14px; }
            .remote-status__body h3 { font-size:1.0625rem; }
            .remote-status__radius { padding-top:12px; }
            .remote-actions { margin-top:8px; padding-top:10px; }
            .remote-actions__inner { gap:10px; }
            .remote-action { min-height:54px; }
        }
        @media (max-height:560px) {
            .remote-status__illustration,
            .remote-status__radius { display:none; }
            .remote-status__body > p { margin-top:6px; line-height:1.4; }
            .remote-action { min-height:48px; }
        }
        @media (max-width:319px) {
            .remote-status__header { gap:6px; padding-inline:12px; }
            .remote-status__header div { gap:6px; }
            .remote-status__header h2 { font-size:.8125rem; white-space:nowrap; }
            .remote-status__badge { padding:6px 8px; font-size:.625rem; }
        }
    </style>
</x-app>
