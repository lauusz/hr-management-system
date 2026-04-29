<x-app title="Izin / Cuti Saya">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Riwayat Pengajuan</h1>
                <p class="section-subtitle">Pantau status pengajuan izin dan cuti Anda</p>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="lr-alert lr-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('ok'))
        <div class="lr-alert lr-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('ok') }}
        </div>
    @endif

    {{-- ============================================== --}}
    {{-- CTA --}}
    {{-- ============================================== --}}
    <div class="lr-cta-bar">
        <a href="{{ route('leave-requests.create') }}" class="lr-btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Pengajuan
        </a>
    </div>

    {{-- ============================================== --}}
    {{-- FILTER SECTION --}}
    {{-- ============================================== --}}
    <div class="lr-filter">
        <form method="GET" action="{{ route('leave-requests.index') }}">
            <div class="lr-filter-body">
                <div class="lr-filter-group">
                    <label class="lr-filter-label">Tanggal Pengajuan</label>
                    <input type="text"
                        id="submitted_range"
                        name="submitted_range"
                        value="{{ $submittedRange ?? '' }}"
                        placeholder="Pilih rentang tanggal"
                        class="lr-filter-input"
                        autocomplete="off">
                </div>

                <div class="lr-filter-group">
                    <label class="lr-filter-label">Jenis Pengajuan</label>
                    <select name="type" class="lr-filter-input">
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

                <div class="lr-filter-actions">
                    <button type="submit" class="lr-btn-filter">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter
                    </button>

                    @if(($submittedRange ?? null) || ($typeFilter ?? null))
                    <a href="{{ route('leave-requests.index') }}" class="lr-btn-reset">
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
    <div class="lr-stats">
        <div class="lr-stat">
            <div class="lr-stat-value">{{ $items->total() }}</div>
            <div class="lr-stat-label">Total</div>
        </div>
        <div class="lr-stat-divider"></div>
        <div class="lr-stat">
            <div class="lr-stat-value lr-stat-value--success">{{ $items->where('status', \App\Models\LeaveRequest::STATUS_APPROVED)->count() }}</div>
            <div class="lr-stat-label">Disetujui</div>
        </div>
        <div class="lr-stat-divider"></div>
        <div class="lr-stat">
            <div class="lr-stat-value lr-stat-value--warning">{{ $items->whereIn('status', [\App\Models\LeaveRequest::PENDING_SUPERVISOR, \App\Models\LeaveRequest::PENDING_HR])->count() }}</div>
            <div class="lr-stat-label">Menunggu</div>
        </div>
        <div class="lr-stat-divider"></div>
        <div class="lr-stat">
            <div class="lr-stat-value lr-stat-value--error">{{ $items->where('status', \App\Models\LeaveRequest::STATUS_REJECTED)->count() }}</div>
            <div class="lr-stat-label">Ditolak</div>
        </div>
    </div>

    {{-- ============================================== --}}
    {{-- REQUEST LIST --}}
    {{-- ============================================== --}}
    <div class="lr-list">
        @forelse($items as $row)
            @php
                $st = $row->status;
                $badgeClass = 'lr-badge--gray';
                $statusLabel = $st;
                $statusIcon = '';

                if ($st === \App\Models\LeaveRequest::STATUS_APPROVED) {
                    $badgeClass = 'lr-badge--success';
                    $roleVal = $row->user->role instanceof \App\Enums\UserRole ? $row->user->role->value : $row->user->role;
                    $isOwnerHRD = in_array(strtoupper((string)$roleVal), ['HRD', 'HR MANAGER']);
                    $statusLabel = $isOwnerHRD ? 'Disetujui' : 'Disetujui HRD';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
                } elseif ($st === \App\Models\LeaveRequest::STATUS_REJECTED) {
                    $badgeClass = 'lr-badge--error';
                    $statusLabel = 'Ditolak';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
                } elseif ($st === \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
                    $badgeClass = 'lr-badge--warning';
                    $statusLabel = 'Menunggu Atasan';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                } elseif ($st === \App\Models\LeaveRequest::PENDING_HR) {
                    $roleVal = $row->user->role instanceof \App\Enums\UserRole ? $row->user->role->value : $row->user->role;
                    $isHRStaff = in_array(strtoupper((string)$roleVal), ['HR STAFF']);
                    if ($isHRStaff) {
                        $badgeClass = 'lr-badge--warning';
                        $statusLabel = 'Menunggu Persetujuan';
                    } else {
                        $badgeClass = 'lr-badge--teal';
                        $statusLabel = 'Atasan Mengetahui';
                    }
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                }

                $typeLabel = \Illuminate\Support\Str::contains($row->type_label, 'Cuti Khusus') ? 'Cuti Khusus' : $row->type_label;

                $typeClass = 'lr-type--default';
                if (in_array($row->type->value, [\App\Enums\LeaveType::CUTI->value, \App\Enums\LeaveType::CUTI_KHUSUS->value])) {
                    $typeClass = 'lr-type--cuti';
                } elseif ($row->type->value === \App\Enums\LeaveType::SAKIT->value) {
                    $typeClass = 'lr-type--sakit';
                } elseif (in_array($row->type->value, [\App\Enums\LeaveType::IZIN_TELAT->value, \App\Enums\LeaveType::IZIN_PULANG_AWAL->value, \App\Enums\LeaveType::IZIN_TENGAH_KERJA->value, \App\Enums\LeaveType::IZIN->value])) {
                    $typeClass = 'lr-type--izin';
                }
            @endphp

            <a href="{{ route('leave-requests.show', $row) }}" class="lr-card">
                <div class="lr-card-top">
                    <span class="lr-type {{ $typeClass }}">
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
                    </span>

                    <span class="lr-badge {{ $badgeClass }}">
                        {!! $statusIcon !!}
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="lr-card-date">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ $row->start_date->translatedFormat('l, j F Y') }}</span>
                    @if($row->end_date && $row->end_date->ne($row->start_date))
                        <span class="lr-card-date-sep">—</span>
                        <span>{{ $row->end_date->translatedFormat('l, j F Y') }}</span>
                    @endif
                </div>

                @if($row->reason)
                    <div class="lr-card-note">
                        {{ Str::limit($row->reason, 100) }}
                    </div>
                @endif

                <div class="lr-card-footer">
                    <div class="lr-card-meta">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $row->created_at->translatedFormat('l, j F Y') }} · {{ $row->created_at->format('H:i') }}</span>
                    </div>
                    <div class="lr-card-action">
                        <span>Detail</span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        @empty
            <div class="lr-empty">
                <div class="lr-empty-icon">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="lr-empty-title">Belum Ada Pengajuan</h3>
                <p class="lr-empty-desc">Ajukan izin atau cuti baru dengan menekan tombol "Buat Pengajuan" di atas.</p>
            </div>
        @endforelse
    </div>

    @if($items->hasPages())
    <div class="lr-pagination">
        <x-pagination :items="$items" />
    </div>
    @endif

    <style>
        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .lr-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .lr-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
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
        /* CTA BAR                                    */
        /* ========================================== */
        .lr-cta-bar {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 16px;
        }
        .lr-btn-primary {
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
            flex-shrink: 0;
        }
        .lr-btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .lr-btn-primary svg {
            flex-shrink: 0;
        }

        /* ========================================== */
        /* FILTER CARD                                */
        /* ========================================== */
        .lr-filter {
            background: var(--white);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .lr-filter-body {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .lr-filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .lr-filter-label {
            font-size: 0.6875rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .lr-filter-input {
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
        .lr-filter-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .lr-filter-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .lr-btn-filter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 16px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            flex: 1;
        }
        .lr-btn-filter:hover {
            background: var(--primary-dark);
        }
        .lr-btn-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 14px;
            background: var(--white);
            color: var(--text-muted);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            flex: 1;
        }
        .lr-btn-reset:hover {
            background: var(--danger-light);
            border-color: #fecaca;
            color: var(--danger);
        }

        /* ========================================== */
        /* STATS ROW                                  */
        /* ========================================== */
        .lr-stats {
            display: flex;
            align-items: center;
            gap: 1px;
            background: var(--border-light);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 16px;
            border: 1px solid var(--border-light);
        }
        .lr-stat {
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
        .lr-stat-value {
            font-size: 1.0625rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1;
        }
        .lr-stat-value--success { color: var(--success); }
        .lr-stat-value--warning { color: var(--warning); }
        .lr-stat-value--error   { color: var(--error); }
        .lr-stat-label {
            font-size: 0.5625rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.02em;
            line-height: 1.2;
        }
        .lr-stat-divider {
            display: none;
        }

        /* ========================================== */
        /* REQUEST LIST & CARDS                       */
        /* ========================================== */
        .lr-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .lr-card {
            display: block;
            background: var(--white);
            border-radius: 16px;
            padding: 16px;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
        }
        .lr-card:hover {
            border-color: rgba(20, 93, 160, 0.35);
            box-shadow: 0 4px 12px rgba(20, 93, 160, 0.08);
            transform: translateY(-2px);
        }
        .lr-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        /* Type badge (rounded, not pill) */
        .lr-type {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .lr-type--default { background: #F8FAFC; color: var(--text-secondary); }
        .lr-type--cuti    { background: rgba(59, 130, 246, 0.1); color: var(--info); }
        .lr-type--sakit   { background: rgba(245, 158, 11, 0.1); color: #b45309; }
        .lr-type--izin    { background: rgba(20, 93, 160, 0.08); color: var(--primary); }

        /* Status badge (pill) */
        .lr-badge {
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
        .lr-badge--success { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .lr-badge--error   { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .lr-badge--warning { background: rgba(245, 158, 11, 0.1); color: #a16207; }
        .lr-badge--gray    { background: #F8FAFC; color: var(--text-secondary); }
        .lr-badge--teal    { background: rgba(20, 184, 166, 0.1); color: #0f766e; border: 1px solid rgba(20, 184, 166, 0.2); }

        .lr-card-date {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        .lr-card-date svg {
            color: var(--text-muted);
            flex-shrink: 0;
        }
        .lr-card-date-sep {
            color: var(--text-light);
            font-weight: 400;
        }
        .lr-card-note {
            font-size: 0.8125rem;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 12px;
        }
        .lr-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid var(--border-light);
            gap: 8px;
        }
        .lr-card-meta {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            color: var(--text-light);
        }
        .lr-card-meta svg {
            flex-shrink: 0;
        }
        .lr-card-action {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--primary);
            flex-shrink: 0;
        }
        .lr-card-action svg {
            transition: transform 0.2s ease;
        }
        .lr-card:hover .lr-card-action svg {
            transform: translateX(3px);
        }

        /* ========================================== */
        /* EMPTY STATE                                */
        /* ========================================== */
        .lr-empty {
            text-align: center;
            padding: 48px 24px;
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--border-light);
        }
        .lr-empty-icon {
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
        .lr-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin: 0 0 6px;
        }
        .lr-empty-desc {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin: 0 auto;
            max-width: 280px;
            line-height: 1.5;
        }

        /* ========================================== */
        /* PAGINATION                                 */
        /* ========================================== */
        .lr-pagination {
            margin-top: 24px;
        }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (min-width: 480px) {
            .lr-cta-bar {
                justify-content: flex-end;
            }
            .lr-btn-primary {
                justify-content: center;
            }
            .lr-stats {
                display: flex;
                align-items: center;
                background: var(--white);
                padding: 14px 20px;
            }
            .lr-stat {
                flex: 1;
                padding: 0;
                gap: 4px;
            }
            .lr-stat-value {
                font-size: 1.375rem;
            }
            .lr-stat-label {
                font-size: 0.6875rem;
                letter-spacing: 0.04em;
            }
            .lr-stat-divider {
                display: block;
                width: 1px;
                height: 36px;
                background: var(--border);
                flex-shrink: 0;
            }
            .lr-filter {
                padding: 18px 20px;
            }
            .lr-filter-body {
                flex-direction: row;
                align-items: flex-end;
                flex-wrap: wrap;
            }
            .lr-filter-group {
                flex: 1;
                min-width: 200px;
            }
            .lr-filter-actions {
                flex-shrink: 0;
            }
            .lr-btn-filter,
            .lr-btn-reset {
                flex: none;
            }
        }

        @media (min-width: 768px) {
            .lr-card {
                padding: 18px 20px;
            }
            .lr-filter {
                padding: 20px;
            }
        }

        @media (min-width: 1024px) {
            .lr-list {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            .lr-empty {
                grid-column: 1 / -1;
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
