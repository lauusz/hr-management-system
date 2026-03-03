<x-app title="Master Gaji Karyawan">
    <div class="card">
        <div class="card-header-simple">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <h4 class="card-title-sm">
                        Master Gaji Karyawan
                    </h4>
                    <p class="card-subtitle-sm">
                        Kelola data Gaji karyawan bulanan.
                    </p>
                </div>
                <div style="text-align:right;">
                    <span style="display: inline-flex; align-items: center; margin-right: 8px; vertical-align: middle;">
                        <span id="selected-count-badge" style="display: inline-flex; align-items: center; justify-content: center; min-width: 26px; height: 24px; padding: 0 8px; border-radius: 9999px; font-size: 11px; font-weight: 700; color: #1f2937; background: #f3f4f6; border: 1px solid #d1d5db;">
                            0
                        </span>
                        <span style="margin-left: 6px; font-size: 12px; color: #4b5563; font-weight: 500;">data dipilih</span>
                    </span>
                    <form id="payroll-send-email-form" action="{{ route('hr.payroll.send-email') }}" method="POST" style="display:inline-block; margin-right: 8px;">
                        @csrf
                        <input type="hidden" name="start_month" value="{{ $startMonth }}">
                        <input type="hidden" name="end_month" value="{{ $endMonth }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="pt_id" value="{{ $ptId }}">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <div id="selected_rows_email_container"></div>

                        <button type="button" id="open-send-email-confirm" class="btn-action" style="padding: 6px 16px; font-size: 12px; cursor: pointer;">
                            Kirim Email
                        </button>
                    </form>
                    <form id="payroll-export-form" action="{{ route('hr.payroll.export') }}" method="GET" style="display:inline-block; margin-right: 8px;">
                        <input type="hidden" name="start_month" value="{{ $startMonth }}">
                        <input type="hidden" name="end_month" value="{{ $endMonth }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="pt_id" value="{{ $ptId }}">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <div id="selected_rows_container"></div>

                        <button type="submit" class="btn-action-outline-success" style="padding: 6px 16px; font-size: 12px; cursor: pointer; color: #16a34a; border: 1px solid #16a34a; background: transparent; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='transparent'">
                            Unduh Data Excel
                        </button>
                    </form>
                    <form action="{{ route('hr.payroll.import.preview') }}" method="POST" enctype="multipart/form-data" style="display:inline-block; margin-right: 8px;">
                        @csrf
                        {{-- Kirim filter saat ini agar bisa diproses/redirect balik dengan benar --}}
                        <input type="hidden" name="month" value="{{ $startMonth }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="pt_id" value="{{ $ptId }}">

                        <label for="file_upload" class="btn-action" style="padding: 6px 16px; font-size: 12px; cursor: pointer;">
                            + Import File
                        </label>
                        <input type="file" name="file" id="file_upload" style="display: none;" onchange="this.form.submit()" accept=".xlsx,.xls,.csv">
                    </form>
                    @if(auth()->user()->isHrManager())
                    <a href="{{ route('hr.payroll.settings') }}" style="padding: 5px 14px; font-size: 13px; font-weight: 500; cursor: pointer; color: #4b5563; border: 1px solid #d1d5db; background: transparent; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; margin-left: 2px; text-decoration: none;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                        Akses
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div style="padding: 16px; background: #f9fafb; border-bottom: 1px solid #f3f4f6;">
            <form action="{{ route('hr.payroll.index') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; align-items: end;">
                <div>
                    <label for="start_month" style="display: block; font-size: 12px; margin-bottom: 4px; color: #4b5563; font-weight: 600;">Start Bulan</label>
                    <select name="start_month" id="start_month" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 6px; font-size: 13px;">
                        @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $startMonth == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month((int) $m)->locale('id')->translatedFormat('F') }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="end_month" style="display: block; font-size: 12px; margin-bottom: 4px; color: #4b5563; font-weight: 600;">End Bulan</label>
                    <select name="end_month" id="end_month" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 6px; font-size: 13px;">
                        @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $endMonth == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month((int) $m)->locale('id')->translatedFormat('F') }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="year" style="display: block; font-size: 12px; margin-bottom: 4px; color: #4b5563; font-weight: 600;">Tahun</label>
                    <select name="year" id="year" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 6px; font-size: 13px;">
                        @foreach(range(date('Y') + 1, 2023) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="pt_id" style="display: block; font-size: 12px; margin-bottom: 4px; color: #4b5563; font-weight: 600;">Perusahaan (PT)</label>
                    <select name="pt_id" id="pt_id" style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 6px; font-size: 13px;">
                        <option value="" {{ empty($ptId) ? 'selected' : '' }}>Semua PT</option>
                        @foreach($pts as $pt)
                        <option value="{{ $pt->id }}" {{ $ptId == $pt->id ? 'selected' : '' }}>
                            {{ $pt->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn-action btn-action-primary" style="flex: 1; justify-content: center; padding: 7px;">
                        Filter Data
                    </button>
                    <a href="{{ route('hr.payroll.index') }}" class="btn-action" style="flex: 1; justify-content: center; padding: 7px; background-color: #f9fafb;">
                        Clear
                    </a>
                </div>
            </form>

            <hr style="margin: 16px 0; border: 0; border-top: 1px solid #e5e7eb;">

            <form action="{{ route('hr.payroll.index') }}" method="GET" style="display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: end;">
                <input type="hidden" name="start_month" value="{{ $startMonth }}">
                <input type="hidden" name="end_month" value="{{ $endMonth }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="pt_id" value="{{ $ptId }}">

                <div>
                    <label for="search" style="display: block; font-size: 12px; margin-bottom: 4px; color: #4b5563; font-weight: 600;">Cari Nama Karyawan</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik nama karyawan..." style="width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 6px; font-size: 13px;">
                </div>
                <div>
                    <button type="submit" class="btn-action" style="padding: 7px 20px; font-size: 12px;">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="select_all_payroll" title="Pilih semua">
                        </th>
                        <th style="min-width: 200px;">Karyawan</th>
                        <th style="min-width: 150px;">Jabatan & Divisi</th>
                        <th style="min-width: 100px;">Bulan</th>
                        <th style="min-width: 100px;">Status</th>
                        <th style="text-align: right; width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrollData as $data)
                    <tr>
                        <td style="text-align: center;">
                            <input type="checkbox" class="payroll-row-checkbox" value="{{ $data->user->id }}-{{ $data->month }}-{{ $data->year }}" title="Pilih baris ini">
                        </td>
                        <td>
                            <div class="employee-info">
                                <div>
                                    <div class="fw-bold" style="font-size: 13px;">{{ $data->user->name }}</div>
                                    <div class="text-muted">{{ $data->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 12px; color: #1f2937;">{{ $data->user->position->name ?? $data->user->profile->jabatan ?? '-' }}</div>
                            <div style="font-size: 11px; color: #6b7280;">{{ $data->user->division->name ?? '-' }}</div>
                        </td>
                        <td>
                            {{ \Carbon\Carbon::create()->month((int) $data->month)->locale('id')->translatedFormat('F') }} {{ $data->year }}
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
                        <td style="text-align: right;">
                            @if($data->latest_payslip)
                            <div class="action-buttons-row">
                                <a href="{{ route('hr.payroll.edit', [
                                      'payslip' => $data->latest_payslip->id,
                                    'filter_start_month' => $startMonth,
                                    'filter_end_month' => $endMonth,
                                    'filter_year' => $year,
                                    'filter_pt_id' => $ptId
                                ]) }}" class="btn-action btn-action-edit">
                                    Edit
                                </a>
                                <button
                                    type="button"
                                    class="btn-action btn-action-clear open-clear-confirm"
                                    data-delete-url="{{ route('hr.payroll.destroy', $data->latest_payslip->id) }}"
                                    data-employee-name="{{ $data->user->name }}"
                                    data-month-label="{{ \Carbon\Carbon::create()->month((int) $data->month)->locale('id')->translatedFormat('F') }} {{ $data->year }}">
                                    Clear
                                </button>
                            </div>
                            @else
                            <a href="{{ route('hr.payroll.create', ['user_id' => $data->user->id, 'month' => $data->month, 'year' => $data->year, 'pt_id' => $ptId, 'filter_start_month' => $startMonth, 'filter_end_month' => $endMonth, 'filter_year' => $year, 'filter_pt_id' => $ptId]) }}" class="btn-action btn-action-primary">
                                Input
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            Tidak ada data karyawan ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-modal
        id="modal-send-email-confirm"
        title="Konfirmasi Kirim Email"
        type="form">
        <p style="margin:0; color:#374151;">
            Kirim email slip gaji ke semua data yang dicentang? Status DRAFT akan diubah menjadi PUBLISHED.
        </p>

        <div style="margin-top: 16px; display:flex; justify-content:flex-end; gap:10px;">
            <button
                type="button"
                data-modal-close="true"
                style="padding:9px 16px; border-radius:8px; border:1px solid #d1d5db; background:#fff; color:#374151; font-size:0.9rem; font-weight:600; cursor:pointer;">
                Batal
            </button>
            <button
                type="button"
                id="confirm-send-email-submit"
                style="padding:9px 20px; border-radius:8px; border:1px solid transparent; background:#1e4a8d; color:#fff; font-size:0.9rem; font-weight:600; cursor:pointer;">
                Ya, Kirim Email
            </button>
        </div>
    </x-modal>

    <x-modal
        id="modal-send-email-empty"
        title="Data Belum Dipilih"
        type="info"
        cancelLabel="Tutup">
        <p style="margin:0; color:#374151;">
            Pilih minimal satu data karyawan yang akan dikirim email.
        </p>
    </x-modal>

    <x-modal
        id="modal-send-email-loading"
        title="Mengirim Email"
        type="form">
        <div style="display:flex; align-items:center; gap:12px;">
            <span class="payroll-loading-spinner" aria-hidden="true"></span>
            <div style="color:#374151;">Proses pengiriman email sedang berjalan, mohon tunggu...</div>
        </div>
    </x-modal>

    <x-modal
        id="modal-clear-payslip-confirm"
        title="Konfirmasi Hapus Data Gaji"
        type="form">
        <p id="clear-payslip-confirm-text" style="margin:0; color:#374151;"></p>

        <div style="margin-top: 16px; display:flex; justify-content:flex-end; gap:10px;">
            <button
                type="button"
                data-modal-close="true"
                style="padding:9px 16px; border-radius:8px; border:1px solid #d1d5db; background:#fff; color:#374151; font-size:0.9rem; font-weight:600; cursor:pointer;">
                Batal
            </button>
            <button
                type="button"
                id="confirm-clear-payslip-submit"
                style="padding:9px 20px; border-radius:8px; border:1px solid transparent; background:#dc2626; color:#fff; font-size:0.9rem; font-weight:600; cursor:pointer;">
                Ya, Hapus Data
            </button>
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
            const exportForm = document.getElementById('payroll-export-form');
            const sendEmailForm = document.getElementById('payroll-send-email-form');
            const sendEmailTriggerButton = document.getElementById('open-send-email-confirm');
            const confirmSendEmailSubmitButton = document.getElementById('confirm-send-email-submit');
            const selectAllCheckbox = document.getElementById('select_all_payroll');
            const selectedRowsContainer = document.getElementById('selected_rows_container');
            const selectedRowsEmailContainer = document.getElementById('selected_rows_email_container');
            const selectedCountBadge = document.getElementById('selected-count-badge');
            const sendEmailConfirmModalId = 'modal-send-email-confirm';
            const sendEmailEmptyModalId = 'modal-send-email-empty';
            const sendEmailLoadingModalId = 'modal-send-email-loading';
            const clearPayslipConfirmModalId = 'modal-clear-payslip-confirm';
            const clearPayslipForm = document.getElementById('clear-payslip-form');
            const clearPayslipText = document.getElementById('clear-payslip-confirm-text');
            const confirmClearPayslipSubmitButton = document.getElementById('confirm-clear-payslip-submit');
            let pendingClearPayslipUrl = null;

            const toggleModalById = (id, show) => {
                const modal = document.getElementById(id);
                if (!modal) {
                    return;
                }

                modal.style.display = show ? 'flex' : 'none';
                document.body.style.overflow = show ? 'hidden' : '';
            };

            const closeSendEmailConfirmModal = () => toggleModalById(sendEmailConfirmModalId, false);
            const openSendEmailConfirmModal = () => toggleModalById(sendEmailConfirmModalId, true);
            const openSendEmailEmptyModal = () => toggleModalById(sendEmailEmptyModalId, true);
            const openSendEmailLoadingModal = () => toggleModalById(sendEmailLoadingModalId, true);
            const closeClearPayslipConfirmModal = () => toggleModalById(clearPayslipConfirmModalId, false);
            const openClearPayslipConfirmModal = () => toggleModalById(clearPayslipConfirmModalId, true);

            const getRowCheckboxes = () => Array.from(document.querySelectorAll('.payroll-row-checkbox'));

            const syncSelectAllState = () => {
                const rowCheckboxes = getRowCheckboxes();
                const checkedCount = rowCheckboxes.filter((checkbox) => checkbox.checked).length;

                if (!selectAllCheckbox) return;

                if (selectedCountBadge) {
                    selectedCountBadge.textContent = String(checkedCount);
                    selectedCountBadge.style.background = checkedCount > 0 ? '#eef2ff' : '#f3f4f6';
                    selectedCountBadge.style.borderColor = checkedCount > 0 ? '#c7d2fe' : '#d1d5db';
                    selectedCountBadge.style.color = checkedCount > 0 ? '#3730a3' : '#1f2937';
                }

                if (rowCheckboxes.length === 0) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                    return;
                }

                selectAllCheckbox.checked = checkedCount === rowCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
            };

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    getRowCheckboxes().forEach((checkbox) => {
                        checkbox.checked = this.checked;
                    });
                    syncSelectAllState();
                });
            }

            getRowCheckboxes().forEach((checkbox) => {
                checkbox.addEventListener('change', syncSelectAllState);
            });

            const appendSelectedRowsToContainer = (container, checkedRows) => {
                if (!container) {
                    return;
                }

                container.innerHTML = '';
                checkedRows.forEach((checkbox) => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'selected_rows[]';
                    hiddenInput.value = checkbox.value;
                    container.appendChild(hiddenInput);
                });
            };

            const bindSelectedRowsSubmit = (form, container, emptyMessage, confirmMessage = null) => {
                if (!form) {
                    return;
                }

                form.addEventListener('submit', function(event) {
                    const checkedRows = getRowCheckboxes().filter((checkbox) => checkbox.checked);

                    if (checkedRows.length === 0) {
                        event.preventDefault();
                        alert(emptyMessage);
                        return;
                    }

                    if (confirmMessage && !confirm(confirmMessage)) {
                        event.preventDefault();
                        return;
                    }

                    appendSelectedRowsToContainer(container, checkedRows);
                });
            };

            bindSelectedRowsSubmit(
                exportForm,
                selectedRowsContainer,
                'Pilih minimal satu data karyawan yang akan diunduh.'
            );

            if (sendEmailTriggerButton) {
                sendEmailTriggerButton.addEventListener('click', function() {
                    const checkedRows = getRowCheckboxes().filter((checkbox) => checkbox.checked);

                    if (checkedRows.length === 0) {
                        openSendEmailEmptyModal();
                        return;
                    }

                    appendSelectedRowsToContainer(selectedRowsEmailContainer, checkedRows);
                    openSendEmailConfirmModal();
                });
            }

            if (confirmSendEmailSubmitButton && sendEmailForm) {
                confirmSendEmailSubmitButton.addEventListener('click', function() {
                    closeSendEmailConfirmModal();
                    openSendEmailLoadingModal();
                    sendEmailForm.submit();
                });
            }

            document.querySelectorAll('.open-clear-confirm').forEach((button) => {
                button.addEventListener('click', function() {
                    const deleteUrl = this.dataset.deleteUrl;
                    const employeeName = this.dataset.employeeName || 'Karyawan';
                    const monthLabel = this.dataset.monthLabel || '-';

                    pendingClearPayslipUrl = deleteUrl;

                    if (clearPayslipText) {
                        clearPayslipText.textContent = `Data gaji ${employeeName} untuk periode ${monthLabel} akan dihapus. Yakin ingin melanjutkan?`;
                    }

                    openClearPayslipConfirmModal();
                });
            });

            if (confirmClearPayslipSubmitButton && clearPayslipForm) {
                confirmClearPayslipSubmitButton.addEventListener('click', function() {
                    if (!pendingClearPayslipUrl) {
                        closeClearPayslipConfirmModal();
                        return;
                    }

                    clearPayslipForm.action = pendingClearPayslipUrl;
                    this.disabled = true;
                    clearPayslipForm.submit();
                });
            }

            const clearPayslipModal = document.getElementById(clearPayslipConfirmModalId);
            if (clearPayslipModal) {
                clearPayslipModal.addEventListener('click', function(event) {
                    if (event.target.matches('[data-modal-close="true"]')) {
                        pendingClearPayslipUrl = null;
                        if (confirmClearPayslipSubmitButton) {
                            confirmClearPayslipSubmitButton.disabled = false;
                        }
                    }
                });
            }

            const sendEmailLoadingModal = document.getElementById(sendEmailLoadingModalId);
            if (sendEmailLoadingModal) {
                sendEmailLoadingModal.querySelectorAll('[data-modal-close="true"]').forEach((button) => {
                    button.style.display = 'none';
                });

                sendEmailLoadingModal.addEventListener('click', function(event) {
                    event.stopPropagation();
                }, true);
            }

            syncSelectAllState();
        });
    </script>

    <!-- STYLES COPIED FROM supervisor/leave_requests/index.blade.php FOR CONSISTENCY -->
    <style>
        /* --- UTILITY --- */
        .fw-bold {
            font-weight: 600;
            color: #111827;
        }

        .text-muted {
            color: #6b7280;
            font-size: 12px;
        }

        .employee-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        /* --- CARD --- */
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid #f3f4f6;
            padding: 0;
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

        /* --- TABLE --- */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 4px;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .custom-table th {
            background: #f9fafb;
            padding: 10px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e5e7eb;
        }

        .custom-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
            color: #1f2937;
            vertical-align: middle;
        }

        .custom-table tr:last-child td {
            border-bottom: none;
        }

        .custom-table tr:hover td {
            background: #fdfdfd;
        }

        /* --- BADGES --- */
        .badge-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-yellow {
            background: #fefce8;
            color: #a16207;
        }

        .badge-gray {
            background: #f3f4f6;
            color: #374151;
        }

        .badge-green {
            background: #dcfce7;
            color: #166534;
        }

        .badge-red {
            background: #fee2e2;
            color: #991b1b;
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

        @keyframes payroll-spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* --- ACTION BUTTONS --- */
        .btn-action {
            padding: 4px 12px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s;
            white-space: nowrap;
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

        .action-buttons-row {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-action-edit {
            border-color: #60a5fa;
            color: #1d4ed8;
            background: #eff6ff;
        }

        .btn-action-edit:hover {
            background: #dbeafe;
            border-color: #3b82f6;
            color: #1e40af;
        }

        .btn-action-clear {
            border-color: #fecaca;
            color: #b91c1c;
            background: #fff1f2;
            cursor: pointer;
        }

        .btn-action-clear:hover {
            background: #ffe4e6;
            border-color: #fda4af;
            color: #991b1b;
        }

        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: #9ca3af;
            font-style: italic;
        }

        /* --- RESPONSIVE --- */
        @media screen and (max-width: 768px) {

            .custom-table,
            .custom-table tbody,
            .custom-table tr,
            .custom-table td {
                display: block;
                width: 100%;
            }

            .custom-table thead {
                display: none;
            }

            .custom-table tr {
                margin-bottom: 12px;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                padding: 12px;
            }

            .custom-table td {
                padding: 8px 0;
                border: none;
            }
        }
    </style>
</x-app>