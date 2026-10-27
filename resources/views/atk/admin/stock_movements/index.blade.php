<x-atk-app title="Riwayat Stok ATK">
    @php
        $currentItemId = (string) request('item_id', '');
        $currentModule = (string) request('module', '');
        $currentType = (string) request('movement_type', '');
        $currentSearch = (string) request('q', '');
        $typeTabs = [
            '' => 'Semua Tipe',
            'IN' => 'Masuk',
            'OUT' => 'Keluar',
            'ADJUSTMENT' => 'Adjustment',
        ];
    @endphp

    <style>
        .atk-stock-admin-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .atk-stock-admin-title-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .atk-stock-count-pill {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            background: #F1F5F9;
            color: #475569;
            font-size: 11.5px;
            font-weight: 700;
        }

        /* Filter & Search Bar */
        .atk-stock-filter-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            padding: 10px 12px;
            margin-bottom: 12px;
            background: #FFFFFF;
            border: 1px solid var(--atk-border);
            border-radius: 10px;
        }
        .atk-stock-filter-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            flex: 1 1 auto;
        }
        .atk-type-pills {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }
        .atk-type-pill {
            display: inline-flex;
            align-items: center;
            height: 32px;
            padding: 0 11px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            color: var(--atk-muted);
            text-decoration: none;
            border: 1px solid transparent;
            transition: all .14s ease;
        }
        .atk-type-pill:hover {
            color: var(--atk-text);
            background: #F8FAFC;
        }
        .atk-type-pill.is-active {
            background: #F1F5F9;
            color: var(--atk-text);
            border-color: #CBD5E1;
            font-weight: 700;
        }
        .atk-filter-select {
            height: 32px;
            min-height: 32px;
            padding: 0 28px 0 10px;
            border-radius: 7px;
            border: 1px solid var(--atk-border);
            background-color: #F8FAFC;
            font-size: 12px;
            font-weight: 600;
            color: var(--atk-text-secondary);
            width: auto;
            max-width: 220px;
        }
        .atk-filter-select:focus {
            background-color: #FFFFFF;
        }

        /* Search Input */
        .atk-stock-search-wrap {
            position: relative;
            flex: 1 1 240px;
            max-width: 320px;
        }
        .atk-stock-search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            pointer-events: none;
        }
        .atk-stock-search-input {
            width: 100%;
            height: 32px;
            padding: 0 28px 0 30px;
            border: 1px solid var(--atk-border);
            border-radius: 7px;
            font-size: 12.5px;
            color: var(--atk-text);
            background: #F8FAFC;
            outline: none;
            transition: border-color .14s ease, background .14s ease;
        }
        .atk-stock-search-input:focus {
            background: #FFFFFF;
            border-color: var(--atk-primary);
            box-shadow: 0 0 0 3px rgba(109, 40, 217, .12);
        }
        .atk-stock-search-clear {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            border: 0;
            border-radius: 4px;
            background: transparent;
            color: #94A3B8;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 0;
        }
        .atk-stock-search-clear:hover {
            color: var(--atk-text);
            background: #E2E8F0;
        }
        .atk-stock-search-clear.is-visible {
            display: inline-flex;
        }

        .atk-stock-results-wrap {
            transition: opacity .15s ease;
        }
        .atk-stock-results-wrap.is-loading {
            opacity: .55;
            pointer-events: none;
        }

        /* Table Refinements */
        .atk-stock-movement-date {
            white-space: nowrap;
            color: var(--atk-muted);
            font-size: 12px;
            font-weight: 500;
            font-variant-numeric: tabular-nums;
        }
        .atk-stock-movement-item strong {
            font-size: 13px;
            font-weight: 600;
            color: var(--atk-text);
        }
        .atk-stock-movement-qty {
            white-space: nowrap;
            font-weight: 600;
            color: var(--atk-text);
            font-variant-numeric: tabular-nums;
        }
        .atk-stock-movement-pt {
            color: var(--atk-muted);
            font-size: 12px;
            font-weight: 500;
        }
        .atk-stock-movement-requester {
            font-weight: 500;
            color: var(--atk-text);
            font-size: 12.5px;
        }
        .atk-stock-movement-processor {
            color: var(--atk-muted);
            font-size: 12px;
        }
        .atk-stock-movement-stock {
            white-space: nowrap;
            font-weight: 700;
            font-size: 12.5px;
            color: var(--atk-text);
            font-variant-numeric: tabular-nums;
        }

        @media (max-width: 720px) {
            .atk-stock-search-wrap {
                max-width: 100%;
                width: 100%;
            }
            .atk-filter-select {
                flex: 1 1 140px;
                max-width: 100%;
            }
        }

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
                gap: 10px;
            }
            .atk-stock-movement-card {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                grid-template-areas:
                    "item type"
                    "module module"
                    "date date"
                    "qty qty"
                    "pt pt"
                    "requester requester"
                    "processor processor"
                    "stock stock";
                gap: 0 10px;
                padding: 13px 14px;
                border: 1px solid var(--atk-border);
                border-radius: 10px;
                background: var(--atk-surface);
            }
            .atk-stock-movement-card td {
                display: grid;
                grid-template-columns: minmax(100px, .7fr) minmax(0, 1fr);
                gap: 10px;
                padding: 6px 0;
                border: 0;
                font-size: 12px;
                overflow-wrap: anywhere;
            }
            .atk-stock-movement-card td::before {
                content: attr(data-label);
                color: var(--atk-muted);
                font-size: 10px;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
            }
            .atk-stock-movement-item {
                grid-area: item;
                display: block !important;
                padding-top: 0 !important;
                padding-bottom: 10px !important;
            }
            .atk-stock-movement-item::before {
                display: none;
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
                padding-top: 10px !important;
            }
            .atk-stock-movement-qty { grid-area: qty; }
            .atk-stock-movement-module { grid-area: module; }
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
                border-radius: 10px;
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

    <div class="atk-stock-admin-bar">
        <div class="atk-stock-admin-title-group">
            <h1 class="atk-title">Riwayat Stok</h1>
            <span class="atk-stock-count-pill" id="stockMovementsCount">{{ $movements->total() }}</span>
        </div>
    </div>

    <form method="GET" action="{{ route('v2.atk.admin.stock-movements.index') }}" class="atk-stock-filter-bar" id="stockMovementsFilterForm">
        <input type="hidden" name="movement_type" id="stockMovementTypeInput" value="{{ $currentType }}">

        <div class="atk-stock-filter-controls">
            <div class="atk-type-pills" id="stockMovementTypePills" role="tablist" aria-label="Filter Tipe Mutasi">
                @foreach($typeTabs as $typeValue => $typeLabel)
                    <a
                        href="{{ route('v2.atk.admin.stock-movements.index', array_filter(['movement_type' => $typeValue, 'module' => $currentModule, 'item_id' => $currentItemId, 'q' => $currentSearch])) }}"
                        data-type="{{ $typeValue }}"
                        class="atk-type-pill {{ $currentType === $typeValue ? 'is-active' : '' }}"
                    >
                        {{ $typeLabel }}
                    </a>
                @endforeach
            </div>

            <select class="atk-select atk-filter-select" name="module" id="stockMovementModuleSelect" aria-label="Filter Modul">
                <option value="">Semua modul</option>
                <option value="ATK" @selected($currentModule === 'ATK')>ATK</option>
                <option value="OPS" @selected($currentModule === 'OPS')>OPS</option>
                <option value="ATK_MKS" @selected($currentModule === 'ATK_MKS')>ATK MKS</option>
            </select>

            <select class="atk-select atk-filter-select" name="item_id" id="stockMovementItemSelect" aria-label="Filter Barang">
                <option value="">Semua barang</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" @selected($currentItemId === (string) $item->id)>{{ $item->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="atk-stock-search-wrap">
            <svg class="atk-stock-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input
                class="atk-stock-search-input"
                id="stockMovementSearchInput"
                name="q"
                value="{{ $currentSearch }}"
                placeholder="Cari barang, pengambil, atau PT..."
                autocomplete="off"
            >
            <button
                type="button"
                class="atk-stock-search-clear {{ $currentSearch !== '' ? 'is-visible' : '' }}"
                id="stockMovementSearchClear"
                aria-label="Hapus pencarian"
            >
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
    </form>

    <div class="atk-stock-results-wrap" id="stockMovementsResults">
        <div class="atk-table-wrap atk-stock-movements-mobile-table">
            <table class="atk-table atk-stock-movements-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Barang</th>
                        <th>Modul</th>
                        <th>Tipe</th>
                        <th>Qty</th>
                        <th>PT</th>
                        <th>Nama Pengambil</th>
                        <th>Diproses Oleh</th>
                        <th>Stok</th>
                    </tr>
                </thead>
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
                            <td class="atk-stock-movement-module" data-label="Modul"><span class="atk-badge {{ in_array($movement->item?->module, ['OPS', 'ATK_MKS'], true) ? 'atk-badge-success' : 'atk-badge-brand' }}">{{ $movement->item?->module === 'ATK_MKS' ? 'ATK MKS' : ($movement->item?->module ?? '-') }}</span></td>
                            <td class="atk-stock-movement-type" data-label="Tipe"><span class="atk-badge atk-badge-{{ $typeLabel[1] }}">{{ $typeLabel[0] }}</span></td>
                            <td class="atk-stock-movement-qty" data-label="Jumlah">{{ $movement->qty }} {{ $movement->item?->unit_name }}</td>
                            <td class="atk-stock-movement-pt" data-label="PT">{{ $sourceRequest?->pt_name_snapshot ?? '-' }}</td>
                            <td class="atk-stock-movement-requester" data-label="Nama Pengambil">{{ $sourceRequest?->user_name_snapshot ?? '-' }}</td>
                            <td class="atk-stock-movement-processor" data-label="Diproses Oleh">{{ $movement->createdBy?->name ?? '-' }}</td>
                            <td class="atk-stock-movement-stock" data-label="Perubahan Stok">{{ $movement->stock_before }} → {{ $movement->stock_after }}</td>
                        </tr>
                    @empty
                        <tr class="atk-stock-movements-empty"><td colspan="9" class="atk-empty">Belum ada riwayat stok yang sesuai filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination :items="$movements" preserve-query />
    </div>

    <script>
        (function () {
            var form = document.getElementById('stockMovementsFilterForm');
            var searchInput = document.getElementById('stockMovementSearchInput');
            var clearBtn = document.getElementById('stockMovementSearchClear');
            var typeInput = document.getElementById('stockMovementTypeInput');
            var typePills = document.getElementById('stockMovementTypePills');
            var moduleSelect = document.getElementById('stockMovementModuleSelect');
            var itemSelect = document.getElementById('stockMovementItemSelect');
            var resultsWrap = document.getElementById('stockMovementsResults');
            var countEl = document.getElementById('stockMovementsCount');

            if (!form || !searchInput || !resultsWrap) return;

            var debounceTimer = null;
            var activeController = null;
            var baseUrl = form.getAttribute('action');

            function getFilterState() {
                return {
                    q: (searchInput.value || '').trim(),
                    movement_type: typeInput ? (typeInput.value || '') : '',
                    module: moduleSelect ? (moduleSelect.value || '') : '',
                    item_id: itemSelect ? (itemSelect.value || '') : ''
                };
            }

            function buildUrl(state) {
                var params = new URLSearchParams();
                if (state.movement_type) params.set('movement_type', state.movement_type);
                if (state.module) params.set('module', state.module);
                if (state.item_id) params.set('item_id', state.item_id);
                if (state.q) params.set('q', state.q);
                var qs = params.toString();
                return qs ? baseUrl + '?' + qs : baseUrl;
            }

            function syncTypePills(state) {
                if (!typePills) return;
                typePills.querySelectorAll('.atk-type-pill').forEach(function (pill) {
                    var t = pill.getAttribute('data-type') || '';
                    pill.classList.toggle('is-active', t === state.movement_type);
                    pill.setAttribute('href', buildUrl({
                        q: state.q,
                        movement_type: t,
                        module: state.module,
                        item_id: state.item_id
                    }));
                });
            }

            function fetchResults(url, pushHistory) {
                if (activeController) {
                    activeController.abort();
                }
                activeController = new AbortController();
                resultsWrap.classList.add('is-loading');

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: activeController.signal
                })
                    .then(function (res) { return res.text(); })
                    .then(function (html) {
                        var doc = new DOMParser().parseFromString(html, 'text/html');
                        var nextResults = doc.getElementById('stockMovementsResults');
                        var nextCount = doc.getElementById('stockMovementsCount');
                        var nextItemSelect = doc.getElementById('stockMovementItemSelect');
                        if (nextResults) {
                            resultsWrap.innerHTML = nextResults.innerHTML;
                        }
                        if (nextCount && countEl) {
                            countEl.textContent = nextCount.textContent;
                        }
                        if (nextItemSelect && itemSelect) {
                            var prevVal = itemSelect.value;
                            itemSelect.innerHTML = nextItemSelect.innerHTML;
                            itemSelect.value = prevVal;
                        }
                        if (pushHistory && window.history && window.history.replaceState) {
                            window.history.replaceState({}, '', url);
                        }
                    })
                    .catch(function (err) {
                        if (err && err.name === 'AbortError') return;
                    })
                    .finally(function () {
                        resultsWrap.classList.remove('is-loading');
                    });
            }

            function triggerFilter() {
                var state = getFilterState();
                if (clearBtn) {
                    clearBtn.classList.toggle('is-visible', state.q.length > 0);
                }
                syncTypePills(state);
                fetchResults(buildUrl(state), true);
            }

            searchInput.addEventListener('input', function () {
                var q = (searchInput.value || '').trim();
                if (clearBtn) {
                    clearBtn.classList.toggle('is-visible', q.length > 0);
                }
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(triggerFilter, 240);
            });

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                clearTimeout(debounceTimer);
                triggerFilter();
            });

            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    searchInput.value = '';
                    searchInput.focus();
                    clearTimeout(debounceTimer);
                    triggerFilter();
                });
            }

            if (typePills) {
                typePills.addEventListener('click', function (e) {
                    var pill = e.target.closest('.atk-type-pill');
                    if (!pill) return;
                    e.preventDefault();
                    if (typeInput) typeInput.value = pill.getAttribute('data-type') || '';
                    clearTimeout(debounceTimer);
                    triggerFilter();
                });
            }

            if (moduleSelect) {
                moduleSelect.addEventListener('change', function () {
                    clearTimeout(debounceTimer);
                    triggerFilter();
                });
            }

            if (itemSelect) {
                itemSelect.addEventListener('change', function () {
                    clearTimeout(debounceTimer);
                    triggerFilter();
                });
            }
        })();
    </script>
</x-atk-app>
