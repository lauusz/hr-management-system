<x-ops-app title="Master Barang OPS">
    <div class="ops-header ops-admin-items-header">
        <div>
            <h1 class="ops-title">Master Barang OPS</h1>
            <p class="ops-subtitle">Kelola barang dan stok operasional.</p>
        </div>
        <a class="ops-btn ops-btn-primary ops-admin-items-create" href="{{ route('v2.ops.admin.items.create') }}">Tambah Barang</a>
    </div>

    <form class="ops-card ops-admin-items-filter" method="GET">
        <input class="ops-input" name="q" value="{{ request('q') }}" placeholder="Cari barang" autocomplete="off">
        <div class="ops-actions">
            <button class="ops-btn ops-btn-primary" type="submit">Cari</button>
            <a class="ops-btn ops-btn-soft" href="{{ route('v2.ops.admin.items.index') }}">Reset</a>
        </div>
    </form>

    <div class="ops-admin-items-table-wrap">
        <table class="ops-admin-items-table">
            <thead>
                <tr><th>Barang</th><th>Stok</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr class="ops-admin-item-card">
                        <td class="ops-admin-item-name" data-label="Barang"><strong>{{ $item->name }}</strong></td>
                        <td class="ops-admin-item-stock" data-label="Stok"><span class="ops-badge">{{ $item->stock_qty }} {{ $item->unit_name }}</span></td>
                        <td class="ops-admin-item-status" data-label="Status"><span class="ops-badge">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td class="ops-admin-item-actions" data-label="Aksi">
                            <div class="ops-actions">
                                <a class="ops-btn ops-btn-soft" href="{{ route('v2.ops.admin.items.edit', $item) }}">Ubah</a>
                                <details class="ops-admin-item-detail">
                                    <summary class="ops-btn ops-btn-soft">Kurangi Stok</summary>
                                    <form method="POST" action="{{ route('v2.ops.admin.items.stock.store', $item) }}" class="ops-admin-item-form">
                                        @csrf
                                        <input type="hidden" name="movement_type" value="OUT">
                                        <input class="ops-input" type="number" name="qty" min="1" max="{{ $item->stock_qty }}" placeholder="Jumlah" required>
                                        <input class="ops-input" name="notes" placeholder="Keterangan (opsional)">
                                        <button class="ops-btn ops-btn-primary" type="submit">Kurangi</button>
                                    </form>
                                </details>
                                <details class="ops-admin-item-detail">
                                    <summary class="ops-btn ops-btn-danger">Hapus</summary>
                                    <form method="POST" action="{{ route('v2.ops.admin.items.destroy', $item) }}" class="ops-admin-item-form">
                                        @csrf
                                        @method('DELETE')
                                        <label class="ops-label">Alasan hapus</label>
                                        <textarea class="ops-textarea" name="deletion_note" required maxlength="1000"></textarea>
                                        <button class="ops-btn ops-btn-danger" type="submit">Hapus Barang</button>
                                    </form>
                                </details>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="ops-admin-items-empty"><td colspan="4">Barang belum tersedia.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :items="$items" preserve-query />

    <style>
        .ops-admin-items-header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
        .ops-admin-items-filter { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:10px; margin-bottom:14px; }
        .ops-admin-items-table-wrap { overflow-x:auto; border:1px solid var(--ops-border); border-radius:18px; background:#fff; box-shadow:0 1px 3px rgba(17,24,39,.04); }
        .ops-admin-items-table { width:100%; border-collapse:collapse; font-size:12px; }
        .ops-admin-items-table th,.ops-admin-items-table td { padding:13px 14px; border-bottom:1px solid var(--ops-border); text-align:left; vertical-align:top; }
        .ops-admin-items-table th { color:var(--ops-muted); font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
        .ops-admin-items-table tbody tr:last-child td { border-bottom:0; }
        .ops-admin-item-detail { position:relative; }
        .ops-admin-item-detail summary { list-style:none; }
        .ops-admin-item-detail summary::-webkit-details-marker { display:none; }
        .ops-admin-item-form { display:grid; gap:8px; min-width:250px; margin-top:8px; padding:12px; border:1px solid var(--ops-border); border-radius:12px; background:#fff; }
        .ops-admin-items-empty td { padding:28px 16px; color:var(--ops-muted); text-align:center; }

        @media (max-width:639px) {
            .ops-admin-items-header { flex-direction:column; }
            .ops-admin-items-create { width:100%; }
            .ops-admin-items-filter { grid-template-columns:1fr; padding:12px; }
            .ops-admin-items-filter .ops-actions,.ops-admin-items-filter .ops-btn { width:100%; }
            .ops-admin-items-filter .ops-actions { display:grid; grid-template-columns:1fr 1fr; }
            .ops-admin-items-table-wrap { overflow:visible; border:0; border-radius:0; background:transparent; box-shadow:none; }
            .ops-admin-items-table,.ops-admin-items-table tbody { display:block; }
            .ops-admin-items-table thead { display:none; }
            .ops-admin-items-table tbody { display:grid; gap:12px; }
            .ops-admin-item-card { display:grid; grid-template-columns:minmax(0,1fr) auto; grid-template-areas:"name stock" "status status" "actions actions"; gap:0 10px; padding:14px; border:1px solid var(--ops-border); border-radius:14px; background:#fff; box-shadow:0 1px 3px rgba(17,24,39,.04); }
            .ops-admin-item-card td { display:grid; grid-template-columns:82px minmax(0,1fr); gap:10px; padding:8px 0; border:0; }
            .ops-admin-item-card td::before { content:attr(data-label); color:var(--ops-muted); font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
            .ops-admin-item-name { grid-area:name; display:block!important; padding-top:0!important; }
            .ops-admin-item-name::before { display:block; margin-bottom:5px; }
            .ops-admin-item-stock { grid-area:stock; display:block!important; padding-top:0!important; text-align:right; }
            .ops-admin-item-stock::before { display:none; }
            .ops-admin-item-status { grid-area:status; }
            .ops-admin-item-actions { grid-area:actions; display:block!important; border-top:1px solid var(--ops-border)!important; }
            .ops-admin-item-actions::before { display:block; margin:4px 0 8px; }
            .ops-admin-item-actions>.ops-actions { display:grid; grid-template-columns:1fr 1fr; width:100%; }
            .ops-admin-item-actions .ops-btn { width:100%; }
            .ops-admin-item-actions>.ops-actions>a:first-child { grid-column:1 / -1; }
            .ops-admin-item-detail,.ops-admin-item-form { min-width:0; width:100%; }
            .ops-admin-item-detail[open] { grid-column:1 / -1; }
            .ops-admin-items-empty { display:block; padding:18px; border:1px solid var(--ops-border); border-radius:14px; background:#fff; text-align:center; }
            .ops-admin-items-empty td { display:block; padding:0; border:0; }
        }
    </style>
</x-ops-app>
