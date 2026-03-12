<x-app title="Preview Import Slip Gaji">
    <div class="card">
        <div class="card-header-simple" style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div style="display: flex; gap: 16px; align-items: center;">


                <div>
                    <h4 class="card-title-sm">Preview Import Data Gaji</h4>
                    <p style="font-size: 12px; color: #6b7280; margin-top: 2px;">
                        Silakan periksa data sebelum disimpan. Anda dapat mengedit angka jika diperlukan.
                    </p>
                </div>
            </div>


        </div>

        <form id="payroll-import-form" action="{{ route('hr.payroll.import.store') }}" method="POST">
            @csrf
            <input type="hidden" name="action" id="payroll-action-input" value="">

            <!-- Filter Section -->
            <div style="padding: 16px; background: #f9fafb; border-bottom: 1px solid #f3f4f6;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; align-items: end;">
                    <div>
                        <label for="month" style="display: block; font-size: 12px; margin-bottom: 4px; color: #4b5563; font-weight: 600;">Bulan</label>
                        <select name="month" id="month" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 6px; font-size: 13px;" required>
                            <option value="">-- Pilih Bulan --</option>
                            @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}">
                                {{ \Carbon\Carbon::create()->month($m)->locale('id')->translatedFormat('F') }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="year" style="display: block; font-size: 12px; margin-bottom: 4px; color: #4b5563; font-weight: 600;">Tahun</label>
                        <select name="year" id="year" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 6px; font-size: 13px;" required>
                            <option value="">-- Pilih Tahun --</option>
                            @foreach(range(date('Y') + 1, 2023) as $y)
                            <option value="{{ $y }}">
                                {{ $y }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="pt_id" style="display: block; font-size: 12px; margin-bottom: 4px; color: #4b5563; font-weight: 600;">Perusahaan (PT)</label>
                        <select name="pt_id" id="pt_id" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 6px; font-size: 13px;" required>
                            <option value="">-- Pilih Perusahaan --</option>
                            @foreach($pts as $p)
                            <option value="{{ $p->id }}" {{ $p->id == ($pt->id ?? '') ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div style="padding: 16px;">
                <div class="table-wrapper">
                    <table class="custom-table" style="font-size: 11px;">
                        <thead>
                            <tr style="background-color: #f3f4f6;">
                                <th rowspan="2" style="width: 40px; text-align: center; vertical-align: middle;" class="col-sticky-1">#</th>
                                <th rowspan="2" style="min-width: 150px; vertical-align: middle;" class="col-sticky-2">Karyawan</th>
                                <th colspan="16" class="text-center" style="border-bottom: 1px solid #d1d5db;">PENDAPATAN</th>
                                <th colspan="6" class="text-center" style="border-bottom: 1px solid #d1d5db;">PENGELUARAN</th>
                                <th rowspan="2" style="min-width: 100px; vertical-align: middle;">Total Penghasilan</th>
                                <th rowspan="2" style="min-width: 120px; vertical-align: middle;">Ket/Sisa Utang</th>
                                <th rowspan="2" style="min-width: 90px; text-align: center; vertical-align: middle;">Aksi</th>
                            </tr>
                            <tr>
                                <!-- Pendapatan -->
                                <th style="min-width: 110px;">Gaji Pokok</th>
                                <th style="min-width: 110px;">Tunj. Jabatan</th>
                                <th style="min-width: 110px;">Tunj. Makan</th>
                                <th style="min-width: 120px;">Fee Marketing</th>
                                <th style="min-width: 120px;">Bonus Bulanan</th>
                                <th style="min-width: 120px;">Telekomunikasi</th>
                                <th style="min-width: 120px;">Tunj. Lainnya</th>
                                <th style="min-width: 110px;">Penempatan</th>
                                <th style="min-width: 110px;">Asuransi</th>
                                <th style="min-width: 110px;">Kelancaran</th>
                                <th style="min-width: 110px;">Lain-lain</th>
                                <th style="min-width: 110px;">Transport</th>
                                <th style="min-width: 110px;">Lembur</th>
                                <th style="min-width: 110px;">THR</th>
                                <th style="min-width: 110px;">Bonus</th>
                                <th style="min-width: 120px; background-color: #e5e7eb;">Total</th>

                                <!-- Pengeluaran -->
                                <th style="min-width: 110px;">BPJS TK</th>
                                <th style="min-width: 110px;">PPh 21</th>
                                <th style="min-width: 110px;">Hutang</th>
                                <th style="min-width: 110px;">BPJS Kes</th>
                                <th style="min-width: 110px;">Keterlambatan</th>
                                <th style="min-width: 100px; background-color: #e5e7eb;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payslips as $index => $row)
                            <tr>
                                <td style="text-align: center;" class="col-sticky-1">{{ $index + 1 }}</td>
                                <td class="col-sticky-2">
                                    <div class="fw-bold">{{ $row['user_name'] }}</div>
                                    <div class="text-muted">{{ $row['email'] ?? '-' }}</div>
                                    <input type="hidden" name="payslips[{{ $index }}][user_id]" value="{{ $row['user_id'] }}">
                                </td>

                                <!-- Pendapatan -->
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][gaji_pokok]" value="{{ number_format($row['gaji_pokok'], 2, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_jabatan]" value="{{ number_format($row['tunjangan_jabatan'], 2, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_makan]" value="{{ number_format($row['tunjangan_makan'], 2, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][fee_marketing]" value="{{ number_format($row['fee_marketing'], 2, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][bonus_bulanan]" value="{{ number_format($row['bonus_bulanan'] ?? 0, 2, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_telekomunikasi]" value="{{ number_format($row['tunjangan_telekomunikasi'], 2, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_lainnya]" value="{{ number_format($row['tunjangan_lainnya'] ?? 0, 2, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_penempatan]" value="{{ number_format($row['tunjangan_penempatan'], 2, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_asuransi]" value="{{ number_format($row['tunjangan_asuransi'], 2, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_kelancaran]" value="{{ number_format($row['tunjangan_kelancaran'], 2, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][pendapatan_lain]" value="{{ number_format($row['pendapatan_lain'], 2, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_transportasi]" value="{{ number_format($row['tunjangan_transportasi'], 2, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][lembur]" value="{{ number_format($row['lembur'], 2, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][thr]" value="{{ number_format($row['thr'] ?? 0, 2, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][bonus]" value="{{ number_format($row['bonus'] ?? 0, 2, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td style="background-color: #f9fafb;">
                                    <span class="fw-bold text-success total-income">{{ number_format($row['total_pendapatan'], 0, ',', '.') }}</span>
                                </td>

                                <!-- Pengeluaran -->
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][potongan_bpjs_tk]" value="{{ number_format($row['potongan_bpjs_tk'], 2, ',', '.') }}" class="form-control-sm input-deduction"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][potongan_pph21]" value="{{ number_format($row['potongan_pph21'], 2, ',', '.') }}" class="form-control-sm input-deduction"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][potongan_hutang]" value="{{ number_format($row['potongan_hutang'], 2, ',', '.') }}" class="form-control-sm input-deduction"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][potongan_bpjs_kes]" value="{{ number_format($row['potongan_bpjs_kes'], 2, ',', '.') }}" class="form-control-sm input-deduction"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][potongan_terlambat]" value="{{ number_format($row['potongan_terlambat'], 2, ',', '.') }}" class="form-control-sm input-deduction"> </td>
                                <td style="background-color: #f9fafb;">
                                    <span class="fw-bold text-danger total-deduction">{{ number_format($row['total_potongan'], 0, ',', '.') }}</span>
                                </td>

                                <!-- Summary & Actions -->
                                <td>
                                    <span class="fw-bold text-primary total-thp">{{ number_format($row['gaji_bersih'], 0, ',', '.') }}</span>
                                </td>

                                <td>
                                    <input type="text" name="payslips[{{ $index }}][sisa_utang]" value="{{ $row['sisa_utang'] ?? '' }}" class="form-control-sm" style="min-width: 100px;">
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-action btn-delete-row" style="padding: 4px 10px; border-color: #ef4444; color: #dc2626;">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 24px; display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" id="btn-back-history" class="btn-action" style="padding: 8px 16px;">
                        Batal
                    </button>
                    <button type="button" id="btn-save-draft" class="btn-action" style="background: #f3f4f6; color: #374151; padding: 8px 16px;">
                        Simpan DRAFT
                    </button>
                    <button type="button" id="btn-publish-email" class="btn-action btn-action-primary" style="padding: 8px 16px;">
                        Publish & Kirim Email
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div id="publish-confirm-modal" class="confirm-modal" style="display: none;">
        <div class="confirm-modal-backdrop"></div>
        <div class="confirm-modal-content" role="dialog" aria-modal="true" aria-labelledby="publish-confirm-title">
            <h3 id="publish-confirm-title" style="margin: 0 0 8px; font-size: 16px; color: #111827;">Konfirmasi Publish</h3>
            <p style="margin: 0 0 16px; font-size: 13px; color: #4b5563;">Yakin ingin mempublikasikan dan menjadwalkan email ke semua karyawan terkait? Estimasi pengiriman selesai sekitar 45 menit.</p>
            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" id="btn-cancel-publish" class="btn-action" style="padding: 8px 14px;">Batal</button>
                <button type="button" id="btn-confirm-publish" class="btn-action btn-action-primary" style="padding: 8px 14px;">Ya, Publish</button>
            </div>
        </div>
    </div>

    <div id="delete-confirm-modal" class="confirm-modal" style="display: none;">
        <div class="confirm-modal-backdrop"></div>
        <div class="confirm-modal-content" role="dialog" aria-modal="true" aria-labelledby="delete-confirm-title">
            <h3 id="delete-confirm-title" style="margin: 0 0 8px; font-size: 16px; color: #111827;">Konfirmasi Hapus Baris</h3>
            <p style="margin: 0 0 16px; font-size: 13px; color: #4b5563;">Baris ini akan dihapus dari proses import. Lanjutkan?</p>
            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" id="btn-cancel-delete" class="btn-action" style="padding: 8px 14px;">Batal</button>
                <button type="button" id="btn-confirm-delete" class="btn-action" style="padding: 8px 14px; border-color: #ef4444; color: #dc2626;">Hapus</button>
            </div>
        </div>
    </div>

    <div id="warning-modal" class="confirm-modal" style="display: none;">
        <div class="confirm-modal-backdrop"></div>
        <div class="confirm-modal-content" role="dialog" aria-modal="true" aria-labelledby="warning-title">
            <h3 id="warning-title" style="margin: 0 0 8px; font-size: 16px; color: #111827;">Peringatan</h3>
            <p id="warning-message" style="margin: 0 0 16px; font-size: 13px; color: #4b5563;">Mohon lengkapi data yang wajib diisi.</p>
            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" id="btn-warning-ok" class="btn-action btn-action-primary" style="padding: 8px 14px;">OK</button>
            </div>
        </div>
    </div>

    <div id="submit-loading-overlay" class="loading-overlay" style="display: none;">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <p id="loading-message" style="margin: 10px 0 0; font-size: 13px; color: #374151;">Sedang memproses...</p>
            <p id="loading-submessage" style="margin: 8px 0 0; font-size: 12px; color: #6b7280; line-height: 1.5;">Email akan diproses bertahap melalui queue dan estimasi selesai semua sekitar 45 menit untuk pengiriman massal. Overlay ini boleh ditutup, tetapi sebaiknya jangan tutup tab atau pindah halaman sebelum submit selesai.</p>
            <div style="margin-top: 14px; display: flex; justify-content: center;">
                <button type="button" id="btn-close-loading" class="btn-action" style="padding: 8px 14px;">Tutup Info</button>
            </div>
        </div>
    </div>

    <style>
        .form-control-sm {
            width: 100%;
            padding: 6px 8px;
            font-size: 13px;
            border: 1px solid transparent;
            border-radius: 2px;
            background-color: transparent;
            transition: all 0.15s ease-in-out;
            color: #1f2937;
        }

        .form-control-sm:hover {
            border-color: #d1d5db;
        }

        .form-control-sm:focus {
            outline: none;
            border-color: #217346;
            /* Excel green */
            box-shadow: 0 0 0 1px #217346;
            background-color: #fff;
        }

        /* Highlight entire cell on hover to mimic spreadsheet */
        .custom-table td:has(.form-control-sm:focus) {
            background-color: #f0fdf4 !important;
            /* light green tint */
        }

        /* Copy styles from index for consistency */
        /* ... (Previously defined styles) ... */
        .fw-bold {
            font-weight: 600;
            color: #111827;
        }

        .text-muted {
            color: #6b7280;
            font-size: 11px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid #f3f4f6;
            margin-bottom: 20px;
        }

        .card-header-simple {
            padding: 16px 24px;
            border-bottom: 1px solid #f3f4f6;
            background: #fff;
        }

        .card-title-sm {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
        }

        .card-subtitle-sm {
            margin: 4px 0 0;
            font-size: 13px;
            color: #6b7280;
        }

        .table-wrapper {
            width: 100%;
            overflow: auto;
            max-height: calc(100vh - 350px);
            min-height: 300px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            position: relative;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        .custom-table th {
            background: #f9fafb;
            padding: 10px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
            /* Sticky Header */
            position: sticky;
            top: 0;
            z-index: 20;
            box-shadow: inset 0 -1px 0 #e5e7eb;
        }

        /* Specific sticky offset for second row of header */
        .custom-table thead tr:nth-child(2) th {
            top: 35px;
            /* Adjust to match the first row height exactly */
            z-index: 19;
        }

        /* Sticky columns logic */
        .col-sticky-1 {
            position: sticky;
            left: 0;
            z-index: 21;
            background: #f9fafb;
            border-right: 1px solid #e5e7eb;
            width: 40px;
            min-width: 40px;
        }

        .col-sticky-2 {
            position: sticky;
            left: 40px;
            /* Width of col-sticky-1 */
            z-index: 21;
            background: #f9fafb;
            border-right: 1px solid #e5e7eb;
        }

        td.col-sticky-1 {
            z-index: 10;
            background: #fff;
        }

        td.col-sticky-2 {
            z-index: 10;
            background: #fff;
        }

        /* Adjust z-index for intersection of sticky header and sticky columns */
        thead tr:nth-child(1) th.col-sticky-1,
        thead tr:nth-child(1) th.col-sticky-2 {
            z-index: 30;
        }

        .custom-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
            color: #1f2937;
            vertical-align: middle;
        }

        .btn-action {
            padding: 4px 12px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-action:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }

        .btn-action-primary {
            background: #4f46e5;
            color: #fff;
            border-color: #4f46e5;
        }

        .btn-action-primary:hover {
            background: #4338ca;
            border-color: #4338ca;
            color: #fff;
        }

        .confirm-modal {
            position: fixed;
            inset: 0;
            z-index: 1100;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .confirm-modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(17, 24, 39, 0.4);
        }

        .confirm-modal-content {
            position: relative;
            width: min(420px, calc(100% - 32px));
            background: #fff;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            padding: 16px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.18);
        }

        .loading-overlay {
            position: fixed;
            inset: 0;
            z-index: 1200;
            background: rgba(255, 255, 255, 0.72);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loading-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px 18px;
            min-width: 250px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .loading-spinner {
            width: 28px;
            height: 28px;
            border-radius: 9999px;
            border: 3px solid #e5e7eb;
            border-top-color: #4f46e5;
            margin: 0 auto;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('payroll-import-form');
            const actionInput = document.getElementById('payroll-action-input');
            const btnSaveDraft = document.getElementById('btn-save-draft');
            const btnPublishEmail = document.getElementById('btn-publish-email');
            const confirmModal = document.getElementById('publish-confirm-modal');
            const btnCancelPublish = document.getElementById('btn-cancel-publish');
            const btnConfirmPublish = document.getElementById('btn-confirm-publish');
            const deleteConfirmModal = document.getElementById('delete-confirm-modal');
            const btnCancelDelete = document.getElementById('btn-cancel-delete');
            const btnConfirmDelete = document.getElementById('btn-confirm-delete');
            const warningModal = document.getElementById('warning-modal');
            const warningMessage = document.getElementById('warning-message');
            const btnWarningOk = document.getElementById('btn-warning-ok');
            const loadingOverlay = document.getElementById('submit-loading-overlay');
            const loadingMessage = document.getElementById('loading-message');
            const btnCloseLoading = document.getElementById('btn-close-loading');
            const table = document.querySelector('.custom-table');
            const tbody = table.querySelector('tbody');
            const monthSelect = document.getElementById('month');
            const yearSelect = document.getElementById('year');
            const btnBackHistory = document.getElementById('btn-back-history');
            let isSubmitting = false;
            let pendingDeleteRow = null;

            if (btnBackHistory) {
                btnBackHistory.addEventListener('click', function() {
                    sessionStorage.setItem('payroll_refresh_after_back', '1');

                    if (window.history.length > 1) {
                        window.history.back();
                        return;
                    }

                    window.location.href = "{{ route('hr.payroll.index') }}";
                });
            }

            // Find all rows (excluding header)
            const rows = table.querySelectorAll('tbody tr');

            // Mapping assets for JS


            const ptSelect = document.getElementById('pt_id');
            const companyNameDisplay = document.getElementById('company-name-display');

            if (ptSelect && companyNameDisplay) {
                ptSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];

                    // Update Name text
                    companyNameDisplay.textContent = selectedOption.text.trim();
                });
            }

            window.formatInput = function(input) {
                // Strip non-numeric except comma
                let value = input.value.replace(/[^0-9,]/g, '');

                // Split decimals
                let parts = value.split(',');
                let integerPart = parts[0];
                let decimalPart = parts.length > 1 ? ',' + parts[1] : '';

                // Strip leading zeros if not decimal
                if (integerPart.length > 1 && integerPart.startsWith('0')) {
                    integerPart = integerPart.substring(1);
                }

                // Add dots to integer part
                integerPart = integerPart.replace(/\./g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ".");

                input.value = integerPart + decimalPart;

                // Trigger update totals handled by event listener
            };

            rows.forEach(row => {
                // Add event listeners to inputs in this row
                const inputs = row.querySelectorAll('.input-income, .input-deduction');
                inputs.forEach(input => {
                    input.addEventListener('input', () => updateTotals(row));
                });
            });

            tbody.addEventListener('click', function(event) {
                const deleteButton = event.target.closest('.btn-delete-row');
                if (!deleteButton) return;

                const row = deleteButton.closest('tr');
                if (!row) return;

                pendingDeleteRow = row;
                openDeleteModal();
            });

            form.addEventListener('submit', function(event) {
                if (isSubmitting) {
                    event.preventDefault();
                    return;
                }

                if (!validateRequiredFilters()) {
                    event.preventDefault();
                    return;
                }

                if (tbody.querySelectorAll('tr').length === 0) {
                    event.preventDefault();
                    showWarning('Tidak ada baris data untuk disimpan.');
                }
            });

            btnSaveDraft.addEventListener('click', function() {
                try {
                    submitWithAction('draft');
                } catch (error) {
                    console.error(error);
                    showWarning('Terjadi kesalahan saat menyimpan draft. Silakan coba lagi.');
                }
            });

            btnPublishEmail.addEventListener('click', function() {
                openPublishModal();
            });

            btnCancelPublish.addEventListener('click', function() {
                closePublishModal();
            });

            btnConfirmPublish.addEventListener('click', function() {
                closePublishModal();
                try {
                    submitWithAction('publish');
                } catch (error) {
                    console.error(error);
                    showWarning('Terjadi kesalahan saat publish. Silakan coba lagi.');
                }
            });

            btnCancelDelete.addEventListener('click', function() {
                closeDeleteModal();
            });

            btnConfirmDelete.addEventListener('click', function() {
                if (pendingDeleteRow) {
                    pendingDeleteRow.remove();
                    renumberRows();
                }
                closeDeleteModal();
            });

            confirmModal.querySelector('.confirm-modal-backdrop').addEventListener('click', function() {
                closePublishModal();
            });

            deleteConfirmModal.querySelector('.confirm-modal-backdrop').addEventListener('click', function() {
                closeDeleteModal();
            });

            warningModal.querySelector('.confirm-modal-backdrop').addEventListener('click', function() {
                closeWarning();
            });

            btnWarningOk.addEventListener('click', function() {
                closeWarning();
            });

            function openPublishModal() {
                if (isSubmitting) return;

                if (!validateRequiredFilters()) {
                    return;
                }

                if (tbody.querySelectorAll('tr').length === 0) {
                    showWarning('Tidak ada baris data untuk disimpan.');
                    return;
                }

                confirmModal.style.display = 'flex';
            }

            function closePublishModal() {
                confirmModal.style.display = 'none';
            }

            function openDeleteModal() {
                if (isSubmitting) return;
                deleteConfirmModal.style.display = 'flex';
            }

            function closeDeleteModal() {
                deleteConfirmModal.style.display = 'none';
                pendingDeleteRow = null;
            }

            function submitWithAction(action) {
                if (isSubmitting) {
                    return;
                }

                if (!validateRequiredFilters()) {
                    return;
                }

                if (tbody.querySelectorAll('tr').length === 0) {
                    showWarning('Tidak ada baris data untuk disimpan.');
                    return;
                }

                actionInput.value = action;
                isSubmitting = true;

                if (action === 'publish') {
                    showLoading('Data sedang dipublish ke queue email...');
                } else {
                    showLoading('Sedang menyimpan draft, mohon tunggu...');
                }

                disableActions();
                form.submit();
            }

            function validateRequiredFilters() {
                const monthValue = monthSelect ? monthSelect.value : '';
                const yearValue = yearSelect ? yearSelect.value : '';

                if (!monthValue || !yearValue) {
                    showWarning('Bulan dan Tahun wajib dipilih sebelum Simpan Draft atau Publish & Kirim Email.');
                    return false;
                }

                return true;
            }

            function showWarning(message) {
                warningMessage.textContent = message;
                warningModal.style.display = 'flex';
            }

            function closeWarning() {
                warningModal.style.display = 'none';
            }

            function showLoading(message) {
                loadingMessage.textContent = message;
                loadingOverlay.style.display = 'flex';
            }

            function closeLoading() {
                loadingOverlay.style.display = 'none';
            }

            if (btnCloseLoading) {
                btnCloseLoading.addEventListener('click', function() {
                    closeLoading();
                });
            }

            function disableActions() {
                btnSaveDraft.disabled = true;
                btnPublishEmail.disabled = true;
                btnCancelPublish.disabled = true;
                btnConfirmPublish.disabled = true;
                btnCancelDelete.disabled = true;
                btnConfirmDelete.disabled = true;
            }

            function renumberRows() {
                const currentRows = tbody.querySelectorAll('tr');
                currentRows.forEach((row, index) => {
                    const numberCell = row.querySelector('td.col-sticky-1');
                    if (numberCell) {
                        numberCell.textContent = index + 1;
                    }
                });
            }

            function updateTotals(row) {
                let totalIncome = 0;
                let totalDeduction = 0;

                // Helper to parse currency (matches Controller logic: remove dots, replace comma with dot)
                function parseCurrency(value) {
                    if (!value) return 0;
                    let clean = value.toString().replace(/\./g, '').replace(/,/g, '.');
                    return parseFloat(clean) || 0;
                }

                // Calculate Income
                row.querySelectorAll('.input-income').forEach(input => {
                    totalIncome += parseCurrency(input.value);
                });

                // Calculate Deduction
                row.querySelectorAll('.input-deduction').forEach(input => {
                    totalDeduction += parseCurrency(input.value);
                });

                // Calculate THP
                const thp = totalIncome - totalDeduction;

                // Update Spans (using ID formatter)
                const formatter = new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });

                const totalIncomeSpan = row.querySelector('.total-income');
                if (totalIncomeSpan) totalIncomeSpan.textContent = formatter.format(totalIncome);

                const totalDeductionSpan = row.querySelector('.total-deduction');
                if (totalDeductionSpan) totalDeductionSpan.textContent = formatter.format(totalDeduction);

                const totalThpSpan = row.querySelector('.total-thp');
                if (totalThpSpan) totalThpSpan.textContent = formatter.format(thp);
            }
        });
    </script>
</x-app>