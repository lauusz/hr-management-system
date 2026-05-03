<x-app title="Pengajuan Izin & Cuti">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Pengajuan Izin & Cuti</h1>
                <p class="section-subtitle">Kelola dan proses semua pengajuan izin & cuti karyawan</p>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="apv-alert apv-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="apv-alert apv-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- SUMMARY STATS --}}
    <div class="hr-stats">
        <div class="hr-stat-card">
            <div class="hr-stat-icon hr-stat-icon--total">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="hr-stat-content">
                <div class="hr-stat-value">{{ $totalCount }}</div>
                <div class="hr-stat-label">Total Pengajuan</div>
            </div>
        </div>

        <div class="hr-stat-card">
            <div class="hr-stat-icon hr-stat-icon--pending">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="hr-stat-content">
                <div class="hr-stat-value">{{ $pendingHrCount }}</div>
                <div class="hr-stat-label">Menunggu HRD</div>
            </div>
        </div>

        <div class="hr-stat-card">
            <div class="hr-stat-icon hr-stat-icon--supervisor">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="hr-stat-content">
                <div class="hr-stat-value">{{ $pendingSupervisorCount }}</div>
                <div class="hr-stat-label">Menunggu Atasan</div>
                @foreach($pendingSupervisorBreakdown->take(3) as $supName => $group)
                <div class="hr-stat-row">
                    <span class="hr-stat-row-name">{{ $supName }}</span>
                    <span class="hr-stat-row-count">{{ $group->count() }}</span>
                </div>
                @endforeach
                @if($pendingSupervisorBreakdown->count() > 3)
                <div class="hr-stat-row-more">+{{ $pendingSupervisorBreakdown->count() - 3 }} atasan lainnya</div>
                @endif
            </div>
        </div>
    </div>

    {{-- FILTER TABS --}}
    <div class="hr-tabs">
        <a href="{{ route('hr.leave.index') }}" class="hr-tab {{ $activeFilter === 'all' ? 'active' : '' }}">
            Semua
            <span class="hr-tab-count">{{ $totalCount }}</span>
        </a>
        <a href="{{ route('hr.leave.index', ['filter' => 'submitted_today']) }}" class="hr-tab {{ $activeFilter === 'submitted_today' ? 'active' : '' }}">
            Diajukan Hari Ini
            <span class="hr-tab-count">{{ $submittedTodayCount }}</span>
        </a>
        <a href="{{ route('hr.leave.index', ['filter' => 'period_today']) }}" class="hr-tab {{ $activeFilter === 'period_today' ? 'active' : '' }}">
            Periode Izin Hari Ini
            <span class="hr-tab-count">{{ $periodTodayCount }}</span>
        </a>
        <a href="{{ route('hr.leave.index', ['filter' => 'pending_hr']) }}" class="hr-tab {{ $activeFilter === 'pending_hr' ? 'active' : '' }}">
            Menunggu HRD
            <span class="hr-tab-count">{{ $pendingHrCount }}</span>
        </a>
        <a href="{{ route('hr.leave.index', ['filter' => 'pending_supervisor']) }}" class="hr-tab {{ $activeFilter === 'pending_supervisor' ? 'active' : '' }}">
            Menunggu Atasan
            <span class="hr-tab-count">{{ $pendingSupervisorCount }}</span>
        </a>
    </div>

    {{-- REQUEST LIST --}}
    <div class="apv-list">
        @forelse($leaves as $lv)
            @php
                $type = $lv->type;
                $typeClass = 'apv-type--default';

                if (in_array($type?->value, [\App\Enums\LeaveType::CUTI->value, \App\Enums\LeaveType::CUTI_KHUSUS->value])) {
                    $typeClass = 'apv-type--cuti';
                } elseif ($type?->value === \App\Enums\LeaveType::SAKIT->value) {
                    $typeClass = 'apv-type--sakit';
                } elseif (in_array($type?->value, [\App\Enums\LeaveType::IZIN_TELAT->value, \App\Enums\LeaveType::IZIN_PULANG_AWAL->value, \App\Enums\LeaveType::IZIN_TENGAH_KERJA->value, \App\Enums\LeaveType::IZIN->value])) {
                    $typeClass = 'apv-type--izin';
                } elseif ($type?->value === \App\Enums\LeaveType::DINAS_LUAR->value) {
                    $typeClass = 'apv-type--dinas';
                }

                $typeLabel = \Illuminate\Support\Str::contains($lv->type_label, 'Cuti Khusus') ? 'Cuti Khusus' : ($lv->type_label ?? $lv->type);

                // Status
                $statusClass = 'apv-badge--gray';
                $statusLabel = $lv->status;
                $statusIcon = '';

                if ($lv->status == \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
                    $statusClass = 'apv-badge--warning';
                    $statusLabel = 'Menunggu Atasan';
                    $supervisorName = $lv->user->directSupervisor?->name ?? $lv->user->manager?->name ?? 'Tidak ada';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                } elseif ($lv->status == \App\Models\LeaveRequest::PENDING_HR) {
                    $roleVal = $lv->user->role instanceof \App\Enums\UserRole ? $lv->user->role->value : $lv->user->role;
                    $isHRStaff = in_array(strtoupper((string)$roleVal), ['HR STAFF']);
                    if ($isHRStaff) {
                        $statusClass = 'apv-badge--warning';
                        $statusLabel = 'Menunggu Persetujuan';
                    } else {
                        $statusClass = 'apv-badge--teal';
                        $statusLabel = 'Atasan Mengetahui';
                    }
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>';
                } elseif ($lv->status == \App\Models\LeaveRequest::STATUS_APPROVED) {
                    $statusClass = 'apv-badge--success';
                    $statusLabel = 'Disetujui';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                } elseif ($lv->status == \App\Models\LeaveRequest::STATUS_REJECTED) {
                    $statusClass = 'apv-badge--error';
                    $statusLabel = 'Ditolak';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                }

                // Card accent based on status
                $cardAccent = '';
                if ($lv->status == \App\Models\LeaveRequest::PENDING_SUPERVISOR) $cardAccent = 'apv-card--pending';
                elseif ($lv->status == \App\Models\LeaveRequest::PENDING_HR) $cardAccent = 'apv-card--teal';
                elseif ($lv->status == \App\Models\LeaveRequest::STATUS_APPROVED) $cardAccent = 'apv-card--approved';
                elseif ($lv->status == \App\Models\LeaveRequest::STATUS_REJECTED) $cardAccent = 'apv-card--rejected';

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

                // Action label based on status
                $actionLabel = 'Detail';
                if ($lv->status == \App\Models\LeaveRequest::PENDING_HR) {
                    $actionLabel = 'Proses';
                }
            @endphp

            <a href="{{ route('hr.leave.show', $lv) }}" class="apv-card {{ $cardAccent }}" data-status="{{ $lv->status }}">
                <div class="apv-card-top">
                    <span class="apv-type {{ $typeClass }}">
                        @if($type?->value === \App\Enums\LeaveType::CUTI->value)
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @elseif($type?->value === \App\Enums\LeaveType::CUTI_KHUSUS->value)
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        @elseif($type?->value === \App\Enums\LeaveType::SAKIT->value)
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        @else
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                        {{ $typeLabel }}
                    </span>

                    <span class="apv-badge {{ $statusClass }}">
                        {!! $statusIcon !!}
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="apv-card-employee">
                    <div class="apv-avatar">{{ substr($lv->user->name, 0, 1) }}</div>
                    <div class="apv-employee-info">
                        <span class="apv-employee-name">{{ $lv->user->name }}</span>
                        <span class="apv-employee-detail">{{ $lv->user->position->name ?? '-' }} — {{ $lv->user->division->name ?? '-' }}</span>
                    </div>
                </div>

                <div class="apv-card-date">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ $start->translatedFormat('l, j F Y') }}</span>
                    @if($end->ne($start))
                        <span class="apv-card-date-sep">—</span>
                        <span>{{ $end->translatedFormat('l, j F Y') }}</span>
                    @endif
                </div>

                @if($lv->reason)
                    <div class="apv-card-note">{{ \Illuminate\Support\Str::limit($lv->reason, 100) }}</div>
                @endif

                {{-- Supervisor name for pending supervisor items --}}
                @if($lv->status == \App\Models\LeaveRequest::PENDING_SUPERVISOR)
                    <div class="apv-card-supervisor">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Atasan: {{ $supervisorName ?? 'Tidak ada' }}</span>
                    </div>
                @endif

                <div class="apv-card-footer">
                    <div class="apv-card-meta">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $timeAgo }} · {{ $durationLabel }}</span>
                    </div>
                    <div class="apv-card-action">
                        <span>{{ $actionLabel }}</span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        @empty
            <div class="apv-empty">
                <div class="apv-empty-icon">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="apv-empty-title">Tidak Ada Pengajuan</h3>
                <p class="apv-empty-desc">Tidak ada pengajuan yang membutuhkan perhatian Anda saat ini.</p>
            </div>
        @endforelse
    </div>

    @if($leaves->hasPages())
    <div class="apv-pagination">
        <x-pagination :items="$leaves" />
    </div>
    @endif

    <style>
        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .apv-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .apv-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }
        .apv-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

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
            color: var(--text-primary, #111827);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy  { background: rgba(10, 61, 98, 0.08);  color: var(--primary-dark, #0A3D62); }

        /* ========================================== */
        /* STATS GRID                                 */
        /* ========================================== */
        .hr-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .hr-stat-card {
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
            padding: 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            transition: all 0.2s ease;
        }
        .hr-stat-card:hover {
            box-shadow: 0 4px 12px rgba(20, 93, 160, 0.08);
            transform: translateY(-1px);
        }

        .hr-stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .hr-stat-icon--total { background: rgba(20, 93, 160, 0.08); color: var(--primary, #145DA0); }
        .hr-stat-icon--pending { background: rgba(245, 158, 11, 0.1); color: #a16207; }
        .hr-stat-icon--supervisor { background: rgba(147, 51, 234, 0.08); color: var(--purple, #9333EA); }

        .hr-stat-content { flex: 1; min-width: 0; }

        .hr-stat-value {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-primary, #111827);
            line-height: 1.2;
        }

        .hr-stat-label {
            font-size: 11px;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-top: 2px;
            font-weight: 600;
        }

        .hr-stat-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 4px;
            padding: 2px 0;
        }
        .hr-stat-row-name {
            font-size: 10px;
            color: var(--text-secondary, #6B7280);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 110px;
        }
        .hr-stat-row-count {
            font-size: 10px;
            font-weight: 600;
            color: var(--purple, #9333EA);
            background: rgba(147, 51, 234, 0.08);
            padding: 1px 6px;
            border-radius: 8px;
        }
        .hr-stat-row-more {
            font-size: 10px;
            color: var(--text-muted, #9CA3AF);
            margin-top: 2px;
            font-style: italic;
        }

        /* ========================================== */
        /* FILTER TABS                                */
        /* ========================================== */
        .hr-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            overflow-x: auto;
            padding-bottom: 4px;
            -webkit-overflow-scrolling: touch;
        }
        .hr-tabs::-webkit-scrollbar { height: 4px; }
        .hr-tabs::-webkit-scrollbar-thumb { background: var(--border-light, #E5E7EB); border-radius: 4px; }

        .hr-tab {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: var(--white, #FFFFFF);
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary, #6B7280);
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s ease;
            font-family: inherit;
            flex-shrink: 0;
            text-decoration: none;
        }
        .hr-tab:hover {
            border-color: var(--primary, #145DA0);
            color: var(--primary, #145DA0);
        }
        .hr-tab.active {
            background: var(--primary-dark, #0A3D62);
            border-color: var(--primary-dark, #0A3D62);
            color: var(--white, #FFFFFF);
            box-shadow: 0 2px 8px rgba(10, 61, 98, 0.2);
        }
        .hr-tab.active .hr-tab-count {
            background: rgba(255,255,255,0.2);
            color: var(--white, #FFFFFF);
        }
        .hr-tab-count {
            background: var(--gray-50, #F5F7FA);
            color: var(--text-secondary, #6B7280);
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 9999px;
            transition: all 0.2s ease;
        }

        /* ========================================== */
        /* REQUEST LIST & CARDS                       */
        /* ========================================== */
        .apv-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .apv-card {
            display: block;
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            padding: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
        }
        .apv-card:hover {
            border-color: rgba(20, 93, 160, 0.35);
            box-shadow: 0 4px 12px rgba(20, 93, 160, 0.08);
            transform: translateY(-2px);
        }
        .apv-card--pending  { border-left: 4px solid var(--warning, #F59E0B); }
        .apv-card--teal     { border-left: 4px solid #14B8A6; }
        .apv-card--approved { border-left: 4px solid var(--success, #22C55E); }
        .apv-card--rejected { border-left: 4px solid var(--error, #EF4444); opacity: 0.85; }

        .apv-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        /* Type badge (rounded, not pill) */
        .apv-type {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .apv-type--default { background: #F8FAFC; color: var(--text-secondary, #374151); }
        .apv-type--cuti    { background: rgba(59, 130, 246, 0.1); color: var(--info, #3B82F6); }
        .apv-type--sakit   { background: rgba(245, 158, 11, 0.1); color: #b45309; }
        .apv-type--izin    { background: rgba(20, 93, 160, 0.08); color: var(--primary, #145DA0); }
        .apv-type--dinas   { background: rgba(147, 51, 234, 0.1); color: var(--purple, #9333EA); }

        /* Status badge (pill) */
        .apv-badge {
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
            flex-shrink: 0;
        }
        .apv-badge--warning { background: rgba(245, 158, 11, 0.1); color: #a16207; }
        .apv-badge--teal    { background: rgba(20, 184, 166, 0.1); color: #0f766e; border: 1px solid rgba(20, 184, 166, 0.2); }
        .apv-badge--success { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .apv-badge--error   { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .apv-badge--gray    { background: #F8FAFC; color: var(--text-secondary, #374151); }

        /* Employee info */
        .apv-card-employee {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }
        .apv-avatar {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary, #145DA0);
            font-size: 0.875rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .apv-employee-info {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .apv-employee-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .apv-employee-detail {
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Date */
        .apv-card-date {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
            margin-bottom: 8px;
        }
        .apv-card-date svg {
            color: var(--text-muted, #6B7280);
            flex-shrink: 0;
        }
        .apv-card-date-sep {
            color: var(--text-muted, #6B7280);
            font-weight: 400;
        }

        /* Note */
        .apv-card-note {
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            line-height: 1.5;
            margin-bottom: 12px;
        }

        /* Supervisor chip (for pending supervisor items) */
        .apv-card-supervisor {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            color: var(--purple, #9333EA);
            background: rgba(147, 51, 234, 0.08);
            padding: 4px 10px;
            border-radius: 8px;
            margin-bottom: 12px;
            width: fit-content;
        }

        /* Footer */
        .apv-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid var(--border-light, #E5E7EB);
            gap: 8px;
        }
        .apv-card-meta {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
        }
        .apv-card-meta svg {
            flex-shrink: 0;
        }
        .apv-card-action {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--primary, #145DA0);
            flex-shrink: 0;
        }
        .apv-card-action svg {
            transition: transform 0.2s ease;
        }
        .apv-card:hover .apv-card-action svg {
            transform: translateX(3px);
        }

        /* ========================================== */
        /* EMPTY STATE                                */
        /* ========================================== */
        .apv-empty {
            text-align: center;
            padding: 48px 24px;
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
        }
        .apv-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 16px;
            background: var(--gray-50, #F5F7FA);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light, #9CA3AF);
        }
        .apv-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin: 0 0 6px;
        }
        .apv-empty-desc {
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            margin: 0 auto;
            max-width: 280px;
            line-height: 1.5;
        }

        /* ========================================== */
        /* PAGINATION                                 */
        /* ========================================== */
        .apv-pagination {
            margin-top: 24px;
        }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (max-width: 1024px) {
            .hr-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .hr-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            .hr-stat-card { padding: 12px; }
            .hr-stat-icon { width: 36px; height: 36px; }
            .hr-stat-value { font-size: 18px; }
            .hr-stat-label { font-size: 10px; }

            .hr-tabs {
                gap: 6px;
                margin-bottom: 12px;
            }
            .hr-tab {
                padding: 6px 12px;
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .hr-stats {
                grid-template-columns: repeat(3, 1fr);
                gap: 6px;
            }
            .hr-stat-card {
                padding: 8px 6px;
                gap: 6px;
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .hr-stat-icon {
                width: 28px;
                height: 28px;
            }
            .hr-stat-icon svg {
                width: 14px;
                height: 14px;
            }
            .hr-stat-value {
                font-size: 15px;
            }
            .hr-stat-label {
                font-size: 8px;
                letter-spacing: 0.02em;
                margin-top: 1px;
            }
            /* Hide supervisor breakdown on narrow mobile to save space */
            .hr-stat-row,
            .hr-stat-row-more {
                display: none;
            }

            .apv-card { padding: 12px; }
        }

        @media (min-width: 768px) {
            .apv-card {
                padding: 20px;
            }
        }

        @media (min-width: 1024px) {
            .apv-list {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            .apv-empty {
                grid-column: 1 / -1;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
