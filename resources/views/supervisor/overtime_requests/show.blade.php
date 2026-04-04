<x-app title="Detail Approval Lembur">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --primary-light: #dbeafe;
            --success: #059669;
            --success-bg: #dcfce7;
            --warning: #d97706;
            --warning-bg: #fefce8;
            --danger: #dc2626;
            --danger-bg: #fee2e2;
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

        .page-wrapper { max-width: 680px; margin: 0 auto; padding: 16px; padding-bottom: 100px; }

        /* Alerts */
        .alert-success { background: var(--success-bg); color: #065f46; padding: 12px 16px; border-radius: 10px; border: 1px solid #a7f3d0; margin-bottom: 16px; font-size: 13.5px; }
        .alert-error { background: var(--danger-bg); color: #991b1b; padding: 12px 16px; border-radius: 10px; border: 1px solid #fecaca; margin-bottom: 16px; font-size: 13.5px; }

        /* Card */
        .card { background: #fff; border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); border: 1px solid var(--gray-200); overflow: hidden; }

        /* Header Card */
        .header-card { padding: 20px; }
        .header-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 16px; }
        .profile-main { display: flex; gap: 14px; align-items: center; }
        .profile-avatar { width: 52px; height: 52px; background: var(--primary-light); color: var(--primary-dark); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; flex-shrink: 0; }
        .profile-info {}
        .profile-name { font-size: 17px; font-weight: 700; color: var(--gray-900); margin-bottom: 4px; }
        .profile-meta { font-size: 12.5px; color: var(--gray-500); display: flex; flex-wrap: wrap; align-items: center; gap: 5px; }
        .profile-meta .dot { color: var(--gray-300); }
        .chip-role { background: var(--gray-100); color: var(--gray-600); padding: 2px 8px; border-radius: 6px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; font-weight: 600; }
        .chip-status { display: inline-block; padding: 6px 14px; border-radius: 30px; font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; white-space: nowrap; }
        .status-pending { background: var(--warning-bg); color: #a16207; }
        .status-supervisor { background: var(--primary-light); color: var(--primary-dark); }
        .status-approved { background: var(--success-bg); color: #166534; }
        .status-rejected { background: var(--danger-bg); color: #991b1b; }
        .divider { height: 1px; background: var(--gray-100); }

        /* Info Cards */
        .info-card { padding: 16px 20px; border-bottom: 1px solid var(--gray-100); }
        .info-card:last-child { border-bottom: none; }
        .info-card-title { font-size: 11px; font-weight: 700; color: var(--gray-400); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 12px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .info-item {}
        .info-label { font-size: 11.5px; color: var(--gray-500); margin-bottom: 3px; font-weight: 500; }
        .info-value { font-size: 14px; color: var(--gray-900); font-weight: 600; }
        .info-value.full { grid-column: 1 / -1; }
        .box-reason { background: var(--gray-50); padding: 14px; border-radius: 10px; border: 1px solid var(--gray-200); color: var(--gray-700); font-size: 13.5px; line-height: 1.6; }
        .badge-duration { display: inline-block; background: var(--primary-light); color: var(--primary-dark); padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; }

        /* Approval Info */
        .approval-row { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid var(--gray-100); }
        .approval-row:last-child { border-bottom: none; padding-bottom: 0; }
        .approval-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .approval-icon.green { background: var(--success-bg); color: var(--success); }
        .approval-icon.blue { background: var(--primary-light); color: var(--primary); }
        .approval-text {}
        .approval-name { font-size: 13.5px; font-weight: 600; color: var(--gray-900); }
        .approval-time { font-size: 11.5px; color: var(--gray-400); margin-top: 1px; }

        /* Rejection Note */
        .rejection-note { background: var(--warning-bg); border: 1px solid #fde68a; border-radius: 10px; padding: 14px; margin-top: 12px; }
        .rejection-label { font-size: 11px; font-weight: 700; color: #92400e; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
        .rejection-content { font-size: 13.5px; color: #b45309; line-height: 1.5; }

        /* Back link */
        .back-link { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 10px; border: 1px solid var(--gray-200); background: #fff; color: var(--gray-700); font-size: 13px; font-weight: 500; text-decoration: none; transition: all 0.2s; margin-bottom: 16px; }
        .back-link:hover { background: var(--gray-50); border-color: var(--gray-300); }
        .back-link svg { flex-shrink: 0; }

        /* Processed Info */
        .processed-info { font-size: 13px; color: var(--gray-500); background: var(--gray-50); padding: 10px 16px; border-radius: 10px; border: 1px solid var(--gray-200); text-align: center; }

        /* Action Bar */
        .action-bar { position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-top: 1px solid var(--gray-200); padding: 12px 16px; padding-bottom: max(12px, env(safe-area-inset-bottom)); z-index: 50; box-shadow: 0 -2px 10px rgba(0,0,0,0.06); }
        .action-bar-inner { max-width: 680px; margin: 0 auto; display: flex; gap: 10px; }
        .btn-back-bar { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 12px 16px; border-radius: 12px; border: 1.5px solid var(--gray-200); background: #fff; color: var(--gray-700); font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s; flex: 1; }
        .btn-back-bar:hover { background: var(--gray-50); }
        .btn-approve-bar { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 12px 16px; border-radius: 12px; background: var(--primary); color: #fff; font-size: 13px; font-weight: 600; border: none; cursor: pointer; transition: background 0.2s; flex: 1; }
        .btn-approve-bar:hover { background: var(--primary-dark); }
        .btn-reject-bar { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 12px 16px; border-radius: 12px; background: #fff; color: var(--danger); font-size: 13px; font-weight: 600; border: 1.5px solid var(--danger-bg); cursor: pointer; transition: all 0.2s; flex: 1; }
        .btn-reject-bar:hover { background: var(--danger-bg); }
        .action-bar svg { flex-shrink: 0; }

        /* Form Control (modal) */
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid var(--gray-300); border-radius: 8px; font-size: 14px; font-family: inherit; outline: none; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }

        /* Bottom spacer */
        .bottom-spacer { height: 80px; }

        @media (max-width: 480px) {
            .info-grid { grid-template-columns: 1fr; }
            .header-top { flex-direction: column; gap: 12px; }
            .action-bar-inner { flex-direction: column; }
        }
        @media (min-width: 681px) {
            .page-wrapper { padding: 24px 16px 100px; }
        }
    </style>

    <div class="page-wrapper">
        <a href="{{ route('supervisor.overtime-requests.index') }}" class="back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
            ← Kembali
        </a>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        <div class="card">
            {{-- Header Card --}}
            <div class="header-card">
                <div class="header-top">
                    <div class="profile-main">
                        <div class="profile-avatar">{{ substr($overtimeRequest->user->name, 0, 1) }}</div>
                        <div class="profile-info">
                            <div class="profile-name">{{ $overtimeRequest->user->name }}</div>
                            <div class="profile-meta">
                                <span class="chip-role">{{ $overtimeRequest->user->role instanceof \App\Enums\UserRole ? $overtimeRequest->user->role->label() : $overtimeRequest->user->role }}</span>
                                <span class="dot">·</span>
                                <span>{{ $overtimeRequest->user->division->name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    @php
                        $statusBadge = 'chip-status status-rejected';
                        $statusLabel = $overtimeRequest->status;
                        if ($overtimeRequest->status == \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR) { $statusBadge = 'chip-status status-pending'; $statusLabel = 'Menunggu Approval'; }
                        elseif ($overtimeRequest->status == \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR) { $statusBadge = 'chip-status status-supervisor'; $statusLabel = 'Disetujui (Menunggu HR)'; }
                        elseif ($overtimeRequest->status == \App\Models\OvertimeRequest::STATUS_APPROVED_HRD) { $statusBadge = 'chip-status status-approved'; $statusLabel = 'Disetujui Final'; }
                        elseif ($overtimeRequest->status == \App\Models\OvertimeRequest::STATUS_REJECTED) { $statusBadge = 'chip-status status-rejected'; $statusLabel = 'Ditolak'; }
                    @endphp
                    <span class="{{ $statusBadge }}">{{ $statusLabel }}</span>
                </div>
                <div class="profile-meta" style="margin-top:0;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Diajukan {{ $overtimeRequest->created_at->format('d M Y, H:i') }}
                </div>
            </div>

            <div class="divider"></div>

            {{-- Info Card: Lembur --}}
            <div class="info-card">
                <div class="info-card-title">Informasi Lembur</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Tanggal</div>
                        <div class="info-value">{{ $overtimeRequest->overtime_date->format('d M Y') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Durasi</div>
                        <div class="info-value"><span class="badge-duration">{{ $overtimeRequest->duration_human }}</span></div>
                    </div>
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <div class="info-label">Jam</div>
                        <div class="info-value">{{ $overtimeRequest->start_time->format('H:i') }} - {{ $overtimeRequest->end_time->format('H:i') }}</div>
                    </div>
                </div>
            </div>

            {{-- Info Card: Keterangan --}}
            <div class="info-card">
                <div class="info-card-title">Keterangan Pekerjaan</div>
                <div class="box-reason">{{ $overtimeRequest->description }}</div>
            </div>

            {{-- Info Card: Approval --}}
            @if($overtimeRequest->supervisorApprover || $overtimeRequest->hrdApprover)
            <div class="info-card">
                <div class="info-card-title">Riwayat Persetujuan</div>
                @if($overtimeRequest->supervisorApprover)
                <div class="approval-row">
                    <div class="approval-icon green">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div class="approval-text">
                        <div class="approval-name">{{ $overtimeRequest->supervisorApprover->name }}</div>
                        <div class="approval-time">Disetujui {{ $overtimeRequest->approved_by_supervisor_at ? $overtimeRequest->approved_by_supervisor_at->format('d M Y, H:i') : '' }}</div>
                    </div>
                </div>
                @endif
                @if($overtimeRequest->hrdApprover)
                <div class="approval-row">
                    <div class="approval-icon blue">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div class="approval-text">
                        <div class="approval-name">{{ $overtimeRequest->hrdApprover->name }}</div>
                        <div class="approval-time">Disetujui HRD {{ $overtimeRequest->approved_by_hrd_at ? $overtimeRequest->approved_by_hrd_at->format('d M Y, H:i') : '' }}</div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Rejection Note --}}
            @if($overtimeRequest->status === \App\Models\OvertimeRequest::STATUS_REJECTED && $overtimeRequest->rejection_note)
            <div class="info-card">
                <div class="rejection-note">
                    <div class="rejection-label">Alasan Penolakan</div>
                    <div class="rejection-content">{{ $overtimeRequest->rejection_note }}</div>
                </div>
            </div>
            @endif

            <div class="bottom-spacer"></div>
        </div>
    </div>

    {{-- Fixed Action Bar --}}
    @if($overtimeRequest->status == \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR)
    <div class="action-bar">
        <div class="action-bar-inner">
            <button type="button" data-modal-target="modal-reject" class="btn-reject-bar">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Tolak
            </button>
            <button type="button" data-modal-target="modal-approve" class="btn-approve-bar">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Setujui
            </button>
        </div>
    </div>
    @else
    <div class="action-bar">
        <div class="action-bar-inner" style="justify-content: center;">
            <a href="{{ route('supervisor.overtime-requests.index') }}" class="btn-back-bar" style="max-width: 320px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Daftar
            </a>
        </div>
    </div>
    @endif

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
        <p style="margin-bottom:14px; color:var(--gray-700); font-size:14px;">
            Yakin menolak pengajuan lembur <strong>{{ $overtimeRequest->user->name }}</strong>?
        </p>
        <div>
            <label style="display:block; font-size:13px; font-weight:600; color:var(--gray-700); margin-bottom:6px;" for="rejection_note">
                Alasan Penolakan <span style="color:var(--danger);">*</span>
            </label>
            <textarea name="rejection_note" id="rejection_note" rows="3" class="form-control" required placeholder="Jelaskan alasan penolakan..."></textarea>
        </div>
    </x-modal>

    {{-- MODAL APPROVE --}}
    <x-modal
        id="modal-approve"
        title="Terima Pengajuan Ini?"
        type="confirm"
        variant="success"
        confirmLabel="Ya, Terima"
        cancelLabel="Batal"
        :confirmFormAction="route('supervisor.overtime-requests.approve', $overtimeRequest->id)"
        confirmFormMethod="POST">
        <p style="margin:0; color:var(--gray-700); font-size:14px;">
            Setujui pengajuan lembur ini?
        </p>
    </x-modal>
</x-app>
