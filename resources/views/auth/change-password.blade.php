<x-app title="Ganti Password">

    <div class="page-wrapper">
        {{-- Header --}}
        <div class="page-header">
            <a href="{{ route('dashboard') }}" class="back-btn">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div class="header-text">
                <h1 class="page-title">Pengaturan Keamanan</h1>
                <p class="page-subtitle">Kelola kata sandi akun Anda</p>
            </div>
        </div>

        {{-- Alert --}}
        @if(session('success'))
            <div class="alert alert-success">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        {{-- Main Card --}}
        <div class="card-security">
            <div class="card-icon">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h2 class="card-title">Ganti Password</h2>
            <p class="card-desc">Pastikan password baru Anda berbeda dan mudah diingat.</p>

            <form method="POST" action="{{ route('settings.password.update') }}" class="security-form">
                @csrf
                @method('PUT')

                <div class="form-field">
                    <label for="current_password" class="field-label">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        Password Saat Ini
                    </label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            name="current_password"
                            id="current_password"
                            class="field-input"
                            placeholder="Masukkan password lama"
                            required
                            autocomplete="current-password">
                        <button type="button" class="toggle-password" data-target="current_password">
                            <svg class="icon-eye" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <div class="form-field">
                    <label for="password" class="field-label">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Password Baru
                    </label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="field-input"
                            placeholder="Minimal 8 karakter"
                            required
                            autocomplete="new-password">
                        <button type="button" class="toggle-password" data-target="password">
                            <svg class="icon-eye" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <div class="form-field">
                    <label for="password_confirmation" class="field-label">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Konfirmasi Password Baru
                    </label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            class="field-input"
                            placeholder="Ulangi password baru"
                            required
                            autocomplete="new-password">
                        <button type="button" class="toggle-password" data-target="password_confirmation">
                            <svg class="icon-eye" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <div class="password-rules">
                    <div class="rule-title">Persyaratan Password:</div>
                    <ul class="rule-list">
                        <li data-rule="length">Minimal 8 karakter</li>
                        <li data-rule="match">Password baru harus sama</li>
                    </ul>
                </div>

                <button type="submit" class="btn-submit">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Password
                </button>
            </form>
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
                    } else {
                        input.type = 'password';
                        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
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
        });
    </script>

    <style>
        :root {
            --navy: #1e4a8d;
            --navy-dark: #163a75;
            --bg-page: #f8fafc;
            --white: #ffffff;
            --border: #e5e7eb;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
        }

        * { box-sizing: border-box; }

        .page-wrapper {
            max-width: 480px;
            margin: 0 auto;
            padding: 24px 16px 48px;
        }

        /* Header */
        .page-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .back-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .back-btn:hover {
            border-color: var(--navy);
            color: var(--navy);
        }

        .back-btn:hover svg {
            transform: translateX(-2px);
        }

        .back-btn svg {
            transition: transform 0.2s;
        }

        .header-text { flex: 1; }

        .page-title {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .page-subtitle {
            margin: 2px 0 0;
            font-size: 13px;
            color: var(--text-secondary);
        }

        /* Alerts */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.4;
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        /* Card */
        .card-security {
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--border);
            padding: 32px 28px;
            text-align: center;
        }

        .card-icon {
            width: 64px;
            height: 64px;
            background: #eff6ff;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--navy);
        }

        .card-title {
            margin: 0 0 8px;
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .card-desc {
            margin: 0 0 28px;
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        /* Form */
        .security-form {
            text-align: left;
        }

        .form-field {
            margin-bottom: 20px;
        }

        .field-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .field-label svg {
            color: var(--navy);
        }

        .input-wrapper {
            position: relative;
        }

        .field-input {
            width: 100%;
            padding: 12px 44px 12px 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            color: var(--text-primary);
            background: var(--white);
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: inherit;
        }

        .field-input:focus {
            outline: none;
            border-color: var(--navy);
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
        }

        .field-input::placeholder {
            color: var(--text-muted);
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password:hover {
            color: var(--navy);
        }

        /* Password Rules */
        .password-rules {
            background: #f9fafb;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 24px;
        }

        .rule-title {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .rule-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .rule-list li {
            font-size: 13px;
            color: var(--text-muted);
            padding: 3px 0 3px 22px;
            position: relative;
            transition: color 0.2s;
        }

        .rule-list li::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 14px;
            height: 14px;
            border: 1px solid var(--border);
            border-radius: 4px;
            transition: all 0.2s;
        }

        .rule-list li.met {
            color: #065f46;
        }

        .rule-list li.met::before {
            background: #059669;
            border-color: #059669;
        }

        .rule-list li.met::after {
            content: '';
            position: absolute;
            left: 3px;
            top: 50%;
            transform: translateY(-60%) rotate(45deg);
            width: 5px;
            height: 9px;
            border: 2px solid #fff;
            border-top: none;
            border-left: none;
        }

        .rule-list li.active {
            color: #dc2626;
        }

        .rule-list li.active::before {
            border-color: #dc2626;
        }

        /* Submit Button */
        .btn-submit {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 14px 24px;
            background: var(--navy);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            font-family: inherit;
        }

        .btn-submit:hover {
            background: var(--navy-dark);
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        /* Responsive */
        @media (max-width: 480px) {
            .page-wrapper {
                padding: 16px 12px 32px;
            }

            .card-security {
                padding: 24px 20px;
                border-radius: 14px;
            }

            .page-header {
                gap: 12px;
            }

            .back-btn {
                width: 36px;
                height: 36px;
            }

            .page-title {
                font-size: 18px;
            }

            .card-icon {
                width: 56px;
                height: 56px;
            }

            .card-icon svg {
                width: 28px;
                height: 28px;
            }
        }

        @media (min-width: 640px) {
            .page-wrapper {
                padding: 32px 24px 64px;
            }

            .card-security {
                padding: 40px 36px;
            }
        }
    </style>

</x-app>
