<x-app title="Edit Pengajuan Bawahan">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Edit Pengajuan Bawahan</h1>
                <p class="section-subtitle">Sesuaikan data pengajuan milik {{ $leave->user->name }}</p>
            </div>
        </div>
    </x-slot>

    <div class="apv-edit-page">

        {{-- Back Button --}}
        <a href="{{ route('approval.show', $leave->id) }}" class="back-btn" aria-label="Kembali ke detail pengajuan">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        {{-- Info Alert --}}
        <div class="apv-info-alert">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <strong>Mode Supervisor:</strong> Anda sedang mengubah data pengajuan milik <strong>{{ $leave->user->name }}</strong>.
                <br>Perubahan yang Anda simpan akan otomatis disetujui oleh Anda dan diteruskan ke HRD.
            </div>
        </div>

        @if($errors->any())
            <div class="apv-alert apv-alert--error">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ $errors->first() }}
            </div>
        @endif

        <div class="apv-card">
            <form method="POST" action="{{ route('approval.update', $leave->id) }}" enctype="multipart/form-data" id="editForm">
                @csrf
                @method('PUT')

                {{-- 1. JENIS PENGAJUAN --}}
                <div class="apv-form-group">
                    <label class="apv-form-label">Jenis Pengajuan <span class="apv-req">*</span></label>
                    <div class="apv-radio-group">
                        @foreach (\App\Enums\LeaveType::cases() as $case)
                            @if ($case->value === \App\Enums\LeaveType::OFF_SPV->value && !$leave->user->isSupervisor())
                                @continue
                            @endif
                            <label class="apv-radio-card">
                                <input type="radio" name="type" value="{{ $case->value }}" @checked(old('type', $leave->type->value) === $case->value)>
                                <span class="apv-radio-label">{{ $case->label() }}</span>
                            </label>
                        @endforeach
                    </div>

                    {{-- DROPDOWN CUTI KHUSUS --}}
                    <div id="special-leave-container">
                        <label for="special_leave_detail" class="apv-form-label" style="margin-top: 12px;">Pilih Kategori Cuti Khusus <span class="apv-req">*</span></label>
                        @php
                            $specialLeaveList = [
                                ['id' => 'CUTI_MELAHIRKAN', 'label' => 'Cuti Melahirkan', 'days' => 90],
                                ['id' => 'ISTRI_MELAHIRKAN', 'label' => 'Istri Melahirkan', 'days' => 2],
                                ['id' => 'NIKAH_KARYAWAN', 'label' => 'Menikah', 'days' => 4],
                                ['id' => 'DEATH_CORE', 'label' => 'Kematian Inti (Ortu/Mertua/Menantu/Istri/Suami/Anak)', 'days' => 2],
                                ['id' => 'DEATH_EXTENDED', 'label' => 'Kematian (Adik/Kakak/Ipar)', 'days' => 2],
                                ['id' => 'DEATH_HOUSE', 'label' => 'Kematian Anggota Rumah', 'days' => 1],
                                ['id' => 'ISTRI_KEGUGURAN', 'label' => 'Istri Keguguran', 'days' => 2],
                                ['id' => 'KHITANAN_ANAK', 'label' => 'Khitanan Anak', 'days' => 2],
                                ['id' => 'PEMBAPTISAN_ANAK', 'label' => 'Pembaptisan Anak', 'days' => 2],
                                ['id' => 'NIKAH_ANAK', 'label' => 'Pernikahan Anak', 'days' => 2],
                                ['id' => 'HAJI', 'label' => 'Ibadah Haji (1x)', 'days' => 40],
                                ['id' => 'UMROH', 'label' => 'Ibadah Umroh (1x)', 'days' => 14],
                            ];
                            $currentSpecial = old('special_leave_detail', $leave->special_leave_category);
                        @endphp
                        <select name="special_leave_detail" id="special_leave_detail" class="apv-form-control" style="margin-top:8px;">
                            <option value="" disabled @if(!$currentSpecial) selected @endif>-- Pilih Alasan --</option>
                            @foreach($specialLeaveList as $sl)
                                <option value="{{ $sl['id'] }}" data-days="{{ $sl['days'] }}" @selected($currentSpecial == $sl['id'])>
                                    {{ $sl['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <div id="special-leave-badge" class="apv-info-badge">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span id="special-leave-text">Maksimal X Hari</span>
                        </div>
                    </div>
                </div>

                {{-- 2. PERIODE --}}
                <div class="apv-form-group">
                    <label for="date_range" class="apv-form-label">Periode Izin <span class="apv-req">*</span></label>
                    @php
                        $startVal = old('start_date', $leave->start_date ? $leave->start_date->format('Y-m-d') : '');
                        $endVal   = old('end_date', $leave->end_date ? $leave->end_date->format('Y-m-d') : '');
                        $rangeVal = ($startVal && $endVal) ? ($startVal . ' sampai ' . $endVal) : $startVal;
                    @endphp
                    <input type="text" id="date_range" name="date_range" class="apv-form-control" value="{{ $rangeVal }}" placeholder="Pilih tanggal mulai sampai selesai" autocomplete="off">
                    <input type="hidden" name="start_date" id="start_date" value="{{ $startVal }}">
                    <input type="hidden" name="end_date" id="end_date" value="{{ $endVal }}">
                    <div id="special-limit-warning" class="apv-limit-warning" style="display:none;"></div>
                </div>

                {{-- 3. JAM (Kondisional) --}}
                <div class="apv-form-group" id="worktime-field" style="display:none;">
                    <label id="worktime-label" class="apv-form-label">Jam Izin</label>
                    <div class="apv-time-range">
                        <div class="apv-time-input-box">
                            <input type="time" name="start_time" id="start_time_input" class="apv-form-control" value="{{ old('start_time', $leave->start_time ? $leave->start_time->format('H:i') : '') }}">
                        </div>
                        <span id="worktime-separator" class="apv-time-sep">s/d</span>
                        <div id="end_time_wrapper" class="apv-time-input-box">
                            <input type="time" name="end_time" id="end_time_input" class="apv-form-control" value="{{ old('end_time', $leave->end_time ? $leave->end_time->format('H:i') : '') }}">
                        </div>
                    </div>
                </div>

                {{-- 4. PIC PENGGANTI --}}
                <div id="substitute-pic-section" class="apv-pic-section">
                    <p class="apv-pic-section-title">Informasi Pendelegasian Tugas</p>
                    <div class="apv-pic-grid">
                        <div class="apv-form-group" style="margin-bottom:0;">
                            <label for="substitute_pic" class="apv-form-label">Nama PIC Pengganti</label>
                            <input type="text" name="substitute_pic" id="substitute_pic" class="apv-form-control" value="{{ old('substitute_pic', $leave->substitute_pic) }}">
                        </div>
                        <div class="apv-form-group" style="margin-bottom:0;">
                            <label for="substitute_phone" class="apv-form-label">Nomor HP PIC</label>
                            <input type="text" name="substitute_phone" id="substitute_phone" class="apv-form-control" value="{{ old('substitute_phone', $leave->substitute_phone) }}">
                        </div>
                    </div>
                </div>

                {{-- 5. UPLOAD BUKTI --}}
                <div class="apv-form-group">
                    <label for="photoInput" class="apv-form-label">Update Bukti Pendukung <span class="apv-optional">(Opsional)</span></label>
                    <div class="apv-file-input-wrapper">
                        <input type="file" name="photo" id="photoInput" class="apv-form-control-file" accept="image/*,.pdf">
                    </div>
                    @if($leave->photo)
                        <div class="apv-current-file">
                            <span class="apv-current-file-label">File saat ini:</span>
                            <a href="{{ asset('storage/leave_photos/' . $leave->photo) }}" target="_blank">Lihat File</a>
                        </div>
                    @endif
                    <div id="photoPreviewContainer" class="apv-preview-container">
                        <p class="apv-preview-label">Preview File Baru:</p>
                        <img id="photoPreview" src="#" alt="Preview">
                    </div>
                </div>

                {{-- 6. ALASAN --}}
                <div class="apv-form-group">
                    <label for="reason" class="apv-form-label">Alasan / Keterangan <span class="apv-req">*</span></label>
                    <textarea name="reason" id="reason" rows="4" class="apv-form-control" required>{{ old('reason', $leave->reason) }}</textarea>
                </div>

                <div class="apv-bottom-spacer"></div>
            </form>
        </div>
    </div>

    {{-- Fixed Action Bar --}}
    <div class="apv-action-bar">
        <div class="apv-action-main">
            <button class="apv-action-btn apv-action-btn--primary" type="submit" form="editForm">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
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

    <style>
        /* ========================================== */
        /* PAGE LAYOUT                                */
        /* ========================================== */
        .apv-edit-page {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding-bottom: 8px;
        }

        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .apv-info-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: rgba(245, 158, 11, 0.08);
            border: 1px solid rgba(245, 158, 11, 0.25);
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 13px;
            font-weight: 500;
            color: #92400e;
            line-height: 1.6;
        }
        .apv-info-alert svg {
            flex-shrink: 0;
            margin-top: 1px;
        }
        .apv-info-alert strong {
            font-weight: 700;
        }

        .apv-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
        }
        .apv-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        /* ========================================== */
        /* BACK BUTTON (KIMI.md pattern)              */
        /* ========================================== */
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
            align-self: flex-start;
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

        /* ========================================== */
        /* SECTION HEADER (x-slot)                    */
        /* ========================================== */
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
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
        .icon-navy  { background: rgba(10, 61, 98, 0.08);  color: var(--primary-dark, #0A3D62); }

        /* ========================================== */
        /* CARD                                       */
        /* ========================================== */
        .apv-card {
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            padding: 24px 20px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        /* ========================================== */
        /* FORM GROUPS                                */
        /* ========================================== */
        .apv-form-group {
            margin-bottom: 24px;
        }
        .apv-form-group:last-child {
            margin-bottom: 0;
        }
        .apv-form-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin-bottom: 8px;
        }
        .apv-req {
            color: var(--error, #EF4444);
            margin-left: 2px;
        }
        .apv-optional {
            font-weight: 400;
            color: var(--text-muted, #6B7280);
        }

        /* ========================================== */
        /* FORM CONTROLS                              */
        /* ========================================== */
        .apv-form-control {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            background: var(--white, #FFFFFF);
            color: var(--text-primary, #111827);
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
            -webkit-appearance: none;
            appearance: none;
        }
        .apv-form-control:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        textarea.apv-form-control {
            resize: vertical;
            min-height: 100px;
            line-height: 1.6;
        }
        select.apv-form-control {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236B7280' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 40px;
        }

        /* ========================================== */
        /* RADIO CARDS                                */
        /* ========================================== */
        .apv-radio-group {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }
        .apv-radio-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--white, #FFFFFF);
        }
        .apv-radio-card:hover {
            border-color: var(--primary, #145DA0);
            background: rgba(20, 93, 160, 0.04);
        }
        .apv-radio-card input[type="radio"] {
            accent-color: var(--primary, #145DA0);
            width: 16px;
            height: 16px;
            margin: 0;
            cursor: pointer;
            flex-shrink: 0;
        }
        .apv-radio-label {
            font-size: 13px;
            color: var(--text-secondary, #374151);
            font-weight: 500;
            line-height: 1.3;
        }

        /* ========================================== */
        /* SPECIAL LEAVE                              */
        /* ========================================== */
        #special-leave-container {
            display: none;
            margin-top: 12px;
            padding: 14px;
            background: rgba(59, 130, 246, 0.06);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 10px;
        }
        #special-leave-container .apv-form-label {
            color: var(--primary, #145DA0);
        }
        #special-leave-badge {
            display: none;
            margin-top: 10px;
        }
        .apv-info-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--primary, #145DA0);
            color: #fff;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .apv-limit-warning {
            display: none;
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 13px;
            margin-top: 8px;
        }

        /* ========================================== */
        /* TIME RANGE                                 */
        /* ========================================== */
        .apv-time-range {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .apv-time-input-box {
            flex: 1;
            min-width: 120px;
        }
        .apv-time-sep {
            color: var(--text-muted, #6B7280);
            font-size: 13px;
            font-weight: 500;
        }

        /* ========================================== */
        /* PIC SECTION                                */
        /* ========================================== */
        #substitute-pic-section {
            display: none;
            margin-bottom: 24px;
            padding: 16px;
            background: var(--gray-50, #F5F7FA);
            border: 1px dashed var(--border-light, #E5E7EB);
            border-radius: 10px;
        }
        .apv-pic-section-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--primary-dark, #0A3D62);
            margin-bottom: 12px;
        }
        .apv-pic-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        /* ========================================== */
        /* FILE INPUT                                 */
        /* ========================================== */
        .apv-file-input-wrapper {
            border: 1.5px dashed var(--border-light, #E5E7EB);
            padding: 14px;
            border-radius: 10px;
            background: var(--gray-50, #F5F7FA);
        }
        .apv-form-control-file {
            width: 100%;
            font-size: 13px;
            color: var(--text-secondary, #374151);
        }
        .apv-preview-container {
            display: none;
            margin-top: 12px;
            padding: 10px;
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            background: var(--gray-50, #F5F7FA);
        }
        .apv-preview-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
            margin: 0 0 6px 0;
        }
        .apv-preview-container img {
            max-width: 100%;
            max-height: 280px;
            border-radius: 8px;
            display: block;
        }
        .apv-current-file {
            margin-top: 8px;
            font-size: 13px;
        }
        .apv-current-file-label {
            color: var(--success, #22C55E);
            font-weight: 500;
        }
        .apv-current-file a {
            color: var(--primary, #145DA0);
            text-decoration: underline;
            font-weight: 500;
        }
        .apv-current-file a:hover {
            color: var(--primary-dark, #0A3D62);
        }

        /* ========================================== */
        /* FIXED ACTION BAR                           */
        /* ========================================== */
        .apv-action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--white, #FFFFFF);
            border-top: 1px solid var(--border-light, #E5E7EB);
            padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
            z-index: 50;
            box-shadow: 0 -2px 12px rgba(0,0,0,0.06);
        }
        .apv-action-main {
            max-width: 720px;
            margin: 0 auto;
            width: 100%;
        }
        .apv-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
            white-space: nowrap;
            font-family: inherit;
            min-height: 48px;
            width: 100%;
        }
        .apv-action-btn svg { flex-shrink: 0; }

        .apv-action-btn--primary {
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #fff;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
        }
        .apv-action-btn--primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }

        .apv-bottom-spacer { height: 100px; }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (max-width: 480px) {
            .apv-radio-group {
                grid-template-columns: 1fr 1fr;
            }
            .apv-time-range {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }
            .apv-time-sep {
                display: none !important;
            }
            .apv-time-input-box {
                width: 100%;
                flex: none;
            }
            .apv-pic-grid {
                grid-template-columns: 1fr;
            }
            .apv-card {
                padding: 20px 16px;
            }
        }

        @media (min-width: 768px) {
            .apv-action-bar {
                padding: 14px 24px calc(14px + env(safe-area-inset-bottom));
            }
            .apv-card {
                padding: 28px 24px;
            }
        }
    </style>

</x-app>
