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

        <form action="{{ route('hr.payroll.import.store') }}" method="POST">
            @csrf

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
                                <th rowspan="2" style="min-width: 150px; vertical-align: middle;">Karyawan</th>
                                <th colspan="12" class="text-center" style="border-bottom: 1px solid #d1d5db;">PENDAPATAN</th>
                                <th colspan="6" class="text-center" style="border-bottom: 1px solid #d1d5db;">PENGELUARAN</th>
                                <th rowspan="2" style="min-width: 100px; vertical-align: middle;">Total Penghasilan</th>
                                <th rowspan="2" style="min-width: 120px; vertical-align: middle;">Ket/Sisa Utang</th>
                            </tr>
                            <tr>
                                <!-- Pendapatan -->
                                <th style="min-width: 110px;">Gaji Pokok</th>
                                <th style="min-width: 110px;">Tunj. Jabatan</th>
                                <th style="min-width: 110px;">Tunj. Makan</th>
                                <th style="min-width: 120px;">Fee Marketing</th>
                                <th style="min-width: 120px;">Telekomunikasi</th>
                                <th style="min-width: 110px;">Penempatan</th>
                                <th style="min-width: 110px;">Asuransi</th>
                                <th style="min-width: 110px;">Kelancaran</th>
                                <th style="min-width: 110px;">Lain-lain</th>
                                <th style="min-width: 110px;">Transport</th>
                                <th style="min-width: 110px;">Lembur</th>
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
                                <td>
                                    <div class="fw-bold">{{ $row['user_name'] }}</div>
                                    <div class="text-muted">{{ $row['nik'] }}</div>
                                    <input type="hidden" name="payslips[{{ $index }}][user_id]" value="{{ $row['user_id'] }}">
                                </td>

                                <!-- Pendapatan -->
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][gaji_pokok]" value="{{ number_format($row['gaji_pokok'], 0, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_jabatan]" value="{{ number_format($row['tunjangan_jabatan'], 0, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_makan]" value="{{ number_format($row['tunjangan_makan'], 0, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][fee_marketing]" value="{{ number_format($row['fee_marketing'], 0, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_telekomunikasi]" value="{{ number_format($row['tunjangan_telekomunikasi'], 0, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_penempatan]" value="{{ number_format($row['tunjangan_penempatan'], 0, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_asuransi]" value="{{ number_format($row['tunjangan_asuransi'], 0, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_kelancaran]" value="{{ number_format($row['tunjangan_kelancaran'], 0, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][pendapatan_lain]" value="{{ number_format($row['pendapatan_lain'], 0, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_transportasi]" value="{{ number_format($row['tunjangan_transportasi'], 0, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][lembur]" value="{{ number_format($row['lembur'], 0, ',', '.') }}" class="form-control-sm input-income"> </td>
                                <td style="background-color: #f9fafb;">
                                    <span class="fw-bold text-success total-income">{{ number_format($row['total_pendapatan'], 0, ',', '.') }}</span>
                                </td>

                                <!-- Pengeluaran -->
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][potongan_bpjs_tk]" value="{{ number_format($row['potongan_bpjs_tk'], 0, ',', '.') }}" class="form-control-sm input-deduction"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][potongan_pph21]" value="{{ number_format($row['potongan_pph21'], 0, ',', '.') }}" class="form-control-sm input-deduction"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][potongan_hutang]" value="{{ number_format($row['potongan_hutang'], 0, ',', '.') }}" class="form-control-sm input-deduction"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][potongan_bpjs_kes]" value="{{ number_format($row['potongan_bpjs_kes'], 0, ',', '.') }}" class="form-control-sm input-deduction"> </td>
                                <td> <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][potongan_terlambat]" value="{{ number_format($row['potongan_terlambat'], 0, ',', '.') }}" class="form-control-sm input-deduction"> </td>
                                <td style="background-color: #f9fafb;">
                                    <span class="fw-bold text-danger total-deduction">{{ number_format($row['total_potongan'], 0, ',', '.') }}</span>
                                </td>

                                <!-- Summary & Actions -->
                                <td>
                                    <span class="fw-bold text-primary total-thp">{{ number_format($row['gaji_bersih'], 0, ',', '.') }}</span>
                                </td>

                                <td>
                                    <input type="text" oninput="formatInput(this)" name="payslips[{{ $index }}][sisa_utang]" value="{{ number_format($row['sisa_utang'] ?? 0, 0, ',', '.') }}" class="form-control-sm" style="min-width: 100px;">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 24px; display: flex; gap: 12px; justify-content: flex-end;">
                    <a href="{{ route('hr.payroll.index') }}" class="btn-action" style="padding: 8px 16px;">
                        Batal
                    </a>
                    <button type="submit" name="action" value="draft" class="btn-action" style="background: #f3f4f6; color: #374151; padding: 8px 16px;">
                        Simpan DRAFT
                    </button>
                    <button type="submit" name="action" value="publish" class="btn-action btn-action-primary" style="padding: 8px 16px;" onclick="return confirm('Yakin ingin mempublikasikan dan mengirim email ke semua karyawan terkait?')">
                        Publish & Kirim Email
                    </button>
                </div>
            </div>
        </form>
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
            overflow: hidden;
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
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
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
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.querySelector('.custom-table');

            // Find all rows (excluding header)
            const rows = table.querySelectorAll('tbody tr');

            // Mapping assets for JS


            const ptSelect = document.getElementById('pt_id');
            const companyNameDisplay = document.getElementById('company-name-display');

            ptSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];

                // Update Name text
                companyNameDisplay.textContent = selectedOption.text.trim();
            });

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