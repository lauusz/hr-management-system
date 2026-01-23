<x-app title="Master Izin / Cuti">

    @if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="card mb-4">
        <div class="card-header-simple">
            <h4 class="card-title-sm">Filter Data</h4>
            <p class="card-subtitle-sm">Cari riwayat pengajuan izin dan cuti karyawan.</p>
        </div>
        
        <form method="GET" action="{{ route('hr.leave.master') }}" class="filter-container">
            <div class="filter-group">
                <label>Tanggal Pengajuan</label>
                <input type="text"
                    id="submitted_range"
                    name="submitted_range"
                    value="{{ $submittedRange ?? '' }}"
                    placeholder="Rentang tanggal..."
                    class="form-control"
                    autocomplete="off">
            </div>

            <div class="filter-group">
                <label>Jenis</label>
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

            @php
                $statusLabels = [
                    \App\Models\LeaveRequest::PENDING_SUPERVISOR => 'Menunggu Supervisor',
                    \App\Models\LeaveRequest::PENDING_HR         => 'Menunggu HRD',
                    \App\Models\LeaveRequest::STATUS_APPROVED    => 'Disetujui',
                    \App\Models\LeaveRequest::STATUS_REJECTED    => 'Ditolak',
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
                
                @if(($q ?? null) || ($typeFilter ?? null) || ($status ?? null) || ($submittedRange ?? null))
                <a href="{{ route('hr.leave.master') }}" class="btn-reset">Reset</a>
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
                        <th>Periode Izin</th>
                        <th>Jenis</th>
                        <th>Status</th>
                        <th class="text-right" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $i => $row)
                        @php
                            // Logic Badge Status
                            $st = $row->status;
                            $badgeClass = 'badge-gray'; // Default
                            
                            if ($st === \App\Models\LeaveRequest::STATUS_APPROVED) {
                                $badgeClass = 'badge-green';
                            } elseif ($st === \App\Models\LeaveRequest::STATUS_REJECTED) {
                                $badgeClass = 'badge-red';
                            } elseif (in_array($st, [\App\Models\LeaveRequest::PENDING_SUPERVISOR, \App\Models\LeaveRequest::PENDING_HR])) {
                                $badgeClass = 'badge-yellow';
                            }
                        @endphp

                        <tr>
                            <td class="text-muted" style="text-align: center;">
                                {{ $items->firstItem() + $i }}
                            </td>

                            <td>
                                <span class="fw-bold">{{ $row->user->name }}</span>
                            </td>

                            <td class="text-muted">
                                {{ $row->created_at?->format('d M Y H:i') ?? '-' }}
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
                                <span class="badge-basic">
                                    {{ \Illuminate\Support\Str::contains($row->type_label, 'Cuti Khusus') ? 'Cuti Khusus' : $row->type_label }}
                                </span>
                            </td>

                            <td>
                                <span class="badge-status {{ $badgeClass }}">
                                    {{ $row->status_label }}
                                </span>

                                {{-- [MODIFIKASI] Menampilkan Nama Atasan jika status PENDING_SUPERVISOR --}}
                                @if($row->status == \App\Models\LeaveRequest::PENDING_SUPERVISOR)
                                    <div class="approver-info">
                                        Menunggu: 
                                        <strong>
                                            {{-- Cek Direct SPV dulu, kalau null cek Manager, kalau null strip --}}
                                            {{ $row->user->directSupervisor->name ?? $row->user->manager->name ?? '-' }}
                                        </strong>
                                    </div>
                                @endif
                            </td>

                            <td class="text-right">
                                <a href="{{ route('hr.leave.show', $row) }}" class="btn-action">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                Belum ada data pengajuan izin/cuti.
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
        .fw-bold { font-weight: 600; color: #111827; }
        .text-muted { color: #6b7280; font-size: 13px; }
        .text-right { text-align: right; }
        .text-date { font-weight: 500; color: #1f2937; }

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
        .table-wrapper { width: 100%; overflow-x: auto; }
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
            vertical-align: top; /* [MODIFIKASI] Diubah ke top agar teks 'Menunggu...' rapi */
        }
        .custom-table tr:last-child td { border-bottom: none; }
        .custom-table tr:hover td { background: #fdfdfd; }

        /* --- APPROVER INFO (NEW) --- */
        .approver-info {
            font-size: 11px; 
            color: #6b7280; 
            margin-top: 6px; 
            line-height: 1.3;
        }

        /* --- BADGES --- */
        .badge-basic {
            background: #f3f4f6;
            color: #374151;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid #e5e7eb;
        }

        .badge-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-green { background: #dcfce7; color: #166534; }   /* Approved */
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
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#submitted_range", {
                mode: "range",
                dateFormat: "Y-m-d",
                allowInput: true,
                locale: { rangeSeparator: " sampai " }
            });
        });
    </script>

</x-app>