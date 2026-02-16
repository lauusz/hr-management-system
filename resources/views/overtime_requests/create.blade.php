<x-app title="Form Pengajuan Lembur">
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="form-title">Formulir Pengajuan Lembur</h3>
                <p class="form-subtitle">Isi data di bawah untuk mengajukan lembur.</p>
            </div>
            <a href="{{ route('overtime-requests.index') }}" class="btn-back">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                Kembali
            </a>
        </div>

        <div class="divider"></div>

        <form action="{{ route('overtime-requests.store') }}" method="POST" class="form-content">
            @csrf
            
            {{-- 1. TANGGAL --}}
            <div class="form-group">
                <label for="overtime_date">Tanggal Lembur <span class="req">*</span></label>
                <input type="date" name="overtime_date" id="overtime_date" 
                    class="form-control @error('overtime_date') border-red-500 @enderror"
                    value="{{ old('overtime_date') }}" required>
                @error('overtime_date')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            {{-- 2. JAM MULAI & SELESAI --}}
            <div class="form-group">
                <label>Jam Lembur <span class="req">*</span></label>
                <div class="time-range-wrapper">
                    <div class="time-input-box">
                        <input type="time" name="start_time" id="start_time" 
                            class="form-control @error('start_time') border-red-500 @enderror"
                            value="{{ old('start_time') }}" required>
                        @error('start_time')
                            <div class="error-msg">{{ $message }}</div>
                        @enderror
                    </div>
                    <span class="separator">s/d</span>
                    <div class="time-input-box">
                        <input type="time" name="end_time" id="end_time"
                            class="form-control @error('end_time') border-red-500 @enderror"
                            value="{{ old('end_time') }}" required>
                        @error('end_time')
                            <div class="error-msg">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- 3. KETERANGAN --}}
            <div class="form-group">
                <label for="description">Keterangan Pekerjaan <span class="req">*</span></label>
                <textarea name="description" id="description" rows="4" 
                    class="form-control @error('description') border-red-500 @enderror"
                    placeholder="Jelaskan pekerjaan yang dilakukan..." required>{{ old('description') }}</textarea>
                @error('description')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('overtime-requests.index') }}" class="btn-cancel">Batal</a>
                <button type="submit" class="btn-submit" id="btn-submit">
                    <span id="btn-text">Ajukan Lembur</span>
                    <span id="btn-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const btnSubmit = document.getElementById('btn-submit');
            const btnText = document.getElementById('btn-text');
            const btnSpinner = document.getElementById('btn-spinner');

            if(form) {
                form.addEventListener('submit', function(e) {
                    // Prevent double submission if already submitting
                    if (btnSubmit.disabled) {
                        e.preventDefault();
                        return;
                    }

                    // Disable button and show spinner
                    btnSubmit.disabled = true;
                    btnText.innerText = 'Memproses...';
                    if(btnSpinner) btnSpinner.classList.remove('d-none');
                    
                    // Allow form to submit normally
                });
            }
        });
    </script>

    <style>
        /* Base Utils */
        .req { color: #dc2626; font-weight: bold; margin-left: 2px; }
        .error-msg { font-size: 12px; color: #dc2626; margin-top: 4px; }

        /* Card System */
        .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid #f3f4f6; overflow: hidden; max-width: 700px; margin: 0 auto; }
        .card-header { padding: 20px; display: flex; justify-content: space-between; align-items: flex-start; }
        .form-title { margin: 0; font-size: 18px; font-weight: 700; color: #111827; }
        .form-subtitle { margin: 4px 0 0; font-size: 13.5px; color: #6b7280; }
        .divider { height: 1px; background: #f3f4f6; width: 100%; }
        
        /* Buttons */
        .btn-back {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 14px; border-radius: 8px; border: 1px solid #d1d5db;
            background: #fff; color: #374151; font-size: 13px; font-weight: 500;
            text-decoration: none; transition: all 0.2s; white-space: nowrap;
        }
        .btn-back:hover { background: #f9fafb; border-color: #9ca3af; }
        
        .btn-primary {
            padding: 12px 24px; background: #1e4a8d; color: #fff; border: none; border-radius: 8px;
            font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s; width: 100%;
            display: inline-flex; justify-content: center; align-items: center; gap: 8px;
        }
        .btn-primary:hover { background: #163a75; }

        /* Form Layout */
        .form-content { padding: 24px; }
        .form-group { margin-bottom: 20px; display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13.5px; font-weight: 600; color: #374151; }

        /* Inputs */
        .form-control {
            padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;
            font-size: 14px; width: 100%; outline: none; transition: border-color 0.2s, box-shadow 0.2s;
            background: #fff; color: #111827; font-family: inherit;
        }
        .form-control:focus { border-color: #1e4a8d; box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1); }
        textarea.form-control { resize: vertical; min-height: 100px; line-height: 1.5; }

        /* Time Range */
        .time-range-wrapper { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .time-input-box { flex: 1; min-width: 120px; }
        .separator { color: #6b7280; font-size: 13px; font-weight: 500; }

        /* Actions */
        .form-actions { margin-top: 32px; padding-top: 20px; border-top: 1px solid #f3f4f6; display: flex; justify-content: flex-end; }
        .form-actions .btn-primary { width: auto; min-width: 140px; }

        @media (max-width: 600px) {
            .card-header { flex-direction: column; gap: 12px; }
            .btn-back { align-self: flex-start; }
            .form-content { padding: 16px; }
            .time-range-wrapper { gap: 8px; }
            .separator { display: none !important; }
            .time-input-box { width: 100%; flex: none; }
            .form-actions .btn-primary { width: 100%; }
        }
        .form-actions { display: flex; gap: 10px; margin-top: 10px; }

        .btn-cancel {
            flex: 1;
            padding: 12px 24px;
            background: #fff;
            color: #374151;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            height: 46px; /* Match height */
        }
        .btn-cancel:hover { background: #f9fafb; border-color: #9ca3af; }

        .btn-submit {
            flex: 2;
            padding: 12px 24px; 
            background: #1e4a8d; 
            color: #fff; 
            border: none; 
            border-radius: 8px;
            font-size: 14px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: background 0.2s; 
            width: 100%;
            display: inline-flex; 
            justify-content: center; 
            align-items: center; 
            gap: 8px;
            height: 46px;
        }
        .btn-submit:hover { background: #163a75; }
        .btn-submit:disabled { background: #93c5fd; cursor: not-allowed; }

        /* Spinner */
        .spinner-border {
            width: 1rem; height: 1rem;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner-border .75s linear infinite;
        }
        @keyframes spinner-border {
            100% { transform: rotate(360deg); }
        }
        .d-none { display: none; }
    </style>
</x-app>
