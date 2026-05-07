<x-app title="Pengajuan Hutang Karyawan">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-amber">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Pengajuan Hutang</h1>
                <p class="section-subtitle">Ajukan pinjaman atau kasbon dengan mudah</p>
            </div>
        </div>
    </x-slot>

    <div class="loan-create-page">

        {{-- Back Button --}}
        <a href="{{ route('employee.loan_requests.index') }}" class="back-btn" aria-label="Kembali ke daftar pengajuan">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('employee.loan_requests.store') }}" enctype="multipart/form-data" id="form-loan">
            @csrf

            {{-- Section 1: Data Pemohon --}}
            <div class="form-card">
                <div class="step-header">
                    <span class="step-num">1</span>
                    <span class="step-title">Data Pemohon</span>
                </div>

                <div class="info-card">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Nama Lengkap</span>
                            <span class="info-value">{{ $snapshot['name'] ?? $user->name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">NIK</span>
                            <span class="info-value">{{ $snapshot['nik'] ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Jabatan</span>
                            <span class="info-value">{{ $snapshot['position'] ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Divisi / Dept</span>
                            <span class="info-value">{{ $snapshot['division'] ?? '-' }}</span>
                        </div>
                        <div class="info-item info-item--full">
                            <span class="info-label">Perusahaan</span>
                            <span class="info-value">{{ $snapshot['pt'] ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 2: Rincian Pinjaman --}}
            <div class="form-card">
                <div class="step-header">
                    <span class="step-num">2</span>
                    <span class="step-title">Rincian Pinjaman</span>
                </div>

                <div class="field-group">
                    <label for="amount_display" class="field-label">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Besar Pinjaman <span class="required">*</span>
                    </label>
                    <div class="input-prefix">
                        <span class="prefix-text">Rp</span>
                        <input
                            id="amount_display"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            class="field-input field-input--prefixed"
                            placeholder="0"
                            value="{{ old('amount') ? number_format(old('amount'), 0, ',', '.') : '' }}"
                            required>
                    </div>
                    <input id="amount" type="hidden" name="amount" value="{{ old('amount') }}">
                </div>

                <div class="field-group">
                    <label for="purpose" class="field-label">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Keperluan / Alasan <span class="required">*</span>
                    </label>
                    <textarea
                        id="purpose"
                        name="purpose"
                        rows="3"
                        class="field-textarea"
                        placeholder="Jelaskan alasan pengajuan Anda secara detail..."
                        required>{{ old('purpose') }}</textarea>
                </div>

                <div class="field-row">
                    <div class="field-group">
                        <label for="disbursement_date" class="field-label">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Tanggal Dibutuhkan
                        </label>
                        <input
                            id="disbursement_date"
                            type="date"
                            name="disbursement_date"
                            class="field-input"
                            value="{{ old('disbursement_date') }}">
                    </div>

                    <div class="field-group">
                        <label for="monthly_installment_input" class="field-label">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Cicilan Per Bulan <span class="required">*</span>
                        </label>
                        <div class="input-prefix">
                            <span class="prefix-text">Rp</span>
                            <input
                                id="monthly_installment_input"
                                type="text"
                                inputmode="numeric"
                                autocomplete="off"
                                class="field-input field-input--prefixed"
                                placeholder="0"
                                value="{{ old('monthly_installment') ? number_format(old('monthly_installment'), 0, ',', '.') : '' }}">
                            <span class="suffix-text">/ Bulan</span>
                        </div>
                        <input type="hidden" id="monthly_installment" name="monthly_installment" value="{{ old('monthly_installment') }}">
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Estimasi Jangka Waktu (Tenor)
                    </label>
                    <div class="preview-box">
                        <span id="tenor_preview" class="preview-value">-</span>
                    </div>
                    <small class="helper-text">Jangka waktu dihitung otomatis berdasarkan besar pinjaman dan cicilan per bulan.</small>
                </div>

                <div class="field-group">
                    <label for="payment_method" class="field-label">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        Metode Pengembalian <span class="required">*</span>
                    </label>
                    <select id="payment_method" name="payment_method" class="field-select" required>
                        <option value="POTONG_GAJI" @selected(old('payment_method', 'POTONG_GAJI') === 'POTONG_GAJI')>Potong Gaji</option>
                        <option value="TUNAI" @selected(old('payment_method') === 'TUNAI')>Tunai / Cash</option>
                        <option value="CICILAN" @selected(old('payment_method') === 'CICILAN')>Cicilan</option>
                    </select>
                    <small class="helper-text">Disarankan menggunakan <b>Potong Gaji</b> untuk kemudahan administrasi.</small>
                </div>

                <div class="field-group">
                    <label for="document" class="field-label">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Dokumen Pendukung
                    </label>
                    <div class="file-upload-box" id="file-upload-box">
                        <input
                            id="document"
                            type="file"
                            name="document"
                            class="file-input"
                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt"
                            onchange="handleFileSelect(this)">
                        <div class="file-upload-content" id="file-upload-content">
                            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <span class="file-upload-title">Klik untuk upload file</span>
                            <span class="file-upload-desc">JPG, PNG, PDF, DOC, DOCX, XLS, XLSX, TXT (Maks 8MB)</span>
                        </div>
                        <div class="file-upload-selected" id="file-upload-selected" style="display:none;">
                            <div class="file-preview" id="file-preview"></div>
                            <div class="file-info">
                                <span class="file-name" id="file-name"></span>
                                <span class="file-size" id="file-size"></span>
                            </div>
                            <button type="button" class="file-remove" onclick="removeFile(event)" title="Hapus file">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="form-actions">
                <button class="btn-submit" type="button" id="btn-submit" onclick="showConfirmModal()">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Kirim Pengajuan
                </button>
            </div>
        </form>

        {{-- Confirmation Modal --}}
        <div id="confirmModal" class="modal-overlay" style="display:none;">
            <div class="modal-content">
                <div class="modal-icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Wajib melunasi pinjaman saat anda keluar dari perusahaan.</span>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-modal btn-modal--secondary" onclick="hideConfirmModal()">Batal</button>
                    <button type="button" class="btn-modal btn-modal--primary" onclick="submitForm()">
                        Ya, Kirim Pengajuan
                    </button>
                </div>
            </div>
        </div>

    </div>

    <style>
        /* ============================================= */
        /* LOAN CREATE PAGE — Scoped Styles              */
        /* ============================================= */
        .loan-create-page {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* --------------------------------------------- */
        /* Header Slot Styles (shared pattern)           */
        /* --------------------------------------------- */
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0;
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

        .icon-amber {
            background: rgba(245, 158, 11, 0.08);
            color: #D97706;
        }

        /* --------------------------------------------- */
        /* Back Button (KIMI.md pattern)                 */
        /* --------------------------------------------- */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            align-self: flex-start;
            height: 36px;
            padding: 0 12px 0 10px;
            background: #FFFFFF;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            color: #6B7280;
            text-decoration: none;
            transition: all 0.15s ease;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            font-family: inherit;
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

        /* --------------------------------------------- */
        /* Alerts                                        */
        /* --------------------------------------------- */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.8125rem;
            font-weight: 500;
            line-height: 1.5;
        }

        .alert svg {
            flex-shrink: 0;
            margin-top: 1px;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #15803D;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #991B1B;
        }

        /* --------------------------------------------- */
        /* Form Card (Step)                              */
        /* --------------------------------------------- */
        .form-card {
            background: #FFFFFF;
            border-radius: 16px;
            padding: 20px 16px;
            border: 1px solid #E5E7EB;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .step-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }

        .step-num {
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0A3D62, #145DA0);
            color: #FFFFFF;
            border-radius: 50%;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .step-title {
            font-size: 0.9375rem;
            font-weight: 700;
            color: #111827;
        }

        /* --------------------------------------------- */
        /* Info Card (Read-only data)                    */
        /* --------------------------------------------- */
        .info-card {
            background: #F5F7FA;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            padding: 14px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .info-label {
            font-size: 0.6875rem;
            color: #9CA3AF;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            line-height: 1.2;
        }

        .info-value {
            font-size: 0.875rem;
            color: #374151;
            font-weight: 600;
            line-height: 1.35;
        }

        /* --------------------------------------------- */
        /* Form Fields                                   */
        /* --------------------------------------------- */
        .field-group {
            margin-bottom: 16px;
        }

        .field-group:last-child {
            margin-bottom: 0;
        }

        .field-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0;
        }

        .field-row .field-group:last-child {
            margin-bottom: 16px;
        }

        .field-row .field-group:last-child:last-child {
            margin-bottom: 0;
        }

        .field-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            letter-spacing: 0.01em;
        }

        .field-label svg {
            flex-shrink: 0;
            color: #6B7280;
        }

        .required {
            color: #EF4444;
            font-weight: 700;
        }

        .field-input,
        .field-select,
        .field-textarea {
            width: 100%;
            border: 1.5px solid #E5E7EB;
            border-radius: 12px;
            font-size: 0.9375rem;
            font-family: inherit;
            color: #111827;
            background: #FFFFFF;
            transition: all 0.2s ease;
            outline: none;
        }

        .field-input {
            height: 48px;
            padding: 0 14px;
        }

        .field-input::placeholder {
            color: #9CA3AF;
        }

        .field-input:focus,
        .field-select:focus,
        .field-textarea:focus {
            border-color: #145DA0;
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }

        .field-input--prefixed {
            padding-left: 44px;
            padding-right: 14px;
        }

        .field-select {
            height: 48px;
            padding: 0 40px 0 14px;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 20px;
        }

        .field-textarea {
            padding: 12px 14px;
            resize: vertical;
            min-height: 100px;
            line-height: 1.5;
        }

        .helper-text {
            display: block;
            color: #6B7280;
            font-size: 0.75rem;
            margin-top: 6px;
            font-weight: 500;
            line-height: 1.4;
        }

        /* --------------------------------------------- */
        /* Input Prefix / Suffix                         */
        /* --------------------------------------------- */
        .input-prefix {
            position: relative;
            display: flex;
            align-items: center;
        }

        .prefix-text {
            position: absolute;
            left: 14px;
            font-size: 0.875rem;
            font-weight: 700;
            color: #9CA3AF;
            pointer-events: none;
        }

        .suffix-text {
            position: absolute;
            right: 14px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #9CA3AF;
            pointer-events: none;
        }

        /* --------------------------------------------- */
        /* Preview Box                                   */
        /* --------------------------------------------- */
        .preview-box {
            display: flex;
            align-items: center;
            padding: 12px 14px;
            background: #F5F7FA;
            border: 1.5px solid #E5E7EB;
            border-radius: 12px;
        }

        .preview-value {
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
        }

        /* --------------------------------------------- */
        /* File Upload                                   */
        /* --------------------------------------------- */
        .file-upload-box {
            position: relative;
            border: 2px dashed #D1D5DB;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            background: #F5F7FA;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .file-upload-box:hover {
            border-color: #145DA0;
            background: rgba(20, 93, 160, 0.04);
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
            gap: 6px;
            color: #6B7280;
        }

        .file-upload-content svg {
            color: #9CA3AF;
        }

        .file-upload-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
        }

        .file-upload-desc {
            font-size: 0.75rem;
            color: #9CA3AF;
            font-weight: 500;
        }

        .file-upload-selected {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            border-radius: 12px;
            text-align: left;
        }

        .file-preview {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
            background: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .file-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .file-preview svg {
            color: #22C55E;
        }

        .file-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            flex: 1;
            min-width: 0;
        }

        .file-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: #065f46;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .file-size {
            font-size: 0.75rem;
            color: #22C55E;
            font-weight: 500;
        }

        .file-remove {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 6px;
            color: #EF4444;
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .file-remove:hover {
            background: rgba(239, 68, 68, 0.15);
        }

        /* --------------------------------------------- */
        /* Submit Button                                 */
        /* --------------------------------------------- */
        .form-actions {
            margin-top: 4px;
        }

        .btn-submit {
            width: 100%;
            height: 48px;
            padding: 0 24px;
            background: linear-gradient(135deg, #0A3D62, #145DA0);
            color: #FFFFFF;
            border: none;
            border-radius: 12px;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .btn-submit:hover {
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.25);
            transform: translateY(-1px);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* --------------------------------------------- */
        /* Modal                                         */
        /* --------------------------------------------- */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 16px;
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-content {
            background: #FFFFFF;
            border-radius: 16px;
            padding: 24px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            animation: slideUp 0.2s ease;
        }

        .modal-icon {
            width: 56px;
            height: 56px;
            background: rgba(245, 158, 11, 0.12);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            color: #F59E0B;
        }

        .modal-title {
            font-size: 1.0625rem;
            font-weight: 700;
            color: #111827;
            margin: 0 0 18px;
        }

        .modal-summary {
            background: #F5F7FA;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 14px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 0;
            border-bottom: 1px solid #F3F4F6;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-row span {
            font-size: 0.8125rem;
            color: #6B7280;
        }

        .summary-row strong {
            font-size: 0.875rem;
            color: #111827;
        }

        .modal-warning {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 12px;
            padding: 10px 12px;
            margin-bottom: 18px;
            color: #991B1B;
            text-align: left;
        }

        .modal-warning svg {
            flex-shrink: 0;
            margin-top: 2px;
            color: #EF4444;
        }

        .modal-warning span {
            font-size: 0.8125rem;
            font-weight: 500;
            line-height: 1.4;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
        }

        .btn-modal {
            flex: 1;
            padding: 11px 16px;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .btn-modal--secondary {
            background: #FFFFFF;
            color: #374151;
            border: 1.5px solid #E5E7EB;
        }

        .btn-modal--secondary:hover {
            background: #F5F7FA;
            border-color: #D1D5DB;
        }

        .btn-modal--primary {
            background: linear-gradient(135deg, #0A3D62, #145DA0);
            color: #FFFFFF;
            border: none;
        }

        .btn-modal--primary:hover {
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.25);
            transform: translateY(-1px);
        }

        /* ============================================= */
        /* RESPONSIVE — Tablet & Desktop                 */
        /* ============================================= */
        @media (min-width: 768px) {
            .loan-create-page {
                gap: 12px;
            }

            .back-btn {
                height: 40px;
                padding: 0 14px 0 12px;
            }

            .back-btn-text {
                font-size: 0.8125rem;
            }

            .form-card {
                padding: 24px;
            }

            .step-num {
                width: 28px;
                height: 28px;
                font-size: 0.8rem;
            }

            .step-title {
                font-size: 1rem;
            }

            .info-grid {
                grid-template-columns: 1fr 1fr;
                gap: 14px 24px;
            }

            .info-item--full {
                grid-column: span 2;
            }

            .field-row {
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }

            .field-input {
                height: 52px;
                padding: 0 16px;
            }

            .field-input--prefixed {
                padding-left: 48px;
                padding-right: 16px;
            }

            .field-select {
                height: 52px;
                padding: 0 40px 0 16px;
            }

            .field-textarea {
                padding: 14px 16px;
                min-height: 120px;
            }

            .preview-box {
                padding: 14px 16px;
            }

            .preview-value {
                font-size: 1.125rem;
            }

            .file-upload-box {
                padding: 24px;
            }

            .btn-submit {
                height: 52px;
            }

            .modal-content {
                padding: 28px;
            }

            .modal-icon {
                width: 64px;
                height: 64px;
                margin-bottom: 16px;
            }

            .modal-title {
                font-size: 1.125rem;
            }

            .modal-actions {
                gap: 12px;
            }

            .btn-modal {
                padding: 12px 20px;
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

        function handleFileSelect(input) {
            var file = input.files[0];
            if (!file) return;

            var content = document.getElementById('file-upload-content');
            var selected = document.getElementById('file-upload-selected');
            var nameEl = document.getElementById('file-name');
            var sizeEl = document.getElementById('file-size');
            var previewEl = document.getElementById('file-preview');

            content.style.display = 'none';
            selected.style.display = 'flex';
            nameEl.textContent = file.name;
            sizeEl.textContent = formatFileSize(file.size);

            if (file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    previewEl.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                };
                reader.readAsDataURL(file);
            } else {
                previewEl.innerHTML = '<svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
            }
        }

        function removeFile(e) {
            e.stopPropagation();
            var input = document.getElementById('document');
            input.value = '';

            document.getElementById('file-upload-content').style.display = 'flex';
            document.getElementById('file-upload-selected').style.display = 'none';
            document.getElementById('file-preview').innerHTML = '';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            var k = 1024;
            var sizes = ['Bytes', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        document.getElementById('confirmModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                hideConfirmModal();
            }
        });
    </script>
    @endpush
</x-app>
