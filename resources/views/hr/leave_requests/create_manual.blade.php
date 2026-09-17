<x-app title="Tambah Data Izin / Cuti">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Form Entri Manual</h1>
                <p class="section-subtitle">Tambahkan arsip pengajuan karyawan ke dalam sistem</p>
            </div>
        </div>
    </x-slot>

    <div class="mlc-container">

        @php
            $oldStart = old('start_date');
            $oldEnd = old('end_date');
            $oldRange = '';
            if ($oldStart && $oldEnd) {
                $oldRange = $oldStart . ' sampai ' . $oldEnd;
            } elseif ($oldStart) {
                $oldRange = $oldStart;
            }
        @endphp

        <a href="{{ route('hr.leave.master') }}" class="back-btn" aria-label="Kembali ke Master Izin dan Cuti">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        {{-- Flash / Error Messages --}}
        @if (session('error'))
            <div class="mlc-alert mlc-alert--error">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mlc-alert mlc-alert--error">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @if (session('success'))
            <div class="mlc-alert mlc-alert--success">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Info Banner --}}
        <div class="mlc-banner" role="note">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Kolom bertanda <strong>*</strong> wajib diisi. Pastikan data sesuai dengan dokumen sumber sebelum disimpan.</span>
        </div>

        {{-- Form --}}
        <div class="manual-form-shell">
            <form id="hr-manual-leave-form" method="POST" action="{{ route('hr.leave.manual.store') }}" enctype="multipart/form-data">

                @csrf

                {{-- Langkah 1: Data Karyawan --}}
                <div class="mlc-step">
                    <div class="mlc-step__header">
                        <span class="mlc-step__num">1</span>
                        <div class="mlc-step__headtext">
                            <span class="mlc-step__title">Data Karyawan</span>
                            <span class="mlc-step__sub">Langkah 1 dari 4 · Tentukan pemilik pengajuan</span>
                        </div>
                    </div>

                    <div class="mlc-field">
                        <label for="manual_user_search" class="mlc-label">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Pilih Karyawan <span class="mlc-required">*</span>
                        </label>
                        <div class="employee-picker">
                            <div class="mlc-input-wrap">
                                <svg class="mlc-input__icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input
                                    type="text"
                                    id="manual_user_search"
                                    class="mlc-input mlc-input--icon"
                                    placeholder="Ketik nama karyawan..."
                                    autocomplete="off"
                                    value="{{ old('user_id') ? optional($employees->firstWhere('id', (int) old('user_id')))->name : '' }}">
                            </div>
                            <div id="manual_user_suggestions" class="employee-suggestions" style="display:none;"></div>
                        </div>
                        <select id="manual_user_id" name="user_id" class="employee-select-hidden" tabindex="-1" aria-hidden="true">
                            <option value="">Pilih karyawan</option>
                            @foreach($employees as $employee)
                                @php
                                    $roleValue = $employee->role instanceof \App\Enums\UserRole ? $employee->role->value : $employee->role;
                                    $employeeLabel = trim($employee->name
                                        . ($employee->position ? ' - ' . $employee->position->name : '')
                                        . ($employee->division ? ' (' . $employee->division->name . ')' : ''));
                                @endphp
                                <option
                                    value="{{ $employee->id }}"
                                    data-role="{{ strtoupper((string) $roleValue) }}"
                                    data-balance="{{ $employee->leave_balance ?? 0 }}"
                                    data-label="{{ $employeeLabel }}"
                                    @selected(old('user_id') == $employee->id)
                                >{{ $employeeLabel }}</option>
                            @endforeach
                        </select>
                        <p class="mlc-helper">Ketik nama untuk mencari, lalu pilih karyawan dari daftar saran.</p>
                    </div>
                </div>

                {{-- Langkah 2: Detail Pengajuan --}}
                <div class="mlc-step">
                    <div class="mlc-step__header">
                        <span class="mlc-step__num">2</span>
                        <div class="mlc-step__headtext">
                            <span class="mlc-step__title">Detail Pengajuan</span>
                            <span class="mlc-step__sub">Langkah 2 dari 4 · Lengkapi jenis, periode, dan status arsip</span>
                        </div>
                    </div>

                    <div class="mlc-grid-2">
                        <div class="mlc-field">
                            <label for="manual_submitted_at" class="mlc-label">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Tanggal Pengajuan
                            </label>
                            <input type="date" id="manual_submitted_at" name="submitted_at" class="mlc-input" value="{{ old('submitted_at') }}">
                            <p class="mlc-helper">Kosongkan untuk memakai tanggal hari ini.</p>
                        </div>
                        <div class="mlc-field">
                            <label for="manual_status" class="mlc-label">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Status
                            </label>
                            <div class="mlc-select-wrap">
                                <select id="manual_status" name="status" class="mlc-select">
                                    <option value="">Pilih status</option>
                                    @foreach($statusOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <svg class="mlc-select__arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                            <p class="mlc-helper">Kosongkan untuk mengikuti status bawaan alur persetujuan.</p>
                        </div>
                    </div>

                    <div class="mlc-field">
                        <span class="mlc-label">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            Jenis Pengajuan <span class="mlc-required">*</span>
                        </span>
                        <div class="mlc-radio-grid">
                            @foreach($typeOptions as $case)
                                <label class="mlc-radio-card">
                                    <input type="radio" name="type" value="{{ $case->value }}" @checked(old('type') === $case->value)>
                                    <span class="mlc-radio-card__label">{{ $case->label() }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div id="manual-balance-info" class="mlc-info" style="display:none;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <strong id="manual-balance-text">Saldo cuti karyawan: 0 hari.</strong>
                                <p>Informasi ini hanya sebagai referensi saat input manual.</p>
                            </div>
                        </div>

                        <div id="manual-special-leave-container" class="mlc-special-box" style="display:none;">
                            <label for="manual_special_leave_detail" class="mlc-label mlc-label--accent">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Pilih Kategori Cuti Khusus
                            </label>
                            <div class="mlc-select-wrap">
                                <select name="special_leave_detail" id="manual_special_leave_detail" class="mlc-select">
                                    <option value="">-- Pilih Alasan --</option>
                                    @foreach($specialLeaveList as $sl)
                                        <option value="{{ $sl['id'] }}" data-days="{{ $sl['days'] }}" @selected(old('special_leave_detail') == $sl['id'])>{{ $sl['label'] }}</option>
                                    @endforeach
                                </select>
                                <svg class="mlc-select__arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                            <div id="manual-special-leave-badge" class="mlc-badge" style="display:none;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span id="manual-special-leave-text">Maksimal 2 Hari</span>
                            </div>
                        </div>
                    </div>

                    <div class="mlc-field">
                        <label for="manual_date_range" class="mlc-label">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Periode Izin <span class="mlc-required">*</span>
                        </label>
                        <div class="mlc-input-wrap">
                            <svg class="mlc-input__icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <input type="text" id="manual_date_range" name="date_range" class="mlc-input mlc-input--icon" value="{{ $oldRange }}" placeholder="Pilih tanggal mulai - selesai" autocomplete="off">
                        </div>
                        <input type="hidden" name="start_date" id="manual_start_date" value="{{ $oldStart }}">
                        <input type="hidden" name="end_date" id="manual_end_date" value="{{ $oldEnd }}">
                        <div id="manual-duration-display" class="mlc-duration" style="display:none;"></div>
                        <div id="manual-special-limit-warning" class="mlc-warning" style="display:none;"></div>
                    </div>

                    <div class="mlc-field" id="manual-worktime-field" style="display:none; margin-bottom:0;">
                        <span id="manual-worktime-label" class="mlc-label">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Jam Izin
                        </span>
                        <div class="mlc-time-range">
                            <div class="mlc-time-box">
                                <div class="mlc-input-wrap">
                                    <svg class="mlc-input__icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <input type="time" name="start_time" id="manual_start_time_input" class="mlc-input mlc-input--icon" value="{{ old('start_time') }}">
                                </div>
                            </div>
                            <span id="manual-worktime-separator" class="mlc-time-sep">s/d</span>
                            <div id="manual_end_time_wrapper" class="mlc-time-box">
                                <div class="mlc-input-wrap">
                                    <svg class="mlc-input__icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <input type="time" name="end_time" id="manual_end_time_input" class="mlc-input mlc-input--icon" value="{{ old('end_time') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Langkah 3: Pendelegasian (kondisional) --}}
                <div id="manual-substitute-pic-section" class="mlc-step" style="display:none;">
                    <div class="mlc-step__header">
                        <span class="mlc-step__num">3</span>
                        <div class="mlc-step__headtext">
                            <span class="mlc-step__title">Informasi Pendelegasian</span>
                            <span class="mlc-step__sub">Langkah 3 dari 4 · Catat PIC pengganti bila diperlukan</span>
                        </div>
                    </div>
                    <p class="mlc-helper" style="margin:-6px 0 14px;">Opsional, untuk kebutuhan dokumentasi sinkronisasi data lama.</p>
                    <div class="mlc-grid-2">
                        <div class="mlc-field">
                            <label for="manual_substitute_pic" class="mlc-label">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Nama PIC Pengganti
                            </label>
                            <input type="text" name="substitute_pic" id="manual_substitute_pic" class="mlc-input" placeholder="Nama rekan pengganti" value="{{ old('substitute_pic') }}">
                        </div>
                        <div class="mlc-field">
                            <label for="manual_substitute_phone" class="mlc-label">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                Nomor HP PIC
                            </label>
                            <input type="tel" name="substitute_phone" id="manual_substitute_phone" class="mlc-input" placeholder="Contoh: 0812..." value="{{ old('substitute_phone') }}">
                        </div>
                    </div>
                </div>

                {{-- Langkah 4: Dokumen & Catatan --}}
                <div class="mlc-step">
                    <div class="mlc-step__header">
                        <span class="mlc-step__num">4</span>
                        <div class="mlc-step__headtext">
                            <span class="mlc-step__title">Dokumen &amp; Catatan</span>
                            <span class="mlc-step__sub">Langkah 4 dari 4 · Lampirkan bukti dan keterangan pendukung</span>
                        </div>
                    </div>

                    <div class="mlc-field">
                        <label for="manual_photo_input" class="mlc-label">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Bukti Pendukung
                        </label>
                        <div class="mlc-upload">
                            <input type="file" name="photo" id="manual_photo_input" class="mlc-upload__input" accept=".jpg,.jpeg,.png,.webp,.heic,.heif,.pdf,.doc,.docx,.xls,.xlsx" data-max-file-size="8388608" data-max-file-label="8 MB">
                            <div class="mlc-upload__content">
                                <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <span class="mlc-upload__title">Klik untuk upload file</span>
                                <span class="mlc-upload__desc">Gambar atau dokumen pendukung jika tersedia (Maks 8 MB)</span>
                            </div>
                        </div>
                        <div id="manual-photo-preview-container" class="mlc-preview">
                            <p class="mlc-preview__label">Preview Foto:</p>
                            <img id="manual-photo-preview" src="#" alt="Preview foto">
                        </div>
                    </div>

                    <div class="mlc-field">
                        <label for="manual_reason" class="mlc-label">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Alasan / Keterangan
                        </label>
                        <textarea name="reason" id="manual_reason" rows="3" class="mlc-textarea" placeholder="Isi keterangan pengajuan manual atau catatan sinkronisasi...">{{ old('reason') }}</textarea>
                    </div>

                    <div class="mlc-field">
                        <label for="manual_notes_hrd" class="mlc-label">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Catatan HRD
                        </label>
                        <textarea name="notes_hrd" id="manual_notes_hrd" rows="2" class="mlc-textarea" placeholder="Catatan internal HRD untuk data manual ini...">{{ old('notes_hrd') }}</textarea>
                    </div>
                </div>

                <div class="mlc-actions manual-form-actions">
                    <a href="{{ route('hr.leave.master') }}" class="mlc-btn mlc-btn--secondary">
                        Batal
                    </a>
                    <button type="submit" class="mlc-btn mlc-btn--primary" id="manual-submit-btn">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        Simpan Data Manual
                    </button>
                </div>

            </form>
        </div>

    </div>

    @push('scripts')
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.css') }}">
    <script src="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const employeeSelect = document.getElementById('manual_user_id');
            const employeeSearchInput = document.getElementById('manual_user_search');
            const employeeSuggestions = document.getElementById('manual_user_suggestions');
            const typeRadios = document.querySelectorAll('input[name="type"]');
            const specialLeaveContainer = document.getElementById('manual-special-leave-container');
            const specialLeaveSelect = document.getElementById('manual_special_leave_detail');
            const specialLeaveBadge = document.getElementById('manual-special-leave-badge');
            const specialLeaveText = document.getElementById('manual-special-leave-text');
            const specialLimitWarning = document.getElementById('manual-special-limit-warning');
            const balanceInfo = document.getElementById('manual-balance-info');
            const balanceText = document.getElementById('manual-balance-text');
            const startDateInput = document.getElementById('manual_start_date');
            const endDateInput = document.getElementById('manual_end_date');
            const durationDisplay = document.getElementById('manual-duration-display');
            const worktimeField = document.getElementById('manual-worktime-field');
            const worktimeLabel = document.getElementById('manual-worktime-label');
            const worktimeSeparator = document.getElementById('manual-worktime-separator');
            const endTimeWrapper = document.getElementById('manual_end_time_wrapper');
            const endTimeInput = document.getElementById('manual_end_time_input');
            const picSection = document.getElementById('manual-substitute-pic-section');
            const photoInput = document.getElementById('manual_photo_input');
            const previewContainer = document.getElementById('manual-photo-preview-container');
            const previewImg = document.getElementById('manual-photo-preview');
            const rangeInput = document.getElementById('manual_date_range');
            const form = document.getElementById('hr-manual-leave-form');
            const submitBtn = document.getElementById('manual-submit-btn');
            let activeSuggestionIndex = -1;

            const CUTI = @json(\App\Enums\LeaveType::CUTI->value);
            const CUTI_KHUSUS = @json(\App\Enums\LeaveType::CUTI_KHUSUS->value);
            const SAKIT = @json(\App\Enums\LeaveType::SAKIT->value);
            const IZIN_TELAT = @json(\App\Enums\LeaveType::IZIN_TELAT->value);
            const IZIN_TENGAH_KERJA = @json(\App\Enums\LeaveType::IZIN_TENGAH_KERJA->value);
            const IZIN_PULANG_AWAL = @json(\App\Enums\LeaveType::IZIN_PULANG_AWAL->value);
            const OFF_SPV = @json(\App\Enums\LeaveType::OFF_SPV->value);
            let manualCalendar = null;

            function selectedType() {
                const checked = document.querySelector('input[name="type"]:checked');
                return checked ? checked.value : null;
            }

            function selectedEmployeeOption() {
                return employeeSelect ? employeeSelect.options[employeeSelect.selectedIndex] : null;
            }

            function employeeOptions() {
                return employeeSelect ? Array.from(employeeSelect.options).filter(function (option) {
                    return option.value !== '';
                }) : [];
            }

            function syncEmployeeSearchFromSelect() {
                if (!employeeSearchInput) return;
                const option = selectedEmployeeOption();
                employeeSearchInput.value = option && option.value ? (option.getAttribute('data-label') || option.textContent.trim()) : '';
            }

            function hideEmployeeSuggestions() {
                if (!employeeSuggestions) return;
                employeeSuggestions.style.display = 'none';
                employeeSuggestions.innerHTML = '';
                activeSuggestionIndex = -1;
            }

            function selectEmployeeByValue(value) {
                if (!employeeSelect) return;
                employeeSelect.value = value || '';
                syncEmployeeSearchFromSelect();
                employeeSelect.dispatchEvent(new Event('change'));
                hideEmployeeSuggestions();
            }

            function renderEmployeeSuggestions(keyword) {
                if (!employeeSuggestions) return;
                const normalizedKeyword = (keyword || '').trim().toLowerCase();
                const matches = employeeOptions().filter(function (option) {
                    const label = (option.getAttribute('data-label') || option.textContent || '').toLowerCase();
                    return normalizedKeyword === '' || label.includes(normalizedKeyword);
                }).slice(0, 30);

                if (matches.length === 0) {
                    employeeSuggestions.innerHTML = '<div class="employee-suggestion-empty">Karyawan tidak ditemukan.</div>';
                    employeeSuggestions.style.display = 'block';
                    activeSuggestionIndex = -1;
                    return;
                }

                employeeSuggestions.innerHTML = matches.map(function (option, index) {
                    const label = option.getAttribute('data-label') || option.textContent.trim();
                    return '<div class="employee-suggestion-item' + (index === activeSuggestionIndex ? ' active' : '') + '" data-value="' + option.value + '">' + label + '</div>';
                }).join('');
                employeeSuggestions.style.display = 'block';
            }

            function selectedEmployeeRole() {
                const option = selectedEmployeeOption();
                return option ? (option.getAttribute('data-role') || '') : '';
            }

            function selectedEmployeeBalance() {
                const option = selectedEmployeeOption();
                return option ? parseInt(option.getAttribute('data-balance') || '0', 10) : 0;
            }

            function isFiveDayWorkWeek() {
                const role = selectedEmployeeRole();
                return role === 'MANAGER' || role === 'HRD';
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
                    if (!isSunday && !(isSaturday && isFiveDayWorkWeek())) {
                        days++;
                    }
                    cursor.setDate(cursor.getDate() + 1);
                }
                return days;
            }

            function updateBalanceInfo() {
                if (!balanceInfo || !balanceText) return;
                if (selectedType() === CUTI && employeeSelect && employeeSelect.value) {
                    balanceInfo.style.display = 'flex';
                    balanceText.textContent = 'Saldo cuti karyawan: ' + selectedEmployeeBalance() + ' hari.';
                } else {
                    balanceInfo.style.display = 'none';
                }
            }

            function updateSpecialLeaveBadge() {
                if (!specialLeaveSelect || !specialLeaveBadge || !specialLeaveText) return;
                const option = specialLeaveSelect.options[specialLeaveSelect.selectedIndex];
                const days = option ? option.getAttribute('data-days') : null;
                if (days) {
                    specialLeaveBadge.style.display = 'inline-flex';
                    specialLeaveText.textContent = 'Maksimal ' + days + ' Hari';
                } else {
                    specialLeaveBadge.style.display = 'none';
                    specialLeaveText.textContent = 'Maksimal 2 Hari';
                }
            }

            function updateDurationDisplay() {
                if (!durationDisplay) return;
                const startVal = startDateInput ? startDateInput.value : '';
                const endVal = endDateInput ? endDateInput.value : '';
                if (!startVal || !endVal) {
                    durationDisplay.style.display = 'none';
                    durationDisplay.innerHTML = '';
                    return;
                }
                const days = calculateWorkingDays(startVal, endVal);
                durationDisplay.style.display = 'flex';
                durationDisplay.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><strong>Estimasi Durasi: ' + days + ' Hari Kerja</strong>';
            }

            function checkSpecialLeaveLimit() {
                if (!specialLimitWarning) return;
                if (selectedType() !== CUTI_KHUSUS || !specialLeaveSelect || !specialLeaveSelect.value) {
                    specialLimitWarning.style.display = 'none';
                    specialLimitWarning.innerHTML = '';
                    return;
                }
                const option = specialLeaveSelect.options[specialLeaveSelect.selectedIndex];
                const maxDays = parseInt(option ? option.getAttribute('data-days') || '0' : '0', 10);
                const diffDays = calculateWorkingDays(startDateInput.value, endDateInput.value);
                if (maxDays > 0 && diffDays > maxDays) {
                    specialLimitWarning.style.display = 'flex';
                    specialLimitWarning.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>Pengajuan terhitung <b>' + diffDays + ' hari kerja</b>, melebihi batas maksimal <b>' + maxDays + ' hari</b> untuk kategori ini.';
                } else {
                    specialLimitWarning.style.display = 'none';
                    specialLimitWarning.innerHTML = '';
                }
            }

            function toggleSections() {
                const type = selectedType();
                const isTelat = type === IZIN_TELAT;
                const isTengahKerja = type === IZIN_TENGAH_KERJA;
                const isPulangAwal = type === IZIN_PULANG_AWAL;

                updateBalanceInfo();
                updateDurationDisplay();

                if (type === CUTI_KHUSUS) {
                    specialLeaveContainer.style.display = 'block';
                    updateSpecialLeaveBadge();
                    checkSpecialLeaveLimit();
                } else {
                    specialLeaveContainer.style.display = 'none';
                    if (specialLeaveSelect) specialLeaveSelect.value = '';
                    if (specialLeaveBadge) specialLeaveBadge.style.display = 'none';
                    if (specialLimitWarning) specialLimitWarning.style.display = 'none';
                }

                if (picSection) {
                    picSection.style.display = (type === CUTI || type === CUTI_KHUSUS || type === SAKIT) ? 'block' : 'none';
                }

                if (worktimeField) {
                    worktimeField.style.display = (isTelat || isTengahKerja || isPulangAwal) ? 'block' : 'none';
                }

                if (worktimeLabel && worktimeSeparator && endTimeWrapper && endTimeInput) {
                    if (isTengahKerja) {
                        worktimeLabel.textContent = 'Jam Izin Tengah Kerja';
                        worktimeSeparator.style.display = 'inline';
                        endTimeWrapper.style.display = 'block';
                    } else if (isPulangAwal) {
                        worktimeLabel.textContent = 'Jam Pulang';
                        worktimeSeparator.style.display = 'none';
                        endTimeWrapper.style.display = 'none';
                        endTimeInput.value = '';
                    } else if (isTelat) {
                        worktimeLabel.textContent = 'Estimasi Jam Tiba';
                        worktimeSeparator.style.display = 'none';
                        endTimeWrapper.style.display = 'none';
                        endTimeInput.value = '';
                    } else {
                        worktimeSeparator.style.display = 'inline';
                        endTimeWrapper.style.display = 'block';
                    }
                }
            }

            if (employeeSelect) {
                employeeSelect.addEventListener('change', function () {
                    updateBalanceInfo();
                    updateDurationDisplay();
                    checkSpecialLeaveLimit();
                });
            }

            if (employeeSearchInput && employeeSuggestions) {
                employeeSearchInput.addEventListener('focus', function () {
                    renderEmployeeSuggestions(employeeSearchInput.value);
                });

                employeeSearchInput.addEventListener('input', function () {
                    employeeSelect.value = '';
                    updateBalanceInfo();
                    updateDurationDisplay();
                    checkSpecialLeaveLimit();
                    activeSuggestionIndex = -1;
                    renderEmployeeSuggestions(employeeSearchInput.value);
                });

                employeeSearchInput.addEventListener('keydown', function (event) {
                    const items = employeeSuggestions.querySelectorAll('.employee-suggestion-item');
                    if (!items.length) return;

                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        activeSuggestionIndex = Math.min(activeSuggestionIndex + 1, items.length - 1);
                        renderEmployeeSuggestions(employeeSearchInput.value);
                    } else if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        activeSuggestionIndex = Math.max(activeSuggestionIndex - 1, 0);
                        renderEmployeeSuggestions(employeeSearchInput.value);
                    } else if (event.key === 'Enter' && activeSuggestionIndex >= 0) {
                        event.preventDefault();
                        const target = items[activeSuggestionIndex];
                        if (target) {
                            selectEmployeeByValue(target.getAttribute('data-value'));
                        }
                    } else if (event.key === 'Escape') {
                        hideEmployeeSuggestions();
                    }
                });

                employeeSuggestions.addEventListener('mousedown', function (event) {
                    const item = event.target.closest('.employee-suggestion-item');
                    if (!item) return;
                    event.preventDefault();
                    selectEmployeeByValue(item.getAttribute('data-value'));
                });

                document.addEventListener('click', function (event) {
                    if (!event.target.closest('.employee-picker')) {
                        hideEmployeeSuggestions();
                    }
                });
            }

            if (specialLeaveSelect) {
                specialLeaveSelect.addEventListener('change', function () {
                    updateSpecialLeaveBadge();
                    checkSpecialLeaveLimit();
                });
            }

            if (startDateInput) startDateInput.addEventListener('change', function () { updateDurationDisplay(); checkSpecialLeaveLimit(); });
            if (endDateInput) endDateInput.addEventListener('change', function () { updateDurationDisplay(); checkSpecialLeaveLimit(); });

            typeRadios.forEach(function (radio) {
                radio.addEventListener('change', function () {
                    toggleSections();
                    if (manualCalendar) manualCalendar.redraw();
                });
            });

            if (photoInput && previewContainer && previewImg) {
                photoInput.addEventListener('change', function () {
                    const file = this.files[0];
                    if (file && file.type && file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function (event) {
                            previewImg.src = event.target.result;
                            previewContainer.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        previewContainer.style.display = 'none';
                        previewImg.src = '';
                    }
                });
            }

            if (form && submitBtn) {
                form.addEventListener('submit', function () {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin" width="18" height="18"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Menyimpan...';
                });
            }

            if (typeof flatpickr === 'function' && rangeInput) {
                manualCalendar = flatpickr(rangeInput, {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    locale: { rangeSeparator: ' sampai ' },
                    disable: [function(date) {
                        return selectedType() === OFF_SPV && date.getDay() !== 6;
                    }],
                    onChange: function (selectedDates, dateStr) {
                        if (!dateStr) {
                            startDateInput.value = '';
                            endDateInput.value = '';
                        } else {
                            const parts = dateStr.split(' sampai ');
                            startDateInput.value = parts[0] || '';
                            endDateInput.value = parts[1] || parts[0] || '';
                        }
                        startDateInput.dispatchEvent(new Event('change'));
                        endDateInput.dispatchEvent(new Event('change'));
                        if (selectedType() === OFF_SPV && selectedDates.length === 1) manualCalendar.close();
                    }
                });
            }

            updateSpecialLeaveBadge();
            syncEmployeeSearchFromSelect();
            toggleSections();
        });
    </script>
    @endpush

    <style>
        /* === HEADER (selaras dengan leave_requests/create) === */
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

        /* === BACK BUTTON (selaras dengan leave_requests/create) === */
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

        /* === CONTAINER (mengikuti lebar penuh layout seperti leave-requests/create) === */
        .mlc-container {
            max-width: 100%;
            margin: 0 auto;
            padding-bottom: 40px;
        }

        /* === ALERT (selaras dengan lrc-alert) === */
        .mlc-alert {
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
        .mlc-alert svg { flex-shrink: 0; margin-top: 1px; }
        .mlc-alert--error {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
        }
        .mlc-alert--success {
            background: #F0FDF4;
            border: 1px solid #BBF7D0;
            color: #15803D;
        }

        /* === INFO BANNER === */
        .mlc-banner {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            background: #EFF6FF;
            border: 1px solid #DBEAFE;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 0.8125rem;
            color: #1E40AF;
            line-height: 1.5;
        }
        .mlc-banner svg { flex-shrink: 0; margin-top: 1px; }

        /* === STEP CARD (selaras dengan lrc-step) === */
        .mlc-step {
            background: var(--white, #fff);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 12px;
            border: 1px solid var(--border-light, #F3F4F6);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .mlc-step__header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }
        .mlc-step__num {
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-dark, #0A3D62);
            color: #fff;
            border-radius: 50%;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .mlc-step__headtext {
            display: flex;
            flex-direction: column;
            gap: 1px;
            min-width: 0;
        }
        .mlc-step__title {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            line-height: 1.3;
        }
        .mlc-step__sub {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-muted, #6B7280);
            line-height: 1.35;
        }

        /* === FIELD & LABEL (selaras dengan lrc-field/lrc-label) === */
        .mlc-field { margin-bottom: 14px; }
        .mlc-field:last-child { margin-bottom: 0; }
        .mlc-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin-bottom: 8px;
        }
        .mlc-label--accent { color: var(--primary-dark, #0A3D62); }
        .mlc-required { color: var(--error, #EF4444); font-weight: 700; }
        .mlc-helper {
            color: var(--text-muted, #6B7280);
            font-size: 0.75rem;
            font-weight: 500;
            margin: 6px 0 0;
            line-height: 1.5;
        }

        /* === INPUT / SELECT / TEXTAREA (selaras dengan lrc-input) === */
        .mlc-input-wrap { position: relative; }
        .mlc-input__icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light, #9CA3AF);
            pointer-events: none;
        }
        .mlc-input,
        .mlc-select,
        .mlc-textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--border, #E5E7EB);
            border-radius: 10px;
            font-size: 0.9375rem;
            color: var(--text-primary, #111827);
            background: var(--white, #fff);
            transition: all 0.2s ease;
            outline: none;
            font-family: inherit;
            box-sizing: border-box;
        }
        .mlc-input--icon { padding-left: 42px; }
        .mlc-input::placeholder,
        .mlc-textarea::placeholder { color: var(--text-light, #9CA3AF); }
        .mlc-input:focus,
        .mlc-select:focus,
        .mlc-textarea:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .mlc-select-wrap { position: relative; }
        .mlc-select {
            padding-right: 40px;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
        }
        .mlc-select__arrow {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light, #9CA3AF);
            pointer-events: none;
        }
        .mlc-textarea {
            resize: vertical;
            min-height: 90px;
            line-height: 1.5;
        }

        /* === GRID 2 KOLOM === */
        .mlc-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 14px;
        }
        .mlc-grid-2 .mlc-field { margin-bottom: 0; }

        /* === RADIO CARD JENIS PENGAJUAN === */
        .mlc-radio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 8px;
        }
        .mlc-radio-card {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 11px 12px;
            border: 1.5px solid var(--border, #E5E7EB);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.15s ease;
            background: var(--white, #fff);
        }
        .mlc-radio-card:hover {
            border-color: var(--primary, #145DA0);
            background: var(--gray-50, #F5F7FA);
        }
        .mlc-radio-card:has(input:checked) {
            border-color: var(--primary, #145DA0);
            background: rgba(20, 93, 160, 0.06);
        }
        .mlc-radio-card input[type="radio"] {
            accent-color: var(--primary, #145DA0);
            width: 16px;
            height: 16px;
            margin: 0;
            flex-shrink: 0;
        }
        .mlc-radio-card__label {
            font-size: 0.8125rem;
            color: var(--text-primary, #111827);
            font-weight: 600;
            line-height: 1.3;
        }

        /* === INFO / WARNING / DURATION (selaras dengan lrc-info/lrc-warning/lrc-duration) === */
        .mlc-info {
            display: flex;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 0.8125rem;
            margin-top: 12px;
            background: #EFF6FF;
            border: 1px solid #DBEAFE;
            color: #1E40AF;
        }
        .mlc-info svg { flex-shrink: 0; margin-top: 1px; }
        .mlc-info strong { font-weight: 600; display: block; margin-bottom: 2px; }
        .mlc-info p { margin: 0; font-size: 0.75rem; opacity: 0.9; }
        .mlc-duration {
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
        .mlc-duration svg { width: 16px; height: 16px; flex-shrink: 0; }
        .mlc-warning {
            align-items: flex-start;
            gap: 8px;
            margin-top: 10px;
            padding: 10px 12px;
            background: #FEFCE8;
            border: 1px solid #FDE68A;
            border-radius: 10px;
            color: #854D0E;
            font-size: 0.8125rem;
            font-weight: 500;
            line-height: 1.5;
        }
        .mlc-warning svg { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; }

        /* === CUTI KHUSUS BOX === */
        .mlc-special-box {
            margin-top: 12px;
            padding: 14px;
            background: rgba(20, 93, 160, 0.04);
            border: 1px solid rgba(20, 93, 160, 0.15);
            border-radius: 12px;
        }
        .mlc-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            padding: 5px 10px;
            background: #EFF6FF;
            color: var(--primary, #145DA0);
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* === TIME RANGE === */
        .mlc-time-range {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .mlc-time-box { flex: 1; min-width: 0; }
        .mlc-time-sep {
            color: var(--text-muted, #6B7280);
            font-size: 0.8125rem;
            font-weight: 600;
            flex-shrink: 0;
        }

        /* === EMPLOYEE PICKER === */
        .employee-picker { position: relative; }
        .employee-select-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }
        .employee-suggestions {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            z-index: 100;
            background: var(--white, #fff);
            border: 1.5px solid var(--border, #E5E7EB);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            max-height: 280px;
            overflow-y: auto;
            padding: 6px;
        }
        .employee-suggestion-item {
            padding: 11px 12px;
            border-radius: 8px;
            font-size: 0.9375rem;
            color: var(--text-primary, #111827);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .employee-suggestion-item:hover,
        .employee-suggestion-item.active {
            background: var(--gray-50, #F5F7FA);
            color: var(--primary, #145DA0);
        }
        .employee-suggestion-empty {
            padding: 12px;
            font-size: 0.875rem;
            color: var(--text-muted, #6B7280);
            text-align: center;
        }

        /* === UPLOAD (selaras dengan lrc-upload) === */
        .mlc-upload {
            position: relative;
            border: 2px dashed var(--border, #E5E7EB);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            background: var(--gray-50, #F5F7FA);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .mlc-upload:hover {
            border-color: var(--primary, #145DA0);
            background: rgba(20, 93, 160, 0.03);
        }
        .mlc-upload__input {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .mlc-upload__content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            color: var(--text-muted, #6B7280);
        }
        .mlc-upload__content svg { color: var(--text-light, #9CA3AF); }
        .mlc-upload__title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
        }
        .mlc-upload__desc {
            font-size: 0.75rem;
            color: var(--text-light, #9CA3AF);
            font-weight: 500;
        }

        /* === PREVIEW === */
        .mlc-preview {
            display: none;
            margin-top: 10px;
            padding: 10px;
            background: var(--gray-50, #F5F7FA);
            border: 1px solid var(--border-light, #F3F4F6);
            border-radius: 10px;
        }
        .mlc-preview__label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted, #6B7280);
            margin: 0 0 6px 0;
        }
        .mlc-preview img {
            max-width: 100%;
            max-height: 180px;
            border-radius: 8px;
            display: block;
            margin: 0 auto;
        }

        /* === ACTIONS === */
        .manual-form-shell { display: block; }
        .mlc-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin: 20px 0 32px;
        }
        .mlc-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            font-family: inherit;
        }
        .mlc-btn svg { width: 18px; height: 18px; flex-shrink: 0; }
        .mlc-btn--primary {
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #fff;
            border: none;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
        }
        .mlc-btn--primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .mlc-btn--primary:disabled {
            background: #94A3B8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .mlc-btn--secondary {
            background: var(--white, #fff);
            color: var(--text-muted, #6B7280);
            border: 1.5px solid var(--border, #E5E7EB);
        }
        .mlc-btn--secondary:hover {
            color: var(--text-primary, #111827);
            background: var(--gray-50, #F5F7FA);
        }

        /* === SPIN ANIMATION === */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .spin { animation: spin 1s linear infinite; }

        /* === RESPONSIVE === */
        @media (max-width: 639px) {
            .mlc-step {
                padding: 16px;
                margin-bottom: 10px;
                border-radius: 14px;
            }
            .mlc-grid-2 { grid-template-columns: 1fr; }
            .mlc-radio-grid { grid-template-columns: 1fr; }
            .mlc-time-range {
                flex-direction: column;
                align-items: stretch;
            }
            .mlc-time-sep { display: none; }
            .mlc-actions {
                flex-direction: column-reverse;
            }
            .mlc-actions .mlc-btn { width: 100%; }
            .back-btn {
                height: 40px;
                padding: 0 14px 0 12px;
            }
            .back-btn-text { font-size: 0.8125rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            .back-btn,
            .mlc-input,
            .mlc-select,
            .mlc-textarea,
            .mlc-radio-card,
            .mlc-btn { transition-duration: 0.01ms; }
            .mlc-btn--primary:hover { transform: none; }
            .spin { animation-duration: 1.5s; }
        }
    </style>

</x-app>
