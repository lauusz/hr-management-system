<x-inv-pesona-app title="Daftar Barang — Inventory Pesona">
    <div class="pesona-catalog-toolbar">
        <div class="pesona-catalog-heading">
            <div>
                <div class="pesona-catalog-title-row">
                    <h1 class="pesona-title">Daftar Barang</h1>
                    <span class="pesona-catalog-count" id="pesonaCatalogCount">{{ $items->total() }} barang</span>
                </div>
                <p class="pesona-subtitle">Persediaan barang garasi PT Pesona.</p>
            </div>
        </div>

        <div class="pesona-catalog-controls">
            <form method="GET" id="pesonaCatalogSearchForm" action="{{ route('inventory.pesona.catalog') }}" class="pesona-catalog-search-form">
                <div class="pesona-search-box">
                    <svg class="pesona-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input
                        class="pesona-search-input"
                        type="search"
                        name="q"
                        maxlength="150"
                        value="{{ $search }}"
                        placeholder="Cari nama atau spesifikasi barang..."
                        aria-label="Cari nama atau spesifikasi barang"
                        autocomplete="off"
                        data-async-search
                    >
                </div>

                <select class="pesona-stock-select" name="stock" aria-label="Filter ketersediaan stok" data-async-filter>
                    <option value="all" @selected($stock === 'all')>Semua Stok</option>
                    <option value="available" @selected($stock === 'available')>Tersedia</option>
                    <option value="out" @selected($stock === 'out')>Stok Habis</option>
                </select>
            </form>

            <a class="pesona-btn pesona-btn-secondary pesona-catalog-cart-link" href="{{ route('inventory.pesona.cart.show') }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <use href="#pesona-icon-cart"/>
                </svg>
                <span>Daftar Ambil</span>
            </a>
        </div>
    </div>

    <div id="pesonaCatalogResults">
        @if($items->isEmpty())
            <div class="pesona-card pesona-empty">
                <p style="margin:0;">{{ ! $hasActiveItems ? 'Belum ada barang yang tersedia di daftar persediaan.' : ($items->total() === 0 ? 'Tidak ada barang yang sesuai dengan pencarian atau filter.' : 'Tidak ada barang pada halaman ini.') }}</p>
                @if($search !== '' || $stock !== 'all')
                    <a class="pesona-btn pesona-btn-secondary" style="margin-top:10px;" href="{{ route('inventory.pesona.catalog') }}" data-async-reset>Reset Filter</a>
                @endif
            </div>
        @else
            <div class="pesona-catalog-grid">
                @foreach($items as $item)
                    @php
                        $out = $item->stock_qty === 0;
                        $low = ! $out && $item->minimum_stock > 0 && $item->stock_qty <= $item->minimum_stock;
                        $belowMin = $item->stock_qty > 0 && $item->stock_qty < $item->min_request_qty;
                        $hasUnitConversion = $item->unit_size > 1;
                    @endphp
                    <article class="pesona-card pesona-product {{ $out ? 'is-out' : '' }}">
                        <div class="pesona-product-media">
                            @if($item->image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}" alt="Foto {{ $item->name }}" loading="lazy" onerror="this.hidden = true; this.nextElementSibling.hidden = false;">
                                <span class="pesona-product-placeholder" hidden>PESONA</span>
                            @else
                                <span class="pesona-product-placeholder">PESONA</span>
                            @endif

                            <span @class([
                                'pesona-badge',
                                'pesona-status-badge',
                                'pesona-badge-neutral' => $out,
                                'pesona-badge-warning' => $low,
                                'pesona-badge-success' => ! $out && ! $low,
                            ])>
                                {{ $out ? 'Stok Habis' : ($low ? 'Stok Terbatas' : 'Tersedia') }}
                            </span>
                        </div>

                        <div class="pesona-product-copy">
                            <h2 class="pesona-product-title" title="{{ $item->name }}">{{ $item->name }}</h2>
                        </div>

                        <div class="pesona-product-stock">
                            <strong class="pesona-stock-pill {{ $out ? 'is-zero' : ($low ? 'is-low' : '') }}">
                                Stok: {{ $item->stock_qty }} {{ $item->unit_name }}
                            </strong>
                            @if($hasUnitConversion)
                                <span class="pesona-product-meta">1 {{ $item->unit_name }} = {{ $item->unit_size }} {{ $item->content_unit_name }}</span>
                            @endif
                        </div>

                        <div class="pesona-product-min">
                            <span class="pesona-product-meta">Min. ambil: {{ $item->min_request_qty }} {{ $item->unit_name }}</span>
                        </div>

                        @if($belowMin)
                            <p class="pesona-product-note">Stok belum memenuhi minimum pengambilan.</p>
                        @elseif($item->stock_qty > 0 && $item->stock_qty >= $item->min_request_qty)
                            <form class="pesona-qty-form" method="POST" action="{{ route('inventory.pesona.cart.add', $item) }}">
                                @csrf
                                <div class="pesona-stepper" data-stepper>
                                    <button
                                        class="pesona-stepper-btn"
                                        type="button"
                                        data-stepper-decrease
                                        aria-label="Kurangi jumlah {{ $item->name }}"
                                        aria-disabled="true"
                                        disabled
                                    >&minus;</button>
                                    <input
                                        class="pesona-stepper-input"
                                        type="number"
                                        name="qty"
                                        min="{{ $item->min_request_qty }}"
                                        max="{{ $item->stock_qty }}"
                                        step="1"
                                        value="{{ $item->min_request_qty }}"
                                        inputmode="numeric"
                                        aria-label="Jumlah {{ $item->name }} ({{ $item->unit_name }})"
                                        data-stepper-input
                                        required
                                    >
                                    <button
                                        class="pesona-stepper-btn"
                                        type="button"
                                        data-stepper-increase
                                        aria-label="Tambah jumlah {{ $item->name }}"
                                        aria-disabled="{{ $item->min_request_qty >= $item->stock_qty ? 'true' : 'false' }}"
                                        @disabled($item->min_request_qty >= $item->stock_qty)
                                    >+</button>
                                </div>
                                <button class="pesona-btn pesona-btn-primary pesona-add-btn" type="submit">Ambil</button>
                            </form>
                        @else
                            <div class="pesona-product-actions">
                                <span class="pesona-btn pesona-btn-disabled" aria-disabled="true">Stok Kosong</span>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
        <x-pagination :items="$items" />
    </div>

    <script>
        (function () {
            const form = document.getElementById('pesonaCatalogSearchForm');
            const input = form ? form.querySelector('[data-async-search]') : null;
            const filterSelect = form ? form.querySelector('[data-async-filter]') : null;
            const results = document.getElementById('pesonaCatalogResults');
            const countBadge = document.getElementById('pesonaCatalogCount');
            if (!form || !input || !results) return;

            let timer;
            let activeController = null;

            function syncStepper(stepper) {
                if (!stepper) return;
                const quantityInput = stepper.querySelector('[data-stepper-input]');
                const decrease = stepper.querySelector('[data-stepper-decrease]');
                const increase = stepper.querySelector('[data-stepper-increase]');
                if (!quantityInput || !decrease || !increase) return;

                const value = Number(quantityInput.value);
                const min = Number(quantityInput.min);
                const max = Number(quantityInput.max);
                const decreaseDisabled = !Number.isFinite(value) || value <= min;
                const increaseDisabled = !Number.isFinite(value) || value >= max;

                decrease.disabled = decreaseDisabled;
                increase.disabled = increaseDisabled;
                decrease.setAttribute('aria-disabled', decreaseDisabled ? 'true' : 'false');
                increase.setAttribute('aria-disabled', increaseDisabled ? 'true' : 'false');
            }

            function syncAllSteppers() {
                results.querySelectorAll('[data-stepper]').forEach(syncStepper);
            }

            function load(url) {
                if (activeController) {
                    activeController.abort();
                }
                activeController = new AbortController();
                results.classList.add('is-loading');

                fetch(url.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: activeController.signal
                })
                    .then(function (response) {
                        if (!response.ok) throw new Error('Network error');
                        return response.text();
                    })
                    .then(function (html) {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const nextResults = doc.getElementById('pesonaCatalogResults');
                        const nextCount = doc.getElementById('pesonaCatalogCount');
                        if (!nextResults) {
                            window.location.href = url.toString();
                            return;
                        }
                        results.innerHTML = nextResults.innerHTML;
                        if (countBadge && nextCount) {
                            countBadge.textContent = nextCount.textContent;
                        }
                        syncAllSteppers();
                        window.history.replaceState(null, '', url.toString());
                    })
                    .catch(function (err) {
                        if (err && err.name === 'AbortError') return;
                        window.location.href = url.toString();
                    })
                    .finally(function () {
                        results.classList.remove('is-loading');
                    });
            }

            function search() {
                const url = new URL(form.action, window.location.origin);
                const params = new URLSearchParams(new FormData(form));
                const q = (params.get('q') || '').trim();
                if (!q) {
                    params.delete('q');
                } else {
                    params.set('q', q);
                }
                if (params.get('stock') === 'all') {
                    params.delete('stock');
                }
                url.search = params.toString();
                load(url);
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                clearTimeout(timer);
                search();
            });

            input.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(search, 300);
            });

            if (filterSelect) {
                filterSelect.addEventListener('change', function () {
                    clearTimeout(timer);
                    search();
                });
            }

            results.addEventListener('click', function (event) {
                const stepperButton = event.target.closest('[data-stepper-decrease], [data-stepper-increase]');
                if (stepperButton && results.contains(stepperButton)) {
                    const stepper = stepperButton.closest('[data-stepper]');
                    const quantityInput = stepper ? stepper.querySelector('[data-stepper-input]') : null;
                    if (!quantityInput) return;

                    if (stepperButton.hasAttribute('data-stepper-decrease')) {
                        quantityInput.stepDown();
                    } else {
                        quantityInput.stepUp();
                    }
                    quantityInput.dispatchEvent(new Event('input', { bubbles: true }));
                    return;
                }

                const resetLink = event.target.closest('[data-async-reset]');
                if (resetLink && results.contains(resetLink)) {
                    event.preventDefault();
                    input.value = '';
                    if (filterSelect) filterSelect.value = 'all';
                    load(new URL(resetLink.href, window.location.origin));
                    return;
                }

                const pageLink = event.target.closest('a');
                if (!pageLink || !pageLink.closest('.hrd-pagination')) return;

                event.preventDefault();
                load(new URL(pageLink.href, window.location.origin));
            });

            results.addEventListener('input', function (event) {
                if (!event.target.matches('[data-stepper-input]')) return;
                syncStepper(event.target.closest('[data-stepper]'));
            });

            results.addEventListener('change', function (event) {
                if (!event.target.matches('[data-stepper-input]')) return;
                const quantityInput = event.target;
                const value = Number(quantityInput.value);
                const min = Number(quantityInput.min);
                const max = Number(quantityInput.max);
                if (!Number.isFinite(value) || value < min) quantityInput.value = min;
                if (Number(quantityInput.value) > max) quantityInput.value = max;
                syncStepper(quantityInput.closest('[data-stepper]'));
            });

            syncAllSteppers();
        })();
    </script>

    <style>
        /* ========== TOP TOOLBAR (Mobile-First) ========== */
        .pesona-catalog-toolbar {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 14px;
        }
        .pesona-catalog-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .pesona-catalog-title-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .pesona-catalog-count {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--pesona-primary);
            background: var(--pesona-primary-soft);
            border: 1px solid rgba(40, 53, 90, 0.12);
            padding: 2px 9px;
            border-radius: 999px;
        }
        .pesona-catalog-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            min-width: 0;
        }
        .pesona-catalog-search-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 8px;
            flex: 1;
            min-width: 0;
            margin: 0;
        }
        .pesona-search-box {
            position: relative;
            min-width: 0;
        }
        .pesona-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            pointer-events: none;
        }
        .pesona-search-input,
        .pesona-stock-select {
            height: 38px;
            min-height: 38px;
            border: 1.5px solid var(--pesona-border);
            border-radius: 10px;
            background: #FFFFFF;
            color: var(--pesona-text);
            font: inherit;
            font-size: 13px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .pesona-search-input {
            width: 100%;
            min-width: 0;
            padding: 0 12px 0 36px;
        }
        .pesona-stock-select {
            padding: 0 28px 0 10px;
            font-weight: 600;
            color: var(--pesona-text-secondary);
            cursor: pointer;
        }
        .pesona-search-input:focus,
        .pesona-stock-select:focus {
            outline: none;
            border-color: var(--pesona-primary);
            box-shadow: 0 0 0 3px rgba(40, 53, 90, 0.12);
        }

        .pesona-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 38px;
            padding: 0 14px;
            border: 0;
            border-radius: 10px;
            text-decoration: none;
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color .15s ease, color .15s ease, box-shadow .15s ease;
        }
        .pesona-btn-primary {
            color: #FFFFFF;
            background: var(--pesona-primary);
        }
        .pesona-btn-primary:hover {
            background: var(--pesona-primary-dark);
        }
        .pesona-btn-secondary {
            color: var(--pesona-primary);
            background: var(--pesona-primary-soft);
            border: 1px solid rgba(40, 53, 90, 0.12);
        }
        .pesona-btn-secondary:hover {
            background: #E2E7F3;
        }
        .pesona-btn-disabled {
            color: var(--pesona-muted);
            background: #F3F4F6;
            cursor: not-allowed;
        }
        .pesona-catalog-cart-link {
            display: none;
            white-space: nowrap;
            height: 38px;
            min-height: 38px;
            flex-shrink: 0;
        }

        /* ========== PRODUCT GRID & CARDS (Mobile-First 2 Columns) ========== */
        #pesonaCatalogResults {
            transition: opacity .15s ease;
        }
        #pesonaCatalogResults.is-loading {
            opacity: .6;
        }
        .pesona-card {
            background: var(--pesona-surface);
            border: 1px solid var(--pesona-border);
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 1px 3px rgba(17, 24, 39, 0.05);
        }
        .pesona-empty {
            text-align: center;
            padding: 32px 18px;
            color: var(--pesona-muted);
            font-size: 13px;
        }
        .pesona-catalog-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .pesona-catalog-grid .pesona-product {
            display: flex;
            min-width: 0;
            flex-direction: column;
            gap: 7px;
            padding: 10px;
            border-radius: 12px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .pesona-catalog-grid .pesona-product:hover {
            border-color: #B8C3DC;
            box-shadow: 0 4px 12px rgba(40, 53, 90, 0.08);
        }
        .pesona-catalog-grid .pesona-product.is-out {
            background: #FAFAFB;
        }

        /* Media */
        .pesona-product-media {
            position: relative;
            border-radius: 9px;
            height: clamp(96px, 26vw, 114px);
            background: var(--pesona-primary-softer);
            border: 1px solid var(--pesona-border-soft);
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .pesona-product-media img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .pesona-product-placeholder {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            color: #94A3B8;
        }
        .pesona-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 7px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            line-height: 1.2;
        }
        .pesona-status-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            white-space: nowrap;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        .pesona-badge-success { color: #15803D; background: #E8F8EE; }
        .pesona-badge-warning { color: #92400E; background: #FEF3C7; }
        .pesona-badge-neutral { color: #4B5563; background: #F3F4F6; }

        /* Title & Copy */
        .pesona-product-copy {
            min-width: 0;
        }
        .pesona-product-title {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            font-size: 12.5px;
            font-weight: 700;
            line-height: 1.35;
            min-height: 2.7em;
            margin: 0;
            color: var(--pesona-text);
            overflow-wrap: anywhere;
        }

        /* Stock & Unit Row */
        .pesona-product-stock {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 4px 6px;
            padding-top: 2px;
        }
        .pesona-stock-pill {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--pesona-primary);
            background: var(--pesona-primary-soft);
            border: 1px solid rgba(40, 53, 90, 0.14);
            padding: 2px 7px;
            border-radius: 6px;
        }
        .pesona-stock-pill.is-low {
            color: var(--pesona-accent-dark);
            background: var(--pesona-accent-soft);
            border-color: rgba(186, 57, 37, 0.22);
        }
        .pesona-stock-pill.is-zero {
            color: var(--pesona-muted);
            background: #F3F4F6;
            border-color: var(--pesona-border);
        }
        .pesona-product-meta {
            font-size: 11px;
            color: var(--pesona-muted);
            line-height: 1.3;
        }
        .pesona-product-min {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .pesona-product-note {
            margin: auto 0 0;
            padding: 6px 8px;
            border-radius: 8px;
            background: #FEF3C7;
            color: #92400E;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.35;
        }

        /* Stepper & Action Row */
        .pesona-qty-form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 6px;
            margin-top: auto;
            padding-top: 2px;
        }
        .pesona-stepper {
            display: grid;
            grid-template-columns: 32px minmax(0, 1fr) 32px;
            width: 100%;
            min-width: 0;
            height: 34px;
            border: 1px solid var(--pesona-border);
            border-radius: 8px;
            background: var(--pesona-surface);
        }
        .pesona-stepper-btn,
        .pesona-stepper-input {
            min-width: 0;
            height: 100%;
            border: 0;
            background: transparent;
            color: var(--pesona-text);
            font: inherit;
            font-size: 12.5px;
            font-weight: 700;
            text-align: center;
        }
        .pesona-stepper-btn {
            padding: 0;
            color: var(--pesona-primary);
            cursor: pointer;
        }
        .pesona-stepper-btn:first-child {
            border-right: 1px solid var(--pesona-border-soft);
            border-radius: 7px 0 0 7px;
        }
        .pesona-stepper-btn:last-child {
            border-left: 1px solid var(--pesona-border-soft);
            border-radius: 0 7px 7px 0;
        }
        .pesona-stepper-input {
            width: 100%;
            padding: 0 2px;
            border-radius: 0;
            appearance: textfield;
            -moz-appearance: textfield;
        }
        .pesona-stepper-input::-webkit-inner-spin-button,
        .pesona-stepper-input::-webkit-outer-spin-button {
            margin: 0;
            appearance: none;
        }
        .pesona-stepper-btn:focus-visible,
        .pesona-stepper-input:focus-visible {
            position: relative;
            z-index: 1;
            outline: 2px solid var(--pesona-primary);
            outline-offset: 1px;
        }
        .pesona-stepper-btn:active:not(:disabled) {
            background: var(--pesona-primary-soft);
        }
        .pesona-stepper-btn:disabled {
            color: #9CA3AF;
            cursor: not-allowed;
            opacity: .5;
        }
        .pesona-product-actions {
            margin-top: auto;
            padding-top: 2px;
        }
        .pesona-product-actions .pesona-btn,
        .pesona-catalog-grid .pesona-add-btn {
            width: 100%;
            height: 34px;
            min-height: 34px;
            border-radius: 8px;
            font-size: 12px;
            padding-inline: 10px;
        }

        @media (hover: hover) {
            .pesona-stepper-btn:hover:not(:disabled) {
                background: var(--pesona-primary-softer);
            }
        }

        /* ========== TABLET & DESKTOP (>= 640px) ========== */
        @media (min-width: 640px) {
            .pesona-catalog-toolbar {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
            }
            .pesona-catalog-controls {
                max-width: 560px;
                justify-content: flex-end;
            }
            .pesona-catalog-cart-link {
                display: inline-flex;
            }
            .pesona-catalog-grid {
                grid-template-columns: repeat(auto-fill, minmax(205px, 1fr));
                gap: 12px;
            }
            .pesona-catalog-grid .pesona-product {
                padding: 12px;
                gap: 8px;
            }
            .pesona-product-media {
                height: 116px;
            }
            .pesona-product-title {
                font-size: 13px;
            }
            .pesona-qty-form {
                grid-template-columns: 92px minmax(0, 1fr);
                align-items: center;
            }
        }
    </style>
</x-inv-pesona-app>
