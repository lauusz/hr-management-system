<x-app title="Tambah PT">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4a2 2 0 012-2h2a2 2 0 012 2v4"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Tambah PT Baru</h1>
                <p class="section-subtitle">Daftarkan entitas perusahaan baru untuk keperluan data karyawan</p>
            </div>
        </div>
    </x-slot>

    <div class="ptc-container">

        <a href="{{ route('hr.pts.index') }}" class="back-btn" aria-label="Kembali ke Master PT">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        {{-- Flash / Error Messages --}}
        @if ($errors->any())
        <div class="ptc-alert ptc-alert--error">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        {{-- Form Card --}}
        <div class="ptc-step">
            <div class="ptc-step__header">
                <span class="ptc-step__num">1</span>
                <h2 class="ptc-step__title">Informasi Perusahaan</h2>
            </div>

            <form method="POST" action="{{ route('hr.pts.store') }}">
                @csrf

                <div class="ptc-field">
                    <label for="name" class="ptc-label">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4a2 2 0 012-2h2a2 2 0 012 2v4"/>
                        </svg>
                        Nama Perusahaan / PT <span class="ptc-required">*</span>
                    </label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        class="ptc-input @error('name') ptc-input--invalid @enderror"
                        value="{{ old('name') }}"
                        placeholder="Contoh: PT TRIGUNA SAMUDRATRANS"
                        required>
                    @error('name')
                        <p class="ptc-error-text">{{ $message }}</p>
                    @else
                        <p class="ptc-helper">Nama PT harus unik dan belum terdaftar di sistem (maksimal 150 karakter).</p>
                    @enderror
                </div>

                <div class="ptc-actions">
                    <a href="{{ route('hr.pts.index') }}" class="ptc-btn ptc-btn--secondary">
                        Batal
                    </a>
                    <button type="submit" class="ptc-btn ptc-btn--primary">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan PT
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
        .ptc-container {
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
        .ptc-alert {
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
        .ptc-alert svg { flex-shrink: 0; margin-top: 1px; }
        .ptc-alert--error {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
        }

        /* ========================================== */
        /* STEP CARD                                  */
        /* ========================================== */
        .ptc-step {
            background: var(--white, #fff);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 12px;
            border: 1px solid var(--border-light, #F3F4F6);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .ptc-step__header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }
        .ptc-step__num {
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
        .ptc-step__title {
            margin: 0;
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
        }

        /* ========================================== */
        /* FIELDS                                     */
        /* ========================================== */
        .ptc-field { margin-bottom: 14px; }
        .ptc-field:last-of-type { margin-bottom: 0; }
        .ptc-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin-bottom: 8px;
        }
        .ptc-label svg { color: var(--text-light, #9CA3AF); flex-shrink: 0; }
        .ptc-required { color: var(--error, #EF4444); font-weight: 700; }
        .ptc-helper {
            color: var(--text-muted, #6B7280);
            font-size: 0.75rem;
            font-weight: 500;
            margin: 6px 0 0;
            line-height: 1.5;
        }
        .ptc-error-text {
            color: #B91C1C;
            font-size: 0.75rem;
            font-weight: 600;
            margin: 6px 0 0;
            line-height: 1.5;
        }

        /* ========================================== */
        /* INPUT                                      */
        /* ========================================== */
        .ptc-input {
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
        .ptc-input::placeholder { color: var(--text-light, #9CA3AF); }
        .ptc-input:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .ptc-input--invalid,
        .ptc-input--invalid:focus {
            border-color: #EF4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.08);
        }

        /* ========================================== */
        /* ACTIONS                                    */
        /* ========================================== */
        .ptc-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-light, #F3F4F6);
        }
        .ptc-btn {
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
        .ptc-btn svg { width: 18px; height: 18px; flex-shrink: 0; }
        .ptc-btn--primary {
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #fff;
            border: none;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
        }
        .ptc-btn--primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .ptc-btn--secondary {
            background: var(--white, #fff);
            color: var(--text-muted, #6B7280);
            border: 1.5px solid var(--border, #E5E7EB);
        }
        .ptc-btn--secondary:hover {
            color: var(--text-primary, #111827);
            background: var(--gray-50, #F5F7FA);
        }

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
        @media (max-width: 639px) {
            .ptc-step {
                padding: 16px;
                margin-bottom: 10px;
                border-radius: 14px;
            }
            .ptc-actions {
                flex-direction: column-reverse;
            }
            .ptc-actions .ptc-btn { width: 100%; }
            .back-btn {
                height: 40px;
                padding: 0 14px 0 12px;
            }
            .back-btn-text { font-size: 0.8125rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            .back-btn,
            .ptc-input,
            .ptc-btn { transition-duration: 0.01ms; }
            .ptc-btn--primary:hover { transform: none; }
        }
    </style>
</x-app>
