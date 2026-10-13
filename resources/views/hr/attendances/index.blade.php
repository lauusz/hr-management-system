<x-app title="Master Absensi">

    @php
        $hasDateFilter = request()->filled('date_start') || request()->filled('date_end');
        $hasAdvancedFilter = $hasDateFilter || ($status ?? null) || ($completion_status ?? null) || ($shiftType ?? null);
        $activeFilterCount = collect([
            $hasDateFilter ? 'date' : null,
            $status ?? null,
            $completion_status ?? null,
            $shiftType ?? null,
        ])->filter()->count();
    @endphp

    <div class="attendance-filter-card">
        <form method="GET" action="{{ route('hr.attendances.index') }}" id="attendance-filter-form">
            @php
            $rangeValue = '';
            if (!empty($date_start) && !empty($date_end)) {
                $rangeValue = $date_start . ' sampai ' . $date_end;
            } elseif (!empty($date_start)) {
                $rangeValue = $date_start;
            }
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
                        <label class="attendance-filter-label">Rentang Tanggal</label>
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

                    <div class="attendance-filter-group">
                        <label class="attendance-filter-label">Status</label>
                        <select name="status" class="attendance-filter-input">
                            <option value="">Semua Status</option>
                            <option value="HADIR" @selected(($status ?? '' )=='HADIR' )>Hadir</option>
                            <option value="TERLAMBAT" @selected(($status ?? '' )=='TERLAMBAT' )>Terlambat</option>
                        </select>
                    </div>

                    <div class="attendance-filter-group">
                        <label class="attendance-filter-label">Status Kelengkapan</label>
                        <select name="completion_status" class="attendance-filter-input">
                            <option value="">Semua</option>
                            <option value="{{ App\Models\Attendance::COMPLETION_CLOSED }}" @selected(($completion_status ?? '') == App\Models\Attendance::COMPLETION_CLOSED)>Lengkap</option>
                            <option value="{{ App\Models\Attendance::COMPLETION_OPEN }}" @selected(($completion_status ?? '') == App\Models\Attendance::COMPLETION_OPEN)>Berjalan</option>
                            <option value="{{ App\Models\Attendance::COMPLETION_MISSED_CLOCK_OUT }}" @selected(($completion_status ?? '') == App\Models\Attendance::COMPLETION_MISSED_CLOCK_OUT)>Belum Clock Out</option>
                            <option value="{{ App\Models\Attendance::COMPLETION_MISSED_CLOCK_IN }}" @selected(($completion_status ?? '') == App\Models\Attendance::COMPLETION_MISSED_CLOCK_IN)>Belum Clock In</option>
                            <option value="{{ App\Models\Attendance::COMPLETION_LATE_CLOCK_OUT }}" @selected(($completion_status ?? '') == App\Models\Attendance::COMPLETION_LATE_CLOCK_OUT)>Clock Out Terlambat</option>
                        </select>
                    </div>

                    <div class="attendance-filter-group">
                        <label class="attendance-filter-label">Jenis Shift</label>
                        <select name="shift_type" class="attendance-filter-input">
                            <option value="">Semua</option>
                            <option value="overnight" @selected(($shiftType ?? '') === 'overnight')>Lintas Hari</option>
                            <option value="regular" @selected(($shiftType ?? '') === 'regular')>Bukan Lintas Hari</option>
                        </select>
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
                        <th style="min-width: 180px;">Nama Karyawan</th>
                        <th>Shift</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Tanggal</th>
                        <th>Terlambat</th>
                        <th>Status</th>
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
                            <span class="text-muted">{{ $at->shift->name ?? '-' }}</span>
                            @if($at->is_overnight)
                                <span class="badge-status bg-purple">Lintas Hari</span>
                            @endif
                        </td>

                        <td>
                            @if($at->clock_in_at)
                            <div class="time-block">
                                <span class="time-clock">{{ $at->clock_in_at->format('H:i') }}</span>
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            @if($at->clock_out_at)
                            <div class="time-block">
                                <span class="time-clock">{{ $at->clock_out_at->format('H:i') }}</span>
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>{{ $at->work_date_label }}</td>

                        <td>
                            @if($at->late_minutes > 0)
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
                                <button type="button"
                                    class="btn-pill btn-sky"
                                    data-modal-target="attendance-location-map"
                                    data-attendance-location="clock-in"
                                    data-attendance-location-lat="{{ $at->clock_in_lat }}"
                                    data-attendance-location-lng="{{ $at->clock_in_lng }}">
                                    Lihat
                                </button>
                                @endif

                                @if($at->clock_out_lat && $at->clock_out_lng)
                                <button type="button"
                                    class="btn-pill btn-sky"
                                    data-modal-target="attendance-location-map"
                                    data-attendance-location="clock-out"
                                    data-attendance-location-lat="{{ $at->clock_out_lat }}"
                                    data-attendance-location-lng="{{ $at->clock_out_lng }}">
                                    Lihat
                                </button>
                                @endif

                                @if(!$at->clock_in_lat && !$at->clock_out_lat)
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="empty-state">
                            Tidak ada data absensi yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 20px;" data-attendance-pagination>
        <x-pagination :items="$items" />
    </div>

    <x-modal id="attendance-location-map" title="Lokasi Presensi" type="info" variant="info" cancelLabel="Tutup">
        <div class="attendance-location-modal">
            <p class="attendance-location-modal__label" id="attendance-location-label"></p>
            <p class="attendance-location-modal__coordinates" id="attendance-location-coordinates"></p>
            <iframe id="attendance-location-frame" title="Peta lokasi presensi" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </x-modal>

    <style>
        /* --- UTILITY --- */
        .mb-4 { margin-bottom: 16px; }
        .mb-4 { margin-bottom: 16px; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: 600; color: #111827; }
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

        /* --- CUSTOM COLUMNS --- */
        .time-block { display: flex; flex-direction: column; line-height: 1.2; }
        .time-clock { font-weight: 600; color: #111827; }

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

        #attendance-location-map .modal-card { max-width: 720px; }
        .attendance-location-modal__label { margin: 0; font-weight: 700; color: #111827; }
        .attendance-location-modal__coordinates { margin: 4px 0 14px; color: #4b5563; font-family: monospace; }
        #attendance-location-frame { display: block; width: 100%; height: min(55vh, 420px); border: 0; border-radius: 10px; background: #f3f4f6; }

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

            async function loadAttendanceResults() {
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
                    ['[data-attendance-results]', '[data-attendance-pagination]'].forEach((selector) => {
                        const current = document.querySelector(selector);
                        const next = documentResult.querySelector(selector);
                        if (!current || !next) throw new Error('Hasil pencarian tidak lengkap');
                        current.innerHTML = next.innerHTML;
                    });

                    initializeAttendanceLocationButtons();
                    window.history.replaceState(null, '', url);
                } catch (error) {
                    if (error.name !== 'AbortError') console.error(error);
                } finally {
                    if (request === currentRequest) {
                        results.classList.remove('is-loading');
                        results.setAttribute('aria-busy', 'false');
                    }
                }
            }

            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(loadAttendanceResults, 350);
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

        function initializeAttendanceLocationButtons() {
            const locationLabel = document.getElementById('attendance-location-label');
            const locationCoordinates = document.getElementById('attendance-location-coordinates');
            const locationFrame = document.getElementById('attendance-location-frame');

            document.querySelectorAll('[data-attendance-location]').forEach(function(button) {
                if (button.dataset.attendanceLocationInitialized) return;

                button.dataset.attendanceLocationInitialized = 'true';
                button.addEventListener('click', function() {
                    const label = this.dataset.attendanceLocation === 'clock-in' ? 'Clock In' : 'Clock Out';
                    const coordinates = `${this.dataset.attendanceLocationLat}, ${this.dataset.attendanceLocationLng}`;

                    locationLabel.textContent = `Lokasi ${label}`;
                    locationCoordinates.textContent = coordinates;
                    locationFrame.src = `https://www.google.com/maps?q=${encodeURIComponent(coordinates)}&z=16&output=embed`;
                    document.getElementById('attendance-location-map').style.display = 'flex';
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

            initializeAttendanceLocationButtons();
        });
    </script>

</x-app>
