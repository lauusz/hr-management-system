<x-app title="Master Izin / Cuti">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Master Izin & Cuti</h1>
                <p class="section-subtitle">Riwayat lengkap pengajuan izin dan cuti seluruh karyawan</p>
            </div>
        </div>
    </x-slot>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="lm-alert lm-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="lm-alert lm-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="lm-cta-bar">
        <a href="{{ route('hr.leave.manual.create') }}" class="lm-btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Data
        </a>
        <a href="{{ route('hr.leave.master.export', request()->query()) }}" class="lm-btn-secondary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export Excel
        </a>
    </div>

    {{-- Stats Summary --}}
    <div class="lm-stats">
        <div class="lm-stat">
            <div class="lm-stat-value">{{ $items->total() }}</div>
            <div class="lm-stat-label">Total Data</div>
        </div>
        <div class="lm-stat-divider"></div>
        <div class="lm-stat">
            <div class="lm-stat-value lm-stat-value--success">{{ $items->where('status', \App\Models\LeaveRequest::STATUS_APPROVED)->count() }}</div>
            <div class="lm-stat-label">Disetujui</div>
        </div>
        <div class="lm-stat-divider"></div>
        <div class="lm-stat">
            <div class="lm-stat-value lm-stat-value--warning">{{ $items->whereIn('status', [\App\Models\LeaveRequest::PENDING_SUPERVISOR, \App\Models\LeaveRequest::PENDING_HR])->count() }}</div>
            <div class="lm-stat-label">Menunggu</div>
        </div>
        <div class="lm-stat-divider"></div>
        <div class="lm-stat">
            <div class="lm-stat-value lm-stat-value--error">{{ $items->where('status', \App\Models\LeaveRequest::STATUS_REJECTED)->count() }}</div>
            <div class="lm-stat-label">Ditolak</div>
        </div>
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
        $hasAdvancedFilter = ($typeFilter ?? null) || ($status ?? null) || ($submittedRange ?? null) || ($periodRange ?? null) || ($pt_id ?? null);
        $activeFilterCount = collect([$typeFilter, $status, $submittedRange, $periodRange, $pt_id])->filter()->count();
    @endphp

    {{-- Filter Card --}}
    <div class="lm-filter-card">
        <form method="GET" action="{{ route('hr.leave.master') }}" id="filterForm">
            <div class="lm-search-row">
                <div class="lm-search-input-wrap">
                    <svg class="lm-search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama karyawan..." class="lm-search-input">
                </div>
                <button type="submit" class="lm-btn-search">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Cari
                </button>
                @if(($q ?? null) || $hasAdvancedFilter)
                    <a href="{{ route('hr.leave.master') }}" class="lm-btn-reset">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reset
                    </a>
                @endif

                <button type="button" class="lm-btn-toggle-filter {{ $hasAdvancedFilter ? 'active' : '' }}" onclick="toggleFilterPanel()">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                    @if($hasAdvancedFilter)
                        <span class="lm-filter-badge">{{ $activeFilterCount }}</span>
                    @endif
                </button>
            </div>

            <div class="lm-filter-panel" id="filterPanel" style="{{ $hasAdvancedFilter ? '' : 'display: none;' }}">
                <div class="lm-filter-grid">
                    <div class="lm-filter-group">
                        <label class="lm-filter-label">Tanggal Pengajuan</label>
                        <input type="text"
                            id="submitted_range"
                            name="submitted_range"
                            value="{{ $submittedRange ?? '' }}"
                            placeholder="Pilih rentang tanggal"
                            class="lm-filter-input"
                            autocomplete="off">
                    </div>

                    <div class="lm-filter-group">
                        <label class="lm-filter-label">Periode Izin</label>
                        <input type="text"
                            id="period_range"
                            name="period_range"
                            value="{{ $periodRange ?? '' }}"
                            placeholder="Pilih rentang periode"
                            class="lm-filter-input"
                            autocomplete="off">
                    </div>

                    <div class="lm-filter-group">
                        <label class="lm-filter-label">Jenis</label>
                        <select name="type" class="lm-filter-input">
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

                    <div class="lm-filter-group">
                        <label class="lm-filter-label">Status</label>
                        <select name="status" class="lm-filter-input">
                            <option value="">Semua Status</option>
                            @foreach($statusOptions as $opt)
                                <option value="{{ $opt }}" @selected($status === $opt)>
                                    {{ $statusLabels[$opt] ?? $opt }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lm-filter-group">
                        <label class="lm-filter-label">PT</label>
                        <select name="pt_id" class="lm-filter-input">
                            <option value="">Semua PT</option>
                            @foreach($pts as $pt)
                                <option value="{{ $pt->id }}" @selected($pt_id == $pt->id)>{{ $pt->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lm-filter-actions">
                        <button type="submit" class="lm-btn-apply">Terapkan Filter</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Table Card --}}
    <div class="lm-table-card">
        @if($items->isEmpty())
            <div class="lm-empty">
                <div class="lm-empty-icon">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="lm-empty-title">Belum Ada Data</h3>
                <p class="lm-empty-desc">Belum ada data pengajuan izin/cuti yang sesuai dengan filter yang dipilih.</p>
            </div>
        @else
            <div class="lm-table-wrap">
                <table class="lm-data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Karyawan</th>
                            <th>Tgl Pengajuan</th>
                            <th>Periode Izin</th>
                            <th>Jenis</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $i => $row)
                            @php
                                $st = $row->status;
                                $badgeClass = 'lm-badge--neutral';
                                $badgeBg = 'var(--gray-100)';
                                $badgeColor = 'var(--text-muted)';
                                $statusIcon = '';
                                $statusLabel = $row->status_label ?? $statusLabels[$st] ?? $st;

                                if ($st === \App\Models\LeaveRequest::STATUS_APPROVED) {
                                    $badgeClass = 'lm-badge--success';
                                    $badgeBg = 'rgba(34, 197, 94, 0.1)';
                                    $badgeColor = 'var(--success)';
                                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
                                } elseif ($st === \App\Models\LeaveRequest::STATUS_REJECTED) {
                                    $badgeClass = 'lm-badge--error';
                                    $badgeBg = 'rgba(239, 68, 68, 0.1)';
                                    $badgeColor = 'var(--error)';
                                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
                                } elseif ($st === \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
                                    $badgeClass = 'lm-badge--warning';
                                    $badgeBg = 'rgba(245, 158, 11, 0.1)';
                                    $badgeColor = 'var(--warning)';
                                    $statusLabel = 'Menunggu Atasan';
                                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                                } elseif ($st === \App\Models\LeaveRequest::PENDING_HR) {
                                    $roleVal = $row->user->role instanceof \App\Enums\UserRole ? $row->user->role->value : $row->user->role;
                                    $isHRStaff = in_array(strtoupper((string)$roleVal), ['HR STAFF']);
                                    if ($isHRStaff) {
                                        $badgeClass = 'lm-badge--warning';
                                        $badgeBg = 'rgba(245, 158, 11, 0.1)';
                                        $badgeColor = 'var(--warning)';
                                        $statusLabel = 'Menunggu Persetujuan';
                                    } else {
                                        $badgeClass = 'lm-badge--teal';
                                        $badgeBg = 'rgba(20, 184, 166, 0.1)';
                                        $badgeColor = '#0f766e';
                                        $statusLabel = 'Atasan Mengetahui';
                                    }
                                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>';
                                } elseif ($st === 'BATAL' || $st === 'CANCEL_REQ') {
                                    $badgeClass = 'lm-badge--neutral';
                                    $badgeBg = 'var(--gray-100)';
                                    $badgeColor = 'var(--text-muted)';
                                }

                                // Type styling
                                $typeClass = 'lm-type--default';
                                $typeLabel = \Illuminate\Support\Str::contains($row->type_label, 'Cuti Khusus') ? 'Cuti Khusus' : $row->type_label;
                                if (in_array($row->type?->value, [\App\Enums\LeaveType::CUTI->value, \App\Enums\LeaveType::CUTI_KHUSUS->value])) {
                                    $typeClass = 'lm-type--cuti';
                                } elseif ($row->type?->value === \App\Enums\LeaveType::SAKIT->value) {
                                    $typeClass = 'lm-type--sakit';
                                } elseif (in_array($row->type?->value, [\App\Enums\LeaveType::IZIN_TELAT->value, \App\Enums\LeaveType::IZIN_PULANG_AWAL->value, \App\Enums\LeaveType::IZIN_TENGAH_KERJA->value, \App\Enums\LeaveType::IZIN->value])) {
                                    $typeClass = 'lm-type--izin';
                                } elseif ($row->type?->value === \App\Enums\LeaveType::DINAS_LUAR->value) {
                                    $typeClass = 'lm-type--dinas';
                                }
                            @endphp

                            <tr class="lm-clickable-row" onclick="window.location.href='{{ route('hr.leave.show', $row) }}'">
                                <td class="lm-cell-center lm-cell-muted">
                                    {{ $items->firstItem() + $i }}
                                </td>

                                <td>
                                    <div class="lm-employee">
                                        <div class="lm-employee-avatar">{{ substr($row->user->name, 0, 1) }}</div>
                                        <div class="lm-employee-info">
                                            <span class="lm-employee-name">{{ $row->user->name }}</span>
                                            <div class="lm-employee-detail">{{ $row->user->position->name ?? '-' }} — {{ $row->user->division->name ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="lm-cell-muted lm-cell-sm">
                                    {{ $row->created_at?->translatedFormat('j F Y') ?? '-' }}<br>
                                    <span class="lm-cell-xs">{{ $row->created_at?->format('H:i') ?? '' }}</span>
                                </td>

                                <td>
                                    <div class="lm-date-cell">
                                        <span class="lm-date-main">{{ $row->start_date->translatedFormat('j F Y') }}</span>
                                        @if($row->end_date && $row->end_date->ne($row->start_date))
                                            <span class="lm-date-range">s/d {{ $row->end_date->translatedFormat('j F Y') }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <span class="lm-type {{ $typeClass }}">
                                        @if($row->type?->value === \App\Enums\LeaveType::CUTI->value)
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        @elseif($row->type?->value === \App\Enums\LeaveType::CUTI_KHUSUS->value)
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        @elseif($row->type?->value === \App\Enums\LeaveType::SAKIT->value)
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                        @elseif($row->type?->value === \App\Enums\LeaveType::DINAS_LUAR->value)
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        @else
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                        {{ $typeLabel }}
                                    </span>
                                </td>

                                <td>
                                    <span class="lm-badge {{ $badgeClass }}" style="background: {{ $badgeBg }}; color: {{ $badgeColor }};">
                                        {!! $statusIcon !!}
                                        {{ $statusLabel }}
                                    </span>

                                    @if($row->status == \App\Models\LeaveRequest::PENDING_SUPERVISOR)
                                        <div class="lm-approver-hint">
                                            Menunggu: <strong>{{ $row->user->directSupervisor->name ?? $row->user->manager->name ?? '-' }}</strong>
                                        </div>
                                    @endif
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
        <div class="lm-pagination">
            <x-pagination :items="$items" />
        </div>
    @endif

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
        /* ========================================== */
        /* SECTION HEADER (x-slot)                    */
        /* ========================================== */
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .section-icon svg {
            width: 16px;
            height: 16px;
        }
        .section-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy  { background: rgba(10, 61, 98, 0.08);  color: var(--primary-dark); }

        /* ========================================== */
        /* BASE                                       */
        /* ========================================== */
        .lm-cta-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .lm-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
            border: none;
            cursor: pointer;
            flex: 1;
        }
        .lm-btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .lm-btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            background: var(--white);
            color: var(--text-muted);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
            flex: 1;
        }
        .lm-btn-secondary:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(20, 93, 160, 0.04);
        }

        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .lm-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 500;
        }
        .lm-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }
        .lm-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        /* ========================================== */
        /* STATS ROW                                  */
        /* ========================================== */
        .lm-stats {
            display: flex;
            align-items: center;
            gap: 1px;
            background: var(--border-light);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 16px;
            border: 1px solid var(--border-light);
        }
        .lm-stat {
            flex: 1;
            background: var(--white);
            padding: 10px 4px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            min-width: 0;
        }
        .lm-stat-value {
            font-size: 1.0625rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1;
        }
        .lm-stat-value--success { color: var(--success); }
        .lm-stat-value--warning { color: var(--warning); }
        .lm-stat-value--error   { color: var(--error); }
        .lm-stat-label {
            font-size: 0.5625rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.02em;
            line-height: 1.2;
        }
        .lm-stat-divider {
            display: none;
        }

        /* ========================================== */
        /* FILTER CARD                                */
        /* ========================================== */
        .lm-filter-card {
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            margin-bottom: 16px;
            overflow: hidden;
        }
        .lm-search-row {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 14px 16px;
        }
        .lm-search-input-wrap {
            position: relative;
            flex: 1;
        }
        .lm-search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            pointer-events: none;
        }
        .lm-search-input {
            width: 100%;
            padding: 10px 14px 10px 42px;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 14px;
            color: var(--text-primary);
            background: var(--white);
            transition: all 0.2s ease;
            outline: none;
            font-family: inherit;
        }
        .lm-search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .lm-search-input::placeholder { color: var(--text-light); }

        .lm-btn-search {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 16px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            white-space: nowrap;
            width: 100%;
        }
        .lm-btn-search:hover { background: var(--primary-dark); }

        .lm-btn-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 14px;
            background: var(--white);
            color: var(--text-muted);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
            width: 100%;
        }
        .lm-btn-reset:hover {
            background: var(--danger-light);
            border-color: #fecaca;
            color: var(--danger);
        }

        .lm-btn-toggle-filter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 16px;
            background: var(--white);
            color: var(--text-muted);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            position: relative;
            font-family: inherit;
            width: 100%;
        }
        .lm-btn-toggle-filter:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        .lm-btn-toggle-filter.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .lm-btn-toggle-filter.active svg { stroke: #fff; }
        .lm-filter-badge {
            background: var(--error);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }
        .lm-btn-toggle-filter.active .lm-filter-badge {
            background: rgba(255,255,255,0.3);
        }

        /* ========================================== */
        /* FILTER PANEL                               */
        /* ========================================== */
        .lm-filter-panel {
            background: var(--gray-50);
            border-top: 1px solid var(--border-light);
            margin: 0;
            padding: 14px 16px;
            animation: lmSlideDown 0.2s ease;
        }
        @keyframes lmSlideDown {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .lm-filter-grid {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .lm-filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .lm-filter-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .lm-filter-input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 13px;
            color: var(--text-primary);
            background: var(--white);
            transition: all 0.2s ease;
            outline: none;
            font-family: inherit;
        }
        .lm-filter-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .lm-filter-actions {
            display: flex;
            justify-content: flex-end;
        }
        .lm-btn-apply {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 20px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        .lm-btn-apply:hover { background: var(--primary-dark); }

        /* ========================================== */
        /* TABLE CARD                                 */
        /* ========================================== */
        .lm-table-card {
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .lm-table-wrap {
            overflow-x: auto;
        }
        .lm-data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }
        .lm-data-table th {
            text-align: left;
            padding: 14px 16px;
            font-size: 0.6875rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--gray-50);
            border-bottom: 1px solid var(--border-light);
        }
        .lm-data-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: top;
            font-size: 0.8125rem;
        }
        .lm-data-table tr:last-child td {
            border-bottom: none;
        }
        .lm-data-table tbody tr:hover td {
            background: var(--gray-50, #F5F7FA);
        }
        .lm-clickable-row {
            cursor: pointer;
        }

        /* ========================================== */
        /* EMPLOYEE CELL                              */
        /* ========================================== */
        .lm-employee {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .lm-employee-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
            font-size: 0.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .lm-employee-name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-primary);
            text-decoration: none;
        }
        .lm-employee-name:hover {
            color: var(--primary);
        }
        .lm-employee-detail {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* ========================================== */
        /* DATE CELL                                  */
        /* ========================================== */
        .lm-date-cell {
            line-height: 1.4;
        }
        .lm-date-main {
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--text-primary);
        }
        .lm-date-range {
            display: block;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* ========================================== */
        /* TYPE BADGE                                 */
        /* ========================================== */
        .lm-type {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .lm-type--default { background: var(--gray-100); color: var(--text-secondary); }
        .lm-type--cuti    { background: rgba(59, 130, 246, 0.1); color: var(--info); }
        .lm-type--sakit   { background: rgba(245, 158, 11, 0.1); color: #b45309; }
        .lm-type--izin    { background: rgba(20, 93, 160, 0.08); color: var(--primary); }
        .lm-type--dinas   { background: rgba(147, 51, 234, 0.1); color: #7c3aed; }

        /* ========================================== */
        /* STATUS BADGE (PILL)                        */
        /* ========================================== */
        .lm-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            white-space: nowrap;
        }
        .lm-badge--success { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .lm-badge--error   { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .lm-badge--warning { background: rgba(245, 158, 11, 0.1); color: #a16207; }
        .lm-badge--teal    { background: rgba(20, 184, 166, 0.1); color: #0f766e; border: 1px solid rgba(20, 184, 166, 0.2); }
        .lm-badge--neutral { background: var(--gray-100); color: var(--text-muted); }

        /* ========================================== */
        /* APPROVER HINT                              */
        /* ========================================== */
        .lm-approver-hint {
            font-size: 0.6875rem;
            color: var(--text-muted);
            margin-top: 4px;
            line-height: 1.3;
        }

        /* ========================================== */
        /* ACTIONS                                    */
        /* ========================================== */
        .lm-actions-cell {
            text-align: right;
            white-space: nowrap;
        }
        .lm-action-btn {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .lm-action-btn:hover {
            background: var(--gray-50);
            color: var(--primary);
        }

        /* ========================================== */
        /* CELL HELPERS                               */
        /* ========================================== */
        .lm-cell-center { text-align: center; }
        .lm-cell-muted { color: var(--text-muted); }
        .lm-cell-sm { font-size: 0.8125rem; }
        .lm-cell-xs { font-size: 0.75rem; }

        /* ========================================== */
        /* EMPTY STATE                                */
        /* ========================================== */
        .lm-empty {
            text-align: center;
            padding: 48px 24px;
            background: var(--white);
        }
        .lm-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 16px;
            background: var(--gray-50);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
        }
        .lm-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin: 0 0 6px;
        }
        .lm-empty-desc {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin: 0 auto;
            max-width: 280px;
            line-height: 1.5;
        }

        /* ========================================== */
        /* PAGINATION                                 */
        /* ========================================== */
        .lm-pagination {
            margin-top: 16px;
        }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (min-width: 480px) {
            .lm-cta-bar {
                justify-content: flex-start;
            }
            .lm-btn-primary,
            .lm-btn-secondary {
                flex: none;
            }
            .lm-stats {
                display: flex;
                align-items: center;
                background: var(--white);
                padding: 14px 20px;
            }
            .lm-stat {
                flex: 1;
                padding: 0;
                gap: 4px;
            }
            .lm-stat-value {
                font-size: 1.375rem;
            }
            .lm-stat-label {
                font-size: 0.6875rem;
                letter-spacing: 0.04em;
            }
            .lm-stat-divider {
                display: block;
                width: 1px;
                height: 36px;
                background: var(--border);
                flex-shrink: 0;
            }
            .lm-search-row {
                flex-direction: row;
                align-items: center;
                flex-wrap: wrap;
                padding: 16px 20px;
            }
            .lm-search-input-wrap {
                min-width: 260px;
            }
            .lm-btn-search,
            .lm-btn-reset,
            .lm-btn-toggle-filter {
                flex-shrink: 0;
                width: auto;
            }
            .lm-filter-panel {
                padding: 16px 20px;
            }
            .lm-filter-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 14px 16px;
                align-items: end;
            }
            .lm-filter-actions {
                grid-column: 1 / -1;
                justify-content: flex-end;
            }
        }

        @media (min-width: 768px) {
            .lm-filter-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .lm-filter-actions {
                grid-column: 1 / -1;
            }
        }

        @media (min-width: 1024px) {
            .lm-data-table th,
            .lm-data-table td {
                padding: 16px 20px;
            }
        }
    </style>

    <script>
    function toggleFilterPanel() {
        const panel = document.getElementById('filterPanel');
        const btn = document.querySelector('.lm-btn-toggle-filter');

        if (panel.style.display === 'none') {
            panel.style.display = '';
            btn.classList.add('active');
        } else {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    }
    </script>

</x-app>
