<x-app title="Edit Jadwal Karyawan">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"/>
                    <line x1="16" y1="2" x2="16" y2="6" stroke-width="2"/>
                    <line x1="8" y1="2" x2="8" y2="6" stroke-width="2"/>
                    <line x1="3" y1="10" x2="21" y2="10" stroke-width="2"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Edit Jadwal Karyawan</h1>
                <p class="section-subtitle">Perbarui shift dan cakupan lokasi presensi karyawan.</p>
            </div>
        </div>
    </x-slot>

    <div class="schedule-edit-page">
        <a href="{{ route('hr.schedules.index') }}" class="schedule-edit-back" aria-label="Kembali ke master jadwal karyawan">
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

        <form id="schedule-edit-form" action="{{ route('hr.schedules.update', $schedule->id) }}" method="POST" class="schedule-edit-form">
            @csrf
            @method('PUT')

            <section class="schedule-edit-card schedule-edit-employee-card">
                <div class="schedule-edit-employee-avatar">{{ strtoupper(substr($schedule->user->name, 0, 1)) }}</div>
                <div class="schedule-edit-employee-body">
                    <span class="schedule-edit-label">Karyawan</span>
                    <h2>{{ $schedule->user->name }}</h2>
                    <p>Data karyawan tidak dapat diubah dari halaman ini.</p>
                </div>
                <input type="hidden" name="user_id" value="{{ $schedule->user_id }}">
            </section>

            <section class="schedule-edit-card">
                <div class="schedule-edit-card-header">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke-width="2"/>
                        <polyline points="12 7 12 12 15 14" stroke-width="2"/>
                    </svg>
                    <h2>Shift Kerja</h2>
                </div>

                <div class="schedule-edit-field">
                    <label for="shift_id">Pilih shift <span aria-hidden="true">*</span></label>
                    <select name="shift_id" id="shift_id" required>
                        <option value="">-- Pilih Shift --</option>
                        @foreach($shifts as $s)
                            <option value="{{ $s->id }}" @selected($schedule->shift_id == $s->id)>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </section>

            <section class="schedule-edit-card">
                <div class="schedule-edit-card-header">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11z"/>
                        <circle cx="12" cy="10" r="2.5" stroke-width="2"/>
                    </svg>
                    <div>
                        <h2>Cakupan Lokasi Presensi</h2>
                        <p>Pilih aturan lokasi yang digunakan saat absensi.</p>
                    </div>
                </div>

                @php($selectedIds = (array) old('location_ids', $selectedLocationIds))
                @php($locationMode = old('location_mode', $schedule->is_flexible ? 'flexible' : ($schedule->is_all_locations ? 'all' : 'selected')))
                <div class="schedule-edit-location-modes">
                    <label class="schedule-edit-location-mode">
                        <input type="radio" name="location_mode" value="selected" @checked($locationMode === 'selected')>
                        <span class="schedule-edit-location-mode-copy">
                            <strong>Lokasi Terpilih</strong>
                            <small>Pilih satu atau beberapa lokasi kantor.</small>
                        </span>
                    </label>
                    <label class="schedule-edit-location-mode">
                        <input type="radio" name="location_mode" value="all" @checked($locationMode === 'all')>
                        <span class="schedule-edit-location-mode-copy">
                            <strong>All Locations</strong>
                            <small>Semua lokasi presensi aktif yang terdaftar.</small>
                        </span>
                    </label>
                    <label class="schedule-edit-location-mode">
                        <input type="radio" name="location_mode" value="flexible" @checked($locationMode === 'flexible')>
                        <span class="schedule-edit-location-mode-copy">
                            <strong>Fleksibel</strong>
                            <small>Di luar lokasi absen wajib menyertakan alasan saat Clock In.</small>
                        </span>
                    </label>
                </div>

                <div id="location-options" class="schedule-edit-location-options">
                    <span class="schedule-edit-label">Lokasi Absen <span aria-hidden="true">*</span></span>
                    <div class="schedule-edit-location-list">
                        <label class="schedule-edit-location-option">
                            <input type="checkbox" id="schedule-edit-select-all-locations" aria-label="Pilih semua lokasi absen">
                            <span>Pilih semua</span>
                        </label>
                        @foreach($locations as $loc)
                            <label class="schedule-edit-location-option">
                                <input type="checkbox" name="location_ids[]" value="{{ $loc->id }}" @checked(in_array($loc->id, $selectedIds))>
                                <span>{{ $loc->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </section>
        </form>
    </div>

    <div class="schedule-edit-action-bar" aria-label="Aksi edit jadwal">
        <div class="schedule-edit-action-dock">
            <a href="{{ route('hr.schedules.index') }}" class="schedule-edit-action-secondary">Batal</a>
            <button type="submit" form="schedule-edit-form" class="schedule-edit-action-primary">Simpan Perubahan</button>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const locationModes = Array.from(document.querySelectorAll('input[name="location_mode"]'));
            const locationOptionsContainer = document.getElementById('location-options');
            const locationOptions = Array.from(document.querySelectorAll('#location-options input[name="location_ids[]"]'));
            const selectAllLocations = document.getElementById('schedule-edit-select-all-locations');

            function syncLocationOptions() {
                const selectedMode = locationModes.find((input) => input.checked)?.value;
                const isLocationMode = ['selected', 'flexible'].includes(selectedMode);
                locationOptions.forEach((checkbox) => {
                    checkbox.disabled = !isLocationMode;
                });
                if (selectAllLocations) {
                    const selectedCount = locationOptions.filter((checkbox) => checkbox.checked).length;
                    selectAllLocations.checked = locationOptions.length > 0 && selectedCount === locationOptions.length;
                    selectAllLocations.indeterminate = selectedCount > 0 && selectedCount < locationOptions.length;
                    selectAllLocations.disabled = !isLocationMode;
                }
                locationOptionsContainer.hidden = !isLocationMode;
            }

            locationModes.forEach((input) => input.addEventListener('change', syncLocationOptions));
            selectAllLocations?.addEventListener('change', function () {
                locationOptions.forEach((checkbox) => {
                    checkbox.checked = this.checked;
                });
                syncLocationOptions();
            });
            locationOptions.forEach((checkbox) => checkbox.addEventListener('change', syncLocationOptions));
            syncLocationOptions();
        });
    </script>
    @endpush

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
        .schedule-edit-field label { display: block; margin-bottom: 7px; color: var(--text-secondary, #374151); font-size: 12px; font-weight: 600; }
        .schedule-edit-field label span, .schedule-edit-location-options > .schedule-edit-label span { color: #dc2626; }
        .schedule-edit-field select { width: 100%; padding: 10px 12px; border: 1.5px solid var(--border-light, #E5E7EB); border-radius: 10px; outline: none; background: var(--white, #fff); color: var(--text-primary, #111827); font: inherit; font-size: 14px; transition: all 0.2s ease; }
        .schedule-edit-field select:focus { border-color: var(--primary, #145DA0); box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1); }

        .schedule-edit-location-modes { display: grid; gap: 8px; }
        .schedule-edit-location-mode { display: flex; gap: 10px; padding: 12px; border: 1px solid var(--border-light, #E5E7EB); border-radius: 12px; cursor: pointer; transition: all 0.15s ease; }
        .schedule-edit-location-mode:hover { border-color: rgba(20, 93, 160, 0.4); background: rgba(20, 93, 160, 0.03); }
        .schedule-edit-location-mode:has(input:checked) { border-color: var(--primary, #145DA0); background: rgba(20, 93, 160, 0.06); }
        .schedule-edit-location-mode input, .schedule-edit-location-option input { margin: 2px 0 0; accent-color: var(--primary, #145DA0); }
        .schedule-edit-location-mode-copy { display: flex; flex-direction: column; gap: 2px; }
        .schedule-edit-location-mode-copy strong { color: var(--text-primary, #111827); font-size: 14px; }
        .schedule-edit-location-mode-copy small { color: var(--text-muted, #6B7280); font-size: 12px; font-weight: 500; line-height: 1.4; }
        .schedule-edit-location-options { margin-top: 16px; }
        .schedule-edit-location-options > .schedule-edit-label { margin-bottom: 8px; }
        .schedule-edit-location-list { display: grid; gap: 6px; max-height: 200px; overflow-y: auto; padding: 8px; border: 1px solid var(--border-light, #E5E7EB); border-radius: 10px; background: var(--gray-50, #F5F7FA); }
        .schedule-edit-location-option { display: flex; align-items: flex-start; gap: 8px; padding: 8px; border-radius: 8px; color: var(--text-secondary, #374151); font-size: 13px; font-weight: 600; cursor: pointer; }
        .schedule-edit-location-option:hover { background: var(--white, #fff); }

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
