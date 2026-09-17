<x-atk-mks-app title="Master Barang ATK MKS">
    <div class="atk-mks-header atk-mks-admin-items-header">
        <div>
            <h1 class="atk-mks-title">Master Barang ATK MKS</h1>
            <p class="atk-mks-subtitle">Kelola barang dan stok operasional.</p>
        </div>
        <a class="atk-mks-btn atk-mks-btn-primary atk-mks-admin-items-create" href="{{ route('v2.atk-mks.admin.items.create') }}">Tambah Barang</a>
    </div>

    <form class="atk-mks-card atk-mks-admin-items-filter" method="GET">
        <input class="atk-mks-input" name="q" value="{{ request('q') }}" placeholder="Cari barang" autocomplete="off">
        <div class="atk-mks-actions">
            <button class="atk-mks-btn atk-mks-btn-primary" type="submit">Cari</button>
            <a class="atk-mks-btn atk-mks-btn-soft" href="{{ route('v2.atk-mks.admin.items.index') }}">Reset</a>
        </div>
    </form>

    <div class="atk-mks-admin-items-table-wrap">
        <table class="atk-mks-admin-items-table">
            <thead>
                <tr><th>Barang</th><th>Stok</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr class="atk-mks-admin-item-card">
                        <td class="atk-mks-admin-item-name" data-label="Barang"><strong>{{ $item->name }}</strong></td>
                        <td class="atk-mks-admin-item-stock" data-label="Stok"><span class="atk-mks-badge">{{ $item->stock_qty }} {{ $item->unit_name }}</span></td>
                        <td class="atk-mks-admin-item-status" data-label="Status"><span class="atk-mks-badge">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td class="atk-mks-admin-item-actions" data-label="Aksi">
                            <div class="atk-mks-actions">
                                <a class="atk-mks-btn atk-mks-btn-soft" href="{{ route('v2.atk-mks.admin.items.edit', $item) }}">Ubah</a>
                                <details class="atk-mks-admin-item-detail">
                                    <summary class="atk-mks-btn atk-mks-btn-danger">Hapus</summary>
                                    <form method="POST" action="{{ route('v2.atk-mks.admin.items.destroy', $item) }}" class="atk-mks-admin-item-form">
                                        @csrf
                                        @method('DELETE')
                                        <label class="atk-mks-label">Alasan hapus</label>
                                        <textarea class="atk-mks-textarea" name="deletion_note" required maxlength="1000"></textarea>
                                        <button class="atk-mks-btn atk-mks-btn-danger" type="submit">Hapus Barang</button>
                                    </form>
                                </details>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="atk-mks-admin-items-empty"><td colspan="4">Barang belum tersedia.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :items="$items" preserve-query />

    <style>
        .atk-mks-admin-items-header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
        .atk-mks-admin-items-filter { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:10px; margin-bottom:14px; }
        .atk-mks-admin-items-table-wrap { overflow-x:auto; border:1px solid var(--atk-mks-border); border-radius:18px; background:#fff; box-shadow:0 1px 3px rgba(17,24,39,.04); }
        .atk-mks-admin-items-table { width:100%; border-collapse:collapse; font-size:12px; }
        .atk-mks-admin-items-table th,.atk-mks-admin-items-table td { padding:13px 14px; border-bottom:1px solid var(--atk-mks-border); text-align:left; vertical-align:top; }
        .atk-mks-admin-items-table th { color:var(--atk-mks-muted); font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
        .atk-mks-admin-items-table tbody tr:last-child td { border-bottom:0; }
        .atk-mks-admin-item-detail { position:relative; }
        .atk-mks-admin-item-detail summary { list-style:none; }
        .atk-mks-admin-item-detail summary::-webkit-details-marker { display:none; }
        .atk-mks-admin-item-form { display:grid; gap:8px; min-width:250px; margin-top:8px; padding:12px; border:1px solid var(--atk-mks-border); border-radius:12px; background:#fff; }
        .atk-mks-admin-items-empty td { padding:28px 16px; color:var(--atk-mks-muted); text-align:center; }

        @media (max-width:639px) {
            .atk-mks-admin-items-header { flex-direction:column; }
            .atk-mks-admin-items-create { width:100%; }
            .atk-mks-admin-items-filter { grid-template-columns:1fr; padding:12px; }
            .atk-mks-admin-items-filter .atk-mks-actions,.atk-mks-admin-items-filter .atk-mks-btn { width:100%; }
            .atk-mks-admin-items-filter .atk-mks-actions { display:grid; grid-template-columns:1fr 1fr; }
            .atk-mks-admin-items-table-wrap { overflow:visible; border:0; border-radius:0; background:transparent; box-shadow:none; }
            .atk-mks-admin-items-table,.atk-mks-admin-items-table tbody { display:block; }
            .atk-mks-admin-items-table thead { display:none; }
            .atk-mks-admin-items-table tbody { display:grid; gap:12px; }
            .atk-mks-admin-item-card { display:grid; grid-template-columns:minmax(0,1fr) auto; grid-template-areas:"name stock" "status status" "actions actions"; gap:0 10px; padding:14px; border:1px solid var(--atk-mks-border); border-radius:14px; background:#fff; box-shadow:0 1px 3px rgba(17,24,39,.04); }
            .atk-mks-admin-item-card td { display:grid; grid-template-columns:82px minmax(0,1fr); gap:10px; padding:8px 0; border:0; }
            .atk-mks-admin-item-card td::before { content:attr(data-label); color:var(--atk-mks-muted); font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
            .atk-mks-admin-item-name { grid-area:name; display:block!important; padding-top:0!important; }
            .atk-mks-admin-item-name::before { display:block; margin-bottom:5px; }
            .atk-mks-admin-item-stock { grid-area:stock; display:block!important; padding-top:0!important; text-align:right; }
            .atk-mks-admin-item-stock::before { display:none; }
            .atk-mks-admin-item-status { grid-area:status; }
            .atk-mks-admin-item-actions { grid-area:actions; display:block!important; border-top:1px solid var(--atk-mks-border)!important; }
            .atk-mks-admin-item-actions::before { display:block; margin:4px 0 8px; }
            .atk-mks-admin-item-actions>.atk-mks-actions { display:grid; grid-template-columns:1fr 1fr; width:100%; }
            .atk-mks-admin-item-actions .atk-mks-btn { width:100%; }
            .atk-mks-admin-item-detail,.atk-mks-admin-item-form { min-width:0; width:100%; }
            .atk-mks-admin-item-detail[open] { grid-column:1 / -1; }
            .atk-mks-admin-items-empty { display:block; padding:18px; border:1px solid var(--atk-mks-border); border-radius:14px; background:#fff; text-align:center; }
            .atk-mks-admin-items-empty td { display:block; padding:0; border:0; }
        }
    </style>
</x-atk-mks-app>


