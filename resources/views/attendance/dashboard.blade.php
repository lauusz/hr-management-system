<x-app title="Presensi Hari Ini">

    <div style="max-width:600px; margin:0 auto; display:flex; flex-direction:column; gap:24px;">

        <div class="card bg-gradient">
            <div style="color:#fff;">
                <h2 style="margin:0; font-size:1.5rem; font-weight:700;">Halo, {{ auth()->user()->name }}!</h2>
                <p style="margin:4px 0 0; opacity:0.9; font-size:0.95rem;">
                    {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}
                </p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Status Hari Ini</h3>
                @if($attendance)
                    <span class="badge-status {{ $attendance->status === 'TERLAMBAT' ? 'bg-red' : 'bg-green' }}">
                        {{ $attendance->status }}
                    </span>
                @else
                    <span class="badge-status bg-yellow">Belum Presensi</span>
                @endif
            </div>

            <div class="card-body">
                @if(!$attendance)
                    <div class="empty-state-box">
                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ca8a04;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p>Anda belum melakukan Clock-In hari ini.</p>
                    </div>
                @else
                    <div class="info-grid">
                        
                        <div class="info-item">
                            <span class="label">Jadwal Shift</span>
                            <span class="value">
                                @if($attendance->normal_start_time || $attendance->normal_end_time)
                                    {{ $attendance->normal_start_time ? $attendance->normal_start_time->format('H:i') : '-' }} - 
                                    {{ $attendance->normal_end_time ? $attendance->normal_end_time->format('H:i') : '-' }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>

                        <div class="info-item">
                            <span class="label">Jam Masuk</span>
                            <span class="value {{ $attendance->clock_in_at ? 'text-primary' : 'text-muted' }}">
                                {{ $attendance->clock_in_at ? $attendance->clock_in_at->format('H:i') : '--:--' }}
                            </span>
                        </div>

                        <div class="info-item">
                            <span class="label">Jam Pulang</span>
                            <span class="value {{ $attendance->clock_out_at ? 'text-primary' : 'text-muted' }}">
                                {{ $attendance->clock_out_at ? $attendance->clock_out_at->format('H:i') : '--:--' }}
                            </span>
                        </div>

                        {{-- INFO KETERLAMBATAN --}}
                        @if($attendance->late_minutes > 0)
                            @php
                                $m = $attendance->late_minutes;
                                $hours = intdiv($m, 60);
                                $minutes = $m % 60;
                                $lateLabel = ($hours > 0 ? $hours.'j ' : '') . $minutes.'m';
                            @endphp
                            <div class="info-item full-width">
                                <span class="label text-red">Keterlambatan</span>
                                <span class="value text-red">{{ $lateLabel }}</span>
                            </div>
                        @endif

                        {{-- INFO PULANG AWAL --}}
                        @if(($attendance->early_leave_minutes ?? 0) > 0)
                            @php
                                $m = $attendance->early_leave_minutes;
                                $hours = intdiv($m, 60);
                                $minutes = $m % 60;
                                $earlyLabel = ($hours > 0 ? $hours.'j ' : '') . $minutes.'m';
                            @endphp
                            <div class="info-item full-width">
                                <span class="label text-orange">Pulang Awal</span>
                                <span class="value text-orange">{{ $earlyLabel }}</span>
                            </div>
                        @endif

                        {{-- [BARU] INFO LEMBUR --}}
                        @if(($attendance->overtime_minutes ?? 0) > 0)
                            @php
                                $m = $attendance->overtime_minutes;
                                $hours = intdiv($m, 60);
                                $minutes = $m % 60;
                                $otLabel = ($hours > 0 ? $hours.'j ' : '') . $minutes.'m';
                            @endphp
                            <div class="info-item full-width">
                                <span class="label text-primary">Lembur</span>
                                <span class="value text-primary">+ {{ $otLabel }}</span>
                            </div>
                        @endif

                    </div>
                @endif
            </div>
        </div>

        <div class="action-grid">
            <a href="{{ route('attendance.clockIn.form') }}" class="btn-clock btn-in {{ $attendance && $attendance->clock_in_at ? 'disabled' : '' }}">
                <div class="icon-circle">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                </div>
                <div class="btn-text">
                    <span class="btn-title">Clock In</span>
                    <span class="btn-desc">Catat jam masuk</span>
                </div>
            </a>

            <a href="{{ route('attendance.clockOut.form') }}" class="btn-clock btn-out {{ (!$attendance || !$attendance->clock_in_at || $attendance->clock_out_at) ? 'disabled' : '' }}">
                <div class="icon-circle">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </div>
                <div class="btn-text">
                    <span class="btn-title">Clock Out</span>
                    <span class="btn-desc">Catat jam pulang</span>
                </div>
            </a>
        </div>

    </div>

    <style>
        /* --- CARD STYLES --- */
        .card { background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); overflow: hidden; border: 1px solid #f3f4f6; }
        
        .bg-gradient { background: linear-gradient(135deg, #1e4a8d 0%, #163a75 100%); padding: 24px; border: none; }
        
        .card-header { padding: 20px; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center; }
        .card-title { margin: 0; font-size: 1.1rem; font-weight: 700; color: #1f2937; }
        .card-body { padding: 20px; }

        /* --- EMPTY STATE --- */
        .empty-state-box { background: #fffbeb; border: 1px solid #fef3c7; border-radius: 12px; padding: 24px; text-align: center; color: #92400e; }
        .empty-state-box p { margin: 12px 0 0 0; font-weight: 500; font-size: 0.95rem; }

        /* --- INFO GRID --- */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .info-item { background: #f9fafb; padding: 12px 16px; border-radius: 12px; display: flex; flex-direction: column; gap: 4px; }
        .info-item.full-width { grid-column: span 2; flex-direction: row; justify-content: space-between; align-items: center; }
        
        .label { font-size: 0.8rem; color: #6b7280; text-transform: uppercase; font-weight: 600; letter-spacing: 0.03em; }
        .value { font-size: 1.1rem; font-weight: 700; color: #111827; }
        
        .text-primary { color: #1e4a8d; }
        .text-muted { color: #9ca3af; }
        .text-red { color: #dc2626; }
        .text-orange { color: #d97706; }

        /* --- BADGES --- */
        .badge-status { padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
        .bg-green { background: #dcfce7; color: #166534; }
        .bg-red { background: #fee2e2; color: #991b1b; }
        .bg-yellow { background: #fef3c7; color: #92400e; }

        /* --- ACTION BUTTONS --- */
        .action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 8px; }
        
        .btn-clock { display: flex; align-items: center; gap: 16px; padding: 20px; border-radius: 16px; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s; position: relative; overflow: hidden; }
        .btn-clock:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
        .btn-clock:active { transform: scale(0.98); }
        
        .btn-in { background: #1e4a8d; color: #fff; }
        .btn-out { background: #fff; border: 2px solid #e5e7eb; color: #374151; }
        .btn-out:hover { border-color: #d1d5db; background: #f9fafb; }

        .btn-clock.disabled { opacity: 0.5; pointer-events: none; filter: grayscale(100%); }

        .icon-circle { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
        .btn-in .icon-circle { background: rgba(255,255,255,0.2); }
        .btn-out .icon-circle { background: #f3f4f6; color: #1f2937; }

        .btn-text { display: flex; flex-direction: column; }
        .btn-title { font-size: 1.1rem; font-weight: 700; line-height: 1.2; }
        .btn-desc { font-size: 0.85rem; opacity: 0.8; margin-top: 2px; }

        @media(max-width: 480px) {
            .action-grid { grid-template-columns: 1fr; }
        }
    </style>

</x-app>