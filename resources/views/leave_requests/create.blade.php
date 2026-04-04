<x-app title="Buat Pengajuan Izin">
    @php
        $user = auth()->user();
        $joinDate = $user->profile->tgl_bergabung ?? null;
        $underOneYear = false;
        $leaveBalance = $user->leave_balance ?? 0;

        if ($joinDate) {
            $start = \Carbon\Carbon::parse($joinDate)->startOfDay();
            $end = \Carbon\Carbon::today();
            $underOneYear = $start->diffInYears($end) < 1;
        }

        $roleStr = strtoupper($user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role);
        $fiveDayRoles = ['MANAGER'];
        $isFiveDayWorkWeek = in_array($roleStr, $fiveDayRoles);

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

        $roleValue = $user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role;
        $role = strtoupper((string) $roleValue);
        $isSpv = in_array($role, ['SUPERVISOR', 'SPV'], true);

        $offRemaining = null;
        if (isset($offSpvInfo) && is_array($offSpvInfo) && array_key_exists('remaining', $offSpvInfo)) {
            $offRemaining = (int) $offSpvInfo['remaining'];
        }
    @endphp

    {{-- ============================================== --}}
    {{-- PAGE HEADER --}}
    {{-- ============================================== --}}
    <div class="page-header">
        <a href="{{ route('leave-requests.index') }}" class="back-btn">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="page-header-text">
            <h1 class="page-title">Buat Pengajuan</h1>
            <p class="page-subtitle">Ajukan izin atau cuti ketidakhadiran</p>
        </div>
    </div>

    {{-- ============================================== --}}
    {{-- ERROR ALERT --}}
    {{-- ============================================== --}}
    @if ($errors->any())
        <div class="alert-error">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    {{-- ============================================== --}}
    {{-- FORM --}}
    {{-- ============================================== --}}
    <form id="form-create-izin" method="POST" action="{{ route('leave-requests.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="shift_end_time" value="{{ $shiftEndDisplay }}">

        {{-- ============================================== --}}
        {{-- SECTION: JENIS PENGAJUAN --}}
        {{-- ============================================== --}}
        <div class="form-section">
            <div class="section-header">
                <div class="section-number">1</div>
                <div class="section-title">Jenis Pengajuan</div>
            </div>

            <div class="type-grid">
                @foreach (\App\Enums\LeaveType::cases() as $case)
                    @if ($case->value === \App\Enums\LeaveType::OFF_SPV->value && !$isSpv)
                        @continue
                    @endif

                    @php
                        $label = $case->label();
                        if ($case->value === \App\Enums\LeaveType::OFF_SPV->value && $offRemaining !== null) {
                            $label = $label . ' (sisa ' . $offRemaining . ')';
                        }
                        if ($case->value === \App\Enums\LeaveType::CUTI->value) {
                            $label = $label . ' (Sisa: ' . $leaveBalance . ')';
                        }

                        // Icon & color for each type
                        $typeIcon = '';
                        $typeColor = 'type-default';
                        if ($case->value === \App\Enums\LeaveType::CUTI->value) {
                            $typeIcon = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
                            $typeColor = 'type-cuti';
                        } elseif ($case->value === \App\Enums\LeaveType::CUTI_KHUSUS->value) {
                            $typeIcon = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>';
                            $typeColor = 'type-cuti-khusus';
                        } elseif ($case->value === \App\Enums\LeaveType::SAKIT->value) {
                            $typeIcon = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>';
                            $typeColor = 'type-sakit';
                        } elseif ($case->value === \App\Enums\LeaveType::IZIN->value) {
                            $typeIcon = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
                            $typeColor = 'type-izin';
                        } elseif ($case->value === \App\Enums\LeaveType::IZIN_TELAT->value) {
                            $typeIcon = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                            $typeColor = 'type-telat';
                        } elseif ($case->value === \App\Enums\LeaveType::IZIN_PULANG_AWAL->value) {
                            $typeIcon = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>';
                            $typeColor = 'type-pulang';
                        } elseif ($case->value === \App\Enums\LeaveType::IZIN_TENGAH_KERJA->value) {
                            $typeIcon = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                            $typeColor = 'type-tenaga';
                        } elseif ($case->value === \App\Enums\LeaveType::OFF_SPV->value) {
                            $typeIcon = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>';
                            $typeColor = 'type-off';
                        } else {
                            $typeIcon = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
                        }
                    @endphp

                    <label class="type-card {{ $typeColor }}">
                        <input type="radio" name="type" value="{{ $case->value }}" @if ($loop->first) required @endif @checked(old('type') === $case->value)>
                        <div class="type-card-content">
                            <div class="type-icon">{!! $typeIcon !!}</div>
                            <div class="type-label">{{ $label }}</div>
                        </div>
                        <div class="type-check">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </label>
                @endforeach
            </div>

            {{-- Balance Info for CUTI --}}
            <div id="balance-info-container" class="info-box" style="display:none;">
                @if($leaveBalance > 0)
                    <div class="info-box-content">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <strong>Sisa Cuti: {{ $leaveBalance }} Hari</strong>
                            <p>Cuti akan berkurang otomatis setelah disetujui.</p>
                        </div>
                    </div>
                @else
                    <div class="info-box-content danger">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <strong>Saldo Cuti Habis</strong>
                            <p>Pilih "Izin (Potong Gaji)" atau tipe lain.</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Special Leave Dropdown --}}
            <div id="special-leave-container" class="special-leave-box" style="display:none;">
                <label for="special_leave_detail" class="special-label">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Kategori Cuti Khusus <span class="required">*</span>
                </label>
                <select name="special_leave_detail" id="special_leave_detail" class="form-select">
                    <option value="" selected disabled>-- Pilih Alasan --</option>
                    @foreach($specialLeaveList as $sl)
                        <option value="{{ $sl['id'] }}" data-days="{{ $sl['days'] }}" @selected(old('special_leave_detail') == $sl['id'])>
                            {{ $sl['label'] }} ({{ $sl['days'] }} hari)
                        </option>
                    @endforeach
                </select>
                <div id="special-leave-badge" class="day-badge" style="display:none;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span id="special-leave-text">Maksimal 2 Hari</span>
                </div>
            </div>
        </div>

        {{-- ============================================== --}}
        {{-- SECTION: PERIODE --}}
        {{-- ============================================== --}}
        <div class="form-section">
            <div class="section-header">
                <div class="section-number">2</div>
                <div class="section-title">Periode Izin</div>
            </div>

            <div class="date-input-wrapper">
                <div class="date-input-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <input type="text"
                    id="date_range"
                    name="date_range"
                    class="date-input"
                    value="{{ $oldRange }}"
                    placeholder="Pilih tanggal mulai — selesai"
                    autocomplete="off">
            </div>
            <input type="hidden" name="start_date" id="start_date" value="{{ $oldStart }}">
            <input type="hidden" name="end_date" id="end_date" value="{{ $oldEnd }}">

            <div id="duration-display" class="duration-display" style="display:none;"></div>

            <div class="warnings-container">
                <small id="cuti-rule" class="cuti-rule"></small>
                <div id="h7-warning" class="warning-alert" style="display:none;"></div>
                <div id="special-limit-warning" class="warning-alert" style="display:none;"></div>
                <div id="tenure-warning" class="warning-alert" style="display:none;" data-under-one-year="{{ $underOneYear ? '1' : '0' }}"></div>
            </div>
        </div>

        {{-- ============================================== --}}
        {{-- SECTION: WAKTU (Conditional) --}}
        {{-- ============================================== --}}
        <div class="form-section" id="worktime-field" style="display:none;">
            <div class="section-header">
                <div class="section-number">3</div>
                <div class="section-title">Waktu Izin</div>
            </div>

            <div class="time-grid">
                <div class="time-input-group">
                    <label id="worktime-label" class="time-label">Jam Izin</label>
                    <div class="time-input-wrapper">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <input type="time" name="start_time" id="start_time_input" class="time-input" value="{{ old('start_time') }}">
                    </div>
                </div>

                <div id="end_time_wrapper" class="time-input-group">
                    <label class="time-label">Jam Selesai</label>
                    <div class="time-input-wrapper">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <input type="time" name="end_time" id="end_time_input" class="time-input" value="{{ old('end_time') }}">
                    </div>
                </div>
            </div>

            <div id="pulang-info" class="helper-text" style="display:none;"></div>
        </div>

        {{-- ============================================== --}}
        {{-- SECTION: PIC PENGGANTI (Conditional) --}}
        {{-- ============================================== --}}
        <div class="form-section" id="substitute-pic-section" style="display:none;">
            <div class="section-header">
                <div class="section-number">{{ isset($worktimeField) && $worktimeField ? '4' : '3' }}</div>
                <div class="section-title">PIC Pengganti</div>
            </div>

            <div class="pic-grid">
                <div class="form-group">
                    <label for="substitute_pic" class="form-label">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Nama PIC Pengganti <span class="required">*</span>
                    </label>
                    <input type="text" name="substitute_pic" id="substitute_pic" class="form-input" placeholder="Nama rekan pengganti" value="{{ old('substitute_pic') }}">
                </div>

                <div class="form-group">
                    <label for="substitute_phone" class="form-label">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        No. HP PIC <span class="required">*</span>
                    </label>
                    <input type="tel" name="substitute_phone" id="substitute_phone" class="form-input" placeholder="Contoh: 0812..." value="{{ old('substitute_phone') }}">
                </div>
            </div>
            <p class="helper-text">Wajib diisi untuk koordinasi selama Anda tidak hadir.</p>
        </div>

        {{-- ============================================== --}}
        {{-- SECTION: BUKTI & ALASAN --}}
        {{-- ============================================== --}}
        <div class="form-section">
            <div class="section-header">
                <div class="section-number">{{ isset($worktimeField) && $worktimeField ? '5' : (isset($substitutePicSection) ? '4' : '3') }}</div>
                <div class="section-title">Bukti & Keterangan</div>
            </div>

            <div class="form-group">
                <label for="photoInput" class="form-label">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Bukti Pendukung <span id="photo-req-indicator" class="required" style="display:none">*</span>
                </label>
                <div class="file-upload-box">
                    <input type="file" name="photo" id="photoInput" class="file-input" accept=".jpg,.jpeg,.png,.webp,.heic,.heif,.pdf,.doc,.docx,.xls,.xlsx">
                    <div class="file-upload-content">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span>Klik untuk upload file</span>
                        <small>JPG, PNG, PDF, DOCX (Maks 8MB)</small>
                    </div>
                </div>
                <div id="photoPreviewContainer" class="photo-preview-box" style="display:none;">
                    <img id="photoPreview" src="#" alt="Preview">
                </div>
            </div>

            <div class="form-group">
                <label for="reason" class="form-label">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Alasan / Keterangan <span class="required">*</span>
                </label>
                <textarea name="reason" id="reason" rows="4" class="form-textarea" placeholder="Jelaskan alasan pengajuan Anda secara detail...">{{ old('reason') }}</textarea>
            </div>
        </div>

        {{-- Location fields (hidden) --}}
        <div id="location" style="display:none;">
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            <input type="hidden" name="accuracy_m" id="accuracy_m">
            <input type="hidden" name="location_captured_at" id="location_captured_at">
        </div>

        {{-- ============================================== --}}
        {{-- SUBMIT BUTTON --}}
        {{-- ============================================== --}}
        <div class="form-actions">
            <button class="btn-submit" type="submit" id="btn-submit-izin">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Kirim Pengajuan
            </button>
        </div>
    </form>

    {{-- ============================================== --}}
    {{-- MODALS --}}
    {{-- ============================================== --}}
    <x-modal id="info-izin-telat" title="Izin Terlambat" variant="info" type="info" cancelLabel="Tutup">
        <p style="margin:0 0 6px 0;">Pengajuan izin terlambat Anda sudah dikirim ke HRD.</p>
        <p style="margin:0;font-size:0.9rem;opacity:.9;">Silakan menunggu proses pengecekan.</p>
    </x-modal>

    <x-modal id="duplicate-warning-modal" title="Peringatan" variant="warning" type="info" cancelLabel="Kembali">
        <div id="duplicate-content" style="color: #1e293b; line-height: 1.6;">
            <p style="margin: 0 0 12px 0;"><strong>Anda sudah memiliki pengajuan pada periode tanggal yang sama.</strong></p>
            <div id="duplicate-list" style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px; border-radius: 4px; margin-bottom: 12px;"></div>
            <p style="margin: 0; font-size: 13px; color: #6b7280;">Hubungi HRD untuk menghapus pengajuan duplikat.</p>
        </div>
    </x-modal>

    <style>
        /* ========================================== */
        /* PAGE HEADER */
        /* ========================================== */
        .page-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .back-btn {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            color: #374151;
            text-decoration: none;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .back-btn:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .page-header-text {
            flex: 1;
        }

        .page-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            margin: 0 0 2px;
        }

        .page-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }

        /* ========================================== */
        /* ALERTS */
        /* ========================================== */
        .alert-error {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.875rem;
        }

        .alert-error svg {
            flex-shrink: 0;
            margin-top: 1px;
        }

        .info-box {
            margin-top: 16px;
        }

        .info-box-content {
            display: flex;
            gap: 12px;
            padding: 14px 16px;
            background: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 12px;
            color: #1e40af;
        }

        .info-box-content.danger {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .info-box-content svg {
            flex-shrink: 0;
            margin-top: 2px;
        }

        .info-box-content strong {
            font-weight: 600;
            display: block;
            margin-bottom: 2px;
        }

        .info-box-content p {
            margin: 0;
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .warning-alert {
            padding: 10px 14px;
            background: #fefce8;
            border: 1px solid #fde047;
            border-radius: 10px;
            color: #854d0e;
            font-size: 0.85rem;
            margin-top: 8px;
        }

        .cuti-rule {
            display: block;
            margin-top: 8px;
            color: #6b7280;
            font-size: 0.8rem;
        }

        .helper-text {
            color: #6b7280;
            font-size: 0.8rem;
            margin-top: 8px;
        }

        .day-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            padding: 6px 12px;
            background: #dbeafe;
            color: #1e40af;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* ========================================== */
        /* FORM SECTIONS */
        /* ========================================== */
        .form-section {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 16px;
            border: 1px solid #f3f4f6;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .section-number {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #1e4a8d;
            color: #fff;
            border-radius: 50%;
            font-size: 0.8rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
        }

        /* ========================================== */
        /* TYPE CARDS */
        /* ========================================== */
        .type-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }

        .type-card {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #fff;
        }

        .type-card:hover {
            border-color: #1e4a8d;
            background: #f8faff;
        }

        .type-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .type-card input[type="radio"]:checked + .type-card-content {
            color: #1e4a8d;
        }

        .type-card input[type="radio"]:checked ~ .type-check {
            opacity: 1;
            transform: scale(1);
        }

        .type-card input[type="radio"]:checked ~ .type-card-content .type-icon {
            background: #eef4ff;
            color: #1e4a8d;
        }

        .type-card-content {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        }

        .type-icon {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            border-radius: 10px;
            color: #6b7280;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .type-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            line-height: 1.3;
        }

        .type-check {
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #1e4a8d;
            border-radius: 50%;
            color: #fff;
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .type-cuti input:checked ~ .type-check { background: #1e4a8d; }
        .type-cuti-khusus input:checked ~ .type-check { background: #7c3aed; }
        .type-sakit input:checked ~ .type-check { background: #dc2626; }
        .type-izin input:checked ~ .type-check { background: #ea580c; }
        .type-telat input:checked ~ .type-check { background: #d97706; }
        .type-pulang input:checked ~ .type-check { background: #2563eb; }
        .type-tenaga input:checked ~ .type-check { background: #0891b2; }
        .type-off input:checked ~ .type-check { background: #7c3aed; }

        .type-cuti { border-color: #dbeafe; background: #f8faff; }
        .type-cuti-khusus { border-color: #ede9fe; background: #faf5ff; }
        .type-sakit { border-color: #fee2e2; background: #fff5f5; }
        .type-izin { border-color: #ffedd5; background: #fff7ed; }
        .type-telat { border-color: #fef3c7; background: #fffbeb; }
        .type-pulang { border-color: #dbeafe; background: #eff6ff; }
        .type-tenaga { border-color: #cffafe; background: #ecfeff; }
        .type-off { border-color: #ede9fe; background: #faf5ff; }

        /* ========================================== */
        /* SPECIAL LEAVE BOX */
        /* ========================================== */
        .special-leave-box {
            margin-top: 16px;
            padding: 16px;
            background: #f8faff;
            border: 1px solid #dbeafe;
            border-radius: 12px;
        }

        .special-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e4a8d;
            margin-bottom: 10px;
        }

        .required {
            color: #dc2626;
            font-weight: 700;
        }

        /* ========================================== */
        /* DATE INPUT */
        /* ========================================== */
        .date-input-wrapper {
            position: relative;
        }

        .date-input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            pointer-events: none;
        }

        .date-input {
            width: 100%;
            padding: 14px 14px 14px 46px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.95rem;
            color: #374151;
            background: #fff;
            transition: all 0.2s ease;
            outline: none;
        }

        .date-input:focus {
            border-color: #1e4a8d;
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
        }

        .duration-display {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
            padding: 12px 14px;
            background: #dcfce7;
            border: 1px solid #86efac;
            border-radius: 10px;
            color: #166534;
            font-size: 0.9rem;
            font-weight: 600;
        }

        /* ========================================== */
        /* TIME INPUTS */
        /* ========================================== */
        .time-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .time-input-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .time-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .time-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .time-input-wrapper svg {
            position: absolute;
            left: 12px;
            color: #6b7280;
            pointer-events: none;
        }

        .time-input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            color: #374151;
            background: #fff;
            transition: all 0.2s ease;
            outline: none;
        }

        .time-input:focus {
            border-color: #1e4a8d;
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
        }

        /* ========================================== */
        /* FORM INPUTS */
        /* ========================================== */
        .form-group {
            margin-bottom: 16px;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            color: #374151;
            background: #fff;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-input:focus {
            border-color: #1e4a8d;
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            color: #374151;
            background: #fff;
            transition: all 0.2s ease;
            outline: none;
            cursor: pointer;
        }

        .form-select:focus {
            border-color: #1e4a8d;
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
        }

        .form-textarea {
            width: 100%;
            padding: 14px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            color: #374151;
            background: #fff;
            transition: all 0.2s ease;
            outline: none;
            resize: vertical;
            min-height: 120px;
            line-height: 1.5;
            font-family: inherit;
        }

        .form-textarea:focus {
            border-color: #1e4a8d;
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
        }

        .pic-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        /* ========================================== */
        /* FILE UPLOAD */
        /* ========================================== */
        .file-upload-box {
            position: relative;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .file-upload-box:hover {
            border-color: #1e4a8d;
            background: #f8faff;
        }

        .file-input {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: #6b7280;
        }

        .file-upload-content svg {
            color: #9ca3af;
        }

        .file-upload-content span {
            font-size: 0.95rem;
            font-weight: 500;
            color: #374151;
        }

        .file-upload-content small {
            font-size: 0.8rem;
            color: #9ca3af;
        }

        .photo-preview-box {
            margin-top: 12px;
            padding: 12px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
        }

        .photo-preview-box img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            display: block;
        }

        /* ========================================== */
        /* SUBMIT BUTTON */
        /* ========================================== */
        .form-actions {
            margin-top: 24px;
        }

        .btn-submit {
            width: 100%;
            padding: 16px 24px;
            background: #1e4a8d;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s ease;
        }

        .btn-submit:hover {
            background: #163a75;
            transform: translateY(-1px);
        }

        .btn-submit:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* ========================================== */
        /* RESPONSIVE */
        /* ========================================== */
        @media (max-width: 768px) {
            .page-header {
                margin-bottom: 16px;
            }

            .page-title {
                font-size: 1.1rem;
            }

            .form-section {
                padding: 20px 16px;
                margin-bottom: 12px;
            }

            .type-grid {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .type-card {
                padding: 12px;
            }

            .type-icon {
                width: 32px;
                height: 32px;
            }

            .type-label {
                font-size: 0.8rem;
            }

            .time-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .pic-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .btn-submit {
                padding: 14px 20px;
            }
        }

        @media (max-width: 480px) {
            .type-grid {
                grid-template-columns: 1fr;
            }

            .back-btn {
                width: 40px;
                height: 40px;
            }
        }
    </style>

    {{-- Scripts are preserved from original --}}
    <script>
        (function() {
            const typeRadios = document.querySelectorAll('input[name="type"]');
            const IS_FIVE_DAY_WORKWEEK = @json($isFiveDayWorkWeek);

            const IZIN_TELAT = @json(\App\Enums\LeaveType::IZIN_TELAT->value);
            const IZIN_TENGAH_KERJA = @json(\App\Enums\LeaveType::IZIN_TENGAH_KERJA->value);
            const IZIN_PULANG_AWAL = @json(\App\Enums\LeaveType::IZIN_PULANG_AWAL->value);
            const CUTI = @json(\App\Enums\LeaveType::CUTI->value);
            const CUTI_KHUSUS = @json(\App\Enums\LeaveType::CUTI_KHUSUS->value);
            const SAKIT = @json(\App\Enums\LeaveType::SAKIT->value);

            const balanceInfoContainer = document.getElementById('balance-info-container');
            const specialLeaveContainer = document.getElementById('special-leave-container');
            const specialLeaveSelect = document.getElementById('special_leave_detail');
            const specialLeaveBadge = document.getElementById('special-leave-badge');
            const specialLeaveText = document.getElementById('special-leave-text');
            const specialLimitWarning = document.getElementById('special-limit-warning');

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
                    if (!isSunday && !(isSaturday && IS_FIVE_DAY_WORKWEEK)) {
                        days++;
                    }
                    cursor.setDate(cursor.getDate() + 1);
                }
                return days;
            }

            function updateSpecialLeaveBadge() {
                if (!specialLeaveSelect || !specialLeaveBadge || !specialLeaveText) return;
                const selectedOption = specialLeaveSelect.options[specialLeaveSelect.selectedIndex];
                const days = selectedOption ? selectedOption.getAttribute('data-days') : null;
                if (days) {
                    specialLeaveBadge.style.display = 'inline-flex';
                    specialLeaveText.textContent = 'Maksimal ' + days + ' Hari';
                } else {
                    specialLeaveBadge.style.display = 'none';
                    specialLeaveText.textContent = 'Maksimal 2 Hari';
                }
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
                        latEl.value = pos.coords.latitude.toFixed(7);
                        lngEl.value = pos.coords.longitude.toFixed(7);
                        accEl.value = (pos.coords.accuracy ?? 0).toFixed(2);
                        tsEl.value = new Date(pos.timestamp).toISOString().slice(0, 19).replace('T', ' ');
                        isRequestingLocation = false;
                    },
                    function() { isRequestingLocation = false; },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            }

            function checkSpecialLeaveLimit() {
                if (!specialLimitWarning) return;
                if (selectedType() !== CUTI_KHUSUS || !specialLeaveSelect.value) {
                    specialLimitWarning.style.display = 'none';
                    specialLimitWarning.textContent = '';
                    return;
                }
                const selectedOption = specialLeaveSelect.options[specialLeaveSelect.selectedIndex];
                const maxDays = parseInt(selectedOption.getAttribute('data-days')) || 0;
                const startStr = document.getElementById('start_date').value;
                const endStr = document.getElementById('end_date').value;
                if (!startStr || !endStr) {
                    specialLimitWarning.style.display = 'none';
                    specialLimitWarning.textContent = '';
                    return;
                }
                const diffDays = calculateWorkingDays(startStr, endStr);
                if (maxDays > 0 && diffDays > maxDays) {
                    specialLimitWarning.style.display = 'block';
                    specialLimitWarning.innerHTML = `Pengajuan terhitung <b>${diffDays} hari kerja</b>, melebihi batas maksimal <b>${maxDays} hari</b>.`;
                } else {
                    specialLimitWarning.style.display = 'none';
                    specialLimitWarning.textContent = '';
                }
            }

            function toggleSection() {
                const val = selectedType();

                // SALDO CUTI INFO
                if (val === CUTI && balanceInfoContainer) {
                    balanceInfoContainer.style.display = 'block';
                } else if(balanceInfoContainer) {
                    balanceInfoContainer.style.display = 'none';
                }

                // CUTI KHUSUS
                if (val === CUTI_KHUSUS) {
                    specialLeaveContainer.style.display = 'block';
                    if(specialLeaveSelect) specialLeaveSelect.required = true;
                    updateSpecialLeaveBadge();
                    checkSpecialLeaveLimit();
                } else {
                    specialLeaveContainer.style.display = 'none';
                    if(specialLeaveSelect) { specialLeaveSelect.required = false; specialLeaveSelect.value = ""; }
                    if(specialLeaveBadge) specialLeaveBadge.style.display = 'none';
                    if(specialLimitWarning) specialLimitWarning.style.display = 'none';
                }

                // LOKASI
                const isTelat = (val === IZIN_TELAT);
                if (isTelat) {
                    requestLocationIfNeeded();
                    if(photoInput) photoInput.required = false;
                    if(photoReqIndicator) photoReqIndicator.style.display = 'none';
                } else {
                    clearLocationValues();
                    if(photoInput) photoInput.required = false;
                    if(photoReqIndicator) photoReqIndicator.style.display = 'none';
                }

                // PIC
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

                // JAM
                const isTengahKerja = (val === IZIN_TENGAH_KERJA);
                const isPulangAwal = (val === IZIN_PULANG_AWAL);
                const showWorktime = isTengahKerja || isPulangAwal || isTelat;

                if (worktimeField) {
                    worktimeField.style.display = showWorktime ? 'block' : 'none';
                }

                if (!startTimeInput || !endTimeInput) return;

                if (isTengahKerja) {
                    if (worktimeLabel) worktimeLabel.innerHTML = 'Jam Izin';
                    if (endTimeWrapper) endTimeWrapper.style.display = 'block';
                    startTimeInput.required = true;
                    endTimeInput.required = true;
                    if (pulangInfo) pulangInfo.style.display = 'none';
                } else if (isPulangAwal) {
                    if (worktimeLabel) worktimeLabel.innerHTML = 'Jam Pulang';
                    if (endTimeWrapper) endTimeWrapper.style.display = 'none';
                    startTimeInput.required = true;
                    endTimeInput.required = false;
                    endTimeInput.value = '';
                    if (pulangInfo) {
                        const shiftEnd = shiftEndInput ? shiftEndInput.value : '';
                        if (shiftEnd) {
                            pulangInfo.style.display = 'block';
                            pulangInfo.textContent = 'Jam pulang shift: ' + shiftEnd + '. Maksimal 1 jam sebelum.';
                        } else {
                            pulangInfo.style.display = 'block';
                            pulangInfo.textContent = 'Maksimal 1 jam sebelum jam pulang shift.';
                        }
                    }
                } else if (isTelat) {
                    if (worktimeLabel) worktimeLabel.innerHTML = 'Estimasi Jam Tiba';
                    if (endTimeWrapper) endTimeWrapper.style.display = 'none';
                    startTimeInput.required = true;
                    endTimeInput.required = false;
                    endTimeInput.value = '';
                    if (pulangInfo) pulangInfo.style.display = 'none';
                } else {
                    startTimeInput.required = false;
                    endTimeInput.required = false;
                    startTimeInput.value = '';
                    endTimeInput.value = '';
                    if (pulangInfo) pulangInfo.style.display = 'none';
                }
            }

            if(specialLeaveSelect) {
                specialLeaveSelect.addEventListener('change', function() {
                    updateSpecialLeaveBadge();
                    checkSpecialLeaveLimit();
                });
            }

            document.getElementById('start_date').addEventListener('change', checkSpecialLeaveLimit);
            document.getElementById('end_date').addEventListener('change', checkSpecialLeaveLimit);

            typeRadios.forEach(function(r) {
                r.addEventListener('change', toggleSection);
            });

            updateSpecialLeaveBadge();
            toggleSection();
        })();

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

            const formIzin = document.getElementById('form-create-izin');
            const btnSubmit = document.getElementById('btn-submit-izin');

            if(formIzin) {
                formIzin.addEventListener('submit', function(e) {
                    if(!formIzin.checkValidity()) return;
                    if(btnSubmit) {
                        if(btnSubmit.disabled || btnSubmit.classList.contains('disabled')) {
                            e.preventDefault();
                            return;
                        }
                        btnSubmit.disabled = true;
                        btnSubmit.classList.add('disabled');
                        btnSubmit.innerHTML = `
                            <svg class="animate-spin" style="width:18px;height:18px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Memproses...
                        `;
                    }
                });
            }
        });
    </script>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            (function() {
                const CUTI_VALUE = @json(\App\Enums\LeaveType::CUTI->value);
                const IS_FIVE_DAY_WORKWEEK = @json($isFiveDayWorkWeek);

                const startInput = document.getElementById('start_date');
                const ruleEl = document.getElementById('cuti-rule');
                const warnEl = document.getElementById('h7-warning');
                const tenureWarnEl = document.getElementById('tenure-warning');
                const durationDisplay = document.getElementById('duration-display');
                const typeRadios = document.querySelectorAll('input[name="type"]');
                var shouldShowIzinTelatPopup = !!@json(session('show_izin_telat_popup'));

                const isUnderOneYear = tenureWarnEl ? tenureWarnEl.getAttribute('data-under-one-year') === '1' : false;

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
                    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
                }

                function getSelectedType() {
                    const r = document.querySelector('input[name="type"]:checked');
                    return r ? r.value : null;
                }

                function isCutiSelected() {
                    return getSelectedType() === CUTI_VALUE;
                }

                function calculateWorkingDays(startStr, endStr) {
                    if (!startStr || !endStr) return 0;
                    let startDate = parseYMD(startStr);
                    let endDate = parseYMD(endStr);
                    if (!startDate || !endDate || startDate > endDate) return 0;
                    let count = 0;
                    let cur = new Date(startDate);
                    while (cur <= endDate) {
                        const day = cur.getDay();
                        if (day === 0) { }
                        else if (day === 6 && IS_FIVE_DAY_WORKWEEK) { }
                        else { count++; }
                        cur.setDate(cur.getDate() + 1);
                    }
                    return count;
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
                    updateDurationDisplay();
                    if (isCutiSelected()) {
                        ruleEl.style.display = 'block';
                        ruleEl.innerHTML = 'Ketentuan: pengajuan minimal H-7 (≥ <b>' + formatID(boundaryDateH7()) + '</b>).';
                        updateWarning();
                        updateTenureWarning();
                    } else {
                        ruleEl.style.display = 'none';
                        warnEl.style.display = 'none';
                        updateTenureWarning();
                    }
                }

                function updateDurationDisplay() {
                    const startVal = document.getElementById('start_date').value;
                    const endVal = document.getElementById('end_date').value;
                    if (!startVal || !endVal) {
                        durationDisplay.style.display = 'none';
                        return;
                    }
                    const days = calculateWorkingDays(startVal, endVal);
                    durationDisplay.style.display = 'flex';
                    durationDisplay.innerHTML = `
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <strong>Estimasi: ${days} Hari Kerja</strong>
                    `;
                }

                function updateWarning() {
                    if (!isCutiSelected()) { warnEl.style.display = 'none'; return; }
                    const today = todayStart();
                    const start = parseYMD(startInput ? startInput.value : '');
                    if (!(start instanceof Date) || isNaN(start)) { warnEl.style.display = 'none'; return; }
                    const diffDays = Math.round((start - today) / (1000 * 60 * 60 * 24));
                    if (diffDays < 7 && diffDays >= 0) {
                        warnEl.style.display = 'block';
                        warnEl.textContent = 'Pengajuan H-' + diffDays + ' (kurang dari H-7). Termasuk Potong uang makan.';
                    } else {
                        warnEl.style.display = 'none';
                    }
                }

                if (startInput) {
                    startInput.addEventListener('input', updateWarning);
                    startInput.addEventListener('change', updateWarning);
                }

                typeRadios.forEach(function(r) {
                    r.addEventListener('change', renderRuleVisibility);
                });

                renderRuleVisibility();
                updateTenureWarning();

                var modal = document.getElementById('info-izin-telat');
                if (modal && shouldShowIzinTelatPopup) {
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                    modal.querySelectorAll('[data-modal-close="true"]').forEach(function(btn) {
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
                    locale: { rangeSeparator: ' sampai ' },
                    onChange: function(selectedDates, dateStr) {
                        if (!dateStr) {
                            startHidden.value = '';
                            endHidden.value = '';
                            startHidden.dispatchEvent(new Event('change'));
                            endHidden.dispatchEvent(new Event('change'));
                            return;
                        }
                        var parts = dateStr.split(' sampai ');
                        startHidden.value = parts[0];
                        endHidden.value = parts.length > 1 ? parts[1] : parts[0];
                        startHidden.dispatchEvent(new Event('change'));
                        endHidden.dispatchEvent(new Event('change'));
                    }
                });
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formIzin = document.getElementById('form-create-izin');
            const btnSubmit = document.getElementById('btn-submit-izin');
            const duplicateModal = document.getElementById('duplicate-warning-modal');
            const duplicateList = document.getElementById('duplicate-list');

            let hasDuplicate = false;
            let originalBtnText = null;

            if (formIzin) {
                formIzin.addEventListener('submit', function(e) {
                    if (hasDuplicate) {
                        e.preventDefault();
                        return false;
                    }
                    if (!formIzin.checkValidity()) return;
                    e.preventDefault();
                    const startDate = document.getElementById('start_date').value;
                    const endDate = document.getElementById('end_date').value;
                    if (!startDate || !endDate) {
                        alert('Silakan isi tanggal pengajuan terlebih dahulu');
                        return;
                    }
                    checkDuplicate(startDate, endDate);
                });
            }

            function checkDuplicate(startDate, endDate) {
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    originalBtnText = btnSubmit.innerHTML;
                    btnSubmit.innerHTML = `
                        <svg class="animate-spin" style="width:16px;height:16px;margin-right:5px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Memeriksa...
                    `;

                    fetch('{{ route("leave-requests.checkDuplicate") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({ start_date: startDate, end_date: endDate })
                    })
                    .then(response => response.json())
                    .then(data => {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = originalBtnText;
                        if (data.has_duplicate) {
                            hasDuplicate = true;
                            showDuplicateWarning(data.duplicates);
                        } else {
                            hasDuplicate = false;
                            formIzin.submit();
                        }
                    })
                    .catch(error => {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = originalBtnText;
                        hasDuplicate = false;
                    });
                }
            }

            function showDuplicateWarning(duplicates) {
                let html = '<ul style="margin: 0; padding-left: 16px; font-size: 13px;">';
                duplicates.forEach(function(dup) {
                    html += `<li style="margin-bottom: 6px;"><strong>${dup.type}</strong><br><span style="font-size: 12px; color: #6b7280;">${dup.start_date} s/d ${dup.end_date} | Status: <strong>${dup.status}</strong></span></li>`;
                });
                html += '</ul>';
                duplicateList.innerHTML = html;
                if (duplicateModal) {
                    duplicateModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                    const closeBtn = duplicateModal.querySelector('[data-modal-close="true"]');
                    if (closeBtn) {
                        closeBtn.onclick = function() {
                            duplicateModal.style.display = 'none';
                            document.body.style.overflow = '';
                            if (btnSubmit) {
                                btnSubmit.disabled = false;
                                btnSubmit.innerHTML = originalBtnText;
                            }
                            hasDuplicate = false;
                        };
                    }
                }
            }
        });
    </script>
    @endpush

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</x-app>
