<x-ops-app title="Pengajuan Saya">
    @php
        $statusLabels = ['PENDING' => 'Menunggu', 'APPROVED' => 'Disetujui', 'PARTIAL' => 'Sebagian', 'REJECTED' => 'Ditolak'];
        $statusClasses = ['APPROVED' => 'success', 'REJECTED' => 'error'];
    @endphp

    <div class="ops-header">
        <h1 class="ops-title">Pengajuan Saya</h1>
        <p class="ops-subtitle">Pantau status barang yang diajukan.</p>
    </div>

    <form method="GET" class="ops-card ops-form-grid ops-request-filter">
        <div>
            <label class="ops-label" for="status">Status</label>
            <select class="ops-select" id="status" name="status">
                <option value="">Semua</option>
                <option value="PENDING" @selected(request('status') === 'PENDING')>Menunggu</option>
                <option value="APPROVED" @selected(request('status') === 'APPROVED')>Disetujui</option>
                <option value="PARTIAL" @selected(request('status') === 'PARTIAL')>Sebagian</option>
                <option value="REJECTED" @selected(request('status') === 'REJECTED')>Ditolak</option>
            </select>
        </div>
        <div class="ops-actions">
            <button class="ops-btn ops-btn-primary" type="submit">Filter</button>
            <a class="ops-btn ops-request-reset" href="{{ route('v2.ops.requests.index') }}">Reset</a>
        </div>
    </form>

    <div class="ops-request-mobile-list">
        @forelse($requests as $request)
            <article class="ops-request-card">
                <div class="ops-request-card-top">
                    <strong class="ops-request-number">{{ $request->request_number }}</strong>
                    <span class="ops-request-badge ops-request-badge-{{ $statusClasses[$request->status] ?? 'warning' }}">{{ $statusLabels[$request->status] ?? $request->status }}</span>
                </div>
                <dl class="ops-request-meta">
                    <div>
                        <dt>PT</dt>
                        <dd>{{ $request->pt_name_snapshot ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt>Tanggal Pengajuan</dt>
                        <dd>{{ $request->created_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
                <a class="ops-btn ops-btn-soft ops-request-detail" href="{{ route('v2.ops.requests.show', $request) }}">Lihat Detail</a>
            </article>
        @empty
            <div class="ops-card ops-empty">Belum ada pengajuan.</div>
        @endforelse
    </div>

    <div class="ops-request-table-wrap ops-request-desktop-table">
        <table class="ops-request-table">
            <thead>
                <tr><th>No Pengajuan</th><th>PT</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td><strong>{{ $request->request_number }}</strong></td>
                        <td>{{ $request->pt_name_snapshot ?? '-' }}</td>
                        <td><span class="ops-request-badge ops-request-badge-{{ $statusClasses[$request->status] ?? 'warning' }}">{{ $statusLabels[$request->status] ?? $request->status }}</span></td>
                        <td>{{ $request->created_at?->format('d/m/Y H:i') }}</td>
                        <td><a class="ops-btn ops-btn-soft" href="{{ route('v2.ops.requests.show', $request) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5">Belum ada pengajuan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :items="$requests" preserve-query />

    <style>
        .ops-request-filter { margin-bottom:14px; }
        .ops-request-reset { color:var(--ops-muted); background:#F1F1F3; }
        .ops-request-mobile-list { display:grid; gap:12px; }
        .ops-request-card { padding:16px; border:1px solid var(--ops-border); border-radius:16px; background:#fff; box-shadow:0 1px 3px rgba(17,24,39,.04); }
        .ops-request-card-top { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; padding-bottom:12px; border-bottom:1px solid #EDF4F2; }
        .ops-request-number { min-width:0; font-size:14px; line-height:1.45; overflow-wrap:anywhere; }
        .ops-request-badge { display:inline-flex; padding:5px 9px; border-radius:999px; font-size:10px; font-weight:800; white-space:nowrap; }
        .ops-request-badge-success { color:#15803D; background:rgba(34,197,94,.12); }
        .ops-request-badge-warning { color:#B45309; background:rgba(245,158,11,.13); }
        .ops-request-badge-error { color:#B91C1C; background:rgba(239,68,68,.12); }
        .ops-request-meta { display:grid; gap:10px; margin:14px 0; }
        .ops-request-meta div { display:flex; align-items:baseline; justify-content:space-between; gap:12px; }
        .ops-request-meta dt { color:var(--ops-muted); font-size:11px; font-weight:700; }
        .ops-request-meta dd { margin:0; color:var(--ops-text); font-size:12px; font-weight:700; text-align:right; }
        .ops-request-detail { width:100%; }
        .ops-request-desktop-table { display:none; }
        .ops-request-table-wrap { overflow-x:auto; border:1px solid var(--ops-border); border-radius:16px; background:#fff; }
        .ops-request-table { width:100%; min-width:720px; border-collapse:collapse; }
        .ops-request-table th,.ops-request-table td { padding:12px 14px; border-bottom:1px solid var(--ops-border); text-align:left; font-size:13px; }
        .ops-request-table th { color:var(--ops-muted); background:#F4FBF8; font-size:11px; letter-spacing:.04em; text-transform:uppercase; }
        .ops-request-table tr:last-child td { border-bottom:0; }

        @media (min-width:640px) {
            .ops-request-mobile-list { display:none; }
            .ops-request-desktop-table { display:block; }
        }
    </style>
</x-ops-app>
