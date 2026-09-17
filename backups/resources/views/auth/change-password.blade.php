<x-app title="Ganti Password">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Pengaturan Keamanan</h1>
                <p class="section-subtitle">Kelola kata sandi akun Anda</p>
            </div>
        </div>
    </x-slot>

    <div class="security-page">
        {{-- Back Button --}}
        <a href="{{ route('dashboard') }}" class="back-btn" aria-label="Kembali ke dashboard">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        {{-- Main Layout: Form + Side Panel --}}
        <div class="security-layout">
            {{-- Form Card --}}
            <div class="security-card">
                <form method="POST" action="{{ route('settings.password.update') }}" class="security-form" id="passwordForm">
                    @csrf
                    @method('PUT')

                    {{-- Identity Section --}}
                    <div class="form-section">
                        <div class="field">
                            <label class="field-label" for="username">Username</label>
                            <div class="input-wrap">
                                <input
                                    type="text"
                                    name="username"
                                    id="username"
                                    class="field-input @error('username') input-error @enderror"
                                    value="{{ old('username', auth()->user()->username ?? '') }}"
                                    placeholder="Masukkan username baru"
                                    autocomplete="username">
                            </div>
                            <p class="field-hint">Tidak boleh ada spasi. Username bisa digunakan untuk login.</p>
                            @error('username')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="field-label" for="email">Email</label>
                            <div class="input-wrap">
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    class="field-input field-input--readonly"
                                    value="{{ auth()->user()->email ?? '' }}"
                                    readonly
                                    autocomplete="email">

                            </div>
                            <p class="field-hint">Hubungi HRD jika perlu mengubah alamat email.</p>
                        </div>
                    </div>

                    {{-- Password Section --}}
                    <div class="form-section">
                        <div class="field">
                            <label class="field-label field-label--spaced" for="current_password">Password Saat Ini</label>
                            <div class="input-wrap">
                                <input
                                    type="password"
                                    name="current_password"
                                    id="current_password"
                                    class="field-input @error('current_password') input-error @enderror"
                                    placeholder="Masukkan password lama"
                                    required
                                    autocomplete="current-password">
                                <button type="button" class="toggle-password" data-target="current_password" aria-label="Tampilkan password">
                                    <svg class="icon-eye" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="field-label" for="password">Password Baru</label>
                            <div class="input-wrap">
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="field-input @error('password') input-error @enderror"
                                    placeholder="Minimal 8 karakter"
                                    required
                                    autocomplete="new-password">
                                <button type="button" class="toggle-password" data-target="password" aria-label="Tampilkan password">
                                    <svg class="icon-eye" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="field-label" for="password_confirmation">Konfirmasi Password Baru</label>
                            <div class="input-wrap">
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    id="password_confirmation"
                                    class="field-input"
                                    placeholder="Ulangi password baru"
                                    required
                                    autocomplete="new-password">
                                <button type="button" class="toggle-password" data-target="password_confirmation" aria-label="Tampilkan password">
                                    <svg class="icon-eye" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="password-rules">
                            <div class="rule-title">Persyaratan Password</div>
                            <ul class="rule-list">
                                <li data-rule="length">
                                    <span class="rule-check"></span>
                                    <span>Minimal 8 karakter</span>
                                </li>
                                <li data-rule="match">
                                    <span class="rule-check"></span>
                                    <span>Password baru harus sama</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit" id="btnSubmit">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="btn-text">Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>


        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            document.querySelectorAll('.toggle-password').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('.icon-eye');

                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 19.02A8.001 8.001 0 017.875 5.875M6.125 4.98A8.001 8.001 0 0112 3c4.478 0 8.268 2.943 9.542 7a8.001 8.001 0 01-2.125 4.02M6.125 4.98A8.001 8.001 0 0112 3"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"/>';
                        this.setAttribute('aria-label', 'Sembunyikan password');
                    } else {
                        input.type = 'password';
                        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
                        this.setAttribute('aria-label', 'Tampilkan password');
                    }
                });
            });

            // Password validation feedback
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('password_confirmation');
            const rules = document.querySelectorAll('.rule-list li');

            function checkPassword() {
                const pass = passwordInput.value;
                const confirm = confirmInput.value;

                rules.forEach(function(rule) {
                    const ruleType = rule.getAttribute('data-rule');
                    rule.classList.remove('active', 'met');

                    if (ruleType === 'length') {
                        if (pass.length >= 8) {
                            rule.classList.add('met');
                        }
                    }

                    if (ruleType === 'match') {
                        if (pass.length > 0 && confirm.length > 0 && pass === confirm) {
                            rule.classList.add('met');
                        } else if (pass.length > 0 && confirm.length > 0) {
                            rule.classList.add('active');
                        }
                    }
                });
            }

            passwordInput.addEventListener('input', checkPassword);
            confirmInput.addEventListener('input', checkPassword);

            // Loading state on submit
            const form = document.getElementById('passwordForm');
            const btnSubmit = document.getElementById('btnSubmit');
            if (form && btnSubmit) {
                form.addEventListener('submit', function() {
                    btnSubmit.disabled = true;
                    btnSubmit.classList.add('is-loading');
                });
            }
        });
    </script>

    <style>
        /* =============================================
           SECURITY PAGE ROOT
           ============================================= */
        .security-page {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding-bottom: 24px;
        }

        /* =============================================
           PAGE HEADER
           ============================================= */
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
            align-self: flex-start;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
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

        /* =============================================
           ALERTS
           ============================================= */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 0.8125rem;
            font-weight: 600;
            line-height: 1.4;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.2);
            color: #15803d;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.06);
            border: 1px solid rgba(239, 68, 68, 0.15);
            color: #b91c1c;
        }

        .alert svg {
            flex-shrink: 0;
            width: 18px;
            height: 18px;
        }

        /* =============================================
           LAYOUT: Form + Side Panel
           ============================================= */
        .security-layout {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .security-card {
            background: var(--white, #fff);
            border: 1px solid var(--border, #E5E7EB);
            border-radius: var(--radius-xl, 16px);
            padding: 20px 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        /* =============================================
           FORM SECTIONS
           ============================================= */
        .form-section {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

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

        /* =============================================
           FORM FIELDS (matched with login page)
           ============================================= */
        .field {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .field-label {
            font-size: 0.6875rem;
            font-weight: 700;
            color: var(--text-secondary, #374151);
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .field-label--spaced {
            margin-top: 2px;
        }

        .input-wrap {
            position: relative;
        }

        .field-input {
            width: 100%;
            height: 46px;
            padding: 0 44px 0 14px;
            border: 1.5px solid var(--border, #E5E7EB);
            border-radius: 10px;
            background: var(--white, #fff);
            font-family: inherit;
            font-size: 0.9375rem;
            color: var(--text-primary, #111827);
            transition: all 0.2s ease;
            -webkit-appearance: none;
            appearance: none;
        }

        .field-input::placeholder {
            color: var(--text-muted, #6B7280);
            opacity: 1;
        }

        .field-input:focus {
            outline: none;
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1), 0 0 0 1px rgba(212, 175, 55, 0.15);
        }

        .field-input:hover:not(:focus):not(.field-input--readonly) {
            border-color: #D1D5DB;
        }

        .field-input.input-error {
            border-color: var(--error, #EF4444);
        }

        .field-input.input-error:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        .field-input--readonly {
            background: var(--gray-50, #F5F7FA);
            color: var(--text-muted, #6B7280);
            cursor: not-allowed;
            padding-right: 14px;
        }

        .field-hint {
            margin: 0;
            font-size: 0.6875rem;
            color: var(--text-muted, #6B7280);
            font-weight: 500;
            line-height: 1.45;
        }

        .error-text {
            margin: 0;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--error, #EF4444);
            line-height: 1.4;
        }

        /* =============================================
           PASSWORD TOGGLE
           ============================================= */
        .toggle-password {
            position: absolute;
            right: 4px;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            cursor: pointer;
            color: var(--text-muted, #6B7280);
            border-radius: 8px;
            transition: all 0.15s ease;
        }

        .toggle-password:hover {
            color: var(--text-secondary, #374151);
            background: var(--gray-50, #F5F7FA);
        }

        .toggle-password:focus-visible {
            outline: 2px solid var(--primary, #145DA0);
            outline-offset: -2px;
        }

        .toggle-password svg {
            width: 18px;
            height: 18px;
        }

        /* =============================================
           PASSWORD RULES
           ============================================= */
        .password-rules {
            background: var(--gray-50, #F5F7FA);
            border-radius: 10px;
            padding: 12px 14px;
        }

        .rule-title {
            font-size: 0.625rem;
            font-weight: 700;
            color: var(--text-muted, #6B7280);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .rule-list {
            margin: 0;
            padding: 0;
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .rule-list li {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .rule-check {
            width: 16px;
            height: 16px;
            min-width: 16px;
            border-radius: 5px;
            border: 1.5px solid var(--border, #E5E7EB);
            background: var(--white, #fff);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .rule-check::after {
            content: '';
            width: 5px;
            height: 8px;
            border: 2px solid transparent;
            border-top: none;
            border-left: none;
            transform: rotate(45deg) translateY(-1px);
            transition: all 0.2s ease;
        }

        .rule-list li.met {
            color: #15803d;
        }

        .rule-list li.met .rule-check {
            background: var(--success, #22C55E);
            border-color: var(--success, #22C55E);
        }

        .rule-list li.met .rule-check::after {
            border-color: #fff;
        }

        .rule-list li.active {
            color: var(--error, #EF4444);
        }

        .rule-list li.active .rule-check {
            border-color: var(--error, #EF4444);
            background: rgba(239, 68, 68, 0.06);
        }

        /* =============================================
           FORM ACTIONS
           ============================================= */
        .form-actions {
            margin-top: 6px;
        }

        .btn-submit {
            width: 100%;
            height: 46px;
            border-radius: 10px;
            border: none;
            color: var(--white, #fff);
            font-family: inherit;
            font-weight: 700;
            font-size: 0.875rem;
            cursor: pointer;
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            box-shadow: 0 3px 10px rgba(10, 61, 98, 0.22);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
        }

        .btn-submit:hover:not(:disabled) {
            background: linear-gradient(135deg, #082D4A, var(--primary-dark, #0A3D62));
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.38);
            transform: translateY(-1px);
        }

        .btn-submit:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(10, 61, 98, 0.28);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-submit.is-loading .btn-text {
            opacity: 0.85;
        }

        .btn-text {
            transition: opacity 0.2s ease;
        }

        /* =============================================
           ICON COLORS
           ============================================= */
        .icon-navy  { background: rgba(10, 61, 98, 0.08);  color: var(--primary-dark, #0A3D62); }
        .icon-blue  { background: rgba(20, 93, 160, 0.08);  color: var(--primary, #145DA0); }

        /* =============================================
           RESPONSIVE
           ============================================= */
        @media (min-width: 480px) {
            .security-page {
                gap: 12px;
                padding-bottom: 32px;
            }

            .back-btn {
                height: 40px;
                padding: 0 14px 0 12px;
            }

            .back-btn-text {
                font-size: 0.8125rem;
            }

            .security-card {
                padding: 24px 24px;
                border-radius: var(--radius-2xl, 20px);
            }

            .field-input {
                height: 50px;
                padding: 0 48px 0 16px;
                border-radius: 12px;
            }

            .toggle-password {
                width: 44px;
                height: 44px;
                right: 6px;
            }

            .btn-submit {
                height: 50px;
                border-radius: 12px;
                font-size: 0.9375rem;
            }

            .field-hint {
                font-size: 0.625rem;
            }
        }

        @media (min-width: 768px) {
            .security-card {
                padding: 28px 32px;
            }

            .form-section {
                gap: 12px;
            }
        }

        @media (min-width: 1024px) {
            .security-card {
                padding: 32px 36px;
            }
        }
    </style>

</x-app>
