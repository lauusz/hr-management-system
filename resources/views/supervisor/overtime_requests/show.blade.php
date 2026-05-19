<x-app title="Detail Approval Lembur">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Detail Approval Lembur</h1>
                <p class="section-subtitle">Informasi lengkap pengajuan lembur karyawan</p>
            </div>
        </div>
    </x-slot>

    @php
        $st = $overtimeRequest->status;
        $statusClass = 'sov-badge--gray';
        $statusLabel = $overtimeRequest->status_label;
        $statusIcon = '';

        if (in_array($st, [\App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR, \App\Models\OvertimeRequest::STATUS_APPROVED_HRD])) {
            $statusClass = 'sov-badge--success';
            $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
        } elseif ($st === \App\Models\OvertimeRequest::STATUS_REJECTED) {
            $statusClass = 'sov-badge--error';
            $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
        } elseif ($st === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
            $statusClass = 'sov-badge--warning';
            $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        } elseif ($st === \App\Models\OvertimeRequest::STATUS_CANCELLED) {
            $statusClass = 'sov-badge--gray';
            $statusIcon = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
        }

        $isPending = $st === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR;
    @endphp

    <div class="sov-detail-page">

        {{-- Back Button --}}
        <a href="{{ route('supervisor.overtime-requests.index') }}" class="back-btn" aria-label="Kembali ke daftar">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="sov-alert sov-alert--success">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="sov-alert sov-alert--error">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="sov-alert sov-alert--error">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ $errors->first() }}
            </div>
        @endif

        <div class="sov-detail-body">

            {{-- Card: Employee Header --}}
            <div class="sov-card">
                <div class="sov-card-header">
                    <div class="sov-avatar sov-avatar--lg">{{ substr($overtimeRequest->user->name, 0, 1) }}</div>
                    <div class="sov-card-header-info">
                        <h2 class="sov-employee-name">{{ $overtimeRequest->user->name }}</h2>
                        <div class="sov-employee-meta">
                            <span class="sov-role-chip">{{ $overtimeRequest->user->role instanceof \App\Enums\UserRole ? $overtimeRequest->user->role->label() : $overtimeRequest->user->role }}</span>
                            <span class="sov-meta-dot">•</span>
                            <span>{{ $overtimeRequest->user->position->name ?? '-' }}</span>
                            <span class="sov-meta-dot">•</span>
                            <span>{{ $overtimeRequest->user->division->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                <div class="sov-card-header-footer">
                    <span class="sov-badge {{ $statusClass }}">
                        {!! $statusIcon !!}
                        {{ $statusLabel }}
                    </span>
                    <span class="sov-submit-time">Diajukan {{ $overtimeRequest->created_at->translatedFormat('j F Y, H:i') }}</span>
                </div>
            </div>

            {{-- Card: Informasi Lembur --}}
            <div class="sov-card">
                <div class="sov-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Informasi Lembur
                </div>

                <div class="sov-detail-row">
                    <span class="sov-detail-label">Tanggal</span>
                    <div class="sov-date-display">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ $overtimeRequest->overtime_date->translatedFormat('l, j F Y') }}</span>
                    </div>
                </div>

                <div class="sov-detail-row">
                    <span class="sov-detail-label">Jam</span>
                    <span class="sov-time-display">{{ $overtimeRequest->start_time->format('H:i') }} - {{ $overtimeRequest->end_time->format('H:i') }}</span>
                </div>

                <div class="sov-detail-row">
                    <span class="sov-detail-label">Durasi</span>
                    <span class="sov-duration-badge">{{ $overtimeRequest->duration_human }}</span>
                </div>
            </div>

            {{-- Card: Keterangan Pekerjaan --}}
            <div class="sov-card">
                <div class="sov-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Keterangan Pekerjaan
                </div>
                <div class="sov-reason-box">
                    {{ $overtimeRequest->description }}
                </div>
            </div>

            {{-- Card: Riwayat Persetujuan --}}
            @if($overtimeRequest->supervisorApprover)
            <div class="sov-card">
                <div class="sov-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Riwayat Persetujuan
                </div>

                <div class="sov-approval-row">
                    <div class="sov-approval-icon sov-approval-icon--green">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="sov-approval-info">
                        <span class="sov-approval-name">{{ $overtimeRequest->supervisorApprover->name }}</span>
                        <span class="sov-approval-time">
                            Disetujui {{ $overtimeRequest->approved_by_supervisor_at ? $overtimeRequest->approved_by_supervisor_at->translatedFormat('j F Y, H:i') : '' }}
                        </span>
                    </div>
                </div>
            </div>
            @endif

            {{-- Card: Alasan Penolakan --}}
            @if($overtimeRequest->status === \App\Models\OvertimeRequest::STATUS_REJECTED && $overtimeRequest->rejection_note)
            <div class="sov-card">
                <div class="sov-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Alasan Penolakan
                </div>
                <div class="sov-rejection-box">
                    {{ $overtimeRequest->rejection_note }}
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- Fixed Action Bar --}}
    <div class="sov-action-bar">
        <div class="sov-action-dock">
            <div class="sov-action-main">
                @if($isPending)
                    {{-- Primary: Approve --}}
                    <button type="button" data-modal-target="modal-approve" class="sov-action-btn sov-action-btn--primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Setujui
                    </button>

                    {{-- Secondary: Reject --}}
                    <div class="sov-action-secondary">
                        <button type="button" data-modal-target="modal-reject" class="sov-action-btn sov-action-btn--outline-danger">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Tolak
                        </button>
                    </div>
                @else
                    <div class="sov-action-secondary" style="justify-content: center;">
                        <a href="{{ route('supervisor.overtime-requests.index') }}" class="sov-action-btn sov-action-btn--secondary">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Kembali ke Daftar
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- MODAL APPROVE --}}
    <x-modal
        id="modal-approve"
        title="Setujui Pengajuan Ini?"
        type="confirm"
        variant="success"
        confirmLabel="Ya, Setujui"
        cancelLabel="Batal"
        :confirmFormAction="route('supervisor.overtime-requests.approve', $overtimeRequest->id)"
        confirmFormMethod="POST">
        <p style="margin:0; color:var(--text-primary, #374151); font-size:14px;">
            Setujui pengajuan lembur <strong>{{ $overtimeRequest->user->name }}</strong>?
        </p>
    </x-modal>

    {{-- MODAL REJECT --}}
    <x-modal
        id="modal-reject"
        title="Tolak Pengajuan Ini?"
        type="confirm"
        variant="danger"
        confirmLabel="Ya, Tolak"
        cancelLabel="Batal"
        :confirmFormAction="route('supervisor.overtime-requests.reject', $overtimeRequest->id)"
        confirmFormMethod="POST">
        <p style="margin-bottom:14px; color:var(--text-primary, #374151); font-size:14px;">
            Yakin menolak pengajuan lembur <strong>{{ $overtimeRequest->user->name }}</strong>?
        </p>
        <div>
            <label style="display:block; font-size:13px; font-weight:600; color:var(--text-primary, #374151); margin-bottom:6px;" for="rejection_note">
                Alasan Penolakan <span style="color:var(--error, #EF4444);">*</span>
            </label>
            <textarea name="rejection_note" id="rejection_note" rows="3" class="sov-form-control" required placeholder="Jelaskan alasan penolakan..."></textarea>
        </div>
    </x-modal>

    <style>
        /* ========================================== */
        /* PAGE LAYOUT                                */
        /* ========================================== */
        .sov-detail-page {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding-bottom: 160px;
        }
        @media (min-width: 640px) {
            .sov-detail-page { padding-bottom: 120px; }
        }
        @media (min-width: 768px) {
            .sov-detail-page { padding-bottom: 100px; }
        }

        .sov-detail-body {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .sov-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
        }
        .sov-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }
        .sov-alert--error {
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
        /* BACK BUTTON                                */
        /* ========================================== */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 12px 0 10px;
            background: var(--white, #fff);
            border: 1px solid var(--border, #E5E7EB);
            border-radius: 10px;
            color: var(--text-muted, #6B7280);
            text-decoration: none;
            transition: all 0.15s ease;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            align-self: flex-start;
        }
        .back-btn:hover {
            border-color: var(--primary, #145DA0);
            color: var(--primary, #145DA0);
            background: var(--gray-50, #F5F7FA);
        }
        .back-btn:hover svg {
            transform: translateX(-2px);
        }
        .back-btn svg {
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }
        .back-btn-text {
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
        }

        /* ========================================== */
        /* CARDS                                      */
        /* ========================================== */
        .sov-card {
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .sov-section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
        }
        .sov-section-title svg {
            color: var(--primary, #145DA0);
            flex-shrink: 0;
        }

        /* ========================================== */
        /* CARD HEADER (Employee)                     */
        /* ========================================== */
        .sov-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        .sov-avatar {
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
        .sov-avatar--lg {
            width: 52px;
            height: 52px;
            font-size: 1.25rem;
            border-radius: 12px;
        }
        .sov-card-header-info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .sov-employee-name {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sov-employee-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px;
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
        }
        .sov-role-chip {
            background: var(--gray-50, #F5F7FA);
            color: var(--text-muted, #6B7280);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .sov-meta-dot {
            color: var(--border-light, #E5E7EB);
        }

        .sov-card-header-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 14px;
            border-top: 1px solid var(--border-light, #E5E7EB);
            gap: 8px;
            flex-wrap: wrap;
        }

        /* ========================================== */
        /* BADGES                                     */
        /* ========================================== */
        .sov-badge {
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
        .sov-badge--success { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .sov-badge--error   { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .sov-badge--warning { background: rgba(245, 158, 11, 0.1); color: #a16207; }
        .sov-badge--gray    { background: #F8FAFC; color: var(--text-secondary, #374151); }

        .sov-submit-time {
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
        }

        /* ========================================== */
        /* DETAIL ROWS                                */
        /* ========================================== */
        .sov-detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
            gap: 12px;
        }
        .sov-detail-row:last-child { border-bottom: none; padding-bottom: 0; }
        .sov-detail-row:first-child { padding-top: 0; }
        .sov-detail-label {
            font-size: 0.6875rem;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            flex-shrink: 0;
        }

        /* ========================================== */
        /* DATE & TIME                                */
        /* ========================================== */
        .sov-date-display {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
        }
        .sov-date-display svg {
            color: var(--text-muted, #6B7280);
            flex-shrink: 0;
        }
        .sov-time-display {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
        }
        .sov-duration-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            background: rgba(34, 197, 94, 0.1);
            color: #15803d;
        }

        /* ========================================== */
        /* REASON BOX                                 */
        /* ========================================== */
        .sov-reason-box {
            background: var(--gray-50, #F5F7FA);
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            padding: 14px;
            font-size: 0.875rem;
            color: var(--text-primary, #111827);
            line-height: 1.7;
            width: 100%;
            box-sizing: border-box;
        }

        /* ========================================== */
        /* REJECTION BOX                              */
        /* ========================================== */
        .sov-rejection-box {
            background: rgba(239, 68, 68, 0.06);
            border: 1px solid rgba(239, 68, 68, 0.18);
            border-radius: 10px;
            padding: 14px;
            font-size: 0.875rem;
            color: #b91c1c;
            line-height: 1.7;
            width: 100%;
            box-sizing: border-box;
        }

        /* ========================================== */
        /* APPROVAL ROW                               */
        /* ========================================== */
        .sov-approval-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sov-approval-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sov-approval-icon--green {
            background: rgba(34, 197, 94, 0.1);
            color: var(--success, #22C55E);
        }
        .sov-approval-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .sov-approval-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
        }
        .sov-approval-time {
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
        }

        /* ========================================== */
        /* FORM CONTROL (modal textarea)              */
        /* ========================================== */
        .sov-form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border, #E5E7EB);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            color: var(--text-primary, #111827);
            background: var(--white, #FFFFFF);
            outline: none;
            resize: vertical;
            transition: all 0.2s ease;
        }
        .sov-form-control:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }

        /* ========================================== */
        /* FIXED ACTION BAR                           */
        /* ========================================== */
        .sov-action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
            background: transparent;
            border: none;
            box-shadow: none;
            padding: 0;
            pointer-events: none;
            transition: left 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        right 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        bottom 0.35s ease;
        }
        .sov-action-dock {
            background: var(--white, #FFFFFF);
            border-top: 1px solid var(--border-light, #E5E7EB);
            border-radius: 18px 18px 0 0;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.06);
            padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
            pointer-events: auto;
        }
        .sov-action-main {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        .sov-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
            white-space: nowrap;
            font-family: inherit;
            min-height: 44px;
        }
        .sov-action-btn svg { flex-shrink: 0; }

        /* Primary: full width, gradient */
        .sov-action-btn--primary {
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #fff;
            width: 100%;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
            font-size: 0.875rem;
            padding: 14px 16px;
        }
        .sov-action-btn--primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }

        /* Secondary row */
        .sov-action-secondary {
            display: flex;
            gap: 8px;
            width: 100%;
        }
        .sov-action-secondary .sov-action-btn {
            flex: 1;
            padding: 10px 8px;
            font-size: 0.75rem;
        }

        .sov-action-btn--secondary {
            background: var(--gray-50, #F5F7FA);
            color: var(--text-secondary, #374151);
            border: 1.5px solid var(--border-light, #E5E7EB);
        }
        .sov-action-btn--secondary:hover {
            background: var(--white, #FFFFFF);
            border-color: var(--gray-300, #D1D5DB);
        }

        .sov-action-btn--outline-danger {
            background: var(--white, #FFFFFF);
            color: var(--error, #EF4444);
            border: 1.5px solid rgba(239, 68, 68, 0.3);
        }
        .sov-action-btn--outline-danger:hover {
            background: rgba(239, 68, 68, 0.06);
            border-color: rgba(239, 68, 68, 0.5);
        }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (min-width: 480px) {
            .sov-action-secondary .sov-action-btn {
                font-size: 0.8125rem;
                padding: 12px 16px;
            }
        }

        @media (min-width: 640px) {
            .sov-action-main {
                flex-direction: row;
                align-items: center;
                flex-wrap: wrap;
            }
            .sov-action-btn--primary {
                width: auto;
                flex: 1;
                order: 2;
            }
            .sov-action-secondary {
                width: auto;
                flex: 1;
                order: 1;
            }
            .sov-action-secondary .sov-action-btn {
                flex: none;
            }
        }

        @media (min-width: 768px) {
            .sov-action-main {
                flex-wrap: nowrap;
            }
            .sov-action-btn--primary {
                flex: none;
                padding: 12px 28px;
            }
            .sov-action-secondary {
                flex: none;
            }
        }

        @media (min-width: 1025px) {
            .sov-action-bar {
                left: calc(var(--sidebar-width) + 12px);
                right: 0;
                bottom: 16px;
                padding: 0 32px;
            }
            .app.sidebar-collapsed .sov-action-bar {
                left: 0;
            }
            .sov-action-dock {
                border: 1px solid var(--border-light, #E5E7EB);
                border-radius: 18px;
                box-shadow: 0 -4px 20px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
                padding: 12px 20px calc(12px + env(safe-area-inset-bottom));
            }
        }
    </style>
</x-app>
