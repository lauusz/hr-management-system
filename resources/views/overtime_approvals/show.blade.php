<x-app title="Detail Approval Lembur">
    @php
        $mapping = $overtimeRequest->user->overtimeApproverAssignment;
        $actor = auth()->id();
        $slot = $mapping && $mapping->supervisor_id == $actor ? 'SUPERVISOR' : ($mapping && $mapping->manager_id == $actor ? 'MANAGER' : null);
        $canAct = $overtimeRequest->status === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR && $mapping?->supervisor_id && $mapping?->manager_id && $slot && !$overtimeRequest->currentApprovalActions()->contains('decision_slot', $slot) && !$overtimeRequest->currentApprovalActions()->contains('actor_id', $actor);
        $badgeClass = match ($overtimeRequest->status) {
            \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR => 'ot-badge--warning',
            \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR, \App\Models\OvertimeRequest::STATUS_APPROVED_HRD => 'ot-badge--success',
            \App\Models\OvertimeRequest::STATUS_REJECTED => 'ot-badge--danger',
            default => 'ot-badge--gray',
        };
    @endphp

    <x-slot name="header">
        <div class="ot-detail-header">
            <div class="ot-detail-icon">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="14" r="8" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 2h4m-2 0v4m0 8 3-3"/></svg>
            </div>
            <div><h1>Detail Approval Lembur</h1><p>Informasi lengkap pengajuan lembur</p></div>
        </div>
    </x-slot>

    <div class="ot-detail {{ $canAct ? 'ot-detail--has-actions' : '' }}">
        <a href="{{ route('overtime-approvals.index') }}" class="ot-back" aria-label="Kembali ke daftar approval lembur">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>

        @if(session('success')) <div class="ot-alert ot-alert--success" role="status">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="ot-alert ot-alert--error" role="alert">{{ session('error') }}</div> @endif
        @if($errors->any()) <div class="ot-alert ot-alert--error" role="alert">{{ $errors->first() }}</div> @endif

        <section class="ot-card" aria-label="Informasi karyawan">
            <div class="ot-employee">
                <div class="ot-avatar" aria-hidden="true">{{ mb_substr($overtimeRequest->user->name, 0, 1) }}</div>
                <div class="ot-employee-info">
                    <h2>{{ $overtimeRequest->user->name }}</h2>
                    <div class="ot-employee-meta"><span class="ot-role">{{ $overtimeRequest->user->role?->label() ?? '-' }}</span><span>•</span><span>{{ $overtimeRequest->user->position?->name ?? '-' }}</span><span>•</span><span>{{ $overtimeRequest->user->division?->name ?? '-' }}</span></div>
                </div>
            </div>
            <div class="ot-employee-footer">
                <span class="ot-badge {{ $badgeClass }}">{{ $overtimeRequest->status_label }}</span>
                @if($overtimeRequest->created_at)<span class="ot-submit-time">Diajukan {{ $overtimeRequest->created_at->translatedFormat('j F Y, H:i') }}</span>@endif
            </div>
        </section>

        <section class="ot-card">
            <h2 class="ot-section-title"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Informasi Lembur</h2>
            <dl class="ot-info">
                <div><dt>Tanggal</dt><dd>{{ $overtimeRequest->overtime_date->translatedFormat('l, j F Y') }}</dd></div>
                <div><dt>Jam</dt><dd>{{ $overtimeRequest->start_time->format('H:i') }} – {{ $overtimeRequest->end_time->format('H:i') }}</dd></div>
                <div><dt>Durasi</dt><dd><span class="ot-duration">{{ $overtimeRequest->duration_human }}</span></dd></div>
            </dl>
        </section>

        <section class="ot-card">
            <h2 class="ot-section-title"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l6 6v10a2 2 0 01-2 2z"/></svg>Keterangan Pekerjaan</h2>
            <div class="ot-description">{{ $overtimeRequest->description }}</div>
        </section>

        @if($overtimeRequest->attachments->isNotEmpty())
            <section class="ot-card">
                <h2 class="ot-section-title"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.6-4.6a2 2 0 012.8 0L16 16m-2-2l1.6-1.6a2 2 0 012.8 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Bukti Pendukung ({{ $overtimeRequest->attachments->count() }} file)</h2>
                <div class="ot-files">
                    @foreach($overtimeRequest->attachments as $attachment)
                        @php $evidenceUrl = route('overtime-requests.attachments.show', [$overtimeRequest, $attachment]); @endphp
                        <div class="ot-file">
                            @if($attachment->mime_type === 'image/jpeg')
                                <button type="button" class="ot-photo" data-image-viewer-src="{{ $evidenceUrl }}" data-image-viewer-alt="{{ $attachment->original_name }}" aria-label="Lihat foto {{ $attachment->original_name }}">
                                    <img src="{{ $evidenceUrl }}" alt="{{ $attachment->original_name }}" loading="lazy">
                                    <span>Lihat Foto</span>
                                </button>
                            @else
                                <div class="ot-file-icon" aria-hidden="true"><svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8m-4-6l6 6m-6-6v6h6"/></svg></div>
                            @endif
                            <a href="{{ $evidenceUrl }}" target="_blank" rel="noopener noreferrer">{{ $attachment->original_name }}</a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="ot-card">
            <h2 class="ot-section-title"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Persetujuan Lembur</h2>
            <div class="ot-approval">@include('overtime_requests._approval_status', ['overtime' => $overtimeRequest])</div>
            @if($overtimeRequest->status === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR && (!$mapping?->supervisor_id || !$mapping?->manager_id))
                <div class="ot-notice">Menunggu HR melengkapi kedua approver.</div>
            @elseif($overtimeRequest->status === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR && $slot && !$overtimeRequest->currentApprovalActions()->contains('decision_slot', $slot) && $overtimeRequest->currentApprovalActions()->contains('actor_id', $actor))
                <div class="ot-notice">Anda sudah memberikan keputusan pada posisi lain. Hubungi HR untuk menetapkan approver yang berbeda.</div>
            @endif
        </section>

        @if(auth()->user()->isManager() && $overtimeRequest->user_id !== auth()->id() && $overtimeRequest->status === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR)
            <section class="ot-card">
                <h2 class="ot-section-title"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9.4a2 2 0 012.8 2.8L11.8 15H9v-2.8z"/></svg>Perubahan Pengajuan</h2>
                @include('overtime_requests._edit_form', ['overtime' => $overtimeRequest])
            </section>
        @endif
    </div>

    @if($canAct)
        <div class="ot-action-bar">
            <div class="ot-action-dock">
                <button type="button" class="ot-action ot-action--approve" data-modal-target="overtime-approve" aria-haspopup="dialog" aria-controls="overtime-approve">Setujui</button>
                <button type="button" class="ot-action ot-action--reject" data-modal-target="overtime-reject" aria-haspopup="dialog" aria-controls="overtime-reject">Tolak</button>
            </div>
        </div>

        <x-modal id="overtime-approve" title="Setujui Pengajuan Lembur?" type="confirm" variant="success" confirmLabel="Ya, Setujui" cancelLabel="Batal" :confirmFormAction="route('overtime-approvals.approve', $overtimeRequest)" confirmFormMethod="POST">
            <p style="margin:0">Setujui pengajuan lembur <strong>{{ $overtimeRequest->user->name }}</strong>?</p>
        </x-modal>

        <x-modal id="overtime-reject" title="Tolak Pengajuan Lembur?" type="form" variant="danger">
            <form method="POST" action="{{ route('overtime-approvals.reject', $overtimeRequest) }}">
                @csrf
                <p class="ot-modal-message">Pengajuan lembur <strong>{{ $overtimeRequest->user->name }}</strong> akan ditolak.</p>
                <label class="ot-modal-label" for="rejection_note">Alasan penolakan</label>
                <textarea class="ot-modal-textarea" id="rejection_note" name="rejection_note" maxlength="255" required>{{ old('rejection_note') }}</textarea>
                <div class="ot-modal-actions">
                    <button type="button" class="modal-btn modal-btn-secondary" data-modal-close="true">Batal</button>
                    <button type="submit" class="modal-btn modal-btn-primary">Ya, Tolak</button>
                </div>
            </form>
        </x-modal>
    @endif

    <style>
        .ot-detail-header { display:flex; align-items:center; gap:10px; }
        .ot-detail-icon { display:flex; align-items:center; justify-content:center; width:32px; height:32px; flex-shrink:0; border-radius:8px; background:rgba(10,61,98,.08); color:var(--primary-dark,#0A3D62); }
        .ot-detail-header h1 { margin:0; font-size:1rem; font-weight:800; line-height:1.25; color:var(--text-primary,#111827); }
        .ot-detail-header p { margin:0; font-size:.8125rem; font-weight:500; line-height:1.35; color:var(--text-muted,#6B7280); }
        .ot-detail { display:flex; flex-direction:column; gap:12px; min-width:0; }
        .ot-detail--has-actions { padding-bottom:calc(130px + env(safe-area-inset-bottom)); }
        .ot-back { align-self:flex-start; display:inline-flex; align-items:center; gap:6px; min-height:36px; padding:0 12px 0 10px; border:1px solid var(--border,#E5E7EB); border-radius:10px; background:#fff; color:var(--text-muted,#6B7280); text-decoration:none; font-size:.75rem; font-weight:600; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .ot-back:hover { border-color:#145DA0; color:#145DA0; }
        .ot-alert,.ot-notice { border-radius:12px; padding:12px 16px; font-size:.8125rem; line-height:1.5; }
        .ot-alert--success { background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; }
        .ot-alert--error { background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; }
        .ot-notice { margin-top:12px; background:#fffbeb; color:#92400e; }
        .ot-card { min-width:0; background:#fff; border:1px solid var(--border-light,#E5E7EB); border-radius:16px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,.04); }
        .ot-section-title { display:flex; align-items:center; gap:8px; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid var(--border-light,#E5E7EB); font-size:.875rem; font-weight:700; color:var(--text-primary,#111827); }
        .ot-section-title svg { flex-shrink:0; color:var(--primary,#145DA0); }
        .ot-employee { display:flex; align-items:center; gap:12px; margin-bottom:16px; }
        .ot-avatar { width:52px; height:52px; display:flex; align-items:center; justify-content:center; flex-shrink:0; border-radius:12px; background:rgba(20,93,160,.08); color:#145DA0; font-size:1.25rem; font-weight:700; }
        .ot-employee-info { min-width:0; }
        .ot-employee-info h2 { margin:0; font-size:1.1rem; line-height:1.35; overflow-wrap:anywhere; }
        .ot-employee-meta { display:flex; align-items:center; flex-wrap:wrap; gap:4px; font-size:.75rem; color:var(--text-muted,#6B7280); overflow-wrap:anywhere; } .ot-role { padding:2px 6px; border-radius:4px; background:#f5f7fa; font-size:.65rem; font-weight:700; text-transform:uppercase; } .ot-submit-time { font-size:.75rem; color:var(--text-muted,#6B7280); }
        .ot-employee-footer { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:8px; padding-top:12px; border-top:1px solid var(--border-light,#E5E7EB); }
        .ot-badge { display:inline-flex; max-width:100%; padding:5px 10px; border-radius:999px; font-size:.6875rem; font-weight:700; text-transform:uppercase; overflow-wrap:anywhere; }
        .ot-badge--warning { background:#fffbeb; color:#a16207; } .ot-badge--success { background:#f0fdf4; color:#15803d; } .ot-badge--danger { background:#fef2f2; color:#b91c1c; } .ot-badge--gray { background:#f3f4f6; color:#4b5563; }
        .ot-info { margin:0; } .ot-info div { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:6px 16px; padding:10px 0; border-bottom:1px solid #f3f4f6; } .ot-info div:last-child { border-bottom:0; padding-bottom:0; }
        .ot-info dt { font-size:.6875rem; font-weight:600; text-transform:uppercase; color:#6B7280; } .ot-info dd { margin:0; font-size:.875rem; font-weight:600; overflow-wrap:anywhere; }
        .ot-duration { display:inline-block; padding:4px 8px; border-radius:8px; background:#ecfdf5; color:#15803d; font-size:.75rem; }
        .ot-description { padding:14px; border-radius:10px; background:#F5F7FA; font-size:.875rem; line-height:1.6; white-space:pre-wrap; overflow-wrap:anywhere; }
        .ot-files { display:flex; flex-wrap:wrap; gap:16px; } .ot-file { width:160px; max-width:100%; min-width:0; font-size:.75rem; } .ot-file a { display:block; color:#145DA0; margin-top:6px; overflow-wrap:anywhere; }
        .ot-photo,.ot-file-icon { width:100%; height:120px; border:1px solid #e5e7eb; border-radius:10px; background:#f5f7fa; overflow:hidden; } .ot-photo { position:relative; display:block; padding:0; cursor:pointer; } .ot-photo img { width:100%; height:100%; object-fit:cover; } .ot-photo span { position:absolute; bottom:0; left:0; right:0; padding:5px; background:rgba(0,0,0,.6); color:white; font-size:.75rem; } .ot-file-icon { display:flex; align-items:center; justify-content:center; color:#145DA0; }
        .ot-approval { overflow-wrap:anywhere; } .ot-approval > div { margin:0 !important; } .ot-approval > div > strong { display:none; } .ot-approval summary { cursor:pointer; color:#145DA0; font-weight:600; }
        .ot-detail .overtime-edit { margin:0 !important; } .ot-detail .overtime-edit summary { cursor:pointer; font-size:.875rem; font-weight:600; color:#145DA0; } .ot-detail .overtime-edit-form { max-width:100% !important; gap:12px !important; }
        .ot-detail .overtime-edit-form label { display:grid; gap:5px; font-size:.8125rem; font-weight:600; color:#374151; } .ot-detail .overtime-edit-form input,.ot-detail .overtime-edit-form textarea,.ot-modal-textarea { box-sizing:border-box; width:100%; min-width:0; padding:10px 12px; border:1px solid #D1D5DB; border-radius:10px; background:#fff; color:#111827; font:inherit; }
        .ot-detail .overtime-edit-form textarea,.ot-modal-textarea { min-height:88px; resize:vertical; } .ot-detail .overtime-edit-form p { margin:0; padding:10px; border-radius:8px; background:#fffbeb; color:#92400e; font-size:.8125rem; } .ot-detail .overtime-edit-form button { justify-self:start; min-height:42px; padding:9px 16px; border:0; border-radius:10px; background:#145DA0; color:white; font:inherit; font-size:.8125rem; font-weight:600; cursor:pointer; }
        .ot-action-bar { position:fixed; z-index:100; left:0; right:0; bottom:0; padding:10px 16px calc(10px + env(safe-area-inset-bottom)); background:rgba(245,247,250,.96); }
        .ot-action-dock { display:flex; gap:10px; max-width:1200px; margin:auto; padding:10px; border:1px solid #e5e7eb; border-radius:16px; background:#fff; box-shadow:0 4px 18px rgba(0,0,0,.1); }
        .ot-action { flex:1; min-height:44px; padding:10px 16px; border-radius:10px; font:inherit; font-size:.875rem; font-weight:700; cursor:pointer; } .ot-action--approve { border:1px solid #145DA0; background:#145DA0; color:#fff; } .ot-action--reject { border:1px solid #ef4444; background:#fff; color:#dc2626; }
        .ot-modal-message { margin:0 0 16px; } .ot-modal-label { display:block; margin-bottom:6px; font-weight:600; } .ot-modal-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:16px; }
        @media (max-width:480px) { .ot-card { padding:16px; } .ot-info div { align-items:flex-start; flex-direction:column; } .ot-modal-actions { flex-direction:column-reverse; } }
        @media (min-width:1025px) { .ot-action-bar { left:calc(var(--sidebar-width) + 12px); bottom:16px; background:transparent; padding:0 16px; } .app.sidebar-collapsed .ot-action-bar { left:0; } }
    </style>

    @if($canAct)
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                ['overtime-approve', 'overtime-reject'].forEach(function (id) {
                    const modal = document.getElementById(id);
                    const trigger = document.querySelector('[data-modal-target="' + id + '"]');
                    if (!modal || !trigger) return;
                    trigger.addEventListener('click', function () {
                        const focusTarget = modal.querySelector(id === 'overtime-reject' ? '#rejection_note' : '.modal-close');
                        if (focusTarget) focusTarget.focus();
                    });
                    modal.addEventListener('click', function (event) {
                        if (event.target === modal || event.target.closest('[data-modal-close]')) trigger.focus();
                    });
                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape' && modal.style.display === 'flex') modal.querySelector('[data-modal-close]').click();
                    });
                });
                @if($errors->has('rejection_note'))
                    document.querySelector('[data-modal-target="overtime-reject"]').click();
                @endif
            });
        </script>
        @endpush
    @endif
</x-app>
