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
            <h1 class="page-title">Detail Pengajuan Hutang</h1>
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
        /* --- UTILS --- */
        .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; gap: 16px; }
        .page-title { font-size: 20px; font-weight: 700; color: #111827; margin: 0; }
        .page-subtitle { font-size: 14px; color: #6b7280; margin: 4px 0 0 0; }
        
        .alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 8px; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; font-size: 14px; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; font-size: 14px; }

        /* --- BUTTONS --- */
        .btn-back { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 99px; border: 1px solid #d1d5db; background: #fff; color: #374151; font-size: 13px; font-weight: 500; text-decoration: none; transition: all 0.2s; white-space: nowrap; }
        .btn-back:hover { background: #f9fafb; border-color: #9ca3af; }

        .btn-download { display: inline-flex; align-items: center; gap: 6px; color: #1e4a8d; font-size: 13px; font-weight: 500; text-decoration: none; padding: 8px 12px; background: #eff6ff; border-radius: 8px; border: 1px solid transparent; width: 100%; justify-content: center; }
        .btn-download:hover { background: #dbeafe; }

        /* --- LAYOUT GRID --- */
        .detail-grid { display: grid; grid-template-columns: 2fr 1.3fr; gap: 20px; align-items: start; }
        
        /* --- CARDS --- */
        .card { background: #fff; border-radius: 12px; border: 1px solid #f3f4f6; box-shadow: 0 2px 8px rgba(0,0,0,0.03); overflow: hidden; margin-bottom: 0; }
        .card-header { padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; }
        .card-title { font-size: 16px; font-weight: 700; color: #111827; margin: 0; }
        .card-title-sm { font-size: 15px; font-weight: 700; color: #111827; margin: 0; padding: 16px 20px 0 20px; }
        .divider { height: 1px; background: #f3f4f6; width: 100%; }

        /* --- INFO GROUPS --- */
        .info-group { padding: 20px; display: flex; flex-direction: column; gap: 16px; }
        .info-row { display: flex; flex-direction: column; gap: 4px; }
        .info-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .label { font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.03em; }
        .value { font-size: 14.5px; color: #1f2937; font-weight: 500; line-height: 1.4; }
        .highlight-text { font-size: 18px; font-weight: 700; color: #111827; }
        .text-muted { color: #9ca3af; font-style: italic; font-size: 13px; }

        .note-box { background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; padding: 12px; font-size: 13.5px; color: #92400e; margin-top: 8px; line-height: 1.5; }

        /* --- BADGES --- */
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; }
        .badge-yellow { background: #fefce8; color: #a16207; border: 1px solid #fef08a; }
        .badge-blue { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
        .badge-red { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-green { background: #f0fdf4; color: #166534; border: 1px solid #dcfce7; }
        .badge-gray { background: #f3f4f6; color: #374151; }

        .badge-sm { font-size: 11px; padding: 2px 6px; background: #f3f4f6; border-radius: 4px; color: #4b5563; font-weight: 500; }

        /* --- PROGRESS BAR & SUMMARY --- */
        .summary-card { padding: 20px; display: flex; flex-direction: column; gap: 16px; }
        .progress-container { margin-bottom: 8px; }
        .progress-labels { display: flex; justify-content: flex-end; font-size: 12px; color: #6b7280; margin-bottom: 6px; font-weight: 500; }
        .progress-track { width: 100%; height: 8px; background: #f3f4f6; border-radius: 4px; overflow: hidden; }
        .progress-fill { height: 100%; background: #10b981; border-radius: 4px; transition: width 0.5s ease; }

        .summary-list { display: flex; flex-direction: column; gap: 10px; }
        .summary-item { display: flex; justify-content: space-between; font-size: 14px; color: #4b5563; }
        .summary-item strong { color: #111827; }
        .text-green { color: #059669 !important; }
        .text-green strong { color: #059669 !important; }
        .divider-dashed { border-top: 1px dashed #d1d5db; margin: 4px 0; }
        .total-item { font-size: 15px; }
        .total-item strong { color: #b91c1c; }

        /* --- TABLE HISTORY --- */
        .table-responsive { overflow-x: auto; margin-top: 10px; }
        .table-history { width: 100%; border-collapse: collapse; min-width: 300px; font-size: 13.5px; }
        .table-history th { text-align: left; padding: 10px 16px; background: #f9fafb; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.03em; border-bottom: 1px solid #e5e7eb; }
        .table-history td { padding: 12px 16px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        .table-history tr:last-child td { border-bottom: none; }
        
        .date-main { font-weight: 500; color: #1f2937; }
        .amount-cell { font-family: monospace; font-size: 13px; font-weight: 600; color: #111827; }
        .text-center { text-align: center; }
        
        .note-row td { padding-top: 4px; padding-bottom: 12px; color: #6b7280; background: #fafaf9; }
        .empty-state { padding: 30px; text-align: center; color: #9ca3af; font-size: 14px; }

        /* --- MOBILE RESPONSIVE --- */
        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: flex-start; gap: 12px; }
            .btn-back { align-self: flex-start; }
            
            .detail-grid { grid-template-columns: 1fr; gap: 16px; }
            .right-column { display: flex; flex-direction: column; gap: 16px; }
            
            /* Pada mobile, history table tetap scrollable horizontal */
            .table-responsive { border-top: 1px solid #f3f4f6; }
        }
    </style>
</x-app>