<x-ops-app title="Katalog OPS">
    <div class="ops-header"><h1 class="ops-title">Katalog</h1><p class="ops-subtitle">Pilih barang yang dibutuhkan.</p></div>
    <form method="GET" class="ops-card ops-actions" style="margin-bottom:14px">
        <input class="ops-input" style="flex:1" name="q" value="{{ request('q') }}" placeholder="Cari barang" autocomplete="off">
        <button class="ops-btn ops-btn-primary" type="submit">Cari</button>
    </form>
    <div class="ops-grid" data-ops-mobile-cards>
        @forelse($items as $item)
            <article class="ops-card">
                <h2>{{ $item->name }}</h2>
                @if($item->description)<p class="ops-muted">{{ $item->description }}</p>@endif
                <p><span class="ops-badge">Stok {{ $item->stock_qty }} {{ $item->unit_name }}</span></p>
                <form method="POST" action="{{ route('v2.ops.cart.add') }}" class="ops-actions">
                    @csrf
                    <input type="hidden" name="atk_item_id" value="{{ $item->id }}">
                    <input class="ops-input" style="width:90px" type="number" name="qty" value="1" min="1" max="{{ $item->stock_qty }}" aria-label="Jumlah {{ $item->name }}">
                    <button class="ops-btn ops-btn-primary" type="submit" @disabled($item->stock_qty < 1)>Tambah</button>
                </form>
            </article>
        @empty
            <div class="ops-card ops-empty">Barang belum tersedia.</div>
        @endforelse
    </div>
    <x-pagination :items="$items" preserve-query />
</x-ops-app>
