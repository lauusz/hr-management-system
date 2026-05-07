<x-app title="Detail Hutang Saya">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Detail Pinjaman</h1>
                <p class="section-subtitle">Memantau status pengajuan dan riwayat pembayaran cicilan</p>
            </div>
        </div>
    </x-slot>

    @php
        $months = $loan->repayment_term ? (int) $loan->repayment_term : 0;
        $monthlyInstallment = $loan->monthly_installment ? (float) $loan->monthly_installment : null;
        $totalPaid = $loan->repayments->sum('amount');
        $remaining = max(0, $loan->amount - $totalPaid);
        $percentage = $loan->amount > 0 ? min(100, round(($totalPaid / $loan->amount) * 100)) : 0;

        $statusLabel = $loan->status;
        $badgeClass = 'lrs-badge--neutral';

        if ($loan->status === 'PENDING_HRD') {
            $statusLabel = 'Menunggu HRD';
            $badgeClass = 'lrs-badge--warning';
        } elseif ($loan->status === 'APPROVED') {
            $statusLabel = 'Disetujui HRD';
            $badgeClass = 'lrs-badge--success';
        } elseif ($loan->status === 'REJECTED') {
            $statusLabel = 'Ditolak';
            $badgeClass = 'lrs-badge--error';
        } elseif ($loan->status === 'LUNAS') {
            $statusLabel = 'Lunas';
            $badgeClass = 'lrs-badge--success';
        }

        $methodLabel = '-';
        if ($loan->payment_method === 'TUNAI') $methodLabel = 'Tunai';
        elseif ($loan->payment_method === 'CICILAN') $methodLabel = 'Transfer Bank';
        elseif ($loan->payment_method === 'POTONG_GAJI') $methodLabel = 'Potong Gaji';

        $docUrl = null;
        $isImageDoc = false;
        if ($loan->document_path) {
            $docUrl = asset('storage/' . $loan->document_path);
            $docExt = strtolower(pathinfo($loan->document_path, PATHINFO_EXTENSION));
            $isImageDoc = in_array($docExt, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
        }
    @endphp

    <div class="loan-show-page">

        {{-- Back Button --}}
        <a href="{{ route('employee.loan_requests.index') }}" class="back-btn" aria-label="Kembali ke daftar pinjaman">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        {{-- Alerts --}}
        @if(session('success'))
        <div class="lrs-alert lrs-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="lrs-alert lrs-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $errors->first() }}
        </div>
        @endif

        {{-- HERO CARD --}}
        <div class="hero-card">
            <div class="hero-top">
                <div class="hero-date">
                    <span class="hero-date-label">Tanggal Pengajuan</span>
                    <span class="hero-date-value">{{ \Illuminate\Support\Carbon::parse($loan->submitted_at)->translatedFormat('j F Y') }}</span>
                </div>
                <span class="lrs-badge {{ $badgeClass }}">{{ $statusLabel }}</span>
            </div>
            <div class="hero-amount">
                <span class="hero-amount-label">Jumlah Pinjaman</span>
                <span class="hero-amount-value">Rp {{ number_format($loan->amount, 0, ',', '.') }}</span>
            </div>
            @if($loan->hrd_decided_at && in_array($loan->status, ['APPROVED', 'REJECTED', 'LUNAS']))
            <div class="hero-processed">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Diproses {{ $loan->hrd_decided_at->translatedFormat('j F Y, H:i') }}
            </div>
            @endif
        </div>

        {{-- MAIN LAYOUT --}}
        <div class="main-layout">

            {{-- LEFT COLUMN --}}
            <div class="left-col">

                {{-- DETAIL CARD --}}
                <div class="lrs-card">
                    <div class="lrs-card-header">
                        <span class="lrs-card-header-title">Detail Pinjaman</span>
                    </div>

                    <div class="lrs-card-body">
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
                        <div class="note-box">
                            <div class="note-icon">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <div class="note-content">
                                <span class="note-title">Catatan HRD</span>
                                <span class="note-text">{{ $loan->notes }}</span>
                            </div>
                        </div>
                        @endif

                        {{-- Document --}}
                        @if($loan->document_path)
                            @if($isImageDoc)
                            <button type="button" onclick="openDocModal()" class="btn-document" style="cursor:pointer;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                Lihat Dokumen Pendukung
                            </button>
                            @else
                            <a href="{{ $docUrl }}" target="_blank" class="btn-document">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                Lihat Dokumen Pendukung
                            </a>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- REPAYMENT HISTORY --}}
                <div class="lrs-card">
                    <div class="lrs-card-header">
                        <span class="lrs-card-header-title">Riwayat Cicilan</span>
                        @if(!$loan->repayments->isEmpty())
                        <span class="lrs-card-header-count">{{ $loan->repayments->count() }} transaksi</span>
                        @endif
                    </div>

                    <div class="lrs-card-body lrs-card-body--compact">
                        @if($loan->repayments->isEmpty())
                        <div class="empty-repayment">
                            <div class="empty-icon">
                                <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <p>Belum ada data pembayaran cicilan.</p>
                        </div>
                        @else
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
                                        @else - @endif
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: SUMMARY --}}
            <div class="right-col">
                <div class="lrs-card summary-card">
                    <div class="lrs-card-header">
                        <span class="lrs-card-header-title">Ringkasan Pembayaran</span>
                    </div>
                    <div class="lrs-card-body">
                        {{-- Progress --}}
                        <div class="progress-wrap">
                            <div class="progress-top">
                                <span class="progress-label">Progres Pembayaran</span>
                                <span class="progress-pct">{{ $percentage }}%</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill {{ $percentage >= 100 ? 'progress-fill--done' : '' }}" style="width: {{ $percentage }}%"></div>
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

        {{-- Image Preview Modal --}}
        @if($isImageDoc)
        <div id="docModal" class="doc-modal" onclick="closeDocModal(event)">
            <div class="doc-modal-backdrop"></div>

            {{-- Toolbar --}}
            <div class="doc-modal-toolbar">
                <button type="button" class="doc-tb-btn" onclick="zoomDoc(-0.25)" aria-label="Perkecil gambar">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
                <span id="docZoomPct" class="doc-tb-pct">100%</span>
                <button type="button" class="doc-tb-btn" onclick="zoomDoc(0.25)" aria-label="Perbesar gambar">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
                <button type="button" class="doc-tb-btn" onclick="resetDocZoom()" aria-label="Reset zoom">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                </button>
            </div>

            {{-- Close button --}}
            <button type="button" class="doc-modal-close" onclick="closeDocModal()" aria-label="Tutup preview">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Viewer --}}
            <div class="doc-modal-viewer">
                <img id="docModalImage" src="{{ $docUrl }}" alt="Dokumen Pendukung" class="doc-modal-img" draggable="false">
            </div>
        </div>
        @endif
    </div>

    <style>
        /* ========================================== */
        /* HEADER SLOT                                */
        /* ========================================== */
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .section-icon svg {
            width: 16px;
            height: 16px;
        }
        .icon-navy {
            background: rgba(10, 61, 98, 0.08);
            color: #0A3D62;
        }
        .section-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: #6B7280;
            font-weight: 500;
            line-height: 1.35;
        }

        /* ========================================== */
        /* BACK BUTTON                                */
        /* ========================================== */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 12px 0 10px;
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            color: #6B7280;
            text-decoration: none;
            transition: all 0.15s ease;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            align-self: flex-start;
        }
        .back-btn:hover {
            border-color: #145DA0;
            color: #145DA0;
            background: #F5F7FA;
        }
        .back-btn:hover svg {
            transform: translateX(-2px);
        }
        .back-btn svg {
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }
        .back-btn-text {
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
        }

        /* ========================================== */
        /* PAGE WRAPPER                               */
        /* ========================================== */
        .loan-show-page {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .lrs-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
        }
        .lrs-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }
        .lrs-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        /* ========================================== */
        /* HERO CARD                                  */
        /* ========================================== */
        .hero-card {
            background: linear-gradient(135deg, #0A3D62 0%, #145DA0 100%);
            border-radius: 16px;
            padding: 18px 16px;
            color: #fff;
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

        /* ========================================== */
        /* BADGES                                     */
        /* ========================================== */
        .lrs-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            white-space: nowrap;
            flex-shrink: 0;
            line-height: 1;
        }
        .lrs-badge--warning {
            background: #FEF3C7;
            color: #a16207;
        }
        .lrs-badge--success {
            background: #DCFCE7;
            color: #15803d;
        }
        .lrs-badge--error {
            background: #FEE2E2;
            color: #b91c1c;
        }
        .lrs-badge--neutral {
            background: rgba(255,255,255,0.25);
            color: #fff;
        }

        /* ========================================== */
        /* LAYOUT                                     */
        /* ========================================== */
        .main-layout {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .left-col,
        .right-col {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* ========================================== */
        /* CARD                                       */
        /* ========================================== */
        .lrs-card {
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .lrs-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            border-bottom: 1px solid #F3F4F6;
            background: #FAFAFA;
        }
        .lrs-card-header-title {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
        }
        .lrs-card-header-count {
            font-size: 11px;
            font-weight: 600;
            color: #9CA3AF;
            background: #F3F4F6;
            padding: 3px 8px;
            border-radius: 20px;
        }
        .lrs-card-body {
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .lrs-card-body--compact {
            padding: 0;
        }

        /* ========================================== */
        /* STATS ROW                                  */
        /* ========================================== */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }
        .stat-box {
            background: #F8FAFC;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .stat-label {
            font-size: 9px;
            font-weight: 700;
            color: #9CA3AF;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .stat-value {
            font-size: 12px;
            font-weight: 700;
            color: #0A3D62;
        }

        /* ========================================== */
        /* DIVIDER & INFO ROWS                        */
        /* ========================================== */
        .detail-divider {
            height: 1px;
            background: #F3F4F6;
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
            color: #9CA3AF;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .info-value {
            font-size: 13px;
            font-weight: 500;
            color: #111827;
            line-height: 1.45;
        }

        /* ========================================== */
        /* NOTE BOX                                   */
        /* ========================================== */
        .note-box {
            display: flex;
            gap: 10px;
            padding: 12px;
            border-radius: 10px;
            background: rgba(59, 130, 246, 0.06);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }
        .note-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 6px;
            color: #145DA0;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .note-content {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .note-title {
            font-size: 11px;
            font-weight: 700;
            color: #145DA0;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .note-text {
            font-size: 12px;
            color: #1D4ED8;
            line-height: 1.5;
        }

        /* ========================================== */
        /* DOCUMENT MODAL                             */
        /* ========================================== */
        .doc-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            width: 100vw;
            height: 100vh;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .doc-modal.is-open {
            display: flex;
        }
        .doc-modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.88);
            backdrop-filter: blur(4px);
        }

        /* Toolbar */
        .doc-modal-toolbar {
            position: fixed;
            top: 12px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 11;
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 6px 10px;
            background: rgba(30, 30, 35, 0.85);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 999px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
            user-select: none;
        }
        .doc-tb-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: transparent;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.15s ease;
            flex-shrink: 0;
            padding: 0;
        }
        .doc-tb-btn:hover {
            background: rgba(255,255,255,0.15);
        }
        .doc-tb-btn:active {
            background: rgba(255,255,255,0.25);
        }
        .doc-tb-btn:disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }
        .doc-tb-pct {
            min-width: 44px;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,0.9);
            font-variant-numeric: tabular-nums;
            padding: 0 6px;
            user-select: none;
        }

        /* Close button */
        .doc-modal-close {
            position: fixed;
            top: 12px;
            right: 12px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.25);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 11;
            flex-shrink: 0;
            padding: 0;
        }
        .doc-modal-close:hover {
            background: rgba(255,255,255,0.25);
            transform: scale(1.05);
        }

        /* Viewer */
        .doc-modal-viewer {
            position: relative;
            z-index: 1;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 56px 8px 8px;
            box-sizing: border-box;
            cursor: default;
        }
        .doc-modal-viewer.is-grab {
            cursor: grab;
        }
        .doc-modal-viewer.is-grabbing {
            cursor: grabbing;
        }

        /* Image */
        .doc-modal-img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 24px 80px rgba(0,0,0,0.5);
            transition: transform 0.15s ease-out;
            transform-origin: center center;
            -webkit-user-drag: none;
            user-select: none;
            pointer-events: none;
        }

        @media (min-width: 768px) {
            .doc-modal-toolbar {
                top: 20px;
                padding: 4px 10px;
                gap: 3px;
                background: rgba(28, 28, 32, 0.82);
                border-color: rgba(255,255,255,0.08);
                box-shadow: 0 4px 20px rgba(0,0,0,0.35);
            }
            .doc-tb-btn {
                width: 32px;
                height: 32px;
            }
            .doc-tb-btn svg {
                width: 14px;
                height: 14px;
            }
            .doc-tb-pct {
                font-size: 12px;
                min-width: 40px;
                padding: 0 4px;
                color: rgba(255,255,255,0.75);
            }
            .doc-modal-close {
                top: 16px;
                right: 16px;
                width: 44px;
                height: 44px;
            }
            .doc-modal-viewer {
                padding: 60px 16px 16px;
            }
            .doc-modal-img {
                border-radius: 10px;
            }
        }
        /* ========================================== */
        /* DOCUMENT BUTTON                            */
        /* ========================================== */
        .btn-document {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 14px;
            background: rgba(20, 93, 160, 0.06);
            border: 1px solid rgba(20, 93, 160, 0.2);
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            color: #145DA0;
            text-decoration: none;
            width: 100%;
            transition: all 0.15s ease;
        }
        .btn-document:hover {
            background: rgba(20, 93, 160, 0.1);
            border-color: rgba(20, 93, 160, 0.35);
        }

        /* ========================================== */
        /* REPAYMENT LIST                             */
        /* ========================================== */
        .repayment-list {
            padding: 4px 0;
        }
        .repayment-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 16px;
            border-bottom: 1px solid #F3F4F6;
        }
        .repayment-item:last-child {
            border-bottom: none;
        }
        .repayment-item-left {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .repayment-no {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #F3F4F6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            color: #6B7280;
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
            color: #111827;
        }
        .repayment-note {
            font-size: 11px;
            color: #9CA3AF;
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
            color: #111827;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        .repayment-method-badge {
            font-size: 10px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 20px;
            background: #EEF2FF;
            color: #3730A3;
        }

        /* ========================================== */
        /* EMPTY REPAYMENT                            */
        /* ========================================== */
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
            background: #F3F4F6;
            border-radius: 12px;
            color: #9CA3AF;
        }
        .empty-repayment p {
            font-size: 13px;
            color: #9CA3AF;
            margin: 0;
        }

        /* ========================================== */
        /* PROGRESS & SUMMARY                         */
        /* ========================================== */
        .progress-wrap {
            margin-bottom: 2px;
        }
        .progress-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .progress-label {
            font-size: 11px;
            font-weight: 600;
            color: #6B7280;
        }
        .progress-pct {
            font-size: 12px;
            font-weight: 700;
            color: #0A3D62;
        }
        .progress-track {
            width: 100%;
            height: 8px;
            background: #E5E7EB;
            border-radius: 999px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #0A3D62, #145DA0);
            border-radius: 999px;
            transition: width 0.4s ease;
        }
        .progress-fill--done {
            background: linear-gradient(90deg, #059669, #22C55E);
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
            color: #6B7280;
        }
        .summary-val {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
        }
        .summary-paid .summary-key { color: #059669; }
        .summary-paid .summary-val { color: #059669; }
        .summary-divider {
            height: 1px;
            border-style: dashed;
            border-color: #D1D5DB;
            border-width: 1px 0 0;
        }
        .summary-remaining .summary-key {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }
        .summary-remaining .summary-val {
            font-size: 14px;
        }
        .val-red { color: #DC2626 !important; }
        .val-zero { color: #059669 !important; }

        /* ========================================== */
        /* DESKTOP ENHANCEMENT                        */
        /* ========================================== */
        @media (min-width: 768px) {
            .loan-show-page {
                gap: 12px;
            }
            .back-btn {
                height: 40px;
                padding: 0 14px 0 12px;
            }
            .back-btn-text {
                font-size: 0.8125rem;
            }
            .hero-card {
                padding: 22px 24px;
            }
            .hero-amount-value {
                font-size: 32px;
            }
            .hero-date-value {
                font-size: 15px;
            }
            .main-layout {
                flex-direction: row;
                align-items: flex-start;
                gap: 16px;
            }
            .left-col {
                flex: 1 1 0;
                min-width: 0;
                gap: 12px;
            }
            .right-col {
                width: 280px;
                flex-shrink: 0;
                gap: 12px;
            }
            .lrs-card-header {
                padding: 16px 20px;
            }
            .lrs-card-body {
                padding: 20px;
            }
            .stats-row {
                gap: 10px;
            }
            .stat-value {
                font-size: 13px;
            }
            .repayment-amount {
                font-size: 14px;
            }
            .repayment-date {
                font-size: 13px;
            }
            .summary-key {
                font-size: 14px;
            }
            .summary-val {
                font-size: 14px;
            }
            .summary-remaining .summary-key,
            .summary-remaining .summary-val {
                font-size: 15px;
            }
        }

        @media (min-width: 1024px) {
            .right-col {
                width: 300px;
            }
            .hero-amount-value {
                font-size: 36px;
            }
        }
    </style>

    @if($isImageDoc)
    <script>
    (function() {
        const MIN_ZOOM = 0.5;
        const MAX_ZOOM = 4;
        const ZOOM_STEP = 0.25;

        let docZoom = 1;
        let docPanX = 0;
        let docPanY = 0;
        let isDragging = false;
        let dragStartX = 0;
        let dragStartY = 0;
        let panStartX = 0;
        let panStartY = 0;

        const modal = document.getElementById('docModal');
        const viewer = modal.querySelector('.doc-modal-viewer');
        const img = document.getElementById('docModalImage');
        const pctEl = document.getElementById('docZoomPct');

        function updateDocTransform() {
            img.style.transform = 'translate(' + docPanX + 'px, ' + docPanY + 'px) scale(' + docZoom + ')';
            if (pctEl) pctEl.textContent = Math.round(docZoom * 100) + '%';
            updateCursor();
        }

        function updateCursor() {
            viewer.classList.remove('is-grab', 'is-grabbing');
            if (docZoom > 1) {
                viewer.classList.add(isDragging ? 'is-grabbing' : 'is-grab');
            }
        }

        window.zoomDoc = function(delta) {
            let newZoom = Math.round((docZoom + delta) / ZOOM_STEP) * ZOOM_STEP;
            newZoom = Math.max(MIN_ZOOM, Math.min(MAX_ZOOM, newZoom));
            if (newZoom === docZoom) return;
            docZoom = newZoom;
            if (docZoom <= 1) {
                docPanX = 0;
                docPanY = 0;
            }
            updateDocTransform();
        };

        window.resetDocZoom = function() {
            docZoom = 1;
            docPanX = 0;
            docPanY = 0;
            isDragging = false;
            updateDocTransform();
        };

        window.openDocModal = function() {
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            window.resetDocZoom();
        };

        window.closeDocModal = function(e) {
            if (e && e.type === 'click') {
                if (!(e.target.classList.contains('doc-modal') || e.target.classList.contains('doc-modal-backdrop'))) {
                    return;
                }
            }
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
            window.resetDocZoom();
        };

        // Mouse drag pan
        viewer.addEventListener('mousedown', function(e) {
            if (e.button !== 0) return;
            if (docZoom <= 1) return;
            isDragging = true;
            dragStartX = e.clientX;
            dragStartY = e.clientY;
            panStartX = docPanX;
            panStartY = docPanY;
            updateCursor();
            e.preventDefault();
        });

        window.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            docPanX = panStartX + (e.clientX - dragStartX);
            docPanY = panStartY + (e.clientY - dragStartY);
            updateDocTransform();
        });

        window.addEventListener('mouseup', function() {
            if (!isDragging) return;
            isDragging = false;
            updateCursor();
        });

        // Touch drag pan
        viewer.addEventListener('touchstart', function(e) {
            if (docZoom <= 1) return;
            if (e.touches.length !== 1) return;
            isDragging = true;
            dragStartX = e.touches[0].clientX;
            dragStartY = e.touches[0].clientY;
            panStartX = docPanX;
            panStartY = docPanY;
            updateCursor();
        }, { passive: false });

        viewer.addEventListener('touchmove', function(e) {
            if (!isDragging || e.touches.length !== 1) return;
            e.preventDefault();
            docPanX = panStartX + (e.touches[0].clientX - dragStartX);
            docPanY = panStartY + (e.touches[0].clientY - dragStartY);
            updateDocTransform();
        }, { passive: false });

        viewer.addEventListener('touchend', function() {
            isDragging = false;
            updateCursor();
        });

        // Wheel zoom
        viewer.addEventListener('wheel', function(e) {
            e.preventDefault();
            if (e.deltaY < 0) {
                window.zoomDoc(ZOOM_STEP);
            } else {
                window.zoomDoc(-ZOOM_STEP);
            }
        }, { passive: false });

        // Double-click toggle 1x / 2x
        viewer.addEventListener('dblclick', function(e) {
            e.preventDefault();
            if (docZoom === 1) {
                docZoom = 2;
            } else {
                docZoom = 1;
                docPanX = 0;
                docPanY = 0;
            }
            updateDocTransform();
        });

        // Keyboard
        document.addEventListener('keydown', function(e) {
            if (!modal.classList.contains('is-open')) return;
            if (e.key === 'Escape') {
                window.closeDocModal();
            } else if (e.key === '+' || e.key === '=') {
                e.preventDefault();
                window.zoomDoc(ZOOM_STEP);
            } else if (e.key === '-') {
                e.preventDefault();
                window.zoomDoc(-ZOOM_STEP);
            } else if (e.key === '0') {
                e.preventDefault();
                window.resetDocZoom();
            }
        });
    })();
    </script>
    @endif
</x-app>
