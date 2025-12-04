<x-app title="Presensi Hari Ini">

    <div style="max-width:520px; margin:0 auto; display:flex; flex-direction:column; gap:18px;">

        <div class="card" style="padding:16px;">
            <h2 style="font-size:1.25rem; font-weight:700; margin-bottom:10px;">Status Presensi Hari Ini</h2>

            @if(!$attendance)
            <div style="padding:12px; background:#fef3c7; border-radius:8px; color:#92400e; margin-bottom:10px;">
                Belum ada presensi hari ini.
            </div>
            @endif

            @if($attendance)
            <div style="display:grid; gap:10px; margin-bottom:14px;">

                <div style="display:flex; justify-content:space-between;">
                    <span>Clock-in</span>
                    <strong>{{ $attendance->clock_in_at ? $attendance->clock_in_at->format('H:i') : '-' }}</strong>
                </div>

                <div style="display:flex; justify-content:space-between;">
                    <span>Clock-out</span>
                    <strong>{{ $attendance->clock_out_at ? $attendance->clock_out_at->format('H:i') : '-' }}</strong>
                </div>

                <div style="display:flex; justify-content:space-between;">
                    <span>Status</span>
                    @if($attendance->status === 'TERLAMBAT')
                    <strong style="color:#dc2626;">Terlambat</strong>
                    @elseif($attendance->status === 'HADIR')
                    <strong style="color:#059669;">Hadir</strong>
                    @else
                    <strong style="opacity:.7;">{{ $attendance->status }}</strong>
                    @endif
                </div>

                @if($attendance->late_minutes > 0)
                @php
                $m = $attendance->late_minutes;
                $hours = intdiv($m, 60);
                $minutes = $m % 60;

                if ($hours > 0 && $minutes > 0) {
                $lateLabel = $hours . ' jam ' . $minutes . ' menit';
                } elseif ($hours > 0) {
                $lateLabel = $hours . ' jam';
                } else {
                $lateLabel = $minutes . ' menit';
                }
                @endphp

                <div style="display:flex; justify-content:space-between;">
                    <span>Keterlambatan</span>
                    <strong style="color:#dc2626;">{{ $lateLabel }}</strong>
                </div>
                @endif

            </div>
            @endif
        </div>

        <div class="card" style="padding:16px; display:flex; flex-direction:column; gap:12px;">

            <a href="{{ route('attendance.clockIn.form') }}"
                style="padding:12px 16px; background:#1e40af;text-decoration: none; color:#fff; text-align:center; border-radius:10px; font-weight:600;">
                Clock-in
            </a>

            <a href="{{ route('attendance.clockOut.form') }}"
                style="padding:12px 16px; background:#059669;text-decoration: none; color:#fff; text-align:center; border-radius:10px; font-weight:600;">
                Clock-out
            </a>

        </div>

    </div>

</x-app>