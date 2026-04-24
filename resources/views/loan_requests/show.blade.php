<x-app title="Detail Hutang Saya">

    @if(session('success'))
    <div class="alert alert-success">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-error">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ $errors->first() }}
    </div>
    @endif

    @php
        $months = $loan->repayment_term ? (int) $loan->repayment_term : 0;
        $monthlyInstallment = $loan->monthly_installment ? (float) $loan->monthly_installment : null;
        $totalPaid = $loan->repayments->sum('amount');
        $remaining = max(0, $loan->amount - $totalPaid);
        $percentage = $loan->amount > 0 ? min(100, round(($totalPaid / $loan->amount) * 100)) : 0;

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

        $methodLabel = '-';
        if ($loan->payment_method === 'TUNAI') $methodLabel = 'Tunai';
        elseif ($loan->payment_method === 'CICILAN') $methodLabel = 'Transfer Bank';
        elseif ($loan->payment_method === 'POTONG_GAJI') $methodLabel = 'Potong Gaji';
    @endphp

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <a href="{{ route('employee.loan_requests.index') }}" class="back-btn">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="page-header-text">
            <p class="page-subtitle">Memantau status pengajuan dan riwayat pembayaran cicilan.</p>
        </div>
    </div>

    {{-- STATUS HERO CARD --}}
    <div class="hero-card">
        <div class="hero-top">
            <div class="hero-date">
                <span class="hero-date-label">Tanggal Pengajuan</span>
                <span class="hero-date-value">{{ \Illuminate\Support\Carbon::parse($loan->submitted_at)->translatedFormat('j F Y') }}</span>
            </div>
            <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
        </div>
        <div class="hero-amount">
            <span class="hero-amount-label">Jumlah Pinjaman</span>
            <span class="hero-amount-value">Rp {{ number_format($loan->amount, 0, ',', '.') }}</span>
        </div>
        @if($loan->hrd_decided_at && in_array($loan->status, ['APPROVED', 'REJECTED', 'LUNAS']))
        <div class="hero-processed">
            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Diproses {{ $loan->hrd_decided_at->translatedFormat('j F Y, H:i') }}
        </div>
        @endif
    </div>

    <div class="main-layout">

        {{-- LEFT COLUMN --}}
        <div class="left-col">

            {{-- DETAIL CARD --}}
            <div class="section-card">
                <div class="section-header">
                    <span class="section-title">Detail Pinjaman</span>
                </div>

                <div class="detail-body">
                    {{-- Key Stats --}}
                    <div class="stats-row">
                        <div class="stat-box">
                            <span class="stat-label">Tenor</span>
                            <span class="stat-value">{{ $months > 0 ? $months . ' Bulan' : '-' }}</span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-label">Cicilan/Bulan</span>
                            <span class="stat-value">{{ $monthlyInstallment ? 'Rp ' . number_format($monthlyInstallment, 0, ',', '.') : '-' }}</span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-label">Metode</span>
                            <span class="stat-value">{{ $methodLabel }}</span>
                        </div>
                    </div>

                    <div class="detail-divider"></div>

                    {{-- Info Rows --}}
                    <div class="info-rows">
                        @if($loan->disbursement_date)
                        <div class="info-row">
                            <span class="info-label">Tanggal Cair</span>
                            <span class="info-value">{{ \Illuminate\Support\Carbon::parse($loan->disbursement_date)->translatedFormat('j F Y') }}</span>
                        </div>
                        @endif

                        @if($loan->purpose)
                        <div class="info-row">
                            <span class="info-label">Keperluan</span>
                            <span class="info-value">{{ $loan->purpose }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- HRD Notes --}}
                    @if($loan->notes && in_array($loan->status, ['APPROVED', 'REJECTED', 'LUNAS']))
                    <div class="note-box note-blue">
                        <div class="note-icon">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </div>
                        <div class="note-content">
                            <span class="note-title">Catatan HRD</span>
                            <span class="note-text">{{ $loan->notes }}</span>
                        </div>
                    </div>
                    @endif

                    {{-- Document --}}
                    @if($loan->document_path)
                    <a href="{{ asset('storage/' . $loan->document_path) }}" target="_blank" class="btn-document">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        Lihat Dokumen Pendukung
                    </a>
                    @endif
                </div>
            </div>

            {{-- REPAYMENT HISTORY --}}
            <div class="section-card">
                <div class="section-header">
                    <span class="section-title">Riwayat Cicilan</span>
                    @if(!$loan->repayments->isEmpty())
                    <span class="section-count">{{ $loan->repayments->count() }} transaksi</span>
                    @endif
                </div>

                @if($loan->repayments->isEmpty())
                <div class="empty-repayment">
                    <div class="empty-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <p>Belum ada data pembayaran cicilan.</p>
                </div>
                @else
                    {{-- Mobile: List Cards --}}
                    <div class="repayment-list">
                        @foreach($loan->repayments as $index => $repayment)
                        <div class="repayment-item">
                            <div class="repayment-item-left">
                                <span class="repayment-no">{{ $index + 1 }}</span>
                                <div class="repayment-info">
                                    <span class="repayment-date">{{ \Illuminate\Support\Carbon::parse($repayment->paid_at)->translatedFormat('j F Y') }}</span>
                                    @if($repayment->note)
                                    <span class="repayment-note">{{ $repayment->note }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="repayment-item-right">
                                <span class="repayment-amount">Rp {{ number_format($repayment->amount, 0, ',', '.') }}</span>
                                <span class="repayment-method-badge">
                                    @if($repayment->method === 'TUNAI') Tunai
                                    @elseif($repayment->method === 'TRANSFER') Transfer
                                    @elseif($repayment->method === 'POTONG_GAJI') Gaji
                                    @else -
                                    @endif
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT COLUMN: SUMMARY --}}
        <div class="right-col">
            <div class="section-card summary-card">
                <div class="section-header">
                    <span class="section-title">Ringkasan Pembayaran</span>
                </div>
                <div class="summary-body">
                    {{-- Progress --}}
                    <div class="progress-wrap">
                        <div class="progress-top">
                            <span class="progress-label">Progres Pembayaran</span>
                            <span class="progress-pct">{{ $percentage }}%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill {{ $percentage >= 100 ? 'progress-done' : '' }}" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>

                    {{-- Summary Items --}}
                    <div class="summary-items">
                        <div class="summary-row">
                            <span class="summary-key">Total Pinjaman</span>
                            <span class="summary-val">Rp {{ number_format($loan->amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="summary-row summary-paid">
                            <span class="summary-key">Sudah Dibayar</span>
                            <span class="summary-val">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-row summary-remaining">
                            <span class="summary-key">Sisa Hutang</span>
                            <span class="summary-val {{ $remaining === 0 ? 'val-zero' : 'val-red' }}">Rp {{ number_format($remaining, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <style>
        :root {
            --navy: #1e4a8d;
            --navy-dark: #163a75;
            --navy-light: #dbeafe;
            --bg-page: #f8fafc;
            --white: #ffffff;
            --border: #e5e7eb;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
        }

        * { box-sizing: border-box; }

        /* ALERT */
        .alert {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 11px 14px;
            border-radius: 10px;
            margin-bottom: 14px;
            font-size: 13px;
            font-weight: 500;
        }
        .alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        /* PAGE HEADER */
        .page-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .back-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .back-btn:hover {
            background: var(--bg-page);
            border-color: var(--navy);
            color: var(--navy);
        }

        .page-header-text { flex: 1; min-width: 0; }

        .page-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .page-subtitle {
            font-size: 12px;
            color: var(--text-secondary);
            margin: 2px 0 0;
        }

        /* HERO CARD */
        .hero-card {
            background: linear-gradient(135deg, var(--navy) 0%, #2563eb 100%);
            border-radius: 14px;
            padding: 18px 16px;
            margin-bottom: 14px;
            color: var(--white);
        }

        .hero-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
        }

        .hero-date {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .hero-date-label {
            font-size: 10px;
            font-weight: 600;
            opacity: 0.7;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .hero-date-value {
            font-size: 14px;
            font-weight: 600;
        }

        .hero-amount {
            display: flex;
            flex-direction: column;
            gap: 3px;
            margin-bottom: 4px;
        }

        .hero-amount-label {
            font-size: 10px;
            font-weight: 600;
            opacity: 0.7;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .hero-amount-value {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .hero-processed {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            opacity: 0.7;
            margin-top: 6px;
        }

        /* BADGE */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            flex-shrink: 0;
        }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .badge-blue { background: #dbeafe; color: #1e3a8a; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-gray { background: rgba(255,255,255,0.2); color: var(--white); }

        /* LAYOUT */
        .main-layout {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .left-col,
        .right-col {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* SECTION CARD */
        .section-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 13px 16px;
            border-bottom: 1px solid #f3f4f6;
            background: #fafafa;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .section-count {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            background: #f3f4f6;
            padding: 3px 8px;
            border-radius: 20px;
        }

        /* DETAIL BODY */
        .detail-body {
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .stats-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
        }

        .stat-box {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .stat-label {
            font-size: 9px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-value {
            font-size: 12px;
            font-weight: 700;
            color: var(--navy);
        }

        .detail-divider {
            height: 1px;
            background: #f3f4f6;
            margin: -4px 0;
        }

        .info-rows {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .info-row {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .info-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-value {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary);
        }

        /* NOTE BOX */
        .note-box {
            display: flex;
            gap: 10px;
            padding: 11px 12px;
            border-radius: 8px;
        }

        .note-blue {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
        }

        .note-icon {
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #dbeafe;
            border-radius: 5px;
            color: #1e3a8a;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .note-content {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .note-title {
            font-size: 10px;
            font-weight: 700;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .note-text {
            font-size: 12px;
            color: #1e40af;
            line-height: 1.5;
        }

        /* DOCUMENT BUTTON */
        .btn-document {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 14px;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #0369a1;
            text-decoration: none;
            width: 100%;
        }
        .btn-document:hover { background: #e0f2fe; }

        /* REPAYMENT LIST */
        .repayment-list {
            padding: 4px 0;
        }

        .repayment-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
        }

        .repayment-item:last-child { border-bottom: none; }

        .repayment-item-left {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .repayment-no {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-secondary);
            flex-shrink: 0;
        }

        .repayment-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .repayment-date {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .repayment-note {
            font-size: 11px;
            color: var(--text-muted);
        }

        .repayment-item-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }

        .repayment-amount {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-primary);
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .repayment-method-badge {
            font-size: 10px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 20px;
            background: #eef2ff;
            color: #3730a3;
        }

        /* EMPTY REPAYMENT */
        .empty-repayment {
            padding: 28px 16px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .empty-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            border-radius: 12px;
            color: var(--text-muted);
        }

        .empty-repayment p {
            font-size: 13px;
            color: var(--text-muted);
            margin: 0;
        }

        /* SUMMARY CARD */
        .summary-card { }

        .summary-body {
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .progress-wrap { }

        .progress-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 7px;
        }

        .progress-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .progress-pct {
            font-size: 12px;
            font-weight: 700;
            color: var(--navy);
        }

        .progress-track {
            width: 100%;
            height: 8px;
            background: #e5e7eb;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #22c55e);
            border-radius: 999px;
            transition: width 0.4s ease;
        }

        .progress-done {
            background: linear-gradient(90deg, #059669, #10b981);
        }

        .summary-items {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }

        .summary-key {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .summary-val {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .summary-paid .summary-key { color: #059669; }
        .summary-paid .summary-val { color: #059669; }

        .summary-divider {
            height: 1px;
            background: #e5e7eb;
            border-style: dashed;
            border-color: #d1d5db;
            border-width: 1px 0 0;
            background: none;
        }

        .summary-remaining .summary-key {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .summary-remaining .summary-val {
            font-size: 14px;
        }

        .val-red { color: #b91c1c !important; }
        .val-zero { color: #059669 !important; }

        /* DESKTOP LAYOUT */
        @media (min-width: 768px) {
            .page-header { margin-bottom: 18px; }
            .page-title { font-size: 18px; }
            .page-subtitle { font-size: 13px; }


            .hero-card { padding: 22px 24px; margin-bottom: 18px; }
            .hero-amount-value { font-size: 32px; }
            .hero-date-value { font-size: 15px; }

            .main-layout {
                flex-direction: row;
                align-items: flex-start;
                gap: 16px;
            }

            .left-col { flex: 1 1 0; min-width: 0; }
            .right-col { width: 280px; flex-shrink: 0; }

            .stats-row { gap: 10px; }
            .stat-value { font-size: 13px; }

            .repayment-amount { font-size: 14px; }
            .repayment-date { font-size: 13px; }

            .summary-key { font-size: 14px; }
            .summary-val { font-size: 14px; }
            .summary-remaining .summary-key,
            .summary-remaining .summary-val { font-size: 15px; }
        }

        @media (min-width: 1024px) {
            .right-col { width: 300px; }
            .hero-amount-value { font-size: 36px; }
        }
    </style>
</x-app>
