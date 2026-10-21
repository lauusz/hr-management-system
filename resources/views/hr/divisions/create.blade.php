<x-app title="Tambah Divisi">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm14 10v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Tambah Divisi Baru</h1>
                <p class="section-subtitle">Buat divisi baru dan tentukan supervisor bila diperlukan</p>
            </div>
        </div>
    </x-slot>

    <div class="mlc-container">

        <a href="{{ route('hr.organization') }}" class="back-btn" aria-label="Kembali ke Divisi dan Jabatan">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        {{-- Flash / Error Messages --}}
        @if (session('error'))
            <div class="mlc-alert mlc-alert--error">
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
            <span>Kolom bertanda <strong>*</strong> wajib diisi. Supervisor divisi bersifat opsional dan dapat diatur atau diubah kapan saja.</span>
        </div>

        {{-- Form --}}
        <div class="manual-form-shell">
            <form method="POST" action="{{ route('hr.divisions.store') }}">
                @csrf

                <div class="mlc-step">
                    <div class="mlc-step__header">
                        <span class="mlc-step__num">1</span>
                        <div class="mlc-step__headtext">
                            <h2 class="mlc-step__title">Informasi Divisi</h2>
                            <span class="mlc-step__sub">Langkah 1 dari 1 · Tentukan nama divisi dan supervisor penanggung jawab</span>
                        </div>
                    </div>

                    <div class="mlc-field">
                        <label for="name" class="mlc-label">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Nama Divisi <span class="mlc-required">*</span>
                        </label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            class="mlc-input @error('name') mlc-input--invalid @enderror"
                            value="{{ old('name') }}"
                            placeholder="Contoh: Finance, Marketing"
                            required>
                        @error('name')
                            <p class="mlc-error-text">{{ $message }}</p>
                        @else
                            <p class="mlc-helper">Nama divisi harus unik dan belum terdaftar di sistem.</p>
                        @enderror
                    </div>

                    <div class="mlc-field">
                        <label for="supervisor" class="mlc-label">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Supervisor
                        </label>
                        <div class="mlc-select-wrap">
                            <select id="supervisor" name="supervisor_id" class="mlc-select @error('supervisor_id') mlc-input--invalid @enderror">
                                <option value="">Tidak ada / Belum ditentukan</option>
                                @foreach ($supervisors as $sup)
                                    <option value="{{ $sup->id }}" @selected((string) old('supervisor_id') === (string) $sup->id)>
                                        {{ $sup->name }} {{ $sup->username ? '('.$sup->username.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <svg class="mlc-select__arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        @error('supervisor_id')
                            <p class="mlc-error-text">{{ $message }}</p>
                        @else
                            <p class="mlc-helper">Opsional. Supervisor bertanggung jawab menyetujui izin dan cuti anggota divisi.</p>
                        @enderror
                    </div>
                </div>

                <div class="apv-action-bar" aria-label="Aksi simpan divisi">
                    <div class="apv-action-dock">
                        <div class="apv-action-main">
                            <a href="{{ route('hr.organization') }}" class="apv-action-btn apv-action-btn--secondary">
                                Batal
                            </a>
                            <button type="submit" class="apv-action-btn apv-action-btn--primary">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Simpan Divisi
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>

    <style>
        /* === HEADER (selaras dengan /hr/leave/master/create & /hr/locations/create) === */
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

        /* === BACK BUTTON === */
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

        /* === CONTAINER === */
        .mlc-container {
            max-width: 100%;
            margin: 0 auto;
            padding-bottom: 120px;
        }

        /* === ALERT === */
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

        /* === STEP CARD === */
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
            margin: 0;
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
        .mlc-label svg { color: var(--text-light, #9CA3AF); flex-shrink: 0; }
        .mlc-required { color: var(--error, #EF4444); font-weight: 700; }
        .mlc-helper {
            color: var(--text-muted, #6B7280);
            font-size: 0.75rem;
            font-weight: 500;
            margin: 6px 0 0;
            line-height: 1.5;
        }
        .mlc-error-text {
            color: #B91C1C;
            font-size: 0.75rem;
            font-weight: 600;
            margin: 6px 0 0;
            line-height: 1.5;
        }

        /* === INPUT / SELECT === */
        .mlc-input,
        .mlc-select {
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
        .mlc-input::placeholder { color: var(--text-light, #9CA3AF); }
        .mlc-input:focus,
        .mlc-select:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .mlc-input--invalid,
        .mlc-input--invalid:focus {
            border-color: #EF4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.08);
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
            .mlc-select,
            .apv-action-btn { transition-duration: 0.01ms; }
            .apv-action-btn--primary:hover { transform: none; }
        }
    </style>
</x-app>
