<x-app title="Hutang Saya">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Hutang Saya</h1>
                <p class="section-subtitle">Ringkasan total hutang dan cicilan berjalan.</p>
            </div>
        </div>
    </x-slot>

    @php
        $runningLoans = $loanSummary['runningLoans'];
        $primaryLoan = $runningLoans->first();
        $totalDebt = $loanSummary['totalDebt'];
        $totalPaid = $loanSummary['totalPaid'];
        $remaining = $loanSummary['remaining'];
        $percentage = $loanSummary['percentage'];
        $monthlyInstallment = $loanSummary['monthlyInstallment'];
        $repayments = $loanSummary['repayments'];

        $methodLabel = '-';
        if ($primaryLoan?->payment_method === 'TUNAI') $methodLabel = 'Tunai';
        elseif ($primaryLoan?->payment_method === 'CICILAN') $methodLabel = 'Cicilan';
        elseif ($primaryLoan?->payment_method === 'POTONG_GAJI') $methodLabel = 'Potong Gaji';
    @endphp

    @if(session('success'))
    <div class="ln-alert ln-alert--success">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="ln-cta-bar">
        @if($hasActiveLoan)
        <button type="button" class="ln-btn-primary" onclick="document.getElementById('modal-active-loan').classList.add('show')">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajukan Hutang
        </button>
        @else
        <a href="{{ route('employee.loan_requests.create') }}" class="ln-btn-primary">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajukan Hutang
        </a>
        @endif
    </div>

    @if($hasActiveLoan)
    @php
        $activeLoan = $loans->whereIn('status', ['PENDING_HRD', 'APPROVED'])->first();
        $activeStatus = $activeLoan->status === 'PENDING_HRD' ? 'Menunggu HRD' : 'Disetujui';
    @endphp
    <div id="modal-active-loan" class="ln-modal-overlay" onclick="if(event.target===this)this.classList.remove('show')">
        <div class="ln-modal-box">
            <div class="ln-modal-icon">
                <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="ln-modal-title">Pinjaman Masih Aktif</h3>
            <p class="ln-modal-desc">
                Saat ini Anda memiliki pinjaman dengan status <strong>{{ $activeStatus }}</strong> sebesar
                <strong>Rp {{ number_format($activeLoan->amount, 0, ',', '.') }}</strong>.
                Pengajuan baru dapat diajukan, namun tidak menjamin persetujuan.
            </p>
            <div class="ln-modal-actions">
                <a href="{{ route('employee.loan_requests.create') }}" class="ln-modal-btn-continue">Lanjutkan</a>
                <button type="button" class="ln-modal-btn-close" onclick="document.getElementById('modal-active-loan').classList.remove('show')">Batal</button>
            </div>
        </div>
    </div>
    @endif

    <div class="ln-dashboard">
        <section class="ln-total-panel">
            <div class="ln-total-top">
                <div>
                    <span class="ln-eyebrow">Total Hutang</span>
                    <h2>Rp {{ number_format($remaining, 0, ',', '.') }}</h2>
                </div>
                <span class="ln-status {{ $remaining > 0 ? 'ln-status--running' : 'ln-status--clear' }}">
                    {{ $remaining > 0 ? 'Berjalan' : 'Tidak Ada Hutang' }}
                </span>
            </div>

            <div class="ln-progress">
                <div class="ln-progress-label">
                    <span>Progres pembayaran</span>
                    <strong>{{ $percentage }}%</strong>
                </div>
                <div class="ln-progress-track">
                    <div class="ln-progress-fill" style="width: {{ $percentage }}%"></div>
                </div>
            </div>

            <div class="ln-summary-grid">
                <div>
                    <span>Total Pinjaman</span>
                    <strong>Rp {{ number_format($totalDebt, 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span>Sudah Dibayar</span>
                    <strong>Rp {{ number_format($totalPaid, 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span>Cicilan / Bulan</span>
                    <strong>{{ $monthlyInstallment > 0 ? 'Rp '.number_format($monthlyInstallment, 0, ',', '.') : '-' }}</strong>
                </div>
                <div>
                    <span>Pengajuan Tertunda</span>
                    <strong>{{ $loanSummary['pendingLoans'] }}</strong>
                </div>
            </div>
        </section>

        <section class="ln-info-panel">
            <div class="ln-panel-header">
                <div>
                    <h3>Detail Hutang Berjalan</h3>
                    <p>{{ $runningLoans->count() }} pinjaman disetujui</p>
                </div>
                @if($primaryLoan)
                <a href="{{ route('employee.loan_requests.show', $primaryLoan->id) }}" class="ln-link-btn">Detail</a>
                @endif
            </div>

            @if($primaryLoan)
            <dl class="ln-detail-list">
                <div>
                    <dt>Tenor</dt>
                    <dd>{{ $primaryLoan->repayment_term ?? '-' }} Bulan</dd>
                </div>
                <div>
                    <dt>Metode</dt>
                    <dd>{{ $methodLabel }}</dd>
                </div>
                <div>
                    <dt>Tanggal Pengajuan</dt>
                    <dd>{{ \Illuminate\Support\Carbon::parse($primaryLoan->submitted_at)->translatedFormat('j F Y') }}</dd>
                </div>
            </dl>
            @else
            <div class="ln-empty-inline">
                Belum ada hutang berjalan. Pengajuan yang menunggu HRD belum dihitung sebagai hutang.
            </div>
            @endif
        </section>
    </div>

    <section class="ln-section">
        <div class="ln-section-head">
            <div>
                <h3>Detail Cicilan</h3>
                <p>Dikelola oleh HRD / HR Staff.</p>
            </div>
        </div>

        @if($repayments->isEmpty())
        <div class="ln-empty-inline">Belum ada cicilan yang tercatat.</div>
        @else
        <div class="ln-repayment-list">
            @foreach($repayments as $repayment)
            <div class="ln-repayment-row">
                <div>
                    <strong>{{ \Illuminate\Support\Carbon::parse($repayment->paid_at)->translatedFormat('j F Y') }}</strong>
                    <span>{{ $repayment->note ?: 'Pembayaran cicilan' }}</span>
                </div>
                <div>
                    <strong>Rp {{ number_format($repayment->amount, 0, ',', '.') }}</strong>
                    <span>
                        @if($repayment->method === 'TUNAI') Tunai
                        @elseif($repayment->method === 'TRANSFER') Transfer
                        @elseif($repayment->method === 'POTONG_GAJI') Potong Gaji
                        @else -
                        @endif
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </section>

    <section class="ln-section">
        <div class="ln-section-head">
            <div>
                <h3>Riwayat Pengajuan</h3>
                <p>Ringkasan semua pengajuan hutang Anda.</p>
            </div>
        </div>

        @if($loans->isEmpty())
        <div class="ln-empty">
            <div class="ln-empty-icon">
                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h3>Belum Ada Riwayat</h3>
            <p>Anda belum pernah mengajukan hutang.</p>
        </div>
        @else
        <div class="ln-history">
            @foreach($loans as $loan)
            @php
                $statusLabel = $loan->status;
                $badgeClass = 'ln-badge--gray';
                if ($loan->status === 'PENDING_HRD') {
                    $statusLabel = 'Menunggu HRD';
                    $badgeClass = 'ln-badge--warning';
                } elseif ($loan->status === 'APPROVED') {
                    $statusLabel = 'Disetujui';
                    $badgeClass = 'ln-badge--success';
                } elseif ($loan->status === 'REJECTED') {
                    $statusLabel = 'Ditolak';
                    $badgeClass = 'ln-badge--error';
                } elseif ($loan->status === 'LUNAS') {
                    $statusLabel = 'Lunas';
                    $badgeClass = 'ln-badge--success';
                } elseif ($loan->status === 'CANCELED') {
                    $statusLabel = 'Dibatalkan';
                    $badgeClass = 'ln-badge--neutral';
                }
            @endphp
            <div class="ln-history-row">
                <a href="{{ route('employee.loan_requests.show', $loan->id) }}">
                    <span>{{ \Illuminate\Support\Carbon::parse($loan->submitted_at)->translatedFormat('j F Y') }}</span>
                    <strong>Rp {{ number_format($loan->amount, 0, ',', '.') }}</strong>
                </a>
                <span class="ln-badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                @if($loan->status === 'PENDING_HRD')
                <form action="{{ route('employee.loan_requests.destroy', $loan->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pengajuan hutang ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="ln-btn-text-danger">Batalkan</button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </section>

    <style>
        :root {
            --primary-dark: #0A3D62;
            --primary: #145DA0;
            --white: #FFFFFF;
            --gray-50: #F5F7FA;
            --gray-100: #F8FAFC;
            --gray-200: #E5E7EB;
            --gray-500: #6B7280;
            --gray-600: #374151;
            --gray-700: #1F2937;
            --gray-900: #111827;
            --border: #E5E7EB;
            --border-light: #F3F4F6;
        }

        * { box-sizing: border-box; }
        .section-header-inline { display: flex; align-items: center; gap: 10px; }
        .section-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .section-icon svg { width: 16px; height: 16px; }
        .section-title { margin: 0; font-size: 1rem; font-weight: 800; color: var(--gray-900); line-height: 1.25; }
        .section-subtitle { margin: 0; font-size: 0.8125rem; color: var(--gray-500); font-weight: 500; line-height: 1.35; }
        .icon-navy { background: rgba(10, 61, 98, 0.08); color: var(--primary-dark); }

        .ln-alert { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 13px; font-weight: 500; }
        .ln-alert--success { background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.25); color: #15803d; }
        .ln-cta-bar { display: flex; justify-content: flex-start; margin-bottom: 16px; }
        .ln-btn-primary { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 18px; background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff; border: 0; border-radius: 12px; font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22); font-family: inherit; }
        .ln-btn-primary:hover { box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32); transform: translateY(-1px); }

        .ln-dashboard { display: grid; grid-template-columns: 1fr; gap: 12px; margin-bottom: 12px; }
        .ln-total-panel,
        .ln-info-panel,
        .ln-section { background: var(--white); border: 1px solid var(--border); border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .ln-total-panel { padding: 18px 16px; background: linear-gradient(135deg, #0A3D62 0%, #145DA0 100%); color: #fff; }
        .ln-info-panel,
        .ln-section { padding: 16px; }
        .ln-total-top,
        .ln-panel-header,
        .ln-section-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
        .ln-eyebrow { display: block; margin-bottom: 4px; font-size: 0.6875rem; color: rgba(255,255,255,0.78); font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; }
        .ln-total-panel h2 { margin: 0; font-size: 1.75rem; line-height: 1.1; font-weight: 800; }
        .ln-status { display: inline-flex; padding: 6px 10px; border-radius: 999px; font-size: 0.6875rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; white-space: nowrap; }
        .ln-status--running { background: #FEF3C7; color: #a16207; }
        .ln-status--clear { background: #DCFCE7; color: #15803d; }

        .ln-progress { margin-top: 18px; }
        .ln-progress-label { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 0.75rem; color: rgba(255,255,255,0.78); }
        .ln-progress-track { height: 8px; background: rgba(255,255,255,0.22); border-radius: 999px; overflow: hidden; }
        .ln-progress-fill { height: 100%; background: #fff; border-radius: 999px; transition: width 0.25s ease; }

        .ln-summary-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 16px; }
        .ln-summary-grid div { padding: 10px; border: 1px solid rgba(255,255,255,0.2); border-radius: 10px; background: rgba(255,255,255,0.08); min-width: 0; }
        .ln-summary-grid span { display: block; margin-bottom: 4px; font-size: 0.625rem; color: rgba(255,255,255,0.7); font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
        .ln-summary-grid strong { display: block; font-size: 0.875rem; line-height: 1.25; word-break: break-word; }

        .ln-panel-header h3,
        .ln-section-head h3 { margin: 0; font-size: 0.9375rem; color: var(--gray-900); font-weight: 800; }
        .ln-panel-header p,
        .ln-section-head p { margin: 3px 0 0; font-size: 0.75rem; color: var(--gray-500); font-weight: 500; }
        .ln-link-btn { flex-shrink: 0; padding: 8px 12px; border: 1px solid rgba(20, 93, 160, 0.25); border-radius: 10px; color: var(--primary); background: rgba(20, 93, 160, 0.06); text-decoration: none; font-size: 0.75rem; font-weight: 700; }
        .ln-detail-list { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin: 14px 0 0; }
        .ln-detail-list div { padding: 10px; background: var(--gray-50); border: 1px solid var(--border-light); border-radius: 10px; }
        .ln-detail-list dt { margin: 0 0 4px; font-size: 0.625rem; color: var(--gray-500); font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
        .ln-detail-list dd { margin: 0; font-size: 0.8125rem; color: var(--gray-700); font-weight: 700; }

        .ln-section { margin-bottom: 12px; }
        .ln-repayment-list,
        .ln-history { margin-top: 14px; border: 1px solid var(--border-light); border-radius: 12px; overflow: hidden; }
        .ln-repayment-row,
        .ln-history-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 14px; border-bottom: 1px solid var(--border-light); background: #fff; }
        .ln-repayment-row:last-child,
        .ln-history-row:last-child { border-bottom: 0; }
        .ln-repayment-row div,
        .ln-history-row a { display: flex; flex-direction: column; gap: 3px; min-width: 0; color: inherit; text-decoration: none; }
        .ln-repayment-row div:last-child { align-items: flex-end; flex-shrink: 0; }
        .ln-repayment-row strong,
        .ln-history-row strong { font-size: 0.8125rem; color: var(--gray-900); }
        .ln-repayment-row span,
        .ln-history-row span { font-size: 0.75rem; color: var(--gray-500); }

        .ln-badge { display: inline-flex; align-items: center; justify-content: center; padding: 5px 10px; border-radius: 999px; font-size: 0.6875rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; white-space: nowrap; }
        .ln-badge--warning { background: rgba(245, 158, 11, 0.1); color: #a16207; }
        .ln-badge--success { background: rgba(34, 197, 94, 0.1); color: #15803d; }
        .ln-badge--error { background: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .ln-badge--neutral,
        .ln-badge--gray { background: var(--gray-100); color: var(--gray-600); border: 1px solid var(--border); }
        .ln-btn-text-danger { padding: 0; border: 0; background: transparent; color: #dc2626; font-size: 0.75rem; font-weight: 700; cursor: pointer; font-family: inherit; }

        .ln-empty,
        .ln-empty-inline { text-align: center; padding: 28px 16px; background: var(--gray-50); border: 1px solid var(--border-light); border-radius: 12px; color: var(--gray-500); font-size: 0.8125rem; font-weight: 500; }
        .ln-empty-icon { width: 64px; height: 64px; margin: 0 auto 12px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; color: #9CA3AF; }
        .ln-empty h3 { margin: 0 0 6px; font-size: 0.9375rem; color: var(--gray-700); }
        .ln-empty p { margin: 0; }

        .ln-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px); }
        .ln-modal-overlay.show { display: flex; }
        .ln-modal-box { background: var(--white); border-radius: 16px; padding: 28px 24px; max-width: 380px; width: 100%; text-align: center; box-shadow: 0 20px 60px rgba(15, 23, 42, 0.2); }
        .ln-modal-icon { width: 56px; height: 56px; border-radius: 50%; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.25); display: flex; align-items: center; justify-content: center; color: #d97706; margin: 0 auto 16px; }
        .ln-modal-title { font-size: 1.0625rem; font-weight: 800; color: var(--gray-900); margin: 0 0 10px; }
        .ln-modal-desc { font-size: 0.8125rem; color: var(--gray-500); line-height: 1.6; margin: 0 0 22px; font-weight: 500; }
        .ln-modal-desc strong { color: var(--gray-900); font-weight: 700; }
        .ln-modal-actions { display: flex; flex-direction: column; gap: 8px; }
        .ln-modal-btn-continue,
        .ln-modal-btn-close { display: flex; align-items: center; justify-content: center; padding: 11px 20px; border-radius: 12px; font-size: 13px; font-weight: 600; cursor: pointer; width: 100%; text-decoration: none; font-family: inherit; }
        .ln-modal-btn-continue { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff; border: 0; }
        .ln-modal-btn-close { background: transparent; border: 1.5px solid var(--border); color: var(--gray-500); }

        @media (min-width: 480px) {
            .ln-cta-bar { justify-content: flex-end; margin-bottom: 20px; }
            .ln-total-panel,
            .ln-info-panel,
            .ln-section { padding: 20px; }
            .ln-total-panel h2 { font-size: 2.25rem; }
        }

        @media (min-width: 768px) {
            .ln-dashboard { grid-template-columns: minmax(0, 1.5fr) minmax(280px, 0.8fr); align-items: stretch; }
            .ln-summary-grid { grid-template-columns: repeat(4, 1fr); }
        }

        @media (max-width: 520px) {
            .ln-summary-grid,
            .ln-detail-list { grid-template-columns: 1fr; }
            .ln-history-row { align-items: flex-start; flex-wrap: wrap; }
            .ln-history-row form { width: 100%; }
        }
    </style>
</x-app>
