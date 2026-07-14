<x-atk-app title="Master Barang ATK">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Master Barang</h1>
            <p class="atk-subtitle">Kelola barang, gambar, satuan, dan stok.</p>
        </div>
        <a class="atk-btn atk-btn-primary" href="{{ route('v2.atk.admin.items.create') }}">Tambah Barang</a>
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
    <div class="atk-table-wrap">
        <table class="atk-table">
            <thead><tr><th>Barang</th><th>Kategori</th><th>Stok</th><th>Satuan</th><th>Tambah Stok</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td><strong>{{ $item->name }}</strong></td>
                        <td>{{ $item->category?->name ?? '-' }}</td>
                        <td>
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
                        <td class="atk-unit-cell">{{ $item->unit_conversion_label }}</td>
                        <td>
                            <form method="POST" action="{{ route('v2.atk.admin.items.stock.store', $item) }}" class="atk-actions">
                                @csrf
                                <input type="hidden" name="movement_type" value="IN">
                                <input class="atk-input" style="width:90px" type="number" min="1" name="qty" value="1">
                                <input class="atk-input" style="width:130px" type="number" min="0" name="unit_price" placeholder="Harga/unit" aria-label="Harga masuk per {{ $item->unit_name }}">
                                <button class="atk-btn atk-btn-secondary" type="submit">Tambah</button>
                            </form>
                        </td>
                        <td><a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.items.edit', $item) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada barang.</td></tr>
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
    </style>
</x-atk-app>
