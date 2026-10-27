<x-app title="Master Absensi">

    @php
        $hasDateFilter = request()->filled('date_start') || request()->filled('date_end');
        $hasAdvancedFilter = $hasDateFilter;
        $activeFilterCount = collect([
            $hasDateFilter ? 'date' : null,
        ])->filter()->count();
    @endphp

    <div class="attendance-filter-card">
        <form method="GET" action="{{ route('hr.attendances.index') }}" id="attendance-filter-form">
            @php
            $rangeValue = ($date_start ?? '') === ($date_end ?? '')
                ? ($date_start ?? '')
                : trim(($date_start ?? '').' to '.($date_end ?? ''));
            @endphp

            <div class="attendance-search-row">
                <div class="attendance-search-input-wrap">
                    <svg class="attendance-search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="search"
                        name="q"
                        value="{{ $q ?? '' }}"
                        placeholder="Cari nama karyawan..."
                        autocomplete="off"
                        class="attendance-search-input"
                        aria-label="Cari nama karyawan"
                        data-attendance-live-search>
                </div>

                @if(($q ?? null) || $hasAdvancedFilter)
                    <a href="{{ route('hr.attendances.index') }}" class="attendance-btn-reset">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reset
                    </a>
                @endif

                <button type="button" class="attendance-btn-toggle-filter {{ $hasAdvancedFilter ? 'active' : '' }}" onclick="toggleAttendanceFilterPanel()">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                    @if($hasAdvancedFilter)
                        <span class="attendance-filter-badge">{{ $activeFilterCount }}</span>
                    @endif
                </button>
            </div>

            <div class="attendance-filter-panel" id="attendance-filter-panel" style="{{ $hasAdvancedFilter ? '' : 'display: none;' }}">
                <div class="attendance-filter-grid">
                    <div class="attendance-filter-group">
                        <label class="attendance-filter-label">Tanggal</label>
                        <input type="text"
                            id="date_range"
                            name="date_range"
                            value="{{ $rangeValue }}"
                            placeholder="Pilih tanggal..."
                            autocomplete="off"
                            class="attendance-filter-input">
                        <input type="hidden" name="date_start" id="date_start" value="{{ $date_start ?? '' }}">
                        <input type="hidden" name="date_end" id="date_end" value="{{ $date_end ?? '' }}">
                    </div>

                    <div class="attendance-filter-actions">
                        <button type="submit" class="attendance-btn-apply">Terapkan Filter</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card attendance-results-card" data-attendance-results aria-live="polite" aria-busy="false">
        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th class="attendance-name-column">Nama Karyawan</th>
                        <th class="attendance-date-column">Tanggal</th>
                        <th class="attendance-time-column">Clock In</th>
                        <th class="attendance-time-column">Clock Out</th>
                        <th class="attendance-media-column">Bukti Kehadiran</th>
                        <th class="attendance-action-cell">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($items as $session)
                    @php
                        $actionEvents = $session->events;
                        $hasEvidence = $actionEvents->contains(fn ($event) => $event->photo || ($event->latitude !== null && $event->longitude !== null));
                        $thumbnailEvent = $actionEvents->first(fn ($event) => $event->photo) ?? $actionEvents->first();
                        $clockInEvidence = $session->clock_ins->map(fn ($event) => [
                            'time' => $event->occurred_at->format('H:i'),
                            'photo' => $event->photo ? asset('storage/'.$event->photo) : '',
                            'latitude' => $event->latitude ?? '',
                            'longitude' => $event->longitude ?? '',
                        ])->values();
                        $clockOutEvidence = $session->clock_outs->map(fn ($event) => [
                            'time' => $event->occurred_at->format('H:i'),
                            'photo' => $event->photo ? asset('storage/'.$event->photo) : '',
                            'latitude' => $event->latitude ?? '',
                            'longitude' => $event->longitude ?? '',
                        ])->values();
                    @endphp
                    <tr>
                        <td class="attendance-name-column">
                            <div class="user-info">
                                <span class="fw-bold">{{ $session->user->name }}</span>
                                <span class="user-attendance-location">{{ $session->attendance_location_scope }}</span>
                                <span class="user-work-schedule">{{ $session->work_schedule }}</span>
                            </div>
                        </td>
                        <td class="attendance-date-column">{{ $session->work_date->format('d/m/y') }}</td>
                        <td class="attendance-time attendance-time-column">
                            <div class="attendance-time-stack">
                                @forelse($session->clock_ins as $event)<span>{{ $event->occurred_at->format('H:i') }}</span>@empty<span>-</span>@endforelse
                            </div>
                        </td>
                        <td class="attendance-time attendance-time-column">
                            <div class="attendance-time-stack">
                                @forelse($session->clock_outs as $event)<span>{{ $event->occurred_at->format('H:i') }}</span>@empty<span>-</span>@endforelse
                            </div>
                        </td>
                        <td class="attendance-media-column">
                            @if($hasEvidence)
                            <button type="button" class="attendance-photo-trigger" data-attendance-evidence
                                data-attendance-evidence-name="{{ $session->user->name }}"
                                data-attendance-evidence-date="{{ $session->work_date->format('d/m/y') }}"
                                data-attendance-evidence-in="{{ json_encode($clockInEvidence) }}"
                                data-attendance-evidence-out="{{ json_encode($clockOutEvidence) }}"
                                aria-label="Lihat bukti kehadiran {{ $session->user->name }}">
                                @if($thumbnailEvent?->photo)
                                <img src="{{ asset('storage/'.$thumbnailEvent->photo) }}" alt="">
                                @else
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-5.25 7-11a7 7 0 10-14 0c0 5.75 7 11 7 11z"/><circle cx="12" cy="10" r="2"/></svg>
                                @endif
                            </button>
                            @else <span class="text-muted">-</span> @endif
                        </td>
                        <td class="attendance-action-cell">
                            @if($actionEvents->contains(fn ($event) => $event->is_flexible === true || $event->approval_status === 'PENDING'))
                            <details class="clock-side-move">
                                <summary aria-label="Opsi koreksi tipe event">⋮</summary>
                                <div class="clock-side-move__menu">
                                    @foreach($actionEvents as $event)
                                    @php($eventLabel = $event->event_type === App\Models\AttendanceEvent::CLOCK_IN ? 'Clock In' : 'Clock Out')
                                    @php($destinationLabel = $event->event_type === App\Models\AttendanceEvent::CLOCK_IN ? 'Clock Out' : 'Clock In')
                                    <div class="clock-side-move__event">
                                    <span class="clock-side-move__event-label">{{ $eventLabel }} · {{ $event->occurred_at->format('H:i') }}</span>
                                    @if($event->is_flexible === true)
                                    <button type="button" class="clock-side-move__button" data-attendance-detail
                                        data-attendance-detail-event="{{ $event->id }}"
                                        data-attendance-detail-name="{{ $session->user->name }}"
                                        data-attendance-detail-type="{{ $eventLabel }}"
                                        data-attendance-detail-time="{{ $event->occurred_at->format('H:i') }}"
                                        data-attendance-detail-date="{{ $event->occurred_at->format('d/m/y') }}"
                                        data-attendance-detail-notes="{{ $event->notes ?? '' }}"
                                        data-attendance-detail-photo="{{ $event->photo ? asset('storage/'.$event->photo) : '' }}"
                                        data-attendance-detail-latitude="{{ $event->latitude ?? '' }}"
                                        data-attendance-detail-longitude="{{ $event->longitude ?? '' }}">Detail</button>
                                    @endif
                                    @if($event->approval_status === 'PENDING')
                                    <button type="button" class="clock-side-move__button" data-attendance-move
                                        data-attendance-move-url="{{ route('hr.attendances.move_event_type', $event) }}"
                                        data-attendance-move-name="{{ $session->user->name }}"
                                        data-attendance-move-source="{{ $eventLabel }} pukul {{ $event->occurred_at->format('H:i') }}"
                                        data-attendance-move-destination="{{ $destinationLabel }}">Pindahkan ke {{ $destinationLabel }}</button>
                                    @endif
                                    </div>
                                    @endforeach
                                </div>
                            </details>
                            @else <span class="text-muted">-</span> @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">Tidak ada event absensi pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-modal id="attendance-evidence-modal" title="Bukti Kehadiran" type="info" variant="info" cancelLabel="Tutup">
        <div class="attendance-evidence-meta">
            <strong id="attendance-evidence-name"></strong>
            <span id="attendance-evidence-date"></span>
        </div>
        <div class="attendance-evidence-grid">
            @foreach(['in' => 'Clock In', 'out' => 'Clock Out'] as $side => $label)
            <section class="attendance-evidence-panel">
                <h4>{{ $label }}</h4>
                <div id="attendance-evidence-{{ $side }}-list" class="attendance-evidence-list"></div>
            </section>
            @endforeach
        </div>
    </x-modal>

    <x-modal id="moveClockSideModal" title="Pindahkan Bukti Absensi?" type="form" variant="warning">
        <p style="margin:0; color:#64748b;"><span id="moveClockSideSource" style="font-weight:700; color:#1e293b;"></span> milik <span id="moveClockSideUserName" style="font-weight:700; color:#1e293b;"></span> akan dipindahkan ke <span id="moveClockSideDestination" style="font-weight:700; color:#1e293b;"></span>.</p>
        <form id="moveClockSideForm" method="POST" style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
            @csrf
            <input id="moveClockSideInput" type="hidden" name="destination">
            <button type="button" class="modal-btn modal-btn-secondary" data-modal-close="true">Batal</button>
            <button type="submit" class="modal-btn modal-btn-primary">Ya, Pindahkan</button>
        </form>
    </x-modal>

    <x-modal id="attendance-detail-modal" title="Detail Absensi Fleksibel" type="info" variant="info" cancelLabel="Tutup">
        <dl class="attendance-detail-grid">
            <div><dt>Karyawan</dt><dd id="attendance-detail-name"></dd></div>
            <div><dt>Tipe</dt><dd id="attendance-detail-type"></dd></div>
            <div><dt>Jam</dt><dd id="attendance-detail-time"></dd></div>
            <div><dt>Tanggal</dt><dd id="attendance-detail-date"></dd></div>
            <div class="attendance-detail-grid__full"><dt>Alasan</dt><dd id="attendance-detail-notes"></dd></div>
        </dl>
        <div class="attendance-evidence-grid attendance-detail-evidence-grid">
            <section class="attendance-evidence-panel">
                <h4>Foto Kehadiran</h4>
                <img id="attendance-detail-photo" alt="Foto absensi" hidden>
                <p id="attendance-detail-photo-empty" class="attendance-evidence-empty">Foto tidak tersedia.</p>
            </section>
            <section class="attendance-evidence-panel">
                <h4>Lokasi Kehadiran</h4>
                <p id="attendance-detail-coordinates" class="attendance-evidence-coordinates" hidden></p>
                <iframe id="attendance-detail-map" title="Peta lokasi kehadiran" loading="lazy" referrerpolicy="no-referrer-when-downgrade" hidden></iframe>
                <p id="attendance-detail-location-empty" class="attendance-evidence-empty">Lokasi tidak tersedia.</p>
            </section>
        </div>
    </x-modal>

    <style>
        /* --- UTILITY --- */
        .mb-4 { margin-bottom: 16px; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: 700; color: #111827; }
        .text-muted { color: #9ca3af; font-size: 13px; font-style: italic; }
        .text-small { font-size: 13px; color: #4b5563; }

        /* --- CARD --- */
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid #f3f4f6;
            overflow: hidden;
            padding: 0;
        }

        /* --- FILTER SECTION --- */
        .attendance-filter-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            margin-bottom: 16px;
            overflow: hidden;
        }

        .attendance-search-row {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 14px 16px;
        }

        .attendance-search-input-wrap {
            position: relative;
            flex: 1;
        }

        .attendance-search-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            color: #9ca3af;
            pointer-events: none;
            transform: translateY(-50%);
        }

        .attendance-search-input,
        .attendance-filter-input {
            width: 100%;
            color: #111827;
            outline: none;
            font-family: inherit;
            transition: all 0.2s ease;
            background: #fff;
            border: 1.5px solid #d1d5db;
        }

        .attendance-search-input {
            padding: 10px 14px 10px 42px;
            border-radius: 12px;
            font-size: 14px;
        }

        .attendance-filter-input {
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 13px;
        }

        .attendance-search-input:focus,
        .attendance-filter-input:focus {
            border-color: #145da0;
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }

        .attendance-btn-reset,
        .attendance-btn-toggle-filter,
        .attendance-btn-apply {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .attendance-btn-reset,
        .attendance-btn-toggle-filter {
            width: 100%;
            padding: 10px 14px;
            color: #6b7280;
            background: #fff;
            border: 1.5px solid #d1d5db;
            border-radius: 12px;
        }

        .attendance-btn-reset {
            text-decoration: none;
        }

        .attendance-btn-reset:hover {
            color: #dc2626;
            background: #fef2f2;
            border-color: #fecaca;
        }

        .attendance-btn-toggle-filter {
            cursor: pointer;
        }

        .attendance-btn-toggle-filter:hover {
            color: #145da0;
            border-color: #145da0;
        }

        .attendance-btn-toggle-filter.active,
        .attendance-btn-apply {
            color: #fff;
            background: #145da0;
            border-color: #145da0;
        }

        .attendance-filter-badge {
            min-width: 18px;
            padding: 2px 6px;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            text-align: center;
            background: #dc2626;
            border-radius: 10px;
        }

        .attendance-btn-toggle-filter.active .attendance-filter-badge {
            background: rgba(255, 255, 255, 0.3);
        }

        .attendance-filter-panel {
            padding: 14px 16px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            animation: attendance-filter-slide-down 0.2s ease;
        }

        @keyframes attendance-filter-slide-down {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .attendance-filter-grid {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .attendance-filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .attendance-filter-label {
            color: #6b7280;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .attendance-filter-actions {
            display: flex;
            justify-content: flex-end;
        }

        .attendance-btn-apply {
            padding: 10px 20px;
            cursor: pointer;
            border: 1px solid #145da0;
            border-radius: 10px;
            font-weight: 600;
        }

        .attendance-btn-apply:hover {
            background: #0d4a83;
        }

        .attendance-results-card.is-loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* --- TABLE --- */
        .table-wrapper { width: 100%; overflow-x: auto; }
        .custom-table { width: 100%; border-collapse: collapse; table-layout: fixed; min-width: 760px; }

        .custom-table th {
            background: #f9fafb;
            padding: 11px 14px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e5e7eb;
        }

        .custom-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 12px;
            color: #1f2937;
            vertical-align: middle;
        }

        .custom-table tr:last-child td { border-bottom: none; }
        .custom-table tr:hover td { background: #fdfdfd; }

        /* --- CUSTOM COLUMNS --- */
        .attendance-name-column { width: 36%; }
        .attendance-time-column { width: 82px; }
        .attendance-date-column { width: 92px; }
        .attendance-media-column { width: 96px; text-align: center !important; }
        .attendance-photo-trigger {
            display: inline-flex;
            width: 40px;
            height: 40px;
            padding: 0;
            overflow: hidden;
            vertical-align: middle;
            cursor: pointer;
            background: #f8fafc;
            border: 1px solid #dbeafe;
            border-radius: 10px;
        }
        .attendance-photo-trigger img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .attendance-photo-trigger:hover { border-color: #145da0; }
        .attendance-photo-trigger:focus-visible {
            outline: 2px solid #145da0;
            outline-offset: 2px;
        }
        .user-info { display: flex; flex-direction: column; gap: 3px; min-width: 150px; }
        .user-attendance-location { color: #64748b; font-size: 11px; font-weight: 600; }
        .user-work-schedule { color: #64748b; font-size: 11px; font-weight: 600; }
        .attendance-type-chip { display: inline-flex; align-items: center; justify-content: center; min-width: 76px; padding: 4px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; white-space: nowrap; }
        .attendance-type-chip--in { color: #176b42; background: #e9f8ef; }
        .attendance-type-chip--out { color: #a33f48; background: #fcebed; }
        .attendance-time { font-weight: 600; white-space: nowrap; }
        .attendance-time-stack { display: grid; gap: 8px; min-height: 20px; }
        .attendance-late { color: #b45309 !important; font-weight: 700; white-space: nowrap; }
        .attendance-action-cell { width: 54px; text-align: center !important; }
        .time-block { display: flex; flex-direction: column; line-height: 1.2; }
        .time-clock { font-weight: 600; color: #111827; }
        .clock-side-cell { display: inline-flex; align-items: center; gap: 4px; }
        .clock-side-move { position: relative; display: inline-flex; }
        .clock-side-move summary { display: inline-flex; width: 22px; height: 22px; align-items: center; justify-content: center; border-radius: 6px; color: #64748b; cursor: pointer; font-size: 17px; font-weight: 700; line-height: 1; list-style: none; }
        .clock-side-move summary::-webkit-details-marker { display: none; }
        .clock-side-move summary:hover, .clock-side-move summary:focus-visible { background: #f1f5f9; color: #145da0; outline: none; }
        .clock-side-move__menu { position: absolute; z-index: 20; top: 50%; right: calc(100% + 6px); left: auto; width: 220px; padding: 4px; transform: translateY(-50%); border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; box-shadow: 0 8px 20px rgba(15, 23, 42, .12); }
        .clock-side-move__event { padding: 6px; border-top: 1px solid #f1f5f9; }
        .clock-side-move__event:first-child { border-top: 0; }
        .clock-side-move__event-label { display: block; padding: 2px 4px 5px; color: #64748b; font-size: 11px; font-weight: 700; }
        .clock-side-move__button { border: 0; border-radius: 6px; background: transparent; color: #334155; padding: 8px 10px; font: inherit; font-size: .75rem; font-weight: 600; white-space: nowrap; cursor: pointer; }
        .clock-side-move__button:hover, .clock-side-move__button:focus-visible { background: #eff6ff; color: #145da0; outline: none; }

        .badge-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .bg-green { background: #dcfce7; color: #166534; }
        .bg-red { background: #fee2e2; color: #991b1b; }
        .bg-gray { background: #f3f4f6; color: #4b5563; }
        .bg-purple { background: #f3e8ff; color: #6b21a8; } /* Style untuk Dinas Luar */
        .bg-yellow { background: #fef3c7; color: #92400e; }

        .badge-late {
            color: #b91c1c;
            font-weight: 600;
            font-size: 12px;
            background: #fef2f2;
            padding: 2px 6px;
            border-radius: 4px;
        }

        #attendance-evidence-modal .modal-card { max-width: 1080px; }
        #attendance-detail-modal .modal-card { max-width: 1080px; }
        .attendance-evidence-meta { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; color: #111827; }
        .attendance-evidence-meta span { padding: 3px 8px; color: #176b42; font-size: 11px; font-weight: 700; background: #e9f8ef; border-radius: 999px; }
        .attendance-evidence-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .attendance-evidence-panel { min-width: 0; padding: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }
        .attendance-evidence-panel h4 { margin: 0 0 10px; color: #334155; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
        .attendance-evidence-list { display: grid; gap: 12px; }
        .attendance-evidence-entry { padding-top: 12px; border-top: 1px solid #e2e8f0; }
        .attendance-evidence-entry:first-child { padding-top: 0; border-top: 0; }
        .attendance-evidence-entry-time { margin: 0 0 8px; color: #176b42; font-size: 12px; font-weight: 700; }
        .attendance-evidence-photo,
        #attendance-detail-photo { display: block; width: 100%; height: min(58vh, 520px); object-fit: contain; border-radius: 8px; background: #e2e8f0; }
        .attendance-evidence-map,
        #attendance-detail-map { display: block; width: 100%; height: min(58vh, 520px); border: 0; border-radius: 8px; background: #e2e8f0; }
        .attendance-evidence-coordinates { margin: -2px 0 10px; color: #64748b; font-family: monospace; font-size: 12px; }
        .attendance-evidence-empty { display: flex; align-items: center; justify-content: center; min-height: 180px; margin: 0; color: #94a3b8; font-size: 13px; font-style: italic; text-align: center; }
        #attendance-evidence-modal [hidden],
        #attendance-detail-modal [hidden] { display: none !important; }
        .attendance-detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin: 0 0 16px; }
        .attendance-detail-grid div { min-width: 0; }
        .attendance-detail-grid dt { color: #64748b; font-size: 11px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
        .attendance-detail-grid dd { margin: 3px 0 0; color: #1f2937; font-size: 14px; font-weight: 600; overflow-wrap: anywhere; white-space: pre-wrap; }
        .attendance-detail-grid__full { grid-column: 1 / -1; }

        .empty-state { padding: 40px; text-align: center; color: #9ca3af; font-style: italic; }

        @media (min-width: 600px) {
            .attendance-search-row {
                flex-direction: row;
                align-items: center;
                flex-wrap: wrap;
                padding: 16px 20px;
            }

            .attendance-search-input-wrap {
                min-width: 260px;
            }

            .attendance-btn-reset,
            .attendance-btn-toggle-filter {
                flex-shrink: 0;
                width: auto;
            }

            .attendance-filter-panel {
                padding: 16px 20px;
            }

            .attendance-filter-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 14px 16px;
                align-items: end;
            }

            .attendance-filter-actions {
                grid-column: 1 / -1;
            }
        }

        @media (min-width: 768px) {
            .attendance-filter-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 767px) {
            .attendance-evidence-grid { grid-template-columns: 1fr; }
            #attendance-evidence-photo,
            #attendance-detail-photo,
            #attendance-evidence-map,
            #attendance-detail-map { height: min(48vh, 360px); }
        }
    </style>

    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.css') }}">
    <script src="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.js') }}"></script>
    <script>
        (function () {
            const form = document.getElementById('attendance-filter-form');
            const input = form?.querySelector('[data-attendance-live-search]');
            const results = document.querySelector('[data-attendance-results]');
            let timer;
            let request;

            if (!form || !input || !results) return;

            window.refreshAttendanceResults = async function() {
                const url = new URL(form.action, window.location.origin);
                const params = new URLSearchParams(new FormData(form));
                params.delete('page');
                url.search = params.toString();

                request?.abort();
                const currentRequest = new AbortController();
                request = currentRequest;
                results.classList.add('is-loading');
                results.setAttribute('aria-busy', 'true');

                try {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: currentRequest.signal,
                    });
                    if (!response.ok) throw new Error('Pencarian absensi gagal');

                    const documentResult = new DOMParser().parseFromString(await response.text(), 'text/html');
                    const nextResults = documentResult.querySelector('[data-attendance-results]');
                    if (!nextResults) throw new Error('Hasil pencarian tidak lengkap');
                    results.innerHTML = nextResults.innerHTML;

                    initializeAttendanceEvidenceButtons();
                    initializeAttendanceDetailButtons();
                    initializeAttendanceMoveButtons();
                    window.history.replaceState(null, '', url);
                } catch (error) {
                    if (error.name !== 'AbortError') console.error(error);
                } finally {
                    if (request === currentRequest) {
                        results.classList.remove('is-loading');
                        results.setAttribute('aria-busy', 'false');
                    }
                }
            };

            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(window.refreshAttendanceResults, 350);
            });
        })();

        function toggleAttendanceFilterPanel() {
            const panel = document.getElementById('attendance-filter-panel');
            const button = document.querySelector('.attendance-btn-toggle-filter');

            if (panel.style.display === 'none') {
                panel.style.display = '';
                button.classList.add('active');
            } else {
                panel.style.display = 'none';
                button.classList.remove('active');
            }
        }

        function closeOtherAttendanceActionMenus(currentMenu = null) {
            document.querySelectorAll('details.clock-side-move[open]').forEach(function(menu) {
                if (menu !== currentMenu) menu.removeAttribute('open');
            });
        }

        document.addEventListener('toggle', function(event) {
            const menu = event.target;

            if (!(menu instanceof HTMLDetailsElement) || !menu.classList.contains('clock-side-move') || !menu.open) return;

            closeOtherAttendanceActionMenus(menu);
        }, true);

        document.addEventListener('click', function(event) {
            if (!event.target.closest('details.clock-side-move')) closeOtherAttendanceActionMenus();
        });

        function openMoveClockSideModal(url, name, source, destinationLabel) {
            document.getElementById('moveClockSideForm').action = url;
            document.getElementById('moveClockSideInput').value = destinationLabel;
            document.getElementById('moveClockSideUserName').innerText = name;
            document.getElementById('moveClockSideSource').innerText = source;
            document.getElementById('moveClockSideDestination').innerText = destinationLabel;
            closeOtherAttendanceActionMenus();
            document.getElementById('moveClockSideModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function initializeAttendanceMoveButtons() {
            document.querySelectorAll('[data-attendance-move]').forEach(function(button) {
                if (button.dataset.attendanceMoveInitialized) return;

                button.dataset.attendanceMoveInitialized = 'true';
                button.addEventListener('click', function() {
                    openMoveClockSideModal(
                        this.dataset.attendanceMoveUrl,
                        this.dataset.attendanceMoveName,
                        this.dataset.attendanceMoveSource,
                        this.dataset.attendanceMoveDestination,
                    );
                });
            });
        }

        function initializeAttendanceEvidenceButtons() {
            const modal = document.getElementById('attendance-evidence-modal');

            document.querySelectorAll('[data-attendance-evidence]').forEach(function(button) {
                if (button.dataset.attendanceEvidenceInitialized) return;

                button.dataset.attendanceEvidenceInitialized = 'true';
                button.addEventListener('click', function() {
                    document.getElementById('attendance-evidence-name').textContent = this.dataset.attendanceEvidenceName;
                    document.getElementById('attendance-evidence-date').textContent = this.dataset.attendanceEvidenceDate;

                    ['in', 'out'].forEach(function(side) {
                        const key = `attendanceEvidence${side === 'in' ? 'In' : 'Out'}`;
                        renderAttendanceEvidenceEntries(side, JSON.parse(this.dataset[key] || '[]'));
                    }, this);

                    closeOtherAttendanceActionMenus();
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                });
            });
        }

        function renderAttendanceEvidenceEntries(side, entries) {
            const list = document.getElementById(`attendance-evidence-${side}-list`);
            list.replaceChildren();

            if (!entries.length) {
                const empty = document.createElement('p');
                empty.className = 'attendance-evidence-empty';
                empty.textContent = 'Belum ada data absensi.';
                list.append(empty);

                return;
            }

            entries.forEach(function(entry) {
                const wrapper = document.createElement('section');
                wrapper.className = 'attendance-evidence-entry';
                const time = document.createElement('p');
                time.className = 'attendance-evidence-entry-time';
                time.textContent = entry.time;
                const evidence = document.createElement('div');
                evidence.className = 'attendance-evidence-grid';

                const photo = document.createElement('section');
                const photoTitle = document.createElement('h4');
                photoTitle.textContent = 'Foto';
                photo.append(photoTitle);
                if (entry.photo) {
                    const image = document.createElement('img');
                    image.className = 'attendance-evidence-photo';
                    image.src = entry.photo;
                    image.alt = 'Foto kehadiran';
                    photo.append(image);
                } else {
                    const empty = document.createElement('p');
                    empty.className = 'attendance-evidence-empty';
                    empty.textContent = 'Foto tidak tersedia.';
                    photo.append(empty);
                }

                const location = document.createElement('section');
                const locationTitle = document.createElement('h4');
                locationTitle.textContent = 'Lokasi';
                location.append(locationTitle);
                if (entry.latitude && entry.longitude) {
                    const coordinates = `${entry.latitude}, ${entry.longitude}`;
                    const label = document.createElement('p');
                    label.className = 'attendance-evidence-coordinates';
                    label.textContent = coordinates;
                    const map = document.createElement('iframe');
                    map.className = 'attendance-evidence-map';
                    map.title = 'Peta lokasi kehadiran';
                    map.loading = 'lazy';
                    map.referrerPolicy = 'no-referrer-when-downgrade';
                    map.src = `https://www.google.com/maps?q=${encodeURIComponent(coordinates)}&z=16&output=embed`;
                    location.append(label, map);
                } else {
                    const empty = document.createElement('p');
                    empty.className = 'attendance-evidence-empty';
                    empty.textContent = 'Lokasi tidak tersedia.';
                    location.append(empty);
                }

                evidence.append(photo, location);
                wrapper.append(time, evidence);
                list.append(wrapper);
            });
        }

        function initializeAttendanceDetailButtons() {
            const modal = document.getElementById('attendance-detail-modal');
            const fields = {
                name: document.getElementById('attendance-detail-name'),
                type: document.getElementById('attendance-detail-type'),
                time: document.getElementById('attendance-detail-time'),
                date: document.getElementById('attendance-detail-date'),
                notes: document.getElementById('attendance-detail-notes'),
                photo: document.getElementById('attendance-detail-photo'),
                photoEmpty: document.getElementById('attendance-detail-photo-empty'),
                coordinates: document.getElementById('attendance-detail-coordinates'),
                map: document.getElementById('attendance-detail-map'),
                locationEmpty: document.getElementById('attendance-detail-location-empty'),
            };

            document.querySelectorAll('[data-attendance-detail]').forEach(function(button) {
                if (button.dataset.attendanceDetailInitialized) return;

                button.dataset.attendanceDetailInitialized = 'true';
                button.addEventListener('click', function() {
                    fields.name.textContent = this.dataset.attendanceDetailName;
                    fields.type.textContent = this.dataset.attendanceDetailType;
                    fields.time.textContent = this.dataset.attendanceDetailTime;
                    fields.date.textContent = this.dataset.attendanceDetailDate;
                    fields.notes.textContent = this.dataset.attendanceDetailNotes || 'Alasan tidak tersedia';

                    if (this.dataset.attendanceDetailPhoto) {
                        fields.photo.src = this.dataset.attendanceDetailPhoto;
                        fields.photo.hidden = false;
                        fields.photoEmpty.hidden = true;
                    } else {
                        fields.photo.removeAttribute('src');
                        fields.photo.hidden = true;
                        fields.photoEmpty.hidden = false;
                    }

                    if (this.dataset.attendanceDetailLatitude && this.dataset.attendanceDetailLongitude) {
                        const coordinates = `${this.dataset.attendanceDetailLatitude}, ${this.dataset.attendanceDetailLongitude}`;
                        fields.coordinates.textContent = coordinates;
                        fields.coordinates.hidden = false;
                        fields.map.src = `https://www.google.com/maps?q=${encodeURIComponent(coordinates)}&z=16&output=embed`;
                        fields.map.hidden = false;
                        fields.locationEmpty.hidden = true;
                    } else {
                        fields.coordinates.hidden = true;
                        fields.map.removeAttribute('src');
                        fields.map.hidden = true;
                        fields.locationEmpty.hidden = false;
                    }
                    closeOtherAttendanceActionMenus();
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // --- Flatpickr Logic ---
            const rangeInput = document.getElementById('date_range');
            const startHidden = document.getElementById('date_start');
            const endHidden = document.getElementById('date_end');

            if (typeof flatpickr === 'function' && rangeInput) {
                flatpickr(rangeInput, {
                    mode: "range",
                    dateFormat: "Y-m-d",
                    allowInput: true,
                    onChange: function(selectedDates) {
                        if (!selectedDates.length) {
                            startHidden.value = "";
                            endHidden.value = "";
                            return;
                        }
                        startHidden.value = flatpickr.formatDate(selectedDates[0], "Y-m-d");
                        endHidden.value = flatpickr.formatDate(selectedDates[selectedDates.length - 1], "Y-m-d");
                    }
                });
            }

            initializeAttendanceEvidenceButtons();
            initializeAttendanceDetailButtons();
            initializeAttendanceMoveButtons();

            const moveForm = document.getElementById('moveClockSideForm');
            moveForm?.addEventListener('submit', async function(event) {
                event.preventDefault();

                const submitButton = moveForm.querySelector('button[type="submit"]');
                submitButton.disabled = true;

                try {
                    const response = await fetch(moveForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(moveForm),
                    });
                    const payload = await response.json();
                    if (!response.ok) throw new Error(payload.message || 'Perubahan tipe event gagal.');

                    document.getElementById('moveClockSideModal').style.display = 'none';
                    document.body.style.overflow = '';
                    await window.refreshAttendanceResults?.();
                } catch (error) {
                    window.alert(error.message || 'Perubahan tipe event gagal.');
                } finally {
                    submitButton.disabled = false;
                }
            });
        });
    </script>

</x-app>
