<x-app title="Detail Approval Lembur">
    <div class="container mx-auto px-4 py-6">
        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert-error">
                {{ session('error') }}
            </div>
        @endif

        <div class="card">
            <div class="profile-header">
                <div class="profile-main">
                    <div class="profile-avatar">
                        {{ substr($overtimeRequest->user->name, 0, 1) }}
                    </div>
                    <div class="profile-info">
                        <h2 class="profile-name">{{ $overtimeRequest->user->name }}</h2>
                        <div class="profile-meta">
                            <span class="chip-role">{{ $overtimeRequest->user->role instanceof \App\Enums\UserRole ? $overtimeRequest->user->role->label() : $overtimeRequest->user->role }}</span>
                            <span class="dot">•</span>
                            <span>{{ $overtimeRequest->user->division->name ?? '-' }}</span>
                            <span class="dot">•</span>
                            <span>{{ $overtimeRequest->created_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                @php
                    $statusBadge = 'badge-gray';
                    $statusLabel = $overtimeRequest->status;

                    if ($overtimeRequest->status == \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
                        $statusBadge = 'badge-yellow';
                        $statusLabel = '⏳ Menunggu Approval';
                    } elseif ($overtimeRequest->status == \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR) {
                        $statusBadge = 'badge-blue';
                        $statusLabel = '✅ Disetujui (Menunggu HR)';
                    } elseif ($overtimeRequest->status == \App\Models\OvertimeRequest::STATUS_APPROVED_HRD) {
                        $statusBadge = 'badge-green';
                        $statusLabel = 'Disetujui Final';
                    } elseif ($overtimeRequest->status == \App\Models\OvertimeRequest::STATUS_REJECTED) {
                        $statusBadge = 'badge-red';
                        $statusLabel = 'Ditolak';
                    }
                @endphp
                
                <div class="status-wrapper">
                    <span class="badge-status {{ $statusBadge }}">
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>

            <div class="divider-full"></div>

            <div class="detail-container">
                <div class="detail-section">
                    <h4 class="section-title">Informasi Lembur</h4>

                    <div class="info-row">
                        <div class="info-label">Tanggal Lembur</div>
                        <div class="info-value">
                            {{ $overtimeRequest->overtime_date->format('d M Y') }}
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Jam Lembur</div>
                        <div class="info-value">
                            {{ $overtimeRequest->start_time->format('H:i') }} - {{ $overtimeRequest->end_time->format('H:i') }}
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Durasi</div>
                        <div class="info-value">
                            <span class="badge-basic">{{ $overtimeRequest->duration_human }}</span>
                        </div>
                    </div>

                    @if($overtimeRequest->supervisorApprover)
                    <div class="info-row">
                        <div class="info-label">Disetujui Supervisor</div>
                        <div class="info-value">
                            {{ $overtimeRequest->supervisorApprover->name }}
                            <div class="text-muted" style="font-size:12px; margin-top:2px;">
                                {{ $overtimeRequest->approved_by_supervisor_at ? $overtimeRequest->approved_by_supervisor_at->format('d M Y H:i') : '' }}
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($overtimeRequest->hrdApprover)
                    <div class="info-row">
                        <div class="info-label">Disetujui HRD</div>
                        <div class="info-value">
                            {{ $overtimeRequest->hrdApprover->name }}
                            <div class="text-muted" style="font-size:12px; margin-top:2px;">
                                {{ $overtimeRequest->approved_by_hrd_at ? $overtimeRequest->approved_by_hrd_at->format('d M Y H:i') : '' }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($overtimeRequest->status === \App\Models\OvertimeRequest::STATUS_REJECTED && $overtimeRequest->rejection_note)
                    <div class="system-note-box">
                        <div class="note-label">Alasan Penolakan:</div>
                        <div class="note-content">{{ $overtimeRequest->rejection_note }}</div>
                    </div>
                    @endif
                </div>

                <div class="detail-section">
                    <h4 class="section-title">Keterangan Pekerjaan</h4>
                    
                    <div class="info-row">
                        <div class="info-label">Deskripsi</div>
                        <div class="info-value box-reason">
                            {{ $overtimeRequest->description }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-footer">
                <div class="left-action">
                    <a href="{{ route('supervisor.overtime-requests.index') }}" class="btn-modern btn-back">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali
                    </a>
                </div>

                <div class="right-action">
                    @if($overtimeRequest->status == \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR)
                        <button type="button" data-modal-target="modal-reject" class="btn-modern btn-reject">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Tolak
                        </button>

                        <button type="button" data-modal-target="modal-approve" class="btn-modern btn-approve">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Setujui
                        </button>
                    @else
                        <div class="processed-info">Status: <strong>{{ $statusLabel }}</strong></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL REJECT --}}
    <x-modal
        id="modal-reject"
        title="Tolak Pengajuan Lembur?"
        type="confirm"
        variant="danger"
        confirmLabel="Tolak Pengajuan"
        cancelLabel="Batal"
        :confirmFormAction="route('supervisor.overtime-requests.reject', $overtimeRequest->id)"
        confirmFormMethod="POST">
        <p style="margin-bottom:12px; color:#374151;">
            Yakin menolak pengajuan lembur <strong>{{ $overtimeRequest->user->name }}</strong>?
        </p>
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="rejection_note">
                Alasan Penolakan <span class="text-red-500">*</span>
            </label>
            <textarea name="rejection_note" id="rejection_note" rows="3" class="form-control" required placeholder="Jelaskan alasan penolakan..."></textarea>
        </div>
    </x-modal>

    {{-- MODAL APPROVE --}}
    <x-modal
        id="modal-approve"
        title="Setujui Pengajuan Lembur?"
        type="confirm"
        variant="primary"
        confirmLabel="Ya, Setujui"
        cancelLabel="Batal"
        :confirmFormAction="route('supervisor.overtime-requests.approve', $overtimeRequest->id)"
        confirmFormMethod="POST">
        <p style="margin:0; color:#374151;">
            Setujui pengajuan lembur ini?
        </p>
    </x-modal>

    <style>
        /* --- UTILITY & ALERTS --- */
        .alert-success { background: #ecfdf5; color: #065f46; padding: 12px 16px; border-radius: 8px; border: 1px solid #a7f3d0; margin-bottom: 16px; font-size: 14px; }
        .alert-error { background: #fef2f2; color: #991b1b; padding: 12px 16px; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 16px; font-size: 14px; }
        .text-muted { color: #6b7280; }

        /* --- CARD --- */
        .card { 
            background: #fff; 
            border-radius: 12px; 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03); 
            border: 1px solid #f3f4f6; 
            overflow: hidden; 
        }

        /* --- PROFILE HEADER --- */
        .profile-header { padding: 24px; display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; flex-wrap: wrap; background: #fff; }
        .profile-main { display: flex; gap: 16px; align-items: center; }
        .profile-avatar { 
            width: 56px; height: 56px; 
            background: #eef2ff; color: #1e4a8d; 
            border-radius: 12px; 
            display: flex; align-items: center; justify-content: center; 
            font-size: 22px; font-weight: 700; 
        }
        .profile-info { display: flex; flex-direction: column; gap: 4px; }
        .profile-name { margin: 0; font-size: 18px; font-weight: 700; color: #111827; }
        
        .profile-meta { font-size: 13px; color: #6b7280; display: flex; align-items: center; flex-wrap: wrap; gap: 6px; margin-top: 4px; }
        .dot { color: #d1d5db; display: inline-block; transform: scale(1.2); }
        .chip-role { 
            background: #f3f4f6; color: #4b5563; 
            padding: 2px 8px; border-radius: 6px; 
            font-size: 11px; text-transform: uppercase; 
            letter-spacing: 0.04em; font-weight: 600; 
        }
        
        .divider-full { height: 1px; background: #f3f4f6; width: 100%; }
        
        /* --- DETAILS LAYOUT --- */
        .detail-container { 
            padding: 32px; 
            display: grid; 
            grid-template-columns: 1fr 1.5fr; 
            gap: 48px; 
        }

        .section-title { 
            font-size: 14px; font-weight: 700; color: #111827; 
            text-transform: uppercase; letter-spacing: 0.05em; 
            margin: 0 0 20px 0; padding-bottom: 8px; 
            border-bottom: 2px solid #f3f4f6; display: inline-block; 
        }
        
        .info-row { margin-bottom: 20px; }
        .info-label { font-size: 12px; color: #6b7280; margin-bottom: 6px; font-weight: 600; text-transform: uppercase; }
        .info-value { font-size: 15px; color: #1f2937; font-weight: 500; line-height: 1.6; }
        
        .box-reason { 
            background: #fdfdfd; 
            padding: 16px; 
            border-radius: 12px; 
            border: 1px solid #f3f4f6; 
            color: #374151; font-size: 14.5px; 
            line-height: 1.6;
        }

        /* --- SYSTEM NOTES --- */
        .system-note-box { background: #fffbeb; border: 1px solid #fef3c7; border-radius: 10px; padding: 16px; margin-top: 10px; }
        .note-label { font-size: 12px; font-weight: 700; color: #92400e; margin-bottom: 6px; text-transform: uppercase; display: flex; align-items: center; }
        .note-content { font-size: 14px; color: #b45309; line-height: 1.5; }

        /* --- BADGES --- */
        .badge-basic { background: #f3f4f6; color: #374151; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; border: 1px solid #e5e7eb; display: inline-block; }
        
        .badge-status { display: inline-block; padding: 6px 14px; border-radius: 30px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
        
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fefce8; color: #a16207; }
        .badge-blue { background: #eff6ff; color: #1d4ed8; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        .badge-teal { background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; }

        /* --- FOOTER ACTION --- */
        .action-footer { background: #f9fafb; padding: 20px 32px; border-top: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .right-action { display: flex; gap: 12px; align-items: center; }
        .action-group { display: flex; gap: 12px; align-items: center; }

        /* --- BUTTONS --- */
        .btn-modern { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 22px; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; border: 1px solid transparent; text-decoration: none; line-height: 1.25; min-width: 140px; }
        
        .btn-back { background: #fff; border-color: #d1d5db; color: #374151; }
        .btn-back:hover { background: #f3f4f6; border-color: #9ca3af; color: #111827; }

        .btn-approve { background: #1e4a8d; color: #fff; border: 1px solid #1e4a8d; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .btn-approve:hover { background: #163a75; border-color: #163a75; transform: translateY(-1px); }

        .btn-reject { background: #fff; border-color: #fee2e2; color: #dc2626; }
        .btn-reject:hover { background: #fef2f2; border-color: #fca5a5; color: #b91c1c; }

        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; transition: border-color 0.2s; }
        .form-control:focus { outline: none; border-color: #1e4a8d; box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1); }

        .processed-info { font-size: 13.5px; color: #6b7280; background: #fff; padding: 8px 16px; border-radius: 8px; border: 1px solid #e5e7eb; font-weight: 500; }

        /* --- RESPONSIVE --- */
        @media(max-width: 1024px) {
            .detail-container { grid-template-columns: 1fr; gap: 32px; padding: 24px; }
            .section-title { width: 100%; border-bottom-width: 1px; }
        }

        @media(max-width: 640px) {
            .profile-header { flex-direction: column; gap: 16px; align-items: stretch; padding: 20px; }
            .status-wrapper { align-self: flex-start; }
            
            .action-footer { flex-direction: column; gap: 16px; align-items: stretch; padding: 16px 20px; }
            .left-action, .right-action { width: 100%; justify-content: stretch; }
            .right-action { flex-direction: column; gap: 10px; }
            
            .btn-modern { width: 100%; justify-content: center; min-width: 0; }
            .btn-modern svg { margin-right: 4px; }
            
            .info-value { font-size: 14px; }
        }
    </style>
</x-app>
