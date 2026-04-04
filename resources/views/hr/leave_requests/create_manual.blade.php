<x-app title="Tambah Data Izin / Cuti">

    <div class="leave-create-container">

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

        {{-- Flash / Error Messages --}}
        @if ($errors->any())
        <div class="flash flash-error">
            <svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        @if(session('success'))
        <div class="flash flash-success">
            <svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        {{-- Info Banner --}}
        <div class="info-banner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span>Form ini digunakan untuk input manual dan sinkronisasi data lama dari form kertas ke sistem.</span>
        </div>

        {{-- Back Link --}}
        <a href="{{ route('hr.leave.master') }}" class="back-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
            Kembali ke Master Izin/Cuti
        </a>

        {{-- Page Header --}}
        <div class="page-header">
            <div class="page-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </div>
            <div>
                <h1 class="page-title">Input Manual Izin / Cuti</h1>
                <p class="page-subtitle">HR dapat mempersiapkan data pengajuan lama dari form manual.</p>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="form-card">
            <form id="hr-manual-leave-form" method="POST" action="{{ route('hr.leave.manual.store') }}" enctype="multipart/form-data">

                @csrf

                {{-- Section: Karyawan --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>Data Karyawan</span>
                    </div>

                    <div class="form-group">
                        <label for="manual_user_search">Pilih Karyawan <span class="req">*</span></label>
                        <div class="employee-picker">
                            <input
                                type="text"
                                id="manual_user_search"
                                class="form-input"
                                placeholder="Ketik nama karyawan..."
                                autocomplete="off"
                                value="{{ old('user_id') ? optional($employees->firstWhere('id', (int) old('user_id')))->name : '' }}">
                            <div id="manual_user_suggestions" class="employee-suggestions" style="display:none;"></div>
                        </div>
                        <select id="manual_user_id" name="user_id" class="form-input employee-select-hidden" tabindex="-1" aria-hidden="true">
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
                                >{{ $employeeLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Section: Detail Pengajuan --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>Detail Pengajuan</span>
                    </div>

                    <div class="two-col-grid">
                        <div class="form-group">
                            <label for="manual_submitted_at">Tanggal Pengajuan</label>
                            <input type="date" id="manual_submitted_at" name="submitted_at" class="form-input" value="{{ old('submitted_at') }}">
                        </div>
                        <div class="form-group">
                            <label for="manual_status">Status</label>
                            <select id="manual_status" name="status" class="form-input">
                                <option value="">Pilih status</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="section-label">Jenis Pengajuan <span class="req">*</span></label>
                        <div class="radio-group-grid">
                            @foreach($typeOptions as $case)
                                <label class="radio-card">
                                    <input type="radio" name="type" value="{{ $case->value }}" @checked(old('type') === $case->value)>
                                    <span class="radio-label">{{ $case->label() }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div id="manual-balance-info" class="info-alert info-alert-blue" style="display:none; margin-top:12px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            <div>
                                <strong id="manual-balance-text">Saldo cuti karyawan: 0 hari.</strong>
                                <div class="info-alert-hint">Informasi ini hanya sebagai referensi saat input manual.</div>
                            </div>
                        </div>

                        <div id="manual-special-leave-container" class="special-leave-box" style="display:none;">
                            <label for="manual_special_leave_detail" class="special-leave-label">Pilih Kategori Cuti Khusus</label>
                            <select name="special_leave_detail" id="manual_special_leave_detail" class="form-input">
                                <option value="">-- Pilih Alasan --</option>
                                @foreach($specialLeaveList as $sl)
                                    <option value="{{ $sl['id'] }}" data-days="{{ $sl['days'] }}" @selected(old('special_leave_detail') == $sl['id'])>{{ $sl['label'] }}</option>
                                @endforeach
                            </select>
                            <div id="manual-special-leave-badge" class="info-badge" style="display:none;">
                                <span id="manual-special-leave-text">Maksimal 2 Hari</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="manual_date_range">Periode Izin <span class="req">*</span></label>
                        <input type="text" id="manual_date_range" name="date_range" class="form-input" value="{{ $oldRange }}" placeholder="Pilih tanggal mulai sampai selesai" autocomplete="off">
                        <input type="hidden" name="start_date" id="manual_start_date" value="{{ $oldStart }}">
                        <input type="hidden" name="end_date" id="manual_end_date" value="{{ $oldEnd }}">
                        <div id="manual-duration-display" class="info-alert info-alert-blue" style="display:none; margin-top:8px;"></div>
                        <div id="manual-special-limit-warning" class="info-alert info-alert-warning" style="display:none;"></div>
                    </div>

                    <div class="form-group" id="manual-worktime-field" style="display:none;">
                        <label id="manual-worktime-label">Jam Izin</label>
                        <div class="time-range-wrapper">
                            <div class="time-input-box">
                                <input type="time" name="start_time" id="manual_start_time_input" class="form-input" value="{{ old('start_time') }}">
                            </div>
                            <span id="manual-worktime-separator" class="separator">s/d</span>
                            <div id="manual_end_time_wrapper" class="time-input-box">
                                <input type="time" name="end_time" id="manual_end_time_input" class="form-input" value="{{ old('end_time') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section: Pendelegasian --}}
                <div id="manual-substitute-pic-section" class="form-section delegate-section" style="display:none;">
                    <div class="form-section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>Informasi Pendelegasian Tugas</span>
                    </div>
                    <p class="delegate-desc">Opsional untuk kebutuhan dokumentasi sinkronisasi data lama.</p>
                    <div class="two-col-grid">
                        <div class="form-group">
                            <label for="manual_substitute_pic">Nama PIC Pengganti</label>
                            <input type="text" name="substitute_pic" id="manual_substitute_pic" class="form-input" placeholder="Nama rekan pengganti" value="{{ old('substitute_pic') }}">
                        </div>
                        <div class="form-group">
                            <label for="manual_substitute_phone">Nomor HP PIC</label>
                            <input type="text" name="substitute_phone" id="manual_substitute_phone" class="form-input" placeholder="Contoh: 0812..." value="{{ old('substitute_phone') }}">
                        </div>
                    </div>
                </div>

                {{-- Section: Dokumen & Catatan --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <span>Dokumen & Catatan</span>
                    </div>

                    <div class="form-group">
                        <label for="manual_photo_input">Bukti Pendukung</label>
                        <div class="file-input-wrapper">
                            <input type="file" name="photo" id="manual_photo_input" class="form-input-file" accept=".jpg,.jpeg,.png,.webp,.heic,.heif,.pdf,.doc,.docx,.xls,.xlsx">
                        </div>
                        <small class="form-hint">Format: Gambar atau dokumen pendukung jika tersedia.</small>
                        <div id="manual-photo-preview-container" class="preview-container">
                            <p class="preview-label">Preview Foto:</p>
                            <img id="manual-photo-preview" src="#" alt="Preview foto">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="manual_reason">Alasan / Keterangan</label>
                        <textarea name="reason" id="manual_reason" rows="3" class="form-input" placeholder="Isi keterangan pengajuan manual atau catatan sinkronisasi...">{{ old('reason') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="manual_notes_hrd">Catatan HRD</label>
                        <textarea name="notes_hrd" id="manual_notes_hrd" rows="2" class="form-input" placeholder="Catatan internal HRD untuk data manual ini...">{{ old('notes_hrd') }}</textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('hr.leave.master') }}" class="btn btn-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary" id="manual-submit-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Simpan Data Manual
                    </button>
                </div>

            </form>
        </div>

    </div>

    @push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
                    balanceInfo.style.display = 'flex';
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
                durationDisplay.style.display = 'flex';
                durationDisplay.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><strong>Estimasi Durasi: ' + days + ' Hari Kerja</strong>';
            }

            function checkSpecialLeaveLimit() {
                if (!specialLimitWarning) return;
                if (selectedType() !== CUTI_KHUSUS || !specialLeaveSelect || !specialLeaveSelect.value) {
                    specialLimitWarning.style.display = 'none';
                    specialLimitWarning.innerHTML = '';
                    return;
                }
                const option = specialLeaveSelect.options[specialLeaveSelect.selectedIndex];
                const maxDays = parseInt(option ? option.getAttribute('data-days') || '0' : '0', 10);
                const diffDays = calculateWorkingDays(startDateInput.value, endDateInput.value);
                if (maxDays > 0 && diffDays > maxDays) {
                    specialLimitWarning.style.display = 'flex';
                    specialLimitWarning.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>Pengajuan terhitung <b>' + diffDays + ' hari kerja</b>, melebihi batas maksimal <b>' + maxDays + ' hari</b> untuk kategori ini.';
                } else {
                    specialLimitWarning.style.display = 'none';
                    specialLimitWarning.innerHTML = '';
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
                    submitBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Menyimpan...';
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
    @endpush

    <style>
        /* === BASE VARIABLES === */
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #64748b;
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success-bg: #f0fdf4;
            --success-text: #15803d;
            --success-border: #bbf7d0;
            --danger-bg: #fef2f2;
            --danger-text: #b91c1c;
            --danger-border: #fecaca;
            --warning-bg: #fffbeb;
            --warning-text: #c2410c;
            --warning-border: #fed7aa;
            --blue-light: #eff6ff;
            --blue-text: #1d4ed8;
            --green-light: #f0fdf4;
            --green-text: #15803d;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
        }

        /* === RESET & BASE === */
        .leave-create-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px 16px 60px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            color: var(--text-main);
        }

        /* === FLASH MESSAGES === */
        .flash {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-radius: var(--radius-md);
            margin-bottom: 16px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .flash-success { background: var(--success-bg); color: var(--success-text); border: 1px solid var(--success-border); }
        .flash-error { background: var(--danger-bg); color: var(--danger-text); border: 1px solid var(--danger-border); }
        .flash-icon { width: 18px; height: 18px; flex-shrink: 0; }

        /* === INFO BANNER === */
        .info-banner {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: var(--blue-light);
            border: 1px solid #dbeafe;
            border-radius: var(--radius-md);
            margin-bottom: 16px;
            font-size: 0.875rem;
            color: var(--blue-text);
        }
        .info-banner svg { width: 16px; height: 16px; flex-shrink: 0; }

        /* === BACK LINK === */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            text-decoration: none;
            margin-bottom: 16px;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--primary); }
        .back-link svg { width: 16px; height: 16px; }

        /* === PAGE HEADER === */
        .page-header {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 20px;
        }
        .page-icon {
            width: 48px;
            height: 48px;
            background: var(--primary);
            color: #fff;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .page-icon svg { width: 24px; height: 24px; }
        .page-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-main);
        }
        .page-subtitle {
            margin: 4px 0 0;
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        /* === FORM CARD === */
        .form-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }

        /* === FORM SECTION === */
        .form-section {
            padding: 24px;
            border-bottom: 1px solid var(--border);
        }
        .form-section:last-of-type {
            border-bottom: none;
        }
        .form-section-header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 16px;
        }
        .form-section-header svg { width: 18px; height: 18px; color: var(--primary); }

        /* === FORM GROUP === */
        .form-group {
            margin-bottom: 16px;
        }
        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 6px;
        }
        .section-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .req { color: var(--danger-text); }
        .form-hint {
            display: block;
            margin-top: 4px;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* === FORM INPUT === */
        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            color: var(--text-main);
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: inherit;
            box-sizing: border-box;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        textarea.form-input {
            resize: vertical;
            min-height: 80px;
            line-height: 1.5;
        }

        /* === TWO COL GRID === */
        .two-col-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        /* === RADIO GROUP === */
        .radio-group-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 8px;
        }
        .radio-card {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.2s;
            background: #fff;
        }
        .radio-card:hover {
            border-color: var(--primary);
            background: var(--blue-light);
        }
        .radio-card:has(input:checked) {
            border-color: var(--primary);
            background: var(--blue-light);
        }
        .radio-card input[type="radio"] {
            accent-color: var(--primary);
            width: 16px;
            height: 16px;
            margin: 0;
        }
        .radio-label {
            font-size: 0.85rem;
            color: var(--text-main);
            font-weight: 500;
            line-height: 1.3;
        }

        /* === INFO ALERT === */
        .info-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
        }
        .info-alert svg { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; }
        .info-alert-blue {
            background: var(--blue-light);
            border: 1px solid #dbeafe;
            color: var(--blue-text);
        }
        .info-alert-warning {
            background: var(--warning-bg);
            border: 1px solid var(--warning-border);
            color: var(--warning-text);
        }
        .info-alert-hint {
            font-size: 0.75rem;
            margin-top: 2px;
            opacity: 0.8;
        }

        /* === SPECIAL LEAVE BOX === */
        .special-leave-box {
            margin-top: 12px;
            padding: 14px;
            background: var(--blue-light);
            border: 1px solid #dbeafe;
            border-radius: var(--radius-sm);
        }
        .special-leave-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--blue-text);
            margin-bottom: 8px;
        }
        .info-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            background: #dbeafe;
            color: var(--blue-text);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .info-badge svg { width: 12px; height: 12px; }

        /* === DELEGATE SECTION === */
        .delegate-section {
            background: #fafbff;
        }
        .delegate-desc {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin: -8px 0 16px;
        }

        /* === TIME RANGE === */
        .time-range-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .time-input-box { flex: 1; min-width: 100px; }
        .separator { color: var(--text-muted); font-size: 0.85rem; font-weight: 500; }

        /* === EMPLOYEE PICKER === */
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
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12);
            max-height: 280px;
            overflow-y: auto;
        }
        .employee-suggestion-item {
            padding: 10px 12px;
            font-size: 0.875rem;
            color: var(--text-main);
            cursor: pointer;
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }
        .employee-suggestion-item:last-child { border-bottom: none; }
        .employee-suggestion-item:hover,
        .employee-suggestion-item.active {
            background: var(--blue-light);
            color: var(--blue-text);
        }
        .employee-suggestion-empty {
            padding: 10px 12px;
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        /* === FILE INPUT === */
        .file-input-wrapper {
            border: 1px dashed #cbd5e1;
            padding: 12px;
            border-radius: var(--radius-sm);
            background: var(--bg-body);
        }
        .form-input-file { width: 100%; font-size: 0.8rem; background: transparent; border: none; padding: 0; }
        .form-input-file:focus { outline: none; }

        /* === PREVIEW === */
        .preview-container {
            display: none;
            margin-top: 12px;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--bg-body);
        }
        .preview-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            margin: 0 0 6px 0;
        }
        .preview-container img {
            max-width: 100%;
            max-height: 200px;
            border-radius: var(--radius-sm);
            display: block;
        }

        /* === BUTTONS === */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.2s;
            text-decoration: none;
        }
        .btn svg { width: 16px; height: 16px; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary { background: var(--bg-body); color: var(--text-muted); border: 1px solid var(--border); }
        .btn-secondary:hover { background: var(--border); }

        /* === FORM ACTIONS === */
        .form-actions {
            margin-top: 24px;
            padding: 20px 24px;
            border-top: 1px solid var(--border);
            background: var(--bg-body);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        /* === SPIN ANIMATION === */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .spin { animation: spin 1s linear infinite; }

        /* === MOBILE RESPONSIVE === */
        @media (max-width: 640px) {
            .page-header {
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }
            .page-icon { margin: 0 auto; }

            .form-section { padding: 20px 16px; }

            .two-col-grid { grid-template-columns: 1fr; }
            .radio-group-grid { grid-template-columns: 1fr; }

            .form-actions {
                flex-direction: column-reverse;
                padding: 16px;
            }
            .btn { width: 100%; }

            .time-range-wrapper { flex-direction: column; align-items: stretch; }
            .separator { display: none; }
        }
    </style>

</x-app>
