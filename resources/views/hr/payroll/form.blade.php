<x-app :title="isset($payslip) ? 'Edit Slip Gaji' : 'Input Slip Gaji'">
    <div class="card">
        <div class="card-header-simple">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h4 class="card-title-sm">
                        {{ isset($payslip) ? 'Edit Slip Gaji' : 'Input Slip Gaji' }}
                    </h4>
                    <p class="card-subtitle-sm">
                        {{ $user->name ?? 'User not found' }} | {{ \Carbon\Carbon::create()->month((int)($month ?: date('m')))->locale('id')->translatedFormat('F') }} {{ $year ?: date('Y') }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('hr.payroll.index', ['start_month' => request('filter_start_month'), 'end_month' => request('filter_end_month'), 'year' => request('filter_year'), 'pt_id' => request('filter_pt_id')]) }}"
                        style="font-size: 13px; font-weight: 500; color: #374151; text-decoration: none; display: flex; align-items: center; gap: 6px; border: 1px solid #d1d5db; padding: 6px 16px; border-radius: 6px; background: #fff; transition: all 0.2s;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 12H5" />
                            <path d="M12 19l-7-7 7-7" />
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <div style="padding: 24px;">
            @if ($errors->any())
            <div style="background-color: #fee2e2; border: 1px solid #f87171; color: #b91c1c; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ (isset($payslip) && $payslip->id) ? route('hr.payroll.update', $payslip->id) : route('hr.payroll.store') }}" method="POST">
                @csrf
                <!-- HIDDEN FILTERS TO PERSIST STATE -->
                <input type="hidden" name="filter_start_month" value="{{ request('filter_start_month') }}">
                <input type="hidden" name="filter_end_month" value="{{ request('filter_end_month') }}">
                <input type="hidden" name="filter_year" value="{{ request('filter_year') }}">
                <input type="hidden" name="filter_pt_id" value="{{ request('filter_pt_id') }}">

                @if(isset($payslip))
                @method('PUT')
                @else
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <input type="hidden" name="period_month" value="{{ $month }}">
                <input type="hidden" name="period_year" value="{{ $year }}">
                @endif

                @php
                $selectedPtId = request('filter_pt_id') ?: (isset($payslip) ? ($payslip->user->profile->pt_id ?? '') : ($user->profile->pt_id ?? ''));
                @endphp

                <div style="margin-bottom: 24px; padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <label for="pt_id" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 8px;">Perusahaan (PT) untuk Logo PDF & Email</label>
                    <select name="pt_id" id="pt_id" style="width: 100%; max-width: 400px; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; background-color: #fff; cursor: pointer; color: #334155;" required>
                        <option value="">-- Pilih PT --</option>
                        @foreach($pts as $p)
                        <option value="{{ $p->id }}" {{ $selectedPtId == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                        @endforeach
                    </select>
                    <p style="margin: 8px 0 0 0; font-size: 12px; color: #64748b;">
                        <em>Pastikan PT sesuai. Logo dan stampel pada Slip Gaji (PDF & Email) akan mengikuti pilihan ini jika ditekan "Publish & Kirim Email".</em>
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px;">
                    <!-- PENDAPATAN -->
                    <div>
                        <h5 style="font-size: 14px; font-weight: 700; color: #1f2937; margin-bottom: 16px; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px;">Pendapatan</h5>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            @foreach([
                            'gaji_pokok' => 'Gaji Pokok',
                            'tunjangan_jabatan' => 'Tunjangan Jabatan',
                            'tunjangan_makan' => 'Tunjangan Makan',
                            'fee_marketing' => 'Fee Marketing',
                            'bonus_bulanan' => 'Bonus Bulanan',
                            'tunjangan_telekomunikasi' => 'Tunjangan Telekomunikasi',
                            'tunjangan_lainnya' => 'Tunjangan Lainnya',
                            'tunjangan_penempatan' => 'Tunjangan Penempatan',
                            'tunjangan_asuransi' => 'Tunjangan Asuransi',
                            'tunjangan_kelancaran' => 'Tunjangan Kelancaran',
                            'pendapatan_lain' => 'Pendapatan Lain',
                            'tunjangan_transportasi' => 'Tunjangan Transportasi',
                            'lembur' => 'Lembur',
                            'thr' => 'THR',
                            'bonus' => 'Bonus'
                            ] as $field => $label)
                            <div class="spreadsheet-row" style="display: flex; align-items: center; border-bottom: 1px solid #f3f4f6; padding: 2px 8px; transition: background-color 0.15s; border-radius: 4px;">
                                <label for="{{ $field }}" class="form-label" style="flex: 1; margin: 0; padding: 6px 0; font-size: 13px; font-weight: 500; color: #4b5563;">{{ $label }}</label>
                                <div style="width: 140px;">
                                    <input type="text" name="{{ $field }}" id="{{ $field }}"
                                        value="{{ old($field, isset($payslip) ? number_format($payslip->$field, 0, ',', '.') : '') }}"
                                        class="form-input income-input currency-input"
                                        placeholder="0">
                                </div>
                            </div>
                            @endforeach

                            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; font-weight: 700; color: #1f2937;">
                                <span>Total Pendapatan:</span>
                                <span id="display_total_pendapatan">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- POTONGAN & SUMMARY -->
                    <div>
                        <h5 style="font-size: 14px; font-weight: 700; color: #991b1b; margin-bottom: 16px; border-bottom: 2px solid #fee2e2; padding-bottom: 8px;">Potongan</h5>
                        <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 32px;">
                            @foreach([
                            'potongan_bpjs_tk' => 'Potongan BPJS TK',
                            'potongan_pph21' => 'Potongan PPh 21',
                            'potongan_hutang' => 'Potongan Hutang',
                            'potongan_bpjs_kes' => 'Potongan BPJS Kesehatan',
                            'potongan_terlambat' => 'Potongan Terlambat'
                            ] as $field => $label)
                            <div class="spreadsheet-row" style="display: flex; align-items: center; border-bottom: 1px solid #fee2e2; padding: 2px 8px; transition: background-color 0.15s; border-radius: 4px;">
                                <label for="{{ $field }}" class="form-label" style="flex: 1; margin: 0; padding: 6px 0; font-size: 13px; font-weight: 500; color: #4b5563;">{{ $label }}</label>
                                <div style="width: 140px;">
                                    <input type="text" name="{{ $field }}" id="{{ $field }}"
                                        value="{{ old($field, isset($payslip) ? number_format($payslip->$field, 0, ',', '.') : '') }}"
                                        class="form-input deduction-input currency-input"
                                        placeholder="0">
                                </div>
                            </div>
                            @endforeach

                            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; font-weight: 700; color: #991b1b;">
                                <span>Total Potongan:</span>
                                <span id="display_total_potongan">Rp 0</span>
                            </div>
                        </div>

                        <!-- FINAL SUMMARY PANEL -->
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <span style="font-size: 16px; font-weight: 800; color: #1e293b;">Gaji Bersih</span>
                                <span id="display_gaji_bersih" style="font-size: 20px; font-weight: 800; color: #4f46e5;">Rp 0</span>
                            </div>

                            <div class="spreadsheet-row" style="display: flex; align-items: center; padding: 2px 8px; margin-bottom: 24px; transition: background-color 0.15s; border-radius: 4px; border-bottom: 1px solid #e2e8f0;">
                                <label for="sisa_utang" class="form-label" style="flex: 1; margin: 0; padding: 6px 0; font-size: 13px; font-weight: 500; color: #4b5563;">Ket / Sisa Utang</label>
                                <div style="width: 140px;">
                                    @php
                                        $sisaUtangValue = old('sisa_utang', $payslip->sisa_utang ?? '');
                                        $sisaUtangText = trim((string) $sisaUtangValue);
                                        $normalizedSisaUtang = str_ireplace('rp', '', preg_replace('/\s+/', '', $sisaUtangText));
                                        if ($sisaUtangText === '' || preg_match('/^0+([.,]0+)?$/', $normalizedSisaUtang)) {
                                            $sisaUtangValue = '';
                                        }
                                    @endphp
                                    <input type="text" name="sisa_utang" id="sisa_utang"
                                        value="{{ $sisaUtangValue }}"
                                        class="form-input"
                                        placeholder="Keterangan"
                                        style="text-align: left;">
                                </div>
                            </div>

                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <a href="{{ route('hr.payroll.index', ['start_month' => request('filter_start_month'), 'end_month' => request('filter_end_month'), 'year' => request('filter_year'), 'pt_id' => request('filter_pt_id')]) }}" class="btn-action" style="padding: 10px 16px; background: #fff; border: 1px solid #d1d5db; border-radius: 6px; color: #374151; font-size: 13px; font-weight: 500; text-decoration: none; cursor: pointer;">
                                    Batal
                                </a>
                                <button type="submit" name="status" value="DRAFT" class="btn-action" style="padding: 10px 16px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; color: #374151; font-size: 13px; font-weight: 500; cursor: pointer;">
                                    Simpan DRAFT
                                </button>
                                <button type="submit" name="status" value="PUBLISHED" class="btn-action-primary" style="padding: 10px 16px; background: #4f46e5; border: none; border-radius: 6px; color: #fff; font-size: 13px; font-weight: 500; cursor: pointer;" onclick="return confirm('Yakin ingin mempublikasikan slip gaji ini? Email notifikasi akan dikirim ke karyawan terkait.')">
                                    Publish & Kirim Email
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const currencyInputs = document.querySelectorAll('.currency-input');
            const displayTotalPendapatan = document.getElementById('display_total_pendapatan');
            const displayTotalPotongan = document.getElementById('display_total_potongan');
            const displayGajiBersih = document.getElementById('display_gaji_bersih');

            // Format number string with dots
            function formatNumber(n) {
                // remove non-digits
                let num = n.replace(/\D/g, "");
                return num.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            // Parse formatted number string back to float
            function parseFormattedNumber(str) {
                if (!str) return 0;
                return parseFloat(str.replace(/\./g, '').replace(/,/g, '.')) || 0;
            }

            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(number);
            }

            function calculateTotals() {
                let totalIncome = 0;
                let totalDeduction = 0;

                // Sum Income
                document.querySelectorAll('.income-input').forEach(input => {
                    totalIncome += parseFormattedNumber(input.value);
                });

                // Sum Deduction
                document.querySelectorAll('.deduction-input').forEach(input => {
                    totalDeduction += parseFormattedNumber(input.value);
                });

                const netSalary = totalIncome - totalDeduction;

                displayTotalPendapatan.textContent = formatRupiah(totalIncome);
                displayTotalPotongan.textContent = formatRupiah(totalDeduction);
                displayGajiBersih.textContent = formatRupiah(netSalary);
            }

            // Attach Event Listeners
            currencyInputs.forEach(input => {
                // On input: format the value
                input.addEventListener('input', function(e) {
                    let cursorPosition = this.selectionStart;
                    let originalLength = this.value.length;

                    this.value = formatNumber(this.value);

                    // Restore cursor position logic (simple)
                    let newLength = this.value.length;
                    this.selectionEnd = cursorPosition + (newLength - originalLength);

                    calculateTotals();
                });

                // Initial format if value exists (handled by Blade value attribute, but good to ensure)
                if (input.value) {
                    input.value = formatNumber(input.value);
                }
            });

            // Handle Form Submission: Remove dots
            form.addEventListener('submit', function(e) {
                currencyInputs.forEach(input => {
                    let cleanValue = input.value.replace(/\./g, "");

                    if (cleanValue === "") {
                        input.value = "0";
                    } else {
                        input.value = cleanValue;
                    }
                });
            });

            // Initial Calculation
            calculateTotals();
        });
    </script>

    <style>
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

        .form-label {
            display: block;
        }

        .form-input {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid transparent;
            /* matches spreadsheet style */
            border-radius: 2px;
            background-color: transparent;
            font-size: 13px;
            color: #1f2937;
            text-align: right;
            transition: all 0.15s ease-in-out;
        }

        .form-input:hover {
            border-color: #d1d5db;
        }

        .form-input:focus {
            outline: none;
            border-color: #217346;
            /* Excel green */
            box-shadow: 0 0 0 1px #217346;
            background-color: #fff;
            position: relative;
            z-index: 2;
        }

        .deduction-input:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 1px #ef4444;
        }

        .spreadsheet-row:has(.form-input:focus) {
            background-color: #f0fdf4 !important;
            /* light green tint */
        }

        .spreadsheet-row:has(.deduction-input:focus) {
            background-color: #fef2f2 !important;
            /* light red tint */
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-action:hover {
            opacity: 0.9;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .btn-action-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-action-primary:hover {
            background-color: #4338ca !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
    </style>
</x-app>