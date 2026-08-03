<x-ops-app title="Keranjang OPS">
    <div class="ops-header"><h1 class="ops-title">Keranjang</h1><p class="ops-subtitle">Periksa jumlah sebelum mengajukan.</p></div>
    <div class="ops-grid" data-ops-mobile-cards>
        @forelse($cartRows as $row)
            <article class="ops-card">
                <h2>{{ $row['item']->name }}</h2>
                <p class="ops-muted">Stok tersedia: {{ $row['item']->stock_qty }} {{ $row['item']->unit_name }}</p>
                <div class="ops-actions">
                    <form method="POST" action="{{ route('v2.ops.cart.update', $row['item']) }}" class="ops-actions">
                        @csrf @method('PUT')
                        <input class="ops-input" style="width:90px" type="number" name="qty" value="{{ $row['qty'] }}" min="1" max="{{ $row['item']->stock_qty }}">
                        <button class="ops-btn ops-btn-soft" type="submit">Ubah</button>
                    </form>
                    <form method="POST" action="{{ route('v2.ops.cart.remove', $row['item']) }}">
                        @csrf @method('DELETE')
                        <button class="ops-btn ops-btn-danger" type="submit">Hapus</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="ops-card ops-empty">Keranjang masih kosong.</div>
        @endforelse
    </div>
    @if($cartRows->isNotEmpty())
        <form method="POST" action="{{ route('v2.ops.cart.submit') }}" class="ops-card" style="margin-top:14px">
            @csrf
            <label class="ops-label" for="notes">Catatan (opsional)</label>
            <textarea class="ops-textarea" id="notes" name="notes" maxlength="1000"></textarea>
            <button class="ops-btn ops-btn-primary" style="margin-top:12px;width:100%" type="submit">Ajukan</button>
        </form>
    @endif
</x-ops-app>
