<x-app title="Revisi Pengajuan Bawahan">

    {{-- Info Alert --}}
    <div style="background:#fffbeb; border:1px solid #fcd34d; color:#92400e; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:14px;">
        <strong>Mode Supervisor:</strong> Anda sedang mengubah data pengajuan milik <strong>{{ $leave->user->name }}</strong>.
        <br>Perubahan yang Anda simpan akan otomatis disetujui oleh Anda dan diteruskan ke HRD.
    </div>

    @if($errors->any())
        <div class="alert-error">
            <ul style="margin:0; padding-left:16px;">
                {{ $errors->first() }}
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="form-title">Form Revisi Data</h3>
                <p class="form-subtitle">Sesuaikan data pengajuan di bawah ini.</p>
            </div>
            <a href="{{ route('approval.show', $leave->id) }}" class="btn-back">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                Batal & Kembali
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
                        {{-- Skip OFF SPV jika user bukan SPV (Logic optional, sesuaikan kebutuhan) --}}
                        @if ($case->value === \App\Enums\LeaveType::OFF_SPV->value && !$leave->user->isSupervisor())
                            @continue
                        @endif

                        <label class="radio-card">
                            <input
                                type="radio"
                                name="type"
                                value="{{ $case->value }}"
                                @checked(old('type', $leave->type->value) === $case->value)
                            >
                            <span class="radio-label">{{ $case->label() }}</span>
                        </label>
                    @endforeach
                </div>
                
                {{-- DROPDOWN CUTI KHUSUS --}}
                <div id="special-leave-container" style="display: none; margin-top: 12px; padding: 12px; background: #eff6ff; border: 1px solid #dbeafe; border-radius: 8px;">
                    <label for="special_leave_detail" style="font-size:13px; color:#1e4a8d; display:block; margin-bottom:6px;">
                        Pilih Kategori Cuti Khusus <span class="req">*</span>
                    </label>
                    
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

                    <select name="special_leave_detail" id="special_leave_detail" class="form-control">
                        <option value="" disabled @if(!$currentSpecial) selected @endif>-- Pilih Alasan --</option>
                        @foreach($specialLeaveList as $sl)
                            <option value="{{ $sl['id'] }}" data-days="{{ $sl['days'] }}" @selected($currentSpecial == $sl['id'])>
                                {{ $sl['label'] }}
                            </option>
                        @endforeach
                    </select>
                    
                    {{-- Badge Info Maksimal Hari --}}
                    <div id="special-leave-badge" class="info-badge" style="display: none;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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

                <input
                    type="text"
                    id="date_range"
                    name="date_range"
                    class="form-control"
                    value="{{ $rangeVal }}"
                    placeholder="Pilih tanggal mulai sampai selesai"
                    autocomplete="off">
                
                <input type="hidden" name="start_date" id="start_date" value="{{ $startVal }}">
                <input type="hidden" name="end_date" id="end_date" value="{{ $endVal }}">
                
                <div class="warning-container">
                    {{-- Warning Overlimit Cuti Khusus --}}
                    <div id="special-limit-warning" role="alert" aria-live="polite" class="alert-warning" style="display:none;"></div>
                </div>
            </div>

            {{-- 3. JAM (Kondisional) --}}
            <div class="form-group" id="worktime-field" style="display:none;">
                <label id="worktime-label">Jam Izin</label>
                <div class="time-range-wrapper">
                    <div class="time-input-box">
                        <input
                            type="time"
                            name="start_time"
                            id="start_time_input"
                            class="form-control"
                            value="{{ old('start_time', $leave->start_time ? $leave->start_time->format('H:i') : '') }}">
                    </div>
                    <span id="worktime-separator" class="separator">s/d</span>
                    <div id="end_time_wrapper" class="time-input-box">
                        <input
                            type="time"
                            name="end_time"
                            id="end_time_input"
                            class="form-control"
                            value="{{ old('end_time', $leave->end_time ? $leave->end_time->format('H:i') : '') }}">
                    </div>
                </div>
            </div>

            {{-- 4. PIC PENGGANTI --}}
            <div id="substitute-pic-section" style="display:none; padding: 16px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; margin-bottom: 20px;">
                <p style="margin-top:0; margin-bottom:12px; font-size:14px; font-weight:600; color:#1e4a8d;">
                    Informasi Pendelegasian Tugas
                </p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="substitute_pic">Nama PIC Pengganti</label>
                        <input 
                            type="text" 
                            name="substitute_pic" 
                            id="substitute_pic" 
                            class="form-control" 
                            value="{{ old('substitute_pic', $leave->substitute_pic) }}">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="substitute_phone">Nomor HP PIC</label>
                        <input 
                            type="text" 
                            name="substitute_phone" 
                            id="substitute_phone" 
                            class="form-control" 
                            value="{{ old('substitute_phone', $leave->substitute_phone) }}">
                    </div>
                </div>
            </div>

            {{-- 5. UPLOAD BUKTI --}}
            <div class="form-group">
                <label for="photoInput">Update Bukti Pendukung <span style="font-weight:400; color:#6b7280;">(Opsional)</span></label>
                <div class="file-input-wrapper">
                    <input type="file" name="photo" id="photoInput" class="form-control-file" accept="image/*,.pdf">
                </div>
                
                @if($leave->photo)
                    <div style="margin-top:8px; font-size:13px;">
                        <span style="color:#059669;">✔ File saat ini:</span> 
                        <a href="{{ asset('storage/leave_photos/' . $leave->photo) }}" target="_blank" style="color:#1e4a8d; text-decoration:underline;">Lihat File</a>
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
                <textarea 
                    name="reason" 
                    id="reason"
                    rows="4" 
                    class="form-control" 
                    required
                >{{ old('reason', $leave->reason) }}</textarea>
            </div>

            <div class="form-actions">
                <button class="btn-primary" type="submit">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:8px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Revisi & Teruskan ke HRD
                </button>
            </div>
        </form>
    </div>

    {{-- SCRIPTS & STYLES --}}
    <style>
        .req { color: #dc2626; font-weight: bold; margin-left: 2px; }
        .error-msg { font-size: 12px; color: #dc2626; margin-top: 4px; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
        .alert-warning { background: #fefce8; border: 1px solid #fde047; color: #854d0e; padding: 10px 14px; border-radius: 8px; margin-top: 8px; font-size: 13.5px; line-height: 1.4; }
        
        .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid #f3f4f6; overflow: hidden; max-width: 700px; margin: 0 auto; }
        .card-header { padding: 20px; display: flex; justify-content: space-between; align-items: flex-start; }
        .form-title { margin: 0; font-size: 18px; font-weight: 700; color: #111827; }
        .form-subtitle { margin: 4px 0 0; font-size: 13.5px; color: #6b7280; }
        .divider { height: 1px; background: #f3f4f6; width: 100%; }
        
        .btn-back { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; border: 1px solid #d1d5db; background: #fff; color: #374151; font-size: 13px; font-weight: 500; text-decoration: none; transition: all 0.2s; white-space: nowrap; }
        .btn-back:hover { background: #f9fafb; border-color: #9ca3af; }
        
        .btn-primary { padding: 12px 24px; background: #1e4a8d; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s; width: 100%; display: flex; align-items: center; justify-content: center; }
        .btn-primary:hover { background: #163a75; }

        .form-content { padding: 24px; }
        .form-group { margin-bottom: 20px; display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13.5px; font-weight: 600; color: #374151; }
        .section-label { display: block; margin-bottom: 8px; font-size: 14px; color: #111827; }

        .form-control { padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; width: 100%; outline: none; background: #fff; color: #111827; font-family: inherit; }
        .form-control:focus { border-color: #1e4a8d; box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1); }
        textarea.form-control { resize: vertical; min-height: 100px; line-height: 1.5; }

        .radio-group-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 10px; }
        .radio-card { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.2s; background: #fff; }
        .radio-card:hover { border-color: #1e4a8d; background: #f0f4ff; }
        .radio-card input[type="radio"] { accent-color: #1e4a8d; width: 16px; height: 16px; margin: 0; cursor: pointer; }
        .radio-label { font-size: 13.5px; color: #374151; font-weight: 500; line-height: 1.3; }

        .time-range-wrapper { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .time-input-box { flex: 1; min-width: 120px; }
        .separator { color: #6b7280; font-size: 13px; font-weight: 500; }

        .file-input-wrapper { border: 1px dashed #cbd5e1; padding: 12px; border-radius: 8px; background: #f8fafc; }
        .form-control-file { width: 100%; font-size: 13px; }
        .preview-container { display: none; margin-top: 12px; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb; }
        .preview-label { font-size: 12px; font-weight: 600; color: #4b5563; margin: 0 0 6px 0; }
        .preview-container img { max-width: 100%; max-height: 300px; border-radius: 6px; display: block; }

        .form-actions { margin-top: 32px; padding-top: 20px; border-top: 1px solid #f3f4f6; display: flex; justify-content: flex-end; }
        .info-badge { display: inline-flex; align-items: center; gap: 6px; margin-top: 8px; background: #dbeafe; color: #1e40af; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; }

        @media (max-width: 600px) {
            .card-header { flex-direction: column; gap: 12px; }
            .btn-back { align-self: flex-start; }
            .form-content { padding: 16px; }
            .radio-group-container { grid-template-columns: 1fr; }
            .time-range-wrapper { gap: 8px; }
            .separator { display: none !important; }
            .time-input-box { width: 100%; flex: none; }
            .form-actions .btn-primary { width: 100%; }
            #substitute-pic-section > div { grid-template-columns: 1fr !important; }
        }
    </style>

    <script>
        (function() {
            const typeRadios = document.querySelectorAll('input[name="type"]');
            
            const IZIN_TELAT = @json(\App\Enums\LeaveType::IZIN_TELAT->value);
            const IZIN_TENGAH_KERJA = @json(\App\Enums\LeaveType::IZIN_TENGAH_KERJA->value);
            const IZIN_PULANG_AWAL = @json(\App\Enums\LeaveType::IZIN_PULANG_AWAL->value);
            const CUTI = @json(\App\Enums\LeaveType::CUTI->value);
            const CUTI_KHUSUS = @json(\App\Enums\LeaveType::CUTI_KHUSUS->value);
            const SAKIT = @json(\App\Enums\LeaveType::SAKIT->value);

            // Elements
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
            const picNameInput = document.getElementById('substitute_pic');
            const picPhoneInput = document.getElementById('substitute_phone');

            function selectedType() {
                const r = document.querySelector('input[name="type"]:checked');
                return r ? r.value : null;
            }

            // Function cek limit (Copy dari create.blade.php tapi simplified)
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
                const diffTime = Math.abs(endDate - startDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; 

                if (maxDays > 0 && diffDays > maxDays) {
                    specialLimitWarning.style.display = 'block';
                    specialLimitWarning.innerHTML = `Durasi revisi <b>${diffDays} hari</b> melebihi batas <b>${maxDays} hari</b>. <br>Peringatan ini akan dicatat otomatis.`;
                } else {
                    specialLimitWarning.style.display = 'none';
                }
            }

            function toggleSection() {
                const val = selectedType();

                // 1. Cuti Khusus
                if (val === CUTI_KHUSUS) {
                    specialLeaveContainer.style.display = 'block';
                    if(specialLeaveSelect) specialLeaveSelect.required = true;
                    checkSpecialLeaveLimit();
                } else {
                    specialLeaveContainer.style.display = 'none';
                    if(specialLeaveSelect) {
                        specialLeaveSelect.required = false;
                        // Jangan reset value saat edit, karena user mungkin cuma klik2 radio
                    }
                    if(specialLeaveBadge) specialLeaveBadge.style.display = 'none';
                    if(specialLimitWarning) specialLimitWarning.style.display = 'none';
                }

                // 2. PIC Pengganti
                const needPic = (val === CUTI || val === CUTI_KHUSUS || val === SAKIT);
                if (picSection) {
                    picSection.style.display = needPic ? 'block' : 'none';
                }

                // 3. Jam Kerja
                const isTengahKerja = (val === IZIN_TENGAH_KERJA);
                const isPulangAwal = (val === IZIN_PULANG_AWAL);
                const isTelat = (val === IZIN_TELAT);
                const showWorktime = isTengahKerja || isPulangAwal || isTelat;

                if (worktimeField) {
                    worktimeField.style.display = showWorktime ? 'block' : 'none';
                }

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

            // Update Badge Cuti Khusus saat ganti dropdown
            if(specialLeaveSelect) {
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
                
                // Trigger change event manual saat load page utk set badge awal
                if(specialLeaveSelect.value) {
                    specialLeaveSelect.dispatchEvent(new Event('change'));
                }
            }

            // Listeners
            typeRadios.forEach(r => r.addEventListener('change', toggleSection));
            document.getElementById('start_date').addEventListener('change', checkSpecialLeaveLimit);
            document.getElementById('end_date').addEventListener('change', checkSpecialLeaveLimit);

            // Init
            toggleSection();
        })();

        // Preview Image
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('photoInput');
            const previewContainer = document.getElementById('photoPreviewContainer');
            const previewImg = document.getElementById('photoPreview');

            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file && file.type && file.type.startsWith('image/')) {
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

        // Init Flatpickr
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
                        // Trigger change utk update warning
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