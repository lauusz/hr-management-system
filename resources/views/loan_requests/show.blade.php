<x-app title="Detail Hutang Saya">

    @if(session('success'))
        <div class="alert-success">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert-error">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Header & Navigasi --}}
    <div class="page-header">
        <div>
            <p class="page-subtitle">Memantau status pengajuan dan riwayat pembayaran cicilan.</p>
        </div>
        <a href="{{ route('employee.loan_requests.index') }}" class="btn-back">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
            Kembali
        </a>
    </div>

    @php
        $months = $loan->repayment_term ? (int) $loan->repayment_term : 0;
        $monthlyInstallment = $months > 0 ? floor($loan->amount / $months) : null;
        $totalPaid = $loan->repayments->sum('amount');
        $remaining = max(0, $loan->amount - $totalPaid);
        $percentage = $loan->amount > 0 ? min(100, round(($totalPaid / $loan->amount) * 100)) : 0;

        // Status Logic
        $statusLabel = $loan->status;
        $badgeClass = 'badge-gray';

        if ($loan->status === 'PENDING_HRD') {
            $statusLabel = 'Menunggu HRD';
            $badgeClass = 'badge-yellow';
        } elseif ($loan->status === 'APPROVED') {
            $statusLabel = 'Disetujui HRD';
            $badgeClass = 'badge-blue';
        } elseif ($loan->status === 'REJECTED') {
            $statusLabel = 'Ditolak';
            $badgeClass = 'badge-red';
        } elseif ($loan->status === 'LUNAS') {
            $statusLabel = 'Lunas';
            $badgeClass = 'badge-green';
        }
    @endphp

    <div class="detail-grid">
        
        {{-- KOLOM KIRI: Detail Utama --}}
        <div class="left-column">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Data Pengajuan</h3>
                    <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                </div>
                
                <div class="divider"></div>
                
                <div class="info-group">

                    <div class="info-row">
                        <div class="label">Tanggal Pengajuan</div>
                        <div class="value">{{ \Illuminate\Support\Carbon::parse($loan->submitted_at)->format('d F Y') }}</div>
                    </div>

                    @if($loan->hrd_decided_at)
                    <div class="info-row">
                        <div class="label">Diproses Tanggal</div>
                        <div class="value">{{ $loan->hrd_decided_at->format('d F Y H:i') }}</div>
                    </div>
                    @endif

                    <div class="info-row">
                        <div class="label">Besar Pinjaman</div>
                        <div class="value highlight-text">Rp {{ number_format($loan->amount, 0, ',', '.') }}</div>
                    </div>

                    <div class="info-row">
                        <div class="label">Keperluan</div>
                        <div class="value">{{ $loan->purpose ?: '-' }}</div>
                    </div>

                    <div class="info-grid-2">
                        <div class="info-row">
                            <div class="label">Tanggal Cair</div>
                            <div class="value">
                                @if($loan->disbursement_date)
                                    {{ \Illuminate\Support\Carbon::parse($loan->disbursement_date)->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="label">Tenor</div>
                            <div class="value">
                                @if($months > 0) {{ $months }} Bulan @else - @endif
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="label">Estimasi Cicilan</div>
                        <div class="value">
                            @if($monthlyInstallment)
                                Rp {{ number_format($monthlyInstallment, 0, ',', '.') }} / bulan
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="label">Metode Pembayaran</div>
                        <div class="value">
                            @if($loan->payment_method === 'TUNAI')
                                Tunai
                            @elseif($loan->payment_method === 'CICILAN')
                                Transfer Bank
                            @elseif($loan->payment_method === 'POTONG_GAJI')
                                Potong Gaji
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    @if($loan->hrd_note)
                    <div class="note-box">
                        <strong>Catatan HRD:</strong><br>
                        {{ $loan->hrd_note }}
                    </div>
                    @endif

                    @if($loan->document_path)
                    <div class="info-row" style="margin-top:10px;">
                        <a href="{{ asset('storage/' . $loan->document_path) }}" target="_blank" class="btn-download">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Lihat Dokumen Pendukung
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: Ringkasan & History --}}
        <div class="right-column">
            
            {{-- Card Ringkasan Pembayaran --}}
            <div class="card summary-card">
                <h3 class="card-title-sm">Ringkasan Pembayaran</h3>
                
                <div class="progress-container">
                    <div class="progress-labels">
                        <span>Terbayar: {{ $percentage }}%</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>

                <div class="summary-list">
                    <div class="summary-item">
                        <span>Total Pinjaman</span>
                        <strong>Rp {{ number_format($loan->amount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="summary-item text-green">
                        <span>Sudah Dibayar</span>
                        <strong>- Rp {{ number_format($totalPaid, 0, ',', '.') }}</strong>
                    </div>
                    <div class="divider-dashed"></div>
                    <div class="summary-item total-item">
                        <span>Sisa Hutang</span>
                        <strong>Rp {{ number_format($remaining, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            {{-- Card Riwayat Cicilan --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title-sm">Riwayat Cicilan</h3>
                </div>
                <div class="table-responsive">
                    @if($loan->repayments->isEmpty())
                        <div class="empty-state">
                            <p>Belum ada data pembayaran cicilan.</p>
                        </div>
                    @else
                        <table class="table-history">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Nominal</th>
                                    <th>Metode</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($loan->repayments as $index => $repayment)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="date-main">{{ \Illuminate\Support\Carbon::parse($repayment->paid_at)->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="amount-cell">
                                        Rp {{ number_format($repayment->amount, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <span class="badge-sm">
                                            @if($repayment->method === 'TUNAI') Tunai
                                            @elseif($repayment->method === 'TRANSFER') Transfer
                                            @elseif($repayment->method === 'POTONG_GAJI') Gaji
                                            @else -
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                                @if($repayment->note)
                                <tr>
                                    <td colspan="4" class="note-row">
                                        <small>Catatan: {{ $repayment->note }}</small>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <style>
        :root {
            --bg-soft: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --card-border: #e5e7eb;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 22px;
        }

        .page-title {
            margin: 0;
            font-size: 24px;
            line-height: 1.2;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.01em;
        }

        .page-subtitle {
            margin: 6px 0 0;
            font-size: 14px;
            color: var(--text-muted);
        }

        .alert-success,
        .alert-error {
            padding: 12px 14px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: #fff;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: .18s ease;
            white-space: nowrap;
        }

        .btn-back:hover { border-color: #cbd5e1; background: var(--bg-soft); }

        .btn-download {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 9px 12px;
            border-radius: 10px;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: .18s ease;
        }

        .btn-download:hover { background: #dbeafe; }

        .detail-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1.3fr);
            gap: 20px;
            align-items: start;
        }

        .left-column,
        .right-column {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .card {
            background: #fff;
            border: 1px solid var(--card-border);
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }

        .card-header {
            padding: 16px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .card-title,
        .card-title-sm {
            margin: 0;
            padding: 0;
            font-size: 16px;
            font-weight: 700;
            color: var(--text-main);
        }

        .card-title-sm { padding: 16px 0 0; }
        .summary-card { padding: 16px 18px 18px; }
        .divider { height: 1px; background: #f1f5f9; }

        .info-group {
            padding: 16px 18px 18px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .info-row { display: flex; flex-direction: column; gap: 4px; }
        .info-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        .label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .value {
            font-size: 14px;
            color: #1e293b;
            font-weight: 500;
            line-height: 1.5;
        }

        .highlight-text { font-size: 22px; font-weight: 800; color: #0f172a; letter-spacing: -.01em; }
        .text-muted { color: #94a3b8; font-size: 13px; }

        .note-box {
            margin-top: 4px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 12px;
            color: #92400e;
            font-size: 13px;
            line-height: 1.5;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .badge-yellow { background: #fef9c3; color: #854d0e; border: 1px solid #fde68a; }
        .badge-blue { background: #dbeafe; color: #1e3a8a; border: 1px solid #bfdbfe; }
        .badge-red { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-green { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-gray { background: #e2e8f0; color: #334155; }

        .badge-sm {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 999px;
            background: #eef2ff;
            color: #3730a3;
            font-weight: 600;
        }

        .progress-container { margin-bottom: 14px; }

        .progress-labels {
            display: flex;
            justify-content: flex-end;
            font-size: 12px;
            color: #64748b;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .progress-track {
            width: 100%;
            height: 9px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #22c55e);
            border-radius: 999px;
            transition: width .45s ease;
        }

        .summary-list { display: flex; flex-direction: column; gap: 8px; }
        .summary-item { display: flex; justify-content: space-between; gap: 8px; font-size: 14px; color: #475569; }
        .summary-item strong { color: #0f172a; }
        .text-green { color: #059669 !important; }
        .text-green strong { color: #059669 !important; }
        .divider-dashed { border-top: 1px dashed #cbd5e1; margin: 8px 0; }
        .total-item { font-size: 15px; font-weight: 700; color: #0f172a; }
        .total-item strong { color: #b91c1c; }

        .table-responsive {
            overflow-x: auto;
            border-top: 1px solid #f1f5f9;
        }

        .table-history {
            width: 100%;
            border-collapse: collapse;
            min-width: 300px;
            font-size: 13px;
        }

        .table-history th {
            text-align: left;
            padding: 10px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .table-history td {
            padding: 11px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            vertical-align: top;
        }

        .table-history tr:last-child td { border-bottom: none; }
        .date-main { font-weight: 600; color: #1e293b; }
        .amount-cell { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 13px; font-weight: 700; color: #0f172a; }
        .text-center { text-align: center; }

        .note-row td {
            padding-top: 6px;
            padding-bottom: 12px;
            color: #64748b;
            background: #f8fafc;
        }

        .empty-state {
            padding: 28px;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
        }

        @media (max-width: 960px) {
            .detail-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: flex-start; gap: 12px; }
            .btn-back { align-self: flex-start; }
            .info-grid-2 { grid-template-columns: 1fr; }
            .card-header { flex-direction: column; align-items: flex-start; }
            .summary-card,
            .info-group { padding: 14px; }
            .card-title-sm { padding-top: 14px; }
        }
    </style>
</x-app>