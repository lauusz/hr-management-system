<x-ops-app title="Pengajuan Saya">
    <div class="ops-header"><h1 class="ops-title">Pengajuan Saya</h1><p class="ops-subtitle">Lihat status barang yang diajukan.</p></div>
    <div class="ops-grid" data-ops-mobile-cards>
        @forelse($requests as $request)
            @php($status = match($request->status) {'PENDING'=>'Menunggu','APPROVED'=>'Disetujui','PARTIAL'=>'Sebagian','REJECTED'=>'Ditolak',default=>$request->status})
            <a class="ops-card" style="color:inherit;text-decoration:none" href="{{ route('v2.ops.requests.show', $request) }}">
                <span class="ops-badge">{{ $status }}</span>
                <h2 style="margin-top:10px">{{ $request->request_number }}</h2>
                <p class="ops-muted">{{ $request->created_at->format('d/m/Y') }} · {{ $request->items->count() }} barang</p>
            </a>
        @empty
            <div class="ops-card ops-empty">Belum ada pengajuan.</div>
        @endforelse
    </div>
    <x-pagination :items="$requests" preserve-query />
</x-ops-app>
