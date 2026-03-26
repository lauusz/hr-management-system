<x-app title="Tambah Data Izin / Cuti">
    @php
        $oldStart = old('start_date');
        $oldEnd = old('end_date');
        $oldRange = '';

        if ($oldStart && $oldEnd) {
            $oldRange = $oldStart . ' sampai ' . $oldEnd;
        } elseif ($oldStart) {
            $oldRange = $oldStart;
        }
    @endphp

    @if ($errors->any())
        <div class="alert-error" style="margin-bottom: 16px;">
            {{ $errors->first() }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert-success" style="margin-bottom: 16px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="alert-warning" style="margin-bottom: 16px;">
        Form ini digunakan untuk input manual dan sinkronisasi data lama dari form kertas ke sistem.
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="form-title">Input Manual Izin / Cuti</h3>
                <p class="form-subtitle">HR dapat menyiapkan data pengajuan lama dari form manual menggunakan tampilan yang serupa dengan form pengajuan karyawan.</p>
            </div>
            <a href="{{ route('hr.leave.master') }}" class="btn-back">Kembali</a>
        </div>

        <div class="divider"></div>

        <form id="hr-manual-leave-form" class="form-content" method="POST" action="{{ route('hr.leave.manual.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="manual_user_search">Karyawan</label>
                <div class="employee-picker">
                    <input
                        type="text"
                        id="manual_user_search"
                        class="form-control"
                        placeholder="Ketik nama karyawan..."
                        autocomplete="off"
                        value="{{ old('user_id') ? optional($employees->firstWhere('id', (int) old('user_id')))->name : '' }}">
                    <div id="manual_user_suggestions" class="employee-suggestions" style="display:none;"></div>
                </div>
                <select id="manual_user_id" name="user_id" class="form-control employee-select-hidden" tabindex="-1" aria-hidden="true">
                    <option value="">Pilih karyawan</option>
                    @foreach($employees as $employee)
                        @php
                            $roleValue = $employee->role instanceof \App\Enums\UserRole ? $employee->role->value : $employee->role;
                            $employeeLabel = trim($employee->name
                                . ($employee->position ? ' - ' . $employee->position->name : '')
                                . ($employee->division ? ' (' . $employee->division->name . ')' : ''));
                        @endphp
                        <option
                            value="{{ $employee->id }}"
                            data-role="{{ strtoupper((string) $roleValue) }}"
                            data-balance="{{ (int) ($employee->leave_balance ?? 0) }}"
                            data-label="{{ $employeeLabel }}"
                            @selected(old('user_id') == $employee->id)
                        >
                            {{ $employeeLabel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="two-col-grid">
                <div class="form-group">
                    <label for="manual_submitted_at">Tanggal Pengajuan</label>
                    <input type="date" id="manual_submitted_at" name="submitted_at" class="form-control" value="{{ old('submitted_at') }}">
                </div>
                <div class="form-group">
                    <label for="manual_status">Status</label>
                    <select id="manual_status" name="status" class="form-control">
                        <option value="">Pilih status</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="section-label">Jenis Pengajuan</label>
                <div class="radio-group-container">
                    @foreach($typeOptions as $case)
                        <label class="radio-card">
                            <input
                                type="radio"
                                name="type"
                                value="{{ $case->value }}"
                                @checked(old('type') === $case->value)
                            >
                            <span class="radio-label">{{ $case->label() }}</span>
                        </label>
                    @endforeach
                </div>

                <div id="manual-balance-info" class="alert-info-blue" style="display:none; margin-top:12px;">
                    <div>
                        <strong id="manual-balance-text">Saldo cuti karyawan: 0 hari.</strong>
                        <div style="font-size:12px; margin-top:2px;">Informasi ini hanya sebagai referensi saat input manual.</div>
                    </div>
                </div>

                <div id="manual-special-leave-container" class="special-leave-box" style="display:none;">
                    <label for="manual_special_leave_detail" class="special-leave-label">Pilih Kategori Cuti Khusus</label>
                    <select name="special_leave_detail" id="manual_special_leave_detail" class="form-control">
                        <option value="">-- Pilih Alasan --</option>
                        @foreach($specialLeaveList as $sl)
                            <option value="{{ $sl['id'] }}" data-days="{{ $sl['days'] }}" @selected(old('special_leave_detail') == $sl['id'])>
                                {{ $sl['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <div id="manual-special-leave-badge" class="info-badge" style="display:none;">
                        <span id="manual-special-leave-text">Maksimal 2 Hari</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="manual_date_range">Periode Izin</label>
                <input
                    type="text"
                    id="manual_date_range"
                    name="date_range"
                    class="form-control"
                    value="{{ $oldRange }}"
                    placeholder="Pilih tanggal mulai sampai selesai"
                    autocomplete="off">
                <input type="hidden" name="start_date" id="manual_start_date" value="{{ $oldStart }}">
                <input type="hidden" name="end_date" id="manual_end_date" value="{{ $oldEnd }}">

                <div id="manual-duration-display" class="alert-info-blue" style="display:none; margin-top:8px;"></div>
                <div id="manual-special-limit-warning" class="alert-warning" style="display:none;"></div>
            </div>

            <div class="form-group" id="manual-worktime-field" style="display:none;">
                <label id="manual-worktime-label">Jam Izin</label>
                <div class="time-range-wrapper">
                    <div class="time-input-box">
                        <input type="time" name="start_time" id="manual_start_time_input" class="form-control" value="{{ old('start_time') }}">
                    </div>
                    <span id="manual-worktime-separator" class="separator">s/d</span>
                    <div id="manual_end_time_wrapper" class="time-input-box">
                        <input type="time" name="end_time" id="manual_end_time_input" class="form-control" value="{{ old('end_time') }}">
                    </div>
                </div>
            </div>

            <div id="manual-substitute-pic-section" class="delegate-box" style="display:none;">
                <p class="delegate-title">Informasi Pendelegasian Tugas</p>
                <div class="two-col-grid">
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="manual_substitute_pic">Nama PIC Pengganti</label>
                        <input type="text" name="substitute_pic" id="manual_substitute_pic" class="form-control" placeholder="Nama rekan pengganti" value="{{ old('substitute_pic') }}">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="manual_substitute_phone">Nomor HP PIC</label>
                        <input type="text" name="substitute_phone" id="manual_substitute_phone" class="form-control" placeholder="Contoh: 0812..." value="{{ old('substitute_phone') }}">
                    </div>
                </div>
                <small class="helper-text" style="display:block; margin-top:8px;">Opsional untuk kebutuhan dokumentasi sinkronisasi data lama.</small>
            </div>

            <div class="form-group">
                <label for="manual_photo_input">Bukti Pendukung</label>
                <div class="file-input-wrapper">
                    <input
                        type="file"
                        name="photo"
                        id="manual_photo_input"
                        class="form-control-file"
                        accept=".jpg,.jpeg,.png,.webp,.heic,.heif,.pdf,.doc,.docx,.xls,.xlsx">
                </div>
                <small class="helper-text">Format: Gambar atau dokumen pendukung jika tersedia.</small>
                <div id="manual-photo-preview-container" class="preview-container">
                    <p class="preview-label">Preview Foto:</p>
                    <img id="manual-photo-preview" src="#" alt="Preview foto">
                </div>
            </div>

            <div class="form-group">
                <label for="manual_reason">Alasan / Keterangan</label>
                <textarea
                    name="reason"
                    id="manual_reason"
                    rows="4"
                    class="form-control"
                    placeholder="Isi keterangan pengajuan manual atau catatan sinkronisasi..."
                >{{ old('reason') }}</textarea>
            </div>

            <div class="form-group">
                <label for="manual_notes_hrd">Catatan HRD</label>
                <textarea
                    name="notes_hrd"
                    id="manual_notes_hrd"
                    rows="3"
                    class="form-control"
                    placeholder="Catatan internal HRD untuk data manual ini..."
                >{{ old('notes_hrd') }}</textarea>
            </div>

            <div class="form-actions">
                <button class="btn-primary" type="submit" id="manual-submit-btn">
                    Simpan Data Manual
                </button>
            </div>
        </form>
    </div>

    <style>
        .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid #f3f4f6; overflow: hidden; max-width: 900px; margin: 0 auto; }
        .card-header { padding: 20px; display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
        .form-title { margin: 0; font-size: 18px; font-weight: 700; color: #111827; }
        .form-subtitle { margin: 4px 0 0; font-size: 13.5px; color: #6b7280; }
        .divider { height: 1px; background: #f3f4f6; width: 100%; }
        .form-content { padding: 24px; }
        .form-group { margin-bottom: 20px; display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13.5px; font-weight: 600; color: #374151; }
        .section-label { display: block; margin-bottom: 8px; font-size: 14px; color: #111827; }
        .form-control {
            padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;
            font-size: 14px; width: 100%; outline: none; transition: border-color 0.2s, box-shadow 0.2s;
            background: #fff; color: #111827; font-family: inherit;
        }
        .form-control:focus { border-color: #1e4a8d; box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1); }
        textarea.form-control { resize: vertical; min-height: 100px; line-height: 1.5; }
        .btn-back {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 14px; border-radius: 8px; border: 1px solid #d1d5db;
            background: #fff; color: #374151; font-size: 13px; font-weight: 500;
            text-decoration: none; transition: all 0.2s; white-space: nowrap;
        }
        .btn-primary {
            padding: 12px 24px; background: #1e4a8d; color: #fff; border: none; border-radius: 8px;
            font-size: 14px; font-weight: 600; width: auto; min-width: 220px;
        }
        .radio-group-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 10px; }
        .radio-card {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px;
            cursor: pointer; transition: all 0.2s; background: #fff;
        }
        .radio-card:hover { border-color: #1e4a8d; background: #f0f4ff; }
        .radio-card input[type="radio"] { accent-color: #1e4a8d; width: 16px; height: 16px; margin: 0; }
        .radio-label { font-size: 13.5px; color: #374151; font-weight: 500; line-height: 1.3; }
        .alert-warning {
            background: #fefce8; border: 1px solid #fde047; color: #854d0e;
            padding: 10px 14px; border-radius: 8px; margin-top: 8px; font-size: 13.5px; line-height: 1.4;
        }
        .alert-error {
            background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
            padding: 12px 16px; border-radius: 8px; font-size: 14px;
        }
        .alert-success {
            background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46;
            padding: 12px 16px; border-radius: 8px; font-size: 14px;
        }
        .alert-info-blue {
            background: #eff6ff; border: 1px solid #dbeafe; color: #1e40af;
            padding: 12px 16px; border-radius: 8px; font-size: 14px;
        }
        .special-leave-box {
            margin-top: 12px; padding: 12px; background: #eff6ff; border: 1px solid #dbeafe; border-radius: 8px;
        }
        .special-leave-label { font-size: 13px; color: #1e4a8d; display:block; margin-bottom:6px; }
        .employee-picker { position: relative; }
        .employee-select-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }
        .employee-suggestions {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            z-index: 30;
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12);
            max-height: 280px;
            overflow-y: auto;
        }
        .employee-suggestion-item {
            padding: 10px 12px;
            font-size: 13.5px;
            color: #111827;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
        }
        .employee-suggestion-item:last-child { border-bottom: none; }
        .employee-suggestion-item:hover,
        .employee-suggestion-item.active {
            background: #eff6ff;
            color: #1d4ed8;
        }
        .employee-suggestion-empty {
            padding: 10px 12px;
            font-size: 13px;
            color: #6b7280;
        }
        .info-badge {
            display: inline-flex; align-items: center; gap: 6px;
            margin-top: 8px; background: #dbeafe; color: #1e40af;
            padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;
        }
        .time-range-wrapper { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .time-input-box { flex: 1; min-width: 120px; }
        .separator { color: #6b7280; font-size: 13px; font-weight: 500; }
        .delegate-box { padding: 16px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; margin-bottom: 20px; }
        .delegate-title { margin-top:0; margin-bottom:12px; font-size:14px; font-weight:600; color:#1e4a8d; }
        .two-col-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .file-input-wrapper { border: 1px dashed #cbd5e1; padding: 12px; border-radius: 8px; background: #f8fafc; }
        .form-control-file { width: 100%; font-size: 13px; }
        .helper-text { font-size: 12px; color: #6b7280; margin-top: 4px; line-height: 1.4; }
        .preview-container { display: none; margin-top: 12px; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb; }
        .preview-label { font-size: 12px; font-weight: 600; color: #4b5563; margin: 0 0 6px 0; }
        .preview-container img { max-width: 100%; max-height: 300px; border-radius: 6px; display: block; }
        .form-actions { margin-top: 32px; padding-top: 20px; border-top: 1px solid #f3f4f6; display: flex; justify-content: flex-end; }

        @media (max-width: 768px) {
            .card-header { flex-direction: column; }
            .two-col-grid { grid-template-columns: 1fr; }
            .radio-group-container { grid-template-columns: 1fr; }
            .btn-back, .btn-primary { width: 100%; }
        }
    </style>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const employeeSelect = document.getElementById('manual_user_id');
            const employeeSearchInput = document.getElementById('manual_user_search');
            const employeeSuggestions = document.getElementById('manual_user_suggestions');
            const typeRadios = document.querySelectorAll('input[name="type"]');
            const specialLeaveContainer = document.getElementById('manual-special-leave-container');
            const specialLeaveSelect = document.getElementById('manual_special_leave_detail');
            const specialLeaveBadge = document.getElementById('manual-special-leave-badge');
            const specialLeaveText = document.getElementById('manual-special-leave-text');
            const specialLimitWarning = document.getElementById('manual-special-limit-warning');
            const balanceInfo = document.getElementById('manual-balance-info');
            const balanceText = document.getElementById('manual-balance-text');
            const startDateInput = document.getElementById('manual_start_date');
            const endDateInput = document.getElementById('manual_end_date');
            const durationDisplay = document.getElementById('manual-duration-display');
            const worktimeField = document.getElementById('manual-worktime-field');
            const worktimeLabel = document.getElementById('manual-worktime-label');
            const worktimeSeparator = document.getElementById('manual-worktime-separator');
            const endTimeWrapper = document.getElementById('manual_end_time_wrapper');
            const endTimeInput = document.getElementById('manual_end_time_input');
            const picSection = document.getElementById('manual-substitute-pic-section');
            const photoInput = document.getElementById('manual_photo_input');
            const previewContainer = document.getElementById('manual-photo-preview-container');
            const previewImg = document.getElementById('manual-photo-preview');
            const rangeInput = document.getElementById('manual_date_range');
            const form = document.getElementById('hr-manual-leave-form');
            const submitBtn = document.getElementById('manual-submit-btn');
            let activeSuggestionIndex = -1;

            const CUTI = @json(\App\Enums\LeaveType::CUTI->value);
            const CUTI_KHUSUS = @json(\App\Enums\LeaveType::CUTI_KHUSUS->value);
            const SAKIT = @json(\App\Enums\LeaveType::SAKIT->value);
            const IZIN_TELAT = @json(\App\Enums\LeaveType::IZIN_TELAT->value);
            const IZIN_TENGAH_KERJA = @json(\App\Enums\LeaveType::IZIN_TENGAH_KERJA->value);
            const IZIN_PULANG_AWAL = @json(\App\Enums\LeaveType::IZIN_PULANG_AWAL->value);

            function selectedType() {
                const checked = document.querySelector('input[name="type"]:checked');
                return checked ? checked.value : null;
            }

            function selectedEmployeeOption() {
                return employeeSelect ? employeeSelect.options[employeeSelect.selectedIndex] : null;
            }

            function employeeOptions() {
                return employeeSelect ? Array.from(employeeSelect.options).filter(function (option) {
                    return option.value !== '';
                }) : [];
            }

            function syncEmployeeSearchFromSelect() {
                if (!employeeSearchInput) return;
                const option = selectedEmployeeOption();
                employeeSearchInput.value = option && option.value ? (option.getAttribute('data-label') || option.textContent.trim()) : '';
            }

            function hideEmployeeSuggestions() {
                if (!employeeSuggestions) return;
                employeeSuggestions.style.display = 'none';
                employeeSuggestions.innerHTML = '';
                activeSuggestionIndex = -1;
            }

            function selectEmployeeByValue(value) {
                if (!employeeSelect) return;
                employeeSelect.value = value || '';
                syncEmployeeSearchFromSelect();
                employeeSelect.dispatchEvent(new Event('change'));
                hideEmployeeSuggestions();
            }

            function renderEmployeeSuggestions(keyword) {
                if (!employeeSuggestions) return;

                const normalizedKeyword = (keyword || '').trim().toLowerCase();
                const matches = employeeOptions().filter(function (option) {
                    const label = (option.getAttribute('data-label') || option.textContent || '').toLowerCase();
                    return normalizedKeyword === '' || label.includes(normalizedKeyword);
                }).slice(0, 30);

                if (matches.length === 0) {
                    employeeSuggestions.innerHTML = '<div class="employee-suggestion-empty">Karyawan tidak ditemukan.</div>';
                    employeeSuggestions.style.display = 'block';
                    activeSuggestionIndex = -1;
                    return;
                }

                employeeSuggestions.innerHTML = matches.map(function (option, index) {
                    const label = option.getAttribute('data-label') || option.textContent.trim();
                    return '<div class="employee-suggestion-item' + (index === activeSuggestionIndex ? ' active' : '') + '" data-value="' + option.value + '">' + label + '</div>';
                }).join('');
                employeeSuggestions.style.display = 'block';
            }

            function selectedEmployeeRole() {
                const option = selectedEmployeeOption();
                return option ? (option.getAttribute('data-role') || '') : '';
            }

            function selectedEmployeeBalance() {
                const option = selectedEmployeeOption();
                return option ? parseInt(option.getAttribute('data-balance') || '0', 10) : 0;
            }

            function isFiveDayWorkWeek() {
                return selectedEmployeeRole() === 'MANAGER';
            }

            function parseYmdAsDate(value) {
                if (!value) return null;
                const parts = value.split('-').map(Number);
                if (parts.length !== 3 || parts.some(Number.isNaN)) return null;
                const date = new Date(parts[0], parts[1] - 1, parts[2]);
                date.setHours(0, 0, 0, 0);
                return date;
            }

            function calculateWorkingDays(startStr, endStr) {
                const startDate = parseYmdAsDate(startStr);
                const endDate = parseYmdAsDate(endStr);
                if (!startDate || !endDate || startDate > endDate) return 0;

                let days = 0;
                const cursor = new Date(startDate);

                while (cursor <= endDate) {
                    const day = cursor.getDay();
                    const isSunday = day === 0;
                    const isSaturday = day === 6;

                    if (!isSunday && !(isSaturday && isFiveDayWorkWeek())) {
                        days++;
                    }

                    cursor.setDate(cursor.getDate() + 1);
                }

                return days;
            }

            function updateBalanceInfo() {
                if (!balanceInfo || !balanceText) return;

                if (selectedType() === CUTI && employeeSelect && employeeSelect.value) {
                    balanceInfo.style.display = 'block';
                    balanceText.textContent = 'Saldo cuti karyawan: ' + selectedEmployeeBalance() + ' hari.';
                } else {
                    balanceInfo.style.display = 'none';
                }
            }

            function updateSpecialLeaveBadge() {
                if (!specialLeaveSelect || !specialLeaveBadge || !specialLeaveText) return;
                const option = specialLeaveSelect.options[specialLeaveSelect.selectedIndex];
                const days = option ? option.getAttribute('data-days') : null;

                if (days) {
                    specialLeaveBadge.style.display = 'inline-flex';
                    specialLeaveText.textContent = 'Maksimal ' + days + ' Hari';
                } else {
                    specialLeaveBadge.style.display = 'none';
                    specialLeaveText.textContent = 'Maksimal 2 Hari';
                }
            }

            function updateDurationDisplay() {
                if (!durationDisplay) return;
                const startVal = startDateInput ? startDateInput.value : '';
                const endVal = endDateInput ? endDateInput.value : '';

                if (!startVal || !endVal) {
                    durationDisplay.style.display = 'none';
                    durationDisplay.innerHTML = '';
                    return;
                }

                const days = calculateWorkingDays(startVal, endVal);
                durationDisplay.style.display = 'block';
                durationDisplay.innerHTML = '<strong>Estimasi Durasi: ' + days + ' Hari Kerja</strong>';
            }

            function checkSpecialLeaveLimit() {
                if (!specialLimitWarning) return;
                if (selectedType() !== CUTI_KHUSUS || !specialLeaveSelect || !specialLeaveSelect.value) {
                    specialLimitWarning.style.display = 'none';
                    specialLimitWarning.textContent = '';
                    return;
                }

                const option = specialLeaveSelect.options[specialLeaveSelect.selectedIndex];
                const maxDays = parseInt(option ? option.getAttribute('data-days') || '0' : '0', 10);
                const diffDays = calculateWorkingDays(startDateInput.value, endDateInput.value);

                if (maxDays > 0 && diffDays > maxDays) {
                    specialLimitWarning.style.display = 'block';
                    specialLimitWarning.innerHTML = 'Pengajuan terhitung <b>' + diffDays + ' hari kerja</b>, melebihi batas maksimal <b>' + maxDays + ' hari</b> untuk kategori ini.';
                } else {
                    specialLimitWarning.style.display = 'none';
                    specialLimitWarning.textContent = '';
                }
            }

            function toggleSections() {
                const type = selectedType();
                const isTelat = type === IZIN_TELAT;
                const isTengahKerja = type === IZIN_TENGAH_KERJA;
                const isPulangAwal = type === IZIN_PULANG_AWAL;

                updateBalanceInfo();
                updateDurationDisplay();

                if (type === CUTI_KHUSUS) {
                    specialLeaveContainer.style.display = 'block';
                    updateSpecialLeaveBadge();
                    checkSpecialLeaveLimit();
                } else {
                    specialLeaveContainer.style.display = 'none';
                    if (specialLeaveSelect) specialLeaveSelect.value = '';
                    if (specialLeaveBadge) specialLeaveBadge.style.display = 'none';
                    if (specialLimitWarning) specialLimitWarning.style.display = 'none';
                }

                if (picSection) {
                    picSection.style.display = (type === CUTI || type === CUTI_KHUSUS || type === SAKIT) ? 'block' : 'none';
                }

                if (worktimeField) {
                    worktimeField.style.display = (isTelat || isTengahKerja || isPulangAwal) ? 'block' : 'none';
                }

                if (worktimeLabel && worktimeSeparator && endTimeWrapper && endTimeInput) {
                    if (isTengahKerja) {
                        worktimeLabel.textContent = 'Jam Izin Tengah Kerja';
                        worktimeSeparator.style.display = 'inline';
                        endTimeWrapper.style.display = 'block';
                    } else if (isPulangAwal) {
                        worktimeLabel.textContent = 'Jam Pulang';
                        worktimeSeparator.style.display = 'none';
                        endTimeWrapper.style.display = 'none';
                        endTimeInput.value = '';
                    } else if (isTelat) {
                        worktimeLabel.textContent = 'Estimasi Jam Tiba';
                        worktimeSeparator.style.display = 'none';
                        endTimeWrapper.style.display = 'none';
                        endTimeInput.value = '';
                    } else {
                        worktimeSeparator.style.display = 'inline';
                        endTimeWrapper.style.display = 'block';
                    }
                }
            }

            if (employeeSelect) {
                employeeSelect.addEventListener('change', function () {
                    updateBalanceInfo();
                    updateDurationDisplay();
                    checkSpecialLeaveLimit();
                });
            }

            if (employeeSearchInput && employeeSuggestions) {
                employeeSearchInput.addEventListener('focus', function () {
                    renderEmployeeSuggestions(employeeSearchInput.value);
                });

                employeeSearchInput.addEventListener('input', function () {
                    employeeSelect.value = '';
                    updateBalanceInfo();
                    updateDurationDisplay();
                    checkSpecialLeaveLimit();
                    activeSuggestionIndex = -1;
                    renderEmployeeSuggestions(employeeSearchInput.value);
                });

                employeeSearchInput.addEventListener('keydown', function (event) {
                    const items = employeeSuggestions.querySelectorAll('.employee-suggestion-item');
                    if (!items.length) return;

                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        activeSuggestionIndex = Math.min(activeSuggestionIndex + 1, items.length - 1);
                        renderEmployeeSuggestions(employeeSearchInput.value);
                    } else if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        activeSuggestionIndex = Math.max(activeSuggestionIndex - 1, 0);
                        renderEmployeeSuggestions(employeeSearchInput.value);
                    } else if (event.key === 'Enter' && activeSuggestionIndex >= 0) {
                        event.preventDefault();
                        const target = items[activeSuggestionIndex];
                        if (target) {
                            selectEmployeeByValue(target.getAttribute('data-value'));
                        }
                    } else if (event.key === 'Escape') {
                        hideEmployeeSuggestions();
                    }
                });

                employeeSuggestions.addEventListener('mousedown', function (event) {
                    const item = event.target.closest('.employee-suggestion-item');
                    if (!item) return;
                    event.preventDefault();
                    selectEmployeeByValue(item.getAttribute('data-value'));
                });

                document.addEventListener('click', function (event) {
                    if (!event.target.closest('.employee-picker')) {
                        hideEmployeeSuggestions();
                    }
                });
            }

            if (specialLeaveSelect) {
                specialLeaveSelect.addEventListener('change', function () {
                    updateSpecialLeaveBadge();
                    checkSpecialLeaveLimit();
                });
            }

            if (startDateInput) startDateInput.addEventListener('change', function () { updateDurationDisplay(); checkSpecialLeaveLimit(); });
            if (endDateInput) endDateInput.addEventListener('change', function () { updateDurationDisplay(); checkSpecialLeaveLimit(); });

            typeRadios.forEach(function (radio) {
                radio.addEventListener('change', toggleSections);
            });

            if (photoInput && previewContainer && previewImg) {
                photoInput.addEventListener('change', function () {
                    const file = this.files[0];
                    if (file && file.type && file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function (event) {
                            previewImg.src = event.target.result;
                            previewContainer.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        previewContainer.style.display = 'none';
                        previewImg.src = '';
                    }
                });
            }

            if (form && submitBtn) {
                form.addEventListener('submit', function () {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Menyimpan...';
                });
            }

            if (typeof flatpickr === 'function' && rangeInput) {
                flatpickr(rangeInput, {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    locale: { rangeSeparator: ' sampai ' },
                    onChange: function (selectedDates, dateStr) {
                        if (!dateStr) {
                            startDateInput.value = '';
                            endDateInput.value = '';
                        } else {
                            const parts = dateStr.split(' sampai ');
                            startDateInput.value = parts[0] || '';
                            endDateInput.value = parts[1] || parts[0] || '';
                        }

                        startDateInput.dispatchEvent(new Event('change'));
                        endDateInput.dispatchEvent(new Event('change'));
                    }
                });
            }

            updateSpecialLeaveBadge();
            syncEmployeeSearchFromSelect();
            toggleSections();
        });
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    @endpush
</x-app>