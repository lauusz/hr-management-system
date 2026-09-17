<x-app title="Tambah Divisi">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm14 10v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Tambah Divisi Baru</h1>
                <p class="section-subtitle">Buat divisi baru dan tentukan supervisor bila diperlukan</p>
            </div>
        </div>
    </x-slot>

    <div class="dv-container">

        <a href="{{ route('hr.organization') }}" class="back-btn" aria-label="Kembali ke Divisi dan Jabatan">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        {{-- Flash / Error Messages --}}
        @if ($errors->any())
        <div class="dv-alert dv-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        {{-- Form Card --}}
        <div class="dv-step">
            <div class="dv-step__header">
                <span class="dv-step__num">1</span>
                <h2 class="dv-step__title">Informasi Divisi</h2>
            </div>

            <form method="POST" action="{{ route('hr.divisions.store') }}">
                @csrf

                <div class="dv-field">
                    <label for="name" class="dv-label">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Nama Divisi <span class="dv-required">*</span>
                    </label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        class="dv-input @error('name') dv-input--invalid @enderror"
                        value="{{ old('name') }}"
                        placeholder="Contoh: Finance, Marketing"
                        required>
                    @error('name')
                        <p class="dv-error-text">{{ $message }}</p>
                    @else
                        <p class="dv-helper">Nama divisi harus unik dan belum terdaftar di sistem.</p>
                    @enderror
                </div>

                <div class="dv-field">
                    <label for="supervisor" class="dv-label">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Supervisor
                    </label>
                    <div class="dv-select-wrap">
                        <select id="supervisor" name="supervisor_id" class="dv-select @error('supervisor_id') dv-input--invalid @enderror">
                            <option value="">Tidak ada / Belum ditentukan</option>
                            @foreach ($supervisors as $sup)
                            <option value="{{ $sup->id }}" @selected(old('supervisor_id') == $sup->id)>
                                {{ $sup->name }}
                            </option>
                            @endforeach
                        </select>
                        <svg class="dv-select__arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    @error('supervisor_id')
                        <p class="dv-error-text">{{ $message }}</p>
                    @else
                        <p class="dv-helper">Opsional. Anda dapat menentukannya nanti.</p>
                    @enderror
                </div>

                <div class="dv-actions">
                    <a href="{{ route('hr.organization') }}" class="dv-btn dv-btn--secondary">
                        Batal
                    </a>
                    <button type="submit" class="dv-btn dv-btn--primary">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan Divisi
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
        .dv-container {
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
        .dv-alert {
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
        .dv-alert svg { flex-shrink: 0; margin-top: 1px; }
        .dv-alert--error {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
        }

        /* ========================================== */
        /* STEP CARD                                  */
        /* ========================================== */
        .dv-step {
            background: var(--white, #fff);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 12px;
            border: 1px solid var(--border-light, #F3F4F6);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .dv-step__header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }
        .dv-step__num {
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
        .dv-step__title {
            margin: 0;
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
        }

        /* ========================================== */
        /* FIELDS                                     */
        /* ========================================== */
        .dv-field { margin-bottom: 14px; }
        .dv-field:last-of-type { margin-bottom: 0; }
        .dv-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin-bottom: 8px;
        }
        .dv-label svg { color: var(--text-light, #9CA3AF); flex-shrink: 0; }
        .dv-required { color: var(--error, #EF4444); font-weight: 700; }
        .dv-helper {
            color: var(--text-muted, #6B7280);
            font-size: 0.75rem;
            font-weight: 500;
            margin: 6px 0 0;
            line-height: 1.5;
        }
        .dv-error-text {
            color: #B91C1C;
            font-size: 0.75rem;
            font-weight: 600;
            margin: 6px 0 0;
            line-height: 1.5;
        }

        /* ========================================== */
        /* INPUT / SELECT                             */
        /* ========================================== */
        .dv-input,
        .dv-select {
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
        .dv-input::placeholder { color: var(--text-light, #9CA3AF); }
        .dv-input:focus,
        .dv-select:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .dv-input--invalid,
        .dv-input--invalid:focus {
            border-color: #EF4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.08);
        }
        .dv-select-wrap { position: relative; }
        .dv-select {
            padding-right: 40px;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
        }
        .dv-select__arrow {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light, #9CA3AF);
            pointer-events: none;
        }

        /* ========================================== */
        /* ACTIONS                                    */
        /* ========================================== */
        .dv-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-light, #F3F4F6);
        }
        .dv-btn {
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
        .dv-btn svg { width: 18px; height: 18px; flex-shrink: 0; }
        .dv-btn--primary {
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #fff;
            border: none;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
        }
        .dv-btn--primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .dv-btn--secondary {
            background: var(--white, #fff);
            color: var(--text-muted, #6B7280);
            border: 1.5px solid var(--border, #E5E7EB);
        }
        .dv-btn--secondary:hover {
            color: var(--text-primary, #111827);
            background: var(--gray-50, #F5F7FA);
        }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (max-width: 639px) {
            .dv-step {
                padding: 16px;
                margin-bottom: 10px;
                border-radius: 14px;
            }
            .dv-actions {
                flex-direction: column-reverse;
            }
            .dv-actions .dv-btn { width: 100%; }
            .back-btn {
                height: 40px;
                padding: 0 14px 0 12px;
            }
            .back-btn-text { font-size: 0.8125rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            .back-btn,
            .dv-input,
            .dv-select,
            .dv-btn { transition-duration: 0.01ms; }
            .dv-btn--primary:hover { transform: none; }
        }
    </style>
</x-app>
