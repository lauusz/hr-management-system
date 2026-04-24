<x-app title="Approval Lembur (Supervisor)">
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

        .page-wrapper { max-width: 680px; margin: 0 auto; padding: 16px; }

        /* Header */
        .page-header { margin-bottom: 16px; }
        .page-header-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 4px; }
        .page-title { font-size: 20px; font-weight: 700; color: var(--gray-900); display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .role-chip { font-size: 11px; background: #e0e7ff; color: #3730a3; padding: 3px 10px; border-radius: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
        .page-subtitle { font-size: 13px; color: var(--gray-500); }
        .total-badge { font-size: 12px; color: var(--gray-500); white-space: nowrap; }
        .total-badge strong { color: var(--gray-700); }

        /* Alerts */
        .alert-success { background: var(--success-bg); color: #065f46; padding: 12px 16px; border-radius: 10px; border: 1px solid #a7f3d0; margin-bottom: 16px; font-size: 13.5px; }
        .alert-danger { background: var(--danger-bg); color: #991b1b; padding: 12px 16px; border-radius: 10px; border: 1px solid #fecaca; margin-bottom: 16px; font-size: 13.5px; }

        /* Cards */
        .card { background: #fff; border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); border: 1px solid var(--gray-200); overflow: hidden; }

        /* Overtime Card */
        .overtime-card { padding: 16px; border-bottom: 1px solid var(--gray-100); display: flex; flex-direction: column; gap: 10px; }
        .overtime-card:last-child { border-bottom: none; }
        .overtime-card.card-pending { border-left: 4px solid var(--primary); }

        .overtime-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
        .employee-info { display: flex; align-items: center; gap: 10px; }
        .avatar { width: 42px; height: 42px; background: var(--primary-light); color: var(--primary-dark); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; flex-shrink: 0; }
        .employee-name { font-size: 14px; font-weight: 700; color: var(--gray-900); }
        .employee-division { font-size: 11.5px; color: var(--gray-400); margin-top: 1px; }

        /* Status chips */
        .chip-status { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; white-space: nowrap; }
        .status-pending { background: var(--warning-bg); color: #a16207; }
        .status-supervisor { background: var(--primary-light); color: var(--primary-dark); }
        .status-approved { background: var(--success-bg); color: #166534; }
        .status-rejected { background: var(--danger-bg); color: #991b1b; }

        .overtime-body { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .overtime-info { display: flex; flex-direction: column; gap: 2px; }
        .overtime-label { font-size: 10.5px; color: var(--gray-400); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; }
        .overtime-value { font-size: 13px; font-weight: 600; color: var(--gray-800); }
        .badge-duration { display: inline-block; background: #f3e8ff; color: #7e22ce; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }

        .overtime-desc { background: var(--gray-50); padding: 10px 12px; border-radius: 8px; border: 1px dashed var(--gray-200); font-size: 12.5px; color: var(--gray-600); line-height: 1.5; margin-top: 2px; }

        .overtime-footer { display: flex; justify-content: flex-end; }
        .btn-card { padding: 8px 18px; border-radius: 10px; font-size: 12.5px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; }
        .btn-card-process { background: var(--primary); color: #fff; }
        .btn-card-process:hover { background: var(--primary-dark); }
        .btn-card-detail { background: var(--gray-100); color: var(--gray-700); border: 1px solid var(--gray-200); }
        .btn-card-detail:hover { background: var(--gray-200); }

        /* Empty State */
        .empty-state { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 60px 20px; color: var(--gray-400); text-align: center; }
        .empty-state svg { opacity: 0.5; }
        .empty-state p { font-size: 14px; max-width: 280px; line-height: 1.6; }

        /* Pagination */
        .pagination-wrapper { margin-top: 16px; display: flex; justify-content: center; }

        @media (max-width: 480px) {
            .overtime-body { grid-template-columns: 1fr; }
        }
        @media (min-width: 681px) {
            .page-wrapper { padding: 24px 16px; }
        }
    </style>

    <div class="page-wrapper">
        <div class="page-header">
            <div class="page-header-top">
                <h1 class="page-title">
                    Approval Lembur
                    <span class="role-chip">Supervisor</span>
                </h1>
                <span class="total-badge">Total: <strong>{{ $overtimes->total() }}</strong></span>
            </div>
            <p class="page-subtitle">Daftar pengajuan lembur yang membutuhkan persetujuan Anda.</p>
        </div>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card">
            @forelse($overtimes as $overtime)
                @php
                    $statusClass = 'status-rejected';
                    $statusLabel = $overtime->status;
                    if ($overtime->status == \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR) { $statusClass = 'status-pending'; $statusLabel = 'Menunggu'; }
                    elseif ($overtime->status == \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR) { $statusClass = 'status-supervisor'; $statusLabel = 'Disetujui SPV'; }
                    elseif ($overtime->status == \App\Models\OvertimeRequest::STATUS_APPROVED_HRD) { $statusClass = 'status-approved'; $statusLabel = 'Disetujui Final'; }
                    elseif ($overtime->status == \App\Models\OvertimeRequest::STATUS_REJECTED) { $statusClass = 'status-rejected'; $statusLabel = 'Ditolak'; }
                    $isPending = $overtime->status == \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR;
                @endphp
                <div class="overtime-card {{ $isPending ? 'card-pending' : '' }}">
                    <div class="overtime-header">
                        <div class="employee-info">
                            <div class="avatar">{{ substr($overtime->user->name, 0, 1) }}</div>
                            <div>
                                <div class="employee-name">{{ $overtime->user->name }}</div>
                                <div class="employee-division">{{ $overtime->user->division->name ?? '-' }}</div>
                            </div>
                        </div>
                        <span class="chip-status {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                    <div class="overtime-body">
                        <div class="overtime-info">
                            <span class="overtime-label">Tanggal</span>
                            <span class="overtime-value">{{ $overtime->overtime_date->translatedFormat('j F Y') }}</span>
                        </div>
                        <div class="overtime-info">
                            <span class="overtime-label">Jam</span>
                            <span class="overtime-value">{{ $overtime->start_time->format('H:i') }} - {{ $overtime->end_time->format('H:i') }}</span>
                        </div>
                        <div class="overtime-info">
                            <span class="overtime-label">Durasi</span>
                            <span class="overtime-value"><span class="badge-duration">{{ $overtime->duration_human }}</span></span>
                        </div>
                        <div class="overtime-info">
                            <span class="overtime-label">Keterangan</span>
                            <div class="overtime-desc">{{ Str::limit($overtime->description, 80) }}</div>
                        </div>
                    </div>
                    <div class="overtime-footer">
                        <a href="{{ route('supervisor.overtime-requests.show', $overtime->id) }}" class="btn-card {{ $isPending ? 'btn-card-process' : 'btn-card-detail' }}">
                            @if($isPending)
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                Proses
                            @else
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Detail
                            @endif
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>Tidak ada pengajuan lembur yang perlu diproses saat ini.</p>
                </div>
            @endforelse
        </div>

        @if($overtimes->hasPages())
            <div class="pagination-wrapper">
                {{ $overtimes->links() }}
            </div>
        @endif
    </div>
</x-app>
