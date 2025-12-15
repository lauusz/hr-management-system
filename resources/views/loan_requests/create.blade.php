<x-app title="Pengajuan Hutang Karyawan">

    @if ($errors->any())
        <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;border-radius:8px;">
            {{ $errors->first() }}
        </div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <p style="font-size:.9rem;opacity:.75;margin:0;">
            Form pengajuan permohonan hutang/kasbon.
        </p>

        <a href="{{ route('employee.loan_requests.index') }}"
           style="font-size:.85rem;padding:6px 10px;border-radius:999px;border:1px solid #d1d5db;text-decoration:none;color:#111827;">
            ← Kembali
        </a>
    </div>

    <form class="card"
          action="{{ route('employee.loan_requests.store') }}"
          method="POST"
          enctype="multipart/form-data"
          style="max-width:640px;margin:0 auto;padding:16px;display:flex;flex-direction:column;gap:14px;">
        @csrf

        <div style="font-size:.9rem;font-weight:600;margin-bottom:4px;">
            Data Karyawan
        </div>

        <div style="display:grid;grid-template-columns:1fr;gap:8px;">
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="font-size:.85rem;font-weight:500;">Nama</label>
                <input
                    type="text"
                    value="{{ $snapshot['name'] ?? $user->name }}"
                    readonly
                    style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#f9fafb;font-size:.9rem;">
            </div>

            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="font-size:.85rem;font-weight:500;">Nomor Induk Karyawan (NIK)</label>
                <input
                    type="text"
                    value="{{ $snapshot['nik'] ?? '' }}"
                    readonly
                    style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#f9fafb;font-size:.9rem;">
            </div>

            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="font-size:.85rem;font-weight:500;">Jabatan</label>
                <input
                    type="text"
                    value="{{ $snapshot['position'] ?? '' }}"
                    readonly
                    style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#f9fafb;font-size:.9rem;">
            </div>

            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="font-size:.85rem;font-weight:500;">Departemen / Divisi</label>
                <input
                    type="text"
                    value="{{ $snapshot['division'] ?? '' }}"
                    readonly
                    style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#f9fafb;font-size:.9rem;">
            </div>

            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="font-size:.85rem;font-weight:500;">Perusahaan</label>
                <input
                    type="text"
                    value="{{ $snapshot['pt'] ?? '' }}"
                    readonly
                    style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#f9fafb;font-size:.9rem;">
            </div>
        </div>

        <hr style="border:none;border-top:1px solid #e5e7eb;margin:8px 0;">

        <div style="font-size:.9rem;font-weight:600;margin-bottom:4px;">
            Detail Pengajuan Hutang
        </div>

        <div style="display:flex;flex-direction:column;gap:10px;">

            <div style="display:flex;flex-direction:column;gap:4px;">
                <label for="amount_display" style="font-size:.85rem;font-weight:500;">Besar Pinjaman</label>
                <input
                    id="amount_display"
                    type="text"
                    inputmode="numeric"
                    autocomplete="off"
                    value="{{ old('amount') ? 'Rp' . number_format(old('amount'), 0, ',', '.') : '' }}"
                    required
                    style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
                <input
                    id="amount"
                    type="hidden"
                    name="amount"
                    value="{{ old('amount') }}">
            </div>

            <div style="display:flex;flex-direction:column;gap:4px;">
                <label for="purpose" style="font-size:.85rem;font-weight:500;">Keperluan</label>
                <textarea
                    id="purpose"
                    name="purpose"
                    rows="3"
                    style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;resize:vertical;">{{ old('purpose') }}</textarea>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;">
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label for="disbursement_date" style="font-size:.85rem;font-weight:500;">Tanggal Pinjam</label>
                    <input
                        id="disbursement_date"
                        type="date"
                        name="disbursement_date"
                        value="{{ old('disbursement_date') }}"
                        style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
                </div>

                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label for="installment_months" style="font-size:.85rem;font-weight:500;">Jangka Waktu Cicilan (bulan)</label>
                    <select
                        id="installment_months"
                        name="installment_months"
                        style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
                        <option value="">Pilih jangka waktu</option>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" @selected(old('installment_months') == $i)>
                                {{ $i }} bulan
                            </option>
                        @endfor
                    </select>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="font-size:.85rem;font-weight:500;">Perkiraan Cicilan Per Bulan</label>
                <input
                    id="installment_preview"
                    type="text"
                    value=""
                    readonly
                    style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#f9fafb;font-size:.9rem;">
                <span style="font-size:.8rem;opacity:.7;">
                    Nilai ini adalah perhitungan otomatis dari besar pinjaman dibagi jangka waktu cicilan.
                </span>
            </div>

            <div style="display:flex;flex-direction:column;gap:4px;">
                <label for="payment_method" style="font-size:.85rem;font-weight:500;">Cara Pengembalian</label>
                <select
                    id="payment_method"
                    name="payment_method"
                    required
                    style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;min-width:180px;">
                    <option value="TUNAI" @selected(old('payment_method') === 'TUNAI')>Tunai</option>
                    <option value="CICILAN" @selected(old('payment_method') === 'CICILAN')>Cicilan (transfer ke rekening perusahaan)</option>
                    <option value="POTONG_GAJI" @selected(old('payment_method', 'POTONG_GAJI') === 'POTONG_GAJI')>Pemotongan Gaji</option>
                </select>
                <span style="font-size:.8rem;opacity:.7;">
                    Default pengembalian adalah pemotongan gaji. Sesuaikan bila ada kesepakatan lain dengan HRD.
                </span>
            </div>

            <div style="display:flex;flex-direction:column;gap:4px;">
                <label for="document" style="font-size:.85rem;font-weight:500;">Bukti Dokumen</label>
                <input
                    id="document"
                    type="file"
                    name="document"
                    accept=".jpg,.jpeg,.png,.pdf"
                    style="width:100%;padding:6px 0;font-size:.9rem;">
                <span style="font-size:.8rem;opacity:.7;">
                    Format: JPG, JPEG, PNG, atau PDF. Maksimal 2 MB.
                </span>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:8px;">
            <button type="submit"
                    style="padding:8px 16px;border-radius:8px;border:none;background:#1e4a8d;color:#fff;font-size:.9rem;cursor:pointer;">
                Ajukan Hutang
            </button>
        </div>
    </form>

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
                    updateInstallmentPreview();
                    return;
                }

                var numeric = parseInt(digits);
                hiddenInput.value = numeric;
                displayInput.value = 'Rp' + formatRupiahNumber(numeric);
                updateInstallmentPreview();
            }

            function formatRupiah(value) {
                if (!value || isNaN(value)) return '';
                return 'Rp' + Number(value).toLocaleString('id-ID');
            }

            function updateInstallmentPreview() {
                var hiddenAmount = document.getElementById('amount');
                var monthsSelect = document.getElementById('installment_months');
                var previewInput = document.getElementById('installment_preview');

                if (!hiddenAmount || !monthsSelect || !previewInput) return;

                var amount = parseFloat(hiddenAmount.value || '0');
                var months = parseInt(monthsSelect.value || '0');

                if (amount > 0 && months > 0) {
                    var perMonth = Math.floor(amount / months);
                    previewInput.value = formatRupiah(perMonth) + ' per bulan';
                } else {
                    previewInput.value = '';
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                var displayInput = document.getElementById('amount_display');
                var monthsSelect = document.getElementById('installment_months');
                var hiddenAmount = document.getElementById('amount');

                if (displayInput) {
                    displayInput.addEventListener('input', updateAmountFormatting);
                    displayInput.addEventListener('blur', updateAmountFormatting);
                }

                if (monthsSelect) {
                    monthsSelect.addEventListener('change', updateInstallmentPreview);
                }

                if (hiddenAmount && hiddenAmount.value) {
                    displayInput.value = 'Rp' + formatRupiahNumber(hiddenAmount.value);
                }

                updateInstallmentPreview();
            });
        </script>
    @endpush
</x-app>
