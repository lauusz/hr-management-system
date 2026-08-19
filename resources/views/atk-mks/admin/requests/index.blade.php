<x-atk-mks-app title="Request Masuk ATK MKS">
    <div class="atk-mks-header atk-mks-admin-request-header">
        <div>
            <h1 class="atk-mks-title">Request Masuk</h1>
            <p class="atk-mks-subtitle">Periksa barang lalu selesaikan pengajuan.</p>
        </div>
        <a class="atk-mks-btn atk-mks-btn-primary" href="{{ route('v2.atk-mks.admin.requests.manual.create') }}">Input Pengambilan Manual</a>
    </div>

    <form method="GET" class="atk-mks-card atk-mks-form-grid atk-mks-admin-request-filter">
        <input class="atk-mks-input" name="q" value="{{ request('q') }}" placeholder="Cari no request, user, atau PT" autocomplete="off">
        <select class="atk-mks-select" name="status">
            <option value="">Semua status</option>
            <option value="PENDING" @selected(request('status') === 'PENDING')>Menunggu</option>
            <option value="APPROVED" @selected(request('status') === 'APPROVED')>Disetujui</option>
            <option value="PARTIAL" @selected(request('status') === 'PARTIAL')>Sebagian</option>
            <option value="REJECTED" @selected(request('status') === 'REJECTED')>Ditolak</option>
        </select>
        <div class="atk-mks-actions">
            <button class="atk-mks-btn atk-mks-btn-primary" type="submit">Filter</button>
            <a class="atk-mks-btn atk-mks-btn-soft" href="{{ route('v2.atk-mks.admin.requests.index') }}">Reset</a>
        </div>
    </form>

    <div class="atk-mks-admin-request-mobile-list">
        @forelse($requests as $request)
            @php($statusLabel = match($request->status) {'PENDING'=>'Menunggu','APPROVED'=>'Disetujui','PARTIAL'=>'Sebagian','REJECTED'=>'Ditolak',default=>$request->status})
            <article class="atk-mks-admin-request-card">
                <div class="atk-mks-admin-request-top">
                    <time datetime="{{ $request->created_at?->toIso8601String() }}">{{ $request->created_at?->format('d/m/Y H:i') }}</time>
                    <span class="atk-mks-badge atk-mks-request-status-{{ strtolower($request->status) }}">{{ $statusLabel }}</span>
                </div>
                <div class="atk-mks-admin-request-person">
                    <strong>{{ $request->user_name_snapshot }}</strong>
                    <span>{{ $request->pt_name_snapshot ?? '-' }}</span>
                </div>
                <div class="atk-mks-admin-request-count">{{ $request->items->count() }} barang</div>
                <a class="atk-mks-btn atk-mks-btn-soft atk-mks-admin-request-action" href="{{ route('v2.atk-mks.admin.requests.show', $request) }}">
                    {{ $request->status === 'PENDING' ? 'Periksa Pengajuan' : 'Lihat Pemeriksaan' }}
                </a>
            </article>
        @empty
            <div class="atk-mks-card atk-mks-empty">Belum ada request.</div>
        @endforelse
    </div>

    <div class="atk-mks-admin-request-desktop-table">
        <table class="atk-mks-admin-request-table">
            <thead><tr><th>Tgl Pengajuan</th><th>User</th><th>PT</th><th>Item</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($requests as $request)
                    @php($statusLabel = match($request->status) {'PENDING'=>'Menunggu','APPROVED'=>'Disetujui','PARTIAL'=>'Sebagian','REJECTED'=>'Ditolak',default=>$request->status})
                    <tr>
                        <td>{{ $request->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $request->user_name_snapshot }}</td>
                        <td>{{ $request->pt_name_snapshot ?? '-' }}</td>
                        <td>{{ $request->items->count() }}</td>
                        <td><span class="atk-mks-badge atk-mks-request-status-{{ strtolower($request->status) }}">{{ $statusLabel }}</span></td>
                        <td><a class="atk-mks-btn atk-mks-btn-soft atk-mks-btn-sm" href="{{ route('v2.atk-mks.admin.requests.show', $request) }}">{{ $request->status === 'PENDING' ? 'Periksa Pengajuan' : 'Lihat Pemeriksaan' }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada request.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :items="$requests" preserve-query />

    <style>
        .atk-mks-admin-request-header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
        .atk-mks-admin-request-filter { margin-bottom:14px; }
        .atk-mks-admin-request-mobile-list { display:grid; gap:12px; }
        .atk-mks-admin-request-card { padding:16px; border:1px solid var(--atk-mks-border); border-radius:16px; background:#fff; box-shadow:0 1px 3px rgba(17,24,39,.04); }
        .atk-mks-admin-request-top { display:flex; align-items:center; justify-content:space-between; gap:10px; padding-bottom:12px; border-bottom:1px solid var(--atk-mks-border); }
        .atk-mks-admin-request-top time { color:var(--atk-mks-muted); font-size:11px; font-weight:700; }
        .atk-mks-admin-request-person { margin-top:14px; }
        .atk-mks-admin-request-person strong,.atk-mks-admin-request-person span { display:block; overflow-wrap:anywhere; }
        .atk-mks-admin-request-person strong { font-size:14px; line-height:1.45; }
        .atk-mks-admin-request-person span { margin-top:4px; color:var(--atk-mks-muted); font-size:12px; font-weight:600; }
        .atk-mks-admin-request-count { margin:14px 0; padding:8px 10px; border-radius:10px; background:var(--atk-mks-soft); color:var(--atk-mks-dark); font-size:12px; font-weight:800; }
        .atk-mks-admin-request-action { width:100%; }
        .atk-mks-request-status-rejected { color:#991B1B; background:#FEE2E2; }
        .atk-mks-request-status-approved { color:var(--atk-mks-dark); background:var(--atk-mks-soft); }
        .atk-mks-request-status-pending,.atk-mks-request-status-partial { color:#92400E; background:#FEF3C7; }
        .atk-mks-admin-request-desktop-table { display:none; overflow-x:auto; border:1px solid var(--atk-mks-border); border-radius:18px; background:#fff; }
        .atk-mks-admin-request-table { width:100%; border-collapse:collapse; font-size:12px; }
        .atk-mks-admin-request-table th,.atk-mks-admin-request-table td { padding:13px 14px; border-bottom:1px solid var(--atk-mks-border); text-align:left; }
        .atk-mks-admin-request-table th { color:var(--atk-mks-muted); font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
        .atk-mks-admin-request-table tbody tr:last-child td { border-bottom:0; }
        .atk-mks-btn-sm { min-height:36px; padding:0 12px; }
        @media (max-width:639px) {
            .atk-mks-admin-request-header { flex-direction:column; }
            .atk-mks-admin-request-header .atk-mks-btn { width:100%; }
            .atk-mks-admin-request-filter { padding:12px; }
            .atk-mks-admin-request-filter .atk-mks-actions { display:grid; grid-template-columns:1fr 1fr; }
            .atk-mks-admin-request-filter .atk-mks-btn { width:100%; }
        }
        @media (min-width:640px) {
            .atk-mks-admin-request-mobile-list { display:none; }
            .atk-mks-admin-request-desktop-table { display:block; }
        }
    </style>
</x-atk-mks-app>


