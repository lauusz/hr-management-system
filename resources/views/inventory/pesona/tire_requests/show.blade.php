<x-inv-pesona-app title="Detail Pengajuan Ban — Inventory Pesona">
    @include('inventory.pesona.admin.tires._style')
    <div class="tire-wrap">
        <div>
            <a class="tire-btn" href="{{ route('inventory.pesona.tire-requests.index') }}">← Pengajuan Ban</a>
            <h1 class="pesona-title" style="margin-top:10px;">Detail Pengajuan Ban</h1>
        </div>
        @include('inventory.pesona.tire_requests._detail')
    </div>
</x-inv-pesona-app>
