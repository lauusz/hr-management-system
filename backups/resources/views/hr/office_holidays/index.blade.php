<x-app title="Kalender Kantor">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Kalender Kantor</h1>
                <p class="section-subtitle">Atur tanggal libur global untuk perhitungan cuti seluruh PT</p>
            </div>
        </div>
    </x-slot>

    <div class="holiday-page">
        @if(session('success'))
            <div class="holiday-alert holiday-alert--success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="holiday-alert holiday-alert--error">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="holiday-alert holiday-alert--error">{{ $errors->first() }}</div>
        @endif

        <section class="holiday-card">
            <div class="holiday-card__header">
                <div>
                    <h2>Tambah Tanggal Libur</h2>
                    <p>Satu tanggal hanya dapat memiliki satu aturan kalender aktif.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('hr.office-holidays.store') }}" class="holiday-form">
                @csrf

                <div class="holiday-field">
                    <label for="holiday_date">Tanggal Libur</label>
                    <input id="holiday_date" name="holiday_date" type="date" value="{{ old('holiday_date') }}" required>
                </div>

                <div class="holiday-field holiday-field--wide">
                    <label for="name">Nama Hari Libur</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Contoh: Tahun Baru Islam" maxlength="255" required>
                </div>

                <div class="holiday-field">
                    <label for="type">Jenis</label>
                    <select id="type" name="type" required>
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', \App\Models\OfficeHoliday::TYPE_NATIONAL) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="holiday-field holiday-field--full">
                    <label for="notes">Catatan</label>
                    <textarea id="notes" name="notes" rows="3" maxlength="2000" placeholder="Catatan tambahan (opsional)">{{ old('notes') }}</textarea>
                </div>

                <div class="holiday-options holiday-field--full">
                    <label class="holiday-check">
                        <input type="hidden" name="deducts_leave" value="0">
                        <input type="checkbox" name="deducts_leave" value="1" @checked(old('deducts_leave'))>
                        <span>
                            <strong>Tetap memotong saldo cuti</strong>
                            <small>Jika aktif, tanggal mengikuti bobot normal hari kerja.</small>
                        </span>
                    </label>

                    <label class="holiday-check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                        <span>
                            <strong>Aktif</strong>
                            <small>Hanya tanggal aktif yang memengaruhi perhitungan cuti.</small>
                        </span>
                    </label>
                </div>

                <div class="holiday-actions holiday-field--full">
                    <button type="submit" class="holiday-btn holiday-btn--primary">Simpan Tanggal Libur</button>
                </div>
            </form>
        </section>

        <section class="holiday-card">
            <div class="holiday-list-header">
                <div>
                    <h2>Daftar Tanggal Libur</h2>
                    <p>{{ $items->total() }} tanggal ditemukan pada tahun {{ $year }}.</p>
                </div>

                <form method="GET" action="{{ route('hr.office-holidays.index') }}" class="holiday-filter">
                    <select name="year" aria-label="Filter tahun">
                        @foreach($years as $yearOption)
                            <option value="{{ $yearOption }}" @selected((int) $yearOption === $year)>{{ $yearOption }}</option>
                        @endforeach
                    </select>
                    <select name="status" aria-label="Filter status">
                        <option value="active" @selected($status === 'active')>Aktif</option>
                        <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
                        <option value="all" @selected($status === 'all')>Semua</option>
                    </select>
                    <button type="submit" class="holiday-btn holiday-btn--secondary">Terapkan</button>
                </form>
            </div>

            @if($items->isEmpty())
                <div class="holiday-empty">
                    <strong>Belum ada tanggal libur</strong>
                    <p>Tambahkan tanggal melalui formulir di atas.</p>
                </div>
            @else
                <div class="holiday-table-wrap">
                    <table class="holiday-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Aturan Cuti</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->holiday_date->translatedFormat('d M Y') }}</strong>
                                        <small>{{ $item->holiday_date->translatedFormat('l') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $item->name }}</strong>
                                        <small>{{ $item->type_label }}{{ $item->notes ? ' · '.$item->notes : '' }}</small>
                                    </td>
                                    <td>
                                        <span class="holiday-badge {{ $item->deducts_leave ? 'holiday-badge--warning' : 'holiday-badge--success' }}">
                                            {{ $item->deducts_leave ? 'Tetap Dipotong' : 'Tidak Dipotong' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="holiday-badge {{ $item->is_active ? 'holiday-badge--info' : 'holiday-badge--neutral' }}">
                                            {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="holiday-row-actions">
                                            <a href="{{ route('hr.office-holidays.edit', $item) }}" class="holiday-link">Edit</a>
                                            @if($item->is_active)
                                                <form method="POST" action="{{ route('hr.office-holidays.destroy', $item) }}" onsubmit="return confirm('Nonaktifkan tanggal libur ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="holiday-link holiday-link--danger">Nonaktifkan</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="holiday-pagination">{{ $items->links() }}</div>
            @endif
        </section>
    </div>

    <style>
        .section-header-inline { display:flex; align-items:center; gap:10px; }
        .section-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .icon-navy { background:rgba(10,61,98,.08); color:#0A3D62; }
        .section-title { margin:0; font-size:1rem; font-weight:800; color:#111827; line-height:1.25; }
        .section-subtitle { margin:0; font-size:.8125rem; color:#6B7280; font-weight:500; }
        .holiday-page { display:flex; flex-direction:column; gap:14px; }
        .holiday-card { background:#fff; border:1px solid #E5E7EB; border-radius:16px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,.04); }
        .holiday-card__header, .holiday-list-header { display:flex; flex-direction:column; gap:14px; margin-bottom:18px; }
        .holiday-card h2 { margin:0; color:#111827; font-size:1rem; font-weight:800; }
        .holiday-card p { margin:4px 0 0; color:#6B7280; font-size:.8125rem; line-height:1.5; }
        .holiday-alert { padding:12px 14px; border-radius:12px; font-size:.8125rem; font-weight:600; }
        .holiday-alert--success { background:rgba(34,197,94,.1); border:1px solid rgba(34,197,94,.3); color:#15803D; }
        .holiday-alert--error { background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.25); color:#B91C1C; }
        .holiday-form { display:grid; grid-template-columns:1fr; gap:14px; }
        .holiday-field { display:flex; flex-direction:column; gap:7px; }
        .holiday-field label { font-size:.75rem; font-weight:700; color:#374151; }
        .holiday-field input, .holiday-field select, .holiday-field textarea, .holiday-filter select { width:100%; min-height:46px; padding:0 13px; border:1.5px solid #E5E7EB; border-radius:10px; background:#fff; color:#111827; font:500 .8125rem 'Plus Jakarta Sans', sans-serif; box-sizing:border-box; }
        .holiday-field textarea { padding:12px 13px; resize:vertical; }
        .holiday-field input:focus, .holiday-field select:focus, .holiday-field textarea:focus, .holiday-filter select:focus { outline:none; border-color:#145DA0; box-shadow:0 0 0 4px rgba(20,93,160,.1); }
        .holiday-options { display:grid; grid-template-columns:1fr; gap:10px; }
        .holiday-check { display:flex; align-items:flex-start; gap:10px; padding:12px; border:1px solid #E5E7EB; border-radius:10px; cursor:pointer; }
        .holiday-check input[type="checkbox"] { width:18px; height:18px; margin-top:1px; accent-color:#145DA0; flex-shrink:0; }
        .holiday-check span { display:flex; flex-direction:column; gap:3px; }
        .holiday-check strong { font-size:.8125rem; color:#374151; }
        .holiday-check small { font-size:.6875rem; color:#6B7280; line-height:1.45; }
        .holiday-actions { display:flex; justify-content:flex-end; }
        .holiday-btn { min-height:44px; padding:0 16px; border-radius:10px; border:0; font:700 .8125rem 'Plus Jakarta Sans', sans-serif; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; }
        .holiday-btn--primary { width:100%; color:#fff; background:linear-gradient(135deg,#0A3D62,#145DA0); box-shadow:0 4px 12px rgba(10,61,98,.2); }
        .holiday-btn--secondary { color:#374151; background:#fff; border:1.5px solid #E5E7EB; }
        .holiday-filter { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
        .holiday-filter .holiday-btn { grid-column:1 / -1; }
        .holiday-table-wrap { overflow-x:auto; border:1px solid #E5E7EB; border-radius:12px; }
        .holiday-table { width:100%; min-width:760px; border-collapse:collapse; }
        .holiday-table th { padding:11px 14px; text-align:left; background:#F5F7FA; color:#6B7280; font-size:.6875rem; text-transform:uppercase; letter-spacing:.04em; }
        .holiday-table td { padding:14px; border-top:1px solid #E5E7EB; color:#374151; font-size:.8125rem; vertical-align:top; }
        .holiday-table td strong, .holiday-table td small { display:block; }
        .holiday-table td small { margin-top:4px; color:#6B7280; line-height:1.45; }
        .holiday-badge { display:inline-flex; padding:5px 9px; border-radius:9999px; font-size:.6875rem; font-weight:700; text-transform:uppercase; white-space:nowrap; }
        .holiday-badge--success { background:rgba(34,197,94,.1); color:#15803D; }
        .holiday-badge--warning { background:rgba(245,158,11,.12); color:#B45309; }
        .holiday-badge--info { background:rgba(59,130,246,.1); color:#1D4ED8; }
        .holiday-badge--neutral { background:#F5F7FA; color:#6B7280; }
        .holiday-row-actions { display:flex; align-items:center; gap:10px; }
        .holiday-row-actions form { margin:0; }
        .holiday-link { padding:0; border:0; background:none; color:#145DA0; font:700 .75rem 'Plus Jakarta Sans', sans-serif; text-decoration:none; cursor:pointer; white-space:nowrap; }
        .holiday-link--danger { color:#EF4444; }
        .holiday-empty { padding:36px 16px; text-align:center; border:1px dashed #D1D5DB; border-radius:12px; }
        .holiday-empty strong { color:#374151; font-size:.875rem; }
        .holiday-pagination { margin-top:16px; }
        @media (min-width:640px) {
            .holiday-form { grid-template-columns:1fr 2fr 1fr; }
            .holiday-field--full { grid-column:1 / -1; }
            .holiday-options { grid-template-columns:1fr 1fr; }
            .holiday-btn--primary { width:auto; }
            .holiday-filter { display:flex; align-items:center; }
            .holiday-filter select { width:130px; }
            .holiday-filter .holiday-btn { grid-column:auto; }
        }
        @media (min-width:1024px) {
            .holiday-card { padding:22px; }
            .holiday-list-header { flex-direction:row; align-items:flex-end; justify-content:space-between; }
        }
    </style>
</x-app>
