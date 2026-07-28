<x-app title="Edit Kalender Kantor">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Edit Tanggal Libur</h1>
                <p class="section-subtitle">Perbarui aturan kalender kantor global</p>
            </div>
        </div>
    </x-slot>

    <div class="holiday-edit-page">
        <a href="{{ route('hr.office-holidays.index', ['year' => $item->holiday_date->year, 'status' => 'all']) }}" class="back-btn" aria-label="Kembali ke Kalender Kantor">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali</span>
        </a>

        @if($errors->any())
            <div class="holiday-alert">{{ $errors->first() }}</div>
        @endif

        <section class="holiday-edit-card">
            <form method="POST" action="{{ route('hr.office-holidays.update', $item) }}" class="holiday-form">
                @csrf
                @method('PUT')

                <div class="holiday-field">
                    <label for="holiday_date">Tanggal Libur</label>
                    <input id="holiday_date" name="holiday_date" type="date" value="{{ old('holiday_date', $item->holiday_date->toDateString()) }}" required>
                </div>

                <div class="holiday-field">
                    <label for="name">Nama Hari Libur</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $item->name) }}" maxlength="255" required>
                </div>

                <div class="holiday-field">
                    <label for="type">Jenis</label>
                    <select id="type" name="type" required>
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', $item->type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="holiday-field">
                    <label for="notes">Catatan</label>
                    <textarea id="notes" name="notes" rows="4" maxlength="2000">{{ old('notes', $item->notes) }}</textarea>
                </div>

                <label class="holiday-check">
                    <input type="hidden" name="deducts_leave" value="0">
                    <input type="checkbox" name="deducts_leave" value="1" @checked(old('deducts_leave', $item->deducts_leave))>
                    <span><strong>Tetap memotong saldo cuti</strong><small>Tanggal mengikuti bobot normal hari kerja.</small></span>
                </label>

                <label class="holiday-check">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active))>
                    <span><strong>Aktif</strong><small>Gunakan tanggal ini dalam perhitungan cuti.</small></span>
                </label>

                <div class="holiday-actions">
                    <a href="{{ route('hr.office-holidays.index') }}" class="holiday-btn holiday-btn--secondary">Batal</a>
                    <button type="submit" class="holiday-btn holiday-btn--primary">Simpan Perubahan</button>
                </div>
            </form>
        </section>
    </div>

    <style>
        .section-header-inline { display:flex; align-items:center; gap:10px; }
        .section-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; }
        .icon-navy { background:rgba(10,61,98,.08); color:#0A3D62; }
        .section-title { margin:0; font-size:1rem; font-weight:800; color:#111827; }
        .section-subtitle { margin:0; font-size:.8125rem; color:#6B7280; font-weight:500; }
        .holiday-edit-page { max-width:720px; display:flex; flex-direction:column; gap:12px; }
        .back-btn { align-self:flex-start; min-height:40px; padding:0 13px; display:inline-flex; align-items:center; gap:7px; color:#6B7280; background:#fff; border:1px solid #E5E7EB; border-radius:10px; text-decoration:none; font-size:.8125rem; font-weight:600; }
        .holiday-alert { padding:12px 14px; border-radius:12px; background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.25); color:#B91C1C; font-size:.8125rem; font-weight:600; }
        .holiday-edit-card { background:#fff; border:1px solid #E5E7EB; border-radius:16px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,.04); }
        .holiday-form { display:grid; gap:15px; }
        .holiday-field { display:flex; flex-direction:column; gap:7px; }
        .holiday-field label { font-size:.75rem; font-weight:700; color:#374151; }
        .holiday-field input, .holiday-field select, .holiday-field textarea { width:100%; min-height:48px; padding:0 14px; border:1.5px solid #E5E7EB; border-radius:10px; box-sizing:border-box; font:500 .875rem 'Plus Jakarta Sans', sans-serif; color:#111827; background:#fff; }
        .holiday-field textarea { padding:12px 14px; resize:vertical; }
        .holiday-field input:focus, .holiday-field select:focus, .holiday-field textarea:focus { outline:none; border-color:#145DA0; box-shadow:0 0 0 4px rgba(20,93,160,.1); }
        .holiday-check { display:flex; gap:10px; padding:12px; border:1px solid #E5E7EB; border-radius:10px; cursor:pointer; }
        .holiday-check input[type="checkbox"] { width:18px; height:18px; accent-color:#145DA0; }
        .holiday-check span { display:flex; flex-direction:column; gap:3px; }
        .holiday-check strong { color:#374151; font-size:.8125rem; }
        .holiday-check small { color:#6B7280; font-size:.6875rem; }
        .holiday-actions { display:flex; flex-direction:column-reverse; gap:9px; padding-top:4px; }
        .holiday-btn { min-height:46px; padding:0 17px; border-radius:10px; font:700 .8125rem 'Plus Jakarta Sans', sans-serif; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; }
        .holiday-btn--primary { border:0; color:#fff; background:linear-gradient(135deg,#0A3D62,#145DA0); }
        .holiday-btn--secondary { color:#374151; background:#fff; border:1.5px solid #E5E7EB; }
        @media (min-width:640px) {
            .holiday-edit-card { padding:22px; }
            .holiday-actions { flex-direction:row; justify-content:flex-end; }
        }
    </style>
</x-app>
