<x-app title="Approval Lembur (HR)">
    <div class="container mx-auto px-4 py-6">
        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header-simple">
                <h4 class="card-title-sm">Approval Lembur</h4>
                <p class="card-subtitle-sm">Daftar pengajuan lembur yang membutuhkan persetujuan Anda.</p>
            </div>

            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="min-width: 140px;">Karyawan</th>
                            <th>Tanggal & Waktu</th>
                            <th style="max-width: 120px;">Durasi</th>
                            <th>Supervisor Approval</th>
                            <th class="text-right" style="width: 60px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overtimes as $overtime)
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <span class="fw-bold" style="font-size: 13px;">{{ $overtime->user->name }}</span>
                                        <span class="text-muted" style="font-size: 11px;">{{ $overtime->user->division->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-date" style="line-height: 1.2;">
                                        <div>{{ $overtime->overtime_date->format('d M Y') }}</div>
                                        <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">
                                            {{ $overtime->start_time->format('H:i') }} - {{ $overtime->end_time->format('H:i') }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-type badge-green">
                                        {{ $overtime->duration_human }}
                                    </span>
                                </td>
                                <td>
                                    <div class="approver-info">
                                        <strong>{{ $overtime->supervisorApprover->name ?? '-' }}</strong>
                                        @if($overtime->approved_by_supervisor_at)
                                            <br><span style="font-size: 10px; color: #6b7280;">{{ $overtime->approved_by_supervisor_at->format('d M H:i') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('hr.overtime-requests.show', $overtime->id) }}" class="btn-action">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">
                                    Tidak ada pengajuan lembur yang perlu disetujui.
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
        .text-muted { color: #6b7280; font-size: 11px; }
        .text-right { text-align: right; }
        .text-date { font-weight: 500; color: #1f2937; font-size: 12px; }
        
        .text-truncate {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 12px;
            color: #4b5563;
        }

        /* --- ALERT --- */
        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #a7f3d0;
            margin-bottom: 16px;
            font-size: 14px;
        }

        /* --- CARD --- */
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid #f3f4f6;
            overflow: hidden;
            padding: 0;
            margin-bottom: 20px;
        }

        .card-header-simple {
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            background: #fff;
        }
        
        .card-title-sm { margin: 0; font-size: 16px; font-weight: 700; color: #1f2937; }
        .card-subtitle-sm { margin: 4px 0 0; font-size: 13px; color: #6b7280; }

        /* --- TABLE --- */
        .table-wrapper { 
            width: 100%; 
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .custom-table { width: 100%; border-collapse: collapse; }

        .custom-table th {
            background: #f9fafb;
            padding: 10px 12px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e5e7eb;
        }

        .custom-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 12px;
            color: #1f2937;
            vertical-align: middle;
        }
        .custom-table tr:last-child td { border-bottom: none; }
        .custom-table tr:hover td { background: #fdfdfd; }

        /* --- USER INFO --- */
        .user-info { display: flex; flex-direction: column; gap: 2px; text-align: left; align-items: flex-start; justify-content: flex-start; }

        /* --- APPROVER INFO --- */
        .approver-info {
            font-size: 11px; 
            color: #4b5563; 
            margin-top: 0; 
            line-height: 1.3;
        }

        /* --- BADGES --- */
        .badge-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        
        /* Warna Badge */
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-gray { background: #f3f4f6; color: #374151; }   
        
        /* --- ACTION BUTTONS --- */
        .btn-action {
            padding: 4px 12px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }
        .btn-action:hover { background: #f3f4f6; border-color: #9ca3af; }

        .empty-state { padding: 40px; text-align: center; color: #9ca3af; font-style: italic; }

        /* --- RESPONSIVE CARD VIEW --- */
        @media screen and (max-width: 768px) {
            .table-wrapper {
                background: transparent;
            }
            
            .custom-table, 
            .custom-table tbody, 
            .custom-table tr, 
            .custom-table td {
                display: block;
                width: 100%;
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

            /* Rewrite Layout for Card */
            
            /* 1. Header Card: Nama & Status (Top Row) */
            .custom-table td:nth-child(1) { /* Karyawan */
                margin-bottom: 8px;
                padding-right: 60px; /* Space for action button if needed */
            }
            .custom-table td:nth-child(1) .user-info .fw-bold { font-size: 15px; }
            
            /* 2. Dates & Duration (Grid Layout) */
            .custom-table td:nth-child(2), /* Tanggal */
            .custom-table td:nth-child(3) { /* Durasi */
                display: inline-block;
                width: auto;
                margin-right: 12px;
                font-size: 12.5px;
                color: #4b5563;
                background: #f9fafb;
                padding: 4px 8px;
                border-radius: 6px;
                margin-bottom: 6px;
            }
            
            .custom-table td:nth-child(2)::before { content: '📅 '; }
            
            /* 3. Supervisor Info */
            .custom-table td:nth-child(4) {
                margin-top: 8px;
                font-size: 12px;
                color: #6b7280;
                padding: 8px 12px;
                background: #f3f4f6;
                border-radius: 8px;
            }
            .approver-info::before { content: 'Approver: '; font-weight: 600; }

            /* 4. Action Button */
            .custom-table td:last-child {
                position: absolute;
                top: 16px;
                right: 16px;
                padding: 0;
                margin: 0;
                border: none;
            }
            .btn-action {
                border: 1px solid #e5e7eb;
                background: #fff; 
                color: #1e4a8d;
                font-size: 11px;
                padding: 4px 10px;
            }
            .btn-action:hover { background: #eff6ff; }
            
            /* Hide empty state row correctly */
            .custom-table tr:has(.empty-state) {
                text-align: center;
                padding: 40px 20px;
            }
        }
    </style>
</x-app>
