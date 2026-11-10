<x-app title="Form Pengajuan Lembur">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Pengajuan Lembur</h1>
                <p class="section-subtitle">Isi data lembur Anda di bawah ini</p>
            </div>
        </div>
    </x-slot>

    <a href="{{ route('overtime-requests.index') }}" class="back-btn" aria-label="Kembali ke daftar lembur">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span class="back-btn-text">Kembali</span>
    </a>

    @if (session('error'))
        <div class="otc-alert otc-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form action="{{ route('overtime-requests.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="otc-step">
            <div class="otc-step__header">
                <span class="otc-step__num">1</span>
                <span class="otc-step__title">Tanggal & Waktu</span>
            </div>

            <div class="otc-field">
                <label for="overtime_date" class="otc-label">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Tanggal Lembur <span class="otc-required">*</span>
                </label>
                <div class="otc-input-wrap">
                    <svg class="otc-input__icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <input type="text" name="overtime_date" id="overtime_date"
                        class="otc-input otc-input--icon @error('overtime_date') otc-input--error @enderror"
                        value="{{ old('overtime_date') }}" placeholder="Pilih tanggal lembur" autocomplete="off" required>
                </div>
                @error('overtime_date')
                    <div class="otc-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="otc-field">
                <label class="otc-label otc-time-label">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Jam Lembur <span class="otc-required">*</span>
                </label>
                <div class="otc-time-grid">
                    @foreach (['start_time' => 'Jam Mulai Lembur', 'end_time' => 'Jam Selesai Lembur'] as $field => $label)
                        @php
                            $oldTime = old($field);
                            $oldTime = is_string($oldTime) && preg_match('/^(?:[01][0-9]|2[0-3]):(?:00|30)$/', $oldTime) ? $oldTime : '';
                            $pickerPrompt = 'Pilih ' . strtolower($label);
                        @endphp
                        <div>
                            <label class="otc-time-field-label" for="{{ $field }}_button">{{ $label }}</label>
                            <input type="hidden" name="{{ $field }}" id="{{ $field }}" value="{{ $oldTime }}">
                            <button type="button" id="{{ $field }}_button" class="otc-input otc-time-button @error($field) otc-input--error @enderror"
                                data-time-field="{{ $field }}" data-picker-title="{{ $pickerPrompt }}" aria-haspopup="dialog" @error($field) aria-invalid="true" aria-describedby="{{ $field }}_error" @enderror>
                                {{ $oldTime ?: $pickerPrompt }}
                            </button>
                            @error($field)
                                <div class="otc-error" id="{{ $field }}_error">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>

                <dialog id="otc-time-picker" class="otc-time-picker" aria-labelledby="otc-time-picker-title">
                    <h2 id="otc-time-picker-title">Pilih jam mulai lembur</h2>
                    <div class="otc-picker-columns">
                        <div class="otc-wheel-col">
                            <label class="otc-wheel-label" for="otc-picker-hour">Jam</label>
                            <select id="otc-picker-hour" size="5" aria-label="Jam" class="otc-sr-only">
                                @for ($hour = 0; $hour < 24; $hour++)
                                    <option value="{{ sprintf('%02d', $hour) }}">{{ sprintf('%02d', $hour) }}</option>
                                @endfor
                            </select>
                            <div class="otc-wheel-stage">
                                <div class="otc-wheel-highlight" aria-hidden="true"></div>
                                <div class="otc-wheel-scroller" id="otc-wheel-hour" data-wheel-for="otc-picker-hour" tabindex="0" role="listbox" aria-label="Pilih Jam"></div>
                            </div>
                        </div>
                        <div class="otc-wheel-col">
                            <label class="otc-wheel-label" for="otc-picker-minute">Menit</label>
                            <select id="otc-picker-minute" size="5" aria-label="Menit" class="otc-sr-only">
                                <option value="00">00</option>
                                <option value="30">30</option>
                            </select>
                            <div class="otc-wheel-stage">
                                <div class="otc-wheel-highlight" aria-hidden="true"></div>
                                <div class="otc-wheel-scroller" id="otc-wheel-minute" data-wheel-for="otc-picker-minute" tabindex="0" role="listbox" aria-label="Pilih Menit"></div>
                            </div>
                        </div>
                    </div>
                    <div class="otc-picker-actions">
                        <button type="button" id="otc-picker-save">Pilih</button>
                    </div>
                </dialog>

                <div class="otc-break-field">
                    @php
                        $breakOptions = [0 => 'Tanpa istirahat', 30 => '30 menit', 60 => '1 jam', 90 => '1 jam 30 menit', 120 => '2 jam', 150 => '2 jam 30 menit', 180 => '3 jam', 210 => '3 jam 30 menit', 240 => '4 jam', 270 => '4 jam 30 menit', 300 => '5 jam'];
                        $oldBreak = (string) old('break_duration_minutes', '0');
                        $oldBreakLabel = $breakOptions[(int) $oldBreak] ?? 'Tanpa istirahat';
                    @endphp
                    <label class="otc-time-field-label" for="break_duration_minutes_button">Durasi Istirahat</label>
                    <select name="break_duration_minutes" id="break_duration_minutes" class="otc-sr-only" @error('break_duration_minutes') aria-invalid="true" aria-describedby="break_duration_minutes_error" @enderror>
                        @foreach ([0 => 'Tanpa istirahat', 30 => '30 menit', 60 => '1 jam', 90 => '1 jam 30 menit', 120 => '2 jam', 150 => '2 jam 30 menit', 180 => '3 jam', 210 => '3 jam 30 menit', 240 => '4 jam', 270 => '4 jam 30 menit', 300 => '5 jam'] as $minutes => $label)
                            <option value="{{ $minutes }}" @selected((string) old('break_duration_minutes', '0') === (string) $minutes)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="button" id="break_duration_minutes_button" class="otc-input otc-time-button @error('break_duration_minutes') otc-input--error @enderror"
                        aria-haspopup="dialog" @error('break_duration_minutes') aria-invalid="true" aria-describedby="break_duration_minutes_error" @enderror>
                        {{ $oldBreakLabel }}
                    </button>
                    @error('break_duration_minutes')
                        <div class="otc-error" id="break_duration_minutes_error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="otc-total">
                    <div class="otc-total-label">Durasi Lembur</div>
                    <div id="otc-total-value" class="otc-total-value" aria-live="polite">0 jam</div>
                </div>

                <dialog id="otc-break-picker" class="otc-time-picker" aria-labelledby="otc-break-picker-title">
                    <h2 id="otc-break-picker-title">Pilih durasi istirahat</h2>
                    <div class="otc-wheel-col">
                        <div class="otc-wheel-stage">
                            <div class="otc-wheel-highlight" aria-hidden="true"></div>
                            <div class="otc-wheel-scroller" id="otc-wheel-break" tabindex="0" role="listbox" aria-label="Pilih Durasi Istirahat"></div>
                        </div>
                    </div>
                    <div class="otc-picker-actions">
                        <button type="button" id="otc-break-picker-save">Pilih</button>
                    </div>
                </dialog>

            </div>
        </div>

        <div class="otc-step otc-step--evidence">
            <div class="otc-step__header">
                <span class="otc-step__num">2</span>
                <span class="otc-step__title">Bukti & Keterangan</span>
            </div>

            <div class="otc-field">
                <label for="photoInput" class="otc-label">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Bukti Pendukung <span>(opsional, disarankan)</span>
                </label>
                <div class="lrc-upload" id="uploadBox">
                    <input type="file" name="photos[]" id="photoInput" class="lrc-upload__input" accept=".jpg,.jpeg,.png,.webp,.heic,.heif,.pdf,.doc,.docx,.xls,.xlsx" data-max-file-size="8388608" data-max-file-label="8 MB" multiple>
                    <div class="lrc-upload__content">
                        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span class="lrc-upload__title">Klik untuk upload file</span>
                        <span class="lrc-upload__desc">JPG, PNG, PDF, DOCX (Maks 3 file · 8 MB per file)</span>
                    </div>
                </div>
                <div id="photoError" role="alert" style="display:none; color:#dc2626; font-size:13px; margin-top:6px;"></div>
                @if ($errors->has('photos') || $errors->has('photos.*') || $errors->has('photo'))
                    <div class="otc-error" role="alert">{{ $errors->first('photos') ?: ($errors->first('photos.*') ?: $errors->first('photo')) }}</div>
                @endif
                <div id="photoListContainer" style="display:none; flex-direction:column; gap:8px; margin-top:8px;"></div>
            </div>

            <div class="otc-field">
                <label for="description" class="otc-label">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Keterangan Pekerjaan <span class="otc-required">*</span>
                </label>
                <textarea name="description" id="description" rows="4"
                    class="otc-textarea @error('description') otc-input--error @enderror"
                    placeholder="Jelaskan pekerjaan yang dilakukan..." required>{{ old('description') }}</textarea>
                @error('description')
                    <div class="otc-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="otc-submit-wrap">
            <button type="submit" class="otc-btn-submit" id="btn-submit">
                <span id="btn-text">Ajukan Lembur</span>
                <span id="btn-spinner" class="otc-spinner" role="status" aria-hidden="true" style="display:none;"></span>
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const btnSubmit = document.getElementById('btn-submit');
            const btnText = document.getElementById('btn-text');
            const btnSpinner = document.getElementById('btn-spinner');

            const picker = document.getElementById('otc-time-picker');
            const hour = document.getElementById('otc-picker-hour');
            const minute = document.getElementById('otc-picker-minute');
            let activeButton;

            const ITEM_HEIGHT = 44;
            const hourValues = Array.from({ length: 24 }, function(_, i) { return String(i).padStart(2, '0'); });
            const minuteValues = ['00', '30'];

            function createInfiniteWheel(scrollerId, selectEl, values, cycles, labels) {
                const scroller = document.getElementById(scrollerId);
                if (!scroller || !scroller.appendChild) return null;

                const len = values.length;
                const midCycle = Math.floor(cycles / 2);
                const total = len * cycles;
                const items = [];
                scroller.innerHTML = '';

                for (let i = 0; i < total; i++) {
                    const val = values[i % len];
                    const label = labels ? labels[i % len] : val;
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'otc-wheel-item';
                    item.dataset.wheelIndex = String(i);
                    item.dataset.wheelValue = val;
                    item.setAttribute('role', 'option');
                    item.textContent = label;
                    item.addEventListener('click', function() {
                        setFromValue(val);
                    });
                    scroller.appendChild(item);
                    items.push(item);
                }

                let settleTimer = null;
                let isAdjusting = false;

                function updateVisuals(activeIndex) {
                    for (let i = 0; i < items.length; i++) {
                        const dist = Math.abs(i - activeIndex);
                        const el = items[i];
                        el.classList.toggle('is-selected', dist === 0);
                        el.classList.toggle('is-near', dist === 1);
                        el.setAttribute('aria-selected', dist === 0 ? 'true' : 'false');
                    }
                }

                function setFromValue(val) {
                    clearTimeout(settleTimer);
                    let valIdx = values.indexOf(val);
                    if (valIdx === -1) valIdx = 0;
                    const targetIndex = midCycle * len + valIdx;
                    selectEl.value = values[valIdx];
                    updateVisuals(targetIndex);
                    isAdjusting = true;
                    scroller.scrollTo({ top: targetIndex * ITEM_HEIGHT, behavior: 'instant' });
                    isAdjusting = false;
                }

                scroller.addEventListener('scroll', function() {
                    if (isAdjusting) return;
                    let idx = Math.round(scroller.scrollTop / ITEM_HEIGHT);
                    if (idx < 0) idx = 0;
                    if (idx >= total) idx = total - 1;

                    const valIdx = ((idx % len) + len) % len;
                    selectEl.value = values[valIdx];

                    if (cycles > 1 && (idx < len * 2 || idx >= len * (cycles - 2))) {
                        const recenteredIdx = midCycle * len + valIdx;
                        isAdjusting = true;
                        scroller.scrollTop += (recenteredIdx - idx) * ITEM_HEIGHT;
                        idx = recenteredIdx;
                        isAdjusting = false;
                    }

                    updateVisuals(idx);

                    clearTimeout(settleTimer);
                    settleTimer = setTimeout(function() {
                        const settledValIdx = values.indexOf(selectEl.value);
                        const finalIdx = midCycle * len + (settledValIdx >= 0 ? settledValIdx : 0);
                        if (Math.abs(scroller.scrollTop - finalIdx * ITEM_HEIGHT) > 1) {
                            isAdjusting = true;
                            scroller.scrollTop = finalIdx * ITEM_HEIGHT;
                            updateVisuals(finalIdx);
                            isAdjusting = false;
                        }
                    }, 120);
                }, { passive: true });

                return { setFromValue: setFromValue };
            }

            const hourWheel = createInfiniteWheel('otc-wheel-hour', hour, hourValues, 7);
            const minuteWheel = createInfiniteWheel('otc-wheel-minute', minute, minuteValues, 1);
            const pickerTitle = document.getElementById('otc-time-picker-title');

            document.querySelectorAll('[data-time-field]').forEach(function(button) {
                button.addEventListener('click', function() {
                    activeButton = button;
                    const value = document.getElementById(button.dataset.timeField).value;
                    hour.value = value ? value.slice(0, 2) : String(new Date().getHours()).padStart(2, '0');
                    minute.value = value ? value.slice(3) : '00';
                    if (pickerTitle) {
                        pickerTitle.textContent = button.dataset.pickerTitle || (button.dataset.timeField === 'end_time' ? 'Pilih jam selesai lembur' : 'Pilih jam mulai lembur');
                    }
                    picker.showModal();
                    if (hourWheel && minuteWheel) {
                        hourWheel.setFromValue(hour.value);
                        minuteWheel.setFromValue(minute.value);
                    } else {
                        hour.focus();
                    }
                });
            });
            document.getElementById('otc-picker-cancel')?.addEventListener('click', function() {
                picker.close();
            });
            picker.addEventListener?.('click', function(e) {
                if (e.target === picker) {
                    picker.close();
                }
            });
            document.getElementById('otc-picker-save').addEventListener('click', function() {
                const value = hour.value + ':' + minute.value;
                document.getElementById(activeButton.dataset.timeField).value = value;
                activeButton.textContent = value;
                updateOvertimeTotal();
                picker.close();
            });

            const breakPicker = document.getElementById('otc-break-picker');
            const breakSelect = document.getElementById('break_duration_minutes');
            const breakButton = document.getElementById('break_duration_minutes_button');
            const breakSave = document.getElementById('otc-break-picker-save');
            const breakValues = ['0', '30', '60', '90', '120', '150', '180', '210', '240', '270', '300'];
            const breakLabels = ['Tanpa istirahat', '30 menit', '1 jam', '1 jam 30 menit', '2 jam', '2 jam 30 menit', '3 jam', '3 jam 30 menit', '4 jam', '4 jam 30 menit', '5 jam'];
            const pendingBreak = { value: breakSelect ? (breakSelect.value || '0') : '0' };
            const breakWheel = createInfiniteWheel('otc-wheel-break', pendingBreak, breakValues, 1, breakLabels);

            function updateOvertimeTotal() {
                const total = document.getElementById('otc-total-value');
                const start = document.getElementById('start_time').value;
                const end = document.getElementById('end_time').value;
                if (!start || !end) {
                    total.textContent = '0 jam';
                    total.classList?.toggle?.('otc-total-value--error', false);
                    return;
                }
                const timePattern = /^(?:[01]\d|2[0-3]):(?:00|30)$/;
                if (!timePattern.test(start) || !timePattern.test(end)) {
                    total.textContent = 'Jam lembur tidak valid.';
                    total.classList?.toggle?.('otc-total-value--error', true);
                    return;
                }
                if (start === end) {
                    total.textContent = 'Jam selesai lembur tidak boleh sama dengan jam mulai.';
                    total.classList?.toggle?.('otc-total-value--error', true);
                    return;
                }
                const startMinutes = Number(start.slice(0, 2)) * 60 + Number(start.slice(3));
                const endMinutes = Number(end.slice(0, 2)) * 60 + Number(end.slice(3));
                const duration = endMinutes - startMinutes + (endMinutes < startMinutes ? 1440 : 0);
                const breakValue = breakSelect ? breakSelect.value : '0';
                if (breakValue !== '' && !breakValues.includes(breakValue)) {
                    total.textContent = 'Durasi istirahat tidak valid.';
                    total.classList?.toggle?.('otc-total-value--error', true);
                    return;
                }
                const breakMinutes = Number(breakValue || '0');
                if (breakMinutes >= duration) {
                    total.textContent = 'Istirahat harus lebih singkat dari durasi lembur.';
                    total.classList?.toggle?.('otc-total-value--error', true);
                    return;
                }
                const workingMinutes = duration - breakMinutes;
                const hours = Math.floor(workingMinutes / 60);
                const minutes = workingMinutes % 60;
                total.classList?.toggle?.('otc-total-value--error', false);
                total.textContent = [hours ? hours + ' jam' : '', minutes ? minutes + ' menit' : ''].filter(Boolean).join(' ');
            }

            updateOvertimeTotal();

            breakButton?.addEventListener('click', function() {
                pendingBreak.value = breakSelect && breakSelect.value ? String(breakSelect.value) : '0';
                breakPicker?.showModal();
                if (breakWheel) {
                    breakWheel.setFromValue(pendingBreak.value);
                }
            });
            breakPicker?.addEventListener?.('click', function(e) {
                if (e.target === breakPicker) {
                    breakPicker.close();
                }
            });
            breakSave?.addEventListener('click', function() {
                if (breakSelect) {
                    breakSelect.value = pendingBreak.value;
                }
                if (breakButton) {
                    const idx = breakValues.indexOf(pendingBreak.value);
                    breakButton.textContent = idx >= 0 ? breakLabels[idx] : 'Tanpa istirahat';
                }
                updateOvertimeTotal();
                breakPicker?.close();
            });

            const dateInput = document.getElementById('overtime_date');
            if (typeof flatpickr === 'function' && dateInput) {
                flatpickr(dateInput, {
                    mode: 'single',
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    disableMobile: true,
                });
            }

            if (form) {
                form.addEventListener('submit', function(e) {
                    const missing = Array.from(document.querySelectorAll('[data-time-field]')).find(function(button) {
                        return !document.getElementById(button.dataset.timeField).value;
                    });
                    if (missing) {
                        e.preventDefault();
                        missing.focus();
                        alert('Silakan pilih jam mulai dan jam selesai lembur.');
                        return;
                    }
                    if (btnSubmit.disabled) {
                        e.preventDefault();
                        return;
                    }
                    btnSubmit.disabled = true;
                    btnText.innerText = 'Memproses...';
                    if (btnSpinner) btnSpinner.style.display = 'inline-block';
                });
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('photoInput');
            const listContainer = document.getElementById('photoListContainer');
            const errorEl = document.getElementById('photoError');
            if (!input || !listContainer) return;

            const MAX_FILES = 3;
            const MAX_SIZE = parseInt(input.getAttribute('data-max-file-size') || '8388608', 10);
            const dt = new DataTransfer();

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
            }

            function showError(msg) {
                if (!errorEl) return;
                errorEl.textContent = msg || '';
                errorEl.style.display = msg ? 'block' : 'none';
            }

            function renderList() {
                listContainer.innerHTML = '';
                const files = Array.from(dt.files);
                if (!files.length) {
                    listContainer.style.display = 'none';
                    return;
                }
                listContainer.style.display = 'flex';

                files.forEach(function(file, idx) {
                    const item = document.createElement('div');
                    item.className = 'lrc-preview';
                    item.style.cssText = 'display:flex; align-items:center; gap:10px; position:relative;';

                    if (file.type && file.type.startsWith('image/')) {
                        const img = document.createElement('img');
                        img.alt = 'Preview';
                        img.style.cssText = 'width:56px; height:56px; object-fit:cover; border-radius:8px; flex-shrink:0;';
                        img.src = URL.createObjectURL(file);
                        img.onload = function() { URL.revokeObjectURL(img.src); };
                        item.appendChild(img);
                    }

                    const info = document.createElement('div');
                    info.className = 'lrc-file-info';
                    const nameSpan = document.createElement('span');
                    nameSpan.className = 'lrc-file-info__name';
                    nameSpan.textContent = file.name;
                    const sizeSpan = document.createElement('span');
                    sizeSpan.className = 'lrc-file-info__size';
                    sizeSpan.textContent = 'Ukuran: ' + formatFileSize(file.size);
                    info.appendChild(nameSpan);
                    info.appendChild(sizeSpan);
                    item.appendChild(info);

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'lrc-preview__remove';
                    removeBtn.setAttribute('aria-label', 'Hapus file');
                    removeBtn.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                    removeBtn.addEventListener('click', function() {
                        const kept = Array.from(dt.files).filter(function(_, i) { return i !== idx; });
                        dt.items.clear();
                        kept.forEach(function(f) { dt.items.add(f); });
                        input.files = dt.files;
                        showError('');
                        renderList();
                    });
                    item.appendChild(removeBtn);

                    listContainer.appendChild(item);
                });
            }

            input.addEventListener('change', function() {
                showError('');
                const incoming = Array.from(this.files);
                for (const file of incoming) {
                    if (dt.files.length >= MAX_FILES) {
                        showError('Maksimal ' + MAX_FILES + ' file bukti pendukung.');
                        break;
                    }
                    if (file.size > MAX_SIZE) {
                        showError('File "' + file.name + '" melebihi 8 MB.');
                        continue;
                    }
                    dt.items.add(file);
                }
                this.files = dt.files;
                renderList();
            });
        });
    </script>

    <style>
        .otc-sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
        .otc-time-button { text-align: left; cursor: pointer; }
        .otc-total {
            margin-top: 14px;
            padding: 14px 16px;
            border: 1px solid rgba(20, 93, 160, 0.18);
            border-radius: 12px;
            background: rgba(20, 93, 160, 0.05);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
        .otc-total-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .otc-total-value {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary-dark);
            line-height: 1.3;
            font-variant-numeric: tabular-nums;
        }
        .otc-total-value--error {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--error);
        }
        .otc-time-picker { width: min(340px, calc(100% - 32px)); border: 1px solid var(--border); border-radius: 16px; padding: 20px; color: var(--text-primary); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.18); }
        .otc-time-picker::backdrop { background: rgba(0, 0, 0, 0.45); }
        .otc-time-picker h2 { margin: 0 0 16px; font-size: 1.0625rem; font-weight: 700; }
        .otc-picker-columns { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .otc-wheel-col { display: flex; flex-direction: column; min-width: 0; }
        .otc-wheel-label { font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px; text-align: center; }
        .otc-wheel-stage {
            position: relative;
            height: 220px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--gray-50, #F8FAFC);
            overflow: hidden;
            user-select: none;
            -webkit-user-select: none;
        }
        .otc-wheel-stage::before,
        .otc-wheel-stage::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            height: 76px;
            pointer-events: none;
            z-index: 2;
        }
        .otc-wheel-stage::before {
            top: 0;
            background: linear-gradient(to bottom, rgba(248, 250, 252, 0.95) 0%, rgba(248, 250, 252, 0) 100%);
        }
        .otc-wheel-stage::after {
            bottom: 0;
            background: linear-gradient(to top, rgba(248, 250, 252, 0.95) 0%, rgba(248, 250, 252, 0) 100%);
        }
        .otc-wheel-highlight {
            position: absolute;
            top: 88px;
            left: 6px;
            right: 6px;
            height: 44px;
            border-radius: 10px;
            background: rgba(20, 93, 160, 0.12);
            border-top: 1.5px solid rgba(20, 93, 160, 0.28);
            border-bottom: 1.5px solid rgba(20, 93, 160, 0.28);
            pointer-events: none;
            z-index: 1;
        }
        .otc-wheel-scroller {
            position: relative;
            z-index: 1;
            height: 220px;
            padding: 88px 0;
            overflow-y: auto;
            scroll-snap-type: y mandatory;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            outline: none;
        }
        .otc-wheel-scroller::-webkit-scrollbar {
            display: none;
        }
        .otc-wheel-item {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 44px;
            padding: 0;
            border: none;
            background: transparent;
            font-family: inherit;
            font-variant-numeric: tabular-nums;
            font-size: 0.9375rem;
            font-weight: 500;
            color: var(--text-muted);
            opacity: 0.35;
            scroll-snap-align: center;
            cursor: pointer;
            transition: transform 0.12s ease, opacity 0.12s ease, color 0.12s ease;
            -webkit-tap-highlight-color: transparent;
        }
        .otc-wheel-item.is-near {
            font-size: 1.0625rem;
            font-weight: 600;
            color: var(--text-secondary);
            opacity: 0.68;
        }
        .otc-wheel-item.is-selected {
            font-size: 1.3125rem;
            font-weight: 800;
            color: var(--primary-dark);
            opacity: 1;
            transform: scale(1.05);
        }
        #otc-wheel-break .otc-wheel-item.is-selected {
            font-size: 1.125rem;
        }
        .otc-picker-actions { display: flex; margin-top: 18px; }
        .otc-picker-actions button { width: 100%; min-height: 46px; padding: 12px 20px; border: 1px solid var(--primary); border-radius: 10px; font-size: 0.9375rem; font-weight: 700; font-family: inherit; cursor: pointer; background: var(--primary); color: white; }
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
        .lrc-file-info {
            margin-top: 10px;
            padding: 8px 10px;
            background: #F8FAFC;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            font-size: 0.75rem;
            color: var(--text-secondary);
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .lrc-file-info__name {
            font-weight: 600;
            color: var(--text-primary);
            word-break: break-all;
        }
        .lrc-file-info__size {
            color: var(--text-muted);
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
            color: var(--text-primary);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy {
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark);
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 12px 0 10px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.15s ease;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            margin-bottom: 16px;
        }
        .back-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--gray-50);
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

        .otc-alert {
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
        .otc-alert svg {
            flex-shrink: 0;
            margin-top: 1px;
        }
        .otc-alert--error {
            background: rgba(239, 68, 68, 0.06);
            border: 1px solid rgba(239, 68, 68, 0.18);
            color: #b91c1c;
        }

        .otc-step {
            background: var(--white);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 12px;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .otc-step__header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }
        .otc-step__num {
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
        .otc-step__title {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .otc-field {
            margin-bottom: 16px;
        }
        .otc-field:last-child {
            margin-bottom: 0;
        }
        .otc-step--evidence .otc-field {
            margin-bottom: 14px;
        }
        .otc-step--evidence .otc-field:last-child {
            margin-bottom: 0;
        }
        .otc-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        .otc-required {
            color: var(--error);
            font-weight: 700;
        }
        .otc-input-wrap {
            position: relative;
        }
        .otc-input__icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            pointer-events: none;
        }
        .otc-input {
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
        .otc-input--icon {
            padding-left: 42px;
        }
        .otc-input::placeholder {
            color: var(--text-light);
        }
        .otc-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .otc-input--error {
            border-color: var(--error);
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.08);
        }
        .otc-input--error:focus {
            border-color: var(--error);
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12);
        }
        .otc-error {
            font-size: 0.75rem;
            color: var(--error);
            margin-top: 6px;
            font-weight: 500;
            line-height: 1.4;
        }
        .otc-textarea {
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
        .otc-textarea::placeholder {
            color: var(--text-light);
        }
        .otc-textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }

        .otc-time-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .otc-time-field-label {
            display: block;
            width: auto;
            height: auto;
            padding: 0;
            margin: 0 0 6px;
            color: var(--text-primary, #1F2937);
            font-size: 0.8125rem;
            font-weight: 600;
        }

        .otc-break-field { margin-top: 14px; }
        .otc-submit-wrap {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid var(--border-light);
        }
        .otc-btn-submit {
            flex: 2;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
            font-family: inherit;
        }
        .otc-btn-submit:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .otc-btn-submit:disabled {
            background: linear-gradient(135deg, #93c5fd, #60a5fa);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .otc-spinner {
            width: 1rem;
            height: 1rem;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: otc-spinner-spin .75s linear infinite;
            display: inline-block;
            vertical-align: middle;
        }
        @keyframes otc-spinner-spin {
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 639px) {
            .otc-time-label {
                display: none;
            }
            .otc-step--evidence {
                padding: 16px;
                margin-bottom: 10px;
            }
        }

        @media (max-width: 480px) {
            .otc-time-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            .otc-submit-wrap {
                flex-direction: column;
            }
            .otc-btn-submit {
                width: 100%;
                flex: none;
            }
            .otc-step {
                padding: 16px;
            }
        }
    </style>

    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.css') }}">
    <script src="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.js') }}"></script>
</x-app>
