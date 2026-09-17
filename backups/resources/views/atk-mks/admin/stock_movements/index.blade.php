<x-atk-mks-app title="Riwayat Stok ATK MKS">
    <div class="atk-mks-header">
        <h1 class="atk-mks-title">Riwayat Stok</h1>
        <p class="atk-mks-subtitle">Catatan stok masuk, keluar, dan penyesuaian barang ATK MKS.</p>
    </div>

    <form method="GET" class="atk-mks-card atk-mks-form-grid atk-mks-stock-movements-filter">
        <select class="atk-mks-select" name="item_id">
            <option value="">Semua barang</option>
            @foreach($items as $item)
                <option value="{{ $item->id }}" @selected((string) request('item_id') === (string) $item->id)>{{ $item->name }}</option>
            @endforeach
        </select>
        <select class="atk-mks-select" name="movement_type">
            <option value="">Semua tipe</option>
            <option value="IN" @selected(request('movement_type') === 'IN')>Masuk</option>
            <option value="OUT" @selected(request('movement_type') === 'OUT')>Keluar</option>
            <option value="ADJUSTMENT" @selected(request('movement_type') === 'ADJUSTMENT')>Penyesuaian</option>
        </select>
        <div class="atk-mks-actions">
            <button class="atk-mks-btn atk-mks-btn-primary" type="submit">Filter</button>
            <a class="atk-mks-btn atk-mks-btn-soft" href="{{ route('v2.atk-mks.admin.stock-movements.index') }}">Reset</a>
        </div>
    </form>

    <div class="atk-mks-stock-movements-table-wrap atk-mks-stock-movements-mobile-table">
        <table class="atk-mks-stock-movements-table">
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
                    <tr class="atk-mks-stock-movement-card">
                        <td class="atk-mks-stock-movement-date" data-label="Tanggal">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="atk-mks-stock-movement-item" data-label="Barang"><strong>{{ $movement->item?->name ?? '-' }}</strong></td>
                        <td class="atk-mks-stock-movement-type" data-label="Tipe"><span class="atk-mks-badge atk-mks-badge-{{ $typeLabel[1] }}">{{ $typeLabel[0] }}</span></td>
                        <td class="atk-mks-stock-movement-qty" data-label="Jumlah">{{ $movement->qty }} {{ $movement->item?->unit_name }}</td>
                        <td class="atk-mks-stock-movement-pt" data-label="PT">{{ $sourceRequest?->pt_name_snapshot ?? '-' }}</td>
                        <td class="atk-mks-stock-movement-requester" data-label="Nama Pengambil">{{ $sourceRequest?->user_name_snapshot ?? '-' }}</td>
                        <td class="atk-mks-stock-movement-processor" data-label="Diproses Oleh">{{ $movement->createdBy?->name ?? '-' }}</td>
                        <td class="atk-mks-stock-movement-stock" data-label="Perubahan Stok">{{ $movement->stock_before }} → {{ $movement->stock_after }}</td>
                    </tr>
                @empty
                    <tr class="atk-mks-stock-movements-empty"><td colspan="8">Belum ada riwayat stok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :items="$movements" preserve-query />

    <style>
        .atk-mks-stock-movements-filter { margin-bottom:14px; }
        .atk-mks-stock-movements-table-wrap { overflow-x:auto; border:1px solid var(--atk-mks-border); border-radius:18px; background:#fff; box-shadow:0 1px 3px rgba(17,24,39,.04); }
        .atk-mks-stock-movements-table { width:100%; min-width:980px; border-collapse:collapse; font-size:12px; }
        .atk-mks-stock-movements-table th,.atk-mks-stock-movements-table td { padding:13px 14px; border-bottom:1px solid var(--atk-mks-border); text-align:left; vertical-align:top; }
        .atk-mks-stock-movements-table th { color:var(--atk-mks-muted); font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; white-space:nowrap; }
        .atk-mks-stock-movements-table tbody tr:last-child td { border-bottom:0; }
        .atk-mks-badge-success { color:var(--atk-mks-dark); background:var(--atk-mks-soft); }
        .atk-mks-badge-error { color:#991B1B; background:#FEE2E2; }
        .atk-mks-badge-warning { color:#92400E; background:#FEF3C7; }
        .atk-mks-badge-neutral { color:var(--atk-mks-muted); background:#F3F4F6; }
        .atk-mks-stock-movements-empty td { padding:28px 16px; color:var(--atk-mks-muted); text-align:center; }

        @media (max-width:639px) {
            .atk-mks-stock-movements-filter { padding:12px; }
            .atk-mks-stock-movements-filter .atk-mks-actions { display:grid; grid-template-columns:1fr 1fr; }
            .atk-mks-stock-movements-filter .atk-mks-btn { width:100%; }
            .atk-mks-stock-movements-mobile-table { overflow:visible; border:0; border-radius:0; background:transparent; box-shadow:none; }
            .atk-mks-stock-movements-table { display:block; min-width:0; }
            .atk-mks-stock-movements-table thead { display:none; }
            .atk-mks-stock-movements-table tbody { display:grid; gap:12px; }
            .atk-mks-stock-movement-card { display:grid; grid-template-columns:minmax(0,1fr) auto; grid-template-areas:"item type" "date date" "qty qty" "pt pt" "requester requester" "processor processor" "stock stock"; gap:0 10px; padding:14px; border:1px solid var(--atk-mks-border); border-radius:14px; background:#fff; box-shadow:0 1px 3px rgba(17,24,39,.04); }
            .atk-mks-stock-movement-card td { display:grid; grid-template-columns:minmax(100px,.7fr) minmax(0,1fr); gap:10px; padding:7px 0; border:0; font-size:12px; overflow-wrap:anywhere; }
            .atk-mks-stock-movement-card td::before { content:attr(data-label); color:var(--atk-mks-muted); font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
            .atk-mks-stock-movement-item { grid-area:item; display:block!important; padding-top:0!important; padding-bottom:12px!important; }
            .atk-mks-stock-movement-item::before { display:block; margin-bottom:5px; }
            .atk-mks-stock-movement-type { grid-area:type; display:block!important; padding-top:0!important; }
            .atk-mks-stock-movement-type::before { display:none; }
            .atk-mks-stock-movement-date { grid-area:date; padding-top:12px!important; border-top:1px solid var(--atk-mks-border)!important; }
            .atk-mks-stock-movement-qty { grid-area:qty; }
            .atk-mks-stock-movement-pt { grid-area:pt; }
            .atk-mks-stock-movement-requester { grid-area:requester; }
            .atk-mks-stock-movement-processor { grid-area:processor; }
            .atk-mks-stock-movement-stock { grid-area:stock; padding-bottom:0!important; font-weight:700; }
            .atk-mks-stock-movements-empty { display:block; padding:18px; border:1px solid var(--atk-mks-border); border-radius:14px; background:#fff; text-align:center; }
            .atk-mks-stock-movements-empty td { display:block; padding:0; border:0; }
        }
    </style>
</x-atk-mks-app>


