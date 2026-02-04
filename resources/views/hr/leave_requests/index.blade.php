<x-app title="Pengajuan Menunggu HRD">

    @if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="card">
        <div class="card-header-simple">
            <h4 class="card-title-sm">Menunggu Approval</h4>
            <p class="card-subtitle-sm">Daftar pengajuan yang membutuhkan persetujuan Anda.</p>
        </div>

        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="min-width: 160px;">Karyawan</th>
                        <th>Jenis</th>
                        <th>Status</th> 
                        <th style="min-width: 120px;">Periode Izin</th>
                        <th>Tgl Pengajuan</th>
                        <th style="min-width: 150px;">Alasan</th>
                        <th class="text-right" style="width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $lv)
                        @php
                            // --- 1. LOGIC WARNA BADGE JENIS CUTI ---
                            $type = $lv->type;
                            $badgeClass = 'badge-gray';
                            
                            if (in_array($type, [
                                \App\Enums\LeaveType::CUTI->value, 
                                \App\Enums\LeaveType::CUTI_KHUSUS->value
                            ])) {
                                $badgeClass = 'badge-blue'; 
                            } 
                            elseif ($type === \App\Enums\LeaveType::SAKIT->value) {
                                $badgeClass = 'badge-yellow'; 
                            } 
                            elseif (in_array($type, [
                                \App\Enums\LeaveType::IZIN_TELAT->value, 
                                \App\Enums\LeaveType::IZIN_PULANG_AWAL->value,
                                \App\Enums\LeaveType::IZIN_TENGAH_KERJA->value,
                                \App\Enums\LeaveType::IZIN->value
                            ])) {
                                $badgeClass = 'badge-orange';
                            }
                            elseif ($type === \App\Enums\LeaveType::DINAS_LUAR->value) {
                                $badgeClass = 'badge-purple';
                            }

                            // --- 2. LOGIC TRACKING STATUS (UPDATED) ---
                            $statusBadge = 'badge-gray';
                            $statusLabel = $lv->status;

                            if ($lv->status == \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
                                $statusBadge = 'badge-yellow';
                                $statusLabel = '⏳ Menunggu Persetujuan Atasan';
                            } 
                            elseif ($lv->status == \App\Models\LeaveRequest::PENDING_HR) {
                                // Status utama di inbox HRD (Sudah lolos atasan)
                                $statusBadge = 'badge-teal'; 
                                $statusLabel = '✅ Atasan Mengetahui'; 
                            } 
                            elseif ($lv->status == \App\Models\LeaveRequest::STATUS_APPROVED) {
                                $statusBadge = 'badge-green';
                                $statusLabel = 'Disetujui';
                            } 
                            elseif ($lv->status == \App\Models\LeaveRequest::STATUS_REJECTED) {
                                $statusBadge = 'badge-red';
                                $statusLabel = 'Ditolak';
                            }
                        @endphp

                        <tr>
                            <td>
                                <div class="user-info">
                                    <span class="fw-bold" style="font-size: 0.9rem;">{{ $lv->user->name }}</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">{{ $lv->user->division->name ?? '-' }}</span>
                                </div>
                            </td>

                            <td>
                                <span class="badge-type {{ $badgeClass }}">
                                    {{ $lv->type_label ?? $lv->type }}
                                </span>
                            </td>

                            {{-- Kolom Tracking Status --}}
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <span class="badge-type {{ $statusBadge }}">
                                        {{ $statusLabel }}
                                    </span>
                                    
                                    {{-- [MODIFIKASI] Menampilkan Nama Atasan (SPV atau Manager) --}}
                                    @if($lv->status == \App\Models\LeaveRequest::PENDING_SUPERVISOR)
                                        <div class="approver-info">
                                            Menunggu: 
                                            <strong>
                                                {{ $lv->user->directSupervisor->name ?? $lv->user->manager->name ?? '-' }}
                                            </strong>
                                        </div>
                                    @endif

                                    @if($lv->status == \App\Models\LeaveRequest::PENDING_HR)
                                        <div class="info-verifikasi">(Menunggu Verifikasi HRD)</div>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <div class="text-date" style="line-height: 1.2;">
                                    <div>{{ $lv->start_date->format('d M Y') }}</div>
                                    @if($lv->end_date && $lv->end_date->ne($lv->start_date))
                                        <div style="font-size: 0.75rem; color: #6b7280; margin-top: 2px;">
                                            s/d {{ $lv->end_date->format('d M Y') }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <span class="text-muted" style="font-size: 0.8rem;">
                                    {{ $lv->created_at->format('d/m/Y') }}<br>
                                    {{ $lv->created_at->format('H:i') }}
                                </span>
                            </td>

                            <td>
                                <div class="text-truncate" title="{{ $lv->reason }}">
                                    {{ Str::limit($lv->reason, 40) }}
                                </div>
                            </td>

                            <td class="text-right">
                                <a href="{{ route('hr.leave.show', $lv) }}" class="btn-action">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                Tidak ada pengajuan yang menunggu HRD saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 20px;">
        <x-pagination :items="$leaves" />
    </div>

    <style>
        /* --- UTILITY --- */
        .fw-bold { font-weight: 600; color: #111827; }
        .text-muted { color: #6b7280; font-size: 13px; }
        .text-right { text-align: right; }
        .text-date { font-weight: 500; color: #1f2937; font-size: 13.5px; }
        
        .text-truncate {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 13.5px;
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
        }

        .card-header-simple {
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            background: #fff;
        }
        
        .card-title-sm { margin: 0; font-size: 16px; font-weight: 700; color: #1f2937; }
        .card-subtitle-sm { margin: 4px 0 0; font-size: 13px; color: #6b7280; }

        /* --- TABLE --- */
        .table-wrapper { width: 100%; }
        .custom-table { width: 100%; border-collapse: collapse; }

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
            padding: 14px 16px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13.5px;
            color: #1f2937;
            vertical-align: top;
        }
        .custom-table tr:last-child td { border-bottom: none; }
        .custom-table tr:hover td { background: #fdfdfd; }

        /* --- USER INFO --- */
        .user-info { display: flex; flex-direction: column; gap: 2px; }

        /* --- APPROVER INFO --- */
        .approver-info {
            font-size: 11px; 
            color: #6b7280; 
            margin-top: 6px; 
            line-height: 1.3;
        }
        .info-verifikasi {
            font-size: 10px; 
            color: #0d9488; 
            margin-top: 2px;
        }

        /* --- BADGES --- */
        .badge-type {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        
        /* Warna Badge Jenis Cuti */
        .badge-blue { background: #eff6ff; color: #1d4ed8; }   
        .badge-yellow { background: #fefce8; color: #a16207; } 
        .badge-orange { background: #fff7ed; color: #c2410c; } 
        .badge-purple { background: #f3e8ff; color: #7e22ce; } 
        .badge-gray { background: #f3f4f6; color: #374151; }   
        
        /* Warna Badge Status Baru */
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        
        /* Badge Teal untuk 'Atasan Mengetahui' */
        .badge-teal { background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; }

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
                padding-right: 60px; /* Space for action button if needed, or status */
            }
            .custom-table td:nth-child(1) .user-info .fw-bold { font-size: 15px; }
            
            /* 2. Status Badge (Absolute Positioned or Flex) */
            .custom-table td:nth-child(3) { /* Status */
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 12px;
            }

            /* 3. Type & Dates (Grid Layout) */
            .custom-table td:nth-child(2), /* Jenis */
            .custom-table td:nth-child(4), /* Periode */
            .custom-table td:nth-child(5) { /* Tgl Pengajuan */
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
            
            /* Add Labels for context */
            .custom-table td:nth-child(4)::before { content: '📅 '; }
            .custom-table td:nth-child(5)::before { content: 'Submitted: '; opacity: 0.7; }

            /* 4. Reason */
            .custom-table td:nth-child(6) {
                margin-top: 8px;
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
                margin-top: 12px;
                text-align: right;
                border-top: 1px solid #f3f4f6;
                padding-top: 12px;
            }
            .btn-action {
                width: 100%;
                text-align: center;
                background: var(--navy);
                color: #fff;
                border: none;
            }
            .btn-action:hover { background: #1e40af; }
            
            /* Hide empty state row correctly */
            .custom-table tr:has(.empty-state) {
                text-align: center;
                padding: 40px 20px;
            }
        }    </style>

</x-app>