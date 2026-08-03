<x-ops-app title="Request Masuk OPS">
    <div class="ops-header"><h1 class="ops-title">Request Masuk</h1><p class="ops-subtitle">Periksa pengajuan kebutuhan operasional.</p></div>
    <form method="GET" class="ops-card ops-form-grid" style="margin-bottom:14px">
        <input class="ops-input" name="q" value="{{ request('q') }}" placeholder="Cari nomor atau nama">
        <select class="ops-select" name="status"><option value="">Semua status</option><option value="PENDING" @selected(request('status') === 'PENDING')>Menunggu</option><option value="APPROVED" @selected(request('status') === 'APPROVED')>Disetujui</option><option value="PARTIAL" @selected(request('status') === 'PARTIAL')>Sebagian</option><option value="REJECTED" @selected(request('status') === 'REJECTED')>Ditolak</option></select>
        <button class="ops-btn ops-btn-primary" type="submit">Cari</button>
    </form>
    <div class="ops-grid" data-ops-mobile-cards>
        @forelse($requests as $request)
            <a class="ops-card" style="color:inherit;text-decoration:none" href="{{ route('v2.ops.admin.requests.show', $request) }}">
                <span class="ops-badge">{{ match($request->status) {'PENDING'=>'Menunggu','APPROVED'=>'Disetujui','PARTIAL'=>'Sebagian','REJECTED'=>'Ditolak',default=>$request->status} }}</span>
                <h2 style="margin-top:10px">{{ $request->request_number }}</h2>
                <p class="ops-muted">{{ $request->user_name_snapshot }} · {{ $request->items->count() }} barang</p>
                <p class="ops-muted">{{ $request->created_at->format('d/m/Y H:i') }}</p>
            </a>
        @empty
            <div class="ops-card ops-empty">Tidak ada request.</div>
        @endforelse
    </div>
    <x-pagination :items="$requests" preserve-query />
</x-ops-app>
