@php
    $stockFilter = request('stock', '');
    $stockTabs = [
        '' => 'Semua',
        'low' => 'Menipis',
        'out' => 'Habis',
    ];
@endphp
<x-atk-app title="Master Barang ATK">
    <div class="atk-items-toolbar">
        <div class="atk-items-heading">
            <h1 class="atk-title">Master Barang</h1>
            <span class="atk-items-total" id="atkItemsTotalCount">{{ $items->total() }} barang</span>
        </div>

        <form method="GET" action="{{ route('v2.atk.admin.items.index') }}" id="atkAdminItemsFilterForm" class="atk-items-controls">
            <div class="atk-items-search-wrap">
                <svg class="atk-items-search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <input class="atk-input atk-items-search-input" type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama barang..." autocomplete="off" data-items-live-search>
            </div>

            <div class="atk-items-stock-pills" role="group" aria-label="Filter stok">
                @foreach($stockTabs as $val => $label)
                    <button type="button" class="atk-stock-filter-pill {{ $stockFilter === $val ? 'active' : '' }}" data-stock-tab="{{ $val }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <select class="atk-select atk-items-stock-select" name="stock" aria-label="Filter status stok" onchange="this.form.submit()">
                <option value="" @selected($stockFilter === '')>Semua stok</option>
                <option value="low" @selected($stockFilter === 'low')>Menipis</option>
                <option value="out" @selected($stockFilter === 'out')>Habis</option>
            </select>

            @if(request()->filled('q') || request()->filled('stock'))
                <a class="atk-btn atk-btn-muted atk-items-reset-btn" href="{{ route('v2.atk.admin.items.index') }}">Reset</a>
            @endif

            <a class="atk-btn atk-btn-primary atk-admin-items-create" href="{{ route('v2.atk.admin.items.create') }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                <span>Tambah Barang</span>
            </a>
        </form>
    </div>

    <div id="atkAdminItemsResults">
        <div class="atk-table-wrap atk-admin-items-mobile-table">
            <table class="atk-table atk-admin-items-table">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Stok</th>
                        <th>Satuan</th>
                        <th>Tambah Stok</th>
                        <th style="width:1%;white-space:nowrap;text-align:right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr class="atk-admin-item-card {{ ! $item->is_active ? 'is-inactive' : '' }}">
                            <td class="atk-admin-item-name" data-label="Barang">
                                <div class="atk-item-identity">
                                    <a href="{{ route('v2.atk.admin.items.edit', $item) }}" class="atk-item-link">
                                        <strong>{{ $item->name }}</strong>
                                    </a>
                                    @if(! $item->is_active)
                                        <span class="atk-badge atk-badge-neutral">Nonaktif</span>
                                    @endif
                                </div>
                            </td>
                            <td class="atk-admin-item-stock" data-label="Stok">
                                <div class="atk-stock-cell">
                                    <span class="atk-stock-row">
                                        @if($item->stock_status === 'OUT')
                                            <span class="atk-badge atk-badge-neutral">Habis (0 {{ $item->unit_name }})</span>
                                        @elseif($item->stock_status === 'LOW')
                                            <span class="atk-badge atk-badge-warning">{{ $item->stock_with_unit }}</span>
                                        @else
                                            <span class="atk-badge atk-badge-success">{{ $item->stock_with_unit }}</span>
                                        @endif
                                    </span>
                                    <span class="atk-stock-sub">
                                        Min. ambil {{ $item->min_request_qty }} {{ $item->unit_name }}@if($item->minimum_stock > 0) · Min. stok {{ $item->minimum_stock }}@endif
                                    </span>
                                </div>
                            </td>
                            <td class="atk-unit-cell atk-admin-item-unit" data-label="Satuan">{{ $item->unit_conversion_label }}</td>
                            <td class="atk-admin-item-restock" data-label="Tambah Stok">
                                <form method="POST" action="{{ route('v2.atk.admin.items.stock.store', $item) }}" class="atk-admin-item-stock-form">
                                    @csrf
                                    <input type="hidden" name="movement_type" value="IN">
                                    <div class="atk-stock-stepper" data-stock-stepper>
                                        <button class="atk-stock-stepper-btn" type="button" data-stock-stepper-decrease aria-label="Kurangi stok masuk {{ $item->name }}" aria-disabled="true" disabled>&minus;</button>
                                        <input class="atk-stock-stepper-input" type="number" min="1" name="qty" value="1" inputmode="numeric" aria-label="Jumlah stok masuk {{ $item->name }}" data-stock-stepper-input>
                                        <button class="atk-stock-stepper-btn" type="button" data-stock-stepper-increase aria-label="Tambah stok masuk {{ $item->name }}" aria-disabled="false">+</button>
                                    </div>
                                    <input class="atk-input atk-price-input" type="number" min="0" name="unit_price" placeholder="Harga/unit" aria-label="Harga masuk per {{ $item->unit_name }}">
                                    <button class="atk-btn atk-btn-secondary atk-restock-btn" type="submit">+ Stok</button>
                                </form>
                            </td>
                            <td class="atk-admin-item-actions" data-label="Aksi">
                                <a class="atk-btn atk-btn-muted atk-edit-btn" href="{{ route('v2.atk.admin.items.edit', $item) }}">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="atk-admin-items-empty">
                            <td colspan="5" class="atk-empty">Belum ada barang yang sesuai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination :items="$items" preserve-query />
    </div>

    <script>
        (function () {
            const results = document.getElementById('atkAdminItemsResults');
            const form = document.getElementById('atkAdminItemsFilterForm');
            const searchInput = form?.querySelector('[data-items-live-search]');
            const stockSelect = form?.querySelector('select[name="stock"]');
            let timer;

            function syncStepper(stepper) {
                const input = stepper?.querySelector('[data-stock-stepper-input]');
                const decrease = stepper?.querySelector('[data-stock-stepper-decrease]');
                if (!input || !decrease) return;
                const disabled = !Number.isFinite(Number(input.value)) || Number(input.value) <= 1;
                decrease.disabled = disabled;
                decrease.setAttribute('aria-disabled', disabled ? 'true' : 'false');
            }

            function syncAllSteppers() {
                results?.querySelectorAll('[data-stock-stepper]').forEach(syncStepper);
            }

            function loadResults(url) {
                fetch(url.toString())
                    .then((r) => r.text())
                    .then((html) => {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const nextResults = doc.getElementById('atkAdminItemsResults');
                        const nextTotal = doc.getElementById('atkItemsTotalCount');
                        if (!nextResults) {
                            window.location.href = url.toString();
                            return;
                        }
                        results.innerHTML = nextResults.innerHTML;
                        if (nextTotal && document.getElementById('atkItemsTotalCount')) {
                            document.getElementById('atkItemsTotalCount').textContent = nextTotal.textContent;
                        }
                        syncAllSteppers();
                        window.history.replaceState(null, '', url.toString());
                    })
                    .catch(() => {
                        window.location.href = url.toString();
                    });
            }

            function triggerFilter() {
                if (!form) return;
                const url = new URL(form.action, window.location.origin);
                const params = new URLSearchParams(new FormData(form));
                if (!params.get('q')) params.delete('q');
                if (!params.get('stock')) params.delete('stock');
                url.search = params.toString();
                loadResults(url);
            }

            searchInput?.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(triggerFilter, 300);
            });

            form?.querySelectorAll('[data-stock-tab]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const val = btn.getAttribute('data-stock-tab') || '';
                    if (stockSelect) stockSelect.value = val;
                    form.querySelectorAll('[data-stock-tab]').forEach((b) => b.classList.toggle('active', b === btn));
                    triggerFilter();
                });
            });

            results?.addEventListener('click', function (event) {
                const button = event.target.closest('[data-stock-stepper-decrease], [data-stock-stepper-increase]');
                if (button) {
                    const stepper = button.closest('[data-stock-stepper]');
                    const input = stepper.querySelector('[data-stock-stepper-input]');
                    button.hasAttribute('data-stock-stepper-decrease') ? input.stepDown() : input.stepUp();
                    syncStepper(stepper);
                    return;
                }

                const pageLink = event.target.closest('.hrd-pagination a');
                if (pageLink) {
                    event.preventDefault();
                    loadResults(new URL(pageLink.href));
                }
            });

            results?.addEventListener('change', function (event) {
                if (!event.target.matches('[data-stock-stepper-input]')) return;
                if (!Number.isFinite(Number(event.target.value)) || Number(event.target.value) < 1) event.target.value = 1;
                syncStepper(event.target.closest('[data-stock-stepper]'));
            });

            syncAllSteppers();
        })();
    </script>

    <style>
        /* ========== TOOLBAR ========== */
        .atk-items-toolbar {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 14px;
        }
        .atk-items-heading {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .atk-items-total {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--atk-primary-dark);
            background: var(--atk-primary-soft);
            padding: 2px 9px;
            border-radius: 999px;
            white-space: nowrap;
        }
        .atk-items-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }
        .atk-items-search-wrap {
            position: relative;
            flex: 1 1 200px;
            min-width: 0;
        }
        .atk-items-search-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            pointer-events: none;
        }
        .atk-items-search-input {
            padding-left: 34px;
            height: 36px;
            min-height: 36px;
            border-radius: 9px;
        }
        .atk-items-stock-pills {
            display: none;
            align-items: center;
            gap: 3px;
            padding: 3px;
            background: var(--atk-surface);
            border: 1px solid var(--atk-border);
            border-radius: 9px;
        }
        .atk-stock-filter-pill {
            height: 28px;
            padding: 0 10px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: var(--atk-muted);
            font: inherit;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color .12s ease, color .12s ease;
        }
        .atk-stock-filter-pill:hover {
            color: var(--atk-text);
            background: var(--atk-bg);
        }
        .atk-stock-filter-pill.active {
            background: var(--atk-primary);
            color: #fff;
        }
        .atk-items-stock-select {
            flex: 1 1 130px;
            height: 36px;
            min-height: 36px;
            border-radius: 9px;
        }
        .atk-items-reset-btn,
        .atk-admin-items-create {
            height: 36px;
            min-height: 36px;
            border-radius: 9px;
            font-size: 12.5px;
            white-space: nowrap;
        }

        /* ========== TABLE ========== */
        .atk-admin-items-table {
            min-width: 780px;
        }
        .atk-admin-items-table th {
            background: #F9FAFB;
            padding: 9px 14px;
        }
        .atk-admin-items-table td {
            padding: 10px 14px;
            vertical-align: middle;
        }
        .atk-admin-items-table tbody tr:hover {
            background: #FAFAFC;
        }
        .atk-admin-item-card.is-inactive {
            background: #F9FAFB;
            opacity: .75;
        }

        .atk-item-identity {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .atk-item-link {
            color: var(--atk-text);
            text-decoration: none;
            font-size: 13px;
            line-height: 1.35;
        }
        .atk-item-link:hover {
            color: var(--atk-primary);
            text-decoration: underline;
        }

        .atk-stock-cell {
            display: flex;
            flex-direction: column;
            gap: 2px;
            white-space: nowrap;
        }
        .atk-stock-row {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .atk-stock-sub {
            font-size: 11px;
            color: var(--atk-muted);
        }
        .atk-unit-cell {
            white-space: nowrap;
            color: var(--atk-muted);
            font-size: 12px;
        }

        /* ========== COMPACT RESTOCK FORM ========== */
        .atk-admin-item-stock-form {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: nowrap;
        }
        .atk-stock-stepper {
            display: grid;
            grid-template-columns: 28px 42px 28px;
            width: 98px;
            height: 32px;
            overflow: hidden;
            border: 1px solid var(--atk-border);
            border-radius: 8px;
            background: var(--atk-surface);
            flex-shrink: 0;
        }
        .atk-stock-stepper-btn,
        .atk-stock-stepper-input {
            min-width: 0;
            height: 100%;
            border: 0;
            background: transparent;
            color: var(--atk-text);
            font: inherit;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
        }
        .atk-stock-stepper-btn {
            padding: 0;
            color: var(--atk-primary-dark);
            cursor: pointer;
        }
        .atk-stock-stepper-btn:first-child { border-right: 1px solid var(--atk-border-soft); }
        .atk-stock-stepper-btn:last-child { border-left: 1px solid var(--atk-border-soft); }
        .atk-stock-stepper-btn:hover:not(:disabled) { background: var(--atk-primary-softer); }
        .atk-stock-stepper-input {
            width: 100%;
            padding: 0;
            appearance: textfield;
            -moz-appearance: textfield;
        }
        .atk-stock-stepper-input::-webkit-inner-spin-button,
        .atk-stock-stepper-input::-webkit-outer-spin-button {
            margin: 0;
            appearance: none;
        }
        .atk-stock-stepper-btn:focus-visible,
        .atk-stock-stepper-input:focus-visible {
            position: relative;
            z-index: 1;
            outline: 2px solid var(--atk-primary);
            outline-offset: -2px;
        }
        .atk-stock-stepper-btn:disabled {
            color: var(--atk-muted);
            cursor: not-allowed;
            opacity: .4;
        }
        .atk-price-input {
            width: 112px;
            height: 32px;
            min-height: 32px;
            padding: 0 9px;
            border-radius: 8px;
            font-size: 12px;
        }
        .atk-restock-btn,
        .atk-edit-btn {
            height: 32px;
            min-height: 32px;
            padding: 0 11px;
            border-radius: 8px;
            font-size: 12px;
            white-space: nowrap;
        }
        .atk-admin-item-actions {
            text-align: right;
        }

        /* ========== MOBILE CARDS ========== */
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
                gap: 10px;
            }
            .atk-admin-item-card {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                grid-template-areas:
                    "name stock"
                    "unit actions"
                    "restock restock";
                gap: 6px 10px;
                padding: 12px;
                border: 1px solid var(--atk-border);
                border-radius: 12px;
                background: var(--atk-surface);
            }
            .atk-admin-item-card td {
                padding: 0;
                border: 0;
                font-size: 12px;
            }
            .atk-admin-item-name {
                grid-area: name;
            }
            .atk-admin-item-stock {
                grid-area: stock;
                text-align: right;
            }
            .atk-admin-item-stock .atk-stock-row {
                justify-content: flex-end;
            }
            .atk-admin-item-stock .atk-stock-sub {
                white-space: normal;
            }
            .atk-admin-item-unit {
                grid-area: unit;
                align-self: center;
            }
            .atk-admin-item-actions {
                grid-area: actions;
                align-self: center;
            }
            .atk-admin-item-restock {
                grid-area: restock;
                padding-top: 8px !important;
                margin-top: 2px;
                border-top: 1px solid var(--atk-border-soft) !important;
            }
            .atk-admin-item-stock-form {
                display: grid;
                grid-template-columns: 98px minmax(0, 1fr) auto;
                gap: 6px;
                width: 100%;
            }
            .atk-price-input {
                width: 100%;
            }
            .atk-admin-items-empty {
                display: block;
                padding: 20px;
                border: 1px solid var(--atk-border);
                border-radius: 12px;
                background: var(--atk-surface);
                text-align: center;
            }
            .atk-admin-items-empty td {
                display: block;
                padding: 0;
                border: 0;
            }
        }

        @media (min-width: 768px) {
            .atk-items-toolbar {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
            }
            .atk-items-controls {
                flex: 1;
                justify-content: flex-end;
                flex-wrap: nowrap;
            }
            .atk-items-search-wrap {
                max-width: 260px;
            }
            .atk-items-stock-pills {
                display: inline-flex;
            }
            .atk-items-stock-select {
                display: none;
            }
        }
    </style>
</x-atk-app>
