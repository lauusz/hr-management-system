<x-app title="Buat Pengajuan Izin">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Buat Pengajuan</h1>
                <p class="section-subtitle">Ajukan izin atau cuti ketidakhadiran</p>
            </div>
        </div>
    </x-slot>

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
        if ($oldStart && $oldEnd) { $oldRange = $oldStart . ' sampai ' . $oldEnd; }
        elseif ($oldStart) { $oldRange = $oldStart; }
        $shiftEndDisplay = null;
        try {
            $empShift = \App\Models\EmployeeShift::with('shift')->where('user_id', auth()->id())->first();
            if ($empShift && $empShift->shift && $empShift->shift->end_time) {
                $shiftEndDisplay = \Carbon\Carbon::parse($empShift->shift->end_time)->format('H:i');
            }
        } catch (\Throwable $e) { $shiftEndDisplay = null; }
        $specialLeaveList = [
            ['id'=>'NIKAH_KARYAWAN','label'=>'Menikah','days'=>4],
            ['id'=>'ISTRI_MELAHIRKAN','label'=>'Istri Melahirkan','days'=>2],
            ['id'=>'ISTRI_KEGUGURAN','label'=>'Istri Keguguran','days'=>2],
            ['id'=>'KHITANAN_ANAK','label'=>'Khitanan Anak','days'=>2],
            ['id'=>'PEMBAPTISAN_ANAK','label'=>'Pembaptisan Anak','days'=>2],
            ['id'=>'NIKAH_ANAK','label'=>'Pernikahan Anak','days'=>2],
            ['id'=>'DEATH_EXTENDED','label'=>'Kematian (Adik / Kakak / Ipar)','days'=>2],
            ['id'=>'DEATH_CORE','label'=>'Kematian Inti (Ortu / Mertua / Menantu / Istri / Suami / Anak)','days'=>2],
            ['id'=>'DEATH_HOUSE','label'=>'Kematian Anggota Rumah','days'=>1],
            ['id'=>'HAJI','label'=>'Ibadah Haji (1x)','days'=>40],
            ['id'=>'UMROH','label'=>'Ibadah Umroh (1x)','days'=>14],
        ];
        $roleValue = $user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role;
        $role = strtoupper((string) $roleValue);
        $isSpv = in_array($role, ['SUPERVISOR','SPV'], true);
        $offRemaining = null;
        if (isset($offSpvInfo) && is_array($offSpvInfo) && array_key_exists('remaining', $offSpvInfo)) {
            $offRemaining = (int) $offSpvInfo['remaining'];
        }
        $leaveTypeMeta = [];
        $isFirstType = true;
        foreach (\App\Enums\LeaveType::cases() as $case) {
            if ($case->value === \App\Enums\LeaveType::OFF_SPV->value && !$isSpv) { continue; }
            $label = $case->label(); $hint = null;
            if ($case->value === \App\Enums\LeaveType::OFF_SPV->value && $offRemaining !== null) { $hint = 'Sisa ' . $offRemaining . ' hari'; }
            if ($case->value === \App\Enums\LeaveType::CUTI->value) { $hint = 'Sisa ' . rtrim(rtrim(sprintf('%.1f', $leaveBalance), '0'), '.') . ' hari'; }
            $typeIcon = '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'; $typeIconKey = 'dinas_luar';
            if ($case->value === \App\Enums\LeaveType::CUTI->value) { $typeIcon = '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'; $typeIconKey = 'cuti'; }
            elseif ($case->value === \App\Enums\LeaveType::CUTI_KHUSUS->value) { $typeIcon = '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'; $typeIconKey = 'cuti_khusus'; }
            elseif ($case->value === \App\Enums\LeaveType::SAKIT->value) { $typeIcon = '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>'; $typeIconKey = 'sakit'; }
            elseif ($case->value === \App\Enums\LeaveType::IZIN->value) { $typeIcon = '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>'; $typeIconKey = 'izin'; }
            elseif ($case->value === \App\Enums\LeaveType::IZIN_TELAT->value) { $typeIcon = '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'; $typeIconKey = 'izin_telat'; }
            elseif ($case->value === \App\Enums\LeaveType::IZIN_PULANG_AWAL->value) { $typeIcon = '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>'; $typeIconKey = 'izin_pulang_awal'; }
            elseif ($case->value === \App\Enums\LeaveType::IZIN_TENGAH_KERJA->value) { $typeIcon = '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'; $typeIconKey = 'izin_tengah_kerja'; }
            elseif ($case->value === \App\Enums\LeaveType::OFF_SPV->value) { $typeIcon = '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>'; $typeIconKey = 'off_spv'; }
            $leaveTypeMeta[] = ['value'=>$case->value,'label'=>$label,'hint'=>$hint,'icon'=>$typeIcon,'iconKey'=>$typeIconKey,'checked'=>old('type')===$case->value,'first'=>$isFirstType];
            $isFirstType = false;
        }
    @endphp

    <a href="{{ route('leave-requests.index') }}" class="back-btn" aria-label="Kembali ke daftar pengajuan">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span class="back-btn-text">Kembali</span>
    </a>

    @if ($errors->any())
        <div class="lrc-alert lrc-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form id="form-create-izin" method="POST" action="{{ route('leave-requests.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="shift_end_time" value="{{ $shiftEndDisplay }}">

        <div class="lrc-step">
            <div class="lrc-step__header">
                <span class="lrc-step__num">1</span>
                <span class="lrc-step__title">Jenis Pengajuan</span>
            </div>

            <div class="lrc-type-radios">
                @foreach ($leaveTypeMeta as $meta)
                    <label class="lrc-type-radio-label">
                        <input type="radio"
                            name="type"
                            value="{{ $meta['value'] }}"
                            @if ($meta['first']) required @endif
                            @checked($meta['checked'])
                            data-label="{{ $meta['label'] }}"
                            data-hint="{{ $meta['hint'] ?? '' }}"
                            data-icon-key="{{ $meta['iconKey'] }}">
                    </label>
                @endforeach
            </div>

            <button type="button" class="lrc-type-trigger" id="typeTrigger" aria-haspopup="listbox" aria-expanded="false">
                <span class="lrc-type-trigger__icon" id="typeTriggerIcon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </span>
                <span class="lrc-type-trigger__text">
                    <span class="lrc-type-trigger__label" id="typeTriggerLabel">Pilih jenis pengajuan</span>
                    <span class="lrc-type-trigger__hint" id="typeTriggerHint"></span>
                </span>
                <svg class="lrc-type-trigger__chevron" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="lrc-type-sheet" id="typeSheet" role="listbox" aria-label="Pilih jenis pengajuan">
                <div class="lrc-type-sheet__backdrop" id="typeSheetBackdrop"></div>
                <div class="lrc-type-sheet__panel">
                    <div class="lrc-type-sheet__handle"></div>
                    <div class="lrc-type-sheet__header">
                        <h3 class="lrc-type-sheet__title">Pilih Jenis Pengajuan</h3>
                        <button type="button" class="lrc-type-sheet__close" id="typeSheetClose" aria-label="Tutup">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="lrc-type-sheet__list">
                        @foreach ($leaveTypeMeta as $meta)
                            <button type="button"
                                class="lrc-type-sheet__item"
                                data-value="{{ $meta['value'] }}"
                                role="option"
                                aria-selected="{{ $meta['checked'] ? 'true' : 'false' }}">
                                <span class="lrc-type-sheet__item-icon">{!! $meta['icon'] !!}</span>
                                <span class="lrc-type-sheet__item-text">
                                    <span class="lrc-type-sheet__item-label">{{ $meta['label'] }}</span>
                                    @if ($meta['hint'])
                                        <span class="lrc-type-sheet__item-hint">{{ $meta['hint'] }}</span>
                                    @endif
                                </span>
                                <span class="lrc-type-sheet__item-check">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div id="balance-info-container" style="display:none;">
                @if($leaveBalance > 0)
                    <div class="lrc-info lrc-info--blue">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <strong>Sisa Cuti: {{ rtrim(rtrim(sprintf('%.1f', $leaveBalance), '0'), '.') }} Hari</strong>
                            <p>Cuti akan berkurang otomatis setelah disetujui.</p>
                        </div>
                    </div>
                @else
                    <div class="lrc-info lrc-info--red">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <strong>Saldo Cuti Habis</strong>
                            <p>Pilih "Izin (Potong Gaji)" atau tipe lain.</p>
                        </div>
                    </div>
                @endif
            </div>

            <div id="special-leave-container" style="display:none;">
                <label for="special_leave_detail" class="lrc-label">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Kategori Cuti Khusus <span class="lrc-required">*</span>
                </label>
                <div class="lrc-type-radios">
                    <select name="special_leave_detail" id="special_leave_detail" class="lrc-select">
                        <option value="" selected disabled data-days="">-- Pilih Alasan --</option>
                        @foreach($specialLeaveList as $sl)
                            <option value="{{ $sl['id'] }}" data-days="{{ $sl['days'] }}" @selected(old('special_leave_detail') == $sl['id'])>
                                {{ $sl['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="button" class="lrc-type-trigger" id="specTrigger" aria-haspopup="listbox" aria-expanded="false">
                    <span class="lrc-type-trigger__icon" id="specTriggerIcon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </span>
                    <span class="lrc-type-trigger__text">
                        <span class="lrc-type-trigger__label" id="specTriggerLabel">Pilih kategori cuti khusus</span>
                        <span class="lrc-type-trigger__hint" id="specTriggerHint"></span>
                    </span>
                    <svg class="lrc-type-trigger__chevron" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="lrc-type-sheet" id="specSheet" role="listbox" aria-label="Pilih Kategori Cuti Khusus">
                    <div class="lrc-type-sheet__backdrop" id="specSheetBackdrop"></div>
                    <div class="lrc-type-sheet__panel">
                        <div class="lrc-type-sheet__handle"></div>
                        <div class="lrc-type-sheet__header">
                            <h3 class="lrc-type-sheet__title">Pilih Kategori Cuti Khusus</h3>
                            <button type="button" class="lrc-type-sheet__close" id="specSheetClose" aria-label="Tutup">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="lrc-type-sheet__list">
                            @foreach($specialLeaveList as $sl)
                                <button type="button"
                                    class="lrc-type-sheet__item"
                                    data-value="{{ $sl['id'] }}"
                                    data-days="{{ $sl['days'] }}"
                                    data-label="{{ $sl['label'] }}"
                                    role="option"
                                    aria-selected="{{ old('special_leave_detail') == $sl['id'] ? 'true' : 'false' }}">
                                    <span class="lrc-type-sheet__item-icon">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </span>
                                    <span class="lrc-type-sheet__item-text">
                                        <span class="lrc-type-sheet__item-label">{{ $sl['label'] }}</span>
                                        <span class="lrc-type-sheet__item-hint">{{ $sl['days'] }} hari</span>
                                    </span>
                                    <span class="lrc-type-sheet__item-check">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div id="special-leave-badge" class="lrc-badge" style="display:none;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span id="special-leave-text">Maksimal 2 Hari</span>
                </div>
            </div>
        </div>

        <div class="lrc-step">
            <div class="lrc-step__header">
                <span class="lrc-step__num">2</span>
                <span class="lrc-step__title">Periode Izin</span>
            </div>
            <div class="lrc-input-wrap">
                <svg class="lrc-input__icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <input type="text"
                    id="date_range"
                    name="date_range"
                    class="lrc-input lrc-input--icon"
                    value="{{ $oldRange }}"
                    placeholder="Pilih tanggal mulai — selesai"
                    autocomplete="off">
            </div>
            <input type="hidden" name="start_date" id="start_date" value="{{ $oldStart }}">
            <input type="hidden" name="end_date" id="end_date" value="{{ $oldEnd }}">
            <div id="duration-display" class="lrc-duration" style="display:none;"></div>
            <div class="lrc-warnings">
                <small id="cuti-rule" class="lrc-rule"></small>
                <div id="h7-warning" class="lrc-warning" style="display:none;"></div>
                <div id="special-limit-warning" class="lrc-warning" style="display:none;"></div>
                <div id="tenure-warning" class="lrc-warning" style="display:none;" data-under-one-year="{{ $underOneYear ? '1' : '0' }}"></div>
            </div>
        </div>

        <div class="lrc-step" id="worktime-field" style="display:none;">
            <div class="lrc-step__header">
                <span class="lrc-step__num">3</span>
                <span class="lrc-step__title">Waktu Izin</span>
            </div>
            <div class="lrc-time-grid">
                <div class="lrc-field">
                    <label id="worktime-label" class="lrc-field__label">Jam Izin</label>
                    <div class="lrc-input-wrap">
                        <svg class="lrc-input__icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <input type="time" name="start_time" id="start_time_input" class="lrc-input lrc-input--icon" value="{{ old('start_time') }}">
                    </div>
                </div>
                <div class="lrc-field" id="end_time_wrapper">
                    <label class="lrc-field__label">Jam Selesai</label>
                    <div class="lrc-input-wrap">
                        <svg class="lrc-input__icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <input type="time" name="end_time" id="end_time_input" class="lrc-input lrc-input--icon" value="{{ old('end_time') }}">
                    </div>
                </div>
            </div>
            <div id="pulang-info" class="lrc-helper" style="display:none;"></div>
        </div>

        <div class="lrc-step" id="substitute-pic-section" style="display:none;">
            <div class="lrc-step__header">
                <span class="lrc-step__num">3</span>
                <span class="lrc-step__title">PIC Pengganti</span>
            </div>
            <div class="lrc-field">
                <label for="substitute_pic" class="lrc-label">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Nama PIC Pengganti <span class="lrc-required">*</span>
                </label>
                <input type="text" name="substitute_pic" id="substitute_pic" class="lrc-input" placeholder="Nama rekan pengganti" value="{{ old('substitute_pic') }}">
            </div>
            <div class="lrc-field">
                <label for="substitute_phone" class="lrc-label">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    No. HP PIC <span class="lrc-required">*</span>
                </label>
                <input type="tel" name="substitute_phone" id="substitute_phone" class="lrc-input" placeholder="Contoh: 0812..." value="{{ old('substitute_phone') }}">
            </div>
            <p class="lrc-helper">Wajib diisi untuk koordinasi selama Anda tidak hadir.</p>
        </div>

        <div class="lrc-step">
            <div class="lrc-step__header">
                <span class="lrc-step__num">4</span>
                <span class="lrc-step__title">Bukti & Keterangan</span>
            </div>
            <div class="lrc-field">
                <label for="photoInput" class="lrc-label">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Bukti Pendukung <span id="photo-req-indicator" class="lrc-required" style="display:none">*</span>
                </label>
                <div class="lrc-upload" id="uploadBox">
                    <input type="file" name="photo" id="photoInput" class="lrc-upload__input" accept=".jpg,.jpeg,.png,.webp,.heic,.heif,.pdf,.doc,.docx,.xls,.xlsx">
                    <div class="lrc-upload__content">
                        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span class="lrc-upload__title">Klik untuk upload file</span>
                        <span class="lrc-upload__desc">JPG, PNG, PDF, DOCX (Maks 8MB)</span>
                    </div>
                </div>
                <div id="photoPreviewContainer" class="lrc-preview" style="display:none;">
                    <img id="photoPreview" src="#" alt="Preview">
                    <button type="button" class="lrc-preview__remove" id="removePreview" aria-label="Hapus preview">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="lrc-field">
                <label for="reason" class="lrc-label">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Alasan / Keterangan <span class="lrc-required">*</span>
                </label>
                <textarea name="reason" id="reason" rows="4" class="lrc-textarea" placeholder="Jelaskan alasan pengajuan Anda secara detail...">{{ old('reason') }}</textarea>
            </div>
        </div>

        <div id="location" style="display:none;">
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            <input type="hidden" name="accuracy_m" id="accuracy_m">
            <input type="hidden" name="location_captured_at" id="location_captured_at">
        </div>

        <div class="lrc-submit-wrap">
            <button class="lrc-submit-btn" type="submit" id="btn-submit-izin">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Kirim Pengajuan
            </button>
        </div>
    </form>



    <x-modal id="duplicate-warning-modal" title="Peringatan" variant="warning" type="info" cancelLabel="Kembali">
        <div id="duplicate-content" style="color: #1e293b; line-height: 1.6;">
            <p style="margin: 0 0 12px 0;"><strong>Anda sudah memiliki pengajuan pada periode tanggal yang sama.</strong></p>
            <div id="duplicate-list" style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px; border-radius: 4px; margin-bottom: 12px;"></div>
            <p style="margin: 0; font-size: 13px; color: #6b7280;">Hubungi HRD untuk menghapus pengajuan duplikat.</p>
        </div>
    </x-modal>

    <style>
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0;
        }
        .section-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .section-icon svg {
            width: 16px;
            height: 16px;
        }
        .section-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary, #111827);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy {
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark, #0A3D62);
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 12px 0 10px;
            background: var(--white, #fff);
            border: 1px solid var(--border, #E5E7EB);
            border-radius: 10px;
            color: var(--text-muted, #6B7280);
            text-decoration: none;
            transition: all 0.15s ease;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            margin-bottom: 16px;
        }
        .back-btn:hover {
            border-color: var(--primary, #145DA0);
            color: var(--primary, #145DA0);
            background: var(--gray-50, #F5F7FA);
        }
        .back-btn:hover svg {
            transform: translateX(-2px);
        }
        .back-btn svg {
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }
        .back-btn-text {
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
        }
        .lrc-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 0.8125rem;
            font-weight: 500;
            line-height: 1.5;
        }
        .lrc-alert svg { flex-shrink: 0; margin-top: 1px; }
        .lrc-alert--error {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
        }
        .lrc-step {
            background: var(--white);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 12px;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .lrc-step__header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }
        .lrc-step__num {
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-dark);
            color: #fff;
            border-radius: 50%;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .lrc-step__title {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .lrc-type-radios {
            position: absolute;
            opacity: 0;
            pointer-events: none;
            width: 0;
            height: 0;
            overflow: hidden;
            left: -9999px;
        }
        .lrc-type-radio-label { display: none; }
        .lrc-type-trigger {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.9375rem;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: left;
        }
        .lrc-type-trigger:hover {
            border-color: var(--primary);
            background: var(--gray-50);
        }
        .lrc-type-trigger:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .lrc-type-trigger__icon {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gray-50);
            border-radius: 10px;
            color: var(--text-muted);
            flex-shrink: 0;
            transition: all 0.2s ease;
        }
        .lrc-type-trigger__text {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .lrc-type-trigger__label {
            font-weight: 600;
            color: var(--text-primary);
            line-height: 1.3;
        }
        .lrc-type-trigger__hint {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 500;
        }
        .lrc-type-trigger__chevron {
            flex-shrink: 0;
            color: var(--text-light);
            transition: transform 0.2s ease;
        }
        .lrc-type-trigger[aria-expanded="true"] .lrc-type-trigger__chevron {
            transform: rotate(180deg);
        }
        .lrc-type-trigger.has-value .lrc-type-trigger__icon {
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary);
        }
        .lrc-type-sheet {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 2000;
            align-items: flex-end;
            justify-content: center;
        }
        .lrc-type-sheet.is-open { display: flex; }
        .lrc-type-sheet__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.45);
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .lrc-type-sheet.is-open .lrc-type-sheet__backdrop { opacity: 1; }
        .lrc-type-sheet__panel {
            position: relative;
            width: 100%;
            max-height: 85vh;
            background: var(--white);
            border-radius: 20px 20px 0 0;
            padding: 16px 0 24px;
            transform: translateY(100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }
        .lrc-type-sheet.is-open .lrc-type-sheet__panel { transform: translateY(0); }
        .lrc-type-sheet__handle {
            width: 40px;
            height: 4px;
            background: var(--border);
            border-radius: 2px;
            margin: 0 auto 12px;
        }
        .lrc-type-sheet__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px 12px;
        }
        .lrc-type-sheet__title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }
        .lrc-type-sheet__close {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gray-50);
            border: none;
            border-radius: 8px;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .lrc-type-sheet__close:hover {
            background: var(--border-light);
            color: var(--text-primary);
        }
        .lrc-type-sheet__list {
            overflow-y: auto;
            padding: 0 16px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .lrc-type-sheet__item {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            background: transparent;
            border: 1.5px solid transparent;
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.875rem;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.15s ease;
            text-align: left;
        }
        .lrc-type-sheet__item:hover { background: var(--gray-50); }
        .lrc-type-sheet__item.is-selected {
            background: rgba(20, 93, 160, 0.06);
            border-color: rgba(20, 93, 160, 0.2);
        }
        .lrc-type-sheet__item-icon {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gray-50);
            border-radius: 10px;
            color: var(--text-muted);
            flex-shrink: 0;
        }
        .lrc-type-sheet__item.is-selected .lrc-type-sheet__item-icon {
            background: rgba(20, 93, 160, 0.1);
            color: var(--primary);
        }
        .lrc-type-sheet__item-text {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .lrc-type-sheet__item-label { font-weight: 600; line-height: 1.3; }
        .lrc-type-sheet__item-hint {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 500;
        }
        .lrc-type-sheet__item-check {
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary);
            border-radius: 50%;
            color: #fff;
            opacity: 0;
            transform: scale(0.6);
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        .lrc-type-sheet__item.is-selected .lrc-type-sheet__item-check {
            opacity: 1;
            transform: scale(1);
        }

        .lrc-info {
            display: flex;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 0.8125rem;
            margin-top: 12px;
        }
        .lrc-info svg { flex-shrink: 0; margin-top: 1px; }
        .lrc-info strong { font-weight: 600; display: block; margin-bottom: 2px; }
        .lrc-info p { margin: 0; font-size: 0.75rem; opacity: 0.9; }
        .lrc-info--blue {
            background: #EFF6FF;
            border: 1px solid #DBEAFE;
            color: #1E40AF;
        }
        .lrc-info--red {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
        }
        .lrc-field { margin-bottom: 14px; }
        .lrc-field:last-child { margin-bottom: 0; }
        .lrc-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        .lrc-required { color: var(--error); font-weight: 700; }
        .lrc-input-wrap { position: relative; }
        .lrc-input__icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            pointer-events: none;
        }
        .lrc-input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.9375rem;
            color: var(--text-primary);
            background: var(--white);
            transition: all 0.2s ease;
            outline: none;
            font-family: inherit;
        }
        .lrc-input--icon { padding-left: 42px; }
        .lrc-input::placeholder { color: var(--text-light); }
        .lrc-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .lrc-select-wrap { position: relative; }
        .lrc-select {
            width: 100%;
            padding: 12px 40px 12px 14px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.9375rem;
            color: var(--text-primary);
            background: var(--white);
            transition: all 0.2s ease;
            outline: none;
            font-family: inherit;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
        }
        .lrc-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .lrc-select__arrow {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            pointer-events: none;
        }
        .lrc-textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.9375rem;
            color: var(--text-primary);
            background: var(--white);
            transition: all 0.2s ease;
            outline: none;
            resize: vertical;
            min-height: 100px;
            line-height: 1.5;
            font-family: inherit;
        }
        .lrc-textarea::placeholder { color: var(--text-light); }
        .lrc-textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .lrc-duration {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            padding: 10px 14px;
            background: #F0FDF4;
            border: 1px solid #BBF7D0;
            border-radius: 10px;
            color: #15803D;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .lrc-warnings {
            margin-top: 8px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .lrc-warning {
            padding: 10px 12px;
            background: #FEFCE8;
            border: 1px solid #FDE68A;
            border-radius: 10px;
            color: #854D0E;
            font-size: 0.8125rem;
            font-weight: 500;
            line-height: 1.5;
        }
        .lrc-rule {
            display: block;
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 500;
            line-height: 1.5;
        }
        .lrc-helper {
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 500;
            margin-top: 6px;
            line-height: 1.5;
        }
        .lrc-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            padding: 5px 10px;
            background: #EFF6FF;
            color: var(--primary);
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .lrc-time-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .lrc-field__label {
            display: block;
            font-size: 0.6875rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 6px;
        }
        .lrc-upload {
            position: relative;
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            background: var(--gray-50);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .lrc-upload:hover {
            border-color: var(--primary);
            background: rgba(20, 93, 160, 0.03);
        }
        .lrc-upload__input {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .lrc-upload__content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            color: var(--text-muted);
        }
        .lrc-upload__content svg { color: var(--text-light); }
        .lrc-upload__title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
        }
        .lrc-upload__desc {
            font-size: 0.75rem;
            color: var(--text-light);
            font-weight: 500;
        }
        .lrc-preview {
            position: relative;
            margin-top: 10px;
            padding: 10px;
            background: var(--gray-50);
            border: 1px solid var(--border-light);
            border-radius: 10px;
        }
        .lrc-preview img {
            max-width: 100%;
            max-height: 180px;
            border-radius: 8px;
            display: block;
            margin: 0 auto;
        }
        .lrc-preview__remove {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 50%;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .lrc-preview__remove:hover {
            background: #FEF2F2;
            border-color: #FECACA;
            color: var(--error);
        }
        .lrc-submit-wrap { margin: 20px 0 32px; }
        .lrc-submit-btn {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            font-family: inherit;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
        }
        .lrc-submit-btn:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .lrc-submit-btn:disabled {
            background: #94A3B8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .animate-spin { animation: spin 1s linear infinite; }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @media (min-width: 640px) {
            .lrc-type-sheet {
                align-items: flex-start;
                justify-content: center;
                padding-top: 120px;
            }
            .lrc-type-sheet__backdrop { background: rgba(0,0,0,0.35); }
            .lrc-type-sheet__panel {
                width: 100%;
                max-width: 420px;
                max-height: 520px;
                border-radius: 16px;
                padding: 16px 0 16px;
                transform: translateY(-10px);
                opacity: 0;
                transition: all 0.2s ease;
                box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            }
            .lrc-type-sheet.is-open .lrc-type-sheet__panel {
                transform: translateY(0);
                opacity: 1;
            }
            .lrc-type-sheet__handle { display: none; }
            .lrc-type-sheet__header {
                padding-bottom: 8px;
                border-bottom: 1px solid var(--border-light);
                margin: 0 16px 8px;
            }
            .lrc-type-sheet__list { padding: 0 12px; }
            .lrc-time-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 639px) {
            .lrc-step {
                padding: 16px;
                margin-bottom: 10px;
                border-radius: 14px;
            }
            .lrc-time-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            .back-btn {
                height: 40px;
                padding: 0 14px 0 12px;
            }
            .back-btn-text {
                font-size: 0.8125rem;
            }
        }
    </style>

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
                    if (!isSunday && !(isSaturday && IS_FIVE_DAY_WORKWEEK)) { days++; }
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
                if (val === CUTI && balanceInfoContainer) {
                    balanceInfoContainer.style.display = 'block';
                } else if(balanceInfoContainer) {
                    balanceInfoContainer.style.display = 'none';
                }
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
                const isTengahKerja = (val === IZIN_TENGAH_KERJA);
                const isPulangAwal = (val === IZIN_PULANG_AWAL);
                const showWorktime = isTengahKerja || isPulangAwal || isTelat;
                if (worktimeField) { worktimeField.style.display = showWorktime ? 'block' : 'none'; }
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
            typeRadios.forEach(function(r) { r.addEventListener('change', toggleSection); });
            updateSpecialLeaveBadge();
            toggleSection();
        })();
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('photoInput');
            const previewContainer = document.getElementById('photoPreviewContainer');
            const previewImg = document.getElementById('photoPreview');
            const removeBtn = document.getElementById('removePreview');
            if (input) {
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
            }
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    input.value = '';
                    previewImg.src = '#';
                    previewContainer.style.display = 'none';
                });
            }
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
                function isCutiSelected() { return getSelectedType() === CUTI_VALUE; }
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
                typeRadios.forEach(function(r) { r.addEventListener('change', renderRuleVisibility); });
                renderRuleVisibility();
                updateTenureWarning();

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
                    if (hasDuplicate) { e.preventDefault(); return false; }
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const trigger = document.getElementById('typeTrigger');
            const sheet = document.getElementById('typeSheet');
            const backdrop = document.getElementById('typeSheetBackdrop');
            const closeBtn = document.getElementById('typeSheetClose');
            const items = sheet ? sheet.querySelectorAll('.lrc-type-sheet__item') : [];
            const radios = document.querySelectorAll('input[name="type"]');
            const triggerIcon = document.getElementById('typeTriggerIcon');
            const triggerLabel = document.getElementById('typeTriggerLabel');
            const triggerHint = document.getElementById('typeTriggerHint');
            const iconMap = {
                cuti: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                cuti_khusus: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                sakit: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>',
                izin: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
                izin_telat: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                izin_pulang_awal: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>',
                izin_tengah_kerja: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                off_spv: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>',
                dinas_luar: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
            };
            function updateTrigger() {
                const checked = document.querySelector('input[name="type"]:checked');
                if (!checked) {
                    triggerLabel.textContent = 'Pilih jenis pengajuan';
                    triggerHint.textContent = '';
                    triggerIcon.innerHTML = iconMap.dinas_luar;
                    trigger.classList.remove('has-value');
                    return;
                }
                trigger.classList.add('has-value');
                triggerLabel.textContent = checked.getAttribute('data-label') || checked.value;
                triggerHint.textContent = checked.getAttribute('data-hint') || '';
                const iconKey = checked.getAttribute('data-icon-key');
                if (iconKey && iconMap[iconKey]) { triggerIcon.innerHTML = iconMap[iconKey]; }
            }
            function openSheet() {
                if (!sheet) return;
                sheet.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
                const checked = document.querySelector('input[name="type"]:checked');
                items.forEach(function(item) {
                    const isSelected = checked && item.getAttribute('data-value') === checked.value;
                    item.classList.toggle('is-selected', isSelected);
                    item.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                });
            }
            function closeSheet() {
                if (!sheet) return;
                sheet.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
            if (trigger) { trigger.addEventListener('click', openSheet); }
            if (backdrop) { backdrop.addEventListener('click', closeSheet); }
            if (closeBtn) { closeBtn.addEventListener('click', closeSheet); }
            items.forEach(function(item) {
                item.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    radios.forEach(function(r) {
                        if (r.value === value) {
                            r.checked = true;
                            r.dispatchEvent(new Event('change'));
                        }
                    });
                    updateTrigger();
                    closeSheet();
                });
            });
            updateTrigger();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const specTrigger = document.getElementById('specTrigger');
            const specSheet = document.getElementById('specSheet');
            const specBackdrop = document.getElementById('specSheetBackdrop');
            const specCloseBtn = document.getElementById('specSheetClose');
            const specItems = specSheet ? specSheet.querySelectorAll('.lrc-type-sheet__item') : [];
            const specSelect = document.getElementById('special_leave_detail');
            const specTriggerLabel = document.getElementById('specTriggerLabel');
            const specTriggerHint = document.getElementById('specTriggerHint');
            function updateSpecTrigger() {
                if (!specSelect || !specTriggerLabel) return;
                const selectedOption = specSelect.options[specSelect.selectedIndex];
                const hasValue = specSelect.value && selectedOption && !selectedOption.disabled;
                if (!hasValue) {
                    specTriggerLabel.textContent = 'Pilih kategori cuti khusus';
                    specTriggerHint.textContent = '';
                    specTrigger.classList.remove('has-value');
                    return;
                }
                specTrigger.classList.add('has-value');
                specTriggerLabel.textContent = selectedOption.textContent.trim();
                const days = selectedOption.getAttribute('data-days');
                specTriggerHint.textContent = days ? days + ' hari' : '';
            }
            function openSpecSheet() {
                if (!specSheet) return;
                specSheet.classList.add('is-open');
                specTrigger.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
                const selectedValue = specSelect ? specSelect.value : '';
                specItems.forEach(function(item) {
                    const isSelected = item.getAttribute('data-value') === selectedValue;
                    item.classList.toggle('is-selected', isSelected);
                    item.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                });
            }
            function closeSpecSheet() {
                if (!specSheet) return;
                specSheet.classList.remove('is-open');
                specTrigger.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
            if (specTrigger) { specTrigger.addEventListener('click', openSpecSheet); }
            if (specBackdrop) { specBackdrop.addEventListener('click', closeSpecSheet); }
            if (specCloseBtn) { specCloseBtn.addEventListener('click', closeSpecSheet); }
            specItems.forEach(function(item) {
                item.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    if (specSelect) {
                        specSelect.value = value;
                        specSelect.dispatchEvent(new Event('change'));
                    }
                    updateSpecTrigger();
                    closeSpecSheet();
                });
            });
            updateSpecTrigger();
        });
    </script>
    @endpush

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</x-app>
