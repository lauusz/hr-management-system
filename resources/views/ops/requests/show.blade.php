<x-ops-app title="Detail Pengajuan">
    @php($status = match($atkRequest->status) {'PENDING'=>'Menunggu','APPROVED'=>'Disetujui','PARTIAL'=>'Sebagian','REJECTED'=>'Ditolak',default=>$atkRequest->status})
    <div class="ops-header"><h1 class="ops-title">Detail Pengajuan</h1><p class="ops-subtitle">{{ $atkRequest->request_number }}</p></div>
    <div class="ops-card" style="margin-bottom:14px">
        <span class="ops-badge">{{ $status }}</span>
        <p class="ops-muted">Diajukan {{ $atkRequest->created_at->format('d/m/Y H:i') }}</p>
        @if($atkRequest->notes)<p>{{ $atkRequest->notes }}</p>@endif
    </div>
    <div class="ops-grid" data-ops-mobile-cards>
        @foreach($atkRequest->items as $item)
            <article class="ops-card">
                <h2>{{ $item->item_name_snapshot }}</h2>
                <p class="ops-muted">Jumlah: {{ $item->qty }} {{ $item->unit_name_snapshot }}</p>
                <span class="ops-badge">{{ match($item->status) {'PENDING'=>'Menunggu','APPROVED'=>'Disetujui','REJECTED'=>'Ditolak',default=>$item->status} }}</span>
                @if($item->admin_note)<p class="ops-muted">{{ $item->admin_note }}</p>@endif
            </article>
        @endforeach
    </div>
    <a class="ops-btn ops-btn-soft" style="margin-top:14px" href="{{ route('v2.ops.requests.index') }}">Kembali</a>
</x-ops-app>
