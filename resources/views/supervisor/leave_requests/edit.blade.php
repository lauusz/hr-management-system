<x-app title="Edit Pengajuan Bawahan">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --primary-light: #dbeafe;
            --success: #059669;
            --warning: #d97706;
            --danger: #dc2626;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, sans-serif; background: var(--gray-50); color: var(--gray-900); }

        .page-wrapper { max-width: 680px; margin: 0 auto; padding: 16px; padding-bottom: 100px; }

        /* Info Alert */
        .info-alert { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; padding: 14px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 13.5px; line-height: 1.5; }
        .info-alert strong { font-weight: 700; }

        /* Card */
        .card { background: #fff; border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); border: 1px solid var(--gray-200); overflow: hidden; }
        .card-header { padding: 20px 20px 16px; display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
        .card-header-text {}
        .form-title { margin: 0; font-size: 18px; font-weight: 700; color: var(--gray-900); }
        .form-subtitle { margin: 4px 0 0; font-size: 13px; color: var(--gray-500); }
        .btn-back { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 10px; border: 1px solid var(--gray-200); background: #fff; color: var(--gray-700); font-size: 13px; font-weight: 500; text-decoration: none; transition: all 0.2s; white-space: nowrap; align-self: center; }
        .btn-back:hover { background: var(--gray-50); border-color: var(--gray-300); }
        .btn-back svg { flex-shrink: 0; }

        .divider { height: 1px; background: var(--gray-100); }

        /* Form Content */
        .form-content { padding: 20px; }
        .form-group { margin-bottom: 24px; }
        .form-group:last-child { margin-bottom: 0; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: var(--gray-700); margin-bottom: 8px; }
        .section-label { display: block; font-size: 13px; font-weight: 600; color: var(--gray-700); margin-bottom: 10px; }
        .req { color: var(--danger); margin-left: 2px; }

        .form-control { width: 100%; padding: 12px 14px; border: 1.5px solid var(--gray-200); border-radius: 10px; font-size: 14px; outline: none; background: #fff; color: var(--gray-900); font-family: inherit; transition: border-color 0.2s, box-shadow 0.2s; -webkit-appearance: none; appearance: none; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        textarea.form-control { resize: vertical; min-height: 100px; line-height: 1.5; }
        select.form-control { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 40px; }

        /* Radio Cards */
        .radio-group-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; }
        .radio-card { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1.5px solid var(--gray-200); border-radius: 10px; cursor: pointer; transition: all 0.2s; background: #fff; }
        .radio-card:hover { border-color: var(--primary); background: #f0f7ff; }
        .radio-card input[type="radio"] { accent-color: var(--primary); width: 16px; height: 16px; margin: 0; cursor: pointer; flex-shrink: 0; }
        .radio-label { font-size: 13px; color: var(--gray-700); font-weight: 500; line-height: 1.3; }

        /* Special Leave */
        #special-leave-container { display: none; margin-top: 12px; padding: 14px; background: var(--primary-light); border: 1px solid #bfdbfe; border-radius: 10px; }
        #special-leave-container label { color: var(--primary-dark); }
        #special-leave-badge { display: none; margin-top: 10px; }
        .info-badge { display: inline-flex; align-items: center; gap: 6px; background: var(--primary); color: #fff; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }

        /* Warning */
        .warning-container { margin-top: 8px; }
        .alert-warning { display: none; background: #fefce8; border: 1px solid #fde047; color: #854d0e; padding: 10px 14px; border-radius: 10px; font-size: 13px; line-height: 1.5; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 13.5px; }

        /* Time Range */
        .time-range-wrapper { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .time-input-box { flex: 1; min-width: 120px; }
        .separator { color: var(--gray-500); font-size: 13px; font-weight: 500; }

        /* PIC Section */
        #substitute-pic-section { display: none; margin-bottom: 24px; padding: 16px; background: var(--gray-50); border: 1px dashed var(--gray-300); border-radius: 10px; }
        .pic-section-title { font-size: 13px; font-weight: 600; color: var(--primary-dark); margin-bottom: 12px; }
        .pic-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        /* File Input */
        .file-input-wrapper { border: 1.5px dashed var(--gray-300); padding: 14px; border-radius: 10px; background: var(--gray-50); }
        .form-control-file { width: 100%; font-size: 13px; }
        .preview-container { display: none; margin-top: 12px; padding: 10px; border: 1px solid var(--gray-200); border-radius: 10px; background: var(--gray-50); }
        .preview-label { font-size: 12px; font-weight: 600; color: var(--gray-600); margin: 0 0 6px 0; }
        .preview-container img { max-width: 100%; max-height: 280px; border-radius: 8px; display: block; }
        .current-file { margin-top: 8px; font-size: 13px; }
        .current-file a { color: var(--primary); text-decoration: underline; }

        /* Action Bar */
        .action-bar { position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-top: 1px solid var(--gray-200); padding: 12px 16px; padding-bottom: max(12px, env(safe-area-inset-bottom)); z-index: 50; box-shadow: 0 -2px 10px rgba(0,0,0,0.06); }
        .action-bar-inner { max-width: 680px; margin: 0 auto; }
        .btn-primary { width: 100%; padding: 14px 24px; background: var(--primary); color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-primary svg { flex-shrink: 0; }

        /* Bottom spacer */
        .bottom-spacer { height: 80px; }

        @media (max-width: 480px) {
            .card-header { flex-direction: column; gap: 12px; }
            .btn-back { align-self: flex-start; }
            .radio-group-container { grid-template-columns: 1fr 1fr; }
            .time-range-wrapper { flex-direction: column; align-items: stretch; gap: 8px; }
            .separator { display: none !important; }
            .time-input-box { width: 100%; flex: none; }
            .pic-grid { grid-template-columns: 1fr; }
        }
        @media (min-width: 681px) {
            .page-wrapper { padding: 24px 16px 100px; }
        }
    </style>

    <div class="page-wrapper">
        {{-- Info Alert --}}
        <div class="info-alert">
            <strong>Mode Supervisor:</strong> Anda sedang mengubah data pengajuan milik <strong>{{ $leave->user->name }}</strong>.
            <br>Perubahan yang Anda simpan akan otomatis disetujui oleh Anda dan diteruskan ke HRD.
        </div>

        @if($errors->any())
            <div class="alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <div class="card-header-text">
                    <h3 class="form-title">Form Edit Data</h3>
                    <p class="form-subtitle">Sesuaikan data pengajuan di bawah ini.</p>
                </div>
                <a href="{{ route('approval.show', $leave->id) }}" class="btn-back">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                    Batal
                </a>
            </div>

            <div class="divider"></div>

            <form class="form-content" method="POST" action="{{ route('approval.update', $leave->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- 1. JENIS PENGAJUAN --}}
                <div class="form-group">
                    <label class="section-label">Jenis Pengajuan <span class="req">*</span></label>
                    <div class="radio-group-container">
                        @foreach (\App\Enums\LeaveType::cases() as $case)
                            @if ($case->value === \App\Enums\LeaveType::OFF_SPV->value && !$leave->user->isSupervisor())
                                @continue
                            @endif
                            <label class="radio-card">
                                <input type="radio" name="type" value="{{ $case->value }}" @checked(old('type', $leave->type->value) === $case->value)>
                                <span class="radio-label">{{ $case->label() }}</span>
                            </label>
                        @endforeach
                    </div>

                    {{-- DROPDOWN CUTI KHUSUS --}}
                    <div id="special-leave-container">
                        <label for="special_leave_detail">Pilih Kategori Cuti Khusus <span class="req">*</span></label>
                        @php
                            $specialLeaveList = [
                                ['id' => 'NIKAH_KARYAWAN', 'label' => 'Menikah', 'days' => 4],
                                ['id' => 'ISTRI_MELAHIRKAN', 'label' => 'Istri Melahirkan', 'days' => 2],
                                ['id' => 'ISTRI_KEGUGURAN', 'label' => 'Istri Keguguran', 'days' => 2],
                                ['id' => 'KHITANAN_ANAK', 'label' => 'Khitanan Anak', 'days' => 2],
                                ['id' => 'PEMBAPTISAN_ANAK', 'label' => 'Pembaptisan Anak', 'days' => 2],
                                ['id' => 'NIKAH_ANAK', 'label' => 'Pernikahan Anak', 'days' => 2],
                                ['id' => 'DEATH_EXTENDED', 'label' => 'Kematian (Adik/Kakak/Ipar)', 'days' => 2],
                                ['id' => 'DEATH_CORE', 'label' => 'Kematian Inti (Ortu/Mertua/Istri/Anak)', 'days' => 2],
                                ['id' => 'DEATH_HOUSE', 'label' => 'Kematian Anggota Rumah', 'days' => 1],
                                ['id' => 'HAJI', 'label' => 'Ibadah Haji (1x)', 'days' => 40],
                                ['id' => 'UMROH', 'label' => 'Ibadah Umroh (1x)', 'days' => 14],
                            ];
                            $currentSpecial = old('special_leave_detail', $leave->special_leave_category);
                        @endphp
                        <select name="special_leave_detail" id="special_leave_detail" class="form-control" style="margin-top:8px;">
                            <option value="" disabled @if(!$currentSpecial) selected @endif>-- Pilih Alasan --</option>
                            @foreach($specialLeaveList as $sl)
                                <option value="{{ $sl['id'] }}" data-days="{{ $sl['days'] }}" @selected($currentSpecial == $sl['id'])>
                                    {{ $sl['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <div id="special-leave-badge" class="info-badge">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span id="special-leave-text">Maksimal X Hari</span>
                        </div>
                    </div>
                </div>

                {{-- 2. PERIODE --}}
                <div class="form-group">
                    <label for="date_range">Periode Izin <span class="req">*</span></label>
                    @php
                        $startVal = old('start_date', $leave->start_date ? $leave->start_date->format('Y-m-d') : '');
                        $endVal   = old('end_date', $leave->end_date ? $leave->end_date->format('Y-m-d') : '');
                        $rangeVal = ($startVal && $endVal) ? ($startVal . ' sampai ' . $endVal) : $startVal;
                    @endphp
                    <input type="text" id="date_range" name="date_range" class="form-control" value="{{ $rangeVal }}" placeholder="Pilih tanggal mulai sampai selesai" autocomplete="off">
                    <input type="hidden" name="start_date" id="start_date" value="{{ $startVal }}">
                    <input type="hidden" name="end_date" id="end_date" value="{{ $endVal }}">
                    <div class="warning-container">
                        <div id="special-limit-warning" class="alert-warning" style="display:none;"></div>
                    </div>
                </div>

                {{-- 3. JAM (Kondisional) --}}
                <div class="form-group" id="worktime-field" style="display:none;">
                    <label id="worktime-label">Jam Izin</label>
                    <div class="time-range-wrapper">
                        <div class="time-input-box">
                            <input type="time" name="start_time" id="start_time_input" class="form-control" value="{{ old('start_time', $leave->start_time ? $leave->start_time->format('H:i') : '') }}">
                        </div>
                        <span id="worktime-separator" class="separator">s/d</span>
                        <div id="end_time_wrapper" class="time-input-box">
                            <input type="time" name="end_time" id="end_time_input" class="form-control" value="{{ old('end_time', $leave->end_time ? $leave->end_time->format('H:i') : '') }}">
                        </div>
                    </div>
                </div>

                {{-- 4. PIC PENGGANTI --}}
                <div id="substitute-pic-section">
                    <p class="pic-section-title">Informasi Pendelegasian Tugas</p>
                    <div class="pic-grid">
                        <div class="form-group" style="margin-bottom:0;">
                            <label for="substitute_pic">Nama PIC Pengganti</label>
                            <input type="text" name="substitute_pic" id="substitute_pic" class="form-control" value="{{ old('substitute_pic', $leave->substitute_pic) }}">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label for="substitute_phone">Nomor HP PIC</label>
                            <input type="text" name="substitute_phone" id="substitute_phone" class="form-control" value="{{ old('substitute_phone', $leave->substitute_phone) }}">
                        </div>
                    </div>
                </div>

                {{-- 5. UPLOAD BUKTI --}}
                <div class="form-group">
                    <label for="photoInput">Update Bukti Pendukung <span style="font-weight:400; color:var(--gray-500);">(Opsional)</span></label>
                    <div class="file-input-wrapper">
                        <input type="file" name="photo" id="photoInput" class="form-control-file" accept="image/*,.pdf">
                    </div>
                    @if($leave->photo)
                        <div class="current-file">
                            <span style="color:var(--success);">File saat ini:</span>
                            <a href="{{ asset('storage/leave_photos/' . $leave->photo) }}" target="_blank">Lihat File</a>
                        </div>
                    @endif
                    <div id="photoPreviewContainer" class="preview-container">
                        <p class="preview-label">Preview File Baru:</p>
                        <img id="photoPreview" src="#" alt="Preview">
                    </div>
                </div>

                {{-- 6. ALASAN --}}
                <div class="form-group">
                    <label for="reason">Alasan / Keterangan <span class="req">*</span></label>
                    <textarea name="reason" id="reason" rows="4" class="form-control" required>{{ old('reason', $leave->reason) }}</textarea>
                </div>

                <div class="bottom-spacer"></div>
            </form>
        </div>
    </div>

    {{-- Fixed Action Bar --}}
    <div class="action-bar">
        <div class="action-bar-inner">
            <button class="btn-primary" type="submit" form="fake-submit" onclick="document.querySelector('form').requestSubmit(); return false;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Edit & Teruskan ke HRD
            </button>
        </div>
    </div>

    <script>
        (function() {
            const typeRadios = document.querySelectorAll('input[name="type"]');

            const IZIN_TELAT = @json(\App\Enums\LeaveType::IZIN_TELAT->value);
            const IZIN_TENGAH_KERJA = @json(\App\Enums\LeaveType::IZIN_TENGAH_KERJA->value);
            const IZIN_PULANG_AWAL = @json(\App\Enums\LeaveType::IZIN_PULANG_AWAL->value);
            const CUTI = @json(\App\Enums\LeaveType::CUTI->value);
            const CUTI_KHUSUS = @json(\App\Enums\LeaveType::CUTI_KHUSUS->value);
            const SAKIT = @json(\App\Enums\LeaveType::SAKIT->value);

            const specialLeaveContainer = document.getElementById('special-leave-container');
            const specialLeaveSelect = document.getElementById('special_leave_detail');
            const specialLeaveBadge = document.getElementById('special-leave-badge');
            const specialLeaveText = document.getElementById('special-leave-text');
            const specialLimitWarning = document.getElementById('special-limit-warning');
            const worktimeField = document.getElementById('worktime-field');
            const worktimeLabel = document.getElementById('worktime-label');
            const startTimeInput = document.getElementById('start_time_input');
            const endTimeInput = document.getElementById('end_time_input');
            const endTimeWrapper = document.getElementById('end_time_wrapper');
            const worktimeSeparator = document.getElementById('worktime-separator');
            const picSection = document.getElementById('substitute-pic-section');

            function selectedType() {
                const r = document.querySelector('input[name="type"]:checked');
                return r ? r.value : null;
            }

            function checkSpecialLeaveLimit() {
                if (!specialLimitWarning) return;
                if (selectedType() !== CUTI_KHUSUS || !specialLeaveSelect.value) {
                    specialLimitWarning.style.display = 'none';
                    return;
                }
                const selectedOption = specialLeaveSelect.options[specialLeaveSelect.selectedIndex];
                const maxDays = parseInt(selectedOption.getAttribute('data-days')) || 0;
                const startStr = document.getElementById('start_date').value;
                const endStr = document.getElementById('end_date').value;
                if (!startStr || !endStr) return;
                const startDate = new Date(startStr);
                const endDate = new Date(endStr);
                const diffDays = Math.ceil(Math.abs(endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
                if (maxDays > 0 && diffDays > maxDays) {
                    specialLimitWarning.style.display = 'block';
                    specialLimitWarning.innerHTML = `Durasi revisi <b>${diffDays} hari</b> melebihi batas <b>${maxDays} hari</b>.`;
                } else {
                    specialLimitWarning.style.display = 'none';
                }
            }

            function toggleSection() {
                const val = selectedType();

                if (val === CUTI_KHUSUS) {
                    specialLeaveContainer.style.display = 'block';
                    if (specialLeaveSelect) specialLeaveSelect.required = true;
                    checkSpecialLeaveLimit();
                } else {
                    specialLeaveContainer.style.display = 'none';
                    if (specialLeaveSelect) specialLeaveSelect.required = false;
                    if (specialLeaveBadge) specialLeaveBadge.style.display = 'none';
                    if (specialLimitWarning) specialLimitWarning.style.display = 'none';
                }

                const needPic = (val === CUTI || val === CUTI_KHUSUS || val === SAKIT);
                if (picSection) picSection.style.display = needPic ? 'block' : 'none';

                const isTengahKerja = (val === IZIN_TENGAH_KERJA);
                const isPulangAwal = (val === IZIN_PULANG_AWAL);
                const isTelat = (val === IZIN_TELAT);
                const showWorktime = isTengahKerja || isPulangAwal || isTelat;
                if (worktimeField) worktimeField.style.display = showWorktime ? 'block' : 'none';

                if (!startTimeInput || !endTimeInput) return;

                if (isTengahKerja) {
                    if (worktimeLabel) worktimeLabel.innerHTML = 'Jam Izin Tengah Kerja';
                    if (worktimeSeparator) worktimeSeparator.style.display = 'inline';
                    if (endTimeWrapper) endTimeWrapper.style.display = 'block';
                    startTimeInput.required = true;
                    endTimeInput.required = true;
                } else if (isPulangAwal) {
                    if (worktimeLabel) worktimeLabel.innerHTML = 'Jam Pulang';
                    if (worktimeSeparator) worktimeSeparator.style.display = 'none';
                    if (endTimeWrapper) endTimeWrapper.style.display = 'none';
                    startTimeInput.required = true;
                    endTimeInput.required = false;
                } else if (isTelat) {
                    if (worktimeLabel) worktimeLabel.innerHTML = 'Estimasi Jam Tiba';
                    if (worktimeSeparator) worktimeSeparator.style.display = 'none';
                    if (endTimeWrapper) endTimeWrapper.style.display = 'none';
                    startTimeInput.required = true;
                    endTimeInput.required = false;
                } else {
                    startTimeInput.required = false;
                    endTimeInput.required = false;
                }
            }

            if (specialLeaveSelect) {
                specialLeaveSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const days = selectedOption.getAttribute('data-days');
                    if (days) {
                        specialLeaveBadge.style.display = 'inline-flex';
                        specialLeaveText.textContent = 'Maksimal ' + days + ' Hari';
                    } else {
                        specialLeaveBadge.style.display = 'none';
                    }
                    checkSpecialLeaveLimit();
                });
                if (specialLeaveSelect.value) specialLeaveSelect.dispatchEvent(new Event('change'));
            }

            typeRadios.forEach(r => r.addEventListener('change', toggleSection));
            document.getElementById('start_date').addEventListener('change', checkSpecialLeaveLimit);
            document.getElementById('end_date').addEventListener('change', checkSpecialLeaveLimit);
            toggleSection();
        })();

        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('photoInput');
            const previewContainer = document.getElementById('photoPreviewContainer');
            const previewImg = document.getElementById('photoPreview');
            if (!input || !previewContainer || !previewImg) return;
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        previewContainer.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.style.display = 'none';
                    previewImg.src = '';
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            var rangeInput = document.getElementById('date_range');
            var startHidden = document.getElementById('start_date');
            var endHidden = document.getElementById('end_date');
            if (typeof flatpickr === 'function' && rangeInput) {
                flatpickr(rangeInput, {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    locale: { rangeSeparator: ' sampai ' },
                    defaultDate: [startHidden.value, endHidden.value].filter(Boolean),
                    onChange: function(selectedDates, dateStr) {
                        if (!dateStr) {
                            startHidden.value = '';
                            endHidden.value = '';
                        } else {
                            var parts = dateStr.split(' sampai ');
                            if (parts.length === 1) {
                                startHidden.value = parts[0];
                                endHidden.value = parts[0];
                            } else {
                                startHidden.value = parts[0];
                                endHidden.value = parts[1];
                            }
                        }
                        startHidden.dispatchEvent(new Event('change'));
                        endHidden.dispatchEvent(new Event('change'));
                    }
                });
            }
        });
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</x-app>
