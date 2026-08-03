<x-ops-app title="Katalog OPS">
    <div class="ops-header"><h1 class="ops-title">Katalog</h1><p class="ops-subtitle">Pilih barang yang dibutuhkan.</p></div>
    <form method="GET" class="ops-card ops-actions" style="margin-bottom:14px">
        <input class="ops-input" style="flex:1" name="q" value="{{ request('q') }}" placeholder="Cari barang" autocomplete="off">
        <button class="ops-btn ops-btn-primary" type="submit">Cari</button>
    </form>
    <div class="ops-grid" data-ops-mobile-cards>
        @forelse($items as $item)
            <article class="ops-card">
                <div class="ops-product-media">
                    @if($item->image_path)
                        <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->name }}" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                        <span hidden>Tanpa foto</span>
                    @else
                        <span>Tanpa foto</span>
                    @endif
                </div>
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
    <style>
        .ops-product-media { display:flex; align-items:center; justify-content:center; width:100%; aspect-ratio:4/3; margin-bottom:12px; overflow:hidden; border-radius:14px; background:var(--ops-soft); color:var(--ops-muted); font-size:12px; font-weight:700; }
        .ops-product-media img { width:100%; height:100%; object-fit:cover; }
    </style>
</x-ops-app>
