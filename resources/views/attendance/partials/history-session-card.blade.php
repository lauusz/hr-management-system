{{--
    Kartu satu hari kerja pada halaman Riwayat Absensi.
    @param object $session  Hasil AttendanceHistoryService::getHistory()
--}}
@php
    $sessionKey = $session->work_date->format('Ymd');
    $shortDate = $session->work_date->format('d/m/y');
    $inEvents = $session->clock_ins;
    $outEvents = $session->clock_outs;
    $inFirst = $inEvents->first();
    $outLast = $outEvents->last();

    $isDinasLuar = ($inFirst?->type === 'DINAS_LUAR') || ($outLast?->type === 'DINAS_LUAR');
    $isFlexible = (bool) ($inFirst?->is_flexible || $outLast?->is_flexible);

    [$typeLabel, $typeClass] = match (true) {
        $isDinasLuar => ['Dinas Luar', 'ah-type--remote'],
        $isFlexible => ['Fleksibel', 'ah-type--flexible'],
        default => ['Di Kantor', 'ah-type--office'],
    };

    [$statusLabel, $statusClass] = match (true) {
        $session->has_rejected => ['Ada yang Ditolak', 'lr-badge--error'],
        $session->has_pending => ['Menunggu Persetujuan', 'lr-badge--warning'],
        $inEvents->isEmpty() => ['Tanpa Absen Masuk', 'lr-badge--gray'],
        $outEvents->isEmpty() => ['Belum Absen Pulang', 'lr-badge--gray'],
        default => ['Lengkap', 'lr-badge--success'],
    };

    $workMinutes = ($inFirst && $outLast)
        ? (int) abs($inFirst->occurred_at->diffInMinutes($outLast->occurred_at))
        : null;
    $workDurationText = $workMinutes !== null
        ? trim((intdiv($workMinutes, 60) > 0 ? intdiv($workMinutes, 60).' jam ' : '').($workMinutes % 60).' menit')
        : null;
    $titleId = 'ah-session-'.$sessionKey;
@endphp

<article class="ah-card" aria-labelledby="{{ $titleId }}">
    {{-- Card Header: Type Badge & Status Badge --}}
    <div class="ah-card-top">
        <span class="ah-type {{ $typeClass }}">{{ $typeLabel }}</span>
        <span class="lr-badge {{ $statusClass }}">{{ $statusLabel }}</span>
    </div>

    {{-- Card Date Header: Hari, Tanggal Lengkap + Short Date (30/10/26) --}}
    <div class="ah-card-date-row">
        <h2 id="{{ $titleId }}" class="ah-card-date">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>{{ $session->work_date->translatedFormat('l, j F Y') }}</span>
        </h2>
        <span class="ah-date-badge">{{ $shortDate }}</span>
    </div>

    {{-- Body: Absen Masuk & Absen Pulang (Jam Ditumpuk Bila > 1x) --}}
    <div class="ah-points">
        @include('attendance.partials.history-endpoint', ['events' => $inEvents, 'kind' => 'in', 'sessionKey' => $sessionKey])
        @include('attendance.partials.history-endpoint', ['events' => $outEvents, 'kind' => 'out', 'sessionKey' => $sessionKey])
    </div>

    {{-- Catatan Alasan & Catatan HRD --}}
    @if($session->user_notes->isNotEmpty() || $session->hrd_notes->isNotEmpty())
        <div class="ah-notes">
            @foreach($session->user_notes as $note)
                <div class="ah-note">
                    <strong>Keterangan Anda</strong>
                    <p>{{ $note }}</p>
                </div>
            @endforeach
            @foreach($session->hrd_notes as $hrdNote)
                <div class="ah-note ah-note--hrd">
                    <strong>Catatan dari HRD</strong>
                    <p>{{ $hrdNote }}</p>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Card Footer: Shift, Lama Kerja, dan Tombol Modal Bukti Kehadiran --}}
    <div class="ah-card-footer">
        <div class="ah-card-footer-info">
            <span class="ah-card-meta" title="{{ $session->shift_name }}">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="ah-card-meta-text">
                    Shift {{ $session->shift_name }}@if($session->work_schedule !== '-') · {{ $session->work_schedule }}@endif
                </span>
            </span>
            @if($workDurationText)
                <span class="ah-card-duration">Lama kerja {{ $workDurationText }}</span>
            @endif
        </div>

        <button type="button"
            class="ah-card-btn-evidence"
            data-open-evidence="{{ $sessionKey }}"
            data-focus-side="in"
            aria-label="Lihat bukti kehadiran tanggal {{ $shortDate }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span>Lihat Bukti Kehadiran</span>
        </button>
    </div>

    {{-- Template HTML Pre-Rendered untuk Modal Bukti Kehadiran (Sesuai Modal /hr/attendances) --}}
    <template id="evidence-template-{{ $sessionKey }}" data-date="{{ $shortDate }}">
        <div class="attendance-evidence-grid">
            {{-- PANEL CLOCK IN --}}
            <section class="attendance-evidence-panel" id="evidence-panel-in-{{ $sessionKey }}">
                <h4>CLOCK IN</h4>
                <div class="attendance-evidence-list">
                    @forelse($inEvents as $event)
                        @php
                            $tz = $event->attendanceTimezone() === 'Asia/Makassar' ? 'WITA' : 'WIB';
                            $typeText = $event->type === 'DINAS_LUAR' ? 'Dinas Luar' : ($event->is_flexible ? 'Fleksibel' : null);
                            $hasCoords = $event->latitude !== null && $event->longitude !== null;
                            $coords = $hasCoords ? $event->latitude.', '.$event->longitude : null;
                            $hasPhoto = $event->photo && ! $event->photo_deleted_at;
                            $photoUrl = $hasPhoto ? asset('storage/'.$event->photo) : null;
                            $locationName = $event->location?->name
                                ?? ($event->type === 'DINAS_LUAR' ? 'Di Luar Kantor' : ($event->is_outside_reference_location ? 'Luar Radius Kantor' : 'Lokasi Terdaftar'));
                        @endphp
                        <section class="attendance-evidence-entry">
                            <p class="attendance-evidence-entry-time">
                                {{ $event->local_occurred_at->format('H:i') }} · {{ $event->local_occurred_at->format('d/m/y') }}@if($typeText) · {{ $typeText }}@endif
                            </p>
                            @if($event->notes)
                                <p class="attendance-evidence-notes">Alasan: {{ $event->notes }}</p>
                            @endif
                            @if($event->hrd_note)
                                <p class="attendance-evidence-notes attendance-evidence-notes--hrd">Catatan HRD: {{ $event->hrd_note }}</p>
                            @endif

                            <div class="attendance-evidence-subgrid">
                                <section class="attendance-evidence-block">
                                    <h5>FOTO</h5>
                                    @if($hasCoords)
                                        <p class="attendance-evidence-coordinates attendance-evidence-photo-spacer" aria-hidden="true">{{ $coords }}</p>
                                    @endif
                                    @if($hasPhoto)
                                        <button type="button"
                                            class="attendance-evidence-photo-btn"
                                            data-image-viewer-src="{{ $photoUrl }}"
                                            data-image-viewer-alt="Foto Clock In {{ $event->local_occurred_at->format('d/m/y H:i') }}"
                                            aria-label="Perbesar foto Clock In">
                                            <img class="attendance-evidence-photo" src="{{ $photoUrl }}" alt="Foto Clock In" loading="lazy">
                                            <span class="attendance-evidence-photo-hint">Ketuk untuk perbesar</span>
                                        </button>
                                    @else
                                        <p class="attendance-evidence-empty">Foto tidak tersedia.</p>
                                    @endif
                                </section>

                                <section class="attendance-evidence-block">
                                    <h5>LOKASI</h5>
                                    @if($hasCoords)
                                        <p class="attendance-evidence-coordinates">{{ $coords }}</p>
                                        <iframe class="attendance-evidence-map"
                                            title="Peta lokasi Clock In"
                                            loading="lazy"
                                            referrerpolicy="no-referrer-when-downgrade"
                                            data-src="https://www.google.com/maps?q={{ urlencode($coords) }}&z=16&output=embed"></iframe>
                                    @else
                                        <p class="attendance-evidence-empty">Lokasi tidak tersedia.</p>
                                    @endif
                                </section>
                            </div>
                        </section>
                    @empty
                        <p class="attendance-evidence-empty">Belum ada data absensi.</p>
                    @endforelse
                </div>
            </section>

            {{-- PANEL CLOCK OUT --}}
            <section class="attendance-evidence-panel" id="evidence-panel-out-{{ $sessionKey }}">
                <h4>CLOCK OUT</h4>
                <div class="attendance-evidence-list">
                    @forelse($outEvents as $event)
                        @php
                            $tz = $event->attendanceTimezone() === 'Asia/Makassar' ? 'WITA' : 'WIB';
                            $typeText = $event->type === 'DINAS_LUAR' ? 'Dinas Luar' : ($event->is_flexible ? 'Fleksibel' : null);
                            $hasCoords = $event->latitude !== null && $event->longitude !== null;
                            $coords = $hasCoords ? $event->latitude.', '.$event->longitude : null;
                            $hasPhoto = $event->photo && ! $event->photo_deleted_at;
                            $photoUrl = $hasPhoto ? asset('storage/'.$event->photo) : null;
                            $locationName = $event->location?->name
                                ?? ($event->type === 'DINAS_LUAR' ? 'Di Luar Kantor' : ($event->is_outside_reference_location ? 'Luar Radius Kantor' : 'Lokasi Terdaftar'));
                        @endphp
                        <section class="attendance-evidence-entry">
                            <p class="attendance-evidence-entry-time">
                                {{ $event->local_occurred_at->format('H:i') }} · {{ $event->local_occurred_at->format('d/m/y') }}@if($typeText) · {{ $typeText }}@endif
                            </p>
                            @if($event->notes)
                                <p class="attendance-evidence-notes">Alasan: {{ $event->notes }}</p>
                            @endif
                            @if($event->hrd_note)
                                <p class="attendance-evidence-notes attendance-evidence-notes--hrd">Catatan HRD: {{ $event->hrd_note }}</p>
                            @endif

                            <div class="attendance-evidence-subgrid">
                                <section class="attendance-evidence-block">
                                    <h5>FOTO</h5>
                                    @if($hasCoords)
                                        <p class="attendance-evidence-coordinates attendance-evidence-photo-spacer" aria-hidden="true">{{ $coords }}</p>
                                    @endif
                                    @if($hasPhoto)
                                        <button type="button"
                                            class="attendance-evidence-photo-btn"
                                            data-image-viewer-src="{{ $photoUrl }}"
                                            data-image-viewer-alt="Foto Clock Out {{ $event->local_occurred_at->format('d/m/y H:i') }}"
                                            aria-label="Perbesar foto Clock Out">
                                            <img class="attendance-evidence-photo" src="{{ $photoUrl }}" alt="Foto Clock Out" loading="lazy">
                                            <span class="attendance-evidence-photo-hint">Ketuk untuk perbesar</span>
                                        </button>
                                    @else
                                        <p class="attendance-evidence-empty">Foto tidak tersedia.</p>
                                    @endif
                                </section>

                                <section class="attendance-evidence-block">
                                    <h5>LOKASI</h5>
                                    @if($hasCoords)
                                        <p class="attendance-evidence-coordinates">{{ $coords }}</p>
                                        <iframe class="attendance-evidence-map"
                                            title="Peta lokasi Clock Out"
                                            loading="lazy"
                                            referrerpolicy="no-referrer-when-downgrade"
                                            data-src="https://www.google.com/maps?q={{ urlencode($coords) }}&z=16&output=embed"></iframe>
                                    @else
                                        <p class="attendance-evidence-empty">Lokasi tidak tersedia.</p>
                                    @endif
                                </section>
                            </div>
                        </section>
                    @empty
                        <p class="attendance-evidence-empty">Belum ada data absensi.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </template>
</article>
