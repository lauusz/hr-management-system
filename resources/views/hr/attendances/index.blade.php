<x-app title="Master Absensi">

    <div class="card mb-4">
        <form method="POST" action="{{ route('hr.attendances.filter') }}" id="attendanceFilterForm">
            @csrf
            @php
                $rangeValue = '';
                if (!empty($date_start) && !empty($date_end)) {
                    $rangeValue = $date_start . ' sampai ' . $date_end;
                } elseif (!empty($date_start)) {
                    $rangeValue = $date_start;
                }

                $today = now()->toDateString();
                $hasCustomDateRange = ($date_start ?? $today) !== $today
                    || ($date_end ?? $today) !== $today;
                $hasAdvancedFilter = $hasCustomDateRange
                    || ($shift_id ?? null)
                    || ($status ?? null)
                    || ($completion_status ?? null);
            @endphp

            <div class="attendance-search-row">
                <div class="attendance-search-wrap">
                    <input type="text"
                        name="q"
                        value="{{ $q ?? '' }}"
                        placeholder="Cari nama karyawan..."
                        autocomplete="off"
                        class="attendance-search-input">
                </div>
                <button type="submit" class="attendance-btn-search">Cari</button>
                @if(($q ?? null) || $hasAdvancedFilter)
                    <button type="submit" name="action" value="reset" class="attendance-btn-reset">Reset</button>
                @endif
                <button type="button"
                    class="attendance-btn-toggle {{ $hasAdvancedFilter ? 'active' : '' }}"
                    aria-expanded="{{ $hasAdvancedFilter ? 'true' : 'false' }}"
                    aria-controls="attendanceFilterPanel"
                    onclick="toggleAttendanceFilterPanel()">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                </button>
            </div>

            <div class="attendance-filter-panel" id="attendanceFilterPanel" style="{{ $hasAdvancedFilter ? '' : 'display: none;' }}">
                <div class="attendance-filter-grid">
                    <div class="filter-group">
                        <label>Rentang Tanggal</label>
                        <div class="input-with-icon">
                            <input type="text"
                                id="date_range"
                                name="date_range"
                                value="{{ $rangeValue }}"
                                placeholder="Pilih tanggal..."
                                autocomplete="off"
                                class="form-control">
                            <input type="hidden" name="date_start" id="date_start" value="{{ $date_start ?? '' }}">
                            <input type="hidden" name="date_end" id="date_end" value="{{ $date_end ?? '' }}">
                        </div>
                    </div>

                    <div class="filter-group">
                        <label>Shift</label>
                        <select name="shift_id" class="form-control">
                            <option value="">Semua Shift</option>
                            @foreach(($shifts ?? collect()) as $shift)
                                <option value="{{ $shift->id }}" @selected((string) ($shift_id ?? '') === (string) $shift->id)>
                                    {{ $shift->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="HADIR" @selected(($status ?? '' )=='HADIR' )>Hadir</option>
                            <option value="TERLAMBAT" @selected(($status ?? '' )=='TERLAMBAT' )>Terlambat</option>
                            <option value="DINAS_LUAR" @selected(($status ?? '' )=='DINAS_LUAR' )>Dinas Luar</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Status Kelengkapan</label>
                        <select name="completion_status" class="form-control">
                            <option value="">Semua</option>
                            <option value="{{ App\Models\Attendance::COMPLETION_CLOSED }}" @selected(($completion_status ?? '') == App\Models\Attendance::COMPLETION_CLOSED)>Lengkap</option>
                            <option value="{{ App\Models\Attendance::COMPLETION_OPEN }}" @selected(($completion_status ?? '') == App\Models\Attendance::COMPLETION_OPEN)>Berjalan</option>
                            <option value="{{ App\Models\Attendance::COMPLETION_MISSED_CLOCK_OUT }}" @selected(($completion_status ?? '') == App\Models\Attendance::COMPLETION_MISSED_CLOCK_OUT)>Belum Clock Out</option>
                            <option value="{{ App\Models\Attendance::COMPLETION_LATE_CLOCK_OUT }}" @selected(($completion_status ?? '') == App\Models\Attendance::COMPLETION_LATE_CLOCK_OUT)>Clock Out Terlambat</option>
                        </select>
                    </div>

                    <div class="attendance-filter-actions">
                        <button type="submit" class="attendance-btn-apply">Terapkan Filter</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="min-width: 180px;">Nama Karyawan</th>
                        <th>Shift</th>
                        <th>Jam Kerja</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Terlambat</th>
                        <th>Status</th>
                        <th>Status Kelengkapan</th>
                        <th class="text-center">Foto In</th>
                        <th class="text-center">Foto Out</th>
                        <th class="text-center">Lokasi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($items as $at)
                    <tr>
                        <td>
                            <div class="user-info">
                                <span class="fw-bold">{{ $at->user->name }}</span>
                            </div>
                        </td>

                        <td>
                            @php
                                $shiftName = $at->shift->name ?? '-';
                                $shortShiftName = str_contains($shiftName, ',')
                                    ? strstr($shiftName, ',', true) . '…'
                                    : $shiftName;
                            @endphp
                            <span class="text-muted shift-name" title="{{ $shiftName }}">
                                {{ $shortShiftName }}
                            </span>
                        </td>

                        <td>
                            @if($at->normal_start_time && $at->normal_end_time)
                                <span class="text-small">{{ $at->normal_start_time->format('H:i') }} - {{ $at->normal_end_time->format('H:i') }}</span>
                            @elseif($at->shift)
                                <span class="text-small">{{ $at->shift->start_time_label }} - {{ $at->shift->end_time_label }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            @if($at->clock_in_at)
                            <div class="time-block">
                                <span class="time-date">{{ $at->clock_in_at->format('d/m/y') }}</span>
                                <span class="time-clock">{{ $at->clock_in_at->format('H:i') }}</span>
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            @if($at->clock_out_at)
                            <div class="time-block">
                                <span class="time-date">{{ $at->clock_out_at->format('d/m/y') }}</span>
                                <span class="time-clock">{{ $at->clock_out_at->format('H:i') }}</span>
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            @if($at->type === 'DINAS_LUAR')
                                <span class="text-muted">-</span>
                            @elseif($at->late_minutes > 0)
                                @php
                                    $jam = floor($at->late_minutes / 60);
                                    $menit = $at->late_minutes % 60;
                                    $hasil = '';
                                    if($jam > 0) $hasil .= $jam . 'j ';
                                    if($menit > 0) $hasil .= $menit . 'm';
                                @endphp
                                <span class="badge-late">{{ $hasil }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- [ADJUSTMENT] Logic Status untuk Handle Dinas Luar --}}
                        <td>
                            @if($at->type === 'DINAS_LUAR')
                                <span class="badge-status bg-purple">Dinas Luar</span>
                                @if($at->approval_status === 'APPROVED')
                                    <span class="badge-status bg-green" style="font-size: 10px; margin-left: 2px;">OK</span>
                                @elseif($at->approval_status === 'REJECTED')
                                    <span class="badge-status bg-red" style="font-size: 10px; margin-left: 2px;">Ditolak</span>
                                @endif
                            @else
                                @if ($at->status === 'TERLAMBAT')
                                    <span class="badge-status bg-red">Terlambat</span>
                                @elseif ($at->status === 'HADIR')
                                    <span class="badge-status bg-green">Hadir</span>
                                @elseif ($at->status === 'ALPHA')
                                    <span class="badge-status bg-red">Alpha</span>
                                @else
                                    <span class="badge-status bg-gray">{{ $at->status ?? '-' }}</span>
                                @endif
                            @endif
                        </td>

                        <td>
                            @if($at->completion_status === App\Models\Attendance::COMPLETION_CLOSED)
                                <span class="attendance-complete-icon" role="img" aria-label="Lengkap" title="Lengkap">
                                    <svg aria-hidden="true" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </span>
                            @elseif($at->completion_status === App\Models\Attendance::COMPLETION_OPEN)
                                <span class="badge-status bg-yellow">Berjalan</span>
                            @elseif($at->completion_status === App\Models\Attendance::COMPLETION_MISSED_CLOCK_OUT)
                                <span class="badge-status bg-red">Belum Clock Out</span>
                            @elseif($at->completion_status === App\Models\Attendance::COMPLETION_LATE_CLOCK_OUT)
                                <span class="badge-status bg-red">Clock Out Terlambat</span>
                            @else
                                <span class="badge-status bg-gray">-</span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($at->clock_in_photo)
                            <button type="button"
                                class="btn-pill btn-blue"
                                data-image-viewer-src="{{ asset('storage/'.$at->clock_in_photo) }}"
                                data-image-viewer-alt="Foto clock-in {{ $at->user->name }}"
                                data-employee-name="{{ $at->user->name }}"
                                data-datetime="{{ $at->clock_in_at ? $at->clock_in_at->format('d/m/Y H:i') : '' }}"
                                data-label="Clock-in">
                                Lihat
                            </button>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($at->clock_out_photo)
                            <button type="button"
                                class="btn-pill btn-blue"
                                data-image-viewer-src="{{ asset('storage/'.$at->clock_out_photo) }}"
                                data-image-viewer-alt="Foto clock-out {{ $at->user->name }}"
                                data-employee-name="{{ $at->user->name }}"
                                data-datetime="{{ $at->clock_out_at ? $at->clock_out_at->format('d/m/Y H:i') : '' }}"
                                data-label="Clock-out">
                                Lihat
                            </button>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td class="text-center">
                             <div class="btn-group-pill">
                                @if($at->clock_in_lat && $at->clock_in_lng)
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $at->clock_in_lat }},{{ $at->clock_in_lng }}"
                                    target="_blank"
                                    class="btn-pill btn-sky">
                                    In
                                </a>
                                @endif

                                @if($at->clock_out_lat && $at->clock_out_lng)
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $at->clock_out_lat }},{{ $at->clock_out_lng }}"
                                    target="_blank"
                                    class="btn-pill btn-sky">
                                    Out
                                </a>
                                @endif

                                @if(!$at->clock_in_lat && !$at->clock_out_lat)
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="empty-state">
                            Tidak ada data absensi yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 20px;">
        <x-pagination :items="$items" />
    </div>

    <style>
        /* --- UTILITY --- */
        .mb-4 { margin-bottom: 16px; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: 600; color: #111827; }
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
        .attendance-search-row {
            padding: 16px 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .attendance-search-wrap {
            flex: 1;
            min-width: 260px;
        }

        .attendance-search-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 13.5px;
            color: #374151;
            outline: none;
        }

        .attendance-search-input:focus,
        .form-control:focus {
            border-color: #1e4a8d;
        }

        .attendance-btn-search,
        .attendance-btn-reset,
        .attendance-btn-toggle,
        .attendance-btn-apply {
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
        }

        .attendance-btn-search,
        .attendance-btn-apply {
            background: #1e4a8d;
            color: #fff;
            border: 1px solid #1e4a8d;
        }

        .attendance-btn-search:hover,
        .attendance-btn-apply:hover {
            background: #163a75;
        }

        .attendance-btn-reset,
        .attendance-btn-toggle {
            background: #fff;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .attendance-btn-reset:hover,
        .attendance-btn-toggle:hover {
            background: #f9fafb;
        }

        .attendance-btn-toggle.active {
            background: #1e4a8d;
            color: #fff;
            border-color: #1e4a8d;
        }

        .attendance-btn-toggle {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            justify-content: center;
        }

        .attendance-filter-panel {
            padding: 16px 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            animation: attendanceFilterSlideDown 0.18s ease-out;
        }

        .attendance-filter-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px 16px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .form-control {
            padding: 9px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 13.5px;
            color: #374151;
            background: #fff;
            min-width: 0;
            width: 100%;
            outline: none;
            transition: border-color 0.2s;
        }

        .attendance-filter-actions {
            grid-column: 1 / -1;
            display: flex;
            justify-content: flex-end;
        }

        @keyframes attendanceFilterSlideDown {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- TABLE --- */
        .table-wrapper { width: 100%; overflow-x: auto; }
        .custom-table { width: 100%; border-collapse: collapse; min-width: 1000px; }

        .custom-table th {
            background: #f9fafb;
            padding: 10px 12px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e5e7eb;
        }

        .custom-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 12px;
            color: #1f2937;
            vertical-align: middle;
        }

        .custom-table tr:last-child td { border-bottom: none; }
        .custom-table tr:hover td { background: #fdfdfd; }

        .custom-table th:first-child,
        .custom-table td:first-child {
            position: sticky;
            left: 0;
            min-width: 180px;
            text-align: left;
            box-shadow: 6px 0 8px -8px rgba(15, 23, 42, 0.35);
        }

        .custom-table th:first-child {
            z-index: 3;
            background: #f9fafb;
        }

        .custom-table td:first-child {
            z-index: 2;
            background: #fff;
        }

        .custom-table th:nth-child(2),
        .custom-table td:nth-child(2) {
            width: 150px;
            min-width: 150px;
            max-width: 150px;
            text-align: left;
        }

        .custom-table th:nth-child(n+3),
        .custom-table td:nth-child(n+3) {
            text-align: center;
        }

        .custom-table th:nth-child(3),
        .custom-table td:nth-child(3) { min-width: 84px; }
        .custom-table th:nth-child(4),
        .custom-table td:nth-child(4),
        .custom-table th:nth-child(5),
        .custom-table td:nth-child(5) { min-width: 76px; }
        .custom-table th:nth-child(6),
        .custom-table td:nth-child(6) { min-width: 70px; }
        .custom-table th:nth-child(7),
        .custom-table td:nth-child(7) { min-width: 90px; }
        .custom-table th:nth-child(8),
        .custom-table td:nth-child(8) { min-width: 110px; }
        .custom-table th:nth-child(9),
        .custom-table td:nth-child(9),
        .custom-table th:nth-child(10),
        .custom-table td:nth-child(10) { min-width: 68px; }
        .custom-table th:nth-child(11),
        .custom-table td:nth-child(11) { min-width: 100px; }

        .custom-table td:nth-child(3),
        .custom-table td:nth-child(4),
        .custom-table td:nth-child(5),
        .custom-table td:nth-child(6) { white-space: nowrap; }

        /* --- CUSTOM COLUMNS --- */
        .custom-table .text-muted,
        .custom-table .text-small { font-size: 12px; }
        .shift-name {
            display: block;
            max-width: 130px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .time-block { display: flex; flex-direction: column; line-height: 1.2; }
        .time-date { font-size: 11px; color: #6b7280; }
        .time-clock { font-size: 12px; font-weight: 700; color: #111827; }

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

        .attendance-complete-icon {
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #dcfce7;
            color: #166534;
        }

        .badge-late {
            color: #b91c1c;
            font-weight: 600;
            font-size: 12px;
            background: #fef2f2;
            padding: 2px 6px;
            border-radius: 4px;
        }

        /* --- PILL BUTTONS (View/Maps) --- */
        .btn-group-pill {
            display: flex;
            justify-content: center;
            gap: 6px;
        }

        .btn-pill {
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s;
            display: inline-block;
        }
        
        .btn-blue {
            background: #eef2ff;
            color: #1e4a8d;
            border-color: #e0e7ff;
        }
        .btn-blue:hover { background: #1e4a8d; color: #fff; }
        
        .btn-sky {
            background: #f0f9ff;
            color: #0369a1;
            border-color: #e0f2fe;
        }
        .btn-sky:hover { background: #0369a1; color: #fff; }

        .empty-state { padding: 40px; text-align: center; color: #9ca3af; font-style: italic; }

        @media(max-width: 768px) {
            .attendance-search-row { align-items: stretch; }
            .attendance-search-wrap { flex-basis: 100%; min-width: 0; }
            .attendance-btn-search,
            .attendance-btn-reset,
            .attendance-btn-toggle { flex: 1; text-align: center; }
            .attendance-filter-grid { grid-template-columns: 1fr; }
            .attendance-btn-apply { width: 100%; }
        }

        @media(min-width: 480px) and (max-width: 768px) {
            .attendance-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        function toggleAttendanceFilterPanel() {
            const panel = document.getElementById('attendanceFilterPanel');
            const button = document.querySelector('.attendance-btn-toggle');
            if (!panel || !button) return;

            const willOpen = panel.style.display === 'none';
            panel.style.display = willOpen ? '' : 'none';
            button.classList.toggle('active', willOpen);
            button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
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
                    locale: { rangeSeparator: " sampai " },
                    onChange: function(selectedDates, dateStr) {
                        if (!dateStr) {
                            startHidden.value = "";
                            endHidden.value = "";
                            return;
                        }
                        const parts = dateStr.split(" sampai ");
                        if (parts.length === 1) {
                            startHidden.value = parts[0];
                            endHidden.value = parts[0];
                        } else {
                            startHidden.value = parts[0];
                            endHidden.value = parts[1];
                        }
                    }
                });
            }
        });
    </script>

</x-app>
