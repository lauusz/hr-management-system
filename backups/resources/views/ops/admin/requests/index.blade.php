<x-ops-app title="Request Masuk OPS">
    <div class="ops-header ops-admin-request-header">
        <div>
            <h1 class="ops-title">Request Masuk</h1>
            <p class="ops-subtitle">Periksa barang lalu selesaikan pengajuan.</p>
        </div>
        <a class="ops-btn ops-btn-primary" href="{{ route('v2.ops.admin.requests.manual.create') }}">Input Pengambilan Manual</a>
    </div>

    <form method="GET" class="ops-card ops-form-grid ops-admin-request-filter">
        <input class="ops-input" name="q" value="{{ request('q') }}" placeholder="Cari no request, user, atau PT" autocomplete="off">
        <select class="ops-select" name="status">
            <option value="">Semua status</option>
            <option value="PENDING" @selected(request('status') === 'PENDING')>Menunggu</option>
            <option value="APPROVED" @selected(request('status') === 'APPROVED')>Disetujui</option>
            <option value="PARTIAL" @selected(request('status') === 'PARTIAL')>Sebagian</option>
            <option value="REJECTED" @selected(request('status') === 'REJECTED')>Ditolak</option>
        </select>
        <div class="ops-actions">
            <button class="ops-btn ops-btn-primary" type="submit">Filter</button>
            <a class="ops-btn ops-btn-soft" href="{{ route('v2.ops.admin.requests.index') }}">Reset</a>
        </div>
    </form>

    <div class="ops-admin-request-mobile-list">
        @forelse($requests as $request)
            @php($statusLabel = match($request->status) {'PENDING'=>'Menunggu','APPROVED'=>'Disetujui','PARTIAL'=>'Sebagian','REJECTED'=>'Ditolak',default=>$request->status})
            <article class="ops-admin-request-card">
                <div class="ops-admin-request-top">
                    <time datetime="{{ $request->created_at?->toIso8601String() }}">{{ $request->created_at?->format('d/m/Y H:i') }}</time>
                    <span class="ops-badge ops-request-status-{{ strtolower($request->status) }}">{{ $statusLabel }}</span>
                </div>
                <div class="ops-admin-request-person">
                    <strong>{{ $request->user_name_snapshot }}</strong>
                    <span>{{ $request->pt_name_snapshot ?? '-' }}</span>
                </div>
                <div class="ops-admin-request-count">{{ $request->items->count() }} barang</div>
                <a class="ops-btn ops-btn-soft ops-admin-request-action" href="{{ route('v2.ops.admin.requests.show', $request) }}">
                    {{ $request->status === 'PENDING' ? 'Periksa Pengajuan' : 'Lihat Pemeriksaan' }}
                </a>
            </article>
        @empty
            <div class="ops-card ops-empty">Belum ada request.</div>
        @endforelse
    </div>

    <div class="ops-admin-request-desktop-table">
        <table class="ops-admin-request-table">
            <thead><tr><th>Tgl Pengajuan</th><th>User</th><th>PT</th><th>Item</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($requests as $request)
                    @php($statusLabel = match($request->status) {'PENDING'=>'Menunggu','APPROVED'=>'Disetujui','PARTIAL'=>'Sebagian','REJECTED'=>'Ditolak',default=>$request->status})
                    <tr>
                        <td>{{ $request->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $request->user_name_snapshot }}</td>
                        <td>{{ $request->pt_name_snapshot ?? '-' }}</td>
                        <td>{{ $request->items->count() }}</td>
                        <td><span class="ops-badge ops-request-status-{{ strtolower($request->status) }}">{{ $statusLabel }}</span></td>
                        <td><a class="ops-btn ops-btn-soft ops-btn-sm" href="{{ route('v2.ops.admin.requests.show', $request) }}">{{ $request->status === 'PENDING' ? 'Periksa Pengajuan' : 'Lihat Pemeriksaan' }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada request.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :items="$requests" preserve-query />

    <style>
        .ops-admin-request-header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
        .ops-admin-request-filter { margin-bottom:14px; }
        .ops-admin-request-mobile-list { display:grid; gap:12px; }
        .ops-admin-request-card { padding:16px; border:1px solid var(--ops-border); border-radius:16px; background:#fff; box-shadow:0 1px 3px rgba(17,24,39,.04); }
        .ops-admin-request-top { display:flex; align-items:center; justify-content:space-between; gap:10px; padding-bottom:12px; border-bottom:1px solid var(--ops-border); }
        .ops-admin-request-top time { color:var(--ops-muted); font-size:11px; font-weight:700; }
        .ops-admin-request-person { margin-top:14px; }
        .ops-admin-request-person strong,.ops-admin-request-person span { display:block; overflow-wrap:anywhere; }
        .ops-admin-request-person strong { font-size:14px; line-height:1.45; }
        .ops-admin-request-person span { margin-top:4px; color:var(--ops-muted); font-size:12px; font-weight:600; }
        .ops-admin-request-count { margin:14px 0; padding:8px 10px; border-radius:10px; background:var(--ops-soft); color:var(--ops-dark); font-size:12px; font-weight:800; }
        .ops-admin-request-action { width:100%; }
        .ops-request-status-rejected { color:#991B1B; background:#FEE2E2; }
        .ops-request-status-approved { color:var(--ops-dark); background:var(--ops-soft); }
        .ops-request-status-pending,.ops-request-status-partial { color:#92400E; background:#FEF3C7; }
        .ops-admin-request-desktop-table { display:none; overflow-x:auto; border:1px solid var(--ops-border); border-radius:18px; background:#fff; }
        .ops-admin-request-table { width:100%; border-collapse:collapse; font-size:12px; }
        .ops-admin-request-table th,.ops-admin-request-table td { padding:13px 14px; border-bottom:1px solid var(--ops-border); text-align:left; }
        .ops-admin-request-table th { color:var(--ops-muted); font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
        .ops-admin-request-table tbody tr:last-child td { border-bottom:0; }
        .ops-btn-sm { min-height:36px; padding:0 12px; }
        @media (max-width:639px) {
            .ops-admin-request-header { flex-direction:column; }
            .ops-admin-request-header .ops-btn { width:100%; }
            .ops-admin-request-filter { padding:12px; }
            .ops-admin-request-filter .ops-actions { display:grid; grid-template-columns:1fr 1fr; }
            .ops-admin-request-filter .ops-btn { width:100%; }
        }
        @media (min-width:640px) {
            .ops-admin-request-mobile-list { display:none; }
            .ops-admin-request-desktop-table { display:block; }
        }
    </style>
</x-ops-app>
