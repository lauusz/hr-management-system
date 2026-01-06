<x-app title="Master Hutang Karyawan">

    @if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="card mb-4">
        <div class="card-header-simple">
            <h4 class="card-title-sm">Filter Data</h4>
            <p class="card-subtitle-sm">Daftar pengajuan pinjaman/kasbon karyawan.</p>
        </div>

        <form method="GET" action="{{ route('hr.loan_requests.index') }}" class="filter-container">
            
            <div class="filter-group">
                <label>Status Pengajuan</label>
                <select name="status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="PENDING_HRD" @selected(request('status') === 'PENDING_HRD')>Menunggu HRD</option>
                    <option value="APPROVED" @selected(request('status') === 'APPROVED')>Disetujui HRD</option>
                    <option value="REJECTED" @selected(request('status') === 'REJECTED')>Ditolak HRD</option>
                </select>
            </div>

            <div class="filter-group flex-grow">
                <label>Cari Karyawan</label>
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}" 
                       placeholder="Nama atau NIK..." 
                       class="form-control">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary">Filter</button>
                
                @if(request('status') || request('q'))
                <a href="{{ route('hr.loan_requests.index') }}" class="btn-reset">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="min-width: 220px;">Karyawan</th>
                        <th>Besar Pinjaman</th>
                        <th>Metode Bayar</th>
                        <th>Tgl Pengajuan</th>
                        <th>Status</th>
                        <th class="text-right" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $loan)
                        @php
                            // Logic Badge Status
                            $st = $loan->status;
                            $badgeClass = 'badge-gray';
                            $statusLabel = $st;

                            if ($st === 'PENDING_HRD') {
                                $badgeClass = 'badge-yellow';
                                $statusLabel = 'Menunggu HRD';
                            } elseif ($st === 'APPROVED') {
                                $badgeClass = 'badge-green';
                                $statusLabel = 'Disetujui';
                            } elseif ($st === 'REJECTED') {
                                $badgeClass = 'badge-red';
                                $statusLabel = 'Ditolak';
                            }

                            // Logic Payment Method
                            $method = $loan->payment_method;
                            if ($method === 'TUNAI') $method = 'Tunai';
                            elseif ($method === 'CICILAN') $method = 'Cicilan';
                            elseif ($method === 'POTONG_GAJI') $method = 'Potong Gaji';
                        @endphp

                        <tr>
                            <td>
                                <div class="user-info">
                                    <span class="fw-bold">{{ $loan->snapshot_name }}</span>
                                    <div class="sub-info">
                                        <span>NIK: {{ $loan->snapshot_nik ?? '-' }}</span>
                                        <span class="dot">•</span>
                                        <span>{{ $loan->snapshot_position ?? '-' }}</span>
                                    </div>
                                    <span class="sub-company">{{ $loan->snapshot_company ?? '-' }}</span>
                                </div>
                            </td>

                            <td>
                                <div class="money-block">
                                    <span class="money-amount">Rp {{ number_format($loan->amount, 0, ',', '.') }}</span>
                                    <span class="money-words text-truncate" style="max-width: 150px;" title="{{ $loan->amount_in_words }}">
                                        {{ $loan->amount_in_words }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="badge-basic">{{ $method }}</span>
                            </td>

                            <td>
                                <div class="date-block">
                                    <span class="main-date">{{ \Illuminate\Support\Carbon::parse($loan->submitted_at)->format('d M Y') }}</span>
                                </div>
                            </td>

                            <td>
                                <span class="badge-status {{ $badgeClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <td class="text-right">
                                <a href="{{ route('hr.loan_requests.show', $loan->id) }}" class="btn-action">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                Belum ada pengajuan hutang yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 20px;">
        @if(method_exists($loans, 'links'))
            {{ $loans->links() }}
        @endif
    </div>

    <style>
        /* --- UTILITY --- */
        .mb-4 { margin-bottom: 16px; }
        .fw-bold { font-weight: 600; color: #111827; }
        .text-muted { color: #6b7280; font-size: 13px; }
        .text-right { text-align: right; }
        .text-truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; }

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
        .filter-group.flex-grow { flex: 2; min-width: 200px; }

        .filter-group label {
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
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
            padding: 14px 16px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13.5px;
            color: #1f2937;
            vertical-align: top; /* Align top karena konten bertumpuk */
        }
        .custom-table tr:last-child td { border-bottom: none; }
        .custom-table tr:hover td { background: #fdfdfd; }

        /* --- CONTENT STYLING --- */
        .user-info { display: flex; flex-direction: column; gap: 3px; }
        .sub-info { font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 4px; flex-wrap: wrap;}
        .sub-company { font-size: 11px; color: #9ca3af; font-style: italic; }
        .dot { color: #d1d5db; }

        .money-block { display: flex; flex-direction: column; gap: 2px; }
        .money-amount { font-weight: 700; color: #1e4a8d; font-size: 14px; }
        .money-words { font-size: 11px; color: #6b7280; font-style: italic; }

        .date-block { display: flex; flex-direction: column; }
        .main-date { color: #374151; font-weight: 500; }

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

</x-app>