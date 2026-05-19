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

    <form action="{{ route('overtime-requests.store') }}" method="POST">
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
                <input type="date" name="overtime_date" id="overtime_date"
                    class="otc-input @error('overtime_date') otc-input--error @enderror"
                    value="{{ old('overtime_date') }}" required>
                @error('overtime_date')
                    <div class="otc-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="otc-field">
                <label class="otc-label">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Jam Lembur <span class="otc-required">*</span>
                </label>
                <div class="otc-time-grid">
                    <div>
                        <div class="otc-input-wrap">
                            <svg class="otc-input__icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <input type="time" name="start_time" id="start_time"
                                class="otc-input otc-input--icon @error('start_time') otc-input--error @enderror"
                                value="{{ old('start_time') }}" required>
                        </div>
                        @error('start_time')
                            <div class="otc-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <div class="otc-input-wrap">
                            <svg class="otc-input__icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <input type="time" name="end_time" id="end_time"
                                class="otc-input otc-input--icon @error('end_time') otc-input--error @enderror"
                                value="{{ old('end_time') }}" required>
                        </div>
                        @error('end_time')
                            <div class="otc-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="otc-step">
            <div class="otc-step__header">
                <span class="otc-step__num">2</span>
                <span class="otc-step__title">Keterangan</span>
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

            if (form) {
                form.addEventListener('submit', function(e) {
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

    <style>
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
</x-app>
