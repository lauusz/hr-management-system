<x-app title="Approval Lembur (Supervisor)">
    <div class="container mx-auto px-4 py-6">
        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header-simple">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <h4 class="card-title-sm">
                            Daftar Pengajuan Masuk
                            <span class="role-indicator">Supervisor Area</span>
                        </h4>
                        <p class="card-subtitle-sm">
                            Daftar pengajuan lembur dari Staff yang membutuhkan persetujuan Anda.
                        </p>
                    </div>
                    <div style="text-align:right; font-size:12px; color:#6b7280;">
                        Total: <strong>{{ $overtimes->total() }}</strong> Pengajuan
                    </div>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="min-width: 150px;">Pemohon</th>
                            <th style="min-width: 120px;">Tanggal & Waktu</th>
                            <th style="width: 1%; white-space: nowrap;">Durasi</th>
                            <th style="width: 1%; white-space: nowrap;">Status</th>
                            <th style="min-width: 120px;">Keterangan</th>
                            <th style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overtimes as $overtime)
                            @php
                                $statusBadge = 'badge-gray';
                                $statusLabel = $overtime->status;

                                if ($overtime->status == \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR) {
                                    $statusBadge = 'badge-yellow';
                                    $statusLabel = '⏳ Menunggu Approval';
                                } elseif ($overtime->status == \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR) {
                                    $statusBadge = 'badge-blue';
                                    $statusLabel = '✅ Disetujui (Menunggu HR)';
                                } elseif ($overtime->status == \App\Models\OvertimeRequest::STATUS_APPROVED_HRD) {
                                    $statusBadge = 'badge-green';
                                    $statusLabel = 'Disetujui Final';
                                } elseif ($overtime->status == \App\Models\OvertimeRequest::STATUS_REJECTED) {
                                    $statusBadge = 'badge-red';
                                    $statusLabel = 'Ditolak';
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="employee-info">
                                        <span class="fw-bold">{{ $overtime->user->name }}</span>
                                        <span class="text-muted" style="font-size: 11px;">{{ $overtime->user->division->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-date" style="line-height: 1.2;">
                                        <div>{{ $overtime->overtime_date->format('d M Y') }}</div>
                                        <div style="font-size: 10px; color: #6b7280; margin-top: 1px;">
                                            {{ $overtime->start_time->format('H:i') }} - {{ $overtime->end_time->format('H:i') }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-type badge-purple">
                                        {{ $overtime->duration_human }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-type {{ $statusBadge }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-truncate" title="{{ $overtime->description }}">
                                        {{ Str::limit($overtime->description, 50) }}
                                    </div>
                                </td>
                                <td>
                                    @if($overtime->status == \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR)
                                        <a href="{{ route('supervisor.overtime-requests.show', $overtime->id) }}" class="btn-action btn-action-primary">
                                            Proses
                                        </a>
                                    @else
                                        <a href="{{ route('supervisor.overtime-requests.show', $overtime->id) }}" class="btn-action">
                                            Detail
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-state">
                                    <div style="display:flex; flex-direction:column; align-items:center; gap:8px;">
                                        <svg width="40" height="40" fill="none" stroke="#9ca3af" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Tidak ada pengajuan lembur yang perlu diproses saat ini.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 20px;">
            {{ $overtimes->links() }}
        </div>
    </div>

    <style>
        /* --- UTILITY --- */
        .fw-bold { font-weight: 600; color: #111827; }
        .text-muted { color: #6b7280; font-size: 13px; }
        .text-date { font-weight: 500; color: #1f2937; font-size: 12px; }
        
        .employee-info { display: flex; flex-direction: column; gap: 2px; }
        
        .role-indicator {
            font-size: 11px; background: #e0e7ff; color: #3730a3; padding: 2px 8px; border-radius: 12px; margin-left: 8px; vertical-align: middle; font-weight: 600; text-transform: uppercase;
        }

        .text-truncate {
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 11px;
            color: #4b5563;
        }

        /* --- ALERTS --- */
        .alert-success { background: #ecfdf5; color: #065f46; padding: 12px 16px; border-radius: 8px; border: 1px solid #a7f3d0; margin-bottom: 16px; font-size: 14px; }
        .alert-danger { background: #fef2f2; color: #991b1b; padding: 12px 16px; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 16px; font-size: 14px; }

        /* --- CARD --- */
        .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03); border: 1px solid #f3f4f6; padding: 0; }
        .card-header-simple { padding: 16px 24px; border-bottom: 1px solid #f3f4f6; background: #fff; }
        .card-title-sm { margin: 0; font-size: 16px; font-weight: 700; color: #1f2937; }
        .card-subtitle-sm { margin: 4px 0 0; font-size: 13px; color: #6b7280; }

        /* --- TABLE --- */
        .table-wrapper { 
            width: 100%; 
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 4px;
        }
        .custom-table { width: 100%; border-collapse: collapse; min-width: 800px; }

        .custom-table th { background: #f9fafb; padding: 10px 12px; text-align: left; font-size: 10px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e5e7eb; }
        .custom-table td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; font-size: 12px; color: #1f2937; vertical-align: top; }
        .custom-table tr:last-child td { border-bottom: none; }
        .custom-table tr:hover td { background: #fdfdfd; }

        /* --- BADGES --- */
        .badge-type { display: inline-block; padding: 2px 7px; border-radius: 20px; font-size: 9.5px; font-weight: 600; white-space: nowrap; }
        .badge-blue { background: #eff6ff; color: #1d4ed8; }   
        .badge-yellow { background: #fefce8; color: #a16207; } 
        .badge-purple { background: #f3e8ff; color: #7e22ce; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        
        /* --- ACTION BUTTONS --- */
        .btn-action { padding: 4px 10px; border: 1px solid #d1d5db; background: #fff; color: #374151; border-radius: 20px; font-size: 11px; font-weight: 500; text-decoration: none; display: inline-block; transition: all 0.2s; white-space: nowrap; }
        .btn-action:hover { background: #f3f4f6; border-color: #9ca3af; }

        .btn-action-primary { background: #1e4a8d; color: #fff; border-color: #1e4a8d; }
        .btn-action-primary:hover { background: #163a75; border-color: #163a75; color: #fff; }

        .empty-state { padding: 60px 20px; text-align: center; color: #9ca3af; font-style: italic; }

        /* --- RESPONSIVE CARD VIEW --- */
        @media screen and (max-width: 768px) {
            .table-wrapper { background: transparent; }
            
            .custom-table, 
            .custom-table tbody, 
            .custom-table tr, 
            .custom-table td {
                display: block;
                width: 100%;
                min-width: 0;
            }

            .custom-table thead { display: none; }

            .custom-table tr {
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.05);
                margin-bottom: 12px;
                border: 1px solid #f3f4f6;
                padding: 16px;
                position: relative;
            }

            .custom-table td {
                padding: 4px 0;
                border: none;
                text-align: left;
            }
            
            /* 1. Header Card: Pemohon (Top Row) */
            .custom-table td:nth-child(1) { /* Pemohon */
                margin-bottom: 8px;
                border-bottom: 1px solid #f3f4f6;
                padding-bottom: 12px;
            }
            .custom-table td:nth-child(1) .employee-info .fw-bold { font-size: 15px; }

            /* 2. Status & Duration - Flex Row */
            .custom-table td:nth-child(3), /* Durasi */
            .custom-table td:nth-child(4) { /* Status */
                display: inline-block;
                width: auto;
                margin-right: 8px;
                margin-bottom: 8px;
            }

            /* 3. Date & Description */
            .custom-table td:nth-child(2) { /* Tanggal */
                display: block;
                font-size: 13px;
                color: #4b5563;
                margin-bottom: 8px;
            }
            .custom-table td:nth-child(2)::before { content: '📅 '; }

            .custom-table td:nth-child(5) { /* Keterangan */
                margin-top: 4px;
                font-style: italic;
                color: #6b7280;
                font-size: 13px;
                padding: 8px 12px;
                background: #fefce8;
                border-radius: 8px;
                border: 1px dashed #fcd34d;
            }
            .text-truncate { max-width: none; white-space: normal; }

            /* 5. Action Button */
            .custom-table td:last-child {
                margin-top: 16px;
                padding-top: 12px;
                border-top: 1px solid #f3f4f6;
                text-align: right;
                display: flex;
                gap: 10px;
            }
            
            .btn-action {
                flex: 1;
                text-align: center;
                justify-content: center;
                display: flex;
                align-items: center;
            }
            
            .custom-table tr:has(.empty-state) {
                text-align: center;
                padding: 40px 20px;
            }
        }
    </style>
</x-app>
