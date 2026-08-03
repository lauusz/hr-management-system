<x-ops-app title="Master Barang OPS">
    <div class="ops-header ops-actions">
        <div style="flex:1"><h1 class="ops-title">Master Barang OPS</h1><p class="ops-subtitle">Tambah, ubah, dan atur stok barang.</p></div>
        <a class="ops-btn ops-btn-primary" href="{{ route('v2.ops.admin.items.create') }}">Tambah Barang</a>
    </div>
    <form class="ops-card ops-actions" method="GET" style="margin-bottom:14px">
        <input class="ops-input" style="flex:1" name="q" value="{{ request('q') }}" placeholder="Cari barang">
        <button class="ops-btn ops-btn-primary" type="submit">Cari</button>
    </form>
    <div class="ops-grid" data-ops-mobile-cards>
        @forelse($items as $item)
            <article class="ops-card">
                <h2>{{ $item->name }}</h2>
                <p class="ops-muted">Stok: {{ $item->stock_qty }} {{ $item->unit_name }}</p>
                <span class="ops-badge">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                <div class="ops-actions" style="margin-top:12px">
                    <a class="ops-btn ops-btn-soft" href="{{ route('v2.ops.admin.items.edit', $item) }}">Ubah</a>
                </div>
                <details style="margin-top:10px"><summary class="ops-btn ops-btn-soft">Tambah Stok</summary>
                    <form method="POST" action="{{ route('v2.ops.admin.items.stock.store', $item) }}" class="ops-form-grid" style="margin-top:10px">
                        @csrf<input type="hidden" name="movement_type" value="IN">
                        <input class="ops-input" type="number" name="qty" min="1" placeholder="Jumlah" required>
                        <input class="ops-input" name="notes" placeholder="Keterangan (opsional)">
                        <button class="ops-btn ops-btn-primary" type="submit">Tambah</button>
                    </form>
                </details>
                <details style="margin-top:10px"><summary class="ops-btn ops-btn-soft">Kurangi Stok</summary>
                    <form method="POST" action="{{ route('v2.ops.admin.items.stock.store', $item) }}" class="ops-form-grid" style="margin-top:10px">
                        @csrf<input type="hidden" name="movement_type" value="OUT">
                        <input class="ops-input" type="number" name="qty" min="1" max="{{ $item->stock_qty }}" placeholder="Jumlah" required>
                        <input class="ops-input" name="notes" placeholder="Keterangan (opsional)">
                        <button class="ops-btn ops-btn-primary" type="submit">Kurangi</button>
                    </form>
                </details>
                <details style="margin-top:10px"><summary class="ops-btn ops-btn-danger">Hapus</summary>
                    <form method="POST" action="{{ route('v2.ops.admin.items.destroy', $item) }}" style="margin-top:10px">
                        @csrf @method('DELETE')
                        <label class="ops-label">Alasan hapus</label>
                        <textarea class="ops-textarea" name="deletion_note" required maxlength="1000"></textarea>
                        <button class="ops-btn ops-btn-danger" style="margin-top:8px" type="submit">Hapus Barang</button>
                    </form>
                </details>
            </article>
        @empty
            <div class="ops-card ops-empty">Barang belum tersedia.</div>
        @endforelse
    </div>
    <x-pagination :items="$items" preserve-query />
</x-ops-app>
