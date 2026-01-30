<x-app title="Pengajuan Hutang Karyawan">

    @if ($errors->any())
        <div class="alert-error">
            <ul style="margin:0; padding-left:16px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="form-title">Formulir Pengajuan</h3>
                <p class="form-subtitle">Isi data di bawah untuk mengajukan pinjaman/kasbon.</p>
            </div>
            <a href="{{ route('employee.loan_requests.index') }}" class="btn-back">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                Kembali
            </a>
        </div>

        <div class="divider"></div>

        <form action="{{ route('employee.loan_requests.store') }}" method="POST" enctype="multipart/form-data" class="form-content">
            @csrf

            <div class="info-box">
                <h4 class="info-title">Data Pemohon</h4>
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

            <div class="form-section">
                <h4 class="section-label">Rincian Pinjaman</h4>

                <div class="form-group">
                    <label for="amount_display">Besar Pinjaman <span class="req">*</span></label>
                    <input
                        id="amount_display"
                        type="text"
                        inputmode="numeric"
                        autocomplete="off"
                        class="form-control text-large"
                        placeholder="Rp 0"
                        value="{{ old('amount') ? 'Rp' . number_format(old('amount'), 0, ',', '.') : '' }}"
                        required>
                    <input id="amount" type="hidden" name="amount" value="{{ old('amount') }}">
                </div>

                <div class="form-group">
                    <label for="purpose">Keperluan / Alasan <span class="req">*</span></label>
                    <textarea
                        id="purpose"
                        name="purpose"
                        rows="3"
                        class="form-control"
                        placeholder="Contoh: Biaya pengobatan keluarga, Renovasi rumah, dll."
                        required>{{ old('purpose') }}</textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="disbursement_date">Tanggal Dibutuhkan</label>
                        <input
                            id="disbursement_date"
                            type="date"
                            name="disbursement_date"
                            class="form-control"
                            value="{{ old('disbursement_date') }}">
                    </div>

                    {{-- [MODIFIED] Bagian Jangka Waktu (Tenor) --}}
                    <div class="form-group">
                        <label for="tenor_select">Jangka Waktu (Tenor) <span class="req">*</span></label>
                        
                        {{-- Logic PHP untuk menentukan selected old value --}}
                        @php
                            $standardOptions = [2, 3, 6, 9, 12, 18, 24];
                            $oldValue = old('installment_months');
                            $isManual = $oldValue && !in_array($oldValue, $standardOptions);
                        @endphp

                        {{-- 1. Dropdown Pilihan --}}
                        <select id="tenor_select" class="form-control">
                            <option value="">Pilih Bulan...</option>
                            @foreach($standardOptions as $opt)
                                <option value="{{ $opt }}" @selected(!$isManual && $oldValue == $opt)>
                                    {{ $opt }} Bulan
                                </option>
                            @endforeach
                            <option value="manual" @selected($isManual)>Lainnya (Input Manual)</option>
                        </select>

                        {{-- 2. Input Manual (Hidden by default) --}}
                        <div id="manual_tenor_wrapper" style="margin-top: 8px; display: {{ $isManual ? 'block' : 'none' }};">
                            <input 
                                type="number" 
                                id="tenor_manual_input" 
                                class="form-control" 
                                placeholder="Masukkan jumlah bulan (cth: 5)"
                                min="1"
                                value="{{ $isManual ? $oldValue : '' }}"
                            >
                            <small class="helper-text" style="color: #ea580c;">Masukkan angka bulan secara manual.</small>
                        </div>

                        {{-- 3. Hidden Input (Ini yang dikirim ke Server) --}}
                        <input type="hidden" name="installment_months" id="installment_months" value="{{ $oldValue }}">
                    </div>
                </div>

                <div class="form-group">
                    <label>Estimasi Cicilan Per Bulan</label>
                    <input
                        id="installment_preview"
                        type="text"
                        class="form-control bg-readonly"
                        readonly
                        placeholder="-">
                    <small class="helper-text">Angka ini adalah estimasi otomatis (Pokok / Tenor).</small>
                </div>

                <div class="form-group">
                    <label for="payment_method">Metode Pengembalian <span class="req">*</span></label>
                    <select id="payment_method" name="payment_method" class="form-control" required>
                        <option value="POTONG_GAJI" @selected(old('payment_method', 'POTONG_GAJI') === 'POTONG_GAJI')>Potong Gaji</option>
                        <option value="TUNAI" @selected(old('payment_method') === 'TUNAI')>Tunai / Cash</option>
                        <option value="CICILAN" @selected(old('payment_method') === 'CICILAN')>Cicilan</option>
                    </select>
                    <small class="helper-text">Disarankan menggunakan <b>Potong Gaji</b> untuk kemudahan administrasi.</small>
                </div>

                <div class="form-group">
                    <label for="document">Dokumen Pendukung (Opsional)</label>
                    <div class="file-input-wrapper">
                        <input
                            id="document"
                            type="file"
                            name="document"
                            class="form-control-file"
                            accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                    <small class="helper-text">Format: JPG, PNG, PDF. Maksimal 2MB.</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    Kirim Pengajuan
                </button>
            </div>
        </form>
    </div>

    <style>
        /* Utils */
        .mb-4 { margin-bottom: 16px; }
        .req { color: #dc2626; font-weight: bold; margin-left: 2px; }
        
        /* Alert */
        .alert-error {
            background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
            padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px;
        }

        /* Card & Header */
        .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid #f3f4f6; overflow: hidden; }
        .card-header { padding: 20px; display: flex; justify-content: space-between; align-items: flex-start; }
        .form-title { margin: 0; font-size: 18px; font-weight: 700; color: #111827; }
        .form-subtitle { margin: 4px 0 0; font-size: 13.5px; color: #6b7280; }
        
        .btn-back {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 14px; border-radius: 8px; border: 1px solid #d1d5db;
            background: #fff; color: #374151; font-size: 13px; font-weight: 500;
            text-decoration: none; transition: all 0.2s;
        }
        .btn-back:hover { background: #f9fafb; border-color: #9ca3af; }

        .divider { height: 1px; background: #f3f4f6; width: 100%; }

        /* Form Content */
        .form-content { padding: 24px; max-width: 700px; margin: 0 auto; }

        /* Info Box (Readonly) */
        .info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 24px; }
        .info-title { margin: 0 0 12px 0; font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 24px; }
        .info-item { display: flex; flex-direction: column; gap: 2px; }
        .info-item label { font-size: 11px; color: #94a3b8; font-weight: 500; text-transform: uppercase; }
        .info-item span { font-size: 14px; color: #334155; font-weight: 600; }
        .full-width { grid-column: span 2; }

        /* Form Inputs */
        .section-label { margin: 0 0 16px 0; font-size: 15px; font-weight: 700; color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; display: inline-block; }
        
        .form-group { margin-bottom: 18px; display: flex; flex-direction: column; gap: 6px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        
        .form-group label { font-size: 13.5px; font-weight: 600; color: #374151; }
        .form-control {
            padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;
            font-size: 14px; width: 100%; outline: none; transition: border-color 0.2s;
            background: #fff; color: #111827;
        }
        .form-control:focus { border-color: #1e4a8d; box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1); }
        .form-control.text-large { font-size: 16px; font-weight: 600; color: #1e4a8d; }
        .bg-readonly { background: #f9fafb; color: #6b7280; cursor: not-allowed; }
        
        .helper-text { font-size: 12px; color: #6b7280; margin-top: 2px; }

        .file-input-wrapper { border: 1px dashed #cbd5e1; padding: 8px; border-radius: 8px; background: #f8fafc; }
        .form-control-file { width: 100%; font-size: 13px; }

        /* Actions */
        .form-actions { display: flex; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid #f3f4f6; }
        .btn-primary {
            padding: 10px 24px; background: #1e4a8d; color: #fff; border: none; border-radius: 8px;
            font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s;
        }
        .btn-primary:hover { background: #163a75; }

        @media(max-width: 600px) {
            .form-row { grid-template-columns: 1fr; gap: 0; }
            .info-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
            .form-content { padding: 16px; }
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

                // Remove non-digit characters
                var raw = displayInput.value || '';
                var digits = raw.replace(/\D/g, '');
                
                if (digits.length === 0) {
                    hiddenInput.value = '';
                    displayInput.value = '';
                    updateInstallmentPreview();
                    return;
                }

                var numeric = parseInt(digits);
                hiddenInput.value = numeric;
                
                // Format display input
                displayInput.value = 'Rp ' + formatRupiahNumber(numeric);
                updateInstallmentPreview();
            }

            function formatRupiah(value) {
                if (!value || isNaN(value)) return '';
                return 'Rp ' + Number(value).toLocaleString('id-ID');
            }

            // [MODIFIED] Fungsi Update Preview Cicilan (Support Select & Manual)
            function updateInstallmentPreview() {
                var hiddenAmount = document.getElementById('amount');
                var previewInput = document.getElementById('installment_preview');
                
                // Elemen Tenor
                var tenorSelect = document.getElementById('tenor_select');
                var tenorManualWrapper = document.getElementById('manual_tenor_wrapper');
                var tenorManualInput = document.getElementById('tenor_manual_input');
                var tenorHidden = document.getElementById('installment_months');

                if (!hiddenAmount || !previewInput || !tenorSelect) return;

                var amount = parseFloat(hiddenAmount.value || '0');
                var selectedVal = tenorSelect.value;
                var months = 0;

                // LOGIKA: Select atau Manual
                if (selectedVal === 'manual') {
                    // Tampilkan Input Manual
                    tenorManualWrapper.style.display = 'block';
                    months = parseInt(tenorManualInput.value || '0');
                    // Update Hidden Input (untuk dikirim ke server)
                    tenorHidden.value = months > 0 ? months : '';
                } else {
                    // Sembunyikan Input Manual
                    tenorManualWrapper.style.display = 'none';
                    months = parseInt(selectedVal || '0');
                    // Update Hidden Input
                    tenorHidden.value = months > 0 ? months : '';
                }

                // Kalkulasi Cicilan
                if (amount > 0 && months > 0) {
                    var perMonth = Math.floor(amount / months);
                    previewInput.value = formatRupiah(perMonth) + ' / bulan';
                } else {
                    previewInput.value = '-';
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                var displayInput = document.getElementById('amount_display');
                var hiddenAmount = document.getElementById('amount');
                
                // Event Listener Tenor
                var tenorSelect = document.getElementById('tenor_select');
                var tenorManualInput = document.getElementById('tenor_manual_input');

                if (displayInput) {
                    displayInput.addEventListener('input', updateAmountFormatting);
                    displayInput.addEventListener('blur', updateAmountFormatting);
                }

                if (tenorSelect) {
                    tenorSelect.addEventListener('change', updateInstallmentPreview);
                }

                if (tenorManualInput) {
                    tenorManualInput.addEventListener('input', updateInstallmentPreview);
                }

                // Initial load
                if (hiddenAmount && hiddenAmount.value) {
                    displayInput.value = 'Rp ' + formatRupiahNumber(hiddenAmount.value);
                }

                updateInstallmentPreview();
            });
        </script>
    @endpush
</x-app>