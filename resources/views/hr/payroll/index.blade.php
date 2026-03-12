<x-app title="Master Gaji Karyawan">
    <div class="card">
        <div class="card-header-simple">
            <div class="header-top-row">
                <div>
                    <h4 class="card-title-sm">Master Gaji Karyawan</h4>
                    <p class="card-subtitle-sm">Kelola data gaji karyawan bulanan (menampilkan karyawan berstatus ACTIVE).</p>
                </div>

                <div class="header-actions">
                    <span class="selected-info">
                        <span id="selected-count-badge" class="selected-count-badge">0</span>
                        <span class="selected-label">data dipilih</span>
                    </span>

                    <form id="payroll-export-form" action="{{ route('hr.payroll.export') }}" method="GET" style="display:inline-block;">
                        <input type="hidden" name="start_month" value="{{ $startMonth }}">
                        <input type="hidden" name="end_month" value="{{ $endMonth }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="pt_id" value="{{ $ptId }}">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <div id="selected_rows_container"></div>
                        <button type="submit" class="btn-action btn-action-outline-success">Unduh Data Excel</button>
                    </form>

                    <form action="{{ route('hr.payroll.import.preview') }}" method="POST" enctype="multipart/form-data" style="display:inline-block;">
                        @csrf
                        <input type="hidden" name="month" value="{{ $startMonth }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="pt_id" value="{{ $ptId }}">

                        <label for="file_upload" class="btn-action" style="cursor: pointer;">+ Import File</label>
                        <input type="file" name="file" id="file_upload" style="display: none;" onchange="this.form.submit()" accept=".xlsx,.xls,.csv,.xlsm">
                    </form>

                    @if(auth()->user()->isHrManager())
                        <a href="{{ route('hr.payroll.settings') }}" class="btn-action">
                            Akses
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="filter-wrap">
            <form action="{{ route('hr.payroll.index') }}" method="GET" class="filter-grid">
                <div>
                    <label class="filter-label" for="start_month">Start Bulan</label>
                    <select name="start_month" id="start_month" class="filter-control">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $startMonth == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month((int) $m)->locale('id')->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="filter-label" for="end_month">End Bulan</label>
                    <select name="end_month" id="end_month" class="filter-control">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $endMonth == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month((int) $m)->locale('id')->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="filter-label" for="year">Tahun</label>
                    <select name="year" id="year" class="filter-control">
                        @foreach(range(date('Y') + 1, 2023) as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="filter-label" for="pt_id">Perusahaan (PT)</label>
                    <select name="pt_id" id="pt_id" class="filter-control">
                        <option value="" {{ empty($ptId) ? 'selected' : '' }}>Semua PT</option>
                        @foreach($pts as $pt)
                            <option value="{{ $pt->id }}" {{ $ptId == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-button-row">
                    <button type="submit" class="btn-action btn-action-primary">Filter Data</button>
                    <a href="{{ route('hr.payroll.index') }}" class="btn-action">Clear</a>
                </div>
            </form>

            <form action="{{ route('hr.payroll.index') }}" method="GET" class="search-grid">
                <input type="hidden" name="start_month" value="{{ $startMonth }}">
                <input type="hidden" name="end_month" value="{{ $endMonth }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="pt_id" value="{{ $ptId }}">

                <div>
                    <label class="filter-label" for="search">Cari Nama Karyawan</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik nama karyawan..." class="filter-control">
                </div>
                <div>
                    <button type="submit" class="btn-action">Cari</button>
                </div>
            </form>

            <div style="margin-top: 8px; display: flex; justify-content: flex-start;">
                <label for="thr_only_toggle" class="thr-only-toggle" title="Tampilkan hanya kolom THR">
                    <input type="checkbox" id="thr_only_toggle">
                    <span>THR</span>
                </label>
            </div>
        </div>

        <form id="payroll-bulk-form" action="{{ route('hr.payroll.import.store') }}" method="POST">
            @csrf
            <input type="hidden" name="action" id="payroll-action-input" value="">
            <input type="hidden" name="month" value="{{ $startMonth }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="pt_id" value="{{ $ptId }}">
            <input type="hidden" name="start_month" value="{{ $startMonth }}">
            <input type="hidden" name="end_month" value="{{ $endMonth }}">
            <input type="hidden" name="thr_only_mode" id="thr_only_mode_input" value="0">

            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th rowspan="2" class="col-sticky-1 text-center" style="min-width: 48px;">
                                <input type="checkbox" id="select_all_payroll" title="Pilih semua">
                            </th>
                            <th rowspan="2" class="col-sticky-2" style="min-width: 220px;">Karyawan</th>
                            <th rowspan="2" style="min-width: 120px;">Periode</th>
                            <th rowspan="2" style="min-width: 90px;">Status</th>
                            <th colspan="16" class="text-center" id="th-group-pendapatan">Pendapatan</th>
                            <th colspan="6" class="text-center" id="th-group-pengeluaran">Pengeluaran</th>
                            <th rowspan="2" style="min-width: 130px;" id="th-total-penghasilan">Total Penghasilan</th>
                            <th rowspan="2" style="min-width: 140px;" id="th-sisa-utang">Ket / Sisa Utang</th>
                            <th rowspan="2" style="min-width: 90px;" class="text-center" id="th-aksi">Aksi</th>
                        </tr>
                        <tr>
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
                            <th style="min-width: 120px; background-color:#e5e7eb;">Total</th>

                            <th style="min-width: 110px;">BPJS TK</th>
                            <th style="min-width: 110px;">PPh 21</th>
                            <th style="min-width: 110px;">Hutang</th>
                            <th style="min-width: 110px;">BPJS Kes</th>
                            <th style="min-width: 120px;">Keterlambatan</th>
                            <th style="min-width: 120px; background-color:#e5e7eb;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrollData as $index => $data)
                            @php
                                $payslip = $data->latest_payslip;
                                $monthLabel = \Carbon\Carbon::create()->month((int) $data->month)->locale('id')->translatedFormat('F');
                            @endphp
                            <tr>
                                <td class="col-sticky-1 text-center">
                                    <input
                                        type="checkbox"
                                        class="payroll-row-checkbox"
                                        value="{{ $data->user->id }}-{{ $data->month }}-{{ $data->year }}"
                                        title="Pilih baris ini"
                                    >
                                </td>
                                <td class="col-sticky-2">
                                    <div class="fw-bold">{{ $data->user->name }}</div>
                                        <div class="text-muted">{{ $data->user->email }}</div>

                                    <input type="hidden" name="payslips[{{ $index }}][user_id]" value="{{ $data->user->id }}">
                                    <input type="hidden" name="payslips[{{ $index }}][period_month]" value="{{ $data->month }}">
                                    <input type="hidden" name="payslips[{{ $index }}][period_year]" value="{{ $data->year }}">
                                </td>
                                <td>
                                    {{ $monthLabel }} {{ $data->year }}
                                </td>
                                <td>
                                    @if($data->payslip_status === 'PUBLISHED')
                                        <span class="badge-type badge-green">PUBLISHED</span>
                                    @elseif($data->payslip_status === 'DRAFT')
                                        <span class="badge-type badge-yellow">DRAFT</span>
                                    @else
                                        <span class="badge-type badge-red">BELUM DIBUAT</span>
                                    @endif
                                </td>

                                <td><input type="text" class="form-control-sm input-income" oninput="formatInput(this)" name="payslips[{{ $index }}][gaji_pokok]" value="{{ number_format($payslip->gaji_pokok ?? 0, 2, ',', '.') }}"></td>
                                <td><input type="text" class="form-control-sm input-income" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_jabatan]" value="{{ number_format($payslip->tunjangan_jabatan ?? 0, 2, ',', '.') }}"></td>
                                <td><input type="text" class="form-control-sm input-income" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_makan]" value="{{ number_format($payslip->tunjangan_makan ?? 0, 2, ',', '.') }}"></td>
                                <td><input type="text" class="form-control-sm input-income" oninput="formatInput(this)" name="payslips[{{ $index }}][fee_marketing]" value="{{ number_format($payslip->fee_marketing ?? 0, 2, ',', '.') }}"></td>
                                <td><input type="text" class="form-control-sm input-income" oninput="formatInput(this)" name="payslips[{{ $index }}][bonus_bulanan]" value="{{ number_format($payslip->bonus_bulanan ?? 0, 2, ',', '.') }}"></td>
                                <td><input type="text" class="form-control-sm input-income" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_telekomunikasi]" value="{{ number_format($payslip->tunjangan_telekomunikasi ?? 0, 2, ',', '.') }}"></td>
                                <td><input type="text" class="form-control-sm input-income" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_lainnya]" value="{{ number_format($payslip->tunjangan_lainnya ?? 0, 2, ',', '.') }}"></td>
                                <td><input type="text" class="form-control-sm input-income" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_penempatan]" value="{{ number_format($payslip->tunjangan_penempatan ?? 0, 2, ',', '.') }}"></td>
                                <td><input type="text" class="form-control-sm input-income" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_asuransi]" value="{{ number_format($payslip->tunjangan_asuransi ?? 0, 2, ',', '.') }}"></td>
                                <td><input type="text" class="form-control-sm input-income" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_kelancaran]" value="{{ number_format($payslip->tunjangan_kelancaran ?? 0, 2, ',', '.') }}"></td>
                                <td><input type="text" class="form-control-sm input-income" oninput="formatInput(this)" name="payslips[{{ $index }}][pendapatan_lain]" value="{{ number_format($payslip->pendapatan_lain ?? 0, 2, ',', '.') }}"></td>
                                <td><input type="text" class="form-control-sm input-income" oninput="formatInput(this)" name="payslips[{{ $index }}][tunjangan_transportasi]" value="{{ number_format($payslip->tunjangan_transportasi ?? 0, 2, ',', '.') }}"></td>
                                <td><input type="text" class="form-control-sm input-income" oninput="formatInput(this)" name="payslips[{{ $index }}][lembur]" value="{{ number_format($payslip->lembur ?? 0, 2, ',', '.') }}"></td>
                                <td><input type="text" class="form-control-sm input-income" oninput="formatInput(this)" name="payslips[{{ $index }}][thr]" value="{{ number_format($payslip->thr ?? 0, 2, ',', '.') }}"></td>
                                <td><input type="text" class="form-control-sm input-income" oninput="formatInput(this)" name="payslips[{{ $index }}][bonus]" value="{{ number_format($payslip->bonus ?? 0, 2, ',', '.') }}"></td>
                                <td style="background:#f9fafb;"><span class="fw-bold text-success total-income">{{ number_format($payslip->total_pendapatan ?? 0, 0, ',', '.') }}</span></td>

                                <td><input type="text" class="form-control-sm input-deduction" oninput="formatInput(this)" name="payslips[{{ $index }}][potongan_bpjs_tk]" value="{{ number_format($payslip->potongan_bpjs_tk ?? 0, 2, ',', '.') }}"></td>
                                <td><input type="text" class="form-control-sm input-deduction" oninput="formatInput(this)" name="payslips[{{ $index }}][potongan_pph21]" value="{{ number_format($payslip->potongan_pph21 ?? 0, 2, ',', '.') }}"></td>
                                <td><input type="text" class="form-control-sm input-deduction" oninput="formatInput(this)" name="payslips[{{ $index }}][potongan_hutang]" value="{{ number_format($payslip->potongan_hutang ?? 0, 2, ',', '.') }}"></td>
                                <td><input type="text" class="form-control-sm input-deduction" oninput="formatInput(this)" name="payslips[{{ $index }}][potongan_bpjs_kes]" value="{{ number_format($payslip->potongan_bpjs_kes ?? 0, 2, ',', '.') }}"></td>
                                <td><input type="text" class="form-control-sm input-deduction" oninput="formatInput(this)" name="payslips[{{ $index }}][potongan_terlambat]" value="{{ number_format($payslip->potongan_terlambat ?? 0, 2, ',', '.') }}"></td>
                                <td style="background:#f9fafb;"><span class="fw-bold text-danger total-deduction">{{ number_format($payslip->total_potongan ?? 0, 0, ',', '.') }}</span></td>

                                <td><span class="fw-bold text-primary total-thp">{{ number_format($payslip->gaji_bersih ?? 0, 0, ',', '.') }}</span></td>
                                <td>
                                    <input
                                        type="text"
                                        class="form-control-sm"
                                        name="payslips[{{ $index }}][sisa_utang]"
                                        value="{{ $payslip->sisa_utang ?? '' }}"
                                        style="text-align:left; min-width:120px;"
                                    >
                                </td>
                                <td class="text-center">
                                    @if($payslip)
                                        <button
                                            type="button"
                                            class="btn-action btn-action-clear open-clear-confirm"
                                            data-delete-url="{{ route('hr.payroll.destroy', $payslip->id) }}"
                                            data-employee-name="{{ $data->user->name }}"
                                            data-month-label="{{ $monthLabel }} {{ $data->year }}"
                                        >
                                            Clear
                                        </button>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="33" class="empty-state">Tidak ada data karyawan ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bulk-footer-actions">
                <button type="button" id="btn-save-draft" class="btn-action">Simpan DRAFT</button>
                <button type="button" id="btn-publish-email" class="btn-action btn-action-primary">Publish & Kirim Email</button>
            </div>
        </form>
    </div>

    <x-modal id="modal-publish-warning" title="Tidak Bisa Publish" type="form">
        <div style="display:flex; align-items:flex-start; gap:12px;">
            <div style="flex-shrink:0; width:40px; height:40px; border-radius:50%; background:#fef2f2; display:flex; align-items:center; justify-content:center;">
                <svg width="20" height="20" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            </div>
            <p style="margin:0; color:#374151; line-height:1.6;">Pilih minimal <strong>satu data karyawan</strong> yang akan dipublish terlebih dahulu dengan mencentang checkbox pada tabel.</p>
        </div>
        <div style="margin-top:16px; display:flex; justify-content:flex-end;">
            <button type="button" data-modal-close="true" class="modal-btn-primary">Mengerti</button>
        </div>
    </x-modal>

    <x-modal id="modal-publish-loading" title="Memproses Publish & Kirim Email" type="form">
        <div style="text-align:center; padding: 20px 0;">
            <div class="payroll-loading-spinner" style="width:40px; height:40px; border-width:3px; margin:0 auto 16px;"></div>
            <p style="margin:0; color:#374151; font-weight:600;">Data sedang dipublish ke queue email...</p>
            <p style="margin:8px 0 0; color:#6b7280; font-size:0.85rem;">Email akan terkirim bertahap dan estimasi selesai semua sekitar <strong>45 menit</strong> untuk pengiriman massal.<br>Modal ini boleh ditutup, tetapi sebaiknya jangan tutup tab atau pindah halaman sebelum submit selesai.</p>
            <div style="margin-top:16px; display:flex; justify-content:center;">
                <button type="button" data-modal-close="true" class="modal-btn-light">Tutup Info</button>
            </div>
        </div>
    </x-modal>

    <x-modal id="modal-publish-confirm" title="Konfirmasi Publish & Kirim Email" type="form">
        <p style="margin:0; color:#374151;">Anda akan mempublish dan menjadwalkan email ke <strong id="publish-confirm-count">0</strong> karyawan yang dipilih. Estimasi pengiriman selesai sekitar <strong>45 menit</strong>. Lanjutkan?</p>
        <div style="margin-top: 16px; display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" data-modal-close="true" class="modal-btn-light">Batal</button>
            <button type="button" id="confirm-publish-submit" class="modal-btn-primary">Ya, Publish & Kirim</button>
        </div>
    </x-modal>

    <x-modal id="modal-clear-payslip-confirm" title="Konfirmasi Hapus Data Gaji" type="form">
        <p id="clear-payslip-confirm-text" style="margin:0; color:#374151;"></p>
        <div style="margin-top: 16px; display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" data-modal-close="true" class="modal-btn-light">Batal</button>
            <button type="button" id="confirm-clear-payslip-submit" class="modal-btn-danger">Ya, Hapus Data</button>
        </div>
    </x-modal>

    <form id="clear-payslip-form" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="filter_start_month" value="{{ $startMonth }}">
        <input type="hidden" name="filter_end_month" value="{{ $endMonth }}">
        <input type="hidden" name="filter_year" value="{{ $year }}">
        <input type="hidden" name="filter_pt_id" value="{{ $ptId }}">
        <input type="hidden" name="search" value="{{ request('search') }}">
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (sessionStorage.getItem('payroll_refresh_after_back') === '1') {
                sessionStorage.removeItem('payroll_refresh_after_back');
                window.location.reload();
                return;
            }

            const exportForm = document.getElementById('payroll-export-form');
            const thrOnlyToggle = document.getElementById('thr_only_toggle');
            const selectAllCheckbox = document.getElementById('select_all_payroll');
            const selectedRowsContainer = document.getElementById('selected_rows_container');
            const selectedCountBadge = document.getElementById('selected-count-badge');
            const clearPayslipForm = document.getElementById('clear-payslip-form');
            const clearPayslipText = document.getElementById('clear-payslip-confirm-text');
            const confirmClearPayslipSubmitButton = document.getElementById('confirm-clear-payslip-submit');
            const bulkForm = document.getElementById('payroll-bulk-form');
            const bulkActionInput = document.getElementById('payroll-action-input');
            const thrOnlyModeInput = document.getElementById('thr_only_mode_input');
            const btnSaveDraft = document.getElementById('btn-save-draft');
            const btnPublishEmail = document.getElementById('btn-publish-email');
            const confirmPublishSubmitButton = document.getElementById('confirm-publish-submit');
            const publishConfirmCount = document.getElementById('publish-confirm-count');

            const clearPayslipConfirmModalId = 'modal-clear-payslip-confirm';
            const publishConfirmModalId = 'modal-publish-confirm';
            const publishLoadingModalId = 'modal-publish-loading';
            const publishWarningModalId = 'modal-publish-warning';
            const groupPendapatanHeader = document.getElementById('th-group-pendapatan');
            const groupPengeluaranHeader = document.getElementById('th-group-pengeluaran');
            const totalPenghasilanHeader = document.getElementById('th-total-penghasilan');
            const sisaUtangHeader = document.getElementById('th-sisa-utang');
            const aksiHeader = document.getElementById('th-aksi');
            const secondHeaderRow = document.querySelector('.custom-table thead tr:nth-child(2)');
            const payrollTable = document.querySelector('.custom-table');
            const stickyKaryawanColumns = document.querySelectorAll('.col-sticky-2');

            let pendingClearPayslipUrl = null;
            let isSubmitting = false;

            const toggleModalById = (id, show) => {
                const modal = document.getElementById(id);
                if (!modal) return;
                modal.style.display = show ? 'flex' : 'none';
                document.body.style.overflow = show ? 'hidden' : '';
            };

            const getRowCheckboxes = () => Array.from(document.querySelectorAll('.payroll-row-checkbox'));

            const toggleThrOnlyMode = (enabled) => {
                // Keep: checkbox, karyawan, periode, status, THR, aksi
                const visibleColumnIndexes = [1, 2, 3, 4, 18, 29];

                const secondHeaderColumns = document.querySelectorAll('.custom-table thead tr:nth-child(2) th');
                secondHeaderColumns.forEach((th, index) => {
                    const currentIndex = index + 1;
                    const isThrColumn = currentIndex === 14;
                    th.style.display = enabled && !isThrColumn ? 'none' : '';
                });

                if (groupPendapatanHeader) {
                    groupPendapatanHeader.colSpan = enabled ? 1 : 16;
                    groupPendapatanHeader.textContent = enabled ? 'THR' : 'Pendapatan';
                    if (enabled) {
                        groupPendapatanHeader.setAttribute('rowspan', '2');
                    } else {
                        groupPendapatanHeader.removeAttribute('rowspan');
                    }
                }
                if (groupPengeluaranHeader) {
                    groupPengeluaranHeader.style.display = enabled ? 'none' : '';
                }
                if (totalPenghasilanHeader) {
                    totalPenghasilanHeader.style.display = enabled ? 'none' : '';
                }
                if (sisaUtangHeader) {
                    sisaUtangHeader.style.display = enabled ? 'none' : '';
                }
                if (aksiHeader) {
                    aksiHeader.style.display = '';
                }

                if (secondHeaderRow) {
                    secondHeaderRow.style.display = enabled ? 'none' : '';
                }

                if (payrollTable) {
                    payrollTable.style.minWidth = enabled ? '860px' : '2600px';
                    payrollTable.style.tableLayout = enabled ? 'fixed' : '';
                }

                stickyKaryawanColumns.forEach((column) => {
                    column.style.minWidth = enabled ? '220px' : '';
                    column.style.width = enabled ? '220px' : '';
                    column.style.maxWidth = enabled ? '220px' : '';
                });

                // Keep THR header compact and centered in THR-only mode.
                if (groupPendapatanHeader) {
                    groupPendapatanHeader.style.minWidth = enabled ? '170px' : '';
                    groupPendapatanHeader.style.width = enabled ? '170px' : '';
                    groupPendapatanHeader.style.maxWidth = enabled ? '170px' : '';
                    groupPendapatanHeader.style.textAlign = enabled ? 'center' : '';
                    groupPendapatanHeader.style.paddingLeft = enabled ? '8px' : '';
                    groupPendapatanHeader.style.paddingRight = enabled ? '8px' : '';
                }

                document.querySelectorAll('.custom-table tbody tr').forEach((row) => {
                    const emptyStateCell = row.querySelector('td.empty-state');
                    if (emptyStateCell) {
                        emptyStateCell.colSpan = enabled ? 6 : 33;
                        return;
                    }

                    row.querySelectorAll('td').forEach((cell, index) => {
                        const currentIndex = index + 1;
                        const shouldShow = !enabled || visibleColumnIndexes.includes(currentIndex);
                        cell.style.display = shouldShow ? '' : 'none';

                        if (!enabled) {
                            cell.style.minWidth = '';
                            cell.style.width = '';
                            cell.style.maxWidth = '';
                            return;
                        }

                        // Compact widths for THR-only visible columns.
                        if (currentIndex === 2) {
                            cell.style.minWidth = '220px';
                            cell.style.width = '220px';
                            cell.style.maxWidth = '220px';
                        } else if (currentIndex === 3) {
                            cell.style.minWidth = '140px';
                            cell.style.width = '140px';
                            cell.style.maxWidth = '140px';
                        } else if (currentIndex === 4) {
                            cell.style.minWidth = '110px';
                            cell.style.width = '110px';
                            cell.style.maxWidth = '110px';
                        } else if (currentIndex === 18) {
                            // Wide enough for 2-digit million values with decimals.
                            cell.style.minWidth = '170px';
                            cell.style.width = '170px';
                            cell.style.maxWidth = '170px';
                        } else if (currentIndex === 29) {
                            cell.style.minWidth = '90px';
                            cell.style.width = '90px';
                            cell.style.maxWidth = '90px';
                        }
                    });
                });
            };

            const appendSelectedRowsToContainer = (container, checkedRows) => {
                if (!container) return;
                container.innerHTML = '';
                checkedRows.forEach((checkbox) => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'selected_rows[]';
                    hiddenInput.value = checkbox.value;
                    container.appendChild(hiddenInput);
                });
            };

            const syncSelectAllState = () => {
                const rowCheckboxes = getRowCheckboxes();
                const checkedCount = rowCheckboxes.filter((checkbox) => checkbox.checked).length;

                if (selectedCountBadge) {
                    selectedCountBadge.textContent = String(checkedCount);
                    selectedCountBadge.style.background = checkedCount > 0 ? '#eef2ff' : '#f3f4f6';
                    selectedCountBadge.style.borderColor = checkedCount > 0 ? '#c7d2fe' : '#d1d5db';
                    selectedCountBadge.style.color = checkedCount > 0 ? '#3730a3' : '#1f2937';
                }

                if (!selectAllCheckbox) return;
                if (rowCheckboxes.length === 0) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                    return;
                }

                selectAllCheckbox.checked = checkedCount === rowCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
            };

            const parseCurrency = (value) => {
                if (!value) return 0;
                return parseFloat(String(value).replace(/\./g, '').replace(/,/g, '.')) || 0;
            };

            const formatIdNumber = (num) => {
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(num);
            };

            const updateRowTotals = (row) => {
                let totalIncome = 0;
                let totalDeduction = 0;

                row.querySelectorAll('.input-income').forEach((input) => {
                    totalIncome += parseCurrency(input.value);
                });

                row.querySelectorAll('.input-deduction').forEach((input) => {
                    totalDeduction += parseCurrency(input.value);
                });

                const net = totalIncome - totalDeduction;

                const totalIncomeNode = row.querySelector('.total-income');
                const totalDeductionNode = row.querySelector('.total-deduction');
                const totalThpNode = row.querySelector('.total-thp');

                if (totalIncomeNode) totalIncomeNode.textContent = formatIdNumber(totalIncome);
                if (totalDeductionNode) totalDeductionNode.textContent = formatIdNumber(totalDeduction);
                if (totalThpNode) totalThpNode.textContent = formatIdNumber(net);
            };

            window.formatInput = function(input) {
                let value = input.value.replace(/[^0-9,]/g, '');
                const parts = value.split(',');
                let integerPart = parts[0];
                const decimalPart = parts.length > 1 ? ',' + parts[1] : '';

                if (integerPart.length > 1 && integerPart.startsWith('0')) {
                    integerPart = integerPart.replace(/^0+/, '') || '0';
                }

                integerPart = integerPart.replace(/\./g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                input.value = integerPart + decimalPart;

                const row = input.closest('tr');
                if (row) {
                    updateRowTotals(row);
                }
            };

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    getRowCheckboxes().forEach((checkbox) => {
                        checkbox.checked = this.checked;
                    });
                    syncSelectAllState();
                });
            }

            if (thrOnlyToggle) {
                thrOnlyToggle.addEventListener('change', function() {
                    toggleThrOnlyMode(this.checked);
                    if (thrOnlyModeInput) {
                        thrOnlyModeInput.value = this.checked ? '1' : '0';
                    }
                });
            }

            getRowCheckboxes().forEach((checkbox) => {
                checkbox.addEventListener('change', syncSelectAllState);
            });

            if (exportForm) {
                exportForm.addEventListener('submit', function(event) {
                    const checkedRows = getRowCheckboxes().filter((checkbox) => checkbox.checked);
                    if (checkedRows.length === 0) {
                        event.preventDefault();
                        alert('Pilih minimal satu data karyawan yang akan diunduh.');
                        return;
                    }

                    appendSelectedRowsToContainer(selectedRowsContainer, checkedRows);
                });
            }

            document.querySelectorAll('.open-clear-confirm').forEach((button) => {
                button.addEventListener('click', function() {
                    pendingClearPayslipUrl = this.dataset.deleteUrl;
                    const employeeName = this.dataset.employeeName || 'Karyawan';
                    const monthLabel = this.dataset.monthLabel || '-';

                    if (clearPayslipText) {
                        clearPayslipText.textContent = `Data gaji ${employeeName} untuk periode ${monthLabel} akan dihapus. Yakin ingin melanjutkan?`;
                    }

                    toggleModalById(clearPayslipConfirmModalId, true);
                });
            });

            if (confirmClearPayslipSubmitButton && clearPayslipForm) {
                confirmClearPayslipSubmitButton.addEventListener('click', function() {
                    if (!pendingClearPayslipUrl) {
                        toggleModalById(clearPayslipConfirmModalId, false);
                        return;
                    }

                    clearPayslipForm.action = pendingClearPayslipUrl;
                    this.disabled = true;
                    clearPayslipForm.submit();
                });
            }

            if (btnSaveDraft && bulkForm) {
                btnSaveDraft.addEventListener('click', function() {
                    if (isSubmitting) return;
                    bulkActionInput.value = 'draft';
                    isSubmitting = true;
                    bulkForm.submit();
                });
            }

            if (btnPublishEmail && bulkForm) {
                btnPublishEmail.addEventListener('click', function() {
                    if (isSubmitting) return;

                    const checkedRows = getRowCheckboxes().filter(cb => cb.checked);
                    if (checkedRows.length === 0) {
                        toggleModalById(publishWarningModalId, true);
                        return;
                    }

                    if (publishConfirmCount) {
                        publishConfirmCount.textContent = String(checkedRows.length);
                    }
                    toggleModalById(publishConfirmModalId, true);
                });
            }

            if (confirmPublishSubmitButton && bulkForm) {
                confirmPublishSubmitButton.addEventListener('click', function() {
                    if (isSubmitting) return;
                    isSubmitting = true;
                    this.disabled = true;

                    // Tutup modal confirm, buka modal loading
                    toggleModalById(publishConfirmModalId, false);
                    toggleModalById(publishLoadingModalId, true);

                    // Hapus hidden inputs lama
                    bulkForm.querySelectorAll('input[name="selected_rows[]"]').forEach(el => el.remove());

                    // Tambahkan selected_rows[] dari checkbox yang dicek
                    const checkedRows = getRowCheckboxes().filter(cb => cb.checked);
                    checkedRows.forEach(cb => {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'selected_rows[]';
                        hidden.value = cb.value;
                        bulkForm.appendChild(hidden);
                    });

                    bulkActionInput.value = 'publish';
                    if (thrOnlyModeInput && thrOnlyToggle) {
                        thrOnlyModeInput.value = thrOnlyToggle.checked ? '1' : '0';
                    }
                    bulkForm.submit();
                });
            }

            document.querySelectorAll('[data-modal-close="true"]').forEach((button) => {
                button.addEventListener('click', function() {
                    const modal = this.closest('[id^="modal-"]');
                    if (!modal) return;
                    modal.style.display = 'none';
                    document.body.style.overflow = '';

                    if (modal.id === clearPayslipConfirmModalId) {
                        pendingClearPayslipUrl = null;
                        if (confirmClearPayslipSubmitButton) {
                            confirmClearPayslipSubmitButton.disabled = false;
                        }
                    }

                    if (modal.id === publishConfirmModalId) {
                        if (confirmPublishSubmitButton) {
                            confirmPublishSubmitButton.disabled = false;
                        }
                    }
                });
            });

            document.querySelectorAll('.custom-table tbody tr').forEach((row) => {
                updateRowTotals(row);
            });

            syncSelectAllState();
            toggleThrOnlyMode(false);
            if (thrOnlyModeInput) {
                thrOnlyModeInput.value = '0';
            }
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

        .header-top-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .header-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .thr-only-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
            color: #374151;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            user-select: none;
        }

        .thr-only-toggle input {
            width: 14px;
            height: 14px;
            cursor: pointer;
        }

        .selected-info {
            display: inline-flex;
            align-items: center;
            margin-right: 2px;
        }

        .selected-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 26px;
            height: 24px;
            padding: 0 8px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            color: #1f2937;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
        }

        .selected-label {
            margin-left: 6px;
            font-size: 12px;
            color: #4b5563;
            font-weight: 500;
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

        .filter-wrap {
            padding: 16px;
            background: #f9fafb;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            align-items: end;
        }

        .search-grid {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: end;
            border-top: 1px solid #e5e7eb;
            padding-top: 14px;
        }

        .filter-label {
            display: block;
            font-size: 12px;
            margin-bottom: 4px;
            color: #4b5563;
            font-weight: 600;
        }

        .filter-control {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 7px 8px;
            font-size: 13px;
            color: #1f2937;
            background: #fff;
        }

        .filter-button-row {
            display: flex;
            gap: 8px;
        }

        .table-wrapper {
            width: 100%;
            overflow: auto;
            max-height: calc(100vh - 330px);
            min-height: 340px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: #fff;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 2600px;
        }

        .custom-table th {
            background: #f9fafb;
            padding: 9px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 20;
            box-shadow: inset 0 -1px 0 #e5e7eb;
        }

        .custom-table thead tr:nth-child(2) th {
            top: 34px;
            z-index: 19;
        }

        .custom-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
            color: #1f2937;
            vertical-align: middle;
            background: #fff;
        }

        .custom-table tr:hover td {
            background: #fcfcfd;
        }

        .col-sticky-1 {
            position: sticky;
            left: 0;
            z-index: 45;
            background: #f9fafb;
            border-right: 1px solid #e5e7eb;
            width: 52px;
            min-width: 52px;
            max-width: 52px;
        }

        .col-sticky-2 {
            position: sticky;
            left: 52px;
            z-index: 70;
            background: #f9fafb;
            border-right: 1px solid #e5e7eb;
            box-shadow: 2px 0 0 #e5e7eb;
        }

        td.col-sticky-1,
        td.col-sticky-2 {
            background: #fff;
        }

        td.col-sticky-1 {
            z-index: 40;
        }

        td.col-sticky-2 {
            z-index: 65;
        }

        thead th.col-sticky-1 {
            z-index: 80;
        }

        thead th.col-sticky-2 {
            z-index: 90;
        }

        .fw-bold {
            font-weight: 600;
            color: #111827;
        }

        .text-muted {
            color: #6b7280;
            font-size: 11px;
        }

        .form-control-sm {
            width: 100%;
            padding: 6px 8px;
            font-size: 13px;
            border: 1px solid transparent;
            border-radius: 2px;
            background-color: transparent;
            transition: all 0.15s ease-in-out;
            color: #1f2937;
            text-align: right;
        }

        .form-control-sm:hover {
            border-color: #d1d5db;
        }

        .form-control-sm:focus {
            outline: none;
            border-color: #217346;
            box-shadow: 0 0 0 1px #217346;
            background-color: #fff;
        }

        .custom-table td:has(.form-control-sm:focus) {
            background-color: #f0fdf4 !important;
        }

        .badge-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }

        .btn-action {
            padding: 6px 14px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            white-space: nowrap;
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

        .btn-action-outline-success {
            color: #16a34a;
            border-color: #16a34a;
        }

        .btn-action-outline-success:hover {
            background: #f0fdf4;
            border-color: #16a34a;
        }

        .btn-action-clear {
            border-color: #fecaca;
            color: #b91c1c;
            background: #fff1f2;
        }

        .btn-action-clear:hover {
            background: #ffe4e6;
            border-color: #fda4af;
        }

        .bulk-footer-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            padding: 14px 16px 16px;
            border-top: 1px solid #f3f4f6;
            background: #fff;
        }

        .empty-state {
            padding: 30px 20px;
            text-align: center;
            color: #9ca3af;
            font-style: italic;
        }

        .text-center {
            text-align: center;
        }

        .payroll-loading-spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #e5e7eb;
            border-top-color: #1e4a8d;
            border-radius: 9999px;
            display: inline-block;
            animation: payroll-spin 0.8s linear infinite;
        }

        .modal-btn-light {
            padding: 9px 16px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
        }

        .modal-btn-primary {
            padding: 9px 20px;
            border-radius: 8px;
            border: 1px solid transparent;
            background: #1e4a8d;
            color: #fff;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
        }

        .modal-btn-danger {
            padding: 9px 20px;
            border-radius: 8px;
            border: 1px solid transparent;
            background: #dc2626;
            color: #fff;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
        }

        @keyframes payroll-spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .header-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .search-grid {
                grid-template-columns: 1fr;
            }

            .bulk-footer-actions {
                justify-content: stretch;
            }

            .bulk-footer-actions .btn-action {
                flex: 1;
            }
        }
    </style>
</x-app>
