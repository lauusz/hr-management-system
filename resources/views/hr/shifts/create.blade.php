<x-app title="Tambah Shift">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke-width="2"/>
                    <polyline points="12 7 12 12 15 14" stroke-width="2"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Tambah Shift Kerja</h1>
                <p class="section-subtitle">Atur jadwal jam kerja, jam pulang, dan hari libur untuk shift baru</p>
            </div>
        </div>
    </x-slot>

    <div class="mlc-container">

        <a href="{{ route('hr.shifts.index') }}" class="back-btn" aria-label="Kembali ke Master Shift Kerja">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        {{-- Flash / Error Messages --}}
        @if (session('error'))
            <div class="mlc-alert mlc-alert--error" role="alert">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mlc-alert mlc-alert--error" role="alert">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @if (session('success'))
            <div class="mlc-alert mlc-alert--success">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Info Banner --}}
        <div class="mlc-banner" role="note">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Kolom bertanda <strong>*</strong> wajib diisi. Pastikan jam masuk, jam pulang, dan aturan hari libur sudah sesuai sebelum disimpan.</span>
        </div>

        {{-- Form --}}
        <div class="manual-form-shell">
            <form method="POST" action="{{ route('hr.shifts.store') }}">
                @csrf

                {{-- Langkah 1: Identitas Shift Kerja --}}
                <div class="mlc-step">
                    <div class="mlc-step__header">
                        <span class="mlc-step__num">1</span>
                        <div class="mlc-step__headtext">
                            <span class="mlc-step__title">Identitas Shift Kerja</span>
                            <span class="mlc-step__sub">Langkah 1 dari 2 · Tentukan nama, deskripsi, dan status aktif shift</span>
                        </div>
                    </div>

                    <div class="mlc-field">
                        <label for="name" class="mlc-label">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" stroke-width="2"/>
                                <polyline points="12 7 12 12 15 14" stroke-width="2"/>
                            </svg>
                            Nama Shift <span class="mlc-required">*</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="mlc-input"
                            value="{{ old('name') }}"
                            placeholder="Contoh: Shift Pagi, Office Hour"
                            required>
                        <p class="mlc-helper">Gunakan nama shift yang jelas untuk mempermudah pengaturan jadwal karyawan.</p>
                    </div>

                    <div class="mlc-field">
                        <label for="description" class="mlc-label">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Deskripsi
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            class="mlc-textarea"
                            rows="2"
                            placeholder="Keterangan tambahan (opsional)">{{ old('description') }}</textarea>
                    </div>

                    <div class="mlc-field">
                        <span class="mlc-label">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Status Shift
                        </span>
                        <label class="mlc-radio-card mlc-checkbox-card">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') == '1')>
                            <span class="mlc-radio-card__label">Shift Aktif</span>
                        </label>
                    </div>
                </div>

                {{-- Langkah 2: Pengaturan Jadwal Harian --}}
                <div class="mlc-step">
                    <div class="mlc-step__header">
                        <span class="mlc-step__num">2</span>
                        <div class="mlc-step__headtext">
                            <span class="mlc-step__title">Pengaturan Jadwal Harian</span>
                            <span class="mlc-step__sub">Langkah 2 dari 2 · Atur jam masuk, jam pulang, penanda lintas hari, dan hari libur mingguan</span>
                        </div>
                    </div>

                    <div class="mlc-field">
                        <input type="hidden" name="is_wita" value="0">
                        <label class="mlc-radio-card mlc-checkbox-card">
                            <input type="checkbox" name="is_wita" value="1" @checked(old('is_wita', false))>
                            <span class="mlc-radio-card__label">WITA (selisih 1 jam dari WIB)</span>
                        </label>
                        <p class="mlc-helper">Berlaku untuk semua karyawan yang menggunakan shift ini. Jika tidak dicentang, waktu mengikuti WIB.</p>
                    </div>

                    <div class="table-responsive">
                        <table class="shift-table">
                            <thead>
                                <tr>
                                    <th style="width: 15%">Hari</th>
                                    <th style="width: 20%">Jam Masuk</th>
                                    <th style="width: 20%">Jam Pulang</th>
                                    <th style="width: 10%; text-align: center;">Lintas Hari</th>
                                    <th style="width: 10%; text-align: center;">Libur</th>
                                    <th style="width: 25%">Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $days = [
                                        1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
                                        4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu',
                                    ];
                                @endphp
                                @foreach($days as $dayNumber => $dayName)
                                    <tr>
                                        <td class="day-name">{{ $dayName }}</td>
                                        <td>
                                            <input
                                                type="time"
                                                name="days[{{ $dayNumber }}][start_time]"
                                                class="form-control form-control-sm start-{{ $dayNumber }}"
                                                value="{{ old("days.$dayNumber.start_time") }}">
                                        </td>
                                        <td>
                                            <input
                                                type="time"
                                                name="days[{{ $dayNumber }}][end_time]"
                                                class="form-control form-control-sm end-{{ $dayNumber }}"
                                                value="{{ old("days.$dayNumber.end_time") }}">
                                        </td>
                                        <td style="text-align: center;">
                                            <label class="checkbox-wrapper center-box">
                                                <input
                                                    type="checkbox"
                                                    name="days[{{ $dayNumber }}][is_overnight]"
                                                    class="overnight-toggle overnight-{{ $dayNumber }}"
                                                    value="1"
                                                    @checked(old("days.$dayNumber.is_overnight"))>
                                            </label>
                                        </td>
                                        <td style="text-align: center;">
                                            <label class="checkbox-wrapper center-box">
                                                <input
                                                    type="checkbox"
                                                    name="days[{{ $dayNumber }}][is_holiday]"
                                                    class="holiday-toggle"
                                                    data-day="{{ $dayNumber }}"
                                                    value="1"
                                                    @checked(old("days.$dayNumber.is_holiday"))>
                                            </label>
                                        </td>
                                        <td>
                                            <input
                                                type="text"
                                                name="days[{{ $dayNumber }}][note]"
                                                class="form-control form-control-sm note-{{ $dayNumber }}"
                                                value="{{ old("days.$dayNumber.note") }}"
                                                placeholder="Ket.">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="apv-action-bar">
                    <div class="apv-action-dock">
                        <div class="apv-action-main">
                            <a href="{{ route('hr.shifts.index') }}" class="apv-action-btn apv-action-btn--secondary">
                                Batal
                            </a>
                            <button type="submit" class="apv-action-btn apv-action-btn--primary">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Simpan Shift
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.holiday-toggle').forEach(cb => {
                const day = cb.dataset.day;
                const startInput = document.querySelector('.start-' + day);
                const endInput = document.querySelector('.end-' + day);
                const overnightInput = document.querySelector('.overnight-' + day);
                const noteInput = document.querySelector('.note-' + day);

                const applyState = () => {
                    if (cb.checked) {
                        if (startInput) {
                            startInput.value = '';
                            startInput.disabled = true;
                        }
                        if (endInput) {
                            endInput.value = '';
                            endInput.disabled = true;
                        }
                        if (overnightInput) {
                            overnightInput.checked = false;
                            overnightInput.disabled = true;
                        }
                    } else {
                        if (startInput) startInput.disabled = false;
                        if (endInput) endInput.disabled = false;
                        if (overnightInput) overnightInput.disabled = false;
                        if (noteInput) noteInput.disabled = false;
                    }
                };

                applyState();
                cb.addEventListener('change', applyState);
            });
        });
    </script>

    <style>
        /* === HEADER (selaras dengan leave_requests/create_manual) === */
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

        /* === BACK BUTTON (selaras dengan leave_requests/create_manual) === */
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

        /* === CONTAINER (mengikuti lebar penuh layout + ruang untuk fixed action dock) === */
        .mlc-container {
            max-width: 100%;
            margin: 0 auto;
            padding-bottom: 120px;
        }

        /* === ALERT (selaras dengan mlc-alert) === */
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

        /* === STEP CARD (selaras dengan mlc-step) === */
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

        /* === FIELD & LABEL === */
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
        .mlc-required { color: var(--error, #EF4444); font-weight: 700; }
        .mlc-helper {
            color: var(--text-muted, #6B7280);
            font-size: 0.75rem;
            font-weight: 500;
            margin: 6px 0 0;
            line-height: 1.5;
        }

        /* === INPUT / TEXTAREA === */
        .mlc-input,
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
        .mlc-input::placeholder,
        .mlc-textarea::placeholder { color: var(--text-light, #9CA3AF); }
        .mlc-input:focus,
        .mlc-textarea:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .mlc-textarea {
            resize: vertical;
            min-height: 84px;
            line-height: 1.5;
        }

        /* === RADIO / CHECKBOX CARD === */
        .mlc-radio-card {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 14px;
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
        .mlc-radio-card input[type="checkbox"] {
            accent-color: var(--primary, #145DA0);
            width: 16px;
            height: 16px;
            margin: 0;
            flex-shrink: 0;
            cursor: pointer;
        }
        .mlc-radio-card__label {
            font-size: 0.8125rem;
            color: var(--text-primary, #111827);
            font-weight: 600;
            line-height: 1.3;
        }

        /* === SHIFT DAILY TABLE (selaras dengan /hr/shifts/edit) === */
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            outline: none;
            background: var(--white, #fff);
            color: var(--text-primary, #111827);
            font: inherit;
            font-size: 14px;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        .form-control:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .form-control:disabled {
            background: var(--gray-50, #F5F7FA);
            color: var(--text-light, #9CA3AF);
            cursor: not-allowed;
        }
        .form-control-sm {
            padding: 8px 10px;
            font-size: 13px;
            border-radius: 8px;
        }
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        .checkbox-wrapper input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--primary, #145DA0);
            cursor: pointer;
        }
        .checkbox-wrapper input[type="checkbox"]:disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }
        .center-box {
            justify-content: center;
        }
        .table-responsive {
            overflow-x: auto;
            border: 1px solid var(--border-light, #E5E7EB);
            border-radius: 12px;
        }
        .shift-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 640px;
        }
        .shift-table th {
            background: var(--gray-50, #F5F7FA);
            padding: 12px 14px;
            text-align: left;
            font-size: 0.6875rem;
            font-weight: 700;
            color: var(--text-muted, #6B7280);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
        }
        .shift-table td {
            padding: 10px 14px;
            border-bottom: 1px solid var(--border-light, #F3F4F6);
            vertical-align: middle;
        }
        .shift-table tr:last-child td {
            border-bottom: none;
        }
        .shift-table tbody tr:hover td {
            background: var(--gray-50, #F5F7FA);
        }
        .day-name {
            font-weight: 600;
            color: var(--text-primary, #111827);
            font-size: 13.5px;
        }

        /* === FIXED ACTION BAR (selaras dengan /hr/leave-requests/show) === */
        .manual-form-shell { display: block; }
        .apv-action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
            background: transparent;
            border: none;
            box-shadow: none;
            padding: 0;
            pointer-events: none;
            transition: left 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        right 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        bottom 0.35s ease;
        }
        .apv-action-dock {
            background: var(--white, #FFFFFF);
            border-top: 1px solid var(--border-light, #E5E7EB);
            border-radius: 18px 18px 0 0;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.06);
            padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
            pointer-events: auto;
        }
        .apv-action-main {
            display: flex;
            flex-direction: column-reverse;
            gap: 8px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .apv-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 11px 18px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
            font-family: inherit;
            min-height: 44px;
        }
        .apv-action-btn svg { flex-shrink: 0; }
        .apv-action-btn--primary {
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: var(--white, #FFFFFF);
            box-shadow: 0 2px 8px rgba(10, 61, 98, 0.2);
        }
        .apv-action-btn--primary:hover {
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.3);
            transform: translateY(-1px);
        }
        .apv-action-btn--secondary {
            background: var(--gray-50, #F5F7FA);
            color: var(--text-secondary, #6B7280);
            border: 1.5px solid var(--border-light, #E5E7EB);
        }
        .apv-action-btn--secondary:hover {
            background: var(--gray-100, #E5E7EB);
        }

        /* === RESPONSIVE === */
        @media (min-width: 640px) {
            .apv-action-main {
                flex-direction: row;
                flex-wrap: wrap;
                align-items: center;
                gap: 12px;
                width: 100%;
            }
            .apv-action-btn {
                width: auto;
                flex: 1 1 0;
                min-width: 0;
                padding: 12px 24px;
                font-size: 14px;
            }
        }
        @media (min-width: 1025px) {
            .apv-action-bar {
                left: calc(var(--sidebar-width) + 12px);
                right: 0;
                bottom: 16px;
                padding: 0 16px;
            }
            .app.sidebar-collapsed .apv-action-bar {
                left: 0;
            }
            .apv-action-dock {
                border: 1px solid var(--border-light, #E5E7EB);
                border-radius: 18px;
                box-shadow: 0 -4px 20px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
                padding: 10px;
                max-width: 1200px;
                margin: 0 auto;
            }
        }
        @media (max-width: 639px) {
            .mlc-step {
                padding: 16px;
                margin-bottom: 10px;
                border-radius: 14px;
            }
            .back-btn {
                height: 40px;
                padding: 0 14px 0 12px;
            }
            .back-btn-text { font-size: 0.8125rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            .back-btn,
            .mlc-input,
            .mlc-textarea,
            .mlc-radio-card,
            .apv-action-btn { transition-duration: 0.01ms; }
            .apv-action-btn--primary:hover { transform: none; }
        }
    </style>
</x-app>
