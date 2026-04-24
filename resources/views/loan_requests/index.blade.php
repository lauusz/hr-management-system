<x-app title="Pengajuan Hutang Saya">

    @if(session('success'))
    <div class="alert alert-success">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div class="page-header-text">
            <p class="page-subtitle">Daftar pengajuan pinjaman Anda ke perusahaan.</p>
        </div>
        @if($hasActiveLoan)
        <button type="button" class="btn-add" onclick="document.getElementById('modal-active-loan').classList.add('show')">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Ajukan
        </button>
        @else
        <a href="{{ route('employee.loan_requests.create') }}" class="btn-add">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Ajukan
        </a>
        @endif
    </div>

    {{-- MODAL: PINJAMAN AKTIF --}}
    @if($hasActiveLoan)
    @php
        $activeLoan = $loans->whereIn('status', ['PENDING_HRD', 'APPROVED'])->first();
        $activeStatus = $activeLoan->status === 'PENDING_HRD' ? 'Menunggu HRD' : 'Disetujui';
    @endphp
    <div id="modal-active-loan" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('show')">
        <div class="modal-box">
            <div class="modal-icon">
                <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="modal-title">Pinjaman Masih Aktif</h3>
            <p class="modal-desc">
                Saat ini Anda memiliki pinjaman dengan status <strong>{{ $activeStatus }}</strong> sebesar
                <strong>Rp {{ number_format($activeLoan->amount, 0, ',', '.') }}</strong>.
                Pengajuan baru dapat diajukan, namun tidak menjamin persetujuan.
            </p>
            <div class="modal-actions">
                <a href="{{ route('employee.loan_requests.create') }}" class="btn-modal-continue">
                    Lanjutkan
                </a>
                <button type="button" class="btn-modal-close" onclick="document.getElementById('modal-active-loan').classList.remove('show')">
                    Batal
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- LOAN LIST --}}
    @forelse($loans as $loan)
    @php
        $st = $loan->status;
        $badgeClass = 'badge-gray';
        $statusLabel = $st;

        if ($st === 'PENDING_HRD') {
            $badgeClass = 'badge-yellow';
            $statusLabel = 'Menunggu HRD';
        } elseif ($st === 'APPROVED') {
            $badgeClass = 'badge-blue';
            $statusLabel = 'Disetujui';
        } elseif ($st === 'REJECTED') {
            $badgeClass = 'badge-red';
            $statusLabel = 'Ditolak';
        } elseif ($st === 'LUNAS') {
            $badgeClass = 'badge-green';
            $statusLabel = 'Lunas';
        }

        $method = $loan->payment_method;
        $methodLabel = '-';
        if ($method === 'TUNAI') $methodLabel = 'Tunai';
        elseif ($method === 'CICILAN') $methodLabel = 'Transfer';
        elseif ($method === 'POTONG_GAJI') $methodLabel = 'Potong Gaji';
    @endphp

    <div class="loan-card">
        <div class="loan-card-header">
            <div class="loan-date">
                <span class="loan-date-main">{{ \Illuminate\Support\Carbon::parse($loan->submitted_at)->translatedFormat('j F Y') }}</span>
                <span class="loan-date-sub">{{ $loan->created_at->format('H:i') }}</span>
            </div>
            <span class="badge-status {{ $badgeClass }}">{{ $statusLabel }}</span>
        </div>

        <div class="loan-card-body">
            <div class="loan-amount">
                <span class="loan-amount-label">Jumlah Pinjaman</span>
                <span class="loan-amount-value">Rp {{ number_format($loan->amount, 0, ',', '.') }}</span>
            </div>

            <div class="loan-details">
                <div class="loan-detail-item">
                    <span class="loan-detail-label">Tenor</span>
                    <span class="loan-detail-value">{{ $loan->repayment_term ?? '-' }} Bulan</span>
                </div>
                <div class="loan-detail-item">
                    <span class="loan-detail-label">Cicilan/Bulan</span>
                    <span class="loan-detail-value">{{ $loan->monthly_installment ? 'Rp ' . number_format($loan->monthly_installment, 0, ',', '.') : '-' }}</span>
                </div>
                <div class="loan-detail-item">
                    <span class="loan-detail-label">Metode</span>
                    <span class="loan-detail-value">{{ $methodLabel }}</span>
                </div>
            </div>

            @if($loan->hrd_decided_at && in_array($st, ['APPROVED', 'REJECTED']))
            <div class="loan-note">
                <span class="loan-note-label">Diproses:</span>
                <span class="loan-note-value">{{ $loan->hrd_decided_at->translatedFormat('j F Y') }}</span>
            </div>
            @endif

            @if($loan->notes && in_array($st, ['APPROVED', 'REJECTED']))
            <div class="loan-response">
                <div class="loan-response-icon">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <div class="loan-response-text">{{ $loan->notes }}</div>
            </div>
            @endif
        </div>

        <div class="loan-card-footer">
            <a href="{{ route('employee.loan_requests.show', $loan->id) }}" class="btn-detail">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Lihat Detail
            </a>
        </div>
    </div>

    @empty
    <div class="empty-state">
        <div class="empty-icon">
            <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <p class="empty-text">Belum ada riwayat pengajuan hutang.</p>
        @if($hasActiveLoan)
        <button type="button" class="btn-add-empty" onclick="document.getElementById('modal-active-loan').classList.add('show')">
            Ajukan Hutang Baru
        </button>
        @else
        <a href="{{ route('employee.loan_requests.create') }}" class="btn-add-empty">Ajukan Hutang Baru</a>
        @endif
    </div>
    @endforelse

    @if(method_exists($loans, 'links') && $loans->hasPages())
    <div class="pagination-wrapper">
        {{ $loans->links() }}
    </div>
    @endif

    <style>
        :root {
            --navy: #1e4a8d;
            --navy-dark: #163a75;
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
            gap: 10px;
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        /* PAGE HEADER */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
        }

        .page-header-text { flex: 1; }

        .page-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .page-subtitle {
            font-size: 13px;
            color: var(--text-secondary);
            margin: 4px 0 0;
        }

        .btn-add {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            background: var(--navy);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            flex-shrink: 0;
        }

        .btn-add:hover { background: var(--navy-dark); }

        /* LOAN CARD */
        .loan-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .loan-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            background: #fafafa;
        }

        .loan-date {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .loan-date-main {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .loan-date-sub {
            font-size: 11px;
            color: var(--text-muted);
        }

        /* BADGE STATUS */
        .badge-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .badge-green { background: #dcfce7; color: #166534; }
        .badge-blue { background: #dbeafe; color: #1e3a8a; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .badge-gray { background: #f3f4f6; color: #374151; }

        /* LOAN CARD BODY */
        .loan-card-body {
            padding: 16px;
        }

        .loan-amount {
            display: flex;
            flex-direction: column;
            gap: 2px;
            margin-bottom: 14px;
            padding-bottom: 14px;
            border-bottom: 1px dashed var(--border);
        }

        .loan-amount-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .loan-amount-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--navy);
        }

        .loan-details {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        .loan-detail-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .loan-detail-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .loan-detail-value {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .loan-note {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 10px;
            background: #f8fafc;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .loan-note-label {
            font-size: 11px;
            color: var(--text-muted);
        }

        .loan-note-value {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .loan-response {
            display: flex;
            gap: 10px;
            padding: 12px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
        }

        .loan-response-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #dbeafe;
            border-radius: 6px;
            color: #1e3a8a;
            flex-shrink: 0;
        }

        .loan-response-text {
            font-size: 12px;
            color: #1e3a8a;
            line-height: 1.5;
        }

        /* LOAN CARD FOOTER */
        .loan-card-footer {
            padding: 12px 16px;
            border-top: 1px solid var(--border);
            background: #fafafa;
        }

        .btn-detail {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 10px 16px;
            background: var(--white);
            color: var(--navy);
            border: 1px solid var(--navy);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-detail:hover {
            background: var(--navy);
            color: var(--white);
        }

        /* EMPTY STATE */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
            text-align: center;
        }

        .empty-icon {
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            border-radius: 16px;
            color: var(--text-muted);
            margin-bottom: 16px;
        }

        .empty-text {
            font-size: 14px;
            color: var(--text-muted);
            margin: 0 0 20px;
        }

        .btn-add-empty {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: var(--navy);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-add-empty:hover { background: var(--navy-dark); }

        /* PAGINATION */
        .pagination-wrapper {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }

        /* RESPONSIVE - TABLET / DESKTOP */
        @media (min-width: 768px) {
            .page-header { align-items: center; margin-bottom: 24px; }
            .page-title { font-size: 24px; }
            .page-subtitle { font-size: 14px; }

            .loan-card { margin-bottom: 16px; border-radius: 14px; }
            .loan-card-header { padding: 16px 20px; }
            .loan-card-body { padding: 20px; }

            .loan-amount { margin-bottom: 16px; padding-bottom: 16px; }
            .loan-amount-value { font-size: 22px; }

            .loan-details {
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
            }

            .loan-detail-label { font-size: 11px; }
            .loan-detail-value { font-size: 14px; }

            .loan-note { padding: 10px 12px; }

            .loan-response { padding: 14px; }
            .loan-response-text { font-size: 13px; }

            .loan-card-footer { padding: 14px 20px; }

            .btn-detail {
                width: auto;
                display: inline-flex;
                padding: 10px 24px;
            }
        }

        /* RESPONSIVE - LARGE DESKTOP */
        @media (min-width: 1024px) {
            .loan-details {
                grid-template-columns: 1fr 1fr 1fr 1fr;
            }
        }

        /* MODAL */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            backdrop-filter: blur(4px);
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal-box {
            background: var(--white);
            border-radius: 16px;
            padding: 28px 24px;
            max-width: 380px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.2);
            animation: modal-in 0.2s ease;
        }

        @keyframes modal-in {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #fef3c7;
            border: 1px solid #fde68a;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d97706;
            margin: 0 auto 16px;
        }

        .modal-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 10px;
        }

        .modal-desc {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.6;
            margin: 0 0 22px;
        }

        .modal-desc strong {
            color: var(--text-primary);
        }

        .modal-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .btn-modal-continue {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 11px 20px;
            background: var(--navy);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            width: 100%;
        }

        .btn-modal-continue:hover { background: var(--navy-dark); }

        .btn-modal-close {
            padding: 10px 20px;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            width: 100%;
        }

        .btn-modal-close:hover { background: #f9fafb; }
    </style>
</x-app>