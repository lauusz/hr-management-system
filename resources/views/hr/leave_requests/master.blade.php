<x-app title="Master Izin / Cuti">

    <div class="leave-master-container">

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="flash flash-success">
            <svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        {{-- Page Header --}}
        <div class="page-header">
            <div class="page-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="page-header-text">
                <h1 class="page-title">Master Izin & Cuti</h1>
                <p class="page-subtitle">Riwayat lengkap pengajuan izin dan cuti seluruh karyawan.</p>
            </div>
            <a href="{{ route('hr.leave.manual.create') }}" class="btn btn-primary ml-auto">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Data
            </a>
        </div>

        {{-- Filter Card --}}
        <div class="filter-card">
            <div class="filter-header">
                <div class="filter-title-row">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="filter-icon"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span>Filter Data</span>
                </div>
            </div>

            <form method="GET" action="{{ route('hr.leave.master') }}" class="filter-form">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="submitted_range">Tanggal Pengajuan</label>
                        <input type="text"
                            id="submitted_range"
                            name="submitted_range"
                            value="{{ $submittedRange ?? '' }}"
                            placeholder="Pilih rentang tanggal..."
                            class="form-input"
                            autocomplete="off">
                    </div>

                    <div class="filter-group">
                        <label for="period_range">Periode Izin</label>
                        <input type="text"
                            id="period_range"
                            name="period_range"
                            value="{{ $periodRange ?? '' }}"
                            placeholder="Pilih rentang periode..."
                            class="form-input"
                            autocomplete="off">
                    </div>

                    <div class="filter-group">
                        <label for="type">Jenis</label>
                        <select name="type" id="type" class="form-input">
                            <option value="">Semua Jenis</option>
                            @foreach($typeOptions as $case)
                                @php
                                    $val = $case->value;
                                    $lbl = ($val === \App\Enums\LeaveType::CUTI_KHUSUS->value) ? 'Cuti Khusus' : $case->label();
                                @endphp
                                <option value="{{ $val }}" @selected($typeFilter === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    @php
                        $statusLabels = [
                            \App\Models\LeaveRequest::PENDING_SUPERVISOR => 'Menunggu Supervisor',
                            \App\Models\LeaveRequest::PENDING_HR => 'Menunggu HRD',
                            \App\Models\LeaveRequest::STATUS_APPROVED => 'Disetujui',
                            \App\Models\LeaveRequest::STATUS_REJECTED => 'Ditolak',
                            'BATAL' => 'Dibatalkan',
                            'CANCEL_REQ' => 'Pengajuan Batal',
                        ];
                    @endphp
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-input">
                            <option value="">Semua Status</option>
                            @foreach($statusOptions as $opt)
                                <option value="{{ $opt }}" @selected($status === $opt)>
                                    {{ $statusLabels[$opt] ?? $opt }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group filter-group-search">
                        <label for="q">Cari Karyawan</label>
                        <input type="text"
                               name="q"
                               id="q"
                               value="{{ $q ?? '' }}"
                               placeholder="Ketik nama karyawan..."
                               class="form-input">
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Filter
                    </button>

                    @if(($q ?? null) || ($typeFilter ?? null) || ($status ?? null) || ($submittedRange ?? null) || ($periodRange ?? null) || ($pt_id ?? null))
                    <a href="{{ route('hr.leave.master') }}" class="btn btn-secondary">
                        Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table Card --}}
        <div class="table-card">
            @if($items->isEmpty())
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <p>Belum ada data pengajuan izin/cuti.</p>
            </div>
            @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Karyawan</th>
                            <th>Tgl Pengajuan</th>
                            <th>Periode Izin</th>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $i => $row)
                        @php
                            $st = $row->status;
                            $badgeClass = 'badge-gray';
                            $badgeBg = '';
                            $badgeColor = '';

                            if ($st === \App\Models\LeaveRequest::STATUS_APPROVED) {
                                $badgeClass = 'badge-green';
                                $badgeBg = 'var(--success-bg)';
                                $badgeColor = 'var(--success-text)';
                            } elseif ($st === \App\Models\LeaveRequest::STATUS_REJECTED) {
                                $badgeClass = 'badge-red';
                                $badgeBg = 'var(--danger-bg)';
                                $badgeColor = 'var(--danger-text)';
                            } elseif (in_array($st, [\App\Models\LeaveRequest::PENDING_SUPERVISOR, \App\Models\LeaveRequest::PENDING_HR])) {
                                $badgeClass = 'badge-yellow';
                                $badgeBg = 'var(--warning-bg)';
                                $badgeColor = 'var(--warning-text)';
                            } elseif ($st === 'BATAL') {
                                $badgeClass = 'badge-gray';
                                $badgeBg = 'var(--bg-body)';
                                $badgeColor = 'var(--text-muted)';
                            }
                        @endphp

                        <tr>
                            <td class="text-muted text-center">
                                {{ $items->firstItem() + $i }}
                            </td>

                            <td>
                                <div class="employee-cell">
                                    <div class="employee-avatar">{{ substr($row->user->name, 0, 1) }}</div>
                                    <div class="employee-info">
                                        <a href="{{ route('hr.leave.show', $row) }}" class="employee-name">{{ $row->user->name }}</a>
                                        <div class="employee-detail">{{ $row->user->position->name ?? '-' }} - {{ $row->user->division->name ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="text-muted text-sm">
                                {{ $row->created_at?->format('d M Y') ?? '-' }}<br>
                                <span class="text-xs">{{ $row->created_at?->format('H:i') ?? '' }}</span>
                            </td>

                            <td>
                                <div class="date-cell">
                                    <span class="date-main">{{ $row->start_date->format('d M Y') }}</span>
                                    @if($row->end_date && $row->end_date->ne($row->start_date))
                                        <span class="date-range">s/d {{ $row->end_date->format('d M Y') }}</span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <span class="badge badge-basic">
                                    {{ \Illuminate\Support\Str::contains($row->type_label, 'Cuti Khusus') ? 'Cuti Khusus' : $row->type_label }}
                                </span>
                            </td>

                            <td>
                                <span class="badge-status" style="background: {{ $badgeBg }}; color: {{ $badgeColor }};">
                                    {{ $row->status_label }}
                                </span>

                                @if($row->status == \App\Models\LeaveRequest::PENDING_SUPERVISOR)
                                    <div class="approver-hint">
                                        Menunggu: <strong>{{ $row->user->directSupervisor->name ?? $row->user->manager->name ?? '-' }}</strong>
                                    </div>
                                @endif
                            </td>

                            <td class="actions-cell">
                                <a href="{{ route('hr.leave.show', $row) }}" class="action-btn" title="Lihat Detail">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Pagination --}}
        @if($items->hasPages())
        <div class="pagination-wrap">
            <x-pagination :items="$items" />
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

            flatpickr("#period_range", {
                mode: "range",
                dateFormat: "Y-m-d",
                allowInput: true,
                locale: { rangeSeparator: " sampai " }
            });
        });

        window.addEventListener("pageshow", function (event) {
            var shouldRefresh = sessionStorage.getItem('hrLeaveForceRefreshOnBack') === '1';
            var historyTraversal = event.persisted ||
                (typeof window.performance != "undefined" && window.performance.navigation.type === 2);

            if (shouldRefresh && historyTraversal) {
                sessionStorage.removeItem('hrLeaveForceRefreshOnBack');
                window.location.reload();
            }
        });
    </script>

    <style>
        /* === BASE VARIABLES === */
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #64748b;
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success-bg: #f0fdf4;
            --success-text: #15803d;
            --success-border: #bbf7d0;
            --danger-bg: #fef2f2;
            --danger-text: #b91c1c;
            --danger-border: #fecaca;
            --warning-bg: #fffbeb;
            --warning-text: #c2410c;
            --warning-border: #fed7aa;
            --blue-light: #eff6ff;
            --blue-text: #1d4ed8;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
        }

        /* === RESET & BASE === */
        .leave-master-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px 16px 40px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            color: var(--text-main);
        }

        /* === FLASH MESSAGES === */
        .flash {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-radius: var(--radius-md);
            margin-bottom: 16px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .flash-success { background: var(--success-bg); color: var(--success-text); border: 1px solid var(--success-border); }
        .flash-icon { width: 18px; height: 18px; flex-shrink: 0; }

        /* === PAGE HEADER === */
        .page-header {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .page-icon {
            width: 48px;
            height: 48px;
            background: var(--primary);
            color: #fff;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .page-icon svg { width: 24px; height: 24px; }
        .page-header-text { flex: 1; min-width: 200px; }
        .page-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-main);
        }
        .page-subtitle {
            margin: 4px 0 0;
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        .ml-auto { margin-left: auto; }

        /* === BUTTONS === */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 18px;
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.2s;
            text-decoration: none;
        }
        .btn svg { width: 16px; height: 16px; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary { background: var(--bg-body); color: var(--text-muted); border: 1px solid var(--border); }
        .btn-secondary:hover { background: var(--border); }

        /* === FILTER CARD === */
        .filter-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            margin-bottom: 16px;
        }
        .filter-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            background: var(--bg-body);
        }
        .filter-title-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .filter-icon { width: 14px; height: 14px; }
        .filter-form {
            padding: 16px 20px;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .filter-group-search {
            grid-column: 1 / -1;
        }
        .filter-group label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .filter-actions {
            display: flex;
            gap: 8px;
            padding-top: 4px;
        }

        /* === FORM INPUTS === */
        .form-input {
            padding: 9px 12px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            color: var(--text-main);
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: inherit;
            width: 100%;
            box-sizing: border-box;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* === TABLE CARD === */
        .table-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }

        /* === TABLE === */
        .table-wrap { overflow-x: auto; }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }
        .data-table th {
            text-align: left;
            padding: 12px 16px;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--bg-body);
            border-bottom: 1px solid var(--border);
        }
        .data-table td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: #fafafa; }

        /* === EMPLOYEE CELL === */
        .employee-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .employee-avatar {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            background: var(--blue-light);
            color: var(--blue-text);
            font-size: 0.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .employee-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-main);
            text-decoration: none;
        }
        .employee-name:hover { color: var(--primary); }
        .employee-detail {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* === DATE CELL === */
        .date-cell { line-height: 1.4; }
        .date-main { font-size: 0.875rem; font-weight: 500; color: var(--text-main); }
        .date-range {
            display: block;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* === BADGES === */
        .badge {
            display: inline-flex;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
            letter-spacing: 0.02em;
        }
        .badge-basic {
            background: var(--bg-body);
            color: var(--text-main);
            border: 1px solid var(--border);
        }
        .badge-status {
            display: inline-flex;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        .badge-green { background: var(--success-bg); color: var(--success-text); }
        .badge-red { background: var(--danger-bg); color: var(--danger-text); }
        .badge-yellow { background: var(--warning-bg); color: var(--warning-text); }
        .badge-gray { background: var(--bg-body); color: var(--text-muted); }

        /* === APPROVER HINT === */
        .approver-hint {
            font-size: 0.7rem;
            color: var(--text-muted);
            margin-top: 4px;
            line-height: 1.3;
        }

        /* === ACTIONS === */
        .actions-cell { text-align: right; white-space: nowrap; }
        .action-btn {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-sm);
            border: none;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: 0.2s;
        }
        .action-btn:hover { background: var(--bg-body); color: var(--primary); }
        .action-btn svg { width: 16px; height: 16px; }

        /* === EMPTY STATE === */
        .empty-state {
            padding: 60px 24px;
            text-align: center;
            color: var(--text-muted);
        }
        .empty-state svg { width: 56px; height: 56px; margin-bottom: 16px; opacity: 0.3; }
        .empty-state p { font-size: 0.95rem; margin: 0; }

        /* === HELPERS === */
        .text-muted { color: var(--text-muted); }
        .text-sm { font-size: 0.8125rem; }
        .text-xs { font-size: 0.75rem; }
        .text-center { text-align: center; }

        /* === PAGINATION === */
        .pagination-wrap {
            margin-top: 16px;
            display: flex;
            justify-content: center;
        }

        /* === MOBILE RESPONSIVE === */
        @media (max-width: 640px) {
            .page-header {
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }
            .page-icon { margin: 0 auto; }
            .ml-auto { margin: 0 auto; }
            .btn-primary { width: 100%; }

            .filter-grid {
                grid-template-columns: 1fr;
            }
            .filter-group-search {
                grid-column: 1;
            }
            .filter-actions {
                flex-direction: column;
            }
            .filter-actions .btn {
                width: 100%;
            }

            .data-table th, .data-table td {
                padding: 10px 12px;
            }
        }
    </style>

</x-app>
