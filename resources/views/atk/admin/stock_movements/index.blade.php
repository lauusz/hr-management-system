<x-atk-app title="Riwayat Stok ATK">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Riwayat Stok</h1>
            <p class="atk-subtitle">Catatan stok masuk, keluar, dan adjustment.</p>
        </div>
    </div>
    <form method="GET" class="atk-card atk-form-grid" style="margin-bottom:14px">
        <select class="atk-select" name="item_id">
            <option value="">Semua barang</option>
            @foreach($items as $item)
                <option value="{{ $item->id }}" @selected((string) request('item_id') === (string) $item->id)>{{ $item->name }}</option>
            @endforeach
        </select>
        <select class="atk-select" name="movement_type">
            <option value="">Semua tipe</option>
            <option value="IN" @selected(request('movement_type') === 'IN')>Masuk</option>
            <option value="OUT" @selected(request('movement_type') === 'OUT')>Keluar</option>
            <option value="ADJUSTMENT" @selected(request('movement_type') === 'ADJUSTMENT')>Adjustment</option>
        </select>
        <div class="atk-actions">
            <button class="atk-btn atk-btn-primary" type="submit">Filter</button>
            <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.stock-movements.index') }}">Reset</a>
        </div>
    </form>
    <div class="atk-table-wrap">
        <table class="atk-table">
            <thead><tr><th>Tanggal</th><th>Barang</th><th>Tipe</th><th>Qty</th><th>Harga</th><th>Total</th><th>Sumber</th><th>Stok</th><th>Catatan</th></tr></thead>
            <tbody>
                @forelse($movements as $movement)
                    @php
                        $typeLabel = match ($movement->movement_type) {
                            'IN' => ['Masuk', 'success'],
                            'OUT' => ['Keluar', 'error'],
                            'ADJUSTMENT' => ['Adjustment', 'warning'],
                            default => [$movement->movement_type, 'neutral'],
                        };
                    @endphp
                    <tr>
                        <td>{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                        <td><strong>{{ $movement->item?->name ?? '-' }}</strong></td>
                        <td><span class="atk-badge atk-badge-{{ $typeLabel[1] }}">{{ $typeLabel[0] }}</span></td>
                        <td>{{ $movement->qty }}</td>
                        <td>{{ $movement->unit_price === null ? '-' : 'Rp '.number_format($movement->unit_price, 0, ',', '.') }}</td>
                        <td>{{ $movement->total_price === null ? '-' : 'Rp '.number_format($movement->total_price, 0, ',', '.') }}</td>
                        <td>{{ $movement->source_label }}</td>
                        <td>{{ $movement->stock_before }} → {{ $movement->stock_after }}</td>
                        <td>{{ $movement->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9">Belum ada riwayat stok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :items="$movements" preserve-query />
</x-atk-app>
