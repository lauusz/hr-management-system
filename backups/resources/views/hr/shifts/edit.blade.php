<x-app title="Edit Shift">

    <div class="main-container">

        @if ($errors->any())
            <div class="alert-error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="form-title">Edit Shift Kerja</h2>
                    <p class="form-subtitle">Perbarui jadwal jam kerja dan aturan libur untuk shift ini.</p>
                </div>
                <a href="{{ route('hr.shifts.index') }}" class="btn-back">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                    Kembali
                </a>
            </div>

            <div class="divider"></div>

            <form method="POST" action="{{ route('hr.shifts.update', $shift->id) }}" class="form-content">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="name">Nama Shift <span class="req">*</span></label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control"
                            value="{{ old('name', $shift->name) }}"
                            required>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Deskripsi</label>
                        <textarea
                            id="description"
                            name="description"
                            class="form-control"
                            rows="2">{{ old('description', $shift->description) }}</textarea>
                    </div>

                    <div class="form-group full-width">
                        <label class="checkbox-wrapper">
                            <input 
                                type="checkbox" 
                                name="is_active" 
                                value="1" 
                                @checked(old('is_active', $shift->is_active))>
                            <span class="checkbox-label">Shift Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="section-divider"></div>

                <div class="form-section-title">Pengaturan Jadwal Harian</div>

                @php
                    $days = [
                        1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 
                        4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu',
                    ];
                    // Mapping data existing ke array key = day_of_week
                    $dayData = $shift->days->keyBy('day_of_week');
                @endphp
                
                <div class="table-responsive">
                    <table class="shift-table">
                        <thead>
                            <tr>
                                <th style="width: 15%">Hari</th>
                                <th style="width: 20%">Jam Masuk</th>
                                <th style="width: 20%">Jam Pulang</th>
                                <th style="width: 10%; text-align: center;">Libur</th>
                                <th style="width: 35%">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($days as $dayNumber => $dayName)
                                @php
                                    $current = $dayData[$dayNumber] ?? null;

                                    // Logic retrieve Value (Old Input -> DB -> Null)
                                    // Start Time
                                    $startVal = old("days.$dayNumber.start_time");
                                    if ($startVal === null && $current && $current->start_time) {
                                        try { $startVal = \Carbon\Carbon::parse($current->start_time)->format('H:i'); } catch(\Exception $e){}
                                    }

                                    // End Time
                                    $endVal = old("days.$dayNumber.end_time");
                                    if ($endVal === null && $current && $current->end_time) {
                                        try { $endVal = \Carbon\Carbon::parse($current->end_time)->format('H:i'); } catch(\Exception $e){}
                                    }

                                    // Holiday
                                    $holidayVal = old("days.$dayNumber.is_holiday");
                                    $isHoliday = $holidayVal !== null ? (bool)$holidayVal : (bool)($current?->is_holiday);

                                    // Note
                                    $noteVal = old("days.$dayNumber.note", $current?->note);
                                @endphp
                                <tr>
                                    <td class="day-name">{{ $dayName }}</td>
                                    <td>
                                        <input
                                            type="time"
                                            name="days[{{ $dayNumber }}][start_time]"
                                            class="form-control form-control-sm start-{{ $dayNumber }}"
                                            value="{{ $startVal }}">
                                    </td>
                                    <td>
                                        <input
                                            type="time"
                                            name="days[{{ $dayNumber }}][end_time]"
                                            class="form-control form-control-sm end-{{ $dayNumber }}"
                                            value="{{ $endVal }}">
                                    </td>
                                    <td style="text-align: center;">
                                        <label class="checkbox-wrapper center-box">
                                            <input
                                                type="checkbox"
                                                name="days[{{ $dayNumber }}][is_holiday]"
                                                class="holiday-toggle"
                                                data-day="{{ $dayNumber }}"
                                                value="1"
                                                @checked($isHoliday)>
                                        </label>
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            name="days[{{ $dayNumber }}][note]"
                                            class="form-control form-control-sm note-{{ $dayNumber }}"
                                            value="{{ $noteVal }}"
                                            placeholder="Ket.">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        Update Shift
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.holiday-toggle').forEach(cb => {
                const day = cb.dataset.day;
                const startInput = document.querySelector('.start-' + day);
                const endInput = document.querySelector('.end-' + day);
                const noteInput = document.querySelector('.note-' + day);

                const applyState = () => {
                    if (cb.checked) {
                        // Jika libur, kosongkan waktu dan disable input
                        if(startInput) {
                            startInput.value = '';
                            startInput.disabled = true;
                        }
                        if(endInput) {
                            endInput.value = '';
                            endInput.disabled = true;
                        }
                        // Note opsional, boleh didisable atau tidak. 
                        // Di sini kita biarkan note aktif agar bisa tulis alasan libur.
                        // if(noteInput) noteInput.disabled = true; 
                    } else {
                        if(startInput) startInput.disabled = false;
                        if(endInput) endInput.disabled = false;
                        if(noteInput) noteInput.disabled = false;
                    }
                };

                // Apply on load
                applyState();

                // Apply on change
                cb.addEventListener('change', applyState);
            });
        });
    </script>

    <style>
        /* Container */
        .main-container {
            max-width: 850px;
            margin: 0 auto;
            padding-bottom: 40px;
        }

        /* Alert */
        .alert-error {
            background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
            padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;
            display: flex; align-items: center; gap: 10px; font-size: 14px;
        }

        /* Card */
        .card {
            background: #fff; border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            border: 1px solid #f3f4f6; overflow: hidden;
        }

        .card-header {
            padding: 24px; display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;
        }

        .form-title { margin: 0; font-size: 18px; font-weight: 700; color: #111827; }
        .form-subtitle { margin: 4px 0 0; font-size: 13.5px; color: #6b7280; }
        .divider { height: 1px; background: #f3f4f6; width: 100%; }
        
        /* Form */
        .form-content { padding: 24px; }
        .form-grid { display: grid; gap: 16px; }
        .full-width { grid-column: 1 / -1; }

        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13.5px; font-weight: 600; color: #374151; }
        .req { color: #dc2626; }

        .form-control {
            padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;
            font-size: 14px; width: 100%; outline: none; background: #fff; color: #111827;
            transition: border-color 0.2s, box-shadow 0.2s; font-family: inherit;
        }
        .form-control:focus { border-color: #1e4a8d; box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1); }
        
        .form-control-sm { padding: 8px 10px; font-size: 13px; }

        /* Checkbox */
        .checkbox-wrapper { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .checkbox-wrapper input[type="checkbox"] { width: 16px; height: 16px; accent-color: #1e4a8d; cursor: pointer; }
        .checkbox-label { font-size: 14px; color: #374151; font-weight: 500; }
        .center-box { justify-content: center; }

        /* Table */
        .section-divider { height: 1px; background: #e5e7eb; margin: 24px 0 16px; }
        .form-section-title { font-size: 16px; font-weight: 700; color: #1e4a8d; margin-bottom: 12px; }

        .table-responsive { overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 8px; }
        .shift-table { width: 100%; border-collapse: collapse; min-width: 600px; }
        .shift-table th { background: #f9fafb; padding: 10px 12px; text-align: left; font-size: 13px; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e5e7eb; }
        .shift-table td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        .shift-table tr:last-child td { border-bottom: none; }
        .day-name { font-weight: 500; color: #111827; font-size: 14px; }

        /* Buttons */
        .btn-back {
            display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px;
            border-radius: 8px; border: 1px solid #d1d5db; background: #fff; color: #374151;
            font-size: 13px; font-weight: 500; text-decoration: none; transition: all 0.2s; white-space: nowrap;
        }
        .btn-back:hover { background: #f9fafb; border-color: #9ca3af; }

        .form-actions { margin-top: 24px; display: flex; justify-content: flex-end; }
        .btn-primary {
            display: inline-flex; justify-content: center; align-items: center;
            padding: 12px 24px; background: #1e4a8d; color: #fff; border: none; border-radius: 8px;
            font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s; min-width: 140px;
        }
        .btn-primary:hover { background: #163a75; }

        /* Mobile */
        @media (max-width: 600px) {
            .card-header { flex-direction: column; gap: 12px; }
            .btn-back { align-self: flex-start; }
            .form-content { padding: 16px; }
            .btn-primary { width: 100%; }
        }
    </style>
</x-app>