<x-app title="Master Jadwal Karyawan">

    @if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
    @endif

    @php
        $activeScheduleFilterCount = collect([$pt ?? null, $positionId ?? null, $shiftId ?? null])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->count();
        $hasActiveScheduleFilters = $activeScheduleFilterCount > 0;
    @endphp

    <div class="schedule-filter-card">
        <form method="GET" action="{{ route('hr.schedules.index') }}" class="schedule-filter-form">
            <div class="schedule-search-row">
                <div class="schedule-search-input-wrap">
                    <svg class="schedule-search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Cari nama karyawan..." autocomplete="off" class="schedule-search-input" aria-label="Cari karyawan" data-schedule-live-search>
                </div>

                @if(($search ?? null) || $hasActiveScheduleFilters)
                <a href="{{ route('hr.schedules.index') }}" class="schedule-btn-reset">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reset
                </a>
                @endif

                <button type="button" id="schedule-filter-toggle" class="schedule-btn-toggle-filter {{ $hasActiveScheduleFilters ? 'active' : '' }}" aria-controls="schedule-filter-panel" aria-expanded="{{ $hasActiveScheduleFilters ? 'true' : 'false' }}">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filter
                    @if($hasActiveScheduleFilters)
                    <span class="schedule-filter-badge">{{ $activeScheduleFilterCount }}</span>
                    @endif
                </button>
            </div>

            <div class="schedule-filter-panel" id="schedule-filter-panel" @unless($hasActiveScheduleFilters) hidden @endunless>
                <div class="schedule-filter-grid">
                    <div class="schedule-filter-group">
                        <label class="schedule-filter-label" for="schedule_pt_id">PT</label>
                        <div class="schedule-select-wrap">
                            <select name="pt_id" id="schedule_pt_id" class="schedule-select">
                                <option value="">Semua PT</option>
                                @foreach($ptOptions as $ptOption)
                                <option value="{{ $ptOption->id }}" @selected(($pt ?? '') == $ptOption->id)>{{ $ptOption->name }}</option>
                                @endforeach
                            </select>
                            <svg class="schedule-select-arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <div class="schedule-filter-group">
                        <label class="schedule-filter-label" for="schedule_position_id">Jabatan</label>
                        <div class="schedule-select-wrap">
                            <select name="position_id" id="schedule_position_id" class="schedule-select">
                                <option value="">Semua Jabatan</option>
                                @foreach($positionOptions as $pos)
                                <option value="{{ $pos->id }}" @selected(($positionId ?? '') == $pos->id)>{{ $pos->name }}</option>
                                @endforeach
                            </select>
                            <svg class="schedule-select-arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <div class="schedule-filter-group">
                        <label class="schedule-filter-label" for="schedule_shift_id">Shift</label>
                        <div class="schedule-select-wrap">
                            <select name="shift_id" id="schedule_shift_id" class="schedule-select">
                                <option value="">Semua Shift</option>
                                <option value="none" @selected(($shiftId ?? '') === 'none')>Belum Ada Jadwal</option>
                                @foreach($shiftOptions as $shift)
                                <option value="{{ $shift->id }}" @selected(($shiftId ?? '') == $shift->id)>{{ $shift->name }}</option>
                                @endforeach
                            </select>
                            <svg class="schedule-select-arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <div class="schedule-filter-actions">
                        <button type="submit" class="schedule-btn-apply">Terapkan Filter</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @php
        $selectedUserIds = old('user_ids', []);
        $selectedUserIds = is_array($selectedUserIds) ? $selectedUserIds : [];
        $selectedLocationIds = (array) old('location_ids', []);
    @endphp

    <div data-schedule-results aria-live="polite" aria-busy="false">
    <div class="card">
        <div class="bulk-actions">
            <span id="selected-users-label" class="text-muted">0 karyawan dipilih</span>
            <button type="button" id="open-bulk-schedule-modal" class="btn-primary" data-modal-target="bulk-schedule-modal" disabled>
                Atur Jadwal Terpilih
            </button>
        </div>
        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 44px; text-align: center;">
                            <input type="checkbox" id="select-all-users" aria-label="Pilih semua karyawan yang tampil" @disabled($items->isEmpty())>
                        </th>
                        <th style="min-width: 200px;">Karyawan</th>
                        <th>PT</th>
                        <th>Shift Aktif</th>
                        <th>Lokasi</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td style="text-align: center;">
                            <input
                                type="checkbox"
                                class="user-schedule-checkbox"
                                value="{{ $item->id }}"
                                aria-label="Pilih {{ $item->name }}"
                                @checked(in_array($item->id, $selectedUserIds))>
                        </td>
                        <td>
                            <div class="user-info">
                                <span class="fw-bold">{{ $item->name }}</span>
                                <span class="text-muted">{{ $item->position_name ?? '-' }}</span>
                            </div>
                        </td>

                        <td>
                            <span class="badge-basic">{{ $item->pt_name ?? '-' }}</span>
                        </td>

                        <td>
                            @if($item->shift_name)
                                <span class="badge-shift">{{ $item->shift_name }}</span>
                            @else
                                <span class="text-muted" style="font-size:12px; font-style:italic;">Belum diatur</span>
                            @endif
                        </td>

                        <td>
                            <div class="text-truncate" style="max-width: 150px;" title="{{ $item->is_all_locations ? 'All Locations' : ($item->location_names ?? $item->location_name) }}">
                                {{ $item->is_all_locations ? 'All Locations' : ($item->location_names ?? $item->location_name ?? '-') }}
                            </div>
                        </td>

                        <td style="width: 100px;">
                            <div class="action-buttons">
                                @if($item->schedule_id)
                                    <a href="{{ route('hr.schedules.edit', $item->schedule_id) }}" class="btn-action edit">
                                        Edit
                                    </a>
                                    
                                    <button type="button" 
                                            data-modal-target="delete-schedule-{{ $item->schedule_id }}" 
                                            class="btn-action delete">
                                        Hapus
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            Tidak ada data karyawan ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach($items as $item)
        @if($item->schedule_id)
        <x-modal
            id="delete-schedule-{{ $item->schedule_id }}"
            title="Hapus Jadwal?"
            type="confirm"
            confirmLabel="Hapus"
            cancelLabel="Batal"
            :confirmFormAction="route('hr.schedules.destroy', $item->schedule_id)"
            confirmFormMethod="DELETE">
            <p style="margin:0 0 4px 0;">
                Yakin ingin menghapus jadwal untuk:
            </p>
            <p style="margin:0; font-weight:700; color:#1f2937;">
                {{ $item->name }}
            </p>
            <p style="margin:8px 0 0 0; font-size:0.85rem; color:#6b7280;">
                Karyawan tidak akan memiliki shift aktif setelah ini.
            </p>
        </x-modal>
        @endif
    @endforeach
    </div>

    <x-modal id="bulk-schedule-modal" title="Atur Jadwal Terpilih" type="form">
        <form action="{{ route('hr.schedules.store') }}" method="POST" class="bulk-schedule-form">
            @csrf

            @if($errors->any())
            <div class="alert-error">
                {{ $errors->first() }}
            </div>
            @endif

            <p class="bulk-schedule-description">
                Jadwal baru akan diterapkan untuk <strong id="selected-users-modal-label">0</strong> karyawan terpilih, termasuk jadwal yang sudah ada.
            </p>

            <div id="selected-user-ids"></div>

            <div class="form-group">
                <label for="bulk_shift_id">Shift Kerja <span class="req">*</span></label>
                <select name="shift_id" id="bulk_shift_id" class="form-control" required>
                    <option value="">-- Pilih Shift --</option>
                    @foreach($shiftOptions as $shift)
                    <option value="{{ $shift->id }}" @selected(old('shift_id') == $shift->id)>{{ $shift->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="location-all-option">
                    <input type="checkbox" name="all_locations" id="bulk_all_locations" value="1" @checked(old('all_locations'))>
                    <span>All Locations</span>
                </label>
                <small class="text-muted">Pilih ini untuk mengabaikan pembatasan daftar lokasi.</small>
            </div>

            <div class="form-group" id="bulk-location-options">
                <label>Lokasi Presensi <span class="req">*</span></label>
                <div class="location-checkbox-list">
                    @foreach($locationOptions as $location)
                    <label class="location-checkbox-option">
                        <input type="checkbox" name="location_ids[]" value="{{ $location->id }}" @checked(in_array($location->id, $selectedLocationIds))>
                        <span>{{ $location->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="modal-form-actions">
                <button type="button" class="btn-reset" data-modal-close="true">Batal</button>
                <button type="submit" class="btn-primary">Simpan Jadwal</button>
            </div>
        </form>
    </x-modal>

    <style>
        /* --- UTILITY --- */
        .mb-4 { margin-bottom: 16px; }
        .fw-bold { font-weight: 600; color: #111827; }
        .text-muted { color: #6b7280; font-size: 11px; }
        .text-right { text-align: right; }
        .text-truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* --- ALERT --- */
        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #a7f3d0;
            margin-bottom: 16px;
            font-size: 14px;
        }

        /* --- CARD --- */
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid #f3f4f6;
            overflow: hidden;
            padding: 0;
        }
        [data-schedule-results].is-loading { opacity: 0.55; pointer-events: none; }

        .bulk-actions {
            padding: 14px 20px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .bulk-schedule-description { margin: 0 0 18px; font-size: 13px; color: #4b5563; }
        .bulk-schedule-form .form-group { margin-bottom: 16px; display: flex; flex-direction: column; gap: 6px; }
        .bulk-schedule-form label { font-size: 13px; font-weight: 600; color: #374151; }
        .location-all-option, .location-checkbox-option { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .location-checkbox-list { display: grid; gap: 8px; max-height: 180px; overflow-y: auto; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; }
        .location-checkbox-option { font-size: 13px; font-weight: 500; }
        .location-checkbox-list input { accent-color: #1e4a8d; }
        .req { color: #dc2626; }
        .alert-error { margin-bottom: 16px; padding: 10px 12px; border: 1px solid #fecaca; border-radius: 8px; background: #fef2f2; color: #991b1b; font-size: 13px; }
        .modal-form-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px; }

        /* --- FILTER SECTION --- */
        .schedule-filter-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            margin-bottom: 16px;
            overflow: hidden;
        }
        .schedule-filter-form { padding: 14px 16px; }
        .schedule-search-row { display: flex; flex-direction: column; gap: 10px; }
        .schedule-search-input-wrap { position: relative; flex: 1; }
        .schedule-search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            color: #9ca3af;
            pointer-events: none;
            transform: translateY(-50%);
        }
        .schedule-search-input {
            width: 100%;
            padding: 10px 14px 10px 42px;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            color: #1f2937;
            font-size: 13px;
            outline: none;
        }
        .schedule-search-input:focus { border-color: #1e4a8d; box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.08); }
        .schedule-btn-reset,
        .schedule-btn-toggle-filter,
        .schedule-btn-apply {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 40px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }
        .schedule-btn-reset,
        .schedule-btn-toggle-filter {
            padding: 10px 16px;
            border: 1.5px solid #e5e7eb;
            background: #fff;
            color: #6b7280;
        }
        .schedule-btn-reset:hover,
        .schedule-btn-toggle-filter:hover { border-color: #1e4a8d; color: #1e4a8d; }
        .schedule-btn-toggle-filter.active,
        .schedule-btn-apply { border: 1px solid #1e4a8d; background: #1e4a8d; color: #fff; }
        .schedule-filter-badge {
            min-width: 18px;
            padding: 2px 6px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.3);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            text-align: center;
        }
        .schedule-filter-panel {
            margin: 14px -16px -14px;
            padding: 14px 16px;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        .schedule-filter-grid { display: grid; gap: 12px; }
        .schedule-filter-group { display: grid; gap: 6px; }
        .schedule-filter-label { color: #374151; font-size: 12px; font-weight: 600; }
        .schedule-select-wrap { position: relative; }
        .schedule-select {
            width: 100%;
            padding: 10px 38px 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            appearance: none;
            background: #fff;
            color: #374151;
            font-size: 13px;
            outline: none;
        }
        .schedule-select:focus { border-color: #1e4a8d; }
        .schedule-select-arrow { position: absolute; right: 12px; top: 50%; color: #6b7280; pointer-events: none; transform: translateY(-50%); }
        .schedule-filter-actions { display: flex; }
        .schedule-btn-apply { width: 100%; padding: 10px 16px; }

        .form-control {
            padding: 9px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 13.5px;
            color: #374151;
            background: #fff;
            width: 100%;
            outline: none;
        }
        .form-control:focus { border-color: #1e4a8d; }

        .btn-primary {
            padding: 9px 18px;
            background: #1e4a8d;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13.5px;
            font-weight: 600;
            white-space: nowrap;
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
        .custom-table { width: 100%; border-collapse: collapse; min-width: 900px; }

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

        /* --- CONTENT STYLING --- */
        /* --- CONTENT STYLING --- */
        .user-info { display: flex; flex-direction: column; gap: 2px; text-align: left; align-items: flex-start; justify-content: flex-start; }
        .user-info .fw-bold { font-size: 13px; }
        
        .badge-basic {
            background: #f3f4f6;
            color: #374151;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 10px;
            border: 1px solid #e5e7eb;
        }

        .badge-shift {
            background: #eff6ff;
            color: #1d4ed8;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
        }

        /* --- ACTION BUTTONS --- */
        .action-buttons {
            display: flex;
            justify-content: flex-start;
            gap: 6px;
        }

        .btn-action {
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-action.edit {
            background: #fff;
            border-color: #d1d5db;
            color: #374151;
        }
        .btn-action.edit:hover { background: #f3f4f6; border-color: #9ca3af; }

        .btn-action.delete {
            background: #fee2e2;
            border-color: #fecaca;
            color: #b91c1c;
        }
        .btn-action.delete:hover { background: #fecaca; }

        .btn-action.primary {
            background: #1e4a8d;
            border-color: #1e4a8d;
            color: #fff;
        }
        .btn-action.primary:hover { background: #163a75; border-color: #163a75; }

        .empty-state { padding: 40px; text-align: center; color: #9ca3af; font-style: italic; }

        @media(min-width: 640px) {
            .schedule-search-row { flex-direction: row; align-items: center; }
            .schedule-search-input-wrap { min-width: 260px; }
            .schedule-btn-reset,
            .schedule-btn-toggle-filter { flex-shrink: 0; }
            .schedule-filter-panel { margin: 16px -20px -16px; padding: 16px 20px; }
            .schedule-filter-form { padding: 16px 20px; }
            .schedule-filter-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); align-items: end; gap: 16px; }
            .schedule-filter-actions { grid-column: 1 / -1; justify-content: flex-end; }
            .schedule-btn-apply { width: auto; }
        }

        @media(max-width: 768px) {
            .btn-primary, .btn-reset { flex: 1; text-align: center; }
            .bulk-actions { align-items: stretch; flex-direction: column; }
            .modal-form-actions { flex-direction: column-reverse; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectedIds = document.getElementById('selected-user-ids');
            const selectedLabel = document.getElementById('selected-users-label');
            const selectedModalLabel = document.getElementById('selected-users-modal-label');
            const allLocations = document.getElementById('bulk_all_locations');
            const locationOptions = Array.from(document.querySelectorAll('#bulk-location-options input[type="checkbox"]'));
            const filterToggle = document.getElementById('schedule-filter-toggle');
            const filterPanel = document.getElementById('schedule-filter-panel');
            const filterForm = document.querySelector('.schedule-filter-form');
            const searchInput = filterForm?.querySelector('[data-schedule-live-search]');
            const results = document.querySelector('[data-schedule-results]');
            let searchTimer;
            let searchRequest;

            filterToggle?.addEventListener('click', function () {
                const willOpen = filterPanel.hidden;

                filterPanel.hidden = !willOpen;
                filterToggle.classList.toggle('active', willOpen);
                filterToggle.setAttribute('aria-expanded', String(willOpen));
            });

            function syncSelection() {
                const userCheckboxes = Array.from(document.querySelectorAll('.user-schedule-checkbox'));
                const selectAll = document.getElementById('select-all-users');
                const openModalButton = document.getElementById('open-bulk-schedule-modal');
                const selected = userCheckboxes.filter((checkbox) => checkbox.checked);

                selectedIds.replaceChildren(...selected.map((checkbox) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'user_ids[]';
                    input.value = checkbox.value;
                    return input;
                }));

                selectedLabel.textContent = `${selected.length} karyawan dipilih`;
                selectedModalLabel.textContent = selected.length;
                if (openModalButton) openModalButton.disabled = selected.length === 0;

                if (selectAll) {
                    selectAll.checked = selected.length > 0 && selected.length === userCheckboxes.length;
                    selectAll.indeterminate = selected.length > 0 && selected.length < userCheckboxes.length;
                }
            }

            document.addEventListener('change', function (event) {
                if (event.target.id === 'select-all-users') {
                    document.querySelectorAll('.user-schedule-checkbox').forEach((checkbox) => {
                        checkbox.checked = event.target.checked;
                    });
                    syncSelection();
                }

                if (event.target.classList.contains('user-schedule-checkbox')) syncSelection();
            });

            function syncLocationOptions() {
                locationOptions.forEach((checkbox) => {
                    checkbox.disabled = allLocations.checked;
                    if (allLocations.checked) checkbox.checked = false;
                });
            }

            allLocations?.addEventListener('change', syncLocationOptions);

            document.addEventListener('click', function (event) {
                const modalTarget = event.target.closest('[data-schedule-results] [data-modal-target]');
                if (modalTarget) {
                    const modal = document.getElementById(modalTarget.dataset.modalTarget);
                    if (modal) {
                        modal.style.display = 'flex';
                        document.body.style.overflow = 'hidden';
                    }
                }

                const modal = event.target.closest('[data-schedule-results] .modal-backdrop');
                if (modal && (event.target === modal || event.target.closest('[data-modal-close]'))) {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                }
            });

            async function loadScheduleResults() {
                if (!filterForm || !results) return;

                const url = new URL(filterForm.action, window.location.origin);
                const params = new URLSearchParams(new FormData(filterForm));
                params.delete('page');
                url.search = params.toString();

                searchRequest?.abort();
                const currentRequest = new AbortController();
                searchRequest = currentRequest;
                results.classList.add('is-loading');
                results.setAttribute('aria-busy', 'true');

                try {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: currentRequest.signal,
                    });
                    if (!response.ok) throw new Error('Pencarian jadwal gagal');

                    const documentResult = new DOMParser().parseFromString(await response.text(), 'text/html');
                    const nextResults = documentResult.querySelector('[data-schedule-results]');
                    if (!nextResults) throw new Error('Hasil pencarian jadwal tidak lengkap');

                    results.innerHTML = nextResults.innerHTML;
                    window.history.replaceState(null, '', url);
                    syncSelection();
                } catch (error) {
                    if (error.name !== 'AbortError') console.error(error);
                } finally {
                    if (searchRequest === currentRequest) {
                        results.classList.remove('is-loading');
                        results.setAttribute('aria-busy', 'false');
                    }
                }
            }

            searchInput?.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(loadScheduleResults, 350);
            });

            syncSelection();
            syncLocationOptions();

            @if($errors->any())
            const modal = document.getElementById('bulk-schedule-modal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
            @endif
        });
    </script>

</x-app>
