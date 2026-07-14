<x-app title="Edit Pengajuan Izin">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Edit Pengajuan</h1>
                <p class="section-subtitle">Ubah data pengajuan izin/cuti yang masih pending</p>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="lr-alert lr-alert--success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="lr-alert lr-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="lr-alert lr-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ $errors->first() }}
        </div>
    @endif

    @php
        $user = auth()->user();
        $typeValue = $item->type instanceof \App\Enums\LeaveType ? $item->type->value : (string) $item->type;
        $specialLeaveList = [
            ['id'=>'NIKAH_KARYAWAN','label'=>'Menikah','days'=>3],
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
        $isTimeBased = in_array($typeValue, [\App\Enums\LeaveType::IZIN_TELAT->value, \App\Enums\LeaveType::IZIN_TENGAH_KERJA->value, \App\Enums\LeaveType::IZIN_PULANG_AWAL->value, \App\Enums\LeaveType::IZIN->value], true);
        $photoUrl = $item->photo ? route('leave-requests.supporting-file', $item) : null;
        $docExt = $item->photo ? strtolower(pathinfo($item->photo, PATHINFO_EXTENSION)) : null;
        $isImageDoc = in_array($docExt, ['jpg','jpeg','png','gif','webp','bmp','svg']);

        $joinDate = $user->profile->tgl_bergabung ?? null;
        $underOneYear = false;
        $leaveBalance = $user->leave_balance ?? 0;
        if ($joinDate) {
            $start = \Carbon\Carbon::parse($joinDate)->startOfDay();
            $end = \Carbon\Carbon::today();
            $underOneYear = $start->diffInYears($end) < 1;
        }
        $roleStr = strtoupper($user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role);
        $isSpv = in_array($roleStr, ['SUPERVISOR','SPV'], true);
        $offRemaining = null;
        if (isset($offSpvInfo) && is_array($offSpvInfo) && array_key_exists('remaining', $offSpvInfo)) {
            $offRemaining = (int) $offSpvInfo['remaining'];
        }
        $oldStart = old('start_date', $item->start_date->format('Y-m-d'));
        $oldEnd = old('end_date', $item->end_date->format('Y-m-d'));
        $oldRange = '';
        if ($oldStart && $oldEnd) { $oldRange = $oldStart . ' sampai ' . $oldEnd; }
        elseif ($oldStart) { $oldRange = $oldStart; }
    @endphp

    <a href="{{ route('leave-requests.index') }}" class="back-btn" aria-label="Kembali ke daftar pengajuan">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span class="back-btn-text">Kembali</span>
    </a>

    <div class="lr-form-card">
        <form method="POST" action="{{ route('leave-requests.update', $item) }}" enctype="multipart/form-data" id="edit-leave-form">
            @csrf
            @method('PUT')
            <input type="hidden" id="leave_id" value="{{ $item->id }}">
            <input type="hidden" name="start_date" id="start_date" value="{{ $oldStart }}">
            <input type="hidden" name="end_date" id="end_date" value="{{ $oldEnd }}">

            <div class="lr-form-section">
                <div class="lr-form-section-title">Jenis Pengajuan</div>
                <select name="type" id="edit_type" class="lr-filter-input" required>
                    @foreach(\App\Enums\LeaveType::cases() as $case)
                        @if($case->value === \App\Enums\LeaveType::OFF_SPV->value && !$canOffSpv)
                            @continue
                        @endif
                        @php
                            $hint = '';
                            if ($case->value === \App\Enums\LeaveType::CUTI->value) {
                                $hint = 'Sisa ' . rtrim(rtrim(sprintf('%.1f', $leaveBalance), '0'), '.') . ' hari';
                            } elseif ($case->value === \App\Enums\LeaveType::OFF_SPV->value && $offRemaining !== null) {
                                $hint = 'Sisa ' . $offRemaining . ' hari';
                            }
                        @endphp
                        <option value="{{ $case->value }}" data-hint="{{ $hint }}" @selected(old('type', $typeValue) === $case->value)>
                            {{ $case->label() }}{{ $hint ? ' (' . $hint . ')' : '' }}
                        </option>
                    @endforeach
                </select>

                <div id="balance-info-container" style="display:none; margin-top:12px;">
                    @if($leaveBalance > 0)
                        <div class="lr-info lr-info--blue">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <strong>Sisa Cuti: {{ rtrim(rtrim(sprintf('%.1f', $leaveBalance), '0'), '.') }} Hari</strong>
                                <p>Cuti akan berkurang otomatis setelah disetujui.</p>
                            </div>
                        </div>
                    @else
                        <div class="lr-info lr-info--red">
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

                <div id="special-leave-box" class="lr-special-box" style="display:none; margin-top:12px;">
                    <label class="lr-filter-label" style="margin-bottom:6px;">Kategori Cuti Khusus <span class="lr-required">*</span></label>
                    <select name="special_leave_detail" id="edit_special_leave" class="lr-filter-input">
                        <option value="">-- Pilih Alasan --</option>
                        @foreach($specialLeaveList as $sl)
                            <option value="{{ $sl['id'] }}" data-days="{{ $sl['days'] }}" @selected(old('special_leave_detail', $item->special_leave_category) === $sl['id'])>
                                {{ $sl['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <div id="special-limit-badge" class="lr-special-badge" style="display:none;"></div>
                </div>
            </div>

            <div class="lr-form-section">
                <div class="lr-form-section-title">Periode Izin</div>
                <div class="lr-input-wrap">
                    <svg class="lr-input__icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <input type="text" id="date_range" class="lr-filter-input lr-input--icon" value="{{ $oldRange }}" placeholder="Pilih tanggal mulai - selesai" autocomplete="off">
                </div>
                <div id="duration-display" class="lr-duration" style="display:none;"></div>
                <div class="lr-warnings">
                    <small id="cuti-rule" class="lr-rule"></small>
                    <div id="h7-warning" class="lr-warning" style="display:none;"></div>
                    <div id="special-limit-warning" class="lr-warning" style="display:none;"></div>
                    <div id="tenure-warning" class="lr-warning" style="display:none;" data-under-one-year="{{ $underOneYear ? '1' : '0' }}"></div>
                </div>
                <div id="duplicate-warning" class="lr-warning lr-warning--error" style="display:none;"></div>
            </div>

            <div class="lr-form-section" id="time-fields" style="display: {{ $isTimeBased ? 'block' : 'none' }};">
                <div class="lr-form-section-title">Jam Izin</div>
                <div class="lr-form-row">
                    <div class="lr-form-group">
                        <label class="lr-filter-label" id="start-time-label">Mulai</label>
                        <input type="time" name="start_time" id="edit_start_time" value="{{ old('start_time', $item->start_time ? $item->start_time->format('H:i') : '') }}" class="lr-filter-input">
                    </div>
                    <div class="lr-form-group" id="end-time-wrapper">
                        <label class="lr-filter-label">Selesai</label>
                        <input type="time" name="end_time" id="edit_end_time" value="{{ old('end_time', $item->end_time ? $item->end_time->format('H:i') : '') }}" class="lr-filter-input">
                    </div>
                </div>
            </div>

            <div class="lr-form-section" id="substitute-pic-section" style="display:none;">
                <div class="lr-form-section-title">PIC Pengganti</div>
                <div class="lr-form-row">
                    <div class="lr-form-group">
                        <label class="lr-filter-label">Nama PIC <span class="lr-required">*</span></label>
                        <input type="text" name="substitute_pic" id="edit_substitute_pic" value="{{ old('substitute_pic', $item->substitute_pic) }}" class="lr-filter-input" placeholder="Nama rekan pengganti">
                    </div>
                    <div class="lr-form-group">
                        <label class="lr-filter-label">Nomor HP PIC <span class="lr-required">*</span></label>
                        <input type="tel" name="substitute_phone" id="edit_substitute_phone" value="{{ old('substitute_phone', $item->substitute_phone) }}" class="lr-filter-input" placeholder="Contoh: 0812...">
                    </div>
                </div>
                <p class="lr-helper">Wajib diisi untuk koordinasi selama Anda tidak hadir.</p>
            </div>

            <div class="lr-form-section">
                <div class="lr-form-section-title">Alasan / Keterangan <span class="lr-required">*</span></div>
                <textarea name="reason" id="edit_reason" rows="4" class="lr-filter-input" placeholder="Jelaskan alasan pengajuan Anda secara detail..." maxlength="5000" required>{{ old('reason', $item->reason) }}</textarea>
                <small id="reason-counter" class="lr-helper" style="text-align:right; display:block;">0 / 5000</small>
            </div>

            <div class="lr-form-section">
                <div class="lr-form-section-title">Bukti Pendukung <span class="lr-optional">(opsional)</span></div>
                @if($photoUrl && $item->photo)
                    @php
                        $filePath = 'leave_photos/' . $item->photo;
                        $fileExists = \Illuminate\Support\Facades\Storage::disk('public')->exists($filePath);
                        $fileSize = $fileExists ? \Illuminate\Support\Facades\Storage::disk('public')->size($filePath) : 0;
                        $fileSizeFormatted = $fileSize > 0
                            ? ($fileSize < 1024
                                ? $fileSize . ' B'
                                : ($fileSize < 1048576
                                    ? round($fileSize / 1024, 1) . ' KB'
                                    : round($fileSize / 1048576, 1) . ' MB'))
                            : 'Tidak diketahui';
                    @endphp
                    <div class="lr-existing-file">
                        <span class="lr-existing-file-label">File saat ini:</span>
                        @if($isImageDoc)
                            <img src="{{ $photoUrl }}" alt="Bukti pendukung" class="lr-existing-file-preview">
                        @endif
                        <a href="{{ $photoUrl }}" target="_blank" class="lr-existing-file-link">
                            <span class="lr-existing-file-badge">{{ strtoupper($docExt) }}</span>
                            <span class="lr-existing-file-size">Ukuran: {{ $fileSizeFormatted }}</span>
                        </a>
                    </div>
                @endif
                <div class="lr-upload" id="uploadBox">
                    <input type="file" name="photo" id="edit_photo" class="lr-upload__input" accept=".jpg,.jpeg,.png,.webp,.heic,.heif,.pdf,.doc,.docx,.xls,.xlsx" data-max-file-size="8388608" data-max-file-label="8 MB">
                    <div class="lr-upload__content">
                        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span class="lr-upload__title">Klik untuk ganti file</span>
                        <span class="lr-upload__desc">JPG, PNG, PDF, DOCX (Maks 8MB)</span>
                    </div>
                </div>
                <div id="photoPreviewContainer" class="lr-preview" style="display:none;">
                    <img id="photoPreview" src="#" alt="Preview">
                    <div id="photoFileInfo" class="lr-file-info"></div>
                    <button type="button" class="lr-preview__remove" id="removePreview" aria-label="Hapus preview">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <small class="lr-form-hint">Kosongkan jika tidak ingin mengubah file. Maksimal 8 MB.</small>
            </div>

            <div id="location-fields" style="display:none;">
                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $item->latitude) }}">
                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $item->longitude) }}">
                <input type="hidden" name="accuracy_m" id="accuracy_m" value="{{ old('accuracy_m', $item->accuracy_m) }}">
                <input type="hidden" name="location_captured_at" id="location_captured_at" value="{{ old('location_captured_at', $item->location_captured_at) }}">
            </div>

            <div class="lr-form-actions">
                <a href="{{ route('leave-requests.index') }}" class="lr-btn-reset">Batal</a>
                <button type="submit" class="lr-btn-primary" id="btn-submit-edit">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const typeSelect = document.getElementById('edit_type');
            const specialBox = document.getElementById('special-leave-box');
            const specialSelect = document.getElementById('edit_special_leave');
            const specialBadge = document.getElementById('special-limit-badge');
            const specialWarning = document.getElementById('special-limit-warning');
            const timeFields = document.getElementById('time-fields');
            const endTimeWrapper = document.getElementById('end-time-wrapper');
            const substituteSection = document.getElementById('substitute-pic-section');
            const balanceInfo = document.getElementById('balance-info-container');
            const durationDisplay = document.getElementById('duration-display');
            const cutiRule = document.getElementById('cuti-rule');
            const h7Warning = document.getElementById('h7-warning');
            const tenureWarning = document.getElementById('tenure-warning');
            const duplicateWarning = document.getElementById('duplicate-warning');
            const locationFields = document.getElementById('location-fields');
            const dateRangeInput = document.getElementById('date_range');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const leaveId = document.getElementById('leave_id').value;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const reasonInput = document.getElementById('edit_reason');
            const reasonCounter = document.getElementById('reason-counter');
            const photoInput = document.getElementById('edit_photo');
            const uploadBox = document.getElementById('uploadBox');
            const photoPreviewContainer = document.getElementById('photoPreviewContainer');
            const photoPreview = document.getElementById('photoPreview');
            const photoFileInfo = document.getElementById('photoFileInfo');
            const removePreview = document.getElementById('removePreview');

            const timeBasedTypes = ['IZIN_TELAT', 'IZIN_TENGAH_KERJA', 'IZIN_PULANG_AWAL', 'IZIN'];
            const specialTypes = ['CUTI', 'CUTI_KHUSUS', 'SAKIT'];
            const specialLeaveLimits = {
                'NIKAH_KARYAWAN': 3, 'ISTRI_MELAHIRKAN': 2, 'ISTRI_KEGUGURAN': 2,
                'KHITANAN_ANAK': 2, 'PEMBAPTISAN_ANAK': 2, 'NIKAH_ANAK': 2,
                'DEATH_EXTENDED': 2, 'DEATH_CORE': 2, 'DEATH_HOUSE': 1,
                'HAJI': 40, 'UMROH': 14
            };
            const underOneYear = tenureWarning.dataset.underOneYear === '1';
            const leaveBalance = parseFloat('{{ $leaveBalance }}') || 0;

            function updateReasonCounter() {
                const len = reasonInput.value.length;
                reasonCounter.textContent = len + ' / 5000';
                reasonCounter.style.color = len > 4500 ? 'var(--error)' : 'var(--text-muted)';
            }
            reasonInput.addEventListener('input', updateReasonCounter);
            updateReasonCounter();

            const fp = flatpickr(dateRangeInput, {
                mode: 'range',
                dateFormat: 'Y-m-d',
                allowInput: true,
                locale: { rangeSeparator: ' sampai ' },
                defaultDate: startDateInput.value && endDateInput.value ? [startDateInput.value, endDateInput.value] : null,
                onChange: function(selectedDates, dateStr) {
                    if (selectedDates.length >= 2) {
                        const start = selectedDates[0];
                        const end = selectedDates[1];
                        startDateInput.value = formatDate(start);
                        endDateInput.value = formatDate(end);
                    } else if (selectedDates.length === 1) {
                        startDateInput.value = formatDate(selectedDates[0]);
                        endDateInput.value = formatDate(selectedDates[0]);
                    }
                    validateDates();
                }
            });

            function formatDate(d) {
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return y + '-' + m + '-' + day;
            }

            function toggleFields() {
                const type = typeSelect.value;
                const isSpecial = type === 'CUTI_KHUSUS';
                const isTime = timeBasedTypes.includes(type);
                const needsPic = specialTypes.includes(type);

                specialBox.style.display = isSpecial ? 'block' : 'none';
                if (!isSpecial) {
                    specialSelect.value = '';
                    specialBadge.style.display = 'none';
                    specialWarning.style.display = 'none';
                }

                timeFields.style.display = isTime ? 'block' : 'none';
                if (type === 'IZIN_TELAT' || type === 'IZIN_PULANG_AWAL') {
                    endTimeWrapper.style.display = 'none';
                    document.getElementById('start-time-label').textContent = type === 'IZIN_TELAT' ? 'Estimasi Jam Tiba' : 'Jam Pulang';
                } else {
                    endTimeWrapper.style.display = 'block';
                    document.getElementById('start-time-label').textContent = 'Mulai';
                }
                if (!isTime) {
                    document.getElementById('edit_start_time').value = '';
                    document.getElementById('edit_end_time').value = '';
                }

                substituteSection.style.display = needsPic ? 'block' : 'none';
                if (!needsPic) {
                    document.getElementById('edit_substitute_pic').value = '';
                    document.getElementById('edit_substitute_phone').value = '';
                }

                balanceInfo.style.display = type === 'CUTI' ? 'block' : 'none';
                cutiRule.style.display = type === 'CUTI' ? 'block' : 'none';

                locationFields.style.display = type === 'IZIN_TELAT' ? 'block' : 'none';
                if (type === 'IZIN_TELAT') {
                    captureLocation();
                }

                validateDates();
            }

            specialSelect.addEventListener('change', function () {
                const selected = specialSelect.options[specialSelect.selectedIndex];
                const days = selected.getAttribute('data-days');
                if (days) {
                    specialBadge.textContent = 'Maksimal ' + days + ' Hari';
                    specialBadge.style.display = 'inline-block';
                } else {
                    specialBadge.style.display = 'none';
                }
                validateDates();
            });

            typeSelect.addEventListener('change', toggleFields);

            function validateDates() {
                const type = typeSelect.value;
                const start = startDateInput.value;
                const end = endDateInput.value;

                durationDisplay.style.display = 'none';
                h7Warning.style.display = 'none';
                tenureWarning.style.display = 'none';
                specialWarning.style.display = 'none';
                duplicateWarning.style.display = 'none';

                if (!start || !end) return;

                if (type === 'CUTI') {
                    if (underOneYear) {
                        tenureWarning.textContent = 'Maaf, masa kerja Anda belum 1 tahun. Belum berhak mengajukan Cuti Tahunan.';
                        tenureWarning.style.display = 'block';
                    }
                    const s = new Date(start);
                    const today = new Date();
                    today.setHours(0,0,0,0);
                    const diff = Math.ceil((s - today) / (1000 * 60 * 60 * 24));
                    if (diff >= 0 && diff < 7) {
                        h7Warning.textContent = 'Pengajuan H-' + diff + ' (kurang dari H-7). Dihitung Potong Uang Makan.';
                        h7Warning.style.display = 'block';
                    }
                    cutiRule.textContent = 'Cuti Tahunan akan memotong saldo cuti sesuai hari kerja efektif.';
                } else if (type === 'CUTI_KHUSUS') {
                    const category = specialSelect.value;
                    const limit = category ? (specialLeaveLimits[category] || 0) : 0;
                    if (limit) {
                        const s = new Date(start); const e = new Date(end);
                        const days = Math.max(0, Math.round((e - s) / (1000 * 60 * 60 * 24)) + 1);
                        if (days > limit) {
                            specialWarning.textContent = 'Durasi ' + days + ' hari kalender melebihi batas maksimal ' + limit + ' hari untuk kategori ini.';
                            specialWarning.style.display = 'block';
                        }
                    }
                }

                calculateEffectiveDays(start, end);
                checkDuplicate(start, end);
            }

            function calculateEffectiveDays(start, end) {
                fetch('{{ route('leave-requests.calculate-effective-days') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ start_date: start, end_date: end })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.label) {
                        durationDisplay.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> <span>' + data.label + '</span>';
                        durationDisplay.style.display = 'flex';

                        if (typeSelect.value === 'CUTI' && data.days > leaveBalance) {
                            durationDisplay.style.background = '#FEF2F2';
                            durationDisplay.style.borderColor = '#FECACA';
                            durationDisplay.style.color = '#991B1B';
                        } else {
                            durationDisplay.style.background = '#F0FDF4';
                            durationDisplay.style.borderColor = '#BBF7D0';
                            durationDisplay.style.color = '#15803D';
                        }
                    }
                })
                .catch(() => {});
            }

            function checkDuplicate(start, end) {
                const type = typeSelect.value;
                if (!type || !start || !end) return;

                fetch('{{ route('leave-requests.checkDuplicate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ type: type, start_date: start, end_date: end, exclude_id: leaveId })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.has_duplicate) {
                        duplicateWarning.textContent = data.message || 'Tanggal bertabrakan dengan pengajuan lain.';
                        duplicateWarning.style.display = 'block';
                    } else {
                        duplicateWarning.style.display = 'none';
                    }
                })
                .catch(() => {});
            }

            function captureLocation() {
                if (!navigator.geolocation) return;
                navigator.geolocation.getCurrentPosition(function(pos) {
                    document.getElementById('latitude').value = pos.coords.latitude;
                    document.getElementById('longitude').value = pos.coords.longitude;
                    document.getElementById('accuracy_m').value = pos.coords.accuracy;
                    document.getElementById('location_captured_at').value = new Date().toISOString();
                }, function() {}, { enableHighAccuracy: true, timeout: 10000 });
            }

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
            }

            function renderFileInfo(file) {
                if (!photoFileInfo || !file) return;
                photoFileInfo.innerHTML = '<span class="lr-file-info__name">' + file.name + '</span>' +
                    '<span class="lr-file-info__size">Ukuran: ' + formatFileSize(file.size) + '</span>';
            }

            // File upload preview & size validation
            photoInput.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;
                const maxBytes = parseInt(this.dataset.maxFileSize, 10) || 8388608;
                if (file.size > maxBytes) {
                    window.showToast('Ukuran file melebihi ' + (this.dataset.maxFileLabel || '8 MB') + '. Pilih file yang lebih kecil.', 'warning');
                    this.value = '';
                    photoPreviewContainer.style.display = 'none';
                    uploadBox.style.display = 'block';
                    if (photoFileInfo) photoFileInfo.innerHTML = '';
                    return;
                }
                renderFileInfo(file);
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        photoPreview.src = e.target.result;
                        photoPreviewContainer.style.display = 'block';
                        uploadBox.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                } else {
                    photoPreview.src = '';
                    photoPreviewContainer.style.display = 'block';
                    uploadBox.style.display = 'none';
                }
            });

            removePreview.addEventListener('click', function () {
                photoInput.value = '';
                photoPreview.src = '#';
                photoPreviewContainer.style.display = 'none';
                uploadBox.style.display = 'block';
                if (photoFileInfo) photoFileInfo.innerHTML = '';
            });

            document.getElementById('edit-leave-form').addEventListener('submit', function (e) {
                const type = typeSelect.value;
                const start = startDateInput.value;
                const end = endDateInput.value;

                if (!type) { window.showToast('Pilih jenis pengajuan.', 'warning'); e.preventDefault(); return; }
                if (!start || !end) { window.showToast('Pilih periode tanggal.', 'warning'); e.preventDefault(); return; }

                if (specialTypes.includes(type)) {
                    const pic = document.getElementById('edit_substitute_pic').value.trim();
                    const phone = document.getElementById('edit_substitute_phone').value.trim();
                    if (!pic || !phone) {
                        window.showToast('Nama PIC dan No. HP PIC wajib diisi untuk tipe ini.', 'warning');
                        e.preventDefault(); return;
                    }
                }

                if (type === 'CUTI_KHUSUS' && !specialSelect.value) {
                    window.showToast('Pilih kategori cuti khusus.', 'warning');
                    e.preventDefault(); return;
                }

                if (type === 'IZIN_TELAT' && !document.getElementById('edit_start_time').value) {
                    window.showToast('Estimasi jam tiba wajib diisi.', 'warning');
                    e.preventDefault(); return;
                }
                if (type === 'IZIN_TENGAH_KERJA' && (!document.getElementById('edit_start_time').value || !document.getElementById('edit_end_time').value)) {
                    window.showToast('Jam mulai dan selesai wajib diisi.', 'warning');
                    e.preventDefault(); return;
                }
                if (type === 'IZIN_PULANG_AWAL' && !document.getElementById('edit_start_time').value) {
                    window.showToast('Jam pulang wajib diisi.', 'warning');
                    e.preventDefault(); return;
                }

                if (type === 'CUTI' && underOneYear) {
                    window.showToast('Maaf, masa kerja Anda belum 1 tahun. Belum berhak mengajukan Cuti Tahunan.', 'warning');
                    e.preventDefault(); return;
                }

                if (duplicateWarning.style.display === 'block') {
                    if (!confirm('Tanggal yang dipilih bertabrakan dengan pengajuan lain. Tetap simpan?')) {
                        e.preventDefault();
                    }
                }
            });

            toggleFields();
            specialSelect.dispatchEvent(new Event('change'));
            if (startDateInput.value && endDateInput.value) {
                validateDates();
            }
        });
    </script>
    @endpush

    <style>
        :root {
            --primary-dark: #0A3D62;
            --primary: #145DA0;
            --primary-light: #1E81B0;
            --success: #22C55E;
            --error: #EF4444;
            --warning: #F59E0B;
            --info: #3B82F6;
            --text-primary: #111827;
            --text-secondary: #374151;
            --text-muted: #6B7280;
            --text-light: #9CA3AF;
            --border: #E5E7EB;
            --border-light: #F3F4F6;
            --white: #FFFFFF;
            --gray-50: #F9FAFB;
            --danger: #EF4444;
            --danger-light: #FEF2F2;
        }
        .section-header-inline { display: flex; align-items: center; gap: 10px; }
        .section-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .section-icon svg { width: 16px; height: 16px; }
        .section-title { margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.01em; line-height: 1.25; }
        .section-subtitle { margin: 0; font-size: 0.8125rem; color: var(--text-muted); font-weight: 500; line-height: 1.35; }
        .icon-navy { background: rgba(10, 61, 98, 0.08); color: var(--primary-dark); }

        .back-btn {
            display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 12px 0 10px;
            background: var(--white); border: 1px solid var(--border); border-radius: 10px;
            color: var(--text-muted); text-decoration: none; transition: all 0.15s ease;
            flex-shrink: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.04); margin-bottom: 16px;
        }
        .back-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--gray-50); }
        .back-btn:hover svg { transform: translateX(-2px); }
        .back-btn svg { transition: transform 0.2s ease; flex-shrink: 0; }
        .back-btn-text { font-size: 0.75rem; font-weight: 600; line-height: 1; }

        .lr-alert { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 13px; font-weight: 500; }
        .lr-alert--success { background: rgba(34, 197, 94, 0.08); border: 1px solid rgba(34, 197, 94, 0.25); color: #16a34a; }
        .lr-alert--error { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; }

        .lr-form-card { background: var(--white); border-radius: 16px; padding: 16px; border: 1px solid var(--border-light); box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .lr-form-section { margin-bottom: 20px; }
        .lr-form-section:last-child { margin-bottom: 0; }
        .lr-form-section-title { font-size: 0.875rem; font-weight: 700; color: var(--text-primary); margin-bottom: 10px; }
        .lr-form-row { display: grid; grid-template-columns: 1fr; gap: 12px; }
        @media (min-width: 640px) { .lr-form-row { grid-template-columns: 1fr 1fr; } }
        .lr-form-group { display: flex; flex-direction: column; gap: 6px; }
        .lr-filter-label { font-size: 0.6875rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em; }
        .lr-filter-input { width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 13px; color: var(--text-primary); background: var(--white); transition: all 0.2s ease; outline: none; font-family: inherit; }
        .lr-filter-input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1); }
        .lr-optional { font-weight: 500; color: var(--text-muted); font-size: 0.75rem; text-transform: none; }
        .lr-required { color: var(--error); font-weight: 700; }
        .lr-form-hint { display: block; margin-top: 6px; font-size: 0.75rem; color: var(--text-muted); }
        .lr-helper { color: var(--text-muted); font-size: 0.75rem; font-weight: 500; margin-top: 6px; line-height: 1.5; }

        .lr-special-box { background: #F8FAFC; border: 1px solid var(--border-light); border-radius: 12px; padding: 12px; }
        .lr-special-badge { display: inline-block; margin-top: 8px; padding: 4px 10px; background: rgba(20, 93, 160, 0.08); color: var(--primary); border-radius: 20px; font-size: 0.75rem; font-weight: 600; }

        .lr-existing-file { margin-bottom: 12px; padding: 12px; background: var(--border-light); border-radius: 10px; }
        .lr-existing-file-label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; }
        .lr-existing-file-preview { display: block; max-width: 200px; max-height: 120px; border-radius: 8px; margin-bottom: 8px; object-fit: cover; }
        .lr-existing-file-link { display: inline-flex; align-items: center; gap: 8px; font-size: 0.8125rem; color: var(--primary); text-decoration: none; word-break: break-all; }
.lr-existing-file-link:hover { text-decoration: underline; }
.lr-existing-file-badge { display: inline-block; padding: 2px 8px; background: rgba(20, 93, 160, 0.08); color: var(--primary); border-radius: 6px; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; }
.lr-existing-file-size { color: var(--text-muted); font-weight: 500; }

        .lr-form-actions { display: flex; gap: 10px; margin-top: 24px; }
        .lr-btn-primary { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 18px; background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff; border: none; border-radius: 12px; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s ease; cursor: pointer; flex: 1; }
        .lr-btn-primary:hover { box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32); transform: translateY(-1px); }
        .lr-btn-reset { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 18px; background: var(--white); color: var(--text-muted); border: 1.5px solid var(--border); border-radius: 12px; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s ease; flex: 1; }
        .lr-btn-reset:hover { background: var(--danger-light); border-color: #fecaca; color: var(--danger); }

        .lr-input-wrap { position: relative; }
        .lr-input__icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-light); pointer-events: none; }
        .lr-input--icon { padding-left: 42px !important; }

        .lr-duration { display: flex; align-items: center; gap: 8px; margin-top: 10px; padding: 10px 14px; background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; color: #15803D; font-size: 0.875rem; font-weight: 600; }
        .lr-warnings { margin-top: 8px; display: flex; flex-direction: column; gap: 8px; }
        .lr-warning { padding: 10px 12px; background: #FEFCE8; border: 1px solid #FDE68A; border-radius: 10px; color: #854D0E; font-size: 0.8125rem; font-weight: 500; line-height: 1.5; }
        .lr-warning--error { background: #FEF2F2; border-color: #FECACA; color: #991B1B; }
        .lr-rule { display: block; color: var(--text-muted); font-size: 0.75rem; font-weight: 500; line-height: 1.5; }

        .lr-info { display: flex; gap: 10px; padding: 12px 14px; border-radius: 10px; font-size: 0.8125rem; }
        .lr-info svg { flex-shrink: 0; margin-top: 1px; }
        .lr-info strong { font-weight: 600; display: block; margin-bottom: 2px; }
        .lr-info p { margin: 0; font-size: 0.75rem; opacity: 0.9; }
        .lr-info--blue { background: #EFF6FF; border: 1px solid #DBEAFE; color: #1E40AF; }
        .lr-info--red { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; }

        .lr-upload { position: relative; border: 2px dashed var(--border); border-radius: 12px; padding: 20px; text-align: center; background: var(--gray-50); cursor: pointer; transition: all 0.2s ease; }
        .lr-upload:hover { border-color: var(--primary); background: rgba(20, 93, 160, 0.03); }
        .lr-upload__input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .lr-upload__content { display: flex; flex-direction: column; align-items: center; gap: 6px; color: var(--text-muted); }
        .lr-upload__content svg { color: var(--text-light); }
        .lr-upload__title { font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); }
        .lr-upload__desc { font-size: 0.75rem; color: var(--text-light); font-weight: 500; }
        .lr-preview { position: relative; margin-top: 10px; padding: 10px; background: var(--gray-50); border: 1px solid var(--border-light); border-radius: 10px; }
        .lr-preview img { max-width: 100%; max-height: 180px; border-radius: 8px; display: block; margin: 0 auto; }
        .lr-preview__remove { position: absolute; top: 6px; right: 6px; width: 28px; height: 28px; border-radius: 50%; background: rgba(0,0,0,0.5); color: #fff; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .lr-file-info { margin-top: 10px; padding: 8px 10px; background: #F8FAFC; border: 1px solid var(--border-light); border-radius: 8px; font-size: 0.75rem; color: var(--text-secondary); display: flex; flex-direction: column; gap: 2px; }
        .lr-file-info__name { font-weight: 600; color: var(--text-primary); word-break: break-all; }
        .lr-file-info__size { color: var(--text-muted); }
    </style>
</x-app>
