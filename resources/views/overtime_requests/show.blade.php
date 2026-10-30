<x-app title="Detail Pengajuan Lembur">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Detail Pengajuan Lembur</h1>
                <p class="section-subtitle">Informasi lengkap pengajuan lembur Anda</p>
            </div>
        </div>
    </x-slot>

    @php
        $mapping = $overtime->user?->overtimeApproverAssignment;
        $status = $overtime->status;
        $badgeClass = 'lrs-status--neutral';
        $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>';
        $statusSub = null;

        if (in_array($status, [\App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR, \App\Models\OvertimeRequest::STATUS_APPROVED_HRD])) {
            $badgeClass = 'lrs-status--success';
            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>';
        } elseif ($status === \App\Models\OvertimeRequest::STATUS_REJECTED) {
            $badgeClass = 'lrs-status--error';
            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>';
        } elseif ($status === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
            $badgeClass = 'lrs-status--warning';
            $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>';
            $statusSub = 'Menunggu: '.($mapping?->supervisor?->name ?? 'SPV belum ditentukan');
        }

        $role = $overtime->user?->role;
        $roleLabel = $role instanceof \App\Enums\UserRole ? $role->value : $role;
    @endphp

    <div class="lrs-page">
        <a href="{{ route('overtime-requests.index') }}" class="back-btn" aria-label="Kembali ke riwayat lembur">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        @if (session('success'))
            <div class="lrs-alert lrs-alert--success">{{ session('success') }}</div>
        @endif
        @if (session('error') || $errors->any())
            <div class="lrs-alert lrs-alert--error">{{ session('error') ?? $errors->first() }}</div>
        @endif

        <div class="lrs-layout">
            <div class="lrs-section lrs-hero">
                <div class="lrs-employee">
                    <div class="lrs-avatar">{{ substr($overtime->user->name, 0, 1) }}</div>
                    <div class="lrs-employee-info">
                        <h2 class="lrs-employee-name">{{ $overtime->user->name }}</h2>
                        <div class="lrs-employee-meta">
                            <span class="lrs-role-badge">{{ $roleLabel }}</span>
                            <span class="lrs-meta-dot">·</span>
                            <span class="lrs-meta-text">Pengajuan lembur</span>
                        </div>
                    </div>
                </div>

                <div class="lrs-status-banner {{ $badgeClass }}">
                    <div class="lrs-status-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $statusIcon !!}</svg>
                    </div>
                    <div class="lrs-status-body">
                        <span class="lrs-status-label">{{ $overtime->status_label }}</span>
                        @if($statusSub)<span class="lrs-status-sub">{{ $statusSub }}</span>@endif
                    </div>
                </div>
            </div>

            <div class="lrs-main-col">
                <section class="lrs-section lrs-summary">
                    <div class="lrs-card-header">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <h3 class="lrs-card-title">Ringkasan Pengajuan</h3>
                    </div>
                    <div class="lrs-info-grid">
                        <div class="lrs-info-item">
                            <span class="lrs-info-label">Tanggal Lembur</span>
                            <span class="lrs-info-value">{{ $overtime->overtime_date->translatedFormat('j F Y') }}</span>
                        </div>
                        <div class="lrs-info-item">
                            <span class="lrs-info-label">Jam Lembur</span>
                            <span class="lrs-info-value">{{ $overtime->start_time->format('H:i') }} – {{ $overtime->end_time->format('H:i') }}</span>
                        </div>
                        @if((int) ($overtime->break_minutes ?? 0) > 0)
                        <div class="lrs-info-item">
                            <span class="lrs-info-label">Istirahat</span>
                            <span class="lrs-info-value lrs-duration-badge">{{ $overtime->break_human }}</span>
                        </div>
                        @endif
                        <div class="lrs-info-item">
                            <span class="lrs-info-label">Durasi Lembur</span>
                            <span class="lrs-info-value lrs-duration-badge">{{ $overtime->duration_human }}</span>
                        </div>
                        <div class="lrs-info-item lrs-info-item--full">
                            <span class="lrs-info-label">Diajukan</span>
                            <span class="lrs-info-value">{{ $overtime->created_at->translatedFormat('j F Y') }} · {{ $overtime->created_at->format('H:i') }}</span>
                        </div>
                    </div>
                </section>

                <section class="lrs-section lrs-reason">
                    <div class="lrs-card-header">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <h3 class="lrs-card-title">Keterangan Lembur</h3>
                    </div>
                    <div class="lrs-reason-body">{{ $overtime->description }}</div>
                </section>

                @if ($overtime->status === \App\Models\OvertimeRequest::STATUS_REJECTED && $overtime->rejection_note)
                    <section class="lrs-section">
                        <div class="lrs-note lrs-note--rejection">
                            <div class="lrs-note-header"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span class="lrs-note-title">Alasan Penolakan</span></div>
                            <p class="lrs-note-text">{{ $overtime->rejection_note }}</p>
                        </div>
                    </section>
                @endif
                @include('overtime_requests._system_note', ['overtime' => $overtime])
            </div>

            <div class="lrs-side-col">
                <section class="lrs-section">
                    <div class="lrs-card-header"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><h3 class="lrs-card-title">Persetujuan Lembur</h3></div>
                    @include('overtime_requests._approval_status', ['overtime' => $overtime, 'showTitle' => false])
                </section>

                @if ($overtime->attachments->isNotEmpty())
                    <section class="lrs-section">
                        <div class="lrs-card-header"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><h3 class="lrs-card-title">Bukti Pendukung</h3></div>
                        @include('overtime_requests._attachments', ['overtime' => $overtime, 'showAttachmentTitle' => false])
                    </section>
                @endif
            </div>

            @if($overtime->status === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR)
                <section class="lrs-section lrs-cancel">
                    <div class="lrs-cancel-inner">
                        <a href="{{ route('overtime-requests.edit', $overtime) }}" class="lrs-btn-edit"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Ubah pengajuan</a>
                        <form method="POST" action="{{ route('overtime-requests.destroy', $overtime) }}" onsubmit="return confirm('Batalkan pengajuan lembur ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="lrs-btn-cancel">Batalkan pengajuan</button>
                        </form>
                    </div>
                </section>
            @endif
        </div>
    </div>

    <style>
        .lrs-page { display:flex; flex-direction:column; gap:10px; } .section-header-inline { display:flex; align-items:center; gap:10px; } .section-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; } .icon-navy { background:rgba(10,61,98,.08); color:var(--primary-dark,#0A3D62); } .section-title { margin:0; font-size:1rem; font-weight:800; color:var(--text-primary,#111827); line-height:1.25; } .section-subtitle { margin:0; font-size:.8125rem; color:var(--text-muted,#6B7280); font-weight:500; line-height:1.35; }
        .back-btn { display:inline-flex; align-items:center; gap:6px; height:36px; padding:0 12px 0 10px; background:#fff; border:1px solid var(--border,#E5E7EB); border-radius:10px; color:var(--text-muted,#6B7280); text-decoration:none; box-shadow:0 1px 2px rgba(0,0,0,.04); align-self:flex-start; } .back-btn:hover { border-color:var(--primary,#145DA0); color:var(--primary,#145DA0); } .back-btn-text { font-size:.75rem; font-weight:600; }
        .lrs-alert { padding:12px 16px; border-radius:12px; font-size:13px; font-weight:500; } .lrs-alert--success { background:rgba(34,197,94,.08); border:1px solid rgba(34,197,94,.25); color:#15803d; } .lrs-alert--error { background:rgba(239,68,68,.08); border:1px solid rgba(239,68,68,.25); color:#dc2626; }
        .lrs-layout { display:grid; grid-template-columns:1fr; gap:12px; } .lrs-main-col, .lrs-side-col { display:contents; } .lrs-section { background:#fff; border-radius:16px; border:1px solid var(--gray-200,#E5E7EB); padding:20px; box-shadow:0 1px 3px rgba(0,0,0,.04); } .lrs-card-header { display:flex; align-items:center; gap:10px; margin-bottom:20px; padding-bottom:14px; border-bottom:1px solid var(--gray-200,#E5E7EB); color:var(--primary-dark,#0A3D62); } .lrs-card-title { margin:0; font-size:.8125rem; font-weight:700; color:var(--gray-900,#111827); text-transform:uppercase; letter-spacing:.05em; }
        .lrs-employee { display:flex; align-items:center; gap:14px; margin-bottom:20px; } .lrs-avatar { width:52px; height:52px; background:linear-gradient(135deg,var(--primary-dark,#0A3D62),var(--primary,#145DA0)); color:#fff; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:20px; font-weight:700; flex-shrink:0; } .lrs-employee-info { flex:1; min-width:0; } .lrs-employee-name { margin:0 0 4px; font-size:1.125rem; font-weight:700; color:var(--gray-900,#111827); line-height:1.2; } .lrs-employee-meta { display:flex; align-items:center; gap:6px; flex-wrap:wrap; } .lrs-role-badge { background:rgba(20,93,160,.08); color:var(--primary,#145DA0); padding:2px 8px; border-radius:9999px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; } .lrs-meta-dot { color:var(--gray-400,#9CA3AF); font-size:12px; } .lrs-meta-text { color:var(--gray-500,#6B7280); font-size:13px; font-weight:500; }
        .lrs-status-banner { display:flex; align-items:center; gap:12px; padding:14px 16px; border-radius:12px; border:1px solid transparent; } .lrs-status-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:#fff; } .lrs-status-body { display:flex; flex-direction:column; gap:2px; min-width:0; } .lrs-status-label { font-size:14px; font-weight:700; line-height:1.2; } .lrs-status-sub { font-size:12px; opacity:.85; font-weight:500; } .lrs-status--success { background:rgba(34,197,94,.08); border-color:rgba(34,197,94,.2); } .lrs-status--success .lrs-status-icon { color:#15803d; background:rgba(34,197,94,.12); } .lrs-status--success .lrs-status-label { color:#15803d; } .lrs-status--warning { background:rgba(245,158,11,.08); border-color:rgba(245,158,11,.2); } .lrs-status--warning .lrs-status-icon { color:#a16207; background:rgba(245,158,11,.12); } .lrs-status--warning .lrs-status-label { color:#a16207; } .lrs-status--error { background:rgba(239,68,68,.08); border-color:rgba(239,68,68,.2); } .lrs-status--error .lrs-status-icon { color:#b91c1c; background:rgba(239,68,68,.12); } .lrs-status--error .lrs-status-label { color:#b91c1c; } .lrs-status--neutral { background:var(--gray-100,#F3F4F6); border-color:var(--gray-200,#E5E7EB); } .lrs-status--neutral .lrs-status-icon { color:var(--gray-600,#4B5563); background:var(--gray-200,#E5E7EB); } .lrs-status--neutral .lrs-status-label { color:var(--gray-700,#374151); }
        .lrs-info-grid { display:grid; grid-template-columns:1fr; gap:16px; } .lrs-info-item { display:flex; flex-direction:column; gap:4px; } .lrs-info-label { font-size:11px; font-weight:700; color:var(--gray-400,#9CA3AF); text-transform:uppercase; letter-spacing:.05em; } .lrs-info-label--secondary { margin-top:8px; } .lrs-info-value { font-size:14px; font-weight:600; color:var(--gray-900,#111827); line-height:1.4; } .lrs-duration-badge { align-self:flex-start; padding:4px 8px; border-radius:8px; background:var(--gray-50,#F5F7FA); color:var(--text-secondary,#374151); font-size:.75rem; } .lrs-reason-body { background:var(--gray-50,#F9FAFB); padding:16px; border-radius:12px; border:1px solid var(--gray-200,#E5E7EB); font-size:14px; line-height:1.7; color:var(--gray-600,#4B5563); white-space:pre-line; } .lrs-note { padding:14px 16px; border-radius:12px; border:1px solid transparent; } .lrs-note-header { display:flex; align-items:center; gap:8px; margin-bottom:6px; } .lrs-note-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; } .lrs-note-text { margin:0; font-size:13px; line-height:1.6; white-space:pre-line; } .lrs-note--rejection { background:rgba(239,68,68,.06); border-color:rgba(239,68,68,.15); color:#991b1b; } .lrs-note--rejection .lrs-note-title { color:#b91c1c; }
        .lrs-cancel { grid-column:1 / -1; } .lrs-cancel-inner { display:flex; justify-content:center; gap:12px; flex-wrap:wrap; } .lrs-btn-cancel { width:100%; border:1.5px solid #fecaca; border-radius:12px; padding:12px 24px; background:#fff; color:#dc2626; font:inherit; font-size:14px; font-weight:600; cursor:pointer; } .lrs-btn-edit { display:inline-flex; align-items:center; justify-content:center; gap:8px; width:100%; padding:12px 24px; border:1.5px solid #bfdbfe; border-radius:12px; background:#fff; color:#2563eb; font-size:14px; font-weight:600; text-decoration:none; }
        @media (min-width:480px) { .back-btn { height:40px; padding:0 14px 0 12px; } .back-btn-text { font-size:.8125rem; } .lrs-btn-cancel, .lrs-btn-edit { width:auto; } } @media (min-width:640px) { .lrs-info-grid { grid-template-columns:1fr 1fr; gap:20px; } .lrs-info-item--full { grid-column:1 / -1; } } @media (min-width:768px) { .lrs-section { padding:24px; } .lrs-avatar { width:60px; height:60px; font-size:24px; border-radius:16px; } .lrs-employee-name { font-size:1.25rem; } .lrs-reason-body { padding:20px; } } @media (min-width:1024px) { .lrs-page { gap:12px; } .lrs-layout { grid-template-columns:1fr 380px; gap:20px; align-items:start; } .lrs-hero { grid-column:1 / -1; } .lrs-main-col { grid-column:1; display:flex; flex-direction:column; gap:16px; } .lrs-side-col { grid-column:2; display:flex; flex-direction:column; gap:16px; } }
    </style>
</x-app>
