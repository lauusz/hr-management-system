<x-atk-app title="Master Barang ATK">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Master Barang</h1>
            <p class="atk-subtitle">Kelola barang, gambar, satuan, dan stok.</p>
        </div>
        <a class="atk-btn atk-btn-primary atk-admin-items-create" href="{{ route('v2.atk.admin.items.create') }}">Tambah Barang</a>
    </div>
    <form method="GET" class="atk-card atk-form-grid" style="margin-bottom:14px">
        <input class="atk-input" name="q" value="{{ request('q') }}" placeholder="Cari barang" autocomplete="off">
        <select class="atk-select" name="stock">
            <option value="">Semua stok</option>
            <option value="out" @selected(request('stock') === 'out')>Habis</option>
            <option value="low" @selected(request('stock') === 'low')>Menipis</option>
        </select>
        <div class="atk-actions">
            <button class="atk-btn atk-btn-primary" type="submit">Filter</button>
            <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.items.index') }}">Reset</a>
        </div>
    </form>
    <div class="atk-table-wrap atk-admin-items-mobile-table">
        <table class="atk-table atk-admin-items-table">
            <thead><tr><th>Barang</th><th>Stok</th><th>Satuan</th><th>Tambah Stok</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($items as $item)
                    <tr class="atk-admin-item-card">
                        <td class="atk-admin-item-name" data-label="Barang"><strong>{{ $item->name }}</strong></td>
                        <td class="atk-admin-item-stock" data-label="Stok">
                            <div class="atk-stock-cell">
                                <span class="atk-stock-row">
                                    <span class="atk-stock-key">Tersedia</span>
                                    @if($item->stock_status === 'OUT')
                                        <span class="atk-badge atk-badge-neutral">Habis</span>
                                    @elseif($item->stock_status === 'LOW')
                                        <span class="atk-badge atk-badge-warning">{{ $item->stock_with_unit }}</span>
                                    @else
                                        <span class="atk-badge atk-badge-success">{{ $item->stock_with_unit }}</span>
                                    @endif
                                </span>
                                <span class="atk-stock-row atk-stock-sub">
                                    Min. ambil {{ $item->min_request_qty }} {{ $item->unit_name }}
                                </span>
                            </div>
                        </td>
                        <td class="atk-unit-cell atk-admin-item-unit" data-label="Satuan">{{ $item->unit_conversion_label }}</td>
                        <td class="atk-admin-item-restock" data-label="Tambah Stok">
                            <form method="POST" action="{{ route('v2.atk.admin.items.stock.store', $item) }}" class="atk-actions atk-admin-item-stock-form">
                                @csrf
                                <input type="hidden" name="movement_type" value="IN">
                                <div class="atk-stock-stepper" data-stock-stepper>
                                    <button class="atk-stock-stepper-btn" type="button" data-stock-stepper-decrease aria-label="Kurangi stok masuk {{ $item->name }}" aria-disabled="true" disabled>&minus;</button>
                                    <input class="atk-stock-stepper-input" type="number" min="1" name="qty" value="1" inputmode="numeric" aria-label="Jumlah stok masuk {{ $item->name }}" data-stock-stepper-input>
                                    <button class="atk-stock-stepper-btn" type="button" data-stock-stepper-increase aria-label="Tambah stok masuk {{ $item->name }}" aria-disabled="false">+</button>
                                </div>
                                <input class="atk-input" style="width:130px" type="number" min="0" name="unit_price" placeholder="Harga/unit" aria-label="Harga masuk per {{ $item->unit_name }}">
                                <button class="atk-btn atk-btn-secondary" type="submit">Tambah</button>
                            </form>
                        </td>
                        <td class="atk-admin-item-actions" data-label="Aksi"><a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.items.edit', $item) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr class="atk-admin-items-empty"><td colspan="5">Belum ada barang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :items="$items" preserve-query />
    <script>
        (function () {
            const table = document.querySelector('.atk-admin-items-table');
            if (!table) return;

            function syncStepper(stepper) {
                const input = stepper?.querySelector('[data-stock-stepper-input]');
                const decrease = stepper?.querySelector('[data-stock-stepper-decrease]');
                if (!input || !decrease) return;
                const disabled = !Number.isFinite(Number(input.value)) || Number(input.value) <= 1;
                decrease.disabled = disabled;
                decrease.setAttribute('aria-disabled', disabled ? 'true' : 'false');
            }

            table.addEventListener('click', function (event) {
                const button = event.target.closest('[data-stock-stepper-decrease], [data-stock-stepper-increase]');
                if (!button) return;
                const stepper = button.closest('[data-stock-stepper]');
                const input = stepper.querySelector('[data-stock-stepper-input]');
                button.hasAttribute('data-stock-stepper-decrease') ? input.stepDown() : input.stepUp();
                syncStepper(stepper);
            });

            table.addEventListener('change', function (event) {
                if (!event.target.matches('[data-stock-stepper-input]')) return;
                if (!Number.isFinite(Number(event.target.value)) || Number(event.target.value) < 1) event.target.value = 1;
                syncStepper(event.target.closest('[data-stock-stepper]'));
            });

            table.querySelectorAll('[data-stock-stepper]').forEach(syncStepper);
        })();
    </script>
    <style>
        .atk-stock-cell {
            display: flex;
            flex-direction: column;
            gap: 4px;
            white-space: nowrap;
        }
        .atk-stock-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .atk-stock-key {
            font-size: 11px;
            font-weight: 700;
            color: var(--atk-muted);
        }
        .atk-stock-sub {
            font-size: 11px;
            font-weight: 600;
            color: var(--atk-muted);
        }
        .atk-unit-cell {
            white-space: nowrap;
            color: var(--atk-muted);
            font-size: 12px;
        }
        .atk-stock-stepper {
            display: grid;
            grid-template-columns: 40px minmax(48px, 1fr) 40px;
            width: 150px;
            min-height: 44px;
            overflow: hidden;
            border: 1px solid var(--atk-border);
            border-radius: 12px;
            background: var(--atk-surface);
        }
        .atk-stock-stepper-btn,
        .atk-stock-stepper-input {
            min-width: 0;
            min-height: 44px;
            border: 0;
            background: transparent;
            color: var(--atk-text);
            font: inherit;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
        }
        .atk-stock-stepper-btn { padding: 0; color: var(--atk-primary-dark); cursor: pointer; }
        .atk-stock-stepper-btn:first-child { border-right: 1px solid var(--atk-border-soft); }
        .atk-stock-stepper-btn:last-child { border-left: 1px solid var(--atk-border-soft); }
        .atk-stock-stepper-input { width: 100%; padding: 0 2px; appearance: textfield; -moz-appearance: textfield; }
        .atk-stock-stepper-input::-webkit-inner-spin-button,
        .atk-stock-stepper-input::-webkit-outer-spin-button { margin: 0; appearance: none; }
        .atk-stock-stepper-btn:focus-visible,
        .atk-stock-stepper-input:focus-visible { position: relative; z-index: 1; outline: 2px solid var(--atk-primary); outline-offset: -2px; }
        .atk-stock-stepper-btn:disabled { color: var(--atk-muted); cursor: not-allowed; opacity: .5; }
        @media (max-width: 639px) {
            .atk-admin-items-create {
                width: 100%;
            }
            .atk-admin-items-mobile-table {
                overflow: visible;
                border: 0;
                border-radius: 0;
                background: transparent;
            }
            .atk-admin-items-table {
                display: block;
                min-width: 0;
            }
            .atk-admin-items-table thead {
                display: none;
            }
            .atk-admin-items-table tbody {
                display: grid;
                gap: 12px;
            }
            .atk-admin-item-card {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                grid-template-areas:
                    "name stock"
                    "unit unit"
                    "restock restock"
                    "actions actions";
                gap: 0 10px;
                padding: 14px;
                border: 1px solid var(--atk-border);
                border-radius: 14px;
                background: var(--atk-surface);
                box-shadow: var(--atk-shadow);
            }
            .atk-admin-item-card td {
                display: grid;
                grid-template-columns: minmax(82px, .65fr) minmax(0, 1fr);
                gap: 10px;
                padding: 8px 0;
                border: 0;
                font-size: 12px;
            }
            .atk-admin-item-card td::before {
                content: attr(data-label);
                color: var(--atk-muted);
                font-size: 10px;
                font-weight: 800;
                letter-spacing: .04em;
                text-transform: uppercase;
            }
            .atk-admin-item-name {
                grid-area: name;
                display: block !important;
                padding-top: 0 !important;
            }
            .atk-admin-item-name::before {
                display: block;
                margin-bottom: 5px;
            }
            .atk-admin-item-stock {
                grid-area: stock;
                display: block !important;
                padding-top: 0 !important;
            }
            .atk-admin-item-stock::before,
            .atk-admin-item-stock .atk-stock-key {
                display: none;
            }
            .atk-admin-item-stock .atk-stock-row {
                justify-content: flex-end;
            }
            .atk-admin-item-stock .atk-stock-sub {
                margin-top: 5px;
                text-align: right;
                white-space: normal;
            }
            .atk-admin-item-unit { grid-area: unit; }
            .atk-admin-item-restock {
                grid-area: restock;
                display: block !important;
                margin-top: 4px;
                border-top: 1px solid var(--atk-border-soft) !important;
            }
            .atk-admin-item-restock::before,
            .atk-admin-item-actions::before {
                display: block;
                margin: 10px 0 8px;
            }
            .atk-admin-item-stock-form {
                display: grid;
                grid-template-columns: minmax(150px, .9fr) minmax(0, 1fr);
                gap: 8px;
                width: 100%;
            }
            .atk-admin-item-stock-form .atk-stock-stepper { width: 100%; }
            .atk-admin-item-stock-form .atk-input {
                width: 100% !important;
            }
            .atk-admin-item-stock-form .atk-btn {
                grid-column: 1 / -1;
                width: 100%;
            }
            .atk-admin-item-actions {
                grid-area: actions;
                display: block !important;
                padding-bottom: 0 !important;
            }
            .atk-admin-item-actions .atk-btn {
                width: 100%;
            }
            .atk-admin-items-empty {
                display: block;
                padding: 18px;
                border: 1px solid var(--atk-border);
                border-radius: 14px;
                background: var(--atk-surface);
                text-align: center;
            }
            .atk-admin-items-empty td {
                display: block;
                padding: 0;
                border: 0;
            }
        }
    </style>
</x-atk-app>
