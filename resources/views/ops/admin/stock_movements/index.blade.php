<x-ops-app title="Riwayat Stok OPS">
    <div class="ops-header">
        <h1 class="ops-title">Riwayat Stok</h1>
        <p class="ops-subtitle">Catatan stok masuk dan keluar barang OPS.</p>
    </div>

    <form method="GET" class="ops-card ops-form-grid" style="margin-bottom:12px">
        <input class="ops-input" name="q" value="{{ request('q') }}" placeholder="Cari barang" autocomplete="off">
        <input class="ops-input" type="month" name="month" value="{{ request('month') }}">
        <div class="ops-actions">
            <button class="ops-btn ops-btn-primary" type="submit">Cari</button>
            <a class="ops-btn ops-btn-soft" href="{{ route('v2.ops.admin.stock-movements.index') }}">Reset</a>
        </div>
    </form>

    <div class="ops-grid" data-ops-mobile-cards>
        @forelse($movements as $movement)
            <article class="ops-card">
                <div class="ops-actions" style="justify-content:space-between">
                    <h2>{{ $movement->item?->name ?? 'Barang' }}</h2>
                    <span class="ops-badge">{{ match($movement->movement_type) { 'IN' => 'Stok Masuk', 'OUT' => 'Stok Keluar', default => 'Koreksi' } }}</span>
                </div>
                <p class="ops-muted">{{ $movement->created_at?->format('d M Y H:i') }}</p>
                <p><strong>{{ $movement->stock_before }} → {{ $movement->stock_after }}</strong> ({{ $movement->qty }})</p>
                <p class="ops-muted">Oleh: {{ $movement->createdBy?->name ?? '-' }}</p>
                @if($movement->notes)<p class="ops-muted">{{ $movement->notes }}</p>@endif
            </article>
        @empty
            <div class="ops-card ops-empty">Belum ada riwayat stok.</div>
        @endforelse
    </div>
    <x-pagination :items="$movements" preserve-query />
</x-ops-app>
