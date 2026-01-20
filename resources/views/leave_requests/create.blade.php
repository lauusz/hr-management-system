<x-app title="Buat Pengajuan Izin">
    @php
        $joinDate = auth()->user()->profile->tgl_bergabung ?? null;
        $underOneYear = false;

        if ($joinDate) {
            $start = \Carbon\Carbon::parse($joinDate)->startOfDay();
            $end = \Carbon\Carbon::today();
            $underOneYear = $start->diffInYears($end) < 1;
        }

        $oldStart = old('start_date');
        $oldEnd = old('end_date');
        $oldRange = '';
        if ($oldStart && $oldEnd) {
            $oldRange = $oldStart . ' sampai ' . $oldEnd;
        } elseif ($oldStart) {
            $oldRange = $oldStart;
        }

        $shiftEndDisplay = null;
        try {
            $empShift = \App\Models\EmployeeShift::with('shift')
                ->where('user_id', auth()->id())
                ->first();
            if ($empShift && $empShift->shift && $empShift->shift->end_time) {
                $shiftEndDisplay = \Carbon\Carbon::parse($empShift->shift->end_time)->format('H:i');
            }
        } catch (\Throwable $e) {
            $shiftEndDisplay = null;
        }

        // [DATA CUTI KHUSUS - Label disesuaikan permintaan]
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
    @endphp

    @if ($errors->any())
        <div class="alert-error">
            <ul style="margin:0; padding-left:16px;">
                {{ $errors->first() }}
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="form-title">Formulir Izin / Cuti</h3>
                <p class="form-subtitle">Isi data di bawah untuk mengajukan izin ketidakhadiran.</p>
            </div>
            <a href="{{ route('leave-requests.index') }}" class="btn-back">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                Kembali
            </a>
        </div>

        <div class="divider"></div>

        <form class="form-content" method="POST" action="{{ route('leave-requests.store') }}" enctype="multipart/form-data">
            @csrf

            <input type="hidden" id="shift_end_time" value="{{ $shiftEndDisplay }}">

            {{-- 1. JENIS PENGAJUAN --}}
            <div class="form-group">
                <label class="section-label">Jenis Pengajuan <span class="req">*</span></label>
                <div class="radio-group-container">
                    @php
                        $user = auth()->user();
                        $roleValue = $user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role;
                        $role = strtoupper((string) $roleValue);
                        $isSpv = in_array($role, ['SUPERVISOR', 'SPV'], true);

                        $offRemaining = null;
                        if (isset($offSpvInfo) && is_array($offSpvInfo) && array_key_exists('remaining', $offSpvInfo)) {
                            $offRemaining = (int) $offSpvInfo['remaining'];
                        }
                    @endphp

                    @foreach (\App\Enums\LeaveType::cases() as $case)
                        @if ($case->value === \App\Enums\LeaveType::OFF_SPV->value && !$isSpv)
                            @continue
                        @endif

                        @php
                            $label = $case->label();
                            if ($case->value === \App\Enums\LeaveType::OFF_SPV->value && $offRemaining !== null) {
                                $label = $label . ' (sisa ' . $offRemaining . ')';
                            }
                        @endphp

                        <label class="radio-card">
                            <input
                                type="radio"
                                name="type"
                                value="{{ $case->value }}"
                                @if ($loop->first) required @endif
                                @checked(old('type') === $case->value)
                            >
                            <span class="radio-label">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                
                {{-- DROPDOWN CUTI KHUSUS --}}
                <div id="special-leave-container" style="display: none; margin-top: 12px; padding: 12px; background: #eff6ff; border: 1px solid #dbeafe; border-radius: 8px;">
                    <label for="special_leave_detail" style="font-size:13px; color:#1e4a8d; display:block; margin-bottom:6px;">
                        Pilih Kategori Cuti Khusus <span class="req">*</span>
                    </label>
                    <select name="special_leave_detail" id="special_leave_detail" class="form-control">
                        <option value="" selected disabled>-- Pilih Alasan --</option>
                        @foreach($specialLeaveList as $sl)
                            <option value="{{ $sl['id'] }}" data-days="{{ $sl['days'] }}" @selected(old('special_leave_detail') == $sl['id'])>
                                {{ $sl['label'] }}
                            </option>
                        @endforeach
                    </select>
                    {{-- Badge Info Maksimal Hari --}}
                    <div id="special-leave-badge" class="info-badge" style="display: none;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span id="special-leave-text">Maksimal 2 Hari</span>
                    </div>
                </div>

                @error('type') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            {{-- 2. PERIODE --}}
            <div class="form-group">
                <label for="date_range">Periode Izin <span class="req">*</span></label>
                <input
                    type="text"
                    id="date_range"
                    name="date_range"
                    class="form-control"
                    value="{{ $oldRange }}"
                    placeholder="Pilih tanggal mulai sampai selesai"
                    autocomplete="off">
                <input type="hidden" name="start_date" id="start_date" value="{{ $oldStart }}">
                <input type="hidden" name="end_date" id="end_date" value="{{ $oldEnd }}">
                
                <div class="warning-container">
                    <small id="cuti-rule" style="display:none; color:#6b7280; margin-top:4px; display:block;"></small>
                    
                    {{-- Warning H-7 (Cuti Tahunan) --}}
                    <div id="h7-warning" role="alert" aria-live="polite" class="alert-warning" style="display:none;"></div>
                    
                    {{-- [BARU] Warning Overlimit (Cuti Khusus) --}}
                    <div id="special-limit-warning" role="alert" aria-live="polite" class="alert-warning" style="display:none;"></div>

                    {{-- Warning Masa Kerja --}}
                    <div id="tenure-warning" 
                         role="alert" 
                         aria-live="polite" 
                         data-under-one-year="{{ $underOneYear ? '1' : '0' }}" 
                         class="alert-warning" 
                         style="display:none;">
                    </div>
                </div>

                @error('start_date') <div class="error-msg">{{ $message }}</div> @enderror
                @error('end_date') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            {{-- 3. INPUT JAM (Reused: Tengah Kerja / Pulang Awal / Izin Telat) --}}
            <div class="form-group" id="worktime-field" style="display:none;">
                <label id="worktime-label">Jam Izin</label>
                <div class="time-range-wrapper">
                    <div class="time-input-box">
                        <input
                            type="time"
                            name="start_time"
                            id="start_time_input"
                            class="form-control"
                            value="{{ old('start_time') }}">
                    </div>
                    <span id="worktime-separator" class="separator">s/d</span>
                    <div id="end_time_wrapper" class="time-input-box">
                        <input
                            type="time"
                            name="end_time"
                            id="end_time_input"
                            class="form-control"
                            value="{{ old('end_time') }}">
                    </div>
                </div>
                <div id="pulang-info" class="helper-text" style="display:none;"></div>
                @error('start_time') <div class="error-msg">{{ $message }}</div> @enderror
                @error('end_time') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            {{-- 4. INFO PIC PENGGANTI (Untuk Cuti/Sakit) --}}
            <div id="substitute-pic-section" style="display:none; padding: 16px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; margin-bottom: 20px;">
                <p style="margin-top:0; margin-bottom:12px; font-size:14px; font-weight:600; color:#1e4a8d;">
                    Informasi Pendelegasian Tugas
                </p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="substitute_pic">Nama PIC Pengganti <span class="req">*</span></label>
                        <input 
                            type="text" 
                            name="substitute_pic" 
                            id="substitute_pic" 
                            class="form-control" 
                            placeholder="Nama rekan pengganti"
                            value="{{ old('substitute_pic') }}">
                         @error('substitute_pic') <div class="error-msg">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="substitute_phone">Nomor HP PIC <span class="req">*</span></label>
                        <input 
                            type="number" 
                            name="substitute_phone" 
                            id="substitute_phone" 
                            class="form-control" 
                            placeholder="Contoh: 0812..."
                            value="{{ old('substitute_phone') }}">
                        @error('substitute_phone') <div class="error-msg">{{ $message }}</div> @enderror
                    </div>
                </div>
                <small class="helper-text" style="display:block; margin-top:8px;">
                    Wajib diisi untuk keperluan koordinasi selama Anda tidak ada di tempat.
                </small>
            </div>

            {{-- 5. LOKASI (Hidden Field - Khusus Telat) --}}
            <div id="location" style="display:none;">
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
                <input type="hidden" name="accuracy_m" id="accuracy_m">
                <input type="hidden" name="location_captured_at" id="location_captured_at">
            </div>

            {{-- 6. UPLOAD BUKTI --}}
            <div class="form-group">
                <label for="photoInput">Bukti Pendukung <span id="photo-req-indicator" class="req" style="display:none">*</span></label>
                <div class="file-input-wrapper">
                    <input
                        type="file"
                        name="photo"
                        id="photoInput"
                        class="form-control-file"
                        accept=".jpg,.jpeg,.png,.webp,.heic,.heif,.pdf,.doc,.docx,.xls,.xlsx">
                </div>
                <small class="helper-text">
                    Format: Gambar (JPG, PNG, HEIC) atau Dokumen (PDF, DOCX). Maks 8 MB.
                    Khusus izin telat, gambar akan dikompres otomatis.
                </small>
                @error('photo') <div class="error-msg">{{ $message }}</div> @enderror

                <div id="photoPreviewContainer" class="preview-container">
                    <p class="preview-label">Preview Foto:</p>
                    <img id="photoPreview" src="#" alt="Preview foto">
                </div>
            </div>

            {{-- 7. ALASAN --}}
            <div class="form-group">
                <label for="reason">Alasan / Keterangan <span class="req">*</span></label>
                <textarea 
                    name="reason" 
                    id="reason"
                    rows="4" 
                    class="form-control" 
                    placeholder="Jelaskan alasan pengajuan izin Anda secara detail..."
                >{{ old('reason') }}</textarea>
                @error('reason') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div class="form-actions">
                <button class="btn-primary" type="submit">Kirim Pengajuan</button>
            </div>
        </form>
    </div>
    <style>
        /* Base Utils */
        .req { color: #dc2626; font-weight: bold; margin-left: 2px; }
        .error-msg { font-size: 12px; color: #dc2626; margin-top: 4px; }

        /* Alert Styling */
        .alert-error {
            background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
            padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px;
        }
        .alert-warning {
            background: #fefce8; border: 1px solid #fde047; color: #854d0e;
            padding: 10px 14px; border-radius: 8px; margin-top: 8px; font-size: 13.5px; line-height: 1.4;
        }

        /* Card System */
        .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid #f3f4f6; overflow: hidden; max-width: 700px; margin: 0 auto; }
        .card-header { padding: 20px; display: flex; justify-content: space-between; align-items: flex-start; }
        .form-title { margin: 0; font-size: 18px; font-weight: 700; color: #111827; }
        .form-subtitle { margin: 4px 0 0; font-size: 13.5px; color: #6b7280; }
        .divider { height: 1px; background: #f3f4f6; width: 100%; }
        
        /* Buttons */
        .btn-back {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 14px; border-radius: 8px; border: 1px solid #d1d5db;
            background: #fff; color: #374151; font-size: 13px; font-weight: 500;
            text-decoration: none; transition: all 0.2s; white-space: nowrap;
        }
        .btn-back:hover { background: #f9fafb; border-color: #9ca3af; }
        
        .btn-primary {
            padding: 12px 24px; background: #1e4a8d; color: #fff; border: none; border-radius: 8px;
            font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s; width: 100%;
        }
        .btn-primary:hover { background: #163a75; }

        /* Form Layout */
        .form-content { padding: 24px; }
        .form-group { margin-bottom: 20px; display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13.5px; font-weight: 600; color: #374151; }
        
        /* Section Label for Radios */
        .section-label { display: block; margin-bottom: 8px; font-size: 14px; color: #111827; }

        /* Inputs */
        .form-control {
            padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;
            font-size: 14px; width: 100%; outline: none; transition: border-color 0.2s, box-shadow 0.2s;
            background: #fff; color: #111827; font-family: inherit;
        }
        .form-control:focus { border-color: #1e4a8d; box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1); }
        textarea.form-control { resize: vertical; min-height: 100px; line-height: 1.5; }

        /* Radio Grid Styling (Cards) */
        .radio-group-container {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 10px;
        }
        .radio-card {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px;
            cursor: pointer; transition: all 0.2s; background: #fff;
        }
        .radio-card:hover { border-color: #1e4a8d; background: #f0f4ff; }
        .radio-card input[type="radio"] {
            accent-color: #1e4a8d; width: 16px; height: 16px; margin: 0; cursor: pointer;
        }
        .radio-label { font-size: 13.5px; color: #374151; font-weight: 500; line-height: 1.3; }

        /* Time Range Inputs */
        .time-range-wrapper { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .time-input-box { flex: 1; min-width: 120px; }
        .separator { color: #6b7280; font-size: 13px; font-weight: 500; }

        /* File Input */
        .file-input-wrapper { border: 1px dashed #cbd5e1; padding: 12px; border-radius: 8px; background: #f8fafc; }
        .form-control-file { width: 100%; font-size: 13px; }
        .helper-text { font-size: 12px; color: #6b7280; margin-top: 4px; line-height: 1.4; }

        /* Preview */
        .preview-container { display: none; margin-top: 12px; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb; }
        .preview-label { font-size: 12px; font-weight: 600; color: #4b5563; margin: 0 0 6px 0; }
        .preview-container img { max-width: 100%; max-height: 300px; border-radius: 6px; display: block; }

        /* Actions */
        .form-actions { margin-top: 32px; padding-top: 20px; border-top: 1px solid #f3f4f6; display: flex; justify-content: flex-end; }
        .form-actions .btn-primary { width: auto; min-width: 140px; }

        /* [BARU] Info Badge Style */
        .info-badge {
            display: inline-flex; align-items: center; gap: 6px;
            margin-top: 8px;
            background: #dbeafe; color: #1e40af;
            padding: 6px 12px; border-radius: 20px;
            font-size: 13px; font-weight: 600;
        }

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

            // Elemen Special Leave
            const specialLeaveContainer = document.getElementById('special-leave-container');
            const specialLeaveSelect = document.getElementById('special_leave_detail');
            const specialLeaveBadge = document.getElementById('special-leave-badge');
            const specialLeaveText = document.getElementById('special-leave-text');
            const specialLimitWarning = document.getElementById('special-limit-warning'); // [BARU]

            const photoInput = document.getElementById('photoInput');
            const photoReqIndicator = document.getElementById('photo-req-indicator');

            const locationWrapper = document.getElementById('location');
            const latEl = document.getElementById('latitude');
            const lngEl = document.getElementById('longitude');
            const accEl = document.getElementById('accuracy_m');
            const tsEl = document.getElementById('location_captured_at');

            const worktimeField = document.getElementById('worktime-field');
            const worktimeLabel = document.getElementById('worktime-label');
            const startTimeInput = document.getElementById('start_time_input');
            const endTimeInput = document.getElementById('end_time_input');
            const endTimeWrapper = document.getElementById('end_time_wrapper');
            const worktimeSeparator = document.getElementById('worktime-separator');
            const pulangInfo = document.getElementById('pulang-info');
            const shiftEndInput = document.getElementById('shift_end_time');

            const picSection = document.getElementById('substitute-pic-section');
            const picNameInput = document.getElementById('substitute_pic');
            const picPhoneInput = document.getElementById('substitute_phone');

            let isRequestingLocation = false;

            function selectedType() {
                const r = document.querySelector('input[name="type"]:checked');
                return r ? r.value : null;
            }

            function clearLocationValues() {
                if (!locationWrapper) return;
                if (latEl) latEl.value = '';
                if (lngEl) lngEl.value = '';
                if (accEl) accEl.value = '';
                if (tsEl) tsEl.value = '';
            }

            function requestLocationIfNeeded() {
                if (!locationWrapper) return;
                if (!latEl || !lngEl || !accEl || !tsEl) return;
                if (latEl.value && lngEl.value) return;
                if (isRequestingLocation) return;
                if (!('geolocation' in navigator)) return;

                isRequestingLocation = true;

                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        const latitude = pos.coords.latitude;
                        const longitude = pos.coords.longitude;
                        const accuracy = pos.coords.accuracy ?? 0;

                        latEl.value = latitude.toFixed(7);
                        lngEl.value = longitude.toFixed(7);
                        accEl.value = accuracy.toFixed(2);
                        tsEl.value = new Date(pos.timestamp).toISOString().slice(0, 19).replace('T', ' ');

                        isRequestingLocation = false;
                    },
                    function() {
                        isRequestingLocation = false;
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            }

            // [BARU] Fungsi Cek Limit Cuti Khusus
            function checkSpecialLeaveLimit() {
                if (!specialLimitWarning) return;
                
                // Pastikan tipe Cuti Khusus aktif & ada pilihan kategori
                if (selectedType() !== CUTI_KHUSUS || !specialLeaveSelect.value) {
                    specialLimitWarning.style.display = 'none';
                    specialLimitWarning.textContent = '';
                    return;
                }

                const selectedOption = specialLeaveSelect.options[specialLeaveSelect.selectedIndex];
                const maxDays = parseInt(selectedOption.getAttribute('data-days')) || 0;
                
                // Ambil tanggal dari input flatpickr (hidden input)
                const startStr = document.getElementById('start_date').value;
                const endStr = document.getElementById('end_date').value;

                if (!startStr || !endStr) return;

                const startDate = new Date(startStr);
                const endDate = new Date(endStr);
                
                // Hitung selisih hari (inklusif)
                const diffTime = Math.abs(endDate - startDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; 

                if (maxDays > 0 && diffDays > maxDays) {
                    specialLimitWarning.style.display = 'block';
                    specialLimitWarning.innerHTML = `Durasi pengajuan <b>${diffDays} hari</b> melebihi batas maksimal <b>${maxDays} hari</b> untuk kategori ini. <br>Sistem akan mencatat kelebihan hari ini sebagai catatan.`;
                } else {
                    specialLimitWarning.style.display = 'none';
                    specialLimitWarning.textContent = '';
                }
            }

            function toggleSection() {
                const val = selectedType();

                // 1. LOGIK CUTI KHUSUS
                if (val === CUTI_KHUSUS) {
                    specialLeaveContainer.style.display = 'block';
                    if(specialLeaveSelect) specialLeaveSelect.required = true;
                    // Trigger cek limit saat ganti tipe
                    checkSpecialLeaveLimit();
                } else {
                    specialLeaveContainer.style.display = 'none';
                    if(specialLeaveSelect) {
                        specialLeaveSelect.required = false;
                        specialLeaveSelect.value = ""; 
                    }
                    if(specialLeaveBadge) specialLeaveBadge.style.display = 'none';
                    if(specialLimitWarning) specialLimitWarning.style.display = 'none';
                }

                // 2. LOGIK LOKASI & FOTO 
                const isTelat = (val === IZIN_TELAT);
                if (isTelat) {
                    requestLocationIfNeeded();
                    if(photoInput) photoInput.required = true;
                    if(photoReqIndicator) photoReqIndicator.style.display = 'inline';
                } else {
                    clearLocationValues();
                    if(photoInput) photoInput.required = false;
                    if(photoReqIndicator) photoReqIndicator.style.display = 'none';
                }

                // 3. LOGIK PIC PENGGANTI
                const needPic = (val === CUTI || val === CUTI_KHUSUS || val === SAKIT);
                if (picSection) {
                    if (needPic) {
                        picSection.style.display = 'block';
                        if(picNameInput) picNameInput.required = true;
                        if(picPhoneInput) picPhoneInput.required = true;
                    } else {
                        picSection.style.display = 'none';
                        if(picNameInput) picNameInput.required = false;
                        if(picPhoneInput) picPhoneInput.required = false;
                    }
                }

                // 4. LOGIK JAM KERJA
                const isTengahKerja = (val === IZIN_TENGAH_KERJA);
                const isPulangAwal = (val === IZIN_PULANG_AWAL);
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
                    if (pulangInfo) { pulangInfo.style.display = 'none'; pulangInfo.textContent = ''; }

                } else if (isPulangAwal) {
                    if (worktimeLabel) worktimeLabel.innerHTML = 'Jam Pulang';
                    if (worktimeSeparator) worktimeSeparator.style.display = 'none';
                    if (endTimeWrapper) endTimeWrapper.style.display = 'none';
                    startTimeInput.required = true;
                    endTimeInput.required = false;
                    endTimeInput.value = '';
                    if (pulangInfo) {
                        const shiftEnd = shiftEndInput ? shiftEndInput.value : '';
                        if (shiftEnd) {
                            pulangInfo.style.display = 'block';
                            pulangInfo.textContent = 'Jam pulang shift Anda: ' + shiftEnd + '. Izin pulang awal maksimal 1 jam sebelum jam pulang.';
                        } else {
                            pulangInfo.style.display = 'block';
                            pulangInfo.textContent = 'Izin pulang awal maksimal 1 jam sebelum jam pulang shift.';
                        }
                    }

                } else if (isTelat) {
                    if (worktimeLabel) worktimeLabel.innerHTML = 'Estimasi Jam Tiba';
                    if (worktimeSeparator) worktimeSeparator.style.display = 'none';
                    if (endTimeWrapper) endTimeWrapper.style.display = 'none';
                    startTimeInput.required = true;
                    endTimeInput.required = false;
                    endTimeInput.value = '';
                    if (pulangInfo) { pulangInfo.style.display = 'none'; pulangInfo.textContent = ''; }

                } else {
                    startTimeInput.required = false;
                    endTimeInput.required = false;
                    startTimeInput.value = '';
                    endTimeInput.value = '';
                    if (pulangInfo) { pulangInfo.style.display = 'none'; pulangInfo.textContent = ''; }
                }
            }

            // Event Listener Dropdown Cuti Khusus
            if(specialLeaveSelect) {
                specialLeaveSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const days = selectedOption.getAttribute('data-days');
                    
                    // Update Badge
                    if (days) {
                        specialLeaveBadge.style.display = 'inline-flex';
                        specialLeaveText.textContent = 'Maksimal ' + days + ' Hari';
                    } else {
                        specialLeaveBadge.style.display = 'none';
                    }

                    // [BARU] Update Warning saat ganti kategori
                    checkSpecialLeaveLimit();
                });
            }

            // Event listener hidden input tanggal (di-trigger oleh flatpickr)
            document.getElementById('start_date').addEventListener('change', checkSpecialLeaveLimit);
            document.getElementById('end_date').addEventListener('change', checkSpecialLeaveLimit);

            typeRadios.forEach(function(r) {
                r.addEventListener('change', toggleSection);
            });
            
            toggleSection();
        })();
    </script>

    <script>
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
    </script>

    <x-modal
        id="info-izin-telat"
        title="Izin Terlambat"
        type="info"
        cancelLabel="Tutup">
        <p style="margin:0 0 6px 0;">
            Pengajuan izin terlambat Anda sudah dikirim ke HRD.
        </p>
        <p style="margin:0;font-size:0.9rem;opacity:.9;">
            Silakan menunggu proses pengecekan. Status pengajuan dapat Anda lihat pada daftar riwayat pengajuan izin.
        </p>
    </x-modal>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            (function() {
                const CUTI_VALUE = @json(\App\Enums\LeaveType::CUTI->value);
                const startInput = document.getElementById('start_date');
                const ruleEl = document.getElementById('cuti-rule');
                const warnEl = document.getElementById('h7-warning');
                const tenureWarnEl = document.getElementById('tenure-warning');
                const typeRadios = document.querySelectorAll('input[name="type"]');
                var shouldShowIzinTelatPopup = !!@json(session('show_izin_telat_popup'));

                const isUnderOneYear = tenureWarnEl ?
                    tenureWarnEl.getAttribute('data-under-one-year') === '1' :
                    false;

                function parseYMD(ymd) {
                    if (!ymd) return null;
                    const parts = ymd.split('-').map(Number);
                    const dt = new Date(parts[0], parts[1] - 1, parts[2]);
                    dt.setHours(0, 0, 0, 0);
                    return dt;
                }

                function todayStart() {
                    const t = new Date();
                    t.setHours(0, 0, 0, 0);
                    return t;
                }

                function boundaryDateH7() {
                    const t = todayStart();
                    const b = new Date(t);
                    b.setDate(b.getDate() + 7);
                    return b;
                }

                function formatID(d) {
                    return d.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    });
                }

                function getSelectedType() {
                    const r = document.querySelector('input[name="type"]:checked');
                    return r ? r.value : null;
                }

                function isCutiSelected() {
                    return getSelectedType() === CUTI_VALUE;
                }

                function updateTenureWarning() {
                    if (!tenureWarnEl) return;

                    if (isCutiSelected() && isUnderOneYear) {
                        tenureWarnEl.style.display = 'block';
                        tenureWarnEl.textContent = 'Masa kerja < 1 tahun, pengajuan cuti akan dipotong gaji.';
                    } else {
                        tenureWarnEl.style.display = 'none';
                        tenureWarnEl.textContent = '';
                    }
                }

                function renderRuleVisibility() {
                    if (!ruleEl || !warnEl) return;

                    if (isCutiSelected()) {
                        ruleEl.style.display = 'block';
                        ruleEl.innerHTML = 'Ketentuan: pengajuan minimal H-7 (≥ <b>' + formatID(boundaryDateH7()) + '</b>).';
                        updateWarning();
                        updateTenureWarning();
                    } else {
                        ruleEl.style.display = 'none';
                        ruleEl.textContent = '';
                        warnEl.style.display = 'none';
                        warnEl.textContent = '';
                        updateTenureWarning();
                    }
                }

                function updateWarning() {
                    if (!isCutiSelected()) {
                        warnEl.style.display = 'none';
                        warnEl.textContent = '';
                        return;
                    }

                    const today = todayStart();
                    const start = parseYMD(startInput ? startInput.value : '');
                    if (!(start instanceof Date) || isNaN(start)) {
                        warnEl.style.display = 'none';
                        warnEl.textContent = '';
                        return;
                    }

                    const diffDays = Math.round((start - today) / (1000 * 60 * 60 * 24));
                    if (diffDays < 7 && diffDays >= 0) {
                        warnEl.style.display = 'block';
                        warnEl.textContent =
                            'Pengajuan H-' + diffDays + ' (kurang dari H-7). ' +
                            'Pengajuan tetap bisa diproses namun mungkin ada konsekuensi administrasi.';
                    } else {
                        warnEl.style.display = 'none';
                        warnEl.textContent = '';
                    }
                }

                if (startInput) {
                    startInput.addEventListener('input', updateWarning);
                    startInput.addEventListener('change', updateWarning);
                    startInput.addEventListener('focus', updateWarning);
                }

                typeRadios.forEach(function(r) {
                    r.addEventListener('change', function() {
                        renderRuleVisibility();
                    });
                });

                renderRuleVisibility();
                updateTenureWarning();

                var modal = document.getElementById('info-izin-telat');
                if (modal) {
                    if (shouldShowIzinTelatPopup) {
                        modal.style.display = 'flex';
                        document.body.style.overflow = 'hidden';
                    }

                    var closeButtons = modal.querySelectorAll('[data-modal-close="true"]');
                    closeButtons.forEach(function(btn) {
                        btn.addEventListener('click', function() {
                            window.location.href = '{{ route('leave-requests.index') }}';
                        });
                    });
                }
            })();

            var rangeInput = document.getElementById('date_range');
            var startHidden = document.getElementById('start_date');
            var endHidden = document.getElementById('end_date');

            if (typeof flatpickr === 'function' && rangeInput) {
                flatpickr(rangeInput, {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    locale: {
                        rangeSeparator: ' sampai '
                    },
                    onChange: function(selectedDates, dateStr) {
                        if (!dateStr) {
                            startHidden.value = '';
                            endHidden.value = '';
                            startHidden.dispatchEvent(new Event('change'));
                            // [BARU] Trigger event untuk update warning
                            endHidden.dispatchEvent(new Event('change'));
                            return;
                        }
                        var parts = dateStr.split(' sampai ');
                        if (parts.length === 1) {
                            startHidden.value = parts[0];
                            endHidden.value = parts[0];
                        } else {
                            startHidden.value = parts[0];
                            endHidden.value = parts[1];
                        }
                        startHidden.dispatchEvent(new Event('change'));
                        // [BARU] Trigger event untuk update warning
                        endHidden.dispatchEvent(new Event('change'));
                    }
                });
            }
        });
    </script>
    @endpush

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</x-app>