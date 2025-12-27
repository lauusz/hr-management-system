<x-app title="Buat Pengajuan Izin">
    @php
    $joinDate = auth()->user()->profile->tgl_bergabung ?? null;
    $underOneYear = false;

    if ($joinDate) {
    $start = \Carbon\Carbon::parse($joinDate)->startOfDay();
    $end = \Carbon\Carbon::today();
    $underOneYear = $start->diffInYears($end) < 1;
        }

        $oldStart=old('start_date');
        $oldEnd=old('end_date');
        $oldRange='' ;
        if ($oldStart && $oldEnd) {
        $oldRange=$oldStart . ' sampai ' . $oldEnd;
        } elseif ($oldStart) {
        $oldRange=$oldStart;
        }

        $shiftEndDisplay=null;
        try {
        $empShift=\App\Models\EmployeeShift::with('shift')
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
            {{ $errors->first() }}
        </div>
        @endif

        <form class="card form-leave" method="POST" action="{{ route('leave-requests.store') }}" enctype="multipart/form-data">
            @csrf

            <input type="hidden" id="shift_end_time" value="{{ $shiftEndDisplay }}">

            <div class="grid-form">
                <div class="field full">
                    <label><b>Jenis Pengajuan:</b></label>
                    <div class="card" style="padding:10px; display:grid; gap:6px">
                        @php
                        $user = auth()->user();
                        $role = strtoupper((string) ($user->role ?? ''));
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

    <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer;">
        <input
            type="radio"
            name="type"
            value="{{ $case->value }}"
            @if ($loop->first) required @endif
            @checked(old('type') === $case->value)
        >
        <span style="line-height:1.4">{{ $label }}</span>
    </label>
@endforeach


                    </div>
                    @error('type') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field full">
                    <label><b>Periode Izin:</b></label>
                    <input
                        type="text"
                        id="date_range"
                        name="date_range"
                        value="{{ $oldRange }}"
                        placeholder="Pilih tanggal mulai sampai selesai"
                        autocomplete="off">
                    <input type="hidden" name="start_date" id="start_date" value="{{ $oldStart }}">
                    <input type="hidden" name="end_date" id="end_date" value="{{ $oldEnd }}">
                    @error('start_date') <div class="error">{{ $message }}</div> @enderror
                    @error('end_date') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field full" id="worktime-field" style="display:none;">
                    <label id="worktime-label"><b>Jam Izin Tengah Kerja:</b></label>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                        <div style="flex:1;min-width:120px;">
                            <input
                                type="time"
                                name="start_time"
                                id="start_time_input"
                                value="{{ old('start_time') }}">
                        </div>
                        <span id="worktime-separator" style="font-size:0.9rem;">sampai</span>
                        <div id="end_time_wrapper" style="flex:1;min-width:120px;">
                            <input
                                type="time"
                                name="end_time"
                                id="end_time_input"
                                value="{{ old('end_time') }}">
                        </div>
                    </div>
                    <div id="pulang-info" class="hint" style="display:none;margin-top:4px;"></div>
                    @error('start_time') <div class="error">{{ $message }}</div> @enderror
                    @error('end_time') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field full">
                    <small id="cuti-rule" style="display:none; color:#6b7280;"></small>
                    <div id="h7-warning"
                        role="alert"
                        aria-live="polite"
                        style="display:none; margin-top:6px; background:#fef9c3; color:#854d0e; padding:8px 10px; border-radius:8px;">
                    </div>
                    <div id="tenure-warning"
                        role="alert"
                        aria-live="polite"
                        data-under-one-year="{{ $underOneYear ? '1' : '0' }}"
                        style="display:none; margin-top:6px; background:#fef9c3; color:#854d0e; padding:8px 10px; border-radius:8px;">
                    </div>
                </div>

                <div class="field full" id="location" style="display:none;">
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <input type="hidden" name="accuracy_m" id="accuracy_m">
                    <input type="hidden" name="location_captured_at" id="location_captured_at">
                </div>

                <div class="field full">
                    <label><b>Bukti Pendukung:</b></label>
                    <input
                        type="file"
                        name="photo"
                        id="photoInput"
                        accept=".jpg,.jpeg,.png,.webp,.heic,.heif,.pdf,.doc,.docx,.xls,.xlsx">
                    <div class="hint">
                        Format: JPG, JPEG, PNG, WebP, HEIC, HEIF, PDF, DOC, DOCX, XLS, XLSX. Maksimal 8 MB.
                        Untuk izin telat, jika mengupload foto maka sistem akan otomatis mengecilkan ukuran file.
                    </div>
                    @error('photo') <div class="error">{{ $message }}</div> @enderror

                    <div id="photoPreviewContainer" class="preview-container">
                        <p>Preview:</p>
                        <img id="photoPreview" src="#" alt="Preview foto">
                    </div>
                </div>

                <div class="field full">
                    <label><b>Alasan:</b></label>
                    <textarea name="reason" rows="4" placeholder="Tulis alasan">{{ old('reason') }}</textarea>
                    @error('reason') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="actions">
                <button class="btn primary" type="submit" style="text-decoration: none;">Kirim</button>
                <button type="button" class="btn" onclick="window.location='{{ route('leave-requests.index') }}'">Batal</button>
            </div>
        </form>

        <style>
            .alert-error {
                background: #ffecec;
                color: #a40000;
                padding: 10px 14px;
                border-radius: 8px;
                margin-bottom: 14px;
                font-size: 14px;
            }

            .form-leave {
                max-width: 520px;
                margin: auto;
            }

            .grid-form {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 14px;
            }

            .field label {
                display: block;
                margin-bottom: 6px;
                font-weight: 500;
                color: #333;
                font-size: 14px;
            }

            .card input[type="radio"] {
                margin-top: 3px;
                transform: scale(1.1);
                accent-color: #1b3e7f;
            }

            .card label {
                transition: background-color .2s;
            }

            .card label:hover {
                background: #f8f9fa;
                border-radius: 6px;
            }

            input[type=text],
            input[type=date],
            select,
            textarea,
            input[type=file],
            input[type=time] {
                width: 100%;
                padding: 10px 12px;
                border: 1px solid #ddd;
                border-radius: 8px;
                font-family: inherit;
                font-size: 14px;
            }

            textarea {
                resize: vertical;
            }

            select option {
                white-space: normal !important;
                line-height: 1.4;
            }

            .hint {
                font-size: 12px;
                color: #666;
                margin-top: 4px;
            }

            .error {
                font-size: 12px;
                color: #a40000;
                margin-top: 4px;
            }

            .full {
                grid-column: 1 / -1;
            }

            .actions {
                margin-top: 18px;
                display: flex;
                gap: 8px;
            }

            .preview-container {
                display: none;
                margin-top: 10px;
            }

            .preview-container p {
                font-size: 14px;
                color: #333;
                margin-bottom: 6px;
            }

            .preview-container img {
                max-width: 100%;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
            }

            @media (max-width: 600px) {
                .grid-form {
                    grid-template-columns: 1fr;
                }

                .form-leave {
                    padding: 12px;
                }
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
                        if (worktimeLabel) {
                            worktimeLabel.innerHTML = '<b>Jam Izin Tengah Kerja:</b>';
                        }
                        if (worktimeSeparator) {
                            worktimeSeparator.style.display = 'inline';
                        }
                        if (endTimeWrapper) {
                            endTimeWrapper.style.display = 'block';
                        }

                        startTimeInput.required = true;
                        endTimeInput.required = true;

                        if (pulangInfo) {
                            pulangInfo.style.display = 'none';
                            pulangInfo.textContent = '';
                        }
                    } else if (isPulangAwal) {
                        if (worktimeLabel) {
                            worktimeLabel.innerHTML = '<b>Jam Pulang:</b>';
                        }
                        if (worktimeSeparator) {
                            worktimeSeparator.style.display = 'none';
                        }
                        if (endTimeWrapper) {
                            endTimeWrapper.style.display = 'none';
                        }

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
                        if (worktimeLabel) {
                            worktimeLabel.innerHTML = '<b>Jam Izin Tengah Kerja:</b>';
                        }
                        if (worktimeSeparator) {
                            worktimeSeparator.style.display = 'inline';
                        }
                        if (endTimeWrapper) {
                            endTimeWrapper.style.display = 'block';
                        }

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
                            tenureWarnEl.textContent = 'Kurang dari 1 tahun kerja, pengajuan cuti akan dipotong gaji.';
                        } else {
                            tenureWarnEl.style.display = 'none';
                            tenureWarnEl.textContent = '';
                        }
                    }

                    function renderRuleVisibility() {
                        if (!ruleEl || !warnEl) return;

                        if (isCutiSelected()) {
                            ruleEl.style.display = 'block';
                            ruleEl.innerHTML = 'Ketentuan: pengajuan minimal H-7 dari hari ini (≥ <b>' + formatID(boundaryDateH7()) + '</b>).';
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
                                'Pengajuan dilakukan ' + diffDays + ' hari sebelum tanggal mulai cuti (kurang dari H-7). ' +
                                'Pengajuan tetap bisa diproses, namun akan ada potongan sesuai kebijakan perusahaan.';
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