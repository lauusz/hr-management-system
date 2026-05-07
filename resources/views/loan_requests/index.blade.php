<x-app title="Pengajuan Hutang Saya">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Pengajuan Hutang Saya</h1>
                <p class="section-subtitle">Daftar pengajuan pinjaman Anda ke perusahaan.</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="ln-alert ln-alert--success">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- ========================================== --}}
    {{-- CTA BAR                                    --}}
    {{-- ========================================== --}}
    <div class="ln-cta-bar">
        @if($hasActiveLoan)
        <button type="button" class="ln-btn-primary" onclick="document.getElementById('modal-active-loan').classList.add('show')">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajukan
        </button>
        @else
        <a href="{{ route('employee.loan_requests.create') }}" class="ln-btn-primary">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajukan
        </a>
        @endif
    </div>

    {{-- ========================================== --}}
    {{-- MODAL: PINJAMAN AKTIF                      --}}
    {{-- ========================================== --}}
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
                <a href="{{ route('employee.loan_requests.create') }}" class="ln-modal-btn-continue">
                    Lanjutkan
                </a>
                <button type="button" class="ln-modal-btn-close" onclick="document.getElementById('modal-active-loan').classList.remove('show')">
                    Batal
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================== --}}
    {{-- LOAN LIST                                  --}}
    {{-- ========================================== --}}
    <div class="ln-list">
        @forelse($loans as $loan)
        @php
            $st = $loan->status;
            $badgeClass = 'ln-badge--gray';
            $statusLabel = $st;

            if ($st === 'PENDING_HRD') {
                $badgeClass = 'ln-badge--warning';
                $statusLabel = 'Menunggu HRD';
            } elseif ($st === 'APPROVED') {
                $badgeClass = 'ln-badge--success';
                $statusLabel = 'Disetujui';
            } elseif ($st === 'REJECTED') {
                $badgeClass = 'ln-badge--error';
                $statusLabel = 'Ditolak';
            } elseif ($st === 'LUNAS') {
                $badgeClass = 'ln-badge--success';
                $statusLabel = 'Lunas';
            } elseif ($st === 'CANCELED') {
                $badgeClass = 'ln-badge--neutral';
                $statusLabel = 'Dibatalkan';
            }

            $method = $loan->payment_method;
            $methodLabel = '-';
            if ($method === 'TUNAI') $methodLabel = 'Tunai';
            elseif ($method === 'CICILAN') $methodLabel = 'Transfer';
            elseif ($method === 'POTONG_GAJI') $methodLabel = 'Potong Gaji';
        @endphp

        <div class="ln-card">
            <div class="ln-card-header">
                <div class="ln-card-meta">
                    <span class="ln-card-date">{{ \Illuminate\Support\Carbon::parse($loan->submitted_at)->translatedFormat('l, j F Y') }}</span>
                    <span class="ln-card-time">{{ $loan->created_at->format('H:i') }}</span>
                </div>
                <span class="ln-badge {{ $badgeClass }}">{{ $statusLabel }}</span>
            </div>

            <div class="ln-card-body">
                <div class="ln-amount">
                    <span class="ln-amount-label">Jumlah Pinjaman</span>
                    <span class="ln-amount-value">Rp {{ number_format($loan->amount, 0, ',', '.') }}</span>
                </div>

                <div class="ln-details">
                    <div class="ln-detail">
                        <span class="ln-detail-label">Tenor</span>
                        <span class="ln-detail-value">{{ $loan->repayment_term ?? '-' }} Bulan</span>
                    </div>
                    <div class="ln-detail">
                        <span class="ln-detail-label">Cicilan / Bulan</span>
                        <span class="ln-detail-value">{{ $loan->monthly_installment ? 'Rp ' . number_format($loan->monthly_installment, 0, ',', '.') : '-' }}</span>
                    </div>
                    <div class="ln-detail">
                        <span class="ln-detail-label">Metode</span>
                        <span class="ln-detail-value">{{ $methodLabel }}</span>
                    </div>
                </div>

                @if($loan->document_path)
                <div class="ln-doc-indicator">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Dokumen tersedia</span>
                </div>
                @endif
            </div>

            <div class="ln-card-footer">
                <a href="{{ route('employee.loan_requests.show', $loan->id) }}" class="ln-btn-detail">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Lihat Detail
                </a>
                @if($st === 'PENDING_HRD')
                <form action="{{ route('employee.loan_requests.destroy', $loan->id) }}" method="POST" class="ln-form-cancel" onsubmit="return confirm('Yakin ingin membatalkan pengajuan hutang ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="ln-btn-cancel">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Batalkan
                    </button>
                </form>
                @endif
            </div>
        </div>

        @empty
        <div class="ln-empty">
            <div class="ln-empty-icon">
                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h3 class="ln-empty-title">Belum Ada Riwayat</h3>
            <p class="ln-empty-desc">Anda belum pernah mengajukan pinjaman. Ajukan pinjaman baru dengan menekan tombol di bawah.</p>
            @if($hasActiveLoan)
            <button type="button" class="ln-btn-primary" onclick="document.getElementById('modal-active-loan').classList.add('show')">
                Ajukan Hutang Baru
            </button>
            @else
            <a href="{{ route('employee.loan_requests.create') }}" class="ln-btn-primary">Ajukan Hutang Baru</a>
            @endif
        </div>
        @endforelse
    </div>

    @if(method_exists($loans, 'links') && $loans->hasPages())
    <div class="ln-pagination">
        {{ $loans->links() }}
    </div>
    @endif

    <style>
        /* ========================================== */
        /* DESIGN TOKENS (inline per view)            */
        /* ========================================== */
        :root {
            --primary-dark: #0A3D62;
            --primary: #145DA0;
            --primary-light: #1E81B0;
            --accent: #D4AF37;
            --white: #FFFFFF;
            --gray-50: #F5F7FA;
            --gray-100: #F8FAFC;
            --gray-200: #E5E7EB;
            --gray-300: #D1D5DB;
            --gray-400: #9CA3AF;
            --gray-500: #6B7280;
            --gray-600: #374151;
            --gray-700: #1F2937;
            --gray-900: #111827;
            --success: #22C55E;
            --success-light: rgba(34, 197, 94, 0.10);
            --warning: #F59E0B;
            --warning-light: rgba(245, 158, 11, 0.10);
            --error: #EF4444;
            --error-light: rgba(239, 68, 68, 0.10);
            --info: #3B82F6;
            --info-light: rgba(59, 130, 246, 0.10);
            --border: #E5E7EB;
            --border-light: #F3F4F6;
            --text-primary: #111827;
            --text-secondary: #374151;
            --text-muted: #6B7280;
            --text-light: #9CA3AF;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
        }

        * { box-sizing: border-box; }

        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .ln-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: var(--radius-lg);
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 500;
        }
        .ln-alert--success {
            background: var(--success-light);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #15803d;
        }

        /* ========================================== */
        /* SECTION HEADER (x-slot)                    */
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
        .section-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy { background: rgba(10, 61, 98, 0.08); color: var(--primary-dark); }

        /* ========================================== */
        /* CTA BAR                                    */
        /* ========================================== */
        .ln-cta-bar {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 16px;
        }
        .ln-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            border: none;
            border-radius: var(--radius-lg);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
            flex-shrink: 0;
            font-family: inherit;
        }
        .ln-btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .ln-btn-primary svg { flex-shrink: 0; }

        /* ========================================== */
        /* LOAN LIST                                  */
        /* ========================================== */
        .ln-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* ========================================== */
        /* LOAN CARD                                  */
        /* ========================================== */
        .ln-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            overflow: hidden;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .ln-card:hover {
            border-color: rgba(20, 93, 160, 0.25);
            box-shadow: 0 4px 14px rgba(10, 61, 98, 0.07);
            transform: translateY(-1px);
        }

        /* Card Header */
        .ln-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light);
            background: linear-gradient(180deg, var(--gray-100) 0%, var(--white) 100%);
        }
        .ln-card-meta {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }
        .ln-card-date {
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.3;
        }
        .ln-card-time {
            font-size: 0.6875rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* Status Badge */
        .ln-badge {
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
        .ln-badge--warning { background: var(--warning-light); color: #a16207; }
        .ln-badge--success { background: var(--success-light); color: #15803d; }
        .ln-badge--error   { background: var(--error-light);   color: #b91c1c; }
        .ln-badge--neutral { background: var(--gray-100);      color: var(--gray-600); border: 1px solid var(--border); }
        .ln-badge--gray    { background: var(--gray-100);      color: var(--gray-600); }

        /* Card Body */
        .ln-card-body {
            padding: 16px;
        }

        /* Amount — Primary Data Point */
        .ln-amount {
            display: flex;
            flex-direction: column;
            gap: 2px;
            margin-bottom: 14px;
        }
        .ln-amount-label {
            font-size: 0.6875rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .ln-amount-value {
            font-size: 1.375rem;
            font-weight: 800;
            color: var(--primary-dark);
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        /* Details Grid */
        .ln-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            padding: 12px;
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-light);
        }
        .ln-detail {
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-width: 0;
        }
        .ln-detail-label {
            font-size: 0.625rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            line-height: 1.2;
        }
        .ln-detail-value {
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--text-secondary);
            line-height: 1.3;
            word-break: break-word;
        }

        /* Document Indicator */
        .ln-doc-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 12px;
            padding: 6px 12px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #0369a1;
            width: fit-content;
        }
        .ln-doc-indicator svg {
            flex-shrink: 0;
        }

        /* Card Footer */
        .ln-card-footer {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            border-top: 1px solid var(--border-light);
            background: linear-gradient(180deg, var(--white) 0%, var(--gray-100) 100%);
        }
        .ln-form-cancel {
            display: inline-flex;
            flex-shrink: 0;
        }
        .ln-btn-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 14px;
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            border-radius: var(--radius-md);
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            font-family: inherit;
        }
        .ln-btn-cancel:hover {
            background: #fee2e2;
            border-color: #fca5a5;
        }
        .ln-btn-detail {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 14px;
            background: var(--white);
            color: var(--primary);
            border: 1.5px solid var(--primary);
            border-radius: var(--radius-md);
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
            flex: 1;
        }
        .ln-btn-detail:hover {
            background: var(--primary);
            color: var(--white);
        }
        .ln-btn-detail svg { flex-shrink: 0; }

        /* ========================================== */
        /* EMPTY STATE                                */
        /* ========================================== */
        .ln-empty {
            text-align: center;
            padding: 48px 24px;
            background: var(--white);
            border-radius: var(--radius-xl);
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .ln-empty-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 16px;
            background: var(--gray-50);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
        }
        .ln-empty-title {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-secondary);
            margin: 0 0 6px;
            letter-spacing: -0.01em;
        }
        .ln-empty-desc {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin: 0 auto 20px;
            max-width: 280px;
            line-height: 1.5;
            font-weight: 500;
        }

        /* ========================================== */
        /* PAGINATION                                 */
        /* ========================================== */
        .ln-pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }

        /* ========================================== */
        /* MODAL                                      */
        /* ========================================== */
        .ln-modal-overlay {
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
        .ln-modal-overlay.show { display: flex; }
        .ln-modal-box {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 28px 24px;
            max-width: 380px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.2);
            animation: ln-modal-in 0.2s ease;
        }
        @keyframes ln-modal-in {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .ln-modal-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--warning-light);
            border: 1px solid rgba(245, 158, 11, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d97706;
            margin: 0 auto 16px;
        }
        .ln-modal-title {
            font-size: 1.0625rem;
            font-weight: 800;
            color: var(--text-primary);
            margin: 0 0 10px;
            letter-spacing: -0.01em;
        }
        .ln-modal-desc {
            font-size: 0.8125rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin: 0 0 22px;
            font-weight: 500;
        }
        .ln-modal-desc strong { color: var(--text-primary); font-weight: 700; }
        .ln-modal-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .ln-modal-btn-continue {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 11px 20px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: var(--white);
            border: none;
            border-radius: var(--radius-lg);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            width: 100%;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        .ln-modal-btn-continue:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.25);
            transform: translateY(-1px);
        }
        .ln-modal-btn-close {
            padding: 10px 20px;
            background: transparent;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-lg);
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            width: 100%;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        .ln-modal-btn-close:hover { background: var(--gray-50); border-color: var(--gray-300); }

        /* ========================================== */
        /* RESPONSIVE — TABLET (480px+)               */
        /* ========================================== */
        @media (min-width: 480px) {
            .ln-cta-bar {
                justify-content: flex-end;
                margin-bottom: 20px;
            }

            .ln-card-header { padding: 16px 20px; }
            .ln-card-body { padding: 20px; }
            .ln-card-footer { padding: 14px 20px; }

            .ln-amount-value { font-size: 1.5rem; }
            .ln-details { gap: 16px; padding: 14px 16px; }
            .ln-detail-label { font-size: 0.6875rem; }
            .ln-detail-value { font-size: 0.875rem; }

            .ln-btn-detail,
            .ln-btn-cancel {
                padding: 10px 18px;
                font-size: 13px;
            }
        }

        /* ========================================== */
        /* RESPONSIVE — DESKTOP (768px+)              */
        /* ========================================== */
        @media (min-width: 768px) {
            .ln-amount-value { font-size: 1.625rem; }

            .ln-card:hover { transform: translateY(-2px); }

            .ln-btn-detail { flex: 0 1 auto; }
        }

        /* ========================================== */
        /* RESPONSIVE — LARGE DESKTOP (1024px+)       */
        /* ========================================== */
        @media (min-width: 1024px) {
            .ln-list {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            .ln-empty {
                grid-column: 1 / -1;
            }
        }
    </style>
</x-app>
