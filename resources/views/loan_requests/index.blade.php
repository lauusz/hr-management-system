<x-app title="Pengajuan Hutang Saya">

    @if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="header-info">
                <h3 class="card-title">Riwayat Hutang</h3>
                <p class="card-subtitle">Daftar pengajuan pinjaman Anda ke perusahaan.</p>
            </div>
            <a href="{{ route('employee.loan_requests.create') }}" class="btn-add">
                + Ajukan Hutang
            </a>
        </div>

        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="min-width: 140px;">Tanggal Pengajuan</th>
                        <th>Besar Pinjaman</th>
                        <th>Jangka Waktu</th>
                        <th>Metode Bayar</th>
                        <th>Status</th>
                        <th class="text-right" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $loan)
                        @php
                            // Logic Status Badge
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
                            } elseif ($st === 'LUNAS') {
                                $badgeClass = 'badge-green';
                                $statusLabel = 'Lunas';
                            }

                            // Logic Metode Label
                            $method = $loan->payment_method;
                            $methodLabel = '-';
                            if ($method === 'TUNAI') $methodLabel = 'Tunai';
                            elseif ($method === 'CICILAN') $methodLabel = 'Cicilan';
                            elseif ($method === 'POTONG_GAJI') $methodLabel = 'Potong Gaji';
                        @endphp

                        <tr>
                            <td>
                                <div class="date-block">
                                    <span class="main-date">{{ \Illuminate\Support\Carbon::parse($loan->submitted_at)->format('d M Y') }}</span>
                                    <span class="sub-date">Pukul {{ $loan->created_at->format('H:i') }}</span>
                                </div>
                            </td>

                            <td>
                                <span class="money-amount">Rp {{ number_format($loan->amount, 0, ',', '.') }}</span>
                            </td>

                            <td>
                                @if($loan->repayment_term)
                                    {{ $loan->repayment_term }} Bulan
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td>
                                <span class="badge-basic">{{ $methodLabel }}</span>
                            </td>

                            <td>
                                <div style="display:flex; flex-direction:column; gap:2px;">
                                    <span class="badge-status {{ $badgeClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                    @if($loan->hrd_decided_at)
                                        <span class="sub-date">Diproses: {{ $loan->hrd_decided_at->format('d/m/y') }}</span>
                                    @endif
                                </div>
                            </td>

                            <td class="text-right">
                                <a href="{{ route('employee.loan_requests.show', $loan->id) }}" class="btn-action">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                Belum ada riwayat pengajuan hutang.
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
        .fw-bold { font-weight: 600; color: #111827; }
        .text-muted { color: #9ca3af; font-size: 13px; }
        .text-right { text-align: right; }

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

        .header-info h3 { margin: 0; font-size: 16px; font-weight: 700; color: #1f2937; }
        .header-info p { margin: 2px 0 0 0; font-size: 13px; color: #6b7280; }

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
            vertical-align: top;
        }
        .custom-table tr:last-child td { border-bottom: none; }
        .custom-table tr:hover td { background: #fdfdfd; }

        /* --- CONTENT STYLES --- */
        .date-block { display: flex; flex-direction: column; }
        .main-date { font-weight: 500; color: #111827; }
        .sub-date { font-size: 11px; color: #9ca3af; }

        .money-amount { font-weight: 700; color: #1e4a8d; font-size: 14px; }

        /* --- BADGES --- */
        .badge-basic {
            background: #f3f4f6;
            color: #374151;
            padding: 3px 8px;
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
            align-self: flex-start;
        }
        
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .badge-gray { background: #f3f4f6; color: #374151; }

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
    </style>

</x-app>