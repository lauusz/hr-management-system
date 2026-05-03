<x-app title="Rekap Izin Bawahan">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Rekap Izin Bawahan</h1>
                <p class="section-subtitle">Riwayat pengajuan izin dan cuti staf di bawah supervisi Anda</p>
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

    {{-- Filter Card --}}
    <div class="apv-filter">
        <form method="GET" action="{{ route('supervisor.leave.master') }}">
            <div class="apv-filter-body">
                <div class="apv-filter-group">
                    <label class="apv-filter-label">Tanggal Pengajuan</label>
                    <input type="text" id="submitted_range" name="submitted_range" value="{{ $submittedRange ?? '' }}" placeholder="Pilih rentang tanggal" class="apv-filter-input" autocomplete="off">
                </div>
                <div class="apv-filter-group">
                    <label class="apv-filter-label">Jenis Pengajuan</label>
                    <select name="type" class="apv-filter-input">
                        <option value="">Semua Jenis</option>
                        @foreach($typeOptions as $case)
                            @php
                                $val = $case->value;
                                $lbl = ($val === \App\Enums\LeaveType::CUTI_KHUSUS->value) ? 'Cuti Khusus' : $case->label();
                            @endphp
                            <option value="{{ $val }}" @selected(($typeFilter ?? '') === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="apv-filter-group">
                    <label class="apv-filter-label">Status</label>
                    <select name="status" class="apv-filter-input">
                        <option value="">Semua Status</option>
                        @foreach($statusOptions as $opt)
                            @php
                                $statusLabels = [
                                    \App\Models\LeaveRequest::PENDING_SUPERVISOR => 'Perlu Diketahui',
                                    \App\Models\LeaveRequest::PENDING_HR => 'Atasan Mengetahui',
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
                <div class="apv-filter-group">
                    <label class="apv-filter-label">Cari Karyawan</label>
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Ketik nama bawahan..." class="apv-filter-input">
                </div>
                <div class="apv-filter-actions">
                    <button type="submit" class="apv-btn-filter">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter
                    </button>
                    @if(($q ?? null) || ($typeFilter ?? null) || ($status ?? null) || ($submittedRange ?? null))
                        <a href="{{ route('supervisor.leave.master') }}" class="apv-btn-reset">
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

    {{-- Leave List --}}
    <div class="apv-list">
        @forelse($items as $row)
            @php
                $st = $row->status;
                $statusClass = 'apv-badge--gray';
                $statusLabel = $row->status_label ?? $st;
                $statusIcon = '';

                if ($st === \App\Models\LeaveRequest::STATUS_APPROVED) {
                    $statusClass = 'apv-badge--success';
                    $statusLabel = 'Disetujui';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
                } elseif ($st === \App\Models\LeaveRequest::STATUS_REJECTED) {
                    $statusClass = 'apv-badge--error';
                    $statusLabel = 'Ditolak';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
                } elseif ($st === \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
                    $statusClass = 'apv-badge--warning';
                    $statusLabel = 'Perlu Diketahui';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                } elseif ($st === \App\Models\LeaveRequest::PENDING_HR) {
                    $statusClass = 'apv-badge--teal';
                    $statusLabel = 'Atasan Mengetahui';
                    $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                } elseif (in_array($st, ['BATAL', 'CANCEL_REQ'])) {
                    $statusClass = 'apv-badge--gray';
                    $statusLabel = $st === 'BATAL' ? 'Dibatalkan' : 'Pengajuan Batal';
                }

                $typeClass = 'apv-type--default';
                $typeValue = $row->type instanceof \App\Enums\LeaveType ? $row->type->value : (string) $row->type;
                if (in_array($typeValue, [\App\Enums\LeaveType::CUTI->value, \App\Enums\LeaveType::CUTI_KHUSUS->value])) {
                    $typeClass = 'apv-type--cuti';
                } elseif ($typeValue === \App\Enums\LeaveType::SAKIT->value) {
                    $typeClass = 'apv-type--sakit';
                } elseif (in_array($typeValue, [\App\Enums\LeaveType::IZIN_TELAT->value, \App\Enums\LeaveType::IZIN_PULANG_AWAL->value, \App\Enums\LeaveType::IZIN_TENGAH_KERJA->value, \App\Enums\LeaveType::IZIN->value])) {
                    $typeClass = 'apv-type--izin';
                } elseif ($typeValue === \App\Enums\LeaveType::DINAS_LUAR->value) {
                    $typeClass = 'apv-type--dinas';
                }

                $typeLabel = \Illuminate\Support\Str::contains($row->type_label ?? $row->type, 'Cuti Khusus') ? 'Cuti Khusus' : ($row->type_label ?? $row->type);

                // Card accent border based on status
                $cardAccent = '';
                if ($st === \App\Models\LeaveRequest::PENDING_SUPERVISOR) $cardAccent = 'apv-card--pending';
                elseif ($st === \App\Models\LeaveRequest::STATUS_APPROVED) $cardAccent = 'apv-card--approved';
                elseif ($st === \App\Models\LeaveRequest::STATUS_REJECTED) $cardAccent = 'apv-card--rejected';
                elseif ($st === \App\Models\LeaveRequest::PENDING_HR) $cardAccent = 'apv-card--teal';
            @endphp

            <a href="{{ route('approval.show', $row) }}" class="apv-card {{ $cardAccent }}">
                <div class="apv-card-top">
                    <span class="apv-type {{ $typeClass }}">
                        @if($typeValue === \App\Enums\LeaveType::CUTI->value)
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @elseif($typeValue === \App\Enums\LeaveType::CUTI_KHUSUS->value)
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        @elseif($typeValue === \App\Enums\LeaveType::SAKIT->value)
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
                    <div class="apv-avatar">{{ substr($row->user->name, 0, 1) }}</div>
                    <div class="apv-employee-info">
                        <span class="apv-employee-name">{{ $row->user->name }}</span>
                        <span class="apv-employee-detail">{{ $row->user->position->name ?? '-' }} — {{ $row->user->division->name ?? '-' }}</span>
                    </div>
                </div>

                <div class="apv-card-date">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ $row->start_date->translatedFormat('l, j F Y') }}</span>
                    @if($row->end_date && $row->end_date->ne($row->start_date))
                        <span class="apv-card-date-sep">—</span>
                        <span>{{ $row->end_date->translatedFormat('l, j F Y') }}</span>
                    @endif
                </div>

                <div class="apv-card-footer">
                    <div class="apv-card-meta">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $row->created_at->translatedFormat('l, j F Y') }} · {{ $row->created_at->format('H:i') }}</span>
                    </div>
                    <div class="apv-card-action">
                        <span>Detail</span>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="apv-empty-title">Belum Ada Pengajuan</h3>
                <p class="apv-empty-desc">Belum ada riwayat pengajuan dari bawahan Anda.</p>
            </div>
        @endforelse
    </div>

    @if($items->hasPages())
    <div class="apv-pagination">
        <x-pagination :items="$items" :preserve-query="true" />
    </div>
    @endif

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
        /* FILTER CARD                                */
        /* ========================================== */
        .apv-filter {
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .apv-filter-body {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }
        .apv-filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .apv-filter-label {
            font-size: 0.6875rem;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .apv-filter-input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            font-size: 13px;
            color: var(--text-primary, #111827);
            background: var(--white, #FFFFFF);
            transition: all 0.2s ease;
            outline: none;
            font-family: inherit;
            -webkit-appearance: none;
            appearance: none;
        }
        .apv-filter-input:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        select.apv-filter-input {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236B7280' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 40px;
        }
        .apv-filter-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .apv-btn-filter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 16px;
            background: var(--primary, #145DA0);
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
        .apv-btn-filter:hover {
            background: var(--primary-dark, #0A3D62);
        }
        .apv-btn-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 14px;
            background: var(--white, #FFFFFF);
            color: var(--text-muted, #6B7280);
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            flex: 1;
        }
        .apv-btn-reset:hover {
            background: rgba(239, 68, 68, 0.04);
            border-color: rgba(239, 68, 68, 0.2);
            color: var(--error, #EF4444);
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
        .apv-card--approved { border-left: 4px solid var(--success, #22C55E); }
        .apv-card--rejected { border-left: 4px solid var(--error, #EF4444); }
        .apv-card--teal     { border-left: 4px solid #14B8A6; }

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
        .apv-badge--success { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .apv-badge--error   { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .apv-badge--warning { background: rgba(245, 158, 11, 0.1); color: #a16207; }
        .apv-badge--teal    { background: rgba(20, 184, 166, 0.1); color: #0f766e; border: 1px solid rgba(20, 184, 166, 0.2); }
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
            margin-bottom: 12px;
        }
        .apv-card-date svg {
            color: var(--text-muted, #6B7280);
            flex-shrink: 0;
        }
        .apv-card-date-sep {
            color: var(--text-muted, #6B7280);
            font-weight: 400;
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
        @media (min-width: 480px) {
            .apv-filter-body {
                grid-template-columns: 1fr 1fr;
            }
            .apv-filter-group:last-child:nth-child(odd) {
                grid-column: 1 / -1;
            }
            .apv-filter-actions {
                grid-column: 1 / -1;
            }
            .apv-card {
                padding: 18px 20px;
            }
        }

        @media (min-width: 768px) {
            .apv-filter {
                padding: 20px;
            }
            .apv-filter-body {
                grid-template-columns: repeat(3, 1fr);
            }
            .apv-filter-group:last-child:nth-child(odd) {
                grid-column: auto;
            }
            .apv-filter-actions {
                grid-column: 1 / -1;
                justify-content: flex-end;
            }
            .apv-btn-filter,
            .apv-btn-reset {
                flex: none;
                padding: 10px 20px;
            }
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

</x-app>
