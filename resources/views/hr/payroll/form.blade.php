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
                    <a href="{{ route('hr.payroll.index', ['month' => request('month'), 'year' => request('year'), 'pt_id' => request('pt_id')]) }}"
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
                <input type="hidden" name="filter_month" value="{{ request('month') }}">
                <input type="hidden" name="filter_year" value="{{ request('year') }}">
                <input type="hidden" name="filter_pt_id" value="{{ request('pt_id') }}">

                @if(isset($payslip))
                @method('PUT')
                @else
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <input type="hidden" name="period_month" value="{{ $month }}">
                <input type="hidden" name="period_year" value="{{ $year }}">
                @endif

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
                            'tunjangan_telekomunikasi' => 'Tunjangan Telekomunikasi',
                            'tunjangan_penempatan' => 'Tunjangan Penempatan',
                            'tunjangan_asuransi' => 'Tunjangan Asuransi',
                            'tunjangan_kelancaran' => 'Tunjangan Kelancaran',
                            'pendapatan_lain' => 'Pendapatan Lain',
                            'tunjangan_transportasi' => 'Tunjangan Transportasi',
                            'lembur' => 'Lembur'
                            ] as $field => $label)
                            <div>
                                <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                                <div class="input-group">
                                    <span class="input-prefix">Rp</span>
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
                            <div>
                                <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                                <div class="input-group">
                                    <span class="input-prefix text-red">Rp</span>
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

                            <div class="mb-4">
                                <label for="sisa_utang" class="form-label" style="margin-bottom: 6px; display:block;">Sisa Utang (Info)</label>
                                <div class="input-group">
                                    <span class="input-prefix">Rp</span>
                                    <input type="text" name="sisa_utang" id="sisa_utang"
                                        value="{{ old('sisa_utang', isset($payslip) ? number_format($payslip->sisa_utang, 0, ',', '.') : '') }}"
                                        class="form-input currency-input"
                                        placeholder="0">
                                </div>
                            </div>

                            <div style="margin-bottom: 20px;">
                                <label class="form-label" style="margin-bottom: 8px; display:block;">Status Publikasi</label>
                                <div style="display: flex; gap: 20px; padding: 4px 0;">
                                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #1f2937; cursor: pointer;">
                                        <input type="radio" name="status" value="DRAFT"
                                            {{ (old('status', $payslip->status ?? 'DRAFT') == 'DRAFT') ? 'checked' : '' }}
                                            style="width: 16px; height: 16px; accent-color: #4f46e5;">
                                        <span>DRAFT <span style="font-size: 11px; color: #6b7280;">(Hanya HR)</span></span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #1f2937; cursor: pointer;">
                                        <input type="radio" name="status" value="PUBLISHED"
                                            {{ (old('status', $payslip->status ?? '') == 'PUBLISHED') ? 'checked' : '' }}
                                            style="width: 16px; height: 16px; accent-color: #16a34a;">
                                        <span>PUBLISHED <span style="font-size: 11px; color: #6b7280;">(Tampil ke Karyawan)</span></span>
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn-primary-block">
                                Simpan Slip Gaji
                            </button>
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
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 4px;
            display: block;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-prefix {
            position: absolute;
            left: 12px;
            font-size: 13px;
            color: #9ca3af;
            pointer-events: none;
        }

        .input-prefix.text-red {
            color: #f87171;
        }

        .form-input {
            width: 100%;
            padding: 8px 12px 8px 36px;
            /* Space for prefix */
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            color: #1f2937;
            text-align: right;
            transition: all 0.2s;
        }

        select.form-input {
            padding-left: 12px;
            text-align: left;
        }

        .form-input:focus {
            border-color: #4f46e5;
            outline: none;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
        }

        .deduction-input:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.1);
        }

        .btn-primary-block {
            width: 100%;
            background: #4f46e5;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-primary-block:hover {
            background: #4338ca;
        }
    </style>
</x-app>