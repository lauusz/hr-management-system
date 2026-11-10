<x-inv-pesona-app :title="$title">
    <h1 class="pesona-title">{{ $title }}</h1>
    <p class="pesona-subtitle">{{ request()->routeIs('inventory.pesona.admin.dashboard') ? 'Area pengelolaan Inventory Pesona.' : 'Pengelolaan persediaan barang garasi PT Pesona.' }}</p>
    @if(request()->routeIs('inventory.pesona.admin.dashboard'))
        <nav aria-label="Fitur admin Inventory Pesona" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:18px;">
            <a class="pesona-button" style="text-decoration:none" href="{{ route('inventory.pesona.admin.vehicles.index') }}">Daftar Nopol</a>
            <a class="pesona-button" style="text-decoration:none" href="{{ route('inventory.pesona.admin.items.index') }}">Master Barang</a>
            <a class="pesona-button" style="text-decoration:none" href="{{ route('inventory.pesona.admin.stock-movements.index') }}">Riwayat Stok</a>
            <a class="pesona-button" style="text-decoration:none" href="{{ route('inventory.pesona.admin.requests.index') }}">Approval Pengambilan</a>
            <a class="pesona-button" style="text-decoration:none" href="{{ route('inventory.pesona.admin.access.index') }}">Akses</a>
        </nav>
    @endif
</x-inv-pesona-app>
