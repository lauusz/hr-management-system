<x-app title="Izin / Cuti Saya">

    @if (session('success'))
        <div class="alert-success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('ok'))
        <div class="alert-success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('ok') }}
        </div>
    @endif

    {{-- ============================================== --}}
    {{-- PAGE HEADER --}}
    {{-- ============================================== --}}
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Riwayat Pengajuan</h1>
            <p class="page-subtitle">Pantau status pengajuan izin dan cuti Anda</p>
        </div>
        <a href="{{ route('leave-requests.create') }}" class="btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Pengajuan
        </a>
    </div>

    {{-- ============================================== --}}
    {{-- FILTER SECTION --}}
    {{-- ============================================== --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('leave-requests.index') }}" class="filter-form">
            <div class="filter-row">
                <div class="filter-field">
                    <label class="filter-label">Tanggal Pengajuan</label>
                    <input type="text"
                        id="submitted_range"
                        name="submitted_range"
                        value="{{ $submittedRange ?? '' }}"
                        placeholder="Pilih rentang tanggal"
                        class="filter-input"
                        autocomplete="off">
                </div>

                <div class="filter-field">
                    <label class="filter-label">Jenis Pengajuan</label>
                    <select name="type" class="filter-input">
                        <option value="">Semua Jenis</option>
                        @foreach($typeOptions as $case)
                            @php
                                $val = $case->value;
                                $lbl = ($val === \App\Enums\LeaveType::CUTI_KHUSUS->value) ? 'Cuti Khusus' : $case->label();
                            @endphp
                            <option value="{{ $val }}" @selected($typeFilter === $val)>
                                {{ $lbl }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-filter">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter
                    </button>

                    @if(($submittedRange ?? null) || ($typeFilter ?? null))
                    <a href="{{ route('leave-requests.index') }}" class="btn-reset">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reset
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- ============================================== --}}
    {{-- SUMMARY STATS --}}
    {{-- ============================================== --}}
    <div class="stats-row">
        <div class="stat-item">
            <div class="stat-value">{{ $items->total() }}</div>
            <div class="stat-label">Total Pengajuan</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <div class="stat-value stat-approved">{{ $items->where('status', \App\Models\LeaveRequest::STATUS_APPROVED)->count() }}</div>
            <div class="stat-label">Disetujui</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <div class="stat-value stat-pending">{{ $items->whereIn('status', [\App\Models\LeaveRequest::PENDING_SUPERVISOR, \App\Models\LeaveRequest::PENDING_HR])->count() }}</div>
            <div class="stat-label">Menunggu</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <div class="stat-value stat-rejected">{{ $items->where('status', \App\Models\LeaveRequest::STATUS_REJECTED)->count() }}</div>
            <div class="stat-label">Ditolak</div>
        </div>
    </div>

    {{-- ============================================== --}}
    {{-- LEAVE LIST --}}
    {{-- ============================================== --}}
    <div class="leave-list">
        @forelse($items as $row)
            @php
                $st = $row->status;
                $badgeClass = 'badge-gray';
                $statusLabel = $st;
                $statusIcon = '';

                if ($st === \App\Models\LeaveRequest::STATUS_APPROVED) {
                    $badgeClass = 'badge-green';
                    $roleVal = $row->user->role instanceof \App\Enums\UserRole ? $row->user->role->value : $row->user->role;
                    $isOwnerHRD = in_array(strtoupper((string)$roleVal), ['HRD', 'HR MANAGER']);
                    $statusLabel = $isOwnerHRD ? 'Disetujui' : 'Disetujui HRD';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                } elseif ($st === \App\Models\LeaveRequest::STATUS_REJECTED) {
                    $badgeClass = 'badge-red';
                    $statusLabel = 'Ditolak';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                } elseif ($st === \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
                    $badgeClass = 'badge-yellow';
                    $statusLabel = 'Menunggu Atasan';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                } elseif ($st === \App\Models\LeaveRequest::PENDING_HR) {
                    $roleVal = $row->user->role instanceof \App\Enums\UserRole ? $row->user->role->value : $row->user->role;
                    $isHRStaff = in_array(strtoupper((string)$roleVal), ['HR STAFF']);
                    if ($isHRStaff) {
                        $badgeClass = 'badge-yellow';
                        $statusLabel = 'Menunggu Persetujuan';
                    } else {
                        $badgeClass = 'badge-teal';
                        $statusLabel = 'Atasan Mengetahui';
                    }
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                }

                $typeLabel = \Illuminate\Support\Str::contains($row->type_label, 'Cuti Khusus') ? 'Cuti Khusus' : $row->type_label;

                // Type color
                $typeClass = 'type-default';
                if (in_array($row->type->value, [\App\Enums\LeaveType::CUTI->value, \App\Enums\LeaveType::CUTI_KHUSUS->value])) {
                    $typeClass = 'type-cuti';
                } elseif ($row->type->value === \App\Enums\LeaveType::SAKIT->value) {
                    $typeClass = 'type-sakit';
                } elseif (in_array($row->type->value, [\App\Enums\LeaveType::IZIN_TELAT->value, \App\Enums\LeaveType::IZIN_PULANG_AWAL->value, \App\Enums\LeaveType::IZIN_TENGAH_KERJA->value, \App\Enums\LeaveType::IZIN->value])) {
                    $typeClass = 'type-izin';
                }
            @endphp

            <a href="{{ route('leave-requests.show', $row) }}" class="leave-card">
                <div class="leave-card-main">
                    <div class="leave-type-badge {{ $typeClass }}">
                        @if($row->type->value === \App\Enums\LeaveType::CUTI->value)
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @elseif($row->type->value === \App\Enums\LeaveType::CUTI_KHUSUS->value)
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        @elseif($row->type->value === \App\Enums\LeaveType::SAKIT->value)
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        @else
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                        {{ $typeLabel }}
                    </div>

                    <div class="leave-status-badge {{ $badgeClass }}">
                        {!! $statusIcon !!}
                        {{ $statusLabel }}
                    </div>
                </div>

                <div class="leave-card-detail">
                    <div class="leave-period">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ $row->start_date->format('d M Y') }}</span>
                        @if($row->end_date && $row->end_date->ne($row->start_date))
                            <span class="period-separator">—</span>
                            <span>{{ $row->end_date->format('d M Y') }}</span>
                        @endif
                    </div>

                    @if($row->reason)
                    <div class="leave-reason">
                        {{ Str::limit($row->reason, 80) }}
                    </div>
                    @endif
                </div>

                <div class="leave-card-footer">
                    <div class="leave-meta">
                        <span class="leave-date">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $row->created_at->format('d M Y') }} · {{ $row->created_at->format('H:i') }}
                        </span>
                    </div>
                    <div class="leave-action">
                        <span>Detail</span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        @empty
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="empty-title">Belum Ada Pengajuan</h3>
                <p class="empty-desc">Ajukan izin atau cuti baru dengan menekan tombol "Buat Pengajuan" di atas.</p>
            </div>
        @endforelse
    </div>

    @if($items->hasPages())
    <div class="pagination-wrapper">
        <x-pagination :items="$items" />
    </div>
    @endif

    <style>
        /* ========================================== */
        /* PAGE HEADER */
        /* ========================================== */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 16px;
        }

        .page-header-left {
            flex: 1;
        }

        .page-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            margin: 0 0 2px;
        }

        .page-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: #1e4a8d;
            color: #fff;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-primary:hover {
            background: #163a75;
            transform: translateY(-1px);
        }

        /* ========================================== */
        /* FILTER CARD */
        /* ========================================== */
        .filter-card {
            background: #fff;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #f3f4f6;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .filter-form {
            width: 100%;
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-end;
        }

        .filter-field {
            flex: 1;
            min-width: 180px;
        }

        .filter-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .filter-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.875rem;
            color: #374151;
            background: #fff;
            transition: all 0.2s ease;
            outline: none;
        }

        .filter-input:focus {
            border-color: #1e4a8d;
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-filter {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            background: #1e4a8d;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-filter:hover {
            background: #163a75;
        }

        .btn-reset {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 14px;
            background: #fff;
            color: #6b7280;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-reset:hover {
            background: #fef2f2;
            border-color: #fca5a5;
            color: #dc2626;
        }

        /* ========================================== */
        /* STATS ROW */
        /* ========================================== */
        .stats-row {
            display: flex;
            align-items: center;
            background: #fff;
            border-radius: 14px;
            padding: 16px 24px;
            margin-bottom: 20px;
            border: 1px solid #f3f4f6;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .stat-item {
            flex: 1;
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
        }

        .stat-approved { color: #16a34a; }
        .stat-pending { color: #ca8a04; }
        .stat-rejected { color: #dc2626; }

        .stat-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .stat-divider {
            width: 1px;
            height: 40px;
            background: #e5e7eb;
        }

        /* ========================================== */
        /* LEAVE LIST */
        /* ========================================== */
        .leave-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .leave-card {
            display: block;
            background: #fff;
            border-radius: 14px;
            padding: 18px 20px;
            border: 1px solid #f3f4f6;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
        }

        .leave-card:hover {
            border-color: #1e4a8d;
            box-shadow: 0 4px 12px rgba(30, 74, 141, 0.08);
            transform: translateY(-1px);
        }

        .leave-card-main {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .leave-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .type-default { background: #f3f4f6; color: #4b5563; }
        .type-cuti { background: #eef4ff; color: #1e4a8d; }
        .type-sakit { background: #fef9c3; color: #854d0e; }
        .type-izin { background: #ffedd5; color: #c2410c; }

        .leave-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        .badge-teal { background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; }

        .leave-card-detail {
            margin-bottom: 12px;
        }

        .leave-period {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #374151;
            font-weight: 500;
        }

        .leave-period svg {
            color: #6b7280;
            flex-shrink: 0;
        }

        .period-separator {
            color: #9ca3af;
        }

        .leave-reason {
            margin-top: 6px;
            font-size: 0.85rem;
            color: #6b7280;
            line-height: 1.4;
        }

        .leave-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid #f3f4f6;
        }

        .leave-meta {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .leave-date {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
            color: #9ca3af;
        }

        .leave-action {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e4a8d;
        }

        .leave-action svg {
            transition: transform 0.2s ease;
        }

        .leave-card:hover .leave-action svg {
            transform: translateX(3px);
        }

        /* ========================================== */
        /* EMPTY STATE */
        /* ========================================== */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            background: #fff;
            border-radius: 14px;
            border: 1px solid #f3f4f6;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 16px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
        }

        .empty-title {
            font-size: 1rem;
            font-weight: 600;
            color: #374151;
            margin: 0 0 6px;
        }

        .empty-desc {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
            max-width: 300px;
            margin-inline: auto;
        }

        /* ========================================== */
        /* ALERTS */
        /* ========================================== */
        .alert-success {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #ecfdf5;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid #a7f3d0;
            margin-bottom: 16px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* ========================================== */
        /* PAGINATION */
        /* ========================================== */
        .pagination-wrapper {
            margin-top: 24px;
        }

        /* ========================================== */
        /* RESPONSIVE */
        /* ========================================== */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }

            .btn-primary {
                justify-content: center;
            }

            .filter-row {
                flex-direction: column;
                gap: 12px;
            }

            .filter-field {
                min-width: 100%;
            }

            .filter-actions {
                width: 100%;
            }

            .btn-filter, .btn-reset {
                flex: 1;
                justify-content: center;
            }

            .stats-row {
                padding: 14px 16px;
                gap: 8px;
            }

            .stat-value {
                font-size: 1.25rem;
            }

            .leave-card {
                padding: 16px;
            }

            .leave-card-main {
                gap: 8px;
            }

            .leave-type-badge, .leave-status-badge {
                font-size: 0.75rem;
                padding: 4px 10px;
            }
        }

        @media (max-width: 480px) {
            .stats-row {
                flex-wrap: wrap;
                justify-content: center;
            }

            .stat-item {
                min-width: 50%;
                padding: 8px 0;
            }

            .stat-divider {
                display: none;
            }

            .leave-period {
                flex-wrap: wrap;
                font-size: 0.85rem;
            }
        }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#submitted_range", {
            mode: "range",
            dateFormat: "Y-m-d",
            allowInput: true,
            locale: { rangeSeparator: " sampai " }
        });
    </script>

</x-app>
