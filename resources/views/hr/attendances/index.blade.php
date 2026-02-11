<x-app title="Master Absensi">

    <div class="card mb-4">
        <form method="GET" class="filter-container">
            @php
            $rangeValue = '';
            if (!empty($date_start) && !empty($date_end)) {
                $rangeValue = $date_start . ' sampai ' . $date_end;
            } elseif (!empty($date_start)) {
                $rangeValue = $date_start;
            }
            @endphp

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
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="HADIR" @selected(($status ?? '' )=='HADIR' )>Hadir</option>
                    <option value="TERLAMBAT" @selected(($status ?? '' )=='TERLAMBAT' )>Terlambat</option>
                </select>
            </div>

            <div class="filter-group flex-grow">
                <label>Cari Karyawan</label>
                <div class="search-input">
                    <input type="text"
                        name="q"
                        value="{{ $q ?? '' }}"
                        placeholder="Nama karyawan..."
                        class="form-control">
                </div>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary">Filter</button>
                @if(($q ?? null) || ($status ?? null) || ($date_start ?? null))
                <a href="{{ route('hr.attendances.index') }}" class="btn-reset">Reset</a>
                @endif
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
                                data-photo-url="{{ asset('storage/'.$at->clock_in_photo) }}"
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
                                data-photo-url="{{ asset('storage/'.$at->clock_out_photo) }}"
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
                        <td colspan="10" class="empty-state">
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

    {{-- [SIMPLE FULL SCREEN VIEWER] --}}
    <div id="simple-viewer" class="simple-viewer-overlay" style="display: none;">
        <button type="button" id="btn-close-simple" class="btn-close-simple">
            <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <img id="simple-viewer-img" src="" alt="Full Preview">
    </div>

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
        .filter-container {
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        .filter-group.flex-grow { flex: 1; min-width: 200px; }

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
            min-width: 160px;
            width: 100%;
            outline: none;
            transition: border-color 0.2s;
        }
        
        .form-control:focus { border-color: #1e4a8d; }

        .filter-actions {
            display: flex;
            gap: 8px;
            padding-bottom: 2px; /* Alignment fix */
        }

        .btn-primary {
            padding: 9px 18px;
            background: #1e4a8d;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13.5px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn-primary:hover { background: #163a75; }
        
        .btn-reset {
            padding: 9px 16px;
            background: #fff;
            color: #374151;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            display: inline-block;
        }
        .btn-reset:hover { background: #f9fafb; }

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
        .time-date { font-size: 11px; color: #6b7280; }
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

        .empty-state { padding: 40px; text-align: center; color: #9ca3af; font-style: italic; }

        /* --- UTILITY --- */
        .simple-viewer-overlay { position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.95); z-index: 99999; display: flex; align-items: center; justify-content: center; }
        .btn-close-simple { position: absolute; top: 20px; right: 20px; background: rgba(255, 255, 255, 0.1); border: none; color: #fff; width: 48px; height: 48px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; z-index: 100000; }
        .btn-close-simple:hover { background: rgba(255, 255, 255, 0.3); }
        #simple-viewer-img { max-width: 95vw; max-height: 95vh; object-fit: contain; border-radius: 4px; box-shadow: 0 0 50px rgba(0,0,0,0.5); }

        @media(max-width: 768px) {
            .filter-container { flex-direction: column; align-items: stretch; gap: 12px; }
            .filter-group, .form-control { width: 100%; min-width: 0; }
            .filter-actions { margin-top: 4px; }
            .btn-primary, .btn-reset { flex: 1; text-align: center; }
        }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Simple Viewer Logic ---
            const viewer = document.getElementById('simple-viewer');
            const viewerImg = document.getElementById('simple-viewer-img');
            const closeBtn = document.getElementById('btn-close-simple');

            function openViewer(url) {
                if(viewer && viewerImg && url) {
                    viewerImg.src = url;
                    viewer.style.display = 'flex';
                    document.body.style.overflow = 'hidden'; 
                }
            }

            function closeViewer() {
                if (viewer) viewer.style.display = 'none';
                if (viewerImg) viewerImg.src = '';
                document.body.style.overflow = ''; 
            }

            // Event Delegation for buttons
            document.body.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('btn-pill') && e.target.hasAttribute('data-photo-url')) {
                    const url = e.target.getAttribute('data-photo-url');
                    if (url) openViewer(url);
                }
            });

            if (closeBtn) closeBtn.addEventListener('click', closeViewer);
            if (viewer) viewer.addEventListener('click', (e) => { if (e.target === viewer) closeViewer(); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && viewer && viewer.style.display === 'flex') closeViewer(); });

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