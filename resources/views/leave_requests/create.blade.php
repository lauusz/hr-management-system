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

            <div class="form-group">
                <label class="section-label">Jenis Pengajuan <span class="req">*</span></label>
                <div class="radio-group-container">
                    @php
                        $user = auth()->user();
                        
                        // FIX: Ambil value dari Enum jika role berupa object, atau string jika biasa
                        $roleValue = $user->role instanceof \App\Enums\UserRole 
                            ? $user->role->value 
                            : $user->role;
                            
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
                @error('type') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

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
                    
                    <div id="h7-warning" role="alert" aria-live="polite" class="alert-warning" style="display:none;"></div>
                    
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

            <div class="form-group" id="worktime-field" style="display:none;">
                <label id="worktime-label">Jam Izin Tengah Kerja</label>
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

            <div id="location" style="display:none;">
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
                <input type="hidden" name="accuracy_m" id="accuracy_m">
                <input type="hidden" name="location_captured_at" id="location_captured_at">
            </div>

            <div class="form-group">
                <label for="photoInput">Bukti Pendukung</label>
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
        /* Highlight selected radio card logic is handled by browser focus/accent, but we can add :has if needed, 
           keeping it simple for broad support */

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

        /* Mobile Adjustments */
        @media (max-width: 600px) {
            .card-header { flex-direction: column; gap: 12px; }
            .btn-back { align-self: flex-start; }
            .form-content { padding: 16px; }
            .radio-group-container { grid-template-columns: 1fr; } /* Stack radios on small screens */
            .time-range-wrapper { gap: 8px; }
            .separator { display: none !important; } /* Hide 's/d' text on mobile, inputs stack */
            .time-input-box { width: 100%; flex: none; }
            .form-actions .btn-primary { width: 100%; }
        }
    </style>

    <script>
        (function() {
            const typeRadios = document.querySelectorAll('input[name="type"]');
            const IZIN_TELAT = @json(\App\Enums\LeaveType::IZIN_TELAT->value);
            const IZIN_TENGAH_KERJA = @json(\App\Enums\LeaveType::IZIN_TENGAH_KERJA->value);
            const IZIN_PULANG_AWAL = @json(\App\Enums\LeaveType::IZIN_PULANG_AWAL->value);

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

            function toggleSection() {
                const val = selectedType();

                const isTelat = (val === IZIN_TELAT);
                if (isTelat) {
                    requestLocationIfNeeded();
                } else {
                    clearLocationValues();
                }

                const isTengahKerja = (val === IZIN_TENGAH_KERJA);
                const isPulangAwal = (val === IZIN_PULANG_AWAL);
                const showWorktime = isTengahKerja || isPulangAwal;

                if (worktimeField) {
                    worktimeField.style.display = showWorktime ? 'block' : 'none';
                }

                if (!startTimeInput || !endTimeInput) {
                    return;
                }

                if (isTengahKerja) {
                    if (worktimeLabel) worktimeLabel.innerHTML = 'Jam Izin Tengah Kerja';
                    if (worktimeSeparator) worktimeSeparator.style.display = 'inline';
                    if (endTimeWrapper) endTimeWrapper.style.display = 'block';

                    startTimeInput.required = true;
                    endTimeInput.required = true;

                    if (pulangInfo) {
                        pulangInfo.style.display = 'none';
                        pulangInfo.textContent = '';
                    }
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
                            pulangInfo.textContent =
                                'Jam pulang shift Anda: ' + shiftEnd +
                                '. Izin pulang awal maksimal 1 jam sebelum jam pulang.';
                        } else {
                            pulangInfo.style.display = 'block';
                            pulangInfo.textContent =
                                'Izin pulang awal maksimal 1 jam sebelum jam pulang shift.';
                        }
                    }
                } else {
                    if (worktimeLabel) worktimeLabel.innerHTML = 'Jam Izin Tengah Kerja';
                    if (worktimeSeparator) worktimeSeparator.style.display = 'inline';
                    if (endTimeWrapper) endTimeWrapper.style.display = 'block';

                    startTimeInput.required = false;
                    endTimeInput.required = false;
                    startTimeInput.value = '';
                    endTimeInput.value = '';

                    if (pulangInfo) {
                        pulangInfo.style.display = 'none';
                        pulangInfo.textContent = '';
                    }
                }
            }

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
                    }
                });
            }
        });
    </script>
    @endpush

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</x-app>