<x-app title="Persetujuan Absensi">

    {{-- CSS Khusus Halaman Approval --}}
    <style>
        /* Card & Layout */
        .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 20px; }
        .container-xl { padding-top: 0; max-width: none; margin: 0 auto; }
        .card[data-approval-attendance-results] { padding: 0; }
        .approval-page-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
        .approval-queue-summary { display: inline-flex; align-items: center; gap: 8px; color: #64748b; font-size: 0.84rem; font-weight: 600; }
        .approval-queue-count { display: inline-flex; min-width: 24px; height: 24px; align-items: center; justify-content: center; padding: 0 7px; border-radius: 999px; background: #eaf2fb; color: #145da0; font-size: 0.75rem; font-weight: 700; }
        .approval-tabs { display: flex; gap: 8px; margin-bottom: 16px; }
        .approval-tab { padding: 9px 15px; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff; color: #64748b; font-size: .85rem; font-weight: 600; text-decoration: none; }
        .approval-tab:hover, .approval-tab:focus-visible { border-color: #145da0; color: #145da0; }
        .approval-tab[aria-current="page"] { border-color: #145da0; background: #145da0; color: #fff; }
        
        /* Table Styles */
        .table-responsive { width: 100%; overflow: visible; }
        .table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .table th { background: #f8fafc; padding: 11px 8px; text-align: left; font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0; overflow-wrap: anywhere; }
        .table td { padding: 10px 8px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #1e293b; font-size: 0.85rem; overflow-wrap: anywhere; }
        .approval-attendance-table th:nth-child(n+2):nth-child(-n+6) { font-size: 0.65rem; }
        .approval-attendance-table td:nth-child(n+4):nth-child(-n+6) { font-size: 0.75rem; }
        .table tr:last-child td { border-bottom: none; }
        .table tr:hover td { background: #fcfcfc; }
        .approval-media-column { text-align: center !important; }
        .approval-attendance-table th.approval-media-column, .approval-attendance-table td.approval-media-column { padding-left: 6px; padding-right: 6px; }
        .approval-hrd-note-cell { min-width: 0; }
        .approval-hrd-note-input { box-sizing: border-box; width: 100%; min-width: 0; min-height: 34px; padding: 7px 8px; border: 1px solid #cbd5e1; border-radius: 7px; color: #1e293b; font: inherit; font-size: 0.72rem; }
        .approval-hrd-note-input:focus { border-color: #145da0; outline: 2px solid rgba(20, 93, 160, .15); outline-offset: 1px; }

        /* User Info Column */
        .user-name { font-weight: 700; color: #1e293b; font-size: 0.68rem; line-height: 1.3; overflow-wrap: anywhere; }
        .user-schedule { margin-top: 2px; color: #64748b; font-size: 0.64rem; font-weight: 600; }

        /* Buttons & Badges */
        .btn-pill { width: 68px; max-width: 100%; padding: 6px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer; border: 1px solid transparent; transition: all 0.2s; }
        .approval-type-chip { display: inline-flex; align-items: center; justify-content: center; padding: 4px 8px; border-radius: 999px; font-size: 0.65rem; font-weight: 700; white-space: nowrap; }
        .approval-type-chip--in { color: #176b42; background: #e9f8ef; }
        .approval-type-chip--out { color: #a33f48; background: #fcebed; }
        .btn-pill:focus-visible, .btn-action:focus-visible, .approval-tab:focus-visible { outline: 2px solid #145da0; outline-offset: 2px; }
        .btn-blue { background: #eef2ff; color: #1e4a8d; border-color: #e0e7ff; }
        .btn-blue:hover { background: #1e4a8d; color: #fff; }
        .btn-sky { background: #f0f9ff; color: #0369a1; border-color: #e0f2fe; }
        .btn-sky:hover { background: #0369a1; color: #fff; }

        .btn-action { border: none; padding: 8px 14px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; display: inline-flex; gap: 6px; align-items: center; transition: 0.2s; }
        .btn-approve { background: #dcfce7; color: #166534; }
        .btn-approve:hover { background: #bbf7d0; transform: translateY(-1px); }
        .btn-reject { background: #fee2e2; color: #991b1b; }
        .btn-reject:hover { background: #fecaca; transform: translateY(-1px); }
        .approval-evidence-trigger { width: 34px; height: 34px; padding: 0; border: 1px solid #dbeafe; border-radius: 8px; background: #eff6ff; color: #1d4ed8; cursor: pointer; overflow: hidden; }
        .approval-evidence-trigger:hover, .approval-evidence-trigger:focus-visible { border-color: #1d4ed8; background: #dbeafe; outline: none; }
        .approval-evidence-trigger img { display: block; width: 100%; height: 100%; object-fit: cover; }
        .approval-evidence-trigger svg { width: 18px; height: 18px; }
        .approval-decision-actions { display: flex; flex-wrap: wrap; gap: 4px; justify-content: center; }
        .approval-decision-actions .btn-action { position: relative; width: 30px; height: 30px; justify-content: center; padding: 0; font-size: 0.78rem; }
        .approval-decision-actions .btn-action[data-tooltip]::after { position: absolute; top: 50%; right: calc(100% + 6px); z-index: 30; padding: 5px 7px; border-radius: 6px; background: #1e293b; color: #fff; content: attr(data-tooltip); font-size: 0.68rem; font-weight: 600; line-height: 1; opacity: 0; pointer-events: none; transform: translateY(-50%); transition: opacity .15s ease; white-space: nowrap; }
        .approval-decision-actions .btn-action[data-tooltip]:hover::after, .approval-decision-actions .btn-action[data-tooltip]:focus-visible::after { opacity: 1; }
        .approval-decision-heading { text-align: center !important; white-space: nowrap; }
        .approval-date { color: #475569; font-size: 0.75rem; font-weight: 600; white-space: nowrap; }
        .clock-in-time--late, .attendance-note--late { color: #dc2626; font-weight: 700; }
        .clock-side-cell { display: inline-flex; align-items: center; gap: 4px; }
        .clock-side-move { position: relative; }
        .clock-side-move summary { display: inline-flex; width: 24px; height: 24px; align-items: center; justify-content: center; border-radius: 6px; color: #64748b; cursor: pointer; font-size: 18px; font-weight: 700; line-height: 1; list-style: none; }
        .clock-side-move summary::-webkit-details-marker { display: none; }
        .clock-side-move summary:hover, .clock-side-move summary:focus-visible { background: #f1f5f9; color: #145da0; outline: none; }
        .clock-side-move__menu { position: absolute; z-index: 20; top: calc(100% + 4px); left: 0; width: max-content; padding: 4px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; box-shadow: 0 8px 20px rgba(15, 23, 42, .12); }
        .clock-side-move__button { border: 0; border-radius: 6px; background: transparent; color: #334155; padding: 8px 10px; font: inherit; font-size: .75rem; font-weight: 600; white-space: nowrap; cursor: pointer; }
        .clock-side-move__button:hover, .clock-side-move__button:focus-visible { background: #eff6ff; color: #145da0; outline: none; }
        /* Filter mengikuti pola master cuti agar pencarian dan status mudah dibedakan. */
        .aa-filter-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.04); margin-bottom: 16px; overflow: hidden; }
        .aa-search-row { display: flex; flex-direction: column; gap: 10px; padding: 14px 16px; }
        .aa-search-input-wrap { position: relative; flex: 1; }
        .aa-search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; }
        .aa-search-input, .aa-filter-input { width: 100%; color: #1e293b; background: #fff; font: inherit; outline: none; transition: all .2s ease; }
        .aa-search-input { padding: 10px 14px 10px 42px; border: 1.5px solid #cbd5e1; border-radius: 12px; font-size: 14px; }
        .aa-search-input:focus, .aa-filter-input:focus { border-color: #145da0; box-shadow: 0 0 0 4px rgba(20,93,160,.1); }
        .aa-search-input::placeholder { color: #94a3b8; }
        .aa-btn-reset, .aa-btn-toggle-filter { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 12px; background: #fff; color: #64748b; font: inherit; font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap; }
        .aa-filter-controls { display: contents; }
        .aa-btn-toggle-filter { padding: 10px 16px; cursor: pointer; }
        .aa-btn-reset:hover { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
        .aa-btn-toggle-filter:hover { border-color: #145da0; color: #145da0; }
        .aa-btn-toggle-filter.active { background: #145da0; border-color: #145da0; color: #fff; }
        .aa-btn-toggle-filter.active svg { stroke: #fff; }
        .aa-filter-badge { min-width: 18px; padding: 2px 6px; border-radius: 10px; background: #dc2626; color: #fff; font-size: 10px; font-weight: 700; text-align: center; }
        .aa-btn-toggle-filter.active .aa-filter-badge { background: rgba(255,255,255,.3); }
        .aa-filter-panel { padding: 14px 16px; border-top: 1px solid #e2e8f0; background: #f8fafc; animation: aaSlideDown .2s ease; }
        @keyframes aaSlideDown { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
        .aa-filter-grid { display: flex; flex-direction: column; gap: 14px; }
        .aa-filter-group { display: flex; flex-direction: column; gap: 6px; }
        .aa-filter-label { color: #64748b; font-size: 11px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; }
        .aa-filter-input { padding: 10px 14px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 13px; }
        .aa-filter-actions { display: flex; justify-content: flex-end; }
        .aa-btn-apply { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 20px; border: 0; border-radius: 10px; background: #145da0; color: #fff; cursor: pointer; font: inherit; font-size: 13px; font-weight: 600; transition: all .2s ease; }
        .aa-btn-apply:hover { background: #0f4c81; }
        .bulk-approve-checkbox { width: 18px; height: 18px; cursor: pointer; accent-color: #166534; }
        .bulk-approve-bar { position: fixed; left: 50%; bottom: calc(20px + env(safe-area-inset-bottom)); z-index: 1500; display: flex; align-items: center; gap: 14px; padding: 12px 16px; transform: translateX(-50%); border: 1px solid #bbf7d0; border-radius: 12px; background: #fff; box-shadow: 0 12px 28px rgba(15, 23, 42, 0.18); }
        .bulk-approve-count { color: #166534; font-size: 0.85rem; font-weight: 700; white-space: nowrap; }
        .bulk-approve-list { max-height: 136px; overflow-y: auto; margin: 12px 0 0; padding-left: 20px; color: #374151; }
        .bulk-approve-list li + li { margin-top: 4px; }
        @media (max-width: 480px) {
            .bulk-approve-bar { right: 16px; left: 16px; justify-content: space-between; transform: none; }
        }
        @media (min-width: 480px) {
            .aa-search-row { flex-direction: row; align-items: center; flex-wrap: wrap; }
            .aa-search-input-wrap { min-width: 260px; }
            .aa-btn-reset, .aa-btn-toggle-filter { flex-shrink: 0; width: auto; }
            .aa-filter-panel { padding: 16px 20px; }
            .aa-filter-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .aa-filter-actions { align-items: end; }
        }
        @media (min-width: 768px) { .aa-filter-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

        @media (max-width: 767px) {
            .approval-page-toolbar { align-items: flex-start; flex-direction: column; }
            .table-responsive { padding: 12px; }
            .approval-attendance-table, .approval-attendance-table tbody { display: block; }
            .approval-attendance-table colgroup, .approval-attendance-table thead { display: none; }
            .approval-attendance-table tbody { display: grid; gap: 12px; }
            .approval-attendance-table tr.approval-attendance-row { display: block; overflow: hidden; border: 1px solid #e2e8f0; border-radius: 12px; }
            .approval-attendance-table td { display: grid; grid-template-columns: minmax(88px, .7fr) minmax(0, 1fr); gap: 10px; padding: 10px 12px; border-bottom: 1px solid #f1f5f9; }
            .approval-attendance-table td::before { content: attr(data-label); color: #64748b; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; }
            .approval-attendance-table td.approval-selection-cell { display: flex; align-items: center; gap: 10px; }
            .approval-attendance-table td.approval-media-column { text-align: left !important; padding: 10px 12px; }
            .approval-attendance-table td.approval-media-column .approval-evidence-trigger { justify-self: start; }
            .approval-type-chip { justify-self: start; }
            .approval-attendance-table td.approval-user-cell, .approval-attendance-table td.approval-decision-cell { display: block; }
            .approval-attendance-table td.approval-user-cell::before, .approval-attendance-table td.approval-decision-cell::before { display: block; margin-bottom: 6px; }
            .approval-decision-actions { justify-content: stretch; }
            .approval-decision-actions .btn-action { flex: 1; justify-content: center; }
            .approval-decision-actions .btn-action[data-tooltip]::after { display: none; }
        }

        /* Modal Styles (Custom for Reject Form which has textarea) */
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
        .modal-content-custom { background: white; width: 100%; max-width: 420px; padding: 24px; border-radius: 16px; animation: pop 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
        @keyframes pop { from{transform:scale(0.9);opacity:0;} to{transform:scale(1);opacity:1;} }
        
        .form-control { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; margin-bottom: 16px; font-size: 0.9rem; outline: none; transition: border-color 0.2s; }
        .form-control:focus { border-color: #2563eb; }
        #approval-attendance-evidence-modal .modal-card { max-width: 1080px !important; }
        .approval-evidence-meta { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; color: #111827; }
        .approval-evidence-meta span { padding: 3px 8px; color: #176b42; font-size: 11px; font-weight: 700; background: #e9f8ef; border-radius: 999px; }
        .approval-evidence-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .approval-evidence-panel { min-width: 0; padding: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }
        .approval-evidence-panel h4 { margin: 0 0 10px; color: #334155; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
        #approval-attendance-evidence-photo { display: block; width: 100%; height: min(58vh, 520px); object-fit: contain; border-radius: 8px; background: #e2e8f0; }
        #approval-attendance-evidence-map { display: block; width: 100%; height: min(58vh, 520px); border: 0; border-radius: 8px; background: #e2e8f0; }
        .approval-evidence-coordinates { margin: -2px 0 10px; color: #64748b; font-family: monospace; font-size: 12px; }
        .approval-evidence-empty { margin: 0; color: #94a3b8; font-size: 13px; }
        @media (max-width: 700px) { .approval-evidence-grid { grid-template-columns: 1fr; } }

    </style>

    <div class="container-xl">
        <nav class="approval-tabs" aria-label="Kategori persetujuan absensi" data-approval-attendance-tabs>
            <a class="approval-tab" href="{{ route('hr.approval_attendance.index', array_merge($filters, ['tab' => 'flexible'])) }}" @if($tab === 'flexible') aria-current="page" @endif>Fleksibel</a>
            <a class="approval-tab" href="{{ route('hr.approval_attendance.index', array_merge($filters, ['tab' => 'remote'])) }}" @if($tab === 'remote') aria-current="page" @endif>Dinas Luar</a>
        </nav>

        <div class="approval-page-toolbar">
            <div class="approval-queue-summary" data-approval-attendance-queue-summary aria-live="polite">
                <span class="approval-queue-count">{{ $pendingAttendances->count() }}</span>
                <span>Absensi menunggu pemeriksaan</span>
            </div>
            <div data-approval-attendance-bulk-all>
                @if($pendingAttendances->isNotEmpty())
                    <button type="button" id="bulkApproveAllButton" class="btn-action btn-approve" aria-label="Terima semua {{ $pendingAttendances->count() }} absensi" title="Terima semua absensi yang tampil">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Terima Semua ({{ $pendingAttendances->count() }})
                    </button>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div style="background:#ecfdf5; color:#065f46; padding:14px 16px; border-radius:10px; margin-bottom:20px; font-weight:600; border:1px solid #a7f3d0; display:flex; align-items:center; gap:10px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        @php
            $activeAttendanceFilterCount = collect([
                $filters['name'] ?? null,
                $filters['event_type'] ?? null,
            ])->filter()->count();
        @endphp
        <div class="aa-filter-card">
            <form method="GET" action="{{ route('hr.approval_attendance.index') }}" id="approvalAttendanceFilterForm">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="aa-search-row">
                    <div class="aa-search-input-wrap">
                        <svg class="aa-search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input id="attendance-filter-name" type="search" name="name" value="{{ $filters['name'] ?? '' }}" placeholder="Cari nama karyawan..." autocomplete="off" class="aa-search-input" aria-label="Cari nama karyawan" data-approval-attendance-live-search>
                    </div>
                    <div class="aa-filter-controls" data-approval-attendance-filter-controls>
                        @if($activeAttendanceFilterCount)
                            <a href="{{ route('hr.approval_attendance.index', ['tab' => $tab]) }}" class="aa-btn-reset"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>Reset</a>
                        @endif
                        <button type="button" class="aa-btn-toggle-filter {{ $activeAttendanceFilterCount ? 'active' : '' }}" data-approval-attendance-filter-toggle aria-controls="approval-attendance-filter-panel" aria-expanded="{{ $activeAttendanceFilterCount ? 'true' : 'false' }}" onclick="toggleApprovalAttendanceFilterPanel()"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 00-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>Filter @if($activeAttendanceFilterCount)<span class="aa-filter-badge">{{ $activeAttendanceFilterCount }}</span>@endif</button>
                    </div>
                </div>
                <div class="aa-filter-panel" id="approval-attendance-filter-panel" style="{{ $activeAttendanceFilterCount ? '' : 'display: none;' }}">
                    <div class="aa-filter-grid">
                        <div class="aa-filter-group"><label class="aa-filter-label" for="attendance-filter-event-type">Tipe Absensi</label><select id="attendance-filter-event-type" name="event_type" class="aa-filter-input"><option value="">Semua</option><option value="CLOCK_IN" @selected(($filters['event_type'] ?? null) === 'CLOCK_IN')>Clock In</option><option value="CLOCK_OUT" @selected(($filters['event_type'] ?? null) === 'CLOCK_OUT')>Clock Out</option></select></div>
                        <div class="aa-filter-actions"><button type="submit" class="aa-btn-apply">Terapkan Filter</button></div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card" data-approval-attendance-results>

            @if($pendingAttendances->count() > 0)
                <div class="table-responsive">
                    <table class="table approval-attendance-table">
                        <colgroup>
                            <col style="width:4%;">
                            <col style="width:18%;">
                            <col style="width:9%;">
                            <col style="width:7%;">
                            <col style="width:9%;">
                            <col style="width:16%;">
                            <col style="width:11%;">
                            <col style="width:17%;">
                            <col style="width:9%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th style="width:48px; text-align:center;"><input type="checkbox" id="bulkApproveAll" class="bulk-approve-checkbox" aria-label="Pilih semua event absensi"></th>
                                <th>Karyawan</th>
                                <th>Tipe</th>
                                <th>Jam</th>
                                <th>Tanggal</th>
                                <th>Alasan</th>
                                <th class="approval-media-column">Bukti Kehadiran</th>
                                <th>Catatan HR</th>
                                <th class="approval-decision-heading">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingAttendances as $event)
                            <tr class="approval-attendance-row" data-attendance-id="{{ $event->id }}">
                                <td class="approval-selection-cell" data-label="Pilih"><input type="checkbox" class="bulk-approve-checkbox bulk-approve-checkbox-item" name="attendance_ids[]" value="{{ $event->id }}" data-bulk-approve-name="{{ $event->user->name }}"></td>
                                <td class="approval-user-cell" data-label="Karyawan">
                                    <div class="user-name">{{ $event->user->name }}</div>
                                    <div class="user-schedule">{{ $event->type === 'DINAS_LUAR' ? 'Dinas Luar' : 'Fleksibel' }}</div>
                                </td>
                                <td data-label="Tipe"><span class="approval-type-chip approval-type-chip--{{ $event->event_type === App\Models\AttendanceEvent::CLOCK_IN ? 'in' : 'out' }}">{{ $event->event_type === App\Models\AttendanceEvent::CLOCK_IN ? 'Clock In' : 'Clock Out' }}</span></td>
                                <td data-label="Jam">{{ $event->local_occurred_at->format('H:i') }}</td>
                                <td data-label="Tanggal">{{ $event->local_occurred_at->format('d/m/y') }}</td>
                                <td data-label="Alasan">{{ trim((string) $event->notes) !== '' ? $event->notes : 'Alasan tidak tersedia' }}</td>
                                <td class="approval-media-column" data-label="Bukti Kehadiran">
                                    @if($event->photo || ($event->latitude !== null && $event->longitude !== null))
                                    <button type="button" class="approval-evidence-trigger" data-approval-attendance-evidence
                                        data-approval-attendance-evidence-name="{{ $event->user->name }}"
                                        data-approval-attendance-evidence-type="{{ $event->event_type === App\Models\AttendanceEvent::CLOCK_IN ? 'Clock In' : 'Clock Out' }}"
                                        data-approval-attendance-evidence-photo="{{ $event->photo ? asset('storage/'.$event->photo) : '' }}"
                                        data-approval-attendance-evidence-latitude="{{ $event->latitude ?? '' }}"
                                        data-approval-attendance-evidence-longitude="{{ $event->longitude ?? '' }}"
                                        aria-label="Lihat bukti kehadiran {{ $event->user->name }}">
                                        @if($event->photo)
                                        <img src="{{ asset('storage/'.$event->photo) }}" alt="">
                                        @else
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-5.25 7-11a7 7 0 10-14 0c0 5.75 7 11 7 11z"/><circle cx="12" cy="10" r="2"/></svg>
                                        @endif
                                    </button>
                                    @else - @endif
                                </td>
                                <td class="approval-hrd-note-cell" data-label="Catatan HR">
                                    <input
                                        type="text"
                                        class="approval-hrd-note-input"
                                        name="hrd_note"
                                        form="approve-attendance-{{ $event->id }}"
                                        value="{{ old('hrd_note', $event->hrd_note) }}"
                                        maxlength="255"
                                        placeholder="Opsional"
                                        data-hrd-note-event-id="{{ $event->id }}"
                                        aria-label="Catatan HR untuk {{ $event->user->name }}">
                                </td>
                                <td class="approval-decision-cell" data-label="Aksi">
                                    <div class="approval-decision-actions">
                                        <form id="approve-attendance-{{ $event->id }}" method="POST" action="{{ route('hr.approval_attendance.approve', $event) }}">@csrf<button type="submit" class="btn-action btn-approve" aria-label="Terima absensi {{ $event->user->name }}">✓</button></form>
                                        <form method="POST" action="{{ route('hr.approval_attendance.move_clock_side', $event) }}">@csrf<button type="submit" class="btn-action" aria-label="Ubah tipe absensi {{ $event->user->name }}">↔</button></form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div id="bulkApproveBar" class="bulk-approve-bar" style="display:none;">
                    <span id="bulkApproveCount" class="bulk-approve-count">0 absensi dipilih</span>
                    <button type="button" id="bulkApproveSelectedButton" class="btn-action btn-approve">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Terima Terpilih
                    </button>
                </div>
            @else
                <div style="padding:60px; text-align:center; color:#94a3b8;">
                    <div style="background:#f1f5f9; width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                        <svg width="40" height="40" fill="none" stroke="#cbd5e1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 style="margin:0; font-size:1.1rem; color:#475569;">Tidak ada absensi</h3>
                    <p style="margin:8px 0 0; font-size:0.95rem;">Tidak ada absensi {{ $tab === 'remote' ? 'Dinas Luar' : 'Fleksibel' }} yang menunggu sesuai filter saat ini.</p>
                </div>
            @endif
        </div>
    </div>

    <x-modal id="approval-attendance-evidence-modal" title="Bukti Kehadiran" type="info" variant="info" cancelLabel="Tutup">
        <div class="approval-evidence-meta">
            <strong id="approval-attendance-evidence-name"></strong>
            <span id="approval-attendance-evidence-type"></span>
        </div>
        <div class="approval-evidence-grid">
            <section class="approval-evidence-panel">
                <h4>Foto Kehadiran</h4>
                <img id="approval-attendance-evidence-photo" alt="Foto kehadiran" hidden>
                <p id="approval-attendance-evidence-photo-empty" class="approval-evidence-empty">Foto tidak tersedia.</p>
            </section>
            <section class="approval-evidence-panel">
                <h4>Lokasi Kehadiran</h4>
                <p id="approval-attendance-evidence-coordinates" class="approval-evidence-coordinates" hidden></p>
                <iframe id="approval-attendance-evidence-map" title="Peta lokasi kehadiran" loading="lazy" referrerpolicy="no-referrer-when-downgrade" hidden></iframe>
                <p id="approval-attendance-evidence-location-empty" class="approval-evidence-empty">Lokasi tidak tersedia.</p>
            </section>
        </div>
    </x-modal>

    {{-- MODAL APPROVE --}}
    <x-modal
        id="approveModal"
        title="Terima Pengajuan Ini?"
        type="confirm"
        variant="success"
        confirmLabel="Ya, Terima"
        cancelLabel="Batal"
        confirmFormAction="#"
        confirmFormMethod="POST">
        <div style="padding: 10px 0;">
            <p style="margin:0; color:#374151; font-size:1rem;">
                Apakah Anda yakin ingin menerima absensi dari <strong id="approveUserName" style="color:#1e293b;"></strong>?
            </p>
            <p style="margin-top:8px; font-size:0.85rem; color:#6b7280;">
                Persetujuan absensi akan dicatat.
            </p>
        </div>
    </x-modal>

    <x-modal
        id="bulkApproveModal"
        title="Terima Absensi Terpilih?"
        type="confirm"
        variant="success"
        confirmLabel="Ya, Terima"
        cancelLabel="Batal"
        confirmFormAction="{{ route('hr.approval_attendance.bulk_approve') }}"
        confirmFormMethod="POST">
        <p style="margin:0; color:#374151;">Absensi berikut akan disetujui:</p>
        <ul id="bulkApproveList" class="bulk-approve-list"></ul>
    </x-modal>

    {{-- MODAL REJECT (Custom HTML untuk Input Textarea) --}}
    <div id="rejectModal" class="modal-backdrop">
        <div class="modal-content-custom">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 style="margin:0; font-size:1.2rem; color:#1e293b; font-weight:700;">Tolak Pengajuan Ini?</h3>
                <button onclick="closeRejectModal()" style="background:none; border:none; color:#94a3b8; cursor:pointer;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <p style="margin-top:-10px; margin-bottom:20px; font-size:0.9rem; color:#64748b;">
                Anda akan menolak absensi dari <span id="rejectUserName" style="font-weight:700; color:#1e293b;"></span>.
            </p>

            <form id="rejectForm" method="POST">
                @csrf
                <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.85rem; color:#334155;">Alasan Penolakan <span style="color:#dc2626;">*</span></label>
                <textarea name="rejection_note" class="form-control" rows="3" placeholder="Contoh: Foto tidak jelas, Lokasi tidak sesuai..." required></textarea>
                
                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                    <button type="button" onclick="closeRejectModal()" style="border:none; background:#f1f5f9; color:#475569; padding:10px 18px; border-radius:8px; font-weight:600; cursor:pointer;">Batal</button>
                    <button type="submit" style="border:none; background:#dc2626; color:white; padding:10px 18px; border-radius:8px; font-weight:600; cursor:pointer;">Konfirmasi Tolak</button>
                </div>
            </form>
        </div>
    </div>

    <div id="moveClockSideModal" class="modal-backdrop" aria-hidden="true">
        <div class="modal-content-custom" role="dialog" aria-modal="true" aria-labelledby="moveClockSideTitle">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 id="moveClockSideTitle" style="margin:0; font-size:1.2rem; color:#1e293b; font-weight:700;">Pindahkan Bukti Absensi?</h3>
                <button type="button" onclick="closeMoveClockSideModal()" style="background:none; border:none; color:#94a3b8; cursor:pointer;" aria-label="Tutup">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <p style="margin-top:-10px; margin-bottom:20px; font-size:0.9rem; color:#64748b;">
                Bukti absensi <span id="moveClockSideUserName" style="font-weight:700; color:#1e293b;"></span> akan dipindahkan ke <span id="moveClockSideDestination" style="font-weight:700; color:#1e293b;"></span>.
            </p>
            <form id="moveClockSideForm" method="POST">
                @csrf
                <input id="moveClockSideInput" type="hidden" name="destination">
                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                    <button type="button" onclick="closeMoveClockSideModal()" style="border:none; background:#f1f5f9; color:#475569; padding:10px 18px; border-radius:8px; font-weight:600; cursor:pointer;">Batal</button>
                    <button type="submit" style="border:none; background:#145da0; color:white; padding:10px 18px; border-radius:8px; font-weight:600; cursor:pointer;">Ya, Pindahkan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- JAVASCRIPT LOGIC --}}
    <script>
        // --- Logic Approve Modal (X-Modal) ---
        function openApproveModal(url, name) {
            // Set nama user di text modal
            document.getElementById('approveUserName').innerText = name;
            
            // Cari form di dalam component x-modal dan ubah action-nya
            const modalEl = document.getElementById('approveModal');
            const form = modalEl.querySelector('form');
            if(form) {
                form.action = url;
            } else {
                console.error("Form tidak ditemukan dalam modal approve!");
            }

            // Tampilkan modal
            modalEl.style.display = 'flex';
        }

        // --- Logic Reject Modal (Custom) ---
        function openRejectModal(id, name) {
            document.getElementById('rejectModal').style.display = 'flex';
            document.getElementById('rejectUserName').innerText = name;
            document.getElementById('rejectForm').action = "/hr/approval-attendance/" + id + "/reject";
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }

        function openMoveClockSideModal(url, name, destination, destinationLabel) {
            document.getElementById('moveClockSideForm').action = url;
            document.getElementById('moveClockSideInput').value = destination;
            document.getElementById('moveClockSideUserName').innerText = name;
            document.getElementById('moveClockSideDestination').innerText = destinationLabel;
            document.getElementById('moveClockSideModal').style.display = 'flex';
        }

        function closeMoveClockSideModal() {
            document.getElementById('moveClockSideModal').style.display = 'none';
        }

        function toggleApprovalAttendanceFilterPanel() {
            const panel = document.getElementById('approval-attendance-filter-panel');
            const button = document.querySelector('[data-approval-attendance-filter-toggle]');

            if (!panel || !button) {
                return;
            }

            const isOpening = panel.style.display === 'none';
            panel.style.display = isOpening ? '' : 'none';
            button.classList.toggle('active', isOpening);
            button.setAttribute('aria-expanded', isOpening ? 'true' : 'false');
        }

        function initializeApprovalAttendanceInteractions() {
            const evidenceModal = document.getElementById('approval-attendance-evidence-modal');
            const evidence = {
                name: document.getElementById('approval-attendance-evidence-name'),
                type: document.getElementById('approval-attendance-evidence-type'),
                photo: document.getElementById('approval-attendance-evidence-photo'),
                photoEmpty: document.getElementById('approval-attendance-evidence-photo-empty'),
                coordinates: document.getElementById('approval-attendance-evidence-coordinates'),
                map: document.getElementById('approval-attendance-evidence-map'),
                locationEmpty: document.getElementById('approval-attendance-evidence-location-empty'),
            };
            const bulkApproveAll = document.getElementById('bulkApproveAll');
            const bulkApproveAllButton = document.getElementById('bulkApproveAllButton');
            const bulkApproveBar = document.getElementById('bulkApproveBar');
            const bulkApproveCount = document.getElementById('bulkApproveCount');
            const bulkApproveSelectedButton = document.getElementById('bulkApproveSelectedButton');
            const bulkApproveModal = document.getElementById('bulkApproveModal');
            const bulkApproveList = document.getElementById('bulkApproveList');
            const bulkApproveForm = bulkApproveModal?.querySelector('form');
            const bulkApproveCheckboxes = Array.from(document.querySelectorAll('.bulk-approve-checkbox-item'));

            function selectedBulkAttendances() {
                return bulkApproveCheckboxes.filter(function (checkbox) {
                    return checkbox.checked;
                });
            }

            function updateBulkApproveControls() {
                const selected = selectedBulkAttendances();

                if (bulkApproveBar) {
                    bulkApproveBar.style.display = selected.length ? 'flex' : 'none';
                }

                if (bulkApproveCount) {
                    bulkApproveCount.textContent = `${selected.length} absensi dipilih`;
                }

                if (bulkApproveAll) {
                    bulkApproveAll.checked = selected.length === bulkApproveCheckboxes.length && selected.length > 0;
                    bulkApproveAll.indeterminate = selected.length > 0 && selected.length < bulkApproveCheckboxes.length;
                }
            }

            function openBulkApproveModal(selected) {
                if (!bulkApproveModal || !bulkApproveList || !bulkApproveForm || selected.length === 0) {
                    return;
                }

                bulkApproveList.replaceChildren();
                bulkApproveForm.querySelectorAll('input[name="attendance_ids[]"], input[data-bulk-approval-note]').forEach(function (input) {
                    input.remove();
                });

                selected.forEach(function (checkbox) {
                    const item = document.createElement('li');
                    item.textContent = checkbox.dataset.bulkApproveName;
                    bulkApproveList.appendChild(item);

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'attendance_ids[]';
                    input.value = checkbox.value;
                    bulkApproveForm.appendChild(input);

                    const noteInput = document.querySelector(`[data-hrd-note-event-id="${checkbox.value}"]`);
                    const note = document.createElement('input');
                    note.type = 'hidden';
                    note.name = `hrd_notes[${checkbox.value}]`;
                    note.value = noteInput?.value ?? '';
                    note.dataset.bulkApprovalNote = 'true';
                    bulkApproveForm.appendChild(note);
                });

                bulkApproveModal.style.display = 'flex';
            }

            bulkApproveCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', updateBulkApproveControls);
            });

            bulkApproveAll?.addEventListener('change', function () {
                bulkApproveCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = bulkApproveAll.checked;
                });
                updateBulkApproveControls();
            });

            bulkApproveSelectedButton?.addEventListener('click', function () {
                openBulkApproveModal(selectedBulkAttendances());
            });

            bulkApproveAllButton?.addEventListener('click', function () {
                bulkApproveCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = true;
                });
                updateBulkApproveControls();
                openBulkApproveModal(selectedBulkAttendances());
            });

            document.querySelectorAll('[data-approval-attendance-evidence]').forEach(function (button) {
                if (button.dataset.approvalAttendanceEvidenceInitialized) return;

                button.dataset.approvalAttendanceEvidenceInitialized = 'true';
                button.addEventListener('click', function () {
                    evidence.name.textContent = this.dataset.approvalAttendanceEvidenceName;
                    evidence.type.textContent = this.dataset.approvalAttendanceEvidenceType;

                    if (this.dataset.approvalAttendanceEvidencePhoto) {
                        evidence.photo.src = this.dataset.approvalAttendanceEvidencePhoto;
                        evidence.photo.hidden = false;
                        evidence.photoEmpty.hidden = true;
                    } else {
                        evidence.photo.removeAttribute('src');
                        evidence.photo.hidden = true;
                        evidence.photoEmpty.hidden = false;
                    }

                    const latitude = this.dataset.approvalAttendanceEvidenceLatitude;
                    const longitude = this.dataset.approvalAttendanceEvidenceLongitude;
                    if (latitude && longitude) {
                        const coordinates = `${latitude}, ${longitude}`;
                        evidence.coordinates.textContent = coordinates;
                        evidence.coordinates.hidden = false;
                        evidence.map.src = `https://www.google.com/maps?q=${encodeURIComponent(coordinates)}&z=16&output=embed`;
                        evidence.map.hidden = false;
                        evidence.locationEmpty.hidden = true;
                    } else {
                        evidence.coordinates.hidden = true;
                        evidence.map.removeAttribute('src');
                        evidence.map.hidden = true;
                        evidence.locationEmpty.hidden = false;
                    }

                    evidenceModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('approvalAttendanceFilterForm');
            const input = form?.querySelector('[data-approval-attendance-live-search]');
            const results = document.querySelector('[data-approval-attendance-results]');
            let timer;
            let request;

            initializeApprovalAttendanceInteractions();

            if (!form || !input || !results) return;

            async function loadApprovalAttendanceResults() {
                const url = new URL(form.action, window.location.origin);
                const params = new URLSearchParams(new FormData(form));
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
                    if (!response.ok) throw new Error('Pencarian persetujuan absensi gagal');

                    const documentResult = new DOMParser().parseFromString(await response.text(), 'text/html');
                    const selectors = [
                        '[data-approval-attendance-tabs]',
                        '[data-approval-attendance-queue-summary]',
                        '[data-approval-attendance-bulk-all]',
                        '[data-approval-attendance-filter-controls]',
                        '[data-approval-attendance-results]',
                    ];

                    selectors.forEach((selector) => {
                        const current = document.querySelector(selector);
                        const next = documentResult.querySelector(selector);
                        if (!current || !next) throw new Error('Hasil pencarian persetujuan absensi tidak lengkap');
                        current.innerHTML = next.innerHTML;
                    });

                    initializeApprovalAttendanceInteractions();
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
                timer = setTimeout(loadApprovalAttendanceResults, 350);
            });
        });

    </script>

</x-app>

