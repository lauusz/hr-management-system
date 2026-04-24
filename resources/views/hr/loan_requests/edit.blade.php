<x-app title="Edit Pinjaman">

    @if(session('success'))
    <div class="alert alert-success">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-error">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ $errors->first() }}
    </div>
    @endif

    @php
        $currentTenor = $loan->repayment_term > 0 ? $loan->repayment_term : ceil($loan->amount / $loan->monthly_installment);
    @endphp

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <a href="{{ route('hr.loan_requests.show', $loan->id) }}" class="back-btn">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="page-header-text">
            <h1 class="page-title">Edit Pinjaman</h1>
            <p class="page-subtitle">{{ $loan->snapshot_name }}</p>
        </div>
    </div>

    {{-- SUMMARY STATS --}}
    <div class="stats-row">
        <div class="stat-card stat-main">
            <div class="stat-value">Rp {{ number_format($loan->amount, 0, ',', '.') }}</div>
            <div class="stat-label">Total Pinjaman</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $currentTenor }}</div>
            <div class="stat-label">Tenor</div>
        </div>
    </div>

    {{-- FORM SECTION --}}
    <form action="{{ route('hr.loan_requests.update', $loan->id) }}" method="POST" class="edit-form">
        @csrf
        @method('PUT')

        {{-- SECTION: DETAIL PINJAMAN --}}
        <div class="section">
            <div class="section-header">
                <div class="section-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="section-title">Detail Pinjaman</h2>
            </div>
            <div class="card">
                <div class="data-grid">
                    <div class="data-item full-width">
                        <span class="data-label">Jumlah Pinjaman (Rp) <span class="req">*</span></span>
                        <input type="text" inputmode="numeric" class="form-input text-large @error('amount') is-invalid @enderror" id="amount_display" value="{{ number_format($loan->amount, 0, ',', '.') }}" placeholder="0">
                        <input type="hidden" name="amount" id="amount" value="{{ $loan->amount }}">
                        @error('amount')
                        <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="data-item full-width">
                        <span class="data-label">Cicilan/Bulan (Rp) <span class="req">*</span></span>
                        <input type="text" inputmode="numeric" class="form-input text-large @error('monthly_installment') is-invalid @enderror" id="installment_display" value="{{ number_format($loan->monthly_installment, 0, ',', '.') }}" placeholder="0">
                        <input type="hidden" name="monthly_installment" id="installment" value="{{ $loan->monthly_installment }}">
                        @error('monthly_installment')
                        <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION: KONFIGURASI --}}
        <div class="section">
            <div class="section-header">
                <div class="section-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h2 class="section-title">Konfigurasi</h2>
            </div>
            <div class="card">
                <div class="config-grid">
                    <div class="data-item">
                        <span class="data-label">Metode Pembayaran <span class="req">*</span></span>
                        <select name="payment_method" class="form-input @error('payment_method') is-invalid @enderror" required>
                            <option value="POTONG_GAJI" @selected($loan->payment_method === 'POTONG_GAJI')>Potong Gaji</option>
                            <option value="TRANSFER" @selected($loan->payment_method === 'CICILAN')>Transfer Bank</option>
                            <option value="TUNAI" @selected($loan->payment_method === 'TUNAI')>Tunai</option>
                        </select>
                        @error('payment_method')
                        <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="data-item">
                        <span class="data-label">Tenor (Bulan) <span class="req">*</span></span>
                        <input type="number" name="repayment_term" id="repayment_term" class="form-input @error('repayment_term') is-invalid @enderror" value="{{ $currentTenor }}" min="1" required>
                        @error('repayment_term')
                        <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="data-grid">
                    <div class="data-item full-width">
                        <span class="data-label">Tanggal Cair</span>
                        <input type="date" name="disbursement_date" class="form-input" value="{{ $loan->disbursement_date?->format('Y-m-d') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION: KETERANGAN --}}
        <div class="section">
            <div class="section-header">
                <div class="section-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <h2 class="section-title">Keterangan</h2>
            </div>
            <div class="card">
                <div class="data-grid">
                    <div class="data-item full-width">
                        <span class="data-label">Keperluan</span>
                        <textarea name="purpose" class="form-input" rows="3" placeholder="Deskripsi keperluan pinjaman">{{ $loan->purpose }}</textarea>
                    </div>
                    <div class="data-item full-width">
                        <span class="data-label">Catatan untuk Employee</span>
                        <textarea name="notes" class="form-input" rows="2" placeholder="Catatan yang akan dilihat employee">{{ $loan->notes }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="form-actions">
            <a href="{{ route('hr.loan_requests.show', $loan->id) }}" class="btn-cancel">Batal</a>
            <button type="submit" class="btn-submit">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Perubahan
            </button>
        </div>
    </form>

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
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

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
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .page-subtitle {
            font-size: 13px;
            color: var(--text-secondary);
            margin: 2px 0 0;
        }

        /* STATS ROW */
        .stats-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 10px;
            margin-bottom: 16px;
        }

        .stat-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px;
            text-align: center;
        }

        .stat-main {
            background: var(--navy);
            border-color: var(--navy);
        }

        .stat-main .stat-value { color: var(--white); }
        .stat-main .stat-label { color: rgba(255,255,255,0.8); }

        .stat-value {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-label {
            font-size: 10px;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* SECTION */
        .section { margin-bottom: 14px; }

        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .section-icon {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eff6ff;
            border-radius: 8px;
            color: var(--navy);
            flex-shrink: 0;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
            flex: 1;
        }

        /* CARD */
        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
        }

        /* DATA GRID */
        .data-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1px;
            background: var(--border);
        }

        .config-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1px;
            background: var(--border);
        }

        .data-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 12px 14px;
            background: var(--white);
        }

        .data-item.full-width { grid-column: span 1; }

        .data-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .req { color: #dc2626; }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            color: var(--text-primary);
            background: var(--white);
            transition: border-color 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--navy);
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
        }

        .form-input.text-large {
            font-size: 16px;
            font-weight: 600;
            color: var(--navy);
        }

        .form-input.is-invalid {
            border-color: #dc2626;
        }

        .error-text {
            display: block;
            font-size: 11px;
            color: #dc2626;
            margin-top: 4px;
        }

        /* FORM ACTIONS */
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-cancel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 16px;
            background: var(--white);
            color: var(--text-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-cancel:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }

        .btn-submit {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 16px;
            background: var(--navy);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-submit:hover { background: var(--navy-dark); }

        /* RESPONSIVE - DESKTOP */
        @media (min-width: 768px) {
            .page-header { margin-bottom: 20px; }
            .page-title { font-size: 20px; }
            .page-subtitle { font-size: 14px; }

            .stats-row {
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
            }

            .stat-card { padding: 16px; }
            .stat-value { font-size: 20px; }
            .stat-label { font-size: 11px; }

            .section { margin-bottom: 16px; }
            .section-header { margin-bottom: 10px; }
            .section-icon { width: 32px; height: 32px; }
            .section-title { font-size: 14px; }

            .card { border-radius: 12px; }

            .data-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .data-item { padding: 14px 16px; }
            .data-item.full-width { grid-column: span 2; }

            .data-label { font-size: 11px; }

            .form-input { padding: 12px 14px; }
            .form-input.text-large { font-size: 18px; }

            .form-actions {
                gap: 12px;
                margin-top: 24px;
            }

            .btn-cancel, .btn-submit {
                flex: none;
                padding: 12px 32px;
            }
        }

        @media (min-width: 1024px) {
            .data-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .data-item.full-width { grid-column: span 3; }
        }
    </style>

    @push('scripts')
    <script>
        function formatRupiahNumber(value) {
            if (!value || isNaN(value)) return '';
            return Number(value).toLocaleString('id-ID');
        }

        function setupRupiahInput(displayId, hiddenId) {
            var display = document.getElementById(displayId);
            var hidden = document.getElementById(hiddenId);
            if (!display || !hidden) return;

            display.addEventListener('input', function() {
                var digits = (display.value || '').replace(/[^\d]/g, '');
                if (digits.length === 0) {
                    hidden.value = '';
                    display.value = '';
                    return;
                }
                var numeric = parseInt(digits);
                hidden.value = numeric;
                display.value = formatRupiahNumber(numeric);
            });

            display.addEventListener('blur', function() {
                var digits = (display.value || '').replace(/[^\d]/g, '');
                if (digits.length > 0) {
                    display.value = formatRupiahNumber(parseInt(digits));
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            setupRupiahInput('amount_display', 'amount');
            setupRupiahInput('installment_display', 'installment');

            var amountInput = document.getElementById('amount');
            var installmentInput = document.getElementById('installment');
            var tenorInput = document.getElementById('repayment_term');

            function calculateTenor() {
                var amount = parseInt(amountInput.value) || 0;
                var installment = parseInt(installmentInput.value) || 0;

                if (amount > 0 && installment > 0) {
                    var tenor = Math.ceil(amount / installment);
                    tenorInput.value = tenor;
                }
            }

            var installmentDisplay = document.getElementById('installment_display');
            if (installmentDisplay) {
                installmentDisplay.addEventListener('input', calculateTenor);
                installmentDisplay.addEventListener('blur', calculateTenor);
            }

            var amountDisplay = document.getElementById('amount_display');
            if (amountDisplay) {
                amountDisplay.addEventListener('input', calculateTenor);
                amountDisplay.addEventListener('blur', calculateTenor);
            }
        });
    </script>
    @endpush
</x-app>