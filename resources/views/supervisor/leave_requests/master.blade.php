<x-app title="Rekap Izin Bawahan">

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --primary-light: #dbeafe;
            --success: #059669;
            --success-bg: #dcfce7;
            --success-text: #166534;
            --warning: #d97706;
            --warning-bg: #fefce8;
            --warning-text: #a16207;
            --danger: #dc2626;
            --danger-bg: #fee2e2;
            --danger-text: #991b1b;
            --teal-bg: #ccfbf1;
            --teal-text: #0f766e;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-900: #111827;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, sans-serif; background: var(--gray-50); color: var(--gray-900); }

        .page-wrapper { max-width: 680px; margin: 0 auto; padding: 16px; }

        /* Header */
        .page-header { margin-bottom: 16px; }
        .page-title { font-size: 20px; font-weight: 700; color: var(--gray-900); display: flex; align-items: center; gap: 10px; margin-bottom: 4px; }
        .role-chip { font-size: 11px; background: #e0e7ff; color: #3730a3; padding: 3px 10px; border-radius: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
        .page-subtitle { font-size: 13px; color: var(--gray-500); }

        /* Alert */
        .alert-success { background: var(--success-bg); color: var(--success-text); padding: 12px 16px; border-radius: 10px; border: 1px solid #a7f3d0; margin-bottom: 16px; font-size: 13.5px; font-weight: 500; }

        /* Filter Card */
        .filter-card { background: #fff; border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); border: 1px solid var(--gray-200); margin-bottom: 16px; overflow: hidden; }
        .filter-header { padding: 14px 20px; border-bottom: 1px solid var(--gray-100); background: var(--gray-50); }
        .filter-title { font-size: 11px; font-weight: 700; color: var(--gray-400); text-transform: uppercase; letter-spacing: 0.06em; display: flex; align-items: center; gap: 6px; }
        .filter-title svg { width: 14px; height: 14px; }
        .filter-body { padding: 16px 20px; }
        .filter-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .filter-group { display: flex; flex-direction: column; gap: 5px; }
        .filter-group.full { grid-column: 1 / -1; }
        .filter-label { font-size: 11.5px; font-weight: 600; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.04em; }
        .form-control { width: 100%; padding: 10px 12px; border: 1.5px solid var(--gray-200); border-radius: 10px; font-size: 13.5px; color: var(--gray-900); background: #fff; font-family: inherit; outline: none; transition: border-color 0.2s, box-shadow 0.2s; -webkit-appearance: none; appearance: none; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        select.form-control { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 36px; }
        .filter-actions { display: flex; gap: 10px; margin-top: 14px; }
        .btn-filter { flex: 1; padding: 11px; background: var(--primary); color: #fff; border: none; border-radius: 10px; font-size: 13.5px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: background 0.2s; }
        .btn-filter:hover { background: var(--primary-dark); }
        .btn-filter svg { width: 15px; height: 15px; }
        .btn-reset { padding: 11px 16px; background: #fff; color: var(--gray-600); border: 1.5px solid var(--gray-200); border-radius: 10px; font-size: 13.5px; font-weight: 500; text-decoration: none; display: flex; align-items: center; gap: 5px; transition: all 0.2s; white-space: nowrap; }
        .btn-reset:hover { background: var(--gray-50); border-color: var(--gray-300); }

        /* Cards */
        .card { background: #fff; border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); border: 1px solid var(--gray-200); overflow: hidden; }

        /* Leave Card */
        .leave-card { padding: 16px; border-bottom: 1px solid var(--gray-100); display: flex; flex-direction: column; gap: 10px; }
        .leave-card:last-child { border-bottom: none; }
        .leave-card.card-pending { border-left: 4px solid var(--primary); }
        .leave-card.card-approved { border-left: 4px solid var(--success); }
        .leave-card.card-rejected { border-left: 4px solid var(--danger); }
        .leave-card.card-atasan { border-left: 4px solid var(--teal-text); }
        .leave-card.card-cancel { border-left: 4px solid var(--gray-400); }

        .leave-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
        .employee-info { display: flex; align-items: center; gap: 10px; }
        .avatar { width: 42px; height: 42px; background: var(--primary-light); color: var(--primary-dark); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; flex-shrink: 0; }
        .employee-name { font-size: 14px; font-weight: 700; color: var(--gray-900); }
        .employee-role { font-size: 11.5px; color: var(--gray-400); margin-top: 1px; }

        .chip-status { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; white-space: nowrap; }
        .status-pending { background: var(--warning-bg); color: var(--warning-text); }
        .status-atasan { background: var(--teal-bg); color: var(--teal-text); }
        .status-approved { background: var(--success-bg); color: var(--success-text); }
        .status-rejected { background: var(--danger-bg); color: var(--danger-text); }
        .status-cancel { background: var(--gray-100); color: var(--gray-500); }

        .leave-body { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .leave-info {}
        .leave-label { font-size: 10.5px; color: var(--gray-400); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-bottom: 2px; }
        .leave-value { font-size: 13px; font-weight: 600; color: var(--gray-800); }
        .leave-value.full { grid-column: 1 / -1; }
        .badge-type { display: inline-block; background: var(--gray-100); color: var(--gray-700); padding: 3px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; border: 1px solid var(--gray-200); }
        .date-range { font-size: 12.5px; color: var(--gray-600); }
        .approver-hint { font-size: 11px; color: var(--gray-400); margin-top: 2px; }

        .leave-footer { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-top: 2px; }
        .submitted-time { font-size: 11px; color: var(--gray-400); }
        .btn-detail { padding: 8px 16px; background: var(--primary); color: #fff; border-radius: 10px; font-size: 12.5px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: background 0.2s; }
        .btn-detail:hover { background: var(--primary-dark); }
        .btn-detail svg { width: 13px; height: 13px; }

        /* Empty State */
        .empty-state { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 60px 20px; color: var(--gray-400); text-align: center; }
        .empty-state svg { opacity: 0.4; }
        .empty-state p { font-size: 14px; max-width: 280px; line-height: 1.6; }

        /* Pagination */
        .pagination-wrap { margin-top: 20px; display: flex; justify-content: center; }

        @media (min-width: 681px) {
            .page-wrapper { padding: 24px 16px; }
        }
        @media (max-width: 480px) {
            .filter-grid { grid-template-columns: 1fr; }
            .filter-group.full { grid-column: 1; }
            .filter-actions { flex-direction: column; }
            .leave-body { grid-template-columns: 1fr; }
            .leave-footer { flex-direction: column; align-items: stretch; }
            .btn-detail { justify-content: center; }
        }
    </style>

    <div class="page-wrapper">
        {{-- Header --}}
        <div class="page-header">
            <h1 class="page-title">
                Rekap Izin Bawahan
                <span class="role-chip">Supervisor</span>
            </h1>
            <p class="page-subtitle">Riwayat pengajuan izin dan cuti staf di bawah supervisi Anda.</p>
        </div>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        {{-- Filter Card --}}
        <div class="filter-card">
            <div class="filter-header">
                <div class="filter-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filter Data
                </div>
            </div>
            <form method="GET" action="{{ route('supervisor.leave.master') }}" class="filter-body">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label class="filter-label" for="submitted_range">Tanggal Pengajuan</label>
                        <input type="text" id="submitted_range" name="submitted_range" value="{{ $submittedRange ?? '' }}" placeholder="Pilih rentang..." class="form-control" autocomplete="off">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label" for="type">Jenis</label>
                        <select name="type" id="type" class="form-control">
                            <option value="">Semua</option>
                            @foreach($typeOptions as $case)
                                @php $val = $case->value; $lbl = ($val === \App\Enums\LeaveType::CUTI_KHUSUS->value) ? 'Cuti Khusus' : $case->label(); @endphp
                                <option value="{{ $val }}" @selected(($typeFilter ?? '') === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label" for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">Semua</option>
                            @foreach($statusOptions as $opt)
                                @php
                                    $statusLabels = [
                                        \App\Models\LeaveRequest::PENDING_SUPERVISOR => 'Menunggu Approval',
                                        \App\Models\LeaveRequest::PENDING_HR => 'Menunggu HRD',
                                        \App\Models\LeaveRequest::STATUS_APPROVED => 'Disetujui',
                                        \App\Models\LeaveRequest::STATUS_REJECTED => 'Ditolak',
                                        'BATAL' => 'Dibatalkan',
                                        'CANCEL_REQ' => 'Pengajuan Batal',
                                    ];
                                @endphp
                                <option value="{{ $opt }}" @selected(($status ?? '') === $opt)>{{ $statusLabels[$opt] ?? $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group full">
                        <label class="filter-label" for="q">Cari Karyawan</label>
                        <input type="text" name="q" id="q" value="{{ $q ?? '' }}" placeholder="Ketik nama bawahan..." class="form-control">
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-filter">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Filter
                    </button>
                    @if(($q ?? null) || ($typeFilter ?? null) || ($status ?? null) || ($submittedRange ?? null))
                        <a href="{{ route('supervisor.leave.master') }}" class="btn-reset">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if($items->isEmpty())
            <div class="card">
                <div class="empty-state">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <p>Belum ada riwayat pengajuan dari bawahan Anda.</p>
                </div>
            </div>
        @else
            <div class="card">
                @foreach($items as $i => $row)
                    @php
                        $st = $row->status;
                        $badgeClass = 'status-cancel';
                        $statusText = $row->status_label ?? $st;
                        if ($st === \App\Models\LeaveRequest::STATUS_APPROVED) { $badgeClass = 'status-approved'; }
                        elseif ($st === \App\Models\LeaveRequest::STATUS_REJECTED) { $badgeClass = 'status-rejected'; }
                        elseif ($st === \App\Models\LeaveRequest::PENDING_SUPERVISOR) { $badgeClass = 'status-pending'; $statusText = 'Menunggu Approval'; }
                        elseif ($st === \App\Models\LeaveRequest::PENDING_HR) { $badgeClass = 'status-atasan'; $statusText = 'Atasan Mengetahui'; }
                        $cardClass = 'leave-card';
                        if (in_array($st, [\App\Models\LeaveRequest::PENDING_SUPERVISOR, \App\Models\LeaveRequest::PENDING_HR])) $cardClass .= ' card-pending';
                        elseif ($st === \App\Models\LeaveRequest::STATUS_APPROVED) $cardClass .= ' card-approved';
                        elseif ($st === \App\Models\LeaveRequest::STATUS_REJECTED) $cardClass .= ' card-rejected';
                        elseif ($st === \App\Models\LeaveRequest::PENDING_HR) $cardClass .= ' card-atasan';
                        else $cardClass .= ' card-cancel';
                    @endphp
                    <div class="{{ $cardClass }}">
                        <div class="leave-header">
                            <div class="employee-info">
                                <div class="avatar">{{ substr($row->user->name, 0, 1) }}</div>
                                <div>
                                    <div class="employee-name">{{ $row->user->name }}</div>
                                    <div class="employee-role">{{ $row->user->position->name ?? 'Staff' }}</div>
                                </div>
                            </div>
                            <span class="chip-status {{ $badgeClass }}">{{ $statusText }}</span>
                        </div>
                        <div class="leave-body">
                            <div class="leave-info">
                                <div class="leave-label">Pengajuan</div>
                                <div class="leave-value">{{ $row->created_at->format('d M Y') }}</div>
                            </div>
                            <div class="leave-info">
                                <div class="leave-label">Periode</div>
                                <div class="date-range">
                                    {{ $row->start_date->format('d M') }}
                                    @if($row->end_date && !$row->end_date->eq($row->start_date))
                                        - {{ $row->end_date->format('d M Y') }}
                                    @endif
                                </div>
                            </div>
                            <div class="leave-info">
                                <div class="leave-label">Jenis</div>
                                <span class="badge-type">
                                    {{ \Illuminate\Support\Str::contains($row->type_label ?? $row->type, 'Cuti Khusus') ? 'Cuti Khusus' : ($row->type_label ?? $row->type) }}
                                </span>
                            </div>
                            @if($st === \App\Models\LeaveRequest::PENDING_SUPERVISOR)
                            <div class="leave-info" style="grid-column: 1 / -1;">
                                <div class="leave-label">Menunggu</div>
                                <div class="approver-hint">{{ $row->user->directSupervisor->name ?? $row->user->manager->name ?? '-' }}</div>
                            </div>
                            @endif
                        </div>
                        <div class="leave-footer">
                            <span class="submitted-time">{{ $row->created_at->format('H:i') }}</span>
                            <a href="{{ route('approval.show', $row) }}" class="btn-detail">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                Detail
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($items->hasPages())
            <div class="pagination-wrap">
                <x-pagination :items="$items" :preserve-query="true" />
            </div>
        @endif
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#submitted_range", {
                mode: "range",
                dateFormat: "Y-m-d",
                allowInput: true,
                locale: { rangeSeparator: " sampai " }
            });
        });
    </script>
</x-app>
