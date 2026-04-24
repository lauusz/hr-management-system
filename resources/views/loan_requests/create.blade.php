<x-app title="Pengajuan Hutang Karyawan">

    {{-- ============================================== --}}
    {{-- PAGE HEADER --}}
    {{-- ============================================== --}}
    <div class="page-header">
        <a href="{{ route('employee.loan_requests.index') }}" class="back-btn">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="page-header-text">
            <h1 class="page-title">Pengajuan Hutang</h1>
            <p class="page-subtitle">Ajukan pinjaman atau kasbon dengan mudah</p>
        </div>
    </div>

    {{-- ============================================== --}}
    {{-- ERROR ALERT --}}
    {{-- ============================================== --}}
    @if ($errors->any())
        <div class="alert-error">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    {{-- ============================================== --}}
    {{-- FORM --}}
    {{-- ============================================== --}}
    <form method="POST" action="{{ route('employee.loan_requests.store') }}" enctype="multipart/form-data" id="form-loan">
        @csrf

        {{-- ============================================== --}}
        {{-- SECTION: DATA PEMOHON --}}
        {{-- ============================================== --}}
        <div class="form-section">
            <div class="section-header">
                <div class="section-number">1</div>
                <div class="section-title">Data Pemohon</div>
            </div>

            <div class="info-card">
                <div class="info-grid">
                    <div class="info-item">
                        <label>Nama Lengkap</label>
                        <span>{{ $snapshot['name'] ?? $user->name }}</span>
                    </div>
                    <div class="info-item">
                        <label>NIK</label>
                        <span>{{ $snapshot['nik'] ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Jabatan</label>
                        <span>{{ $snapshot['position'] ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Divisi / Dept</label>
                        <span>{{ $snapshot['division'] ?? '-' }}</span>
                    </div>
                    <div class="info-item full-width">
                        <label>Perusahaan</label>
                        <span>{{ $snapshot['pt'] ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================== --}}
        {{-- SECTION: RINCIAN PINJAMAN --}}
        {{-- ============================================== --}}
        <div class="form-section">
            <div class="section-header">
                <div class="section-number">2</div>
                <div class="section-title">Rincian Pinjaman</div>
            </div>

            <div class="form-group">
                <label for="amount_display" class="form-label">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Besar Pinjaman <span class="required">*</span>
                </label>
                <div class="input-prefix-wrapper">
                    <span class="prefix-label">Rp</span>
                    <input
                        id="amount_display"
                        type="text"
                        inputmode="numeric"
                        autocomplete="off"
                        class="form-input text-large"
                        placeholder="0"
                        value="{{ old('amount') ? number_format(old('amount'), 0, ',', '.') : '' }}"
                        required>
                </div>
                <input id="amount" type="hidden" name="amount" value="{{ old('amount') }}">
            </div>

            <div class="form-group">
                <label for="purpose" class="form-label">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Keperluan / Alasan <span class="required">*</span>
                </label>
                <textarea
                    id="purpose"
                    name="purpose"
                    rows="3"
                    class="form-textarea"
                    placeholder="Jelaskan alasan pengajuan Anda secara detail..."
                    required>{{ old('purpose') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="disbursement_date" class="form-label">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Tanggal Dibutuhkan
                    </label>
                    <input
                        id="disbursement_date"
                        type="date"
                        name="disbursement_date"
                        class="form-input"
                        value="{{ old('disbursement_date') }}">
                </div>

                <div class="form-group">
                    <label for="monthly_installment_input" class="form-label">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Cicilan Per Bulan <span class="required">*</span>
                    </label>
                    <div class="input-suffix-wrapper">
                        <span class="prefix-label-sm">Rp</span>
                        <input
                            id="monthly_installment_input"
                            type="text"
                            inputmode="numeric"
                            class="form-input text-large"
                            placeholder="0"
                            autocomplete="off"
                            value="{{ old('monthly_installment') ? number_format(old('monthly_installment'), 0, ',', '.') : '' }}">
                        <span class="suffix-label">/ Bulan</span>
                    </div>
                    <input type="hidden" id="monthly_installment" name="monthly_installment" value="{{ old('monthly_installment') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Estimasi Jangka Waktu (Tenor)
                </label>
                <div class="preview-box tenor-preview">
                    <span id="tenor_preview" class="preview-value">-</span>
                </div>
                <small class="helper-text">Jangka waktu dihitung otomatis berdasarkan besar pinjaman dan cicilan per bulan.</small>
            </div>

            <div class="form-group">
                <label for="payment_method" class="form-label">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Metode Pengembalian <span class="required">*</span>
                </label>
                <select id="payment_method" name="payment_method" class="form-select" required>
                    <option value="POTONG_GAJI" @selected(old('payment_method', 'POTONG_GAJI') === 'POTONG_GAJI')>Potong Gaji</option>
                    <option value="TUNAI" @selected(old('payment_method') === 'TUNAI')>Tunai / Cash</option>
                    <option value="CICILAN" @selected(old('payment_method') === 'CICILAN')>Cicilan</option>
                </select>
                <small class="helper-text">Disarankan menggunakan <b>Potong Gaji</b> untuk kemudahan administrasi.</small>
            </div>

            <div class="form-group">
                <label for="document" class="form-label">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Dokumen Pendukung
                </label>
                <div class="file-upload-box">
                    <input
                        id="document"
                        type="file"
                        name="document"
                        class="file-input"
                        accept=".jpg,.jpeg,.png,.pdf">
                    <div class="file-upload-content">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span>Klik untuk upload file</span>
                        <small>JPG, PNG, PDF (Maks 2MB)</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================== --}}
        {{-- SUBMIT BUTTON --}}
        {{-- ============================================== --}}
        <div class="form-actions">
            <button class="btn-submit" type="button" id="btn-submit" onclick="showConfirmModal()">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Kirim Pengajuan
            </button>
        </div>
    </form>

    {{-- ============================================== --}}
    {{-- CONFIRMATION MODAL --}}
    {{-- ============================================== --}}
    <div id="confirmModal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <div class="modal-icon">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="modal-title">Konfirmasi Pengajuan</h3>

            <div class="modal-summary">
                <div class="summary-row">
                    <span>Besar Pinjaman</span>
                    <strong id="summaryAmount">Rp 0</strong>
                </div>
                <div class="summary-row">
                    <span>Cicilan per Bulan</span>
                    <strong id="summaryInstallment">Rp 0</strong>
                </div>
                <div class="summary-row">
                    <span>Jangka Waktu</span>
                    <strong id="summaryTenor">0 Bulan</strong>
                </div>
                <div class="summary-row">
                    <span>Metode</span>
                    <strong id="summaryMethod">Potong Gaji</strong>
                </div>
            </div>

            <div class="modal-warning">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Wajib melunasi pinjaman saat anda keluar dari perusahaan.</span>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="hideConfirmModal()">Batal</button>
                <button type="button" class="btn-confirm" onclick="submitForm()">
                    Ya, Kirim Pengajuan
                </button>
            </div>
        </div>
    </div>

    <style>
        /* ========================================== */
        /* MODAL */
        /* ========================================== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: #fff;
            border-radius: 16px;
            padding: 28px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            animation: slideUp 0.2s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-icon {
            width: 64px;
            height: 64px;
            background: #fef3c7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: #d97706;
        }

        .modal-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #111827;
            margin: 0 0 20px;
        }

        .modal-summary {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-row span {
            font-size: 0.85rem;
            color: #6b7280;
        }

        .summary-row strong {
            font-size: 0.9rem;
            color: #111827;
        }

        .modal-warning {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 20px;
            color: #991b1b;
            text-align: left;
        }

        .modal-warning svg {
            flex-shrink: 0;
            margin-top: 1px;
        }

        .modal-warning span {
            font-size: 0.85rem;
            font-weight: 500;
            line-height: 1.4;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
        }

        .btn-cancel {
            flex: 1;
            padding: 12px 20px;
            background: #fff;
            color: #374151;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-cancel:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .btn-confirm {
            flex: 1;
            padding: 12px 20px;
            background: #1e4a8d;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-confirm:hover {
            background: #163a75;
        }

        /* ========================================== */
        /* PAGE HEADER */
        /* ========================================== */
        .page-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .back-btn {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            color: #374151;
            text-decoration: none;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .back-btn:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .page-header-text {
            flex: 1;
        }

        .page-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            margin: 0 0 2px;
        }

        .page-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }

        /* ========================================== */
        /* ALERTS */
        /* ========================================== */
        .alert-error {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.875rem;
        }

        .alert-error svg {
            flex-shrink: 0;
            margin-top: 1px;
        }

        .required {
            color: #dc2626;
            font-weight: 700;
        }

        .helper-text {
            color: #6b7280;
            font-size: 0.8rem;
            margin-top: 6px;
        }

        /* ========================================== */
        /* FORM SECTIONS */
        /* ========================================== */
        .form-section {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 16px;
            border: 1px solid #f3f4f6;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .section-number {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #1e4a8d;
            color: #fff;
            border-radius: 50%;
            font-size: 0.8rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
        }

        /* ========================================== */
        /* INFO CARD */
        /* ========================================== */
        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px 24px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .info-item label {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-item span {
            font-size: 14px;
            color: #334155;
            font-weight: 600;
        }

        .full-width {
            grid-column: span 2;
        }

        /* ========================================== */
        /* FORM INPUTS */
        /* ========================================== */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            color: #374151;
            background: #fff;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-input:focus {
            border-color: #1e4a8d;
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
        }

        .form-input.text-large {
            font-size: 16px;
            font-weight: 600;
            color: #1e4a8d;
        }

        .form-select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            color: #374151;
            background: #fff;
            transition: all 0.2s ease;
            outline: none;
            cursor: pointer;
        }

        .form-select:focus {
            border-color: #1e4a8d;
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
        }

        .form-textarea {
            width: 100%;
            padding: 14px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            color: #374151;
            background: #fff;
            transition: all 0.2s ease;
            outline: none;
            resize: vertical;
            min-height: 120px;
            line-height: 1.5;
            font-family: inherit;
        }

        .form-textarea:focus {
            border-color: #1e4a8d;
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
        }

        /* ========================================== */
        /* INPUT WITH PREFIX/SUFFIX */
        /* ========================================== */
        .input-prefix-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-prefix-wrapper .form-input {
            padding-left: 42px;
        }

        .prefix-label {
            position: absolute;
            left: 14px;
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            pointer-events: none;
        }

        .input-suffix-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-suffix-wrapper .form-input {
            padding-left: 44px;
            padding-right: 64px;
        }

        .prefix-label-sm {
            position: absolute;
            left: 14px;
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            pointer-events: none;
        }

        .suffix-label {
            position: absolute;
            right: 14px;
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            pointer-events: none;
        }

        /* ========================================== */
        /* PREVIEW BOX */
        /* ========================================== */
        .preview-box {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
        }

        .preview-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #374151;
        }

        /* ========================================== */
        /* FILE UPLOAD */
        /* ========================================== */
        .file-upload-box {
            position: relative;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .file-upload-box:hover {
            border-color: #1e4a8d;
            background: #f8faff;
        }

        .file-input {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: #6b7280;
        }

        .file-upload-content svg {
            color: #9ca3af;
        }

        .file-upload-content span {
            font-size: 0.95rem;
            font-weight: 500;
            color: #374151;
        }

        .file-upload-content small {
            font-size: 0.8rem;
            color: #9ca3af;
        }

        /* ========================================== */
        /* SUBMIT BUTTON */
        /* ========================================== */
        .form-actions {
            margin-top: 24px;
        }

        .btn-submit {
            width: 100%;
            padding: 16px 24px;
            background: #1e4a8d;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s ease;
        }

        .btn-submit:hover {
            background: #163a75;
            transform: translateY(-1px);
        }

        .btn-submit:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
        }

        /* ========================================== */
        /* RESPONSIVE */
        /* ========================================== */
        @media (max-width: 768px) {
            .page-header {
                margin-bottom: 16px;
            }

            .page-title {
                font-size: 1.1rem;
            }

            .form-section {
                padding: 20px 16px;
                margin-bottom: 12px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .form-row .form-group {
                margin-bottom: 16px;
            }

            .form-row .form-group:last-child {
                margin-bottom: 0;
            }

            .tenor-preview {
                margin-top: 8px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: span 1;
            }

            .btn-submit {
                padding: 14px 20px;
            }
        }

        @media (max-width: 480px) {
            .back-btn {
                width: 40px;
                height: 40px;
            }

            .section-number {
                width: 26px;
                height: 26px;
                font-size: 0.75rem;
            }

            .section-title {
                font-size: 0.95rem;
            }
        }
    </style>

    @push('scripts')
    <script>
        function formatRupiahNumber(value) {
            if (!value || isNaN(value)) return '';
            return Number(value).toLocaleString('id-ID');
        }

        function updateAmountFormatting() {
            var displayInput = document.getElementById('amount_display');
            var hiddenInput = document.getElementById('amount');
            if (!displayInput || !hiddenInput) return;

            var raw = displayInput.value || '';
            var digits = raw.replace(/\D/g, '');

            if (digits.length === 0) {
                hiddenInput.value = '';
                displayInput.value = '';
                updateTenorPreview();
                return;
            }

            var numeric = parseInt(digits);
            hiddenInput.value = numeric;
            displayInput.value = formatRupiahNumber(numeric);
            updateTenorPreview();
        }

        function updateMonthlyInstallmentFormatting() {
            var displayInput = document.getElementById('monthly_installment_input');
            var hiddenInput = document.getElementById('monthly_installment');
            if (!displayInput || !hiddenInput) return;

            var raw = displayInput.value || '';
            var digits = raw.replace(/\D/g, '');

            if (digits.length === 0) {
                hiddenInput.value = '';
                displayInput.value = '';
                updateTenorPreview();
                return;
            }

            var numeric = parseInt(digits);
            hiddenInput.value = numeric;
            displayInput.value = formatRupiahNumber(numeric);
            updateTenorPreview();
        }

        function updateTenorPreview() {
            var amount = parseFloat(document.getElementById('amount')?.value || '0');
            var monthlyInstallment = parseFloat(document.getElementById('monthly_installment')?.value || '0');
            var tenorPreview = document.getElementById('tenor_preview');

            if (!tenorPreview) return;

            if (amount > 0 && monthlyInstallment > 0) {
                var tenor = Math.ceil(amount / monthlyInstallment);
                tenorPreview.textContent = tenor + ' Bulan';
            } else {
                tenorPreview.textContent = '-';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            var amountDisplay = document.getElementById('amount_display');
            var monthlyInstallmentInput = document.getElementById('monthly_installment_input');

            if (amountDisplay) {
                amountDisplay.addEventListener('input', updateAmountFormatting);
                amountDisplay.addEventListener('blur', updateAmountFormatting);
            }

            if (monthlyInstallmentInput) {
                monthlyInstallmentInput.addEventListener('input', updateMonthlyInstallmentFormatting);
                monthlyInstallmentInput.addEventListener('blur', updateMonthlyInstallmentFormatting);
            }

            // Initial load
            var hiddenAmount = document.getElementById('amount');
            var hiddenMonthly = document.getElementById('monthly_installment');
            if (hiddenAmount?.value) {
                amountDisplay.value = formatRupiahNumber(hiddenAmount.value);
            }
            if (hiddenMonthly?.value) {
                monthlyInstallmentInput.value = formatRupiahNumber(hiddenMonthly.value);
            }

            updateTenorPreview();
        });

        function showConfirmModal() {
            var form = document.getElementById('form-loan');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            var amount = parseFloat(document.getElementById('amount')?.value || '0');
            var installment = parseFloat(document.getElementById('monthly_installment')?.value || '0');
            var tenor = amount > 0 && installment > 0 ? Math.ceil(amount / installment) : 0;
            var method = document.getElementById('payment_method')?.value || 'POTONG_GAJI';

            var methodLabels = {
                'POTONG_GAJI': 'Potong Gaji',
                'TUNAI': 'Tunai / Cash',
                'CICILAN': 'Cicilan'
            };

            document.getElementById('summaryAmount').textContent = 'Rp ' + formatRupiahNumber(amount);
            document.getElementById('summaryInstallment').textContent = 'Rp ' + formatRupiahNumber(installment);
            document.getElementById('summaryTenor').textContent = tenor + ' Bulan';
            document.getElementById('summaryMethod').textContent = methodLabels[method] || method;

            var modal = document.getElementById('confirmModal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function hideConfirmModal() {
            var modal = document.getElementById('confirmModal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        function submitForm() {
            document.getElementById('form-loan').submit();
        }

        // Close modal on overlay click
        document.getElementById('confirmModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                hideConfirmModal();
            }
        });
    </script>
    @endpush
</x-app>