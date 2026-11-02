{{--
    Blok Masuk / Pulang di kartu riwayat absensi.
    Menampilkan jam absensi; jika absen lebih dari sekali (mis. jam 8 dan jam 16),
    jam ditumpuk vertikal sesuai referensi pengguna.
    Ditekan untuk menampilkan modal bukti kehadiran seperti di /hr/attendances.
    @param \Illuminate\Support\Collection<int, \App\Models\AttendanceEvent> $events
    @param string $kind 'in' | 'out'
    @param string $sessionKey
--}}
@php
    $isIn = $kind === 'in';
    $label = $isIn ? 'Absen Masuk' : 'Absen Pulang';
    $emptyLabel = $isIn ? 'Belum absen masuk' : 'Belum absen pulang';
    $approvalMap = [
        'PENDING' => ['Menunggu', 'ah-mini-badge--warning'],
        'APPROVED' => ['Disetujui', 'ah-mini-badge--success'],
        'REJECTED' => ['Ditolak', 'ah-mini-badge--error'],
    ];
    $count = $events->count();
    $firstEvent = $events->first();
    $locationText = $firstEvent
        ? ($firstEvent->location?->name ?? ($firstEvent->type === 'DINAS_LUAR' ? 'Di Luar Kantor' : ($firstEvent->is_outside_reference_location ? 'Luar Radius Kantor' : 'Lokasi Terdaftar')))
        : null;
    $hasPhoto = $events->contains(fn ($e) => $e->photo && ! $e->photo_deleted_at);
    $hasLocation = $events->contains(fn ($e) => $e->latitude !== null && $e->longitude !== null);
@endphp

<button type="button"
    class="ah-point ah-point--{{ $kind }}"
    data-open-evidence="{{ $sessionKey }}"
    data-focus-side="{{ $kind }}"
    aria-label="Lihat bukti {{ $label }}">
    <div class="ah-point-head">
        <span class="ah-point-label">
            @if($isIn)
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            @else
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            @endif
            <span>{{ $label }}</span>
        </span>
        @if($count > 1)
            <span class="ah-count-badge" title="Tercatat {{ $count }} kali">{{ $count }}x</span>
        @endif
    </div>

    @if($count > 0)
        {{-- JAM ABSENSI DITUMPUK VERTIKAL (IMAGE 1) --}}
        <div class="ah-time-stack">
            @foreach($events as $event)
                @php $approval = $approvalMap[$event->approval_status] ?? null; @endphp
                <div class="ah-time-row">
                    <span class="ah-time-val">{{ $event->local_occurred_at->format('H:i') }}</span>
                    <span class="ah-tz">{{ $event->attendanceTimezone() === 'Asia/Makassar' ? 'WITA' : 'WIB' }}</span>
                    @if($approval)
                        <span class="ah-mini-badge {{ $approval[1] }}">{{ $approval[0] }}</span>
                    @endif
                </div>
            @endforeach
        </div>

        @if($locationText)
            <div class="ah-point-loc" title="{{ $locationText }}">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11z"/><circle cx="12" cy="10" r="2.5" stroke-width="2"/></svg>
                <span class="ah-point-loc-text">{{ $locationText }}</span>
            </div>
        @endif
    @else
        <div class="ah-point-empty">
            <span class="ah-time-val ah-time-val--empty">--:--</span>
            <span class="ah-point-empty-label">{{ $emptyLabel }}</span>
        </div>
    @endif

    <div class="ah-point-footer">
        <span class="ah-point-cue">
            <span>Lihat bukti</span>
            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/></svg>
        </span>
        <div class="ah-point-indicators">
            @if($hasPhoto)
                <span class="ah-indicator-icon" title="Ada foto kehadiran">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3" stroke-width="2"/></svg>
                </span>
            @endif
            @if($hasLocation)
                <span class="ah-indicator-icon" title="Ada koordinat lokasi">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11z"/><circle cx="12" cy="10" r="2.5" stroke-width="2"/></svg>
                </span>
            @endif
        </div>
    </div>
</button>
