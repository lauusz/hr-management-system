<x-app title="Pengajuan Hutang Karyawan">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Pengajuan Hutang</h1>
                <p class="section-subtitle">Isi data pinjaman dengan mudah.</p>
            </div>
        </div>
    </x-slot>

    <main class="loan-create-page">
        <a href="{{ route('employee.loan_requests.index') }}" class="loan-back-btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>

        @if(session('success'))
            <div class="loan-alert loan-alert--success" role="status">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="loan-alert loan-alert--error" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('employee.loan_requests.store') }}" enctype="multipart/form-data" id="form-loan">
            @csrf

            <details class="applicant-summary">
                <summary>
                    <span class="applicant-summary__identity">
                        <strong>{{ $snapshot['name'] ?? $user->name }}</strong>
                        <span>NIK {{ $snapshot['nik'] ?? '-' }}</span>
                    </span>
                    <span class="applicant-summary__action">Lihat data</span>
                </summary>
                <dl class="applicant-summary__details">
                    <div>
                        <dt>Jabatan</dt>
                        <dd>{{ $snapshot['position'] ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt>Divisi / Departemen</dt>
                        <dd>{{ $snapshot['division'] ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt>Perusahaan</dt>
                        <dd>{{ $snapshot['pt'] ?? '-' }}</dd>
                    </div>
                </dl>
            </details>

            <section class="loan-form-card" aria-labelledby="loan-detail-title">
                <div class="loan-section-heading">
                    <span class="loan-section-number" aria-hidden="true">1</span>
                    <h2 id="loan-detail-title">Detail pinjaman</h2>
                </div>

                <div class="loan-field">
                    <label for="amount_display">Berapa dana yang dibutuhkan? <span class="loan-required" aria-hidden="true">*</span></label>
                    <div class="loan-money-input">
                        <span>Rp</span>
                        <input
                            id="amount_display"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="Masukkan jumlah dana"
                            value="{{ old('amount') ? number_format(old('amount'), 0, ',', '.') : '' }}"
                            required>
                    </div>
                    <input id="amount" type="hidden" name="amount" value="{{ old('amount') }}">
                </div>

                <div class="loan-field">
                    <label for="purpose">Untuk keperluan apa? <span class="loan-required" aria-hidden="true">*</span></label>
                    <textarea
                        id="purpose"
                        name="purpose"
                        rows="3"
                        placeholder="Tuliskan keperluan pengajuan Anda"
                        required>{{ old('purpose') }}</textarea>
                </div>

                <div class="loan-field-grid">
                    <div class="loan-field">
                        <label for="disbursement_date">Tanggal dana dibutuhkan</label>
                        <input
                            id="disbursement_date"
                            type="date"
                            name="disbursement_date"
                            value="{{ old('disbursement_date') }}">
                    </div>

                    <div class="loan-field">
                        <label for="monthly_installment_input">Cicilan per bulan <span class="loan-required" aria-hidden="true">*</span></label>
                        <div class="loan-money-input">
                            <span>Rp</span>
                            <input
                                id="monthly_installment_input"
                                type="text"
                                inputmode="numeric"
                                autocomplete="off"
                                placeholder="Masukkan jumlah cicilan"
                                value="{{ old('monthly_installment') ? number_format(old('monthly_installment'), 0, ',', '.') : '' }}"
                                required>
                        </div>
                        <input type="hidden" id="monthly_installment" name="monthly_installment" value="{{ old('monthly_installment') }}">
                    </div>
                </div>

                <div class="tenor-summary" aria-live="polite">
                    <span>Perkiraan selesai</span>
                    <strong id="tenor_preview">Isi jumlah pinjaman dan cicilan</strong>
                </div>

                <div class="loan-section-divider"></div>

                <div class="loan-section-heading">
                    <span class="loan-section-number" aria-hidden="true">2</span>
                    <h2>Cara membayar</h2>
                </div>

                <div class="loan-field">
                    <label for="payment_method">Cara pembayaran <span class="loan-required" aria-hidden="true">*</span></label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="POTONG_GAJI" @selected(old('payment_method', 'POTONG_GAJI') === 'POTONG_GAJI')>Potong Gaji</option>
                        <option value="TUNAI" @selected(old('payment_method') === 'TUNAI')>Tunai / Cash</option>
                        <option value="CICILAN" @selected(old('payment_method') === 'CICILAN')>Cicilan</option>
                    </select>
                    <small>Pilih cara pembayaran yang sudah disepakati dengan perusahaan.</small>
                </div>

                <div class="loan-field">
                    <label for="document">Dokumen pendukung <span class="loan-optional">(opsional)</span></label>
                    <div class="loan-file" id="file-upload-box">
                        <input
                            id="document"
                            type="file"
                            name="document"
                            accept="image/*,.heic,.heif,.pdf,.doc,.docx,.xls,.xlsx,.txt"
                            data-max-file-size="8388608"
                            data-max-file-label="8 MB"
                            onchange="handleFileSelect(this)">
                        <label for="document" id="file-upload-content">
                            <strong>Pilih dokumen</strong>
                            <span>JPG, PNG, PDF, DOC, XLS, atau TXT · Maks. 8 MB</span>
                        </label>
                        <div class="loan-file__selected" id="file-upload-selected" hidden>
                            <span class="loan-file__type" id="file-preview">FILE</span>
                            <span class="loan-file__info">
                                <strong id="file-name"></strong>
                                <span id="file-size"></span>
                            </span>
                            <button type="button" onclick="removeFile(event)" aria-label="Hapus dokumen">Hapus</button>
                        </div>
                    </div>
                </div>

                <div class="loan-field">
                    <label for="notes">Catatan tambahan <span class="loan-optional">(opsional)</span></label>
                    <textarea
                        id="notes"
                        name="notes"
                        rows="2"
                        placeholder="Tambahkan catatan jika diperlukan">{{ old('notes') }}</textarea>
                </div>

                <p class="loan-reminder">Pastikan data yang Anda isi sudah benar sebelum melanjutkan.</p>
            </section>

            <div class="loan-submit-dock">
                <button class="loan-submit" type="button" id="btn-submit" onclick="showConfirmModal()">
                    Ajukan
                </button>
            </div>
        </form>

        <div id="confirmModal" class="loan-modal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
            <div class="loan-modal__content">
                <p class="loan-modal__eyebrow">Periksa kembali</p>
                <h2 id="confirm-title">Konfirmasi Pengajuan</h2>

                <div class="loan-modal__summary">
                    <div><span>Besar pinjaman</span><strong id="summaryAmount">Rp 0</strong></div>
                    <div><span>Cicilan per bulan</span><strong id="summaryInstallment">Rp 0</strong></div>
                    <div><span>Jangka waktu</span><strong id="summaryTenor">0 bulan</strong></div>
                    <div><span>Cara pembayaran</span><strong id="summaryMethod">Potong Gaji</strong></div>
                </div>

                <p class="loan-modal__warning">Pinjaman wajib dilunasi saat Anda keluar dari perusahaan.</p>

                <div class="loan-modal__actions">
                    <button type="button" class="loan-modal__cancel" onclick="hideConfirmModal()">Kembali</button>
                    <button type="button" class="loan-modal__confirm" onclick="submitForm()">Kirim Pengajuan</button>
                </div>
            </div>
        </div>
    </main>

    <style>
        .section-header-inline { display:flex; align-items:center; gap:10px; }
        .section-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .section-icon svg { width:16px; height:16px; }
        .section-title { margin:0; font-size:1rem; font-weight:800; color:var(--text-primary); line-height:1.25; }
        .section-subtitle { margin:0; font-size:.8125rem; color:var(--text-muted); font-weight:500; line-height:1.35; }
        .icon-navy { background:rgba(10,61,98,.08); color:var(--primary-dark); }

        .loan-create-page { width:100%; max-width:960px; margin:0 auto; padding-bottom:8px; }
        .loan-back-btn { display:inline-flex; align-items:center; justify-content:center; gap:6px; min-height:40px; margin-bottom:16px; padding:0 14px 0 12px; border:1px solid var(--border); border-radius:10px; background:var(--white); color:var(--text-muted); text-decoration:none; font-size:.8125rem; font-weight:700; box-shadow:0 1px 2px rgba(0,0,0,.04); transition:all .15s ease; }
        .loan-back-btn:hover { border-color:var(--primary); background:var(--gray-50); color:var(--primary); }
        .loan-back-btn svg { flex-shrink:0; }
        .loan-back-btn:focus-visible { outline:3px solid rgba(20,93,160,.25); outline-offset:2px; }
        .loan-create-page form { display:flex; flex-direction:column; gap:18px; }
        .loan-alert { margin-bottom:14px; padding:12px 14px; border:1px solid; border-radius:12px; font-size:.8125rem; line-height:1.45; }
        .loan-alert--success { border-color:#bbf7d0; background:#f0fdf4; color:#166534; }
        .loan-alert--error { border-color:#fecaca; background:#fef2f2; color:#991b1b; }

        .applicant-summary { border:1px solid var(--border); border-radius:16px; background:var(--white); overflow:hidden; }
        .applicant-summary summary { min-height:84px; display:flex; align-items:center; gap:16px; padding:18px 20px; cursor:pointer; list-style:none; }
        .applicant-summary summary::-webkit-details-marker { display:none; }
        .applicant-summary__identity { min-width:0; display:flex; flex:1; flex-direction:column; gap:4px; }
        .applicant-summary__identity strong { overflow:hidden; color:var(--text-primary); font-size:.9375rem; line-height:1.35; text-overflow:ellipsis; white-space:nowrap; }
        .applicant-summary__identity span { color:var(--text-muted); font-size:.8125rem; }
        .applicant-summary__action { flex:0 0 auto; color:var(--primary-dark); font-size:.8125rem; font-weight:700; }
        .applicant-summary[open] .applicant-summary__action { font-size:0; }
        .applicant-summary[open] .applicant-summary__action::after { content:'Tutup'; font-size:.8125rem; }
        .applicant-summary__details { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; margin:0; padding:0 20px 18px; border-top:1px solid var(--border-light); }
        .applicant-summary__details div { padding-top:16px; }
        .applicant-summary__details dt { margin-bottom:4px; color:var(--text-light); font-size:.6875rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; }
        .applicant-summary__details dd { margin:0; color:var(--text-secondary); font-size:.8125rem; font-weight:600; line-height:1.4; }

        .loan-form-card { padding:26px; border:1px solid var(--border); border-radius:18px; background:var(--white); }
        .loan-section-heading { display:flex; align-items:center; gap:12px; margin-bottom:24px; }
        .loan-section-number { width:34px; height:34px; display:grid; place-items:center; flex:0 0 auto; border-radius:50%; background:var(--primary-dark); color:var(--white); font-size:.875rem; font-weight:700; }
        .loan-section-heading h2 { margin:0; color:var(--text-primary); font-size:1.0625rem; font-weight:700; letter-spacing:-.01em; }
        .loan-section-divider { height:1px; margin:28px 0; background:var(--border); }

        .loan-field { margin-bottom:22px; }
        .loan-field:last-of-type { margin-bottom:0; }
        .loan-field label { display:block; margin-bottom:9px; color:var(--text-secondary); font-size:.875rem; font-weight:600; line-height:1.4; }
        .loan-required { color:var(--error); }
        .loan-optional { color:var(--text-muted); font-weight:500; }
        .loan-field input:not([type="file"]), .loan-field select, .loan-field textarea { width:100%; border:1px solid #d8dee7; border-radius:12px; background:var(--white); color:var(--text-primary); font:inherit; font-size:.875rem; transition:border-color .15s ease, box-shadow .15s ease; }
        .loan-field input:not([type="file"]), .loan-field select { min-height:50px; padding:0 14px; }
        .loan-field textarea { min-height:96px; padding:13px 14px; resize:vertical; line-height:1.5; }
        .loan-field textarea[rows="2"] { min-height:78px; }
        .loan-field input:focus, .loan-field select:focus, .loan-field textarea:focus { outline:0; border-color:var(--primary); box-shadow:0 0 0 3px rgba(20,93,160,.12); }
        .loan-field input::placeholder, .loan-field textarea::placeholder { color:var(--text-light); }
        .loan-field small { display:block; margin-top:7px; color:var(--text-muted); font-size:.75rem; line-height:1.45; }
        .loan-field-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }

        .loan-money-input { position:relative; }
        .loan-money-input > span { position:absolute; z-index:1; left:14px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:.875rem; font-weight:600; }
        .loan-money-input input { padding-left:46px !important; }

        .tenor-summary { display:flex; align-items:center; justify-content:space-between; gap:16px; min-height:54px; margin-top:2px; padding:14px 16px; border-radius:12px; background:#f0f6fb; color:var(--primary-dark); }
        .tenor-summary span { font-size:.8125rem; font-weight:500; }
        .tenor-summary strong { text-align:right; font-size:.875rem; font-weight:700; }

        .loan-file { position:relative; min-height:78px; border:1px dashed #cbd5e1; border-radius:12px; background:#fafbfc; }
        .loan-file > input { position:absolute; width:1px; height:1px; opacity:0; pointer-events:none; }
        .loan-file > label { min-height:78px; display:flex; flex-direction:column; justify-content:center; gap:4px; margin:0; padding:14px 16px; cursor:pointer; }
        .loan-file > label strong { color:var(--primary-dark); font-size:.875rem; }
        .loan-file > label span { color:var(--text-muted); font-size:.75rem; font-weight:500; }
        .loan-file:focus-within { border-color:var(--primary); box-shadow:0 0 0 3px rgba(20,93,160,.12); }
        .loan-file__selected { min-height:78px; align-items:center; gap:12px; padding:12px 14px; }
        .loan-file__selected:not([hidden]) { display:flex; }
        .loan-file__type { min-width:42px; padding:8px 6px; border-radius:8px; background:#e9f2f9; color:var(--primary-dark); text-align:center; font-size:.625rem; font-weight:800; }
        .loan-file__info { min-width:0; display:flex; flex:1; flex-direction:column; gap:3px; }
        .loan-file__info strong { overflow:hidden; color:var(--text-secondary); font-size:.8125rem; text-overflow:ellipsis; white-space:nowrap; }
        .loan-file__info span { color:var(--text-muted); font-size:.75rem; }
        .loan-file__selected button { min-height:40px; padding:0 10px; border:0; border-radius:8px; background:#fef2f2; color:#b91c1c; font:inherit; font-size:.75rem; font-weight:700; cursor:pointer; }

        .loan-reminder { margin:24px 0 0; padding:12px 14px; border-radius:12px; background:#fff8e8; color:#9a6700; font-size:.75rem; line-height:1.45; }
        .loan-submit-dock { padding:14px 0 0; }
        .loan-submit { width:100%; min-height:56px; border:0; border-radius:14px; background:var(--primary-dark); color:var(--white); font:inherit; font-size:.9375rem; font-weight:700; cursor:pointer; box-shadow:0 6px 16px rgba(10,61,98,.18); }
        .loan-submit:hover { background:#08334f; }
        .loan-submit:focus-visible { outline:3px solid rgba(20,93,160,.28); outline-offset:3px; }

        .loan-modal { position:fixed; z-index:2100; inset:0; align-items:center; justify-content:center; padding:18px; background:rgba(15,23,42,.5); }
        .loan-modal__content { width:100%; max-width:420px; padding:24px; border-radius:18px; background:var(--white); box-shadow:0 20px 45px rgba(15,23,42,.18); }
        .loan-modal__eyebrow { margin:0 0 5px; color:var(--primary); font-size:.75rem; font-weight:700; }
        .loan-modal h2 { margin:0 0 18px; color:var(--text-primary); font-size:1.125rem; }
        .loan-modal__summary { padding:6px 14px; border:1px solid var(--border); border-radius:12px; background:var(--gray-50); }
        .loan-modal__summary div { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:10px 0; border-bottom:1px solid var(--border); }
        .loan-modal__summary div:last-child { border-bottom:0; }
        .loan-modal__summary span { color:var(--text-muted); font-size:.75rem; }
        .loan-modal__summary strong { color:var(--text-primary); text-align:right; font-size:.8125rem; }
        .loan-modal__warning { margin:14px 0 18px; padding:11px 12px; border-radius:10px; background:#fff8e8; color:#8a5b00; font-size:.75rem; line-height:1.45; }
        .loan-modal__actions { display:grid; grid-template-columns:1fr 1.35fr; gap:10px; }
        .loan-modal__actions button { min-height:46px; border-radius:11px; font:inherit; font-size:.8125rem; font-weight:700; cursor:pointer; }
        .loan-modal__cancel { border:1px solid var(--border); background:var(--white); color:var(--text-secondary); }
        .loan-modal__confirm { border:0; background:var(--primary-dark); color:var(--white); }

        @media (max-width:767px) {
            .content-wrapper { padding:16px 16px max(16px,env(safe-area-inset-bottom)); }
            .topbar { margin-bottom:18px; }
            .burger { display:none; }
            .loan-create-page form { gap:14px; }
            .applicant-summary summary { min-height:72px; padding:14px 16px; }
            .applicant-summary__details { grid-template-columns:1fr; gap:0; padding:0 16px 14px; }
            .applicant-summary__details div { padding-top:12px; }
            .loan-form-card { padding:20px 16px; border-radius:16px; }
            .loan-section-heading { margin-bottom:22px; }
            .loan-field-grid { grid-template-columns:1fr; gap:0; }
            .loan-section-divider { margin:26px 0; }
            .loan-submit-dock { margin:0 -4px; padding:16px 4px 0; }
        }

        @media (max-width:359px) {
            .content-wrapper { padding:12px 12px max(12px,env(safe-area-inset-bottom)); }
            .topbar { margin-bottom:14px; }
            .section-title { font-size:.9375rem; }
            .section-subtitle { font-size:.75rem; }
            .applicant-summary__identity strong { font-size:.8125rem; }
            .loan-form-card { padding:18px 14px; }
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

            var digits = (displayInput.value || '').replace(/\D/g, '');
            hiddenInput.value = digits.length ? parseInt(digits) : '';
            displayInput.value = digits.length ? formatRupiahNumber(hiddenInput.value) : '';
            updateTenorPreview();
        }

        function updateMonthlyInstallmentFormatting() {
            var displayInput = document.getElementById('monthly_installment_input');
            var hiddenInput = document.getElementById('monthly_installment');
            if (!displayInput || !hiddenInput) return;

            var digits = (displayInput.value || '').replace(/\D/g, '');
            hiddenInput.value = digits.length ? parseInt(digits) : '';
            displayInput.value = digits.length ? formatRupiahNumber(hiddenInput.value) : '';
            updateTenorPreview();
        }

        function updateTenorPreview() {
            var amount = parseFloat(document.getElementById('amount')?.value || '0');
            var installment = parseFloat(document.getElementById('monthly_installment')?.value || '0');
            var preview = document.getElementById('tenor_preview');
            if (!preview) return;
            preview.textContent = amount > 0 && installment > 0
                ? Math.ceil(amount / installment) + ' bulan'
                : 'Isi jumlah pinjaman dan cicilan';
        }

        document.addEventListener('DOMContentLoaded', function () {
            var amountDisplay = document.getElementById('amount_display');
            var installmentDisplay = document.getElementById('monthly_installment_input');

            amountDisplay?.addEventListener('input', updateAmountFormatting);
            amountDisplay?.addEventListener('blur', updateAmountFormatting);
            installmentDisplay?.addEventListener('input', updateMonthlyInstallmentFormatting);
            installmentDisplay?.addEventListener('blur', updateMonthlyInstallmentFormatting);

            if (document.getElementById('amount')?.value) amountDisplay.value = formatRupiahNumber(document.getElementById('amount').value);
            if (document.getElementById('monthly_installment')?.value) installmentDisplay.value = formatRupiahNumber(document.getElementById('monthly_installment').value);
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
            var tenor = Math.ceil(amount / installment);
            var method = document.getElementById('payment_method')?.value || 'POTONG_GAJI';
            var labels = { POTONG_GAJI: 'Potong Gaji', TUNAI: 'Tunai / Cash', CICILAN: 'Cicilan' };

            document.getElementById('summaryAmount').textContent = 'Rp ' + formatRupiahNumber(amount);
            document.getElementById('summaryInstallment').textContent = 'Rp ' + formatRupiahNumber(installment);
            document.getElementById('summaryTenor').textContent = tenor + ' bulan';
            document.getElementById('summaryMethod').textContent = labels[method] || method;

            document.getElementById('confirmModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            document.querySelector('.loan-modal__cancel')?.focus();
        }

        function hideConfirmModal() {
            document.getElementById('confirmModal').style.display = 'none';
            document.body.style.overflow = '';
            document.getElementById('btn-submit')?.focus();
        }

        function submitForm() {
            document.getElementById('form-loan').submit();
        }

        function handleFileSelect(input) {
            var file = input.files[0];
            if (!file) return;

            document.getElementById('file-upload-content').hidden = true;
            document.getElementById('file-upload-selected').hidden = false;
            document.getElementById('file-name').textContent = file.name;
            document.getElementById('file-size').textContent = formatFileSize(file.size);
            document.getElementById('file-preview').textContent = (file.name.split('.').pop() || 'FILE').slice(0, 4).toUpperCase();
        }

        function removeFile(event) {
            event.stopPropagation();
            document.getElementById('document').value = '';
            document.getElementById('file-upload-content').hidden = false;
            document.getElementById('file-upload-selected').hidden = true;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            var units = ['Bytes', 'KB', 'MB', 'GB'];
            var index = Math.floor(Math.log(bytes) / Math.log(1024));
            return parseFloat((bytes / Math.pow(1024, index)).toFixed(2)) + ' ' + units[index];
        }

        document.getElementById('confirmModal')?.addEventListener('click', function (event) {
            if (event.target === this) hideConfirmModal();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && document.getElementById('confirmModal')?.style.display === 'flex') hideConfirmModal();
        });
    </script>
    @endpush
</x-app>
