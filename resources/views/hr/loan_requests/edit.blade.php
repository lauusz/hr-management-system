<x-app title="Edit Pinjaman">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Edit Pinjaman</h1>
                <p class="section-subtitle">{{ $loan->snapshot_name }}</p>
            </div>
        </div>
    </x-slot>

    <div class="edit-wrapper">

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

        $docUrl = $loan->document_path ? asset('storage/' . $loan->document_path) : null;
        $docExt = $loan->document_path ? strtolower(pathinfo($loan->document_path, PATHINFO_EXTENSION)) : null;
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
        $isImageDoc = $docExt && in_array($docExt, $imageExts);
        $docName = $loan->document_path ? basename($loan->document_path) : null;

        // File type config for non-image documents
        $fileTypeConfig = $docExt ? match($docExt) {
            'pdf' => ['label' => 'Dokumen PDF', 'color' => 'pdf', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v6a1 1 0 001 1h6"/>'],
            'doc', 'docx' => ['label' => 'Dokumen Word', 'color' => 'word', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
            'xls', 'xlsx' => ['label' => 'Dokumen Excel', 'color' => 'excel', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
            'txt' => ['label' => 'File Teks', 'color' => 'text', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
            default => ['label' => 'Dokumen', 'color' => 'default', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
        } : ['label' => 'Dokumen', 'color' => 'default', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'];
    @endphp

    {{-- BACK BUTTON --}}
    <div class="page-header">
        <a href="{{ route('hr.loan_requests.show', $loan->id) }}" class="back-btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span class="back-btn-text">Kembali</span>
        </a>
    </div>

    {{-- LOAN SUMMARY BAR --}}
    <div class="loan-summary-bar">
        <div class="summary-amount">
            <span class="summary-amount-value">Rp {{ number_format($loan->amount, 0, ',', '.') }}</span>
            <span class="summary-amount-label">Total Pinjaman</span>
        </div>
        <div class="summary-meta">
            <div class="summary-meta-item">
                <span class="summary-meta-label">Tenor</span>
                <span class="summary-meta-value">{{ $currentTenor }} bulan</span>
            </div>
            <div class="summary-meta-divider"></div>
            <div class="summary-meta-item">
                <span class="summary-meta-label">Cicilan/Bulan</span>
                <span class="summary-meta-value">Rp {{ number_format($loan->monthly_installment, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- FORM CONTAINER --}}
    <div class="form-container">
        <form action="{{ route('hr.loan_requests.update', $loan->id) }}" method="POST" class="edit-form" enctype="multipart/form-data">
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
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Jumlah Pinjaman (Rp) <span class="req">*</span></label>
                            <input type="text" inputmode="numeric" class="form-input text-large @error('amount') is-invalid @enderror" id="amount_display" value="{{ number_format($loan->amount, 0, ',', '.') }}" placeholder="0">
                            <input type="hidden" name="amount" id="amount" value="{{ $loan->amount }}">
                            @error('amount')
                            <span class="error-text">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cicilan/Bulan (Rp) <span class="req">*</span></label>
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
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Metode Pembayaran <span class="req">*</span></label>
                            <select name="payment_method" class="form-input @error('payment_method') is-invalid @enderror" required>
                                <option value="POTONG_GAJI" @selected($loan->payment_method === 'POTONG_GAJI')>Potong Gaji</option>
                                <option value="TRANSFER" @selected($loan->payment_method === 'CICILAN')>Transfer Bank</option>
                                <option value="TUNAI" @selected($loan->payment_method === 'TUNAI')>Tunai</option>
                            </select>
                            @error('payment_method')
                            <span class="error-text">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tenor (Bulan) <span class="req">*</span></label>
                            <input type="number" name="repayment_term" id="repayment_term" class="form-input @error('repayment_term') is-invalid @enderror" value="{{ $currentTenor }}" min="1" required>
                            @error('repayment_term')
                            <span class="error-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-row form-row--single">
                        <div class="form-group">
                            <label class="form-label">Tanggal Cair</label>
                            <input type="date" name="disbursement_date" class="form-input" value="{{ $loan->disbursement_date?->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION: DOKUMEN PENDUKUNG --}}
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h2 class="section-title">Dokumen Pendukung</h2>
                </div>
                <div class="card">
                    @if($loan->document_path)
                    <div class="doc-existing">
                        <span class="doc-existing-label">Dokumen saat ini</span>
                        @if($isImageDoc)
                        <div class="doc-image-wrap">
                            <a href="{{ $docUrl }}" target="_blank" class="doc-image-link">
                                <img src="{{ $docUrl }}" alt="Dokumen Pendukung" loading="lazy">
                            </a>
                        </div>
                        @else
                        <div class="doc-file">
                            <div class="doc-file-icon icon-{{ $fileTypeConfig['color'] }}">
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $fileTypeConfig['svg'] !!}</svg>
                            </div>
                            <div class="doc-file-info">
                                <span class="doc-file-name">{{ $docName }}</span>
                                <span class="doc-file-type">{{ $fileTypeConfig['label'] }} &middot; {{ strtoupper($docExt) }}</span>
                            </div>
                            <a href="{{ $docUrl }}" target="_blank" class="doc-file-btn">Lihat</a>
                        </div>
                        @endif
                    </div>

                    <div class="doc-action-group">
                        <label class="doc-checkbox">
                            <input type="checkbox" name="delete_document" value="1" id="delete_document">
                            <span class="doc-checkbox-box">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span class="doc-checkbox-label">Hapus dokumen saat ini</span>
                        </label>
                    </div>
                    @endif

                    {{-- PREVIEW DOKUMEN BARU (muncul setelah pilih file) --}}
                    <div class="doc-new-preview" id="doc-new-preview" style="display: none;">
                        <span class="doc-existing-label">Preview dokumen baru</span>
                        <div id="doc-new-preview-content"></div>
                    </div>

                    <div class="doc-upload" id="doc-upload-area">
                        <label class="form-label">{{ $loan->document_path ? 'Ganti Dokumen' : 'Unggah Dokumen' }}</label>
                        <div class="file-input-wrapper">
                            <input type="file" name="document" id="document" class="file-input" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                            <div class="file-input-trigger">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <span class="file-input-text" id="file-label-text">Klik untuk pilih file</span>
                            </div>
                        </div>
                        <span class="file-input-hint">Format: JPG, PNG, PDF, DOC, XLS, TXT. Maks 8 MB.</span>
                        @error('document')
                        <span class="error-text">{{ $message }}</span>
                        @enderror
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
                    <div class="form-row form-row--stack">
                        <div class="form-group">
                            <label class="form-label">Keperluan</label>
                            <textarea name="purpose" class="form-input" rows="3" placeholder="Deskripsi keperluan pinjaman">{{ $loan->purpose }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Catatan untuk Employee</label>
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
    </div>{{-- end form-container --}}
    </div>{{-- end edit-wrapper --}}

    <style>
        :root {
            --primary-dark: #0A3D62;
            --primary: #145DA0;
            --primary-light: #1E81B0;
            --accent: #D4AF37;
            --accent-light: #E6C65C;
            --accent-dark: #B8962E;
            --success: #22C55E;
            --warning: #F59E0B;
            --error: #EF4444;
            --info: #3B82F6;
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
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
        }

        * { box-sizing: border-box; }

        /* ALERT */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: var(--radius-lg);
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #16a34a;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        /* PAGE HEADER */
        .page-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 12px 0 10px;
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            color: var(--gray-500);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            cursor: pointer;
            font-family: inherit;
        }

        .back-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--gray-50);
        }

        .back-btn:hover svg { transform: translateX(-2px); }
        .back-btn svg { transition: transform 0.2s ease; flex-shrink: 0; }

        .back-btn-text {
            display: none;
        }

        .page-header-text { flex: 1; min-width: 0; }

        .page-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            letter-spacing: -0.02em;
        }

        .page-subtitle {
            font-size: 13px;
            color: var(--gray-500);
            margin: 2px 0 0;
        }

        /* LOAN SUMMARY BAR */
        .loan-summary-bar {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 14px 16px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .summary-amount {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
            flex: 1;
        }

        .summary-amount-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-dark);
            letter-spacing: -0.02em;
        }

        .summary-amount-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--gray-400);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .summary-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            flex: 1.2;
        }

        .summary-meta-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .summary-meta-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--gray-400);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .summary-meta-value {
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-700);
        }

        .summary-meta-divider {
            width: 1px;
            height: 24px;
            background: var(--gray-200);
            flex-shrink: 0;
        }

        /* EDIT WRAPPER */
        .edit-wrapper {
            width: 100%;
        }

        /* FORM CONTAINER */
        .form-container {
            width: 100%;
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
            background: rgba(20, 93, 160, 0.08);
            border-radius: var(--radius-md);
            color: var(--primary);
            flex-shrink: 0;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
            flex: 1;
        }

        /* CARD */
        .card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        /* FORM ROW & GROUP */
        .form-row {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .form-row--single {
            margin-top: 14px;
        }

        .form-row--stack {
            gap: 14px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
            flex: 1;
        }

        .form-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .req { color: var(--error); }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 14px;
            color: var(--gray-900);
            background: var(--white);
            transition: all 0.2s ease;
            line-height: 1.5;
            font-family: inherit;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }

        .form-input.text-large {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary);
        }

        .form-input.is-invalid {
            border-color: var(--error);
        }

        .form-input.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        select.form-input {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236B7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 32px;
        }

        textarea.form-input {
            resize: vertical;
            min-height: 80px;
        }

        .error-text {
            display: block;
            font-size: 12px;
            color: var(--error);
            margin-top: 2px;
        }

        /* FORM ACTIONS */
        .form-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
            padding-bottom: 24px;
        }

        .btn-cancel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 16px;
            background: var(--white);
            color: var(--gray-600);
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-lg);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            white-space: nowrap;
        }

        .btn-cancel:hover {
            background: var(--gray-50);
            border-color: var(--gray-300);
        }

        .btn-submit {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 16px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: var(--white);
            border: none;
            border-radius: var(--radius-lg);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.25);
        }

        .btn-submit:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.35);
            transform: translateY(-1px);
        }

        .btn-submit svg {
            flex-shrink: 0;
        }

        /* RESPONSIVE - TABLET */
        @media (min-width: 480px) {
            .back-btn-text {
                display: inline;
            }

            .page-title {
                font-size: 20px;
            }

            .loan-summary-bar {
                padding: 16px 20px;
                gap: 20px;
            }

            .summary-amount-value {
                font-size: 20px;
            }

            .summary-meta {
                gap: 16px;
            }

            .summary-meta-value {
                font-size: 14px;
            }

            .summary-meta-divider {
                height: 28px;
            }

            .card {
                padding: 18px;
            }
        }

        /* RESPONSIVE - DESKTOP */
        @media (min-width: 768px) {
            .page-header {
                margin-bottom: 20px;
            }

            .loan-summary-bar {
                margin-bottom: 20px;
                padding: 18px 24px;
            }

            .summary-amount-value {
                font-size: 22px;
            }

            .summary-meta-value {
                font-size: 15px;
            }

            .section {
                margin-bottom: 16px;
            }

            .section-header {
                margin-bottom: 10px;
            }

            .section-icon {
                width: 32px;
                height: 32px;
            }

            .section-title {
                font-size: 14px;
            }

            .card {
                padding: 20px;
                border-radius: var(--radius-xl);
            }

            .form-row {
                flex-direction: row;
                gap: 16px;
            }

            .form-row--single {
                margin-top: 16px;
            }

            .form-row--single .form-group {
                max-width: 50%;
            }

            .form-row--stack {
                flex-direction: column;
                gap: 16px;
            }

            .form-group {
                gap: 8px;
            }

            .form-label {
                font-size: 12px;
            }

            .form-input {
                padding: 12px 14px;
                font-size: 14px;
            }

            .form-input.text-large {
                font-size: 18px;
            }

            textarea.form-input {
                min-height: 100px;
            }

            .form-actions {
                flex-direction: row;
                justify-content: flex-start;
                gap: 12px;
                margin-top: 24px;
                padding-bottom: 0;
            }

            .btn-cancel,
            .btn-submit {
                flex: none;
                padding: 12px 28px;
                min-width: 140px;
            }
        }

        /* DOCUMENT SECTION */
        .doc-existing {
            margin-bottom: 14px;
        }

        .doc-new-preview {
            margin-bottom: 14px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .doc-existing-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-400);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 8px;
        }

        .doc-image-wrap {
            border-radius: var(--radius-md);
            overflow: hidden;
            background: var(--gray-100);
            border: 1px solid var(--gray-200);
        }

        .doc-image-link {
            display: block;
            line-height: 0;
        }

        .doc-image-link img {
            width: 100%;
            max-height: 280px;
            object-fit: contain;
            display: block;
            transition: transform 0.3s ease;
        }

        .doc-image-link:hover img {
            transform: scale(1.02);
        }

        .doc-file {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
        }

        .doc-file-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(20, 93, 160, 0.08);
            border-radius: var(--radius-md);
            color: var(--primary);
            flex-shrink: 0;
        }

        .doc-file-icon.icon-pdf {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .doc-file-icon.icon-word {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
        }

        .doc-file-icon.icon-excel {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
        }

        .doc-file-icon.icon-text {
            background: rgba(107, 114, 128, 0.1);
            color: #4b5563;
        }

        .doc-file-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            flex: 1;
            min-width: 0;
        }

        .doc-file-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-700);
            word-break: break-all;
        }

        .doc-file-type {
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-400);
        }

        .doc-file-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background: var(--white);
            color: var(--primary);
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .doc-file-btn:hover {
            background: rgba(20, 93, 160, 0.08);
            border-color: var(--primary);
        }

        .doc-action-group {
            padding-top: 12px;
            border-top: 1px solid var(--gray-200);
            margin-bottom: 14px;
        }

        .doc-checkbox {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
        }

        .doc-checkbox input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .doc-checkbox-box {
            width: 18px;
            height: 18px;
            border: 2px solid var(--gray-300);
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .doc-checkbox-box svg {
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.2s ease;
            color: var(--white);
        }

        .doc-checkbox input:checked + .doc-checkbox-box {
            background: var(--error);
            border-color: var(--error);
        }

        .doc-checkbox input:checked + .doc-checkbox-box svg {
            opacity: 1;
            transform: scale(1);
        }

        .doc-checkbox-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-600);
        }

        .doc-checkbox input:checked ~ .doc-checkbox-label {
            color: var(--error);
        }

        .doc-upload {
            padding-top: 14px;
            border-top: 1px dashed var(--gray-300);
        }

        .doc-upload .form-label {
            margin-bottom: 6px;
            display: block;
        }

        .file-input-wrapper {
            position: relative;
        }

        .file-input {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
        }

        .file-input-trigger {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px;
            background: var(--gray-50);
            border: 2px dashed var(--gray-300);
            border-radius: var(--radius-md);
            color: var(--gray-500);
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .file-input-wrapper:hover .file-input-trigger,
        .file-input-wrapper:focus-within .file-input-trigger {
            border-color: var(--primary);
            background: rgba(20, 93, 160, 0.04);
            color: var(--primary);
        }

        .file-input-hint {
            display: block;
            font-size: 11px;
            color: var(--gray-400);
            margin-top: 6px;
        }

        .file-input-text.has-file {
            color: var(--primary);
        }

        /* SECTION HEADER INLINE (x-slot header) */
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header-inline .section-title {
            font-size: 1rem;
            font-weight: 800;
            color: var(--gray-900);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }

        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--gray-500);
            font-weight: 500;
            line-height: 1.35;
        }

        .icon-navy {
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark);
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

            // Document file input handler + live preview
            var fileInput = document.getElementById('document');
            var fileLabelText = document.getElementById('file-label-text');
            var newPreviewWrap = document.getElementById('doc-new-preview');
            var newPreviewContent = document.getElementById('doc-new-preview-content');

            function getFileTypeConfig(ext) {
                ext = ext.toLowerCase();
                var configs = {
                    pdf: { label: 'Dokumen PDF', color: 'pdf', icon: 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z M12 3v6a1 1 0 001 1h6' },
                    doc: { label: 'Dokumen Word', color: 'word', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
                    docx: { label: 'Dokumen Word', color: 'word', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
                    xls: { label: 'Dokumen Excel', color: 'excel', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
                    xlsx: { label: 'Dokumen Excel', color: 'excel', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
                    txt: { label: 'File Teks', color: 'text', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' }
                };
                return configs[ext] || { label: 'Dokumen', color: 'default', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' };
            }

            function clearNewPreview() {
                if (newPreviewWrap) newPreviewWrap.style.display = 'none';
                if (newPreviewContent) newPreviewContent.innerHTML = '';
            }

            function showNewPreview(file) {
                if (!newPreviewWrap || !newPreviewContent) return;
                var imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
                var ext = file.name.split('.').pop() || '';
                var isImage = imageExts.indexOf(ext.toLowerCase()) !== -1;

                if (isImage) {
                    var url = URL.createObjectURL(file);
                    newPreviewContent.innerHTML =
                        '<div class="doc-image-wrap">' +
                            '<div class="doc-image-link">' +
                                '<img src="' + url + '" alt="Preview" style="max-height: 280px; width: 100%; object-fit: contain; display: block;">' +
                            '</div>' +
                        '</div>';
                } else {
                    var cfg = getFileTypeConfig(ext);
                    newPreviewContent.innerHTML =
                        '<div class="doc-file">' +
                            '<div class="doc-file-icon icon-' + cfg.color + '">' +
                                '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="' + cfg.icon + '"/></svg>' +
                            '</div>' +
                            '<div class="doc-file-info">' +
                                '<span class="doc-file-name">' + file.name + '</span>' +
                                '<span class="doc-file-type">' + cfg.label + ' &middot; ' + ext.toUpperCase() + '</span>' +
                            '</div>' +
                        '</div>';
                }
                newPreviewWrap.style.display = 'block';
            }

            if (fileInput && fileLabelText) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        var file = this.files[0];
                        fileLabelText.textContent = file.name;
                        fileLabelText.classList.add('has-file');
                        showNewPreview(file);
                    } else {
                        fileLabelText.textContent = 'Klik untuk pilih file';
                        fileLabelText.classList.remove('has-file');
                        clearNewPreview();
                    }
                });
            }

            // Delete document checkbox handler
            var deleteCheckbox = document.getElementById('delete_document');
            var uploadArea = document.getElementById('doc-upload-area');
            if (deleteCheckbox && uploadArea) {
                deleteCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        uploadArea.style.opacity = '0.5';
                        uploadArea.style.pointerEvents = 'none';
                        if (fileInput) {
                            fileInput.value = '';
                            clearNewPreview();
                        }
                        if (fileLabelText) {
                            fileLabelText.textContent = 'Klik untuk pilih file';
                            fileLabelText.classList.remove('has-file');
                        }
                    } else {
                        uploadArea.style.opacity = '1';
                        uploadArea.style.pointerEvents = 'auto';
                    }
                });
            }
        });
    </script>
    @endpush
</x-app>
