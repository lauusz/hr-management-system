<x-app :title="$pageTitle ?? 'Master Data Lembur'">
    <div class="container mx-auto px-4 py-6">
        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header-simple">
                <h4 class="card-title-sm">Filter Data</h4>
                <p class="card-subtitle-sm">Menampilkan data lembur berdasarkan periode yang dipilih.</p>
            </div>
            
            <form method="GET" action="{{ route('hr.overtime-requests.master') }}" class="filter-container">
                <div class="filter-group">
                    <label>Tanggal Lembur</label>
                    <input type="text"
                        id="overtime_date_range"
                        name="overtime_date_range"
                        value="{{ $overtimeDateRange ?? '' }}"
                        placeholder="Rentang tanggal..."
                        class="form-control"
                        autocomplete="off">
                </div>

                @php
                    $statusLabels = [
                        \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR => 'Menunggu Supervisor',
                        \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR => 'Disetujui Supervisor',
                        \App\Models\OvertimeRequest::STATUS_APPROVED_HRD => 'Disetujui HRD',
                        \App\Models\OvertimeRequest::STATUS_REJECTED => 'Ditolak',
                        \App\Models\OvertimeRequest::STATUS_CANCELLED => 'Dibatalkan',
                    ];
                @endphp
                <div class="filter-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        @foreach($statusOptions as $opt)
                            <option value="{{ $opt }}" @selected($status === $opt)>
                                {{ $statusLabels[$opt] ?? $opt }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group flex-grow">
                    <label>Karyawan</label>
                    <input type="text"
                           name="q"
                           value="{{ $q ?? '' }}"
                           placeholder="Cari nama karyawan..."
                           class="form-control">
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-primary">Filter</button>
                    
                    @if(($q ?? null) || ($status ?? null) || ($overtimeDateRange ?? null))
                    <a href="{{ route('hr.overtime-requests.master') }}" class="btn-reset">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="card">
            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Karyawan</th>
                            <th>Tgl Pengajuan</th>
                            <th>Tanggal Lembur</th>
                            <th>Waktu & Durasi</th>
                            <th>Status</th>
                            <th class="text-right" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overtimes as $i => $overtime)
                            @php
                                $statusColor = match($overtime->status) {
                                    \App\Models\OvertimeRequest::STATUS_APPROVED_HRD => 'badge-green',
                                    \App\Models\OvertimeRequest::STATUS_REJECTED => 'badge-red',
                                    \App\Models\OvertimeRequest::STATUS_CANCELLED => 'badge-red', 
                                    \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR => 'badge-blue',
                                    default => 'badge-yellow',
                                };
                            @endphp
                            <tr>
                                <td class="text-muted" style="text-align: center;">
                                    {{ $overtimes->firstItem() + $i }}
                                </td>
                                <td>
                                    <div class="user-info">
                                        <span class="fw-bold">{{ $overtime->user->name }}</span>
                                        <span class="text-muted" style="font-size: 11px;">{{ $overtime->user->division->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="text-muted" style="font-size: 11px;">
                                    {{ $overtime->created_at->format('d/m/Y') }}<br>
                                    {{ $overtime->created_at->format('H:i') }}
                                </td>
                                <td>
                                    <div class="text-date">{{ $overtime->overtime_date->format('d M Y') }}</div>
                                </td>
                                <td>
                                    <div class="text-date" style="font-size: 12px;">
                                        {{ $overtime->start_time->format('H:i') }} - {{ $overtime->end_time->format('H:i') }}
                                    </div>
                                    <div style="font-size: 11px; color: #6b7280; margin-top: 2px; font-weight: 600;">
                                        {{ $overtime->duration_human }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-status {{ $statusColor }}">
                                        {{ $overtime->status_label }}
                                    </span>
                                    @if($overtime->status === \App\Models\OvertimeRequest::STATUS_REJECTED && $overtime->rejection_note)
                                        <div style="font-size: 10px; color: #dc2626; margin-top: 4px; max-width: 150px; line-height: 1.2;">
                                            Note: {{ $overtime->rejection_note }}
                                        </div>
                                    @endif
                                    
                                    @if($overtime->status == \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR)
                                        <div class="approver-info">
                                            Menunggu: <strong>{{ $overtime->user->directSupervisor->name ?? '-' }}</strong>
                                        </div>
                                    @elseif($overtime->status == \App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR)
                                        <div class="approver-info">
                                            Approved by: <strong>{{ $overtime->supervisorApprover->name ?? '-' }}</strong>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('hr.overtime-requests.show', $overtime->id) }}" class="btn-action">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">
                                    Belum ada data lembur.
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
        .mb-4 { margin-bottom: 16px; }
        .fw-bold { font-weight: 600; color: #111827; }
        .text-muted { color: #6b7280; font-size: 13px; }
        .text-right { text-align: right; }
        .text-date { font-weight: 500; color: #1f2937; font-size: 13px; }

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
        }

        .card-header-simple {
            padding: 16px 20px 0;
            margin-bottom: 8px;
        }
        
        .card-title-sm { margin: 0; font-size: 15px; font-weight: 700; color: #1f2937; }
        .card-subtitle-sm { margin: 2px 0 0; font-size: 13px; color: #6b7280; }

        /* --- FILTER SECTION --- */
        .filter-container {
            padding: 16px 20px 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
        }

        .filter-group { flex: 1; min-width: 160px; display: flex; flex-direction: column; gap: 4px; }
        .filter-group.flex-grow { flex: 1.5; min-width: 200px; }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
        }

        .form-control {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 13.5px;
            color: #374151;
            background: #fff;
            width: 100%;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-control:focus { border-color: #1e4a8d; }

        .filter-actions { display: flex; gap: 8px; padding-bottom: 2px; }

        .btn-primary {
            padding: 9px 18px;
            background: #1e4a8d;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13.5px;
            font-weight: 600;
            white-space: nowrap;
        }
        .btn-primary:hover { background: #163a75; }

        .btn-reset {
            padding: 9px 16px;
            background: #fff;
            color: #374151;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            display: inline-block;
        }
        .btn-reset:hover { background: #f9fafb; }

        /* --- TABLE --- */
        .table-wrapper { 
            width: 100%; 
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .custom-table { width: 100%; border-collapse: collapse; min-width: 900px; }

        .custom-table th {
            background: #f9fafb;
            padding: 12px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e5e7eb;
        }

        .custom-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13.5px;
            color: #1f2937;
            vertical-align: top;
        }
        .custom-table tr:last-child td { border-bottom: none; }
        .custom-table tr:hover td { background: #fdfdfd; }

        .user-info { display: flex; flex-direction: column; gap: 2px; }

        /* --- APPROVER INFO --- */
        .approver-info {
            font-size: 11px; 
            color: #6b7280; 
            margin-top: 4px; 
            line-height: 1.3;
        }

        /* --- BADGES --- */
        .badge-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-green { background: #dcfce7; color: #166534; }   /* Approved HRD */
        .badge-blue { background: #dbeafe; color: #1e40af; }    /* Approved SPV */
        .badge-red { background: #fee2e2; color: #991b1b; }     /* Rejected */
        .badge-yellow { background: #fef9c3; color: #854d0e; }  /* Pending */
        .badge-gray { background: #e5e7eb; color: #374151; }    /* Default */

        /* --- ACTION BUTTONS --- */
        .btn-action {
            padding: 6px 14px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }
        .btn-action:hover { background: #f3f4f6; border-color: #9ca3af; }

        .empty-state { padding: 40px; text-align: center; color: #9ca3af; font-style: italic; }

        @media(max-width: 1024px) {
            .filter-container { flex-direction: column; align-items: stretch; gap: 12px; }
            .filter-group, .form-control { width: 100%; min-width: 0; }
            .filter-actions { margin-top: 4px; }
            .btn-primary, .btn-reset { flex: 1; text-align: center; }

            /* --- RESPONSIVE CARD VIEW --- */
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
            
            /* Hide Index */
            .custom-table td:nth-child(1) { display: none; }

            /* Employee Name */
            .custom-table td:nth-child(2) {
                margin-bottom: 8px;
                padding-bottom: 8px;
                border-bottom: 1px solid #f3f4f6;
            }
            .custom-table td:nth-child(2) .fw-bold { font-size: 15px; }

            /* Status */
            .custom-table td:nth-child(6) {
                display: block;
                margin-bottom: 12px;
            }

            /* Dates */
            .custom-table td:nth-child(3),
            .custom-table td:nth-child(4), 
            .custom-table td:nth-child(5) {
                display: block;
                background: #f9fafb;
                padding: 6px 10px;
                border-radius: 6px;
                color: #4b5563;
                font-size: 13px;
                margin-bottom: 4px;
            }
            
            .custom-table td:nth-child(3)::before { content: 'Diajukan: '; }
            .custom-table td:nth-child(4)::before { content: 'Tgl Lembur: '; }
            
            /* Action */
            .custom-table td:last-child {
                border-top: 1px solid #f3f4f6;
                margin-top: 12px;
                padding-top: 12px;
                text-align: center;
            }
            .btn-action {
                display: block;
                width: 100%;
                background: #1e4a8d;
                color: #fff;
                border: none;
                padding: 8px;
            }
            
            .custom-table tr:has(.empty-state) {
                text-align: center;
                padding: 40px 20px;
            }
        }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#overtime_date_range", {
                mode: "range",
                dateFormat: "Y-m-d",
                minDate: "2020-01-01",
                locale: {
                    rangeSeparator: " sampai "
                }
            });
        });
    </script>
</x-app>
