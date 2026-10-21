<x-app title="Edit Shift">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke-width="2"/>
                    <polyline points="12 7 12 12 15 14" stroke-width="2"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Edit Shift Kerja</h1>
                <p class="section-subtitle">Perbarui jadwal jam kerja dan aturan libur untuk shift ini.</p>
            </div>
        </div>
    </x-slot>

    <div class="schedule-edit-page">
        <a href="{{ route('hr.shifts.index') }}" class="schedule-edit-back" aria-label="Kembali ke master shift kerja">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali</span>
        </a>

        @if ($errors->any())
            <div class="schedule-edit-alert" role="alert">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                    <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form id="shift-edit-form" method="POST" action="{{ route('hr.shifts.update', $shift->id) }}" class="schedule-edit-form">
            @csrf
            @method('PUT')

            <section class="schedule-edit-card schedule-edit-employee-card">
                <div class="schedule-edit-employee-avatar">{{ strtoupper(substr($shift->name, 0, 1)) }}</div>
                <div class="schedule-edit-employee-body">
                    <span class="schedule-edit-label">Shift Kerja</span>
                    <h2>{{ $shift->name }}</h2>
                    <p>{{ $shift->description ?: 'Atur pola jam kerja harian dan aturan libur untuk shift ini.' }}</p>
                </div>
            </section>

            <section class="schedule-edit-card">
                <div class="schedule-edit-card-header">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke-width="2"/>
                        <polyline points="12 7 12 12 15 14" stroke-width="2"/>
                    </svg>
                    <div>
                        <h2>Informasi Shift Kerja</h2>
                        <p>Perbarui nama, deskripsi, dan status keaktifan shift.</p>
                    </div>
                </div>

                <div class="schedule-edit-fields">
                    <div class="schedule-edit-field">
                        <label for="name">Nama Shift <span aria-hidden="true">*</span></label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control"
                            value="{{ old('name', $shift->name) }}"
                            placeholder="Contoh: Shift Pagi, Office Hour"
                            required>
                    </div>

                    <div class="schedule-edit-field">
                        <label for="description">Deskripsi</label>
                        <textarea
                            id="description"
                            name="description"
                            class="form-control"
                            rows="2"
                            placeholder="Keterangan tambahan (opsional)">{{ old('description', $shift->description) }}</textarea>
                    </div>

                    <div class="schedule-edit-location-modes">
                        <label class="schedule-edit-location-mode">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                @checked(old('is_active', $shift->is_active))>
                            <span class="schedule-edit-location-mode-copy">
                                <strong>Shift Aktif</strong>
                                <small>Shift yang aktif dapat dipilih pada pengaturan jadwal kerja karyawan.</small>
                            </span>
                        </label>
                    </div>
                </div>
            </section>

            <section class="schedule-edit-card">
                <div class="schedule-edit-card-header">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"/>
                        <line x1="16" y1="2" x2="16" y2="6" stroke-width="2"/>
                        <line x1="8" y1="2" x2="8" y2="6" stroke-width="2"/>
                        <line x1="3" y1="10" x2="21" y2="10" stroke-width="2"/>
                    </svg>
                    <div>
                        <h2>Pengaturan Jadwal Harian</h2>
                        <p>Atur jam masuk, jam pulang, penanda lintas hari, dan hari libur mingguan.</p>
                    </div>
                </div>

                @php
                    $days = [
                        1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
                        4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu',
                    ];
                    $dayData = $shift->days->keyBy('day_of_week');
                @endphp

                <div class="table-responsive">
                    <table class="shift-table">
                        <thead>
                            <tr>
                                <th style="width: 15%">Hari</th>
                                <th style="width: 20%">Jam Masuk</th>
                                <th style="width: 20%">Jam Pulang</th>
                                <th style="width: 10%; text-align: center;">Lintas Hari</th>
                                <th style="width: 10%; text-align: center;">Libur</th>
                                <th style="width: 25%">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($days as $dayNumber => $dayName)
                                @php
                                    $current = $dayData[$dayNumber] ?? null;

                                    $startVal = old("days.$dayNumber.start_time");
                                    if ($startVal === null && $current && $current->start_time) {
                                        try { $startVal = \Carbon\Carbon::parse($current->start_time)->format('H:i'); } catch(\Exception $e){}
                                    }

                                    $endVal = old("days.$dayNumber.end_time");
                                    if ($endVal === null && $current && $current->end_time) {
                                        try { $endVal = \Carbon\Carbon::parse($current->end_time)->format('H:i'); } catch(\Exception $e){}
                                    }

                                    $holidayVal = old("days.$dayNumber.is_holiday");
                                    $isHoliday = $holidayVal !== null ? (bool)$holidayVal : (bool)($current?->is_holiday);

                                    $overnightVal = old("days.$dayNumber.is_overnight");
                                    $isOvernight = $overnightVal !== null
                                        ? filter_var($overnightVal, FILTER_VALIDATE_BOOLEAN)
                                        : (bool)($current?->is_overnight);

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
                                                name="days[{{ $dayNumber }}][is_overnight]"
                                                class="overnight-toggle overnight-{{ $dayNumber }}"
                                                value="1"
                                                @checked($isOvernight)>
                                        </label>
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
            </section>
        </form>
    </div>

    <div class="schedule-edit-action-bar" aria-label="Aksi edit shift">
        <div class="schedule-edit-action-dock">
            <a href="{{ route('hr.shifts.index') }}" class="schedule-edit-action-secondary">Batal</a>
            <button type="submit" form="shift-edit-form" class="schedule-edit-action-primary">Simpan Perubahan</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.holiday-toggle').forEach(cb => {
                const day = cb.dataset.day;
                const startInput = document.querySelector('.start-' + day);
                const endInput = document.querySelector('.end-' + day);
                const overnightInput = document.querySelector('.overnight-' + day);
                const noteInput = document.querySelector('.note-' + day);

                const applyState = () => {
                    if (cb.checked) {
                        if(startInput) {
                            startInput.value = '';
                            startInput.disabled = true;
                        }
                        if(endInput) {
                            endInput.value = '';
                            endInput.disabled = true;
                        }
                        if(overnightInput) {
                            overnightInput.checked = false;
                            overnightInput.disabled = true;
                        }
                    } else {
                        if(startInput) startInput.disabled = false;
                        if(endInput) endInput.disabled = false;
                        if(overnightInput) overnightInput.disabled = false;
                        if(noteInput) noteInput.disabled = false;
                    }
                };

                applyState();
                cb.addEventListener('change', applyState);
            });
        });
    </script>

    <style>
        .schedule-edit-page { display: flex; flex-direction: column; gap: 12px; width: 100%; padding-bottom: 160px; }
        .section-header-inline { display: flex; align-items: center; gap: 10px; }
        .section-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .icon-navy { color: var(--primary-dark, #0A3D62); background: rgba(10, 61, 98, 0.08); }
        .section-title { margin: 0; color: var(--text-primary, #111827); font-size: 1rem; font-weight: 800; letter-spacing: -0.01em; line-height: 1.25; }
        .section-subtitle { margin: 0; color: var(--text-muted, #6B7280); font-size: 0.8125rem; font-weight: 500; line-height: 1.35; }

        .schedule-edit-back { align-self: flex-start; display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 12px 0 10px; border: 1px solid var(--border-light, #E5E7EB); border-radius: 10px; background: var(--white, #fff); box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04); color: var(--text-muted, #6B7280); font-size: 0.75rem; font-weight: 600; text-decoration: none; transition: all 0.15s ease; }
        .schedule-edit-back:hover { border-color: var(--primary, #145DA0); background: var(--gray-50, #F5F7FA); color: var(--primary, #145DA0); }
        .schedule-edit-back svg { flex-shrink: 0; transition: transform 0.2s ease; }
        .schedule-edit-back:hover svg { transform: translateX(-2px); }

        .schedule-edit-alert { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 12px; background: rgba(239, 68, 68, 0.08); color: #dc2626; font-size: 13px; font-weight: 500; }

        .schedule-edit-form { display: flex; flex-direction: column; gap: 12px; }
        .schedule-edit-card { padding: 20px; border: 1px solid var(--border-light, #E5E7EB); border-radius: 16px; background: var(--white, #fff); box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04); }
        .schedule-edit-employee-card { display: flex; align-items: center; gap: 14px; }
        .schedule-edit-employee-avatar { display: flex; align-items: center; justify-content: center; width: 52px; height: 52px; flex: 0 0 52px; border-radius: 14px; background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0)); color: #fff; font-size: 20px; font-weight: 700; }
        .schedule-edit-employee-body { min-width: 0; }
        .schedule-edit-label { display: block; color: var(--text-muted, #6B7280); font-size: 11px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; }
        .schedule-edit-employee-body h2 { margin: 3px 0; color: var(--text-primary, #111827); font-size: 1.125rem; font-weight: 700; line-height: 1.2; }
        .schedule-edit-employee-body p { margin: 0; color: var(--text-muted, #6B7280); font-size: 12px; font-weight: 500; line-height: 1.4; }

        .schedule-edit-card-header { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--border-light, #E5E7EB); color: var(--primary-dark, #0A3D62); }
        .schedule-edit-card-header h2 { margin: 0; color: var(--text-primary, #111827); font-size: 0.8125rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; }
        .schedule-edit-card-header p { margin: 3px 0 0; color: var(--text-muted, #6B7280); font-size: 12px; font-weight: 500; line-height: 1.4; }

        .schedule-edit-fields { display: flex; flex-direction: column; gap: 14px; }
        .schedule-edit-field label { display: block; margin-bottom: 7px; color: var(--text-secondary, #374151); font-size: 12px; font-weight: 600; }
        .schedule-edit-field label span { color: #dc2626; }
        .form-control { width: 100%; padding: 10px 12px; border: 1.5px solid var(--border-light, #E5E7EB); border-radius: 10px; outline: none; background: var(--white, #fff); color: var(--text-primary, #111827); font: inherit; font-size: 14px; transition: all 0.2s ease; }
        .form-control:focus { border-color: var(--primary, #145DA0); box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1); }
        .form-control:disabled { background: var(--gray-50, #F5F7FA); color: var(--text-light, #9CA3AF); cursor: not-allowed; }
        .form-control-sm { padding: 8px 10px; font-size: 13px; border-radius: 8px; }

        .schedule-edit-location-modes { display: grid; gap: 8px; }
        .schedule-edit-location-mode { display: flex; gap: 10px; padding: 12px; border: 1px solid var(--border-light, #E5E7EB); border-radius: 12px; cursor: pointer; transition: all 0.15s ease; }
        .schedule-edit-location-mode:hover { border-color: rgba(20, 93, 160, 0.4); background: rgba(20, 93, 160, 0.03); }
        .schedule-edit-location-mode:has(input:checked) { border-color: var(--primary, #145DA0); background: rgba(20, 93, 160, 0.06); }
        .schedule-edit-location-mode input { margin: 2px 0 0; width: 16px; height: 16px; accent-color: var(--primary, #145DA0); flex-shrink: 0; }
        .schedule-edit-location-mode-copy { display: flex; flex-direction: column; gap: 2px; }
        .schedule-edit-location-mode-copy strong { color: var(--text-primary, #111827); font-size: 14px; }
        .schedule-edit-location-mode-copy small { color: var(--text-muted, #6B7280); font-size: 12px; font-weight: 500; line-height: 1.4; }

        .checkbox-wrapper { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .checkbox-wrapper input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--primary, #145DA0); cursor: pointer; }
        .checkbox-wrapper input[type="checkbox"]:disabled { cursor: not-allowed; opacity: 0.5; }
        .center-box { justify-content: center; }

        .table-responsive { overflow-x: auto; border: 1px solid var(--border-light, #E5E7EB); border-radius: 12px; }
        .shift-table { width: 100%; border-collapse: collapse; min-width: 640px; }
        .shift-table th { background: var(--gray-50, #F5F7FA); padding: 12px 14px; text-align: left; font-size: 0.6875rem; font-weight: 700; color: var(--text-muted, #6B7280); text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid var(--border-light, #E5E7EB); }
        .shift-table td { padding: 10px 14px; border-bottom: 1px solid var(--border-light, #F3F4F6); vertical-align: middle; }
        .shift-table tr:last-child td { border-bottom: none; }
        .shift-table tbody tr:hover td { background: var(--gray-50, #F5F7FA); }
        .day-name { font-weight: 600; color: var(--text-primary, #111827); font-size: 13.5px; }

        .schedule-edit-action-bar { position: fixed; right: 0; bottom: 0; left: 0; z-index: 50; pointer-events: none; transition: left 0.35s cubic-bezier(0.4, 0, 0.2, 1), right 0.35s cubic-bezier(0.4, 0, 0.2, 1), bottom 0.35s ease; }
        .schedule-edit-action-dock { display: flex; flex-direction: column-reverse; gap: 10px; margin: 0 auto; padding: 14px 16px calc(14px + env(safe-area-inset-bottom)); border-top: 1px solid var(--border-light, #E5E7EB); border-radius: 18px 18px 0 0; background: var(--white, #fff); box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.06); pointer-events: auto; }
        .schedule-edit-action-secondary, .schedule-edit-action-primary { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 11px 18px; border-radius: 10px; font: inherit; font-size: 13.5px; font-weight: 600; text-align: center; text-decoration: none; cursor: pointer; transition: all 0.2s; }
        .schedule-edit-action-secondary { border: 1.5px solid var(--border-light, #E5E7EB); background: var(--gray-50, #F5F7FA); color: var(--text-secondary, #6B7280); }
        .schedule-edit-action-primary { border: 0; background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0)); color: #fff; box-shadow: 0 2px 8px rgba(10, 61, 98, 0.2); }
        .schedule-edit-action-secondary:hover { border-color: var(--gray-300, #D1D5DB); background: var(--white, #fff); }
        .schedule-edit-action-primary:hover { box-shadow: 0 4px 12px rgba(10, 61, 98, 0.3); transform: translateY(-1px); }

        @media (min-width: 640px) {
            .schedule-edit-action-dock { flex-direction: row; gap: 12px; }
            .schedule-edit-action-secondary, .schedule-edit-action-primary { flex: 1 1 0; min-width: 0; padding: 12px 24px; font-size: 14px; }
        }

        @media (min-width: 768px) {
            .schedule-edit-page { padding-bottom: 100px; }
            .schedule-edit-card { padding: 24px; }
        }

        @media (min-width: 1025px) {
            .schedule-edit-action-bar { left: calc(var(--sidebar-width) + 12px); right: 0; bottom: 16px; padding: 0 16px; background: transparent; border: 0; box-shadow: none; }
            .app.sidebar-collapsed .schedule-edit-action-bar { left: 0; }
            .schedule-edit-action-dock { max-width: 1200px; padding: 10px; border: 1px solid var(--border-light, #E5E7EB); border-radius: 18px; box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.06), 0 1px 3px rgba(0, 0, 0, 0.04); }
        }

        @media (max-width: 420px) {
            .schedule-edit-employee-card { align-items: flex-start; }
            .schedule-edit-employee-avatar { width: 44px; height: 44px; flex-basis: 44px; border-radius: 12px; font-size: 17px; }
            .schedule-edit-employee-body h2 { font-size: 1rem; }
        }
    </style>
</x-app>
