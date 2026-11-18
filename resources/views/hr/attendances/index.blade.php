<x-app :title="auth()->user()->isHR() ? 'Master Absensi' : 'Absensi Tim'">

    @if(auth()->user()->isHR())
        <div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
            <a href="{{ route('hr.attendance-access.index') }}" class="attendance-access-link">Atur Akses Absensi</a>
        </div>
    @endif

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

                <a href="{{ route('hr.attendances.index') }}" id="attendance-reset" class="attendance-btn-reset" @if(! (($q ?? null) || $hasAdvancedFilter)) hidden @endif>
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Reset
                </a>

                <button type="button" class="attendance-btn-toggle-filter {{ $hasAdvancedFilter ? 'active' : '' }}" onclick="toggleAttendanceFilterPanel()">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                    <span id="attendance-filter-badge" class="attendance-filter-badge" @unless($hasAdvancedFilter) hidden @endunless>{{ $activeFilterCount }}</span>
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
                        $hasEvidence = $actionEvents->contains(fn ($event) => $event->is_flexible === true || $event->notes || $event->hrd_note || ($event->photo && ! $event->photo_deleted_at) || ($event->latitude !== null && $event->longitude !== null));
                        $thumbnailEvent = $actionEvents->first(fn ($event) => $event->photo && ! $event->photo_deleted_at);
                        $clockInEvidence = $session->clock_ins->map(fn ($event) => [
                            'time' => $event->local_occurred_at->format('H:i'),
                            'date' => $event->local_occurred_at->format('d/m/y'),
                            'type' => $event->type,
                            'is_flexible' => $event->is_flexible === true,
                            'notes' => $event->notes ?? '',
                            'hrd_note' => $event->hrd_note ?? '',
                            'photo' => $event->photo && ! $event->photo_deleted_at ? asset('storage/'.$event->photo) : '',
                            'latitude' => $event->latitude ?? '',
                            'longitude' => $event->longitude ?? '',
                        ])->values();
                        $clockOutEvidence = $session->clock_outs->map(fn ($event) => [
                            'time' => $event->local_occurred_at->format('H:i'),
                            'date' => $event->local_occurred_at->format('d/m/y'),
                            'type' => $event->type,
                            'is_flexible' => $event->is_flexible === true,
                            'notes' => $event->notes ?? '',
                            'hrd_note' => $event->hrd_note ?? '',
                            'photo' => $event->photo && ! $event->photo_deleted_at ? asset('storage/'.$event->photo) : '',
                            'latitude' => $event->latitude ?? '',
                            'longitude' => $event->longitude ?? '',
                        ])->values();
                    @endphp
                    <tr>
                        <td class="attendance-name-column">
                            <div class="user-info">
                                <span class="fw-bold">{{ $session->user->name }}</span>
                                <span class="user-attendance-location">{{ $session->attendance_location_scope }}</span>
                                <span class="user-work-schedule">{{ $session->work_schedule }}
                                    @if(auth()->user()->isHR() && $actionEvents->isNotEmpty())
                                    <button type="button" class="schedule-snapshot-edit" data-attendance-snapshot
                                        title="Edit Snapshot Jadwal" aria-label="Edit Snapshot Jadwal {{ $session->user->name }}"
                                        data-attendance-snapshot-url="{{ route('hr.attendances.schedule_snapshot', $actionEvents->first()) }}"
                                        data-attendance-snapshot-save-url="{{ route('hr.attendances.schedule_snapshot.update', $actionEvents->first()) }}"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg></button>
                                    @endif
                                </span>
                            </div>
                        </td>
                        <td class="attendance-date-column">{{ $session->work_date->format('d/m/y') }}</td>
                        <td class="attendance-time attendance-time-column">
                            <div class="attendance-time-stack">
                                @forelse($session->clock_ins as $event)<span>{{ $event->local_occurred_at->format('H:i') }}</span>@empty<span>-</span>@endforelse
                            </div>
                        </td>
                        <td class="attendance-time attendance-time-column">
                            <div class="attendance-time-stack">
                                @forelse($session->clock_outs as $event)<span>{{ $event->local_occurred_at->format('H:i') }}</span>@empty<span>-</span>@endforelse
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
                            @if(auth()->user()->isHR() && $actionEvents->isNotEmpty())
                            <details class="clock-side-move">
                                <summary aria-label="Opsi koreksi tipe event">⋮</summary>
                                <div class="clock-side-move__menu">
                                    @foreach($actionEvents as $event)
                                    @php($isClockIn = $event->event_type === App\Models\AttendanceEvent::CLOCK_IN)
                                    @php($eventLabel = $isClockIn ? 'Clock In' : 'Clock Out')
                                    @php($destinationLabel = $isClockIn ? 'Clock Out' : 'Clock In')
                                    <div class="clock-side-move__event">
                                    <span class="clock-side-move__event-label">{{ $eventLabel }} · {{ $event->local_occurred_at->format('H:i') }}</span>
                                    <button type="button" class="clock-side-move__button" data-attendance-move
                                        data-attendance-move-url="{{ route('hr.attendances.move_event_type', $event) }}"
                                        data-attendance-move-name="{{ $session->user->name }}"
                                        data-attendance-move-source="{{ $eventLabel }} pukul {{ $event->local_occurred_at->format('H:i') }} tanggal {{ $event->local_occurred_at->format('d/m/Y') }}"
                                        data-attendance-move-from="{{ $event->event_type }}"
                                        data-attendance-move-to="{{ $isClockIn ? App\Models\AttendanceEvent::CLOCK_OUT : App\Models\AttendanceEvent::CLOCK_IN }}"
                                        data-attendance-move-destination="{{ $destinationLabel }}">Pindahkan ke {{ $destinationLabel }}</button>
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
        <p style="margin:0; color:#64748b;"><span id="moveClockSideSource" style="font-weight:700; color:#1e293b;"></span> milik <span id="moveClockSideUserName" style="font-weight:700; color:#1e293b;"></span> akan dipindahkan ke <span id="moveClockSideDestination" style="font-weight:700; color:#1e293b;"></span>. Waktu pencatatan tidak berubah.</p>
        <form id="moveClockSideForm" method="POST" style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
            @csrf
            <input id="moveClockSideInput" type="hidden" name="event_type">
            <input id="moveClockSideFromInput" type="hidden" name="from_event_type">
            <button type="button" class="modal-btn modal-btn-secondary" data-modal-close="true">Batal</button>
            <button type="submit" class="modal-btn modal-btn-primary">Ya, Pindahkan</button>
        </form>
    </x-modal>

    <x-modal id="scheduleSnapshotModal" title="Koreksi Jadwal Absensi" type="form" variant="warning">
        <div id="scheduleSnapshotBody" style="font-size:13px; color:#334155; max-height:60vh; overflow:auto;"></div>
        <form id="scheduleSnapshotForm" method="POST" style="margin-top:14px;">
            @csrf
            <input type="hidden" id="scheduleSnapshotSourceId" name="source_schedule_id">
            <input type="hidden" id="scheduleSnapshotSourceFingerprint" name="source_fingerprint">
            <div id="scheduleSnapshotWorkSchedule" style="margin-bottom:10px; padding:10px; border:1px dashed #cbd5e1; border-radius:8px; font-size:13px;"></div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                <label style="font-size:12px; font-weight:700;">Jam mulai
                    <input id="scheduleSnapshotStart" type="time" name="start_time" style="display:block; width:100%; margin-top:4px; border:1px solid #cbd5e1; border-radius:8px; padding:8px; font:inherit;">
                </label>
                <label style="font-size:12px; font-weight:700;">Jam selesai
                    <input id="scheduleSnapshotEnd" type="time" name="end_time" style="display:block; width:100%; margin-top:4px; border:1px solid #cbd5e1; border-radius:8px; padding:8px; font:inherit;">
                </label>
                <label style="font-size:12px; font-weight:700;">Zona waktu
                    <select id="scheduleSnapshotWita" name="is_wita" required style="display:block; width:100%; margin-top:4px; border:1px solid #cbd5e1; border-radius:8px; padding:8px; font:inherit;">
                        <option value="">Pilih…</option><option value="0">WIB</option><option value="1">WITA</option>
                    </select>
                </label>
                <label style="font-size:12px; font-weight:700;">Lintas hari
                    <select id="scheduleSnapshotOvernight" name="is_overnight" required style="display:block; width:100%; margin-top:4px; border:1px solid #cbd5e1; border-radius:8px; padding:8px; font:inherit;">
                        <option value="">Pilih…</option><option value="0">Tidak</option><option value="1">Ya (selesai hari berikutnya)</option>
                    </select>
                </label>
                <label style="font-size:12px; font-weight:700;">Hari libur
                    <select id="scheduleSnapshotHoliday" name="is_holiday" required style="display:block; width:100%; margin-top:4px; border:1px solid #cbd5e1; border-radius:8px; padding:8px; font:inherit;">
                        <option value="">Pilih…</option><option value="0">Tidak</option><option value="1">Ya</option>
                    </select>
                </label>
                <label style="font-size:12px; font-weight:700;">Nama shift
                    <input id="scheduleSnapshotShiftName" type="text" name="shift_name" maxlength="100" style="display:block; width:100%; margin-top:4px; border:1px solid #cbd5e1; border-radius:8px; padding:8px; font:inherit;">
                </label>
            </div>
            <label for="scheduleSnapshotReason" style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Alasan koreksi (wajib)</label>
            <textarea id="scheduleSnapshotReason" name="reason" rows="2" maxlength="500" required style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:8px; font:inherit;"></textarea>
            <p id="scheduleSnapshotError" role="alert" style="display:none; color:#b91c1c; font-size:12px; margin:8px 0 0;"></p>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                <button type="button" class="modal-btn modal-btn-secondary" data-modal-close="true">Batal</button>
                <button type="submit" id="scheduleSnapshotSubmit" class="modal-btn modal-btn-primary" disabled>Simpan Koreksi</button>
            </div>
        </form>
    </x-modal>

    @push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.css') }}">
    <style>
        .attendance-access-link { display:inline-flex; padding:10px 14px; border:1px solid #bfdbfe; border-radius:10px; background:#fff; color:#145da0; font-size:13px; font-weight:600; text-decoration:none; }
        .attendance-access-link:hover { background:#eff6ff; }
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
        .schedule-snapshot-edit { display: inline-flex; width: 28px; height: 28px; margin: -6px 0 -6px 2px; align-items: center; justify-content: center; border: 0; border-radius: 6px; background: transparent; color: #64748b; cursor: pointer; vertical-align: middle; }
        .schedule-snapshot-edit:hover, .schedule-snapshot-edit:focus-visible { background: #eff6ff; color: #145da0; outline: 2px solid #145da0; outline-offset: 1px; }
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

        #attendance-evidence-modal .modal-card { max-width: 1040px; width: 100%; }
        .attendance-evidence-meta { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; color: #111827; }
        .attendance-evidence-meta strong { font-size: 1rem; font-weight: 800; color: #111827; }
        .attendance-evidence-meta span { padding: 3px 10px; color: #176b42; font-size: 11px; font-weight: 700; background: #e9f8ef; border-radius: 999px; }
        .attendance-evidence-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .attendance-evidence-panel { min-width: 0; padding: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }
        .attendance-evidence-panel h4 { margin: 0 0 10px; color: #334155; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
        .attendance-evidence-list { display: grid; gap: 14px; }
        .attendance-evidence-entry { padding-top: 14px; border-top: 1px solid #e2e8f0; }
        .attendance-evidence-entry:first-child { padding-top: 0; border-top: 0; }
        .attendance-evidence-entry-time { margin: 0 0 8px; color: #176b42; font-size: 12px; font-weight: 700; }
        .attendance-evidence-subgrid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .attendance-evidence-block h5 { margin: 0 0 6px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em; }
        .attendance-evidence-photo-btn { display: block; position: relative; width: 100%; padding: 0; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #e2e8f0; cursor: pointer; text-align: left; }
        .attendance-evidence-photo-btn:focus-visible { outline: 2px solid #145da0; outline-offset: 2px; }
        .attendance-evidence-photo { display: block; width: 100%; height: min(30vh, 220px); object-fit: cover; }
        .attendance-evidence-photo-hint { position: absolute; bottom: 6px; right: 6px; background: rgba(15, 23, 42, 0.78); color: #fff; font-size: 10px; font-weight: 600; padding: 2px 7px; border-radius: 4px; pointer-events: none; }
        .attendance-evidence-map { display: block; width: 100%; height: min(30vh, 220px); border: 0; border-radius: 8px; background: #e2e8f0; }
        .attendance-evidence-coordinates { margin: -2px 0 6px; color: #64748b; font-family: monospace; font-size: 11px; word-break: break-all; }
        .evidence-maps-button { display: inline-block; padding: 4px 8px; border: 1px solid #2563eb; border-radius: 6px; color: #2563eb; font: 600 12px sans-serif; text-decoration: none; }
        .attendance-evidence-photo-spacer { visibility: hidden; }
        .attendance-evidence-empty { display: flex; align-items: center; justify-content: center; min-height: 120px; margin: 0; color: #94a3b8; font-size: 13px; font-style: italic; text-align: center; }
        .attendance-evidence-notes { margin: 0 0 10px; color: #334155; font-size: 12px; overflow-wrap: anywhere; white-space: pre-wrap; background: #fff; padding: 8px 10px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .attendance-evidence-notes--hrd { background: #fffbeb; border-color: #fde68a; color: #78350f; border-left: 3px solid #d97706; }

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
            .attendance-evidence-grid { grid-template-columns: 1fr; gap: 14px; }
            .attendance-evidence-subgrid { grid-template-columns: 1fr; gap: 10px; }
            .attendance-evidence-photo-spacer { display: none; }
            .attendance-evidence-photo,
            .attendance-evidence-map { height: 180px; }
        }
    </style>
    @endpush

    @include('hr._master_ajax')
    <script src="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.js') }}"></script>
    <script>
        (function () {
            const ajax = MasterAjax.init({
                form: '#attendance-filter-form',
                search: '[data-attendance-live-search]',
                regions: ['[data-attendance-results]'],
                reset: '#attendance-reset',
                sync: ['#attendance-reset', '#attendance-filter-badge'],
                onUpdate() {
                    initializeAttendanceEvidenceButtons();
                    initializeAttendanceMoveButtons();
                    const panel = document.getElementById('attendance-filter-panel');
                    document.querySelector('.attendance-btn-toggle-filter')
                        ?.classList.toggle('active', panel.style.display !== 'none' || !document.getElementById('attendance-filter-badge').hidden);
                },
            });

            // Dipakai setelah koreksi berhasil: muat ulang URL aktif (filter tetap).
            window.refreshAttendanceResults = () => ajax?.refresh();
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

        function openMoveClockSideModal(url, name, source, destinationLabel, fromType, toType) {
            document.getElementById('moveClockSideForm').action = url;
            document.getElementById('moveClockSideInput').value = toType;
            document.getElementById('moveClockSideFromInput').value = fromType;
            document.getElementById('moveClockSideUserName').innerText = name;
            document.getElementById('moveClockSideSource').innerText = source;
            document.getElementById('moveClockSideDestination').innerText = destinationLabel;
            closeOtherAttendanceActionMenus();
            document.getElementById('moveClockSideModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        const scheduleSnapshotState = { saveUrl: '', payload: null };

        const scheduleSnapshotMonths = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        function scheduleSnapshotDate(isoDate) {
            const [year, month, day] = String(isoDate).split('-').map(Number);
            return `${day} ${scheduleSnapshotMonths[month - 1]} ${year}`;
        }

        function scheduleSnapshotLine(label, value) {
            const row = document.createElement('div');
            const strong = document.createElement('strong');
            strong.textContent = label + ': ';
            row.append(strong, document.createTextNode(value));
            return row;
        }

        function scheduleSnapshotBlock(title, lines) {
            const block = document.createElement('section');
            block.style.cssText = 'margin-top:10px; padding:10px; border:1px solid #e2e8f0; border-radius:8px;';
            const heading = document.createElement('h4');
            heading.style.cssText = 'margin:0 0 4px; font-size:13px;';
            heading.textContent = title;
            block.append(heading, ...lines);
            return block;
        }

        function scheduleSnapshotDescribe(d) {
            const zone = d.is_wita === null ? 'zona waktu belum tercatat' : (d.is_wita ? 'WITA' : 'WIB');
            const lines = [
                scheduleSnapshotLine('Shift', d.shift || '-'),
                scheduleSnapshotLine('Jam', d.start && d.end ? `${d.start}–${d.end} ${zone}` : `belum tercatat (${zone})`),
            ];
            if (d.is_overnight === true) lines.push(scheduleSnapshotLine('Keterangan', 'Selesai pada hari berikutnya'));
            if (d.is_holiday === true) lines.push(scheduleSnapshotLine('Keterangan', 'Hari libur'));
            if (d.is_holiday === null) lines.push(scheduleSnapshotLine('Keterangan', 'Status libur belum tercatat'));
            return lines;
        }

        function scheduleSnapshotMessage(text) {
            const box = document.getElementById('scheduleSnapshotError');
            box.textContent = text || '';
            box.style.display = text ? 'block' : 'none';
        }

        const scheduleSnapshotValue = v => v === null || v === undefined ? '' : (v === true ? '1' : (v === false ? '0' : String(v)));

        function fillScheduleSnapshotForm(form) {
            document.getElementById('scheduleSnapshotStart').value = form.start_time || '';
            document.getElementById('scheduleSnapshotEnd').value = form.end_time || '';
            document.getElementById('scheduleSnapshotWita').value = scheduleSnapshotValue(form.is_wita);
            document.getElementById('scheduleSnapshotOvernight').value = scheduleSnapshotValue(form.is_overnight);
            document.getElementById('scheduleSnapshotHoliday').value = scheduleSnapshotValue(form.is_holiday);
            document.getElementById('scheduleSnapshotShiftName').value = form.shift_name || '';
        }

        function renderScheduleSnapshotWorkSchedule(schedule) {
            const box = document.getElementById('scheduleSnapshotWorkSchedule');
            box.replaceChildren();
            const title = document.createElement('strong');
            title.textContent = 'Jadwal Kerja';
            box.append(title);

            if (!schedule) {
                const none = document.createElement('p');
                none.style.margin = '4px 0 0';
                none.textContent = 'Belum ada Jadwal Kerja untuk tanggal ini. Isi manual.';
                box.append(none);
                return;
            }

            const zone = schedule.is_wita ? 'WITA' : 'WIB';
            const lines = [
                scheduleSnapshotLine('Shift', schedule.shift_name || '-'),
                scheduleSnapshotLine('Jam', schedule.start_time && schedule.end_time ? `${schedule.start_time}–${schedule.end_time} ${zone}` : `belum tercatat (${zone})`),
            ];
            if (schedule.is_overnight) lines.push(scheduleSnapshotLine('Keterangan', 'Selesai pada hari berikutnya'));
            if (schedule.is_holiday) lines.push(scheduleSnapshotLine('Keterangan', 'Hari libur'));
            if (schedule.group) lines.push(scheduleSnapshotLine('Kelompok', schedule.group));
            const hint = document.createElement('p');
            hint.style.cssText = 'color:#64748b; margin:4px 0 6px;';
            hint.textContent = 'Hanya mengisi form; belum disimpan.';
            const use = document.createElement('button');
            use.type = 'button';
            use.className = 'modal-btn modal-btn-secondary';
            use.textContent = 'Gunakan Jadwal Kerja';
            use.addEventListener('click', function() {
                fillScheduleSnapshotForm({ start_time: schedule.start_time, end_time: schedule.end_time, is_wita: schedule.is_wita, is_overnight: schedule.is_overnight, is_holiday: schedule.is_holiday, shift_name: schedule.shift_name });
                document.getElementById('scheduleSnapshotSourceId').value = schedule.id;
                document.getElementById('scheduleSnapshotSourceFingerprint').value = schedule.fingerprint;
            });
            box.append(...lines, hint, use);
        }

        function renderScheduleSnapshot(data) {
            const body = document.getElementById('scheduleSnapshotBody');
            body.replaceChildren();
            body.append(scheduleSnapshotLine('Karyawan', data.employee || '-'), scheduleSnapshotLine('Tanggal kerja', scheduleSnapshotDate(data.work_date)));

            const typeLabel = type => type === 'CLOCK_IN' ? 'Masuk' : 'Pulang';
            const summary = data.events.map(event => `${typeLabel(event.type)} ${event.time}`);
            const joined = summary.length > 1 ? `${summary.slice(0, -1).join(', ')} dan ${summary[summary.length - 1]}` : summary[0];
            const scope = document.createElement('p');
            scope.style.margin = '6px 0 0';
            scope.textContent = `Berlaku untuk ${data.events.length} log: ${joined}`;
            body.append(scope);

            const keys = new Set(data.events.map(event => JSON.stringify(event.snapshot)));
            if (keys.size === 1) {
                body.append(scheduleSnapshotBlock('Jadwal saat ini', scheduleSnapshotDescribe(data.events[0].snapshot)));
            } else {
                const lines = [];
                data.events.forEach(function(event) {
                    const title = document.createElement('div');
                    title.style.cssText = 'margin-top:6px; font-weight:700;';
                    title.textContent = `${typeLabel(event.type)} ${event.time}`;
                    lines.push(title, ...scheduleSnapshotDescribe(event.snapshot));
                });
                const warn = document.createElement('p');
                warn.style.cssText = 'color:#b45309; margin:0 0 4px;';
                warn.textContent = 'Jadwal tiap log berbeda. Isian berlaku untuk semua log.';
                body.append(scheduleSnapshotBlock('Jadwal saat ini', [warn, ...lines]));
            }

            const note = document.createElement('p');
            note.style.cssText = 'color:#64748b; margin:10px 0 0;';
            note.textContent = 'Jam absen, bukti, dan approval tidak berubah. Isian menjadi acuan report.';
            body.append(note);

            fillScheduleSnapshotForm(data.form);
            document.getElementById('scheduleSnapshotSourceId').value = '';
            document.getElementById('scheduleSnapshotSourceFingerprint').value = '';
            renderScheduleSnapshotWorkSchedule(data.schedule);

            scheduleSnapshotMessage('');
            document.getElementById('scheduleSnapshotSubmit').disabled = false;
        }

        async function openScheduleSnapshotModal(url, saveUrl) {
            scheduleSnapshotState.saveUrl = saveUrl;
            scheduleSnapshotState.payload = null;
            document.getElementById('scheduleSnapshotReason').value = '';
            document.getElementById('scheduleSnapshotSubmit').disabled = true;
            document.getElementById('scheduleSnapshotBody').textContent = 'Memuat…';
            document.getElementById('scheduleSnapshotWorkSchedule').replaceChildren();
            scheduleSnapshotMessage('');
            closeOtherAttendanceActionMenus();
            document.getElementById('scheduleSnapshotModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';

            try {
                const response = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error('Gagal memuat data snapshot jadwal.');
                scheduleSnapshotState.payload = await response.json();
                renderScheduleSnapshot(scheduleSnapshotState.payload);
            } catch (error) {
                document.getElementById('scheduleSnapshotBody').textContent = '';
                scheduleSnapshotMessage(error.message || 'Gagal memuat data snapshot jadwal.');
            }
        }

        function initializeAttendanceSnapshotButtons() {
            document.querySelectorAll('[data-attendance-snapshot]').forEach(function(button) {
                if (button.dataset.attendanceSnapshotInitialized) return;

                button.dataset.attendanceSnapshotInitialized = 'true';
                button.addEventListener('click', function() {
                    openScheduleSnapshotModal(this.dataset.attendanceSnapshotUrl, this.dataset.attendanceSnapshotSaveUrl);
                });
            });
        }

        function initializeAttendanceMoveButtons() {
            initializeAttendanceSnapshotButtons();
            document.querySelectorAll('[data-attendance-move]').forEach(function(button) {
                if (button.dataset.attendanceMoveInitialized) return;

                button.dataset.attendanceMoveInitialized = 'true';
                button.addEventListener('click', function() {
                    openMoveClockSideModal(
                        this.dataset.attendanceMoveUrl,
                        this.dataset.attendanceMoveName,
                        this.dataset.attendanceMoveSource,
                        this.dataset.attendanceMoveDestination,
                        this.dataset.attendanceMoveFrom,
                        this.dataset.attendanceMoveTo,
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
                const type = entry.type === 'DINAS_LUAR' ? 'Dinas Luar' : (entry.is_flexible ? 'Fleksibel' : '');
                time.textContent = `${entry.time} · ${entry.date}${type ? ` · ${type}` : ''}`;
                const evidence = document.createElement('div');
                evidence.className = 'attendance-evidence-subgrid';

                const photo = document.createElement('section');
                photo.className = 'attendance-evidence-block';
                const photoTitle = document.createElement('h5');
                photoTitle.textContent = 'Foto';
                photo.append(photoTitle);
                if (entry.photo) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'attendance-evidence-photo-btn';
                    button.dataset.imageViewerSrc = entry.photo;
                    button.dataset.imageViewerAlt = `Foto ${side === 'in' ? 'Clock In' : 'Clock Out'} ${entry.date} ${entry.time}`;
                    button.setAttribute('aria-label', 'Perbesar foto kehadiran');
                    const image = document.createElement('img');
                    image.className = 'attendance-evidence-photo';
                    image.src = entry.photo;
                    image.alt = 'Foto kehadiran';
                    button.append(image);
                    const hint = document.createElement('span');
                    hint.className = 'attendance-evidence-photo-hint';
                    hint.textContent = 'Ketuk untuk perbesar';
                    button.append(hint);
                    photo.append(button);
                } else {
                    const empty = document.createElement('p');
                    empty.className = 'attendance-evidence-empty';
                    empty.textContent = 'Foto tidak tersedia.';
                    photo.append(empty);
                }

                const location = document.createElement('section');
                location.className = 'attendance-evidence-block';
                const locationTitle = document.createElement('h5');
                locationTitle.textContent = 'Lokasi';
                location.append(locationTitle);
                if (entry.latitude !== '' && entry.longitude !== '') {
                    const coordinates = `${entry.latitude}, ${entry.longitude}`;
                    const label = document.createElement('p');
                    label.className = 'attendance-evidence-coordinates';
                    label.textContent = coordinates;
                    const link = document.createElement('a');
                    link.className = 'evidence-maps-button';
                    link.href = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(coordinates)}`;
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    link.textContent = 'Buka di Google Maps';
                    label.append(' ', link);
                    const spacer = label.cloneNode(true);
                    spacer.classList.add('attendance-evidence-photo-spacer');
                    spacer.setAttribute('aria-hidden', 'true');
                    spacer.setAttribute('inert', '');
                    spacer.querySelector('a').removeAttribute('href');
                    photoTitle.after(spacer);
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
                wrapper.append(time);
                if (entry.is_flexible || entry.notes) {
                    const notes = document.createElement('p');
                    notes.className = 'attendance-evidence-notes';
                    notes.textContent = `Alasan: ${entry.notes || '-'}`;
                    wrapper.append(notes);
                }
                if (entry.hrd_note) {
                    const notes = document.createElement('p');
                    notes.className = 'attendance-evidence-notes attendance-evidence-notes--hrd';
                    notes.textContent = `Catatan HRD: ${entry.hrd_note}`;
                    wrapper.append(notes);
                }
                wrapper.append(evidence);
                list.append(wrapper);
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
            initializeAttendanceMoveButtons();

            document.getElementById('scheduleSnapshotForm')?.addEventListener('submit', async function(event) {
                event.preventDefault();

                const payload = scheduleSnapshotState.payload;
                const submitButton = document.getElementById('scheduleSnapshotSubmit');
                if (!payload || submitButton.disabled) return;
                submitButton.disabled = true;
                scheduleSnapshotMessage('');
                let status = 0;

                try {
                    const body = new FormData(event.target);
                    body.set('fingerprint', payload.fingerprint);
                    payload.event_ids.forEach(id => body.append('event_ids[]', id));

                    const response = await fetch(scheduleSnapshotState.saveUrl, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body,
                    });
                    status = response.status;
                    const result = await response.json().catch(() => ({}));
                    if (!response.ok) throw new Error(result.message || Object.values(result.errors || {}).flat()[0] || 'Koreksi snapshot jadwal gagal.');

                    document.getElementById('scheduleSnapshotModal').style.display = 'none';
                    document.body.style.overflow = '';
                    await window.refreshAttendanceResults?.();
                } catch (error) {
                    scheduleSnapshotMessage(error.message || 'Koreksi snapshot jadwal gagal.');
                    // 409 = modal basi: tetap nonaktif sampai HR memuat ulang.
                    submitButton.disabled = status === 409;
                }
            });

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
                    window.logAttendanceMessage?.(error.message || 'Perubahan tipe event gagal.');
                    window.alert(error.message || 'Perubahan tipe event gagal.');
                } finally {
                    submitButton.disabled = false;
                }
            });
        });
    </script>

</x-app>
