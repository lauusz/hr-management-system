<x-atk-app title="Master Barang ATK">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Master Barang</h1>
            <p class="atk-subtitle">Kelola barang, gambar, satuan, dan stok.</p>
        </div>
        <a class="atk-btn atk-btn-primary atk-admin-items-create" href="{{ route('v2.atk.admin.items.create') }}">Tambah Barang</a>
    </div>
    <form method="GET" class="atk-card atk-form-grid" style="margin-bottom:14px">
        <input class="atk-input" name="q" value="{{ request('q') }}" placeholder="Cari barang" autocomplete="off">
        <select class="atk-select" name="category_id">
            <option value="">Semua kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <select class="atk-select" name="stock">
            <option value="">Semua stok</option>
            <option value="out" @selected(request('stock') === 'out')>Habis</option>
            <option value="low" @selected(request('stock') === 'low')>Menipis</option>
        </select>
        <div class="atk-actions">
            <button class="atk-btn atk-btn-primary" type="submit">Filter</button>
            <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.items.index') }}">Reset</a>
        </div>
    </form>
    <div class="atk-table-wrap atk-admin-items-mobile-table">
        <table class="atk-table atk-admin-items-table">
            <thead><tr><th>Barang</th><th>Kategori</th><th>Stok</th><th>Satuan</th><th>Tambah Stok</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($items as $item)
                    <tr class="atk-admin-item-card">
                        <td class="atk-admin-item-name" data-label="Barang"><strong>{{ $item->name }}</strong></td>
                        <td class="atk-admin-item-category" data-label="Kategori">{{ $item->category?->name ?? '-' }}</td>
                        <td class="atk-admin-item-stock" data-label="Stok">
                            <div class="atk-stock-cell">
                                <span class="atk-stock-row">
                                    <span class="atk-stock-key">Tersedia</span>
                                    @if($item->stock_status === 'OUT')
                                        <span class="atk-badge atk-badge-neutral">Habis</span>
                                    @elseif($item->stock_status === 'LOW')
                                        <span class="atk-badge atk-badge-warning">{{ $item->stock_with_unit }}</span>
                                    @else
                                        <span class="atk-badge atk-badge-success">{{ $item->stock_with_unit }}</span>
                                    @endif
                                </span>
                                <span class="atk-stock-row atk-stock-sub">
                                    Min. ambil {{ $item->min_request_qty }} {{ $item->unit_name }}
                                </span>
                            </div>
                        </td>
                        <td class="atk-unit-cell atk-admin-item-unit" data-label="Satuan">{{ $item->unit_conversion_label }}</td>
                        <td class="atk-admin-item-restock" data-label="Tambah Stok">
                            <form method="POST" action="{{ route('v2.atk.admin.items.stock.store', $item) }}" class="atk-actions atk-admin-item-stock-form">
                                @csrf
                                <input type="hidden" name="movement_type" value="IN">
                                <input class="atk-input" style="width:90px" type="number" min="1" name="qty" value="1" aria-label="Jumlah stok masuk {{ $item->name }}">
                                <input class="atk-input" style="width:130px" type="number" min="0" name="unit_price" placeholder="Harga/unit" aria-label="Harga masuk per {{ $item->unit_name }}">
                                <button class="atk-btn atk-btn-secondary" type="submit">Tambah</button>
                            </form>
                        </td>
                        <td class="atk-admin-item-actions" data-label="Aksi"><a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.items.edit', $item) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr class="atk-admin-items-empty"><td colspan="6">Belum ada barang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :items="$items" preserve-query />
    <style>
        .atk-stock-cell {
            display: flex;
            flex-direction: column;
            gap: 4px;
            white-space: nowrap;
        }
        .atk-stock-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .atk-stock-key {
            font-size: 11px;
            font-weight: 700;
            color: var(--atk-muted);
        }
        .atk-stock-sub {
            font-size: 11px;
            font-weight: 600;
            color: var(--atk-muted);
        }
        .atk-unit-cell {
            white-space: nowrap;
            color: var(--atk-muted);
            font-size: 12px;
        }
        @media (max-width: 639px) {
            .atk-admin-items-create {
                width: 100%;
            }
            .atk-admin-items-mobile-table {
                overflow: visible;
                border: 0;
                border-radius: 0;
                background: transparent;
            }
            .atk-admin-items-table {
                display: block;
                min-width: 0;
            }
            .atk-admin-items-table thead {
                display: none;
            }
            .atk-admin-items-table tbody {
                display: grid;
                gap: 12px;
            }
            .atk-admin-item-card {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                grid-template-areas:
                    "name stock"
                    "category category"
                    "unit unit"
                    "restock restock"
                    "actions actions";
                gap: 0 10px;
                padding: 14px;
                border: 1px solid var(--atk-border);
                border-radius: 14px;
                background: var(--atk-surface);
                box-shadow: var(--atk-shadow);
            }
            .atk-admin-item-card td {
                display: grid;
                grid-template-columns: minmax(82px, .65fr) minmax(0, 1fr);
                gap: 10px;
                padding: 8px 0;
                border: 0;
                font-size: 12px;
            }
            .atk-admin-item-card td::before {
                content: attr(data-label);
                color: var(--atk-muted);
                font-size: 10px;
                font-weight: 800;
                letter-spacing: .04em;
                text-transform: uppercase;
            }
            .atk-admin-item-name {
                grid-area: name;
                display: block !important;
                padding-top: 0 !important;
            }
            .atk-admin-item-name::before {
                display: block;
                margin-bottom: 5px;
            }
            .atk-admin-item-stock {
                grid-area: stock;
                display: block !important;
                padding-top: 0 !important;
            }
            .atk-admin-item-stock::before,
            .atk-admin-item-stock .atk-stock-key {
                display: none;
            }
            .atk-admin-item-stock .atk-stock-row {
                justify-content: flex-end;
            }
            .atk-admin-item-stock .atk-stock-sub {
                margin-top: 5px;
                text-align: right;
                white-space: normal;
            }
            .atk-admin-item-category { grid-area: category; }
            .atk-admin-item-unit { grid-area: unit; }
            .atk-admin-item-restock {
                grid-area: restock;
                display: block !important;
                margin-top: 4px;
                border-top: 1px solid var(--atk-border-soft) !important;
            }
            .atk-admin-item-restock::before,
            .atk-admin-item-actions::before {
                display: block;
                margin: 10px 0 8px;
            }
            .atk-admin-item-stock-form {
                display: grid;
                grid-template-columns: 90px minmax(0, 1fr);
                gap: 8px;
                width: 100%;
            }
            .atk-admin-item-stock-form .atk-input {
                width: 100% !important;
            }
            .atk-admin-item-stock-form .atk-btn {
                grid-column: 1 / -1;
                width: 100%;
            }
            .atk-admin-item-actions {
                grid-area: actions;
                display: block !important;
                padding-bottom: 0 !important;
            }
            .atk-admin-item-actions .atk-btn {
                width: 100%;
            }
            .atk-admin-items-empty {
                display: block;
                padding: 18px;
                border: 1px solid var(--atk-border);
                border-radius: 14px;
                background: var(--atk-surface);
                text-align: center;
            }
            .atk-admin-items-empty td {
                display: block;
                padding: 0;
                border: 0;
            }
        }
    </style>
</x-atk-app>
