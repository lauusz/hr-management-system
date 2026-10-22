<x-app title="Ubah Pengajuan Lembur">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Ubah Pengajuan Lembur</h1>
                <p class="section-subtitle">Ubah data pengajuan lembur yang masih menunggu persetujuan SPV</p>
            </div>
        </div>
    </x-slot>

    <a href="{{ route('overtime-requests.show', $overtime) }}" class="back-btn" aria-label="Kembali ke detail pengajuan lembur">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span class="back-btn-text">Kembali</span>
    </a>

    @if (session('error'))
        <div class="otc-alert otc-alert--error">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="otc-alert otc-alert--error">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('overtime-requests.update', $overtime) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="otc-step">
            <div class="otc-step__header">
                <span class="otc-step__num">1</span>
                <span class="otc-step__title">Tanggal & Waktu</span>
            </div>

            <div class="otc-field">
                <label for="overtime_date" class="otc-label">Tanggal Lembur <span class="otc-required">*</span></label>
                <input type="date" name="overtime_date" id="overtime_date"
                    class="otc-input @error('overtime_date') otc-input--error @enderror"
                    value="{{ old('overtime_date', $overtime->overtime_date->format('Y-m-d')) }}" required>
            </div>

            <div class="otc-field">
                <label class="otc-label">Jam Lembur <span class="otc-required">*</span></label>
                <div class="otc-time-grid">
                    <div>
                        <label class="otc-time-field-label" for="start_time">Jam Mulai Lembur</label>
                        <input type="time" name="start_time" id="start_time"
                            class="otc-input @error('start_time') otc-input--error @enderror"
                            value="{{ old('start_time', $overtime->start_time->format('H:i')) }}" required>
                    </div>
                    <div>
                        <label class="otc-time-field-label" for="end_time">Jam Selesai Lembur</label>
                        <input type="time" name="end_time" id="end_time"
                            class="otc-input @error('end_time') otc-input--error @enderror"
                            value="{{ old('end_time', $overtime->end_time->format('H:i')) }}" required>
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
                <label for="description" class="otc-label">Keterangan Pekerjaan <span class="otc-required">*</span></label>
                <textarea name="description" id="description" rows="5"
                    class="otc-textarea @error('description') otc-input--error @enderror"
                    required>{{ old('description', $overtime->description) }}</textarea>
            </div>
        </div>

        <div class="otc-submit-wrap">
            <a href="{{ route('overtime-requests.show', $overtime) }}" class="otc-btn-secondary">Batal</a>
            <button type="submit" class="otc-btn-submit">Simpan Perubahan</button>
        </div>
    </form>

    <style>
        .section-header-inline { display:flex; align-items:center; gap:10px; }
        .section-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .icon-navy { background:rgba(10,61,98,.08); color:var(--primary-dark); }
        .section-title { margin:0; font-size:1rem; font-weight:800; color:var(--text-primary); letter-spacing:-.01em; line-height:1.25; }
        .section-subtitle { margin:0; font-size:.8125rem; color:var(--text-muted); font-weight:500; line-height:1.35; }
        .back-btn { display:inline-flex; align-items:center; gap:6px; height:36px; margin-bottom:16px; padding:0 12px 0 10px; background:var(--white); border:1px solid var(--border); border-radius:10px; color:var(--text-muted); text-decoration:none; transition:all .15s ease; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .back-btn:hover { border-color:var(--primary); color:var(--primary); background:var(--gray-50); }
        .back-btn:hover svg { transform:translateX(-2px); }
        .back-btn svg { transition:transform .2s ease; }
        .back-btn-text { font-size:.75rem; font-weight:600; }
        .otc-alert { margin-bottom:16px; padding:12px 16px; border-radius:12px; font-size:.8125rem; font-weight:500; }
        .otc-alert--error { background:rgba(239,68,68,.06); border:1px solid rgba(239,68,68,.18); color:#b91c1c; }
        .otc-step { margin-bottom:12px; padding:20px; background:var(--white); border:1px solid var(--border-light); border-radius:16px; box-shadow:0 1px 3px rgba(0,0,0,.04); }
        .otc-step__header { display:flex; align-items:center; gap:10px; margin-bottom:16px; }
        .otc-step__num { display:flex; width:26px; height:26px; align-items:center; justify-content:center; border-radius:50%; background:var(--primary-dark); color:#fff; font-size:.75rem; font-weight:700; }
        .otc-step__title { color:var(--text-primary); font-size:.9375rem; font-weight:700; }
        .otc-field { margin-bottom:16px; }
        .otc-field:last-child { margin-bottom:0; }
        .otc-label, .otc-time-field-label { display:block; margin-bottom:8px; color:var(--text-secondary); font-size:.8125rem; font-weight:600; }
        .otc-required { color:var(--error); font-weight:700; }
        .otc-input, .otc-textarea { box-sizing:border-box; width:100%; border:1.5px solid var(--border); border-radius:10px; padding:12px 14px; outline:none; background:var(--white); color:var(--text-primary); font:inherit; transition:all .2s ease; }
        .otc-textarea { min-height:120px; resize:vertical; line-height:1.5; }
        .otc-input:focus, .otc-textarea:focus { border-color:var(--primary); box-shadow:0 0 0 4px rgba(20,93,160,.1); }
        .otc-input--error { border-color:var(--error); }
        .otc-time-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .otc-submit-wrap { display:flex; gap:10px; margin-top:20px; padding-top:16px; border-top:1px solid var(--border-light); }
        .otc-btn-submit, .otc-btn-secondary { display:inline-flex; flex:1; align-items:center; justify-content:center; border-radius:12px; padding:12px 20px; font:inherit; font-size:.875rem; font-weight:600; text-decoration:none; cursor:pointer; }
        .otc-btn-submit { border:0; background:linear-gradient(135deg,var(--primary-dark),var(--primary)); color:#fff; box-shadow:0 4px 12px rgba(10,61,98,.22); }
        .otc-btn-secondary { border:1.5px solid var(--border); background:var(--white); color:var(--text-secondary); }
        @media (min-width:480px) { .back-btn { height:40px; padding:0 14px 0 12px; } .back-btn-text { font-size:.8125rem; } .otc-btn-submit { flex:2; } }
        @media (max-width:480px) { .otc-time-grid, .otc-submit-wrap { grid-template-columns:1fr; flex-direction:column; } .otc-step { padding:16px; } }
    </style>
</x-app>
