<x-app title="Pengajuan Izin & Cuti">

    @if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
    @endif

    {{-- SUMMARY STATS --}}
    <div class="stats-grid">
        <div class="stat-card stat-pending">
            <div class="stat-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $leaves->total() }}</div>
                <div class="stat-label">Total</div>
            </div>
        </div>

        <div class="stat-card stat-hr">
            <div class="stat-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $leaves->where('status', \App\Models\LeaveRequest::PENDING_HR)->count() }}</div>
                <div class="stat-label">Menunggu HRD</div>
            </div>
        </div>

        @php
            $pendingSupervisorLeaves = $leaves->where('status', \App\Models\LeaveRequest::PENDING_SUPERVISOR);
            $bySupervisor = $pendingSupervisorLeaves->groupBy(fn($lv) => ($lv->user->directSupervisor?->name ?? $lv->user->manager?->name) ?? 'Tanpa Atasan')->sortByDesc(fn($group) => $group->count());
        @endphp
        <div class="stat-card stat-supervisor">
            <div class="stat-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $pendingSupervisorLeaves->count() }}</div>
                <div class="stat-label">Menunggu Atasan</div>
                @foreach($bySupervisor->take(3) as $supName => $group)
                <div class="stat-supervisor-row">
                    <span class="stat-supervisor-name">{{ $supName }}</span>
                    <span class="stat-supervisor-count">{{ $group->count() }}</span>
                </div>
                @endforeach
                @if($bySupervisor->count() > 3)
                <div class="stat-supervisor-more">+{{ $bySupervisor->count() - 3 }} atasan lainnya</div>
                @endif
            </div>
        </div>
    </div>

    {{-- FILTER TABS --}}
    <div class="filter-tabs">
        <button class="tab-btn {{ !$submittedToday && !$periodToday ? 'active' : '' }}" data-filter="all" data-url="{{ route('hr.leave.index') }}">
            Semua
            <span class="tab-count">{{ $leaves->total() }}</span>
        </button>
        <button class="tab-btn {{ $submittedToday ? 'active' : '' }}" data-filter="submitted_today" data-url="{{ route('hr.leave.index', ['submitted_today' => 1]) }}">
            Diajukan Hari Ini
            <span class="tab-count">{{ $leaves->total() }}</span>
        </button>
        <button class="tab-btn {{ $periodToday ? 'active' : '' }}" data-filter="period_today" data-url="{{ route('hr.leave.index', ['period_today' => 1]) }}">
            Periode Izin Hari Ini
            <span class="tab-count">{{ $leaves->total() }}</span>
        </button>
        <button class="tab-btn" data-filter="PENDING_HR">
            Menunggu HRD
            <span class="tab-count">{{ $leaves->where('status', \App\Models\LeaveRequest::PENDING_HR)->count() }}</span>
        </button>
        <button class="tab-btn" data-filter="PENDING_SUPERVISOR">
            Menunggu Atasan
            <span class="tab-count">{{ $leaves->where('status', \App\Models\LeaveRequest::PENDING_SUPERVISOR)->count() }}</span>
        </button>
    </div>

    {{-- MAIN LIST --}}
    <div class="card">
        <div class="list-container">
            @forelse($leaves as $lv)
                @php
                    $type = $lv->type;
                    $badgeClass = 'badge-gray';
                    $typeIcon = '';

                    if (in_array($type, [\App\Enums\LeaveType::CUTI->value, \App\Enums\LeaveType::CUTI_KHUSUS->value])) {
                        $badgeClass = 'badge-blue';
                        $typeIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
                    } elseif ($type === \App\Enums\LeaveType::SAKIT->value) {
                        $badgeClass = 'badge-yellow';
                        $typeIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
                    } elseif (in_array($type, [\App\Enums\LeaveType::IZIN_TELAT->value, \App\Enums\LeaveType::IZIN_PULANG_AWAL->value, \App\Enums\LeaveType::IZIN_TENGAH_KERJA->value, \App\Enums\LeaveType::IZIN->value])) {
                        $badgeClass = 'badge-orange';
                        $typeIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                    } elseif ($type === \App\Enums\LeaveType::DINAS_LUAR->value) {
                        $badgeClass = 'badge-purple';
                        $typeIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>';
                    }

                    // Status
                    $statusBadge = 'badge-gray';
                    $statusLabel = $lv->status;
                    $statusIcon = '';

                    if ($lv->status == \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
                        $statusBadge = 'badge-yellow';
                        $statusLabel = 'Menunggu Atasan';
                        $supervisorName = $lv->user->directSupervisor?->name ?? $lv->user->manager?->name ?? 'Tidak ada';
                        $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                    } elseif ($lv->status == \App\Models\LeaveRequest::PENDING_HR) {
                        $roleVal = $lv->user->role instanceof \App\Enums\UserRole ? $lv->user->role->value : $lv->user->role;
                        $isHRStaff = in_array(strtoupper((string)$roleVal), ['HR STAFF']);
                        if ($isHRStaff) {
                            $statusBadge = 'badge-yellow';
                            $statusLabel = 'Menunggu Persetujuan';
                        } else {
                            $statusBadge = 'badge-teal';
                            $statusLabel = 'Atasan Mengetahui';
                        }
                        $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>';
                    } elseif ($lv->status == \App\Models\LeaveRequest::STATUS_APPROVED) {
                        $statusBadge = 'badge-green';
                        $statusLabel = 'Disetujui';
                        $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                    } elseif ($lv->status == \App\Models\LeaveRequest::STATUS_REJECTED) {
                        $statusBadge = 'badge-red';
                        $statusLabel = 'Ditolak';
                        $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                    }

                    // Relative time
                    $created = $lv->created_at;
                    $diffMinutes = (int) $created->diffInMinutes();
                    $diffHours = (int) $created->diffInHours();
                    $diffDays = (int) $created->diffInDays();

                    if ($diffMinutes < 1) {
                        $timeAgo = 'Baru saja';
                    } elseif ($diffMinutes < 60) {
                        $timeAgo = $diffMinutes . ' menit lalu';
                    } elseif ($diffHours < 24) {
                        $timeAgo = $diffHours . ' jam lalu';
                    } elseif ($diffDays == 1) {
                        $timeAgo = 'Kemarin';
                    } elseif ($diffDays < 7) {
                        $timeAgo = $diffDays . ' hari lalu';
                    } else {
                        $timeAgo = $created->translatedFormat('j F');
                    }

                    // Duration
                    $start = $lv->start_date;
                    $end = $lv->end_date ?? $start;
                    $days = $start->diffInDays($end) + 1;
                    $durationLabel = $days == 1 ? '1 hari' : $days . ' hari';
                @endphp

                <div class="list-item" data-status="{{ $lv->status }}">
                    {{-- LEFT: Employee Info --}}
                    <div class="item-main">
                        <div class="item-avatar">
                            {{ substr($lv->user->name, 0, 1) }}
                        </div>
                        <div class="item-info">
                            <a href="{{ route('hr.leave.show', $lv) }}" class="item-name">
                                {{ $lv->user->name }}
                            </a>
                            <div class="item-meta">
                                <span class="item-division">{{ $lv->user->division->name ?? '-' }}</span>
                                <span class="item-dot"></span>
                                <span class="item-date">{{ $timeAgo }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- CENTER: Request Details --}}
                    <div class="item-details">
                        <div class="detail-row">
                            <span class="badge-type {{ $badgeClass }}">
                                {!! $typeIcon !!}
                                {{ $lv->type_label ?? $lv->type }}
                            </span>
                            <span class="detail-duration">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ $durationLabel }}
                            </span>
                        </div>
                        <div class="detail-period">
                            {{ \Carbon\Carbon::parse($lv->start_date)->translatedFormat('l, j F Y') }}
                            @if($end->ne($start))
                                - {{ \Carbon\Carbon::parse($end)->translatedFormat('l, j F Y') }}
                            @endif
                        </div>
                        @if($lv->reason)
                        <div class="detail-reason">{{ Str::limit($lv->reason, 60) }}</div>
                        @endif
                    </div>

                    {{-- RIGHT: Status & Actions --}}
                    <div class="item-actions">
                        <span class="badge-type {{ $statusBadge }}">
                            {!! $statusIcon !!}
                            {{ $statusLabel }}
                        </span>

                        @if($lv->status == \App\Models\LeaveRequest::PENDING_HR)
                            <div class="action-buttons">
                                <a href="{{ route('hr.leave.show', $lv) }}" class="btn-approve-sm">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Proses
                                </a>
                            </div>
                        @elseif($lv->status == \App\Models\LeaveRequest::PENDING_SUPERVISOR)
                            <div class="action-buttons">
                                <a href="{{ route('hr.leave.show', $lv) }}" class="btn-detail-sm">
                                    Lihat
                                </a>
                            </div>
                            <span class="badge-supervisor">
                                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                {{ $supervisorName }}
                            </span>
                        @else
                            <div class="action-buttons">
                                <a href="{{ route('hr.leave.show', $lv) }}" class="btn-detail-sm">
                                    Detail
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p>Tidak ada pengajuan yang membutuhkan perhatian Anda saat ini.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div style="margin-top: 20px;">
        <x-pagination :items="$leaves" />
    </div>

    <style>
        :root {
            --navy: #1e4a8d;
            --navy-dark: #163a75;
            --bg-page: #f8fafc;
            --white: #ffffff;
            --border: #e5e7eb;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
        }

        * { box-sizing: border-box; }

        /* --- ALERT --- */
        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid #a7f3d0;
            margin-bottom: 16px;
            font-size: 14px;
        }

        /* --- STATS GRID --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--white);
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-pending .stat-icon { background: #fefce8; color: #a16207; }
        .stat-hr .stat-icon { background: #eff6ff; color: #1e4a8d; }
        .stat-supervisor .stat-icon { background: #f3e8ff; color: #7e22ce; }
        .stat-approved .stat-icon { background: #dcfce7; color: #166534; }

        .stat-content { flex: 1; min-width: 0; }

        .stat-value {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .stat-label {
            font-size: 11px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-top: 2px;
        }

        .stat-supervisor-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 4px;
            padding: 2px 0;
        }

        .stat-supervisor-name {
            font-size: 10px;
            color: var(--text-secondary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 120px;
        }

        .stat-supervisor-count {
            font-size: 10px;
            font-weight: 600;
            color: #7e22ce;
            background: #f3e8ff;
            padding: 1px 6px;
            border-radius: 8px;
        }

        .stat-supervisor-more {
            font-size: 10px;
            color: var(--text-muted);
            margin-top: 2px;
            font-style: italic;
        }

        /* --- FILTER TABS --- */
        .filter-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            overflow-x: auto;
            padding-bottom: 4px;
            -webkit-overflow-scrolling: touch;
        }

        .filter-tabs::-webkit-scrollbar { height: 4px; }
        .filter-tabs::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

        .tab-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary);
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }

        .tab-btn:hover {
            border-color: var(--navy);
            color: var(--navy);
        }

        .tab-btn.active {
            background: var(--navy);
            border-color: var(--navy);
            color: var(--white);
        }

        .tab-btn.active .tab-count {
            background: rgba(255,255,255,0.2);
            color: var(--white);
        }

        .tab-count {
            background: #f3f4f6;
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 10px;
        }

        /* --- CARD --- */
        .card {
            background: var(--white);
            border-radius: 14px;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .list-container {
            display: flex;
            flex-direction: column;
        }

        /* --- LIST ITEM --- */
        .list-item {
            display: grid;
            grid-template-columns: 200px 1fr auto;
            gap: 16px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            align-items: center;
            transition: background 0.15s;
        }

        .list-item:last-child { border-bottom: none; }
        .list-item:hover { background: #fafbfc; }

        .list-item[data-status="PENDING_SUPERVISOR"] {
            border-left: 3px solid #f59e0b;
        }

        .list-item[data-status="PENDING_HR"] {
            border-left: 3px solid var(--navy);
        }

        .list-item[data-status="APPROVED"] {
            border-left: 3px solid #059669;
        }

        .list-item[data-status="REJECTED"] {
            border-left: 3px solid #dc2626;
            opacity: 0.7;
        }

        /* --- AVATAR & NAME --- */
        .item-main {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .item-avatar {
            width: 40px;
            height: 40px;
            background: var(--navy);
            color: var(--white);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .item-info { min-width: 0; }

        .item-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            text-decoration: none;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-name:hover { color: var(--navy); }

        .item-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 2px;
        }

        .item-division {
            font-size: 11px;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-dot {
            width: 3px;
            height: 3px;
            background: var(--text-muted);
            border-radius: 50%;
        }

        .item-date {
            font-size: 11px;
            color: var(--text-muted);
            white-space: nowrap;
        }

        /* --- DETAILS --- */
        .item-details { min-width: 0; }

        .detail-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 4px;
        }

        .detail-duration {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .detail-period {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 4px;
        }

        .detail-reason {
            font-size: 12px;
            color: var(--text-muted);
            font-style: italic;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 300px;
        }

        /* --- BADGES --- */
        .badge-type {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 600;
            white-space: nowrap;
            letter-spacing: 0.02em;
        }

        .badge-blue { background: #eff6ff; color: #1d4ed8; }
        .badge-yellow { background: #fefce8; color: #a16207; }
        .badge-orange { background: #fff7ed; color: #c2410c; }
        .badge-purple { background: #f3e8ff; color: #7e22ce; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-teal { background: #ccfbf1; color: #0f766e; }

        .badge-supervisor {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            color: #7e22ce;
            background: #f3e8ff;
            padding: 3px 8px;
            border-radius: 6px;
            white-space: nowrap;
        }

        /* --- ACTION BUTTONS --- */
        .item-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-approve-sm {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 12px;
            background: var(--navy);
            color: var(--white);
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-approve-sm:hover { background: var(--navy-dark); }

        .btn-detail-sm {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            background: #f3f4f6;
            color: var(--text-secondary);
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-detail-sm:hover { background: #e5e7eb; color: var(--text-primary); }

        /* --- EMPTY STATE --- */
        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: var(--text-muted);
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: #9ca3af;
        }

        .empty-state p { font-size: 14px; margin: 0; }

        /* --- RESPONSIVE --- */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .list-item {
                grid-template-columns: 160px 1fr auto;
                gap: 12px;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .stat-card { padding: 12px; }
            .stat-value { font-size: 18px; }
            .stat-label { font-size: 10px; }

            .filter-tabs {
                gap: 6px;
                margin-bottom: 12px;
            }

            .tab-btn {
                padding: 6px 12px;
                font-size: 12px;
            }

            .card { border-radius: 12px; }

            .list-container { gap: 0; }

            .list-item {
                display: flex;
                flex-direction: column;
                gap: 12px;
                padding: 16px;
                border-left: none;
                border-bottom: 1px solid var(--border);
                position: relative;
            }

            .list-item[data-status="PENDING_SUPERVISOR"]::before,
            .list-item[data-status="PENDING_HR"]::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 3px;
            }

            .list-item[data-status="PENDING_SUPERVISOR"]::before { background: #f59e0b; }
            .list-item[data-status="PENDING_HR"]::before { background: var(--navy); }

            .item-main {
                width: 100%;
            }

            .item-actions {
                width: 100%;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                padding-top: 12px;
                border-top: 1px solid #f3f4f6;
            }

            .detail-reason { max-width: none; }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .stat-card {
                padding: 10px;
                gap: 10px;
            }

            .stat-icon {
                width: 36px;
                height: 36px;
            }

            .stat-value { font-size: 16px; }

            .list-item { padding: 12px; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter tabs functionality
            const tabBtns = document.querySelectorAll('.tab-btn');

            tabBtns.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    const url = this.dataset.url;
                    if (url) {
                        window.location.href = url;
                    } else {
                        // Handle status filter (non-URL filters)
                        const filter = this.dataset.filter;
                        const listItems = document.querySelectorAll('.list-item');

                        // Update active tab
                        tabBtns.forEach(function(b) {
                            if (!b.dataset.url) b.classList.remove('active');
                        });
                        this.classList.add('active');

                        // Filter items
                        listItems.forEach(function(item) {
                            if (filter === 'all') {
                                item.style.display = '';
                            } else {
                                if (item.dataset.status === filter) {
                                    item.style.display = '';
                                } else {
                                    item.style.display = 'none';
                                }
                            }
                        });
                    }
                });
            });

            // pageshow handler for back navigation
            window.addEventListener('pageshow', function(event) {
                var shouldRefresh = sessionStorage.getItem('hrLeaveForceRefreshOnBack') === '1';
                var historyTraversal = event.persisted ||
                    (typeof window.performance != 'undefined' && window.performance.navigation.type === 2);

                if (shouldRefresh && historyTraversal) {
                    sessionStorage.removeItem('hrLeaveForceRefreshOnBack');
                    window.location.reload();
                }
            });
        });
    </script>

</x-app>
