<x-ops-app title="Riwayat Stok OPS">
    <div class="ops-header">
        <h1 class="ops-title">Riwayat Stok</h1>
        <p class="ops-subtitle">Catatan stok masuk, keluar, dan penyesuaian barang OPS.</p>
    </div>

    <form method="GET" class="ops-card ops-form-grid ops-stock-movements-filter">
        <select class="ops-select" name="item_id">
            <option value="">Semua barang</option>
            @foreach($items as $item)
                <option value="{{ $item->id }}" @selected((string) request('item_id') === (string) $item->id)>{{ $item->name }}</option>
            @endforeach
        </select>
        <select class="ops-select" name="movement_type">
            <option value="">Semua tipe</option>
            <option value="IN" @selected(request('movement_type') === 'IN')>Masuk</option>
            <option value="OUT" @selected(request('movement_type') === 'OUT')>Keluar</option>
            <option value="ADJUSTMENT" @selected(request('movement_type') === 'ADJUSTMENT')>Penyesuaian</option>
        </select>
        <div class="ops-actions">
            <button class="ops-btn ops-btn-primary" type="submit">Filter</button>
            <a class="ops-btn ops-btn-soft" href="{{ route('v2.ops.admin.stock-movements.index') }}">Reset</a>
        </div>
    </form>

    <div class="ops-stock-movements-table-wrap ops-stock-movements-mobile-table">
        <table class="ops-stock-movements-table">
            <thead><tr><th>Tanggal</th><th>Barang</th><th>Tipe</th><th>Qty</th><th>PT</th><th>Nama Pengambil</th><th>Diproses Oleh</th><th>Stok</th></tr></thead>
            <tbody>
                @forelse($movements as $movement)
                    @php
                        $typeLabel = match ($movement->movement_type) {
                            'IN' => ['Masuk', 'success'],
                            'OUT' => ['Keluar', 'error'],
                            'ADJUSTMENT' => ['Penyesuaian', 'warning'],
                            default => [$movement->movement_type, 'neutral'],
                        };
                        $sourceRequest = $movement->source_type === \App\Models\AtkStockMovement::SOURCE_REQUEST
                            ? $requestSources->get($movement->source_id)
                            : null;
                    @endphp
                    <tr class="ops-stock-movement-card">
                        <td class="ops-stock-movement-date" data-label="Tanggal">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="ops-stock-movement-item" data-label="Barang"><strong>{{ $movement->item?->name ?? '-' }}</strong></td>
                        <td class="ops-stock-movement-type" data-label="Tipe"><span class="ops-badge ops-badge-{{ $typeLabel[1] }}">{{ $typeLabel[0] }}</span></td>
                        <td class="ops-stock-movement-qty" data-label="Jumlah">{{ $movement->qty }} {{ $movement->item?->unit_name }}</td>
                        <td class="ops-stock-movement-pt" data-label="PT">{{ $sourceRequest?->pt_name_snapshot ?? '-' }}</td>
                        <td class="ops-stock-movement-requester" data-label="Nama Pengambil">{{ $sourceRequest?->user_name_snapshot ?? '-' }}</td>
                        <td class="ops-stock-movement-processor" data-label="Diproses Oleh">{{ $movement->createdBy?->name ?? '-' }}</td>
                        <td class="ops-stock-movement-stock" data-label="Perubahan Stok">{{ $movement->stock_before }} → {{ $movement->stock_after }}</td>
                    </tr>
                @empty
                    <tr class="ops-stock-movements-empty"><td colspan="8">Belum ada riwayat stok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :items="$movements" preserve-query />

    <style>
        .ops-stock-movements-filter { margin-bottom:14px; }
        .ops-stock-movements-table-wrap { overflow-x:auto; border:1px solid var(--ops-border); border-radius:18px; background:#fff; box-shadow:0 1px 3px rgba(17,24,39,.04); }
        .ops-stock-movements-table { width:100%; min-width:980px; border-collapse:collapse; font-size:12px; }
        .ops-stock-movements-table th,.ops-stock-movements-table td { padding:13px 14px; border-bottom:1px solid var(--ops-border); text-align:left; vertical-align:top; }
        .ops-stock-movements-table th { color:var(--ops-muted); font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; white-space:nowrap; }
        .ops-stock-movements-table tbody tr:last-child td { border-bottom:0; }
        .ops-badge-success { color:var(--ops-dark); background:var(--ops-soft); }
        .ops-badge-error { color:#991B1B; background:#FEE2E2; }
        .ops-badge-warning { color:#92400E; background:#FEF3C7; }
        .ops-badge-neutral { color:var(--ops-muted); background:#F3F4F6; }
        .ops-stock-movements-empty td { padding:28px 16px; color:var(--ops-muted); text-align:center; }

        @media (max-width:639px) {
            .ops-stock-movements-filter { padding:12px; }
            .ops-stock-movements-filter .ops-actions { display:grid; grid-template-columns:1fr 1fr; }
            .ops-stock-movements-filter .ops-btn { width:100%; }
            .ops-stock-movements-mobile-table { overflow:visible; border:0; border-radius:0; background:transparent; box-shadow:none; }
            .ops-stock-movements-table { display:block; min-width:0; }
            .ops-stock-movements-table thead { display:none; }
            .ops-stock-movements-table tbody { display:grid; gap:12px; }
            .ops-stock-movement-card { display:grid; grid-template-columns:minmax(0,1fr) auto; grid-template-areas:"item type" "date date" "qty qty" "pt pt" "requester requester" "processor processor" "stock stock"; gap:0 10px; padding:14px; border:1px solid var(--ops-border); border-radius:14px; background:#fff; box-shadow:0 1px 3px rgba(17,24,39,.04); }
            .ops-stock-movement-card td { display:grid; grid-template-columns:minmax(100px,.7fr) minmax(0,1fr); gap:10px; padding:7px 0; border:0; font-size:12px; overflow-wrap:anywhere; }
            .ops-stock-movement-card td::before { content:attr(data-label); color:var(--ops-muted); font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
            .ops-stock-movement-item { grid-area:item; display:block!important; padding-top:0!important; padding-bottom:12px!important; }
            .ops-stock-movement-item::before { display:block; margin-bottom:5px; }
            .ops-stock-movement-type { grid-area:type; display:block!important; padding-top:0!important; }
            .ops-stock-movement-type::before { display:none; }
            .ops-stock-movement-date { grid-area:date; padding-top:12px!important; border-top:1px solid var(--ops-border)!important; }
            .ops-stock-movement-qty { grid-area:qty; }
            .ops-stock-movement-pt { grid-area:pt; }
            .ops-stock-movement-requester { grid-area:requester; }
            .ops-stock-movement-processor { grid-area:processor; }
            .ops-stock-movement-stock { grid-area:stock; padding-bottom:0!important; font-weight:700; }
            .ops-stock-movements-empty { display:block; padding:18px; border:1px solid var(--ops-border); border-radius:14px; background:#fff; text-align:center; }
            .ops-stock-movements-empty td { display:block; padding:0; border:0; }
        }
    </style>
</x-ops-app>
