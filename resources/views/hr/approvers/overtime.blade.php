<x-app title="Approver Lembur">
    <x-slot name="header">
        <div class="oam-header">
            <div class="oam-header-icon">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="oam-header-title">Approver Lembur</h1>
                <p class="oam-header-subtitle">Atur supervisor dan manager khusus lembur untuk setiap karyawan.</p>
            </div>
        </div>
    </x-slot>

    <a href="{{ route('hr.approvers.index') }}" class="oam-back" aria-label="Kembali ke daftar approver">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Kembali</span>
    </a>

    @if(session('success')) <div class="oam-alert oam-alert-success" role="status">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="oam-alert oam-alert-error" role="alert">{{ session('error') }}</div> @endif
    @if($errors->any()) <div class="oam-alert oam-alert-error" role="alert">{{ $errors->first() }}</div> @endif
    @if(session('overtime_skipped'))
        <div class="oam-alert oam-alert-warning">
            <strong>Karyawan yang dilewati:</strong>
            <ul>@foreach(session('overtime_skipped') as $skip)<li>{{ $skip }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="oam-filter-card">
        <div class="oam-search-row">
            <div class="oam-search-wrap">
                <svg class="oam-search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input id="employee-search" type="search" placeholder="Cari nama atau divisi karyawan..." autocomplete="off" aria-label="Cari karyawan">
            </div>
            <button type="button" class="oam-btn-reset" id="reset-filter" hidden>
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Reset
            </button>
            <button type="button" class="oam-btn-filter" id="toggle-filter" aria-expanded="false" aria-controls="employee-filter-panel">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter <span class="oam-filter-badge" id="filter-count" hidden></span>
            </button>
        </div>
        <div class="oam-filter-panel" id="employee-filter-panel" hidden>
            <div class="oam-filter-grid">
                <label>PT
                    <select id="filter-pt"><option value="">Semua PT</option>@foreach($pts as $pt)<option value="{{ $pt->id }}">{{ $pt->name }}</option>@endforeach</select>
                </label>
                <label>Divisi
                    <select id="filter-division"><option value="">Semua Divisi</option>@foreach($divisions as $division)<option value="{{ $division->id }}">{{ $division->name }}</option>@endforeach</select>
                </label>
                <label>Jabatan
                    <select id="filter-position"><option value="">Semua Jabatan</option>@foreach($positions as $position)<option value="{{ $position->id }}">{{ $position->name }}</option>@endforeach</select>
                </label>
                <div class="oam-filter-actions"><button type="button" class="oam-btn-primary" id="apply-filter">Terapkan Filter</button></div>
            </div>
        </div>
    </div>

    <div class="oam-card">
        <div class="oam-bulk-actions">
            <div class="oam-bulk-heading">
                <strong>Atur approver terpilih</strong>
                <span id="selection-count">0 dipilih</span>
            </div>
            <p class="oam-selection-note">Karyawan yang dipilih tetap terpilih saat filter berubah, termasuk yang tidak sedang tampil.</p>
            <form id="bulk-approvers" method="POST" action="{{ route('hr.approvers.overtime.bulk') }}" class="oam-bulk-form">
                @csrf
                <label>Supervisor
                    <select name="supervisor_id" required><option value="">Pilih supervisor</option>@foreach($supervisors as $person)<option value="{{ $person->id }}">{{ $person->name }}</option>@endforeach</select>
                </label>
                <label>Manager
                    <select name="manager_id" required><option value="">Pilih manager</option>@foreach($managers as $person)<option value="{{ $person->id }}">{{ $person->name }}</option>@endforeach</select>
                </label>
                <button type="submit" class="oam-btn-primary">Terapkan ke karyawan terpilih</button>
            </form>
        </div>
        <div class="oam-table-wrapper">
            <table class="oam-table">
                <thead>
                    <tr>
                        <th><label class="oam-select-visible"><input id="select-visible" type="checkbox"> Pilih yang tampil</label></th>
                        <th>Karyawan</th>
                        <th>Supervisor</th>
                        <th>Manager</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($employees as $employee)
                    <tr data-employee-row data-search="{{ mb_strtolower($employee->name.' '.($employee->division->name ?? '')) }}" data-pt="{{ $employee->profile?->pt_id }}" data-division="{{ $employee->division_id }}" data-position="{{ $employee->position_id }}">
                        <td><input form="bulk-approvers" type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" aria-label="Pilih {{ $employee->name }}"></td>
                        <td><div class="oam-user-info"><strong>{{ $employee->name }}</strong><span>{{ $employee->division->name ?? '-' }}</span></div></td>
                        <td><select form="assignment-{{ $employee->id }}" name="supervisor_id" aria-label="Supervisor untuk {{ $employee->name }}"><option value="">Belum ditentukan</option>@if($employee->overtimeApproverAssignment?->supervisor && !$supervisors->contains('id', $employee->overtimeApproverAssignment->supervisor_id))<option value="{{ $employee->overtimeApproverAssignment->supervisor_id }}" selected>{{ $employee->overtimeApproverAssignment->supervisor->name }} (tidak lagi memenuhi syarat)</option>@endif @foreach($supervisors as $person)<option value="{{ $person->id }}" @selected($employee->overtimeApproverAssignment?->supervisor_id == $person->id)>{{ $person->name }}</option>@endforeach</select></td>
                        <td><select form="assignment-{{ $employee->id }}" name="manager_id" aria-label="Manager untuk {{ $employee->name }}"><option value="">Belum ditentukan</option>@if($employee->overtimeApproverAssignment?->manager && !$managers->contains('id', $employee->overtimeApproverAssignment->manager_id))<option value="{{ $employee->overtimeApproverAssignment->manager_id }}" selected>{{ $employee->overtimeApproverAssignment->manager->name }} (tidak lagi memenuhi syarat)</option>@endif @foreach($managers as $person)<option value="{{ $person->id }}" @selected($employee->overtimeApproverAssignment?->manager_id == $person->id)>{{ $person->name }}</option>@endforeach</select></td>
                        <td><form id="assignment-{{ $employee->id }}" method="POST" action="{{ route('hr.approvers.overtime.store') }}">@csrf<input type="hidden" name="employee_id" value="{{ $employee->id }}"><button type="submit" class="oam-btn-action">Simpan</button></form></td>
                    </tr>
                @endforeach
                    <tr id="employee-no-results" hidden><td colspan="5">Tidak ada karyawan yang cocok.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <style>
        .oam-header { display: flex; align-items: center; gap: 10px; margin-bottom: 0; }
        .oam-header-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: rgba(10, 61, 98, 0.08); color: var(--primary-dark, #0A3D62); }
        .oam-header-title { margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-primary, #111827); letter-spacing: -0.01em; line-height: 1.25; }
        .oam-header-subtitle { margin: 0; font-size: 0.8125rem; color: var(--text-muted, #6B7280); font-weight: 500; line-height: 1.35; }
        .oam-back { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 12px 0 10px; background: var(--white, #fff); border: 1px solid var(--border, #E5E7EB); border-radius: 10px; color: var(--text-muted, #6B7280); text-decoration: none; transition: all 0.15s ease; flex-shrink: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.04); margin-bottom: 16px; }
        .oam-back:hover { border-color: var(--primary, #145DA0); color: var(--primary, #145DA0); background: var(--gray-50, #F5F7FA); }
        .oam-back svg { transition: transform 0.2s ease; flex-shrink: 0; }
        .oam-back:hover svg { transform: translateX(-2px); }
        .oam-back span { font-size: 0.75rem; font-weight: 600; line-height: 1; }
        .oam-alert { padding: 12px 16px; border: 1px solid; border-radius: 8px; margin-bottom: 16px; font-size: 13px; }
        .oam-alert-success { background: #ecfdf5; color: #065f46; border-color: #a7f3d0; }
        .oam-alert-error { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
        .oam-alert-warning { background: #fffbeb; color: #92400e; border-color: #fde68a; }
        .oam-alert ul { margin: 6px 0 0; padding-left: 20px; }
        .oam-filter-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04); margin-bottom: 16px; padding: 14px 16px; }
        .oam-search-row { display: flex; flex-direction: column; gap: 10px; }
        .oam-search-wrap { position: relative; flex: 1; }
        .oam-search-icon { position: absolute; left: 14px; top: 50%; color: #9ca3af; pointer-events: none; transform: translateY(-50%); }
        .oam-search-wrap input { width: 100%; padding: 10px 14px 10px 42px; border: 1.5px solid #e5e7eb; border-radius: 12px; color: #1f2937; font-size: 14px; outline: none; }
        .oam-search-wrap input:focus { border-color: #1e4a8d; box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.08); }
        .oam-btn-filter, .oam-btn-reset { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 16px; background: #fff; color: #6b7280; border: 1.5px solid #e5e7eb; border-radius: 12px; font-family: inherit; font-size: 13px; font-weight: 500; cursor: pointer; white-space: nowrap; }
        .oam-btn-filter:hover, .oam-btn-reset:hover { border-color: #145da0; color: #145da0; }
        .oam-btn-filter.active { background: #145da0; border-color: #145da0; color: #fff; }
        .oam-filter-badge { background: rgba(255, 255, 255, 0.3); border-radius: 10px; padding: 2px 6px; font-size: 10px; font-weight: 700; }
        .oam-filter-panel { background: #f5f7fa; border-top: 1px solid #e5e7eb; margin: 14px -16px -14px; padding: 14px 16px; border-radius: 0 0 16px 16px; }
        .oam-filter-grid { display: flex; flex-direction: column; gap: 14px; }
        .oam-filter-grid label { display: flex; flex-direction: column; gap: 6px; color: #6b7280; font-size: 11px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; }
        .oam-filter-grid select { width: 100%; padding: 10px 14px; border: 1.5px solid #e5e7eb; border-radius: 10px; background: #fff; color: #111827; font-family: inherit; font-size: 13px; cursor: pointer; }
        .oam-filter-grid select:focus { border-color: #145da0; outline: none; box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1); }
        .oam-filter-actions { display: flex; justify-content: flex-end; }
        .oam-filter-panel[hidden], .oam-btn-reset[hidden], .oam-filter-badge[hidden], .oam-table tr[hidden] { display: none; }
        .oam-card { background: #fff; border: 1px solid #f3f4f6; border-radius: 12px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03); overflow: hidden; }
        .oam-bulk-actions { padding: 14px 20px; border-bottom: 1px solid #f3f4f6; }
        .oam-bulk-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
        .oam-bulk-heading strong { font-size: 13px; color: #1f2937; }
        .oam-bulk-heading span { color: #6b7280; font-size: 12px; }
        .oam-selection-note { margin: -4px 0 12px; color: #6b7280; font-size: 11px; }
        .oam-bulk-form { display: flex; align-items: end; gap: 12px; flex-wrap: wrap; }
        .oam-bulk-form label { display: grid; gap: 6px; flex: 1 1 180px; color: #374151; font-size: 13px; font-weight: 600; }
        .oam-bulk-form select, .oam-table select { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; color: #374151; font-size: 13px; outline: none; }
        .oam-bulk-form select:focus, .oam-table select:focus { border-color: #1e4a8d; }
        .oam-btn-primary { padding: 9px 18px; border: 0; border-radius: 8px; background: #1e4a8d; color: #fff; cursor: pointer; font-size: 13px; font-weight: 600; }
        .oam-btn-primary:hover { background: #163a75; }
        .oam-table-wrapper { width: 100%; overflow-x: auto; }
        .oam-table { width: 100%; border-collapse: collapse; min-width: 800px; }
        .oam-table th { background: #f9fafb; padding: 10px 12px; text-align: left; font-size: 10px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e5e7eb; }
        .oam-table td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; font-size: 12px; color: #1f2937; vertical-align: middle; }
        .oam-table tr:last-child td { border-bottom: 0; }
        .oam-table tr:hover td { background: #fdfdfd; }
        .oam-table input[type=checkbox] { accent-color: #1e4a8d; }
        .oam-table select { min-width: 180px; }
        .oam-select-visible { display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; cursor: pointer; }
        .oam-user-info { display: flex; flex-direction: column; gap: 2px; }
        .oam-user-info strong { color: #111827; font-size: 13px; font-weight: 600; }
        .oam-user-info span { color: #6b7280; font-size: 11px; }
        .oam-btn-action { padding: 4px 12px; border: 1px solid #d1d5db; border-radius: 6px; background: #fff; color: #374151; font-size: 11px; font-weight: 600; cursor: pointer; }
        .oam-btn-action:hover { background: #f3f4f6; border-color: #9ca3af; }
        @media (min-width: 640px) { .oam-filter-card { padding: 16px 20px; } .oam-search-row { flex-direction: row; align-items: center; } .oam-filter-panel { margin: 16px -20px -16px; padding: 16px 20px; } .oam-filter-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; } .oam-filter-actions { grid-column: 1 / -1; } }
        @media (max-width: 768px) { .oam-bulk-form { align-items: stretch; flex-direction: column; } .oam-bulk-form label { flex: auto; width: 100%; } .oam-btn-primary { width: 100%; } }
        @media (max-width: 639px) { .oam-back { height: 40px; padding: 0 14px 0 12px; } .oam-back span { font-size: 0.8125rem; } }
    </style>
    <script>
        (() => {
            const rows = [...document.querySelectorAll('[data-employee-row]')];
            const search = document.getElementById('employee-search');
            const all = document.getElementById('select-visible');
            const count = document.getElementById('selection-count');
            const panel = document.getElementById('employee-filter-panel');
            const toggle = document.getElementById('toggle-filter');
            const reset = document.getElementById('reset-filter');
            const badge = document.getElementById('filter-count');
            const noResults = document.getElementById('employee-no-results');
            const filters = {
                pt: document.getElementById('filter-pt'),
                division: document.getElementById('filter-division'),
                position: document.getElementById('filter-position'),
            };
            let applied = { pt: '', division: '', position: '' };
            const refresh = () => {
                const visible = rows.filter(row => !row.hidden);
                count.textContent = rows.filter(row => row.querySelector('input[type=checkbox]').checked).length + ' dipilih';
                all.checked = visible.length > 0 && visible.every(row => row.querySelector('input[type=checkbox]').checked);
                all.indeterminate = !all.checked && visible.some(row => row.querySelector('input[type=checkbox]').checked);
                noResults.hidden = visible.length > 0;
            };
            const filterRows = () => {
                rows.forEach(row => {
                    row.hidden = !row.dataset.search.includes(search.value.toLowerCase())
                        || Object.entries(applied).some(([key, value]) => value && row.dataset[key] !== value);
                });
                const filterCount = Object.values(applied).filter(Boolean).length;
                badge.textContent = filterCount;
                badge.hidden = !filterCount;
                toggle.classList.toggle('active', filterCount > 0);
                reset.hidden = !filterCount && !search.value;
                refresh();
            };
            toggle.addEventListener('click', () => {
                panel.hidden = !panel.hidden;
                toggle.setAttribute('aria-expanded', String(!panel.hidden));
            });
            document.getElementById('apply-filter').addEventListener('click', () => {
                applied = Object.fromEntries(Object.entries(filters).map(([key, select]) => [key, select.value]));
                filterRows();
            });
            reset.addEventListener('click', () => {
                search.value = '';
                Object.values(filters).forEach(select => select.value = '');
                applied = { pt: '', division: '', position: '' };
                filterRows();
            });
            search.addEventListener('input', filterRows);
            all.addEventListener('change', () => { rows.filter(row => !row.hidden).forEach(row => row.querySelector('input[type=checkbox]').checked = all.checked); refresh(); });
            rows.forEach(row => row.querySelector('input[type=checkbox]').addEventListener('change', refresh));
            refresh();
        })();
    </script>
</x-app>
