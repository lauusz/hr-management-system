<x-app title="Tambah Jabatan">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Tambah Jabatan Baru</h1>
                <p class="section-subtitle">Daftarkan nama jabatan dan hubungkan dengan divisi terkait</p>
            </div>
        </div>
    </x-slot>

    <div class="pos-container">

        <a href="{{ route('hr.organization') }}" class="back-btn" aria-label="Kembali ke Divisi dan Jabatan">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        {{-- Flash / Error Messages --}}
        @if ($errors->any())
        <div class="pos-alert pos-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        {{-- Form Card --}}
        <div class="pos-step">
            <div class="pos-step__header">
                <span class="pos-step__num">1</span>
                <h2 class="pos-step__title">Informasi Jabatan</h2>
            </div>

            <form method="POST" action="{{ route('hr.positions.store') }}">
                @csrf

                <div class="pos-field">
                    <label for="name" class="pos-label">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Nama Jabatan <span class="pos-required">*</span>
                    </label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        class="pos-input @error('name') pos-input--invalid @enderror"
                        value="{{ old('name') }}"
                        placeholder="Contoh: Senior Staff, Manager, Helper"
                        required>
                    @error('name')
                        <p class="pos-error-text">{{ $message }}</p>
                    @else
                        <p class="pos-helper">Nama jabatan harus unik dalam divisi yang sama.</p>
                    @enderror
                </div>

                <div class="pos-field">
                    <label for="division_id" class="pos-label">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Divisi
                    </label>
                    <div class="pos-select-wrap">
                        <select id="division_id" name="division_id" class="pos-select @error('division_id') pos-input--invalid @enderror">
                            <option value="">Tidak ada / Umum</option>
                            @foreach ($divisions as $division)
                            <option value="{{ $division->id }}" @selected(old('division_id') == $division->id)>
                                {{ $division->name }}
                            </option>
                            @endforeach
                        </select>
                        <svg class="pos-select__arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    @error('division_id')
                        <p class="pos-error-text">{{ $message }}</p>
                    @else
                        <p class="pos-helper">Opsional. Pilih divisi yang menaungi jabatan ini.</p>
                    @enderror
                </div>

                <div class="pos-field">
                    <label class="pos-checkbox">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', 1))>
                        <span class="pos-checkbox__box">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <span class="pos-checkbox__text">
                            <span class="pos-checkbox__title">Jabatan aktif dan dapat digunakan</span>
                            <span class="pos-checkbox__desc">Jabatan nonaktif tidak akan muncul pada pilihan di formulir lain.</span>
                        </span>
                    </label>
                    @error('is_active')
                        <p class="pos-error-text">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pos-actions">
                    <a href="{{ route('hr.organization') }}" class="pos-btn pos-btn--secondary">
                        Batal
                    </a>
                    <button type="submit" class="pos-btn pos-btn--primary">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan Jabatan
                    </button>
                </div>
            </form>
        </div>

    </div>

    <style>
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
        .icon-navy {
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark, #0A3D62);
        }

        /* ========================================== */
        /* CONTAINER — lebar penuh mengikuti layout   */
        /* ========================================== */
        .pos-container {
            max-width: 100%;
            margin: 0 auto;
            padding-bottom: 40px;
        }

        /* ========================================== */
        /* BACK BUTTON                                */
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

        /* ========================================== */
        /* ALERT                                      */
        /* ========================================== */
        .pos-alert {
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
        .pos-alert svg { flex-shrink: 0; margin-top: 1px; }
        .pos-alert--error {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
        }

        /* ========================================== */
        /* STEP CARD                                  */
        /* ========================================== */
        .pos-step {
            background: var(--white, #fff);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 12px;
            border: 1px solid var(--border-light, #F3F4F6);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .pos-step__header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }
        .pos-step__num {
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
        .pos-step__title {
            margin: 0;
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
        }

        /* ========================================== */
        /* FIELDS                                     */
        /* ========================================== */
        .pos-field { margin-bottom: 14px; }
        .pos-field:last-of-type { margin-bottom: 0; }
        .pos-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin-bottom: 8px;
        }
        .pos-label svg { color: var(--text-light, #9CA3AF); flex-shrink: 0; }
        .pos-required { color: var(--error, #EF4444); font-weight: 700; }
        .pos-helper {
            color: var(--text-muted, #6B7280);
            font-size: 0.75rem;
            font-weight: 500;
            margin: 6px 0 0;
            line-height: 1.5;
        }
        .pos-error-text {
            color: #B91C1C;
            font-size: 0.75rem;
            font-weight: 600;
            margin: 6px 0 0;
            line-height: 1.5;
        }

        /* ========================================== */
        /* INPUT / SELECT                             */
        /* ========================================== */
        .pos-input,
        .pos-select {
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
        .pos-input::placeholder { color: var(--text-light, #9CA3AF); }
        .pos-input:focus,
        .pos-select:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .pos-input--invalid,
        .pos-input--invalid:focus {
            border-color: #EF4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.08);
        }
        .pos-select-wrap { position: relative; }
        .pos-select {
            padding-right: 40px;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
        }
        .pos-select__arrow {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light, #9CA3AF);
            pointer-events: none;
        }

        /* ========================================== */
        /* CHECKBOX                                   */
        /* ========================================== */
        .pos-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
            padding: 12px 14px;
            border: 1.5px solid var(--border, #E5E7EB);
            border-radius: 10px;
            background: var(--gray-50, #F5F7FA);
            transition: all 0.15s ease;
        }
        .pos-checkbox:hover {
            border-color: var(--primary, #145DA0);
        }
        .pos-checkbox input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        .pos-checkbox__box {
            width: 18px;
            height: 18px;
            border: 1.5px solid var(--border, #E5E7EB);
            border-radius: 5px;
            background: var(--white, #fff);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 1px;
            color: transparent;
            transition: all 0.15s ease;
        }
        .pos-checkbox input[type="checkbox"]:checked + .pos-checkbox__box {
            background: var(--primary, #145DA0);
            border-color: var(--primary, #145DA0);
            color: #fff;
        }
        .pos-checkbox input[type="checkbox"]:focus-visible + .pos-checkbox__box {
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.15);
        }
        .pos-checkbox__text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .pos-checkbox__title {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            line-height: 1.4;
        }
        .pos-checkbox__desc {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-muted, #6B7280);
            line-height: 1.5;
        }

        /* ========================================== */
        /* ACTIONS                                    */
        /* ========================================== */
        .pos-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-light, #F3F4F6);
        }
        .pos-btn {
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
        .pos-btn svg { width: 18px; height: 18px; flex-shrink: 0; }
        .pos-btn--primary {
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #fff;
            border: none;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
        }
        .pos-btn--primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .pos-btn--secondary {
            background: var(--white, #fff);
            color: var(--text-muted, #6B7280);
            border: 1.5px solid var(--border, #E5E7EB);
        }
        .pos-btn--secondary:hover {
            color: var(--text-primary, #111827);
            background: var(--gray-50, #F5F7FA);
        }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (max-width: 639px) {
            .pos-step {
                padding: 16px;
                margin-bottom: 10px;
                border-radius: 14px;
            }
            .pos-actions {
                flex-direction: column-reverse;
            }
            .pos-actions .pos-btn { width: 100%; }
            .back-btn {
                height: 40px;
                padding: 0 14px 0 12px;
            }
            .back-btn-text { font-size: 0.8125rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            .back-btn,
            .pos-input,
            .pos-select,
            .pos-checkbox,
            .pos-checkbox__box,
            .pos-btn { transition-duration: 0.01ms; }
            .pos-btn--primary:hover { transform: none; }
        }
    </style>
</x-app>
