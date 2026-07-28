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
    <div class="atk-table-wrap atk-stock-movements-mobile-table">
        <table class="atk-table atk-stock-movements-table">
            <thead><tr><th>Tanggal</th><th>Barang</th><th>Tipe</th><th>Qty</th><th>PT</th><th>Nama Pengambil</th><th>Diproses Oleh</th><th>Stok</th></tr></thead>
            <tbody>
                @forelse($movements as $movement)
                    @php
                        $typeLabel = match ($movement->movement_type) {
                            'IN' => ['Masuk', 'success'],
                            'OUT' => ['Keluar', 'error'],
                            'ADJUSTMENT' => ['Adjustment', 'warning'],
                            default => [$movement->movement_type, 'neutral'],
                        };
                        $sourceRequest = $movement->source_type === \App\Models\AtkStockMovement::SOURCE_REQUEST
                            ? $requestSources->get($movement->source_id)
                            : null;
                    @endphp
                    <tr class="atk-stock-movement-card">
                        <td class="atk-stock-movement-date" data-label="Tanggal">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="atk-stock-movement-item" data-label="Barang"><strong>{{ $movement->item?->name ?? '-' }}</strong></td>
                        <td class="atk-stock-movement-type" data-label="Tipe"><span class="atk-badge atk-badge-{{ $typeLabel[1] }}">{{ $typeLabel[0] }}</span></td>
                        <td class="atk-stock-movement-qty" data-label="Jumlah">{{ $movement->qty }} {{ $movement->item?->unit_name }}</td>
                        <td class="atk-stock-movement-pt" data-label="PT">{{ $sourceRequest?->pt_name_snapshot ?? '-' }}</td>
                        <td class="atk-stock-movement-requester" data-label="Nama Pengambil">{{ $sourceRequest?->user_name_snapshot ?? '-' }}</td>
                        <td class="atk-stock-movement-processor" data-label="Diproses Oleh">{{ $movement->createdBy?->name ?? '-' }}</td>
                        <td class="atk-stock-movement-stock" data-label="Perubahan Stok">{{ $movement->stock_before }} → {{ $movement->stock_after }}</td>
                    </tr>
                @empty
                    <tr class="atk-stock-movements-empty"><td colspan="8">Belum ada riwayat stok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :items="$movements" preserve-query />

    <style>
        @media (max-width: 639px) {
            .atk-stock-movements-mobile-table {
                overflow: visible;
                border: 0;
                border-radius: 0;
                background: transparent;
            }
            .atk-stock-movements-table {
                display: block;
                min-width: 0;
            }
            .atk-stock-movements-table thead {
                display: none;
            }
            .atk-stock-movements-table tbody {
                display: grid;
                gap: 12px;
            }
            .atk-stock-movement-card {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                grid-template-areas:
                    "item type"
                    "date date"
                    "qty qty"
                    "pt pt"
                    "requester requester"
                    "processor processor"
                    "stock stock";
                gap: 0 10px;
                padding: 14px;
                border: 1px solid var(--atk-border);
                border-radius: 14px;
                background: var(--atk-surface);
                box-shadow: var(--atk-shadow);
            }
            .atk-stock-movement-card td {
                display: grid;
                grid-template-columns: minmax(100px, .7fr) minmax(0, 1fr);
                gap: 10px;
                padding: 7px 0;
                border: 0;
                font-size: 12px;
                overflow-wrap: anywhere;
            }
            .atk-stock-movement-card td::before {
                content: attr(data-label);
                color: var(--atk-muted);
                font-size: 10px;
                font-weight: 800;
                letter-spacing: .04em;
                text-transform: uppercase;
            }
            .atk-stock-movement-item {
                grid-area: item;
                display: block !important;
                padding-top: 0 !important;
                padding-bottom: 12px !important;
            }
            .atk-stock-movement-item::before {
                display: block;
                margin-bottom: 5px;
            }
            .atk-stock-movement-type {
                grid-area: type;
                display: block !important;
                padding-top: 0 !important;
            }
            .atk-stock-movement-type::before {
                display: none;
            }
            .atk-stock-movement-date {
                grid-area: date;
                border-top: 1px solid var(--atk-border-soft) !important;
                padding-top: 12px !important;
            }
            .atk-stock-movement-qty { grid-area: qty; }
            .atk-stock-movement-pt { grid-area: pt; }
            .atk-stock-movement-requester { grid-area: requester; }
            .atk-stock-movement-processor { grid-area: processor; }
            .atk-stock-movement-stock {
                grid-area: stock;
                font-weight: 700;
                padding-bottom: 0 !important;
            }
            .atk-stock-movements-empty {
                display: block;
                padding: 18px;
                border: 1px solid var(--atk-border);
                border-radius: 14px;
                background: var(--atk-surface);
                text-align: center;
            }
            .atk-stock-movements-empty td {
                display: block;
                padding: 0;
                border: 0;
            }
        }
    </style>
</x-atk-app>
