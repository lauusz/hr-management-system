<x-app title="Pengaturan Akun">

    <div class="account-container">
        
        @if(session('success'))
            <div class="alert-success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h2 class="form-title">Ganti Password</h2>
                <p class="form-subtitle">Amankan akun Anda dengan memperbarui kata sandi secara berkala.</p>
            </div>

            <div class="divider"></div>

            <div class="form-content">
                <form method="POST" action="{{ route('settings.password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="current_password">Password Saat Ini</label>
                        <input 
                            type="password" 
                            name="current_password" 
                            id="current_password" 
                            class="form-control"
                            placeholder="Masukkan password lama"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            class="form-control"
                            placeholder="Minimal 8 karakter"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password Baru</label>
                        <input 
                            type="password" 
                            name="password_confirmation" 
                            id="password_confirmation"
                            class="form-control"
                            placeholder="Ulangi password baru"
                            required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            Simpan Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* Container Layout */
        .account-container {
            max-width: 500px;
            margin: 0 auto;
            padding-bottom: 40px;
        }

        /* Card System */
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            border: 1px solid #f3f4f6;
            overflow: hidden;
        }

        .card-header {
            padding: 24px 24px 16px 24px;
        }

        .form-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #111827;
        }

        .form-subtitle {
            margin: 6px 0 0 0;
            font-size: 13.5px;
            color: #6b7280;
            line-height: 1.5;
        }

        .divider {
            height: 1px;
            background: #f3f4f6;
            width: 100%;
        }

        .form-content {
            padding: 24px;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 13.5px;
            font-weight: 600;
            color: #374151;
        }

        .form-control {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            width: 100%;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fff;
            color: #111827;
            font-family: inherit;
        }

        .form-control:focus {
            border-color: #1e4a8d;
            box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1);
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        /* Buttons */
        .form-actions {
            margin-top: 8px;
        }

        .btn-primary {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 12px 24px;
            background: #1e4a8d;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-primary:hover {
            background: #163a75;
        }

        /* Alerts */
        .alert-success, .alert-error {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
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

        /* Mobile Adjustments */
        @media (max-width: 600px) {
            .account-container {
                padding-left: 0;
                padding-right: 0;
            }
            
            .card {
                border-radius: 0; /* Full width on very small screens feels more native */
                border-left: none;
                border-right: none;
                box-shadow: none;
            }
            
            /* Restore border radius if screen is slightly larger than phone but smaller than desktop */
            @media (min-width: 400px) {
                .card {
                    border-radius: 12px;
                    border: 1px solid #f3f4f6;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
                }
            }

            .form-content {
                padding: 20px;
            }
        }
    </style>
</x-app>