<x-app title="Izin / Cuti Saya">

    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('ok'))
        <div class="alert-success">
            {{ session('ok') }}
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header-simple">
            <h4 class="card-title-sm">Filter Data</h4>
            <p class="card-subtitle-sm">Cari riwayat pengajuan izin/cuti Anda.</p>
        </div>

        <form method="GET" action="{{ route('leave-requests.index') }}" class="filter-container">
            
            <div class="filter-group">
                <label>Tanggal Pengajuan</label>
                <input type="text"
                    id="submitted_range"
                    name="submitted_range"
                    value="{{ $submittedRange ?? '' }}"
                    placeholder="Pilih rentang tanggal"
                    class="form-control"
                    autocomplete="off">
            </div>

            <div class="filter-group">
                <label>Jenis Pengajuan</label>
                <select name="type" class="form-control">
                    <option value="">Semua Jenis</option>
                    @foreach($typeOptions as $case)
                        @php
                            $val = $case->value;
                            $lbl = ($val === \App\Enums\LeaveType::CUTI_KHUSUS->value) ? 'Cuti Khusus' : $case->label();
                        @endphp
                        <option value="{{ $val }}" @selected($typeFilter === $val)>
                            {{ $lbl }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary">Filter</button>
                
                @if(($submittedRange ?? null) || ($typeFilter ?? null))
                <a href="{{ route('leave-requests.index') }}" class="btn-reset">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="header-info">
                <h3 class="card-title">Riwayat Pengajuan</h3>
                <p class="card-subtitle">Daftar izin dan cuti yang pernah Anda ajukan.</p>
            </div>
            <a href="{{ route('leave-requests.create') }}" class="btn-add">
                + Buat Pengajuan
            </a>
        </div>

        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="min-width: 160px;">Tanggal Pengajuan</th>
                        <th>Jenis</th>
                        <th style="min-width: 180px;">Periode Izin</th>
                        <th>Tracking Status</th>
                        <th class="text-right" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $row)
                        @php
                            // 1. Logic Status Badge & Label (Disesuaikan dengan Hierarki Baru)
                            $st = $row->status;
                            $badgeClass = 'badge-gray';
                            $statusLabel = $row->status; // Default fallback

                            if ($st === \App\Models\LeaveRequest::STATUS_APPROVED) {
                                $badgeClass = 'badge-green';
                                $statusLabel = 'Disetujui HRD';
                            } elseif ($st === \App\Models\LeaveRequest::STATUS_REJECTED) {
                                $badgeClass = 'badge-red';
                                $statusLabel = 'Ditolak';
                            } elseif ($st === \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
                                // Pending Approval Atasan Langsung (Bisa SPV atau Manager)
                                $badgeClass = 'badge-yellow';
                                $statusLabel = '⏳ Menunggu Persetujuan Atasan';
                            } elseif ($st === \App\Models\LeaveRequest::PENDING_HR) {
                                // Sudah di-ACC Atasan, OTW HRD
                                $badgeClass = 'badge-teal';
                                $statusLabel = '✅ Atasan Mengetahui';
                            }

                            // 2. Logic Label Jenis
                            $typeLabel = \Illuminate\Support\Str::contains($row->type_label, 'Cuti Khusus') ? 'Cuti Khusus' : $row->type_label;
                        @endphp

                        <tr>
                            <td>
                                <div class="date-block">
                                    <span class="main-date">{{ $row->created_at->format('d M Y') }}</span>
                                    <span class="sub-date">Pukul {{ $row->created_at->format('H:i') }}</span>
                                </div>
                            </td>

                            <td>
                                <span class="badge-basic">{{ $typeLabel }}</span>
                            </td>

                            <td>
                                <span class="text-date">
                                    {{ $row->start_date->format('d M Y') }}
                                    @if($row->end_date && $row->end_date->ne($row->start_date))
                                        – {{ $row->end_date->format('d M Y') }}
                                    @endif
                                </span>
                            </td>

                            <td>
                                <span class="badge-status {{ $badgeClass }}">
                                    {{ $statusLabel }}
                                </span>
                                {{-- Tambahan Info Text untuk status PENDING_HR --}}
                                @if($st === \App\Models\LeaveRequest::PENDING_HR)
                                    <div style="font-size:10px; color:#0d9488; margin-top:2px;">(Menunggu Verifikasi HRD)</div>
                                @endif
                            </td>

                            <td class="text-right">
                                <a href="{{ route('leave-requests.show', $row) }}" class="btn-action">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                Belum ada riwayat pengajuan izin/cuti.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 20px;">
        <x-pagination :items="$items" />
    </div>

    <style>
        /* --- UTILITY --- */
        .mb-4 { margin-bottom: 16px; }
        .text-right { text-align: right; }
        .text-date { font-weight: 500; color: #1f2937; font-size: 13.5px; }

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

        .card-header {
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f3f4f6;
            background: #fff;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .card-header-simple {
            padding: 16px 20px 0;
            margin-bottom: 8px;
        }

        .header-info h3 { margin: 0; font-size: 16px; font-weight: 700; color: #1f2937; }
        .header-info p { margin: 2px 0 0 0; font-size: 13px; color: #6b7280; }
        
        .card-title-sm { margin: 0; font-size: 15px; font-weight: 700; color: #1f2937; }
        .card-subtitle-sm { margin: 2px 0 0; font-size: 13px; color: #6b7280; }

        .btn-add {
            padding: 8px 16px;
            border-radius: 8px;
            background: #1e4a8d;
            color: #fff;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .btn-add:hover { background: #163a75; }

        /* --- FILTER SECTION --- */
        .filter-container {
            padding: 16px 20px 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
        }

        .filter-group { flex: 1; min-width: 160px; display: flex; flex-direction: column; gap: 4px; }
        
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
        .table-wrapper { width: 100%; overflow-x: auto; }
        .custom-table { width: 100%; border-collapse: collapse; min-width: 800px; }

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
            vertical-align: middle;
        }
        .custom-table tr:last-child td { border-bottom: none; }
        .custom-table tr:hover td { background: #fdfdfd; }

        /* --- CONTENT STYLES --- */
        .date-block { display: flex; flex-direction: column; }
        .main-date { font-weight: 500; color: #111827; }
        .sub-date { font-size: 11px; color: #9ca3af; }

        /* --- BADGES --- */
        .badge-basic {
            background: #f3f4f6;
            color: #374151;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid #e5e7eb;
            display: inline-block;
        }

        .badge-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            white-space: nowrap;
        }
        
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        /* [BARU] Badge Teal untuk 'Atasan Mengetahui' */
        .badge-teal { background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; }

        /* --- ACTION BUTTON --- */
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

        @media(max-width: 768px) {
            .filter-container { flex-direction: column; align-items: stretch; gap: 12px; }
            .filter-group, .form-control { width: 100%; min-width: 0; }
            .filter-actions { margin-top: 4px; }
            .btn-primary, .btn-reset { flex: 1; text-align: center; }
        }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#submitted_range", {
            mode: "range",
            dateFormat: "Y-m-d",
            allowInput: true,
            locale: { rangeSeparator: " sampai " }
        });
    </script>

</x-app>