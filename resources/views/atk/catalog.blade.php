<x-atk-app title="Katalog ATK">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Katalog ATK</h1>
            <p class="atk-subtitle">Pilih barang tersedia, lalu ajukan melalui keranjang.</p>
        </div>
        <a class="atk-btn atk-btn-secondary atk-catalog-cart-link" href="{{ route('v2.atk.cart.show') }}">Keranjang ({{ $cartCount }})</a>
    </div>

    <form method="GET" id="atkCatalogSearchForm" action="{{ route('v2.atk.catalog') }}" class="atk-card atk-catalog-search">
        <input class="atk-input" type="search" name="q" value="{{ request('q') }}" placeholder="Cari barang ATK..." autocomplete="off" data-async-search>
    </form>

    <div id="atkCatalogResults">
    @if($items->count())
        <div class="atk-grid atk-catalog-grid">
            @foreach($items as $item)
                @php
                    $inCartQty = session('atk_cart', [])[$item->id] ?? null;
                @endphp
                <article class="atk-card atk-product">
                    <div class="atk-product-media">
                        @if($item->image_path)
                            <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->name }}" onerror="this.hidden = true; this.nextElementSibling.hidden = false;">
                            <span class="atk-product-placeholder" hidden>ATK</span>
                        @else
                            <span class="atk-product-placeholder">ATK</span>
                        @endif
                        @if($item->stock_status === 'OUT')
                            <span class="atk-badge atk-badge-neutral atk-stock-empty-badge">Stok Kosong</span>
                        @endif
                    </div>
                    <div class="atk-product-copy">
                        <h2 class="atk-product-title">{{ $item->name }}</h2>
                        @if($item->category)
                            <p class="atk-product-meta">{{ $item->category->name }}</p>
                        @endif
                    </div>
                    <div class="atk-product-stock">
                        @if($inCartQty)
                            <span class="atk-badge atk-badge-brand">Di Keranjang: {{ $inCartQty }}</span>
                        @endif
                        <strong>{{ $item->stock_qty }} {{ $item->unit_name }}</strong>
                    </div>
                    @if($item->unit_size != 1 || $item->unit_name !== $item->content_unit_name)
                        <p class="atk-product-meta atk-product-unit">1 {{ $item->unit_name }} = {{ $item->unit_size }} {{ $item->content_unit_name }}</p>
                    @endif
                    @if($item->stock_qty > 0)
                        <form method="POST" action="{{ route('v2.atk.cart.add') }}" class="atk-qty-form">
                            @csrf
                            <input type="hidden" name="atk_item_id" value="{{ $item->id }}">
                            <div class="atk-stepper" data-stepper>
                                <button class="atk-stepper-btn" type="button" data-stepper-decrease aria-label="Kurangi jumlah {{ $item->name }}" aria-disabled="true" disabled>&minus;</button>
                                <input class="atk-stepper-input" type="number" name="qty" min="{{ $item->min_request_qty }}" max="{{ $item->stock_qty }}" value="{{ $item->min_request_qty }}" inputmode="numeric" aria-label="Jumlah {{ $item->name }}" data-stepper-input>
                                <button class="atk-stepper-btn" type="button" data-stepper-increase aria-label="Tambah jumlah {{ $item->name }}" aria-disabled="{{ $item->min_request_qty >= $item->stock_qty ? 'true' : 'false' }}" @disabled($item->min_request_qty >= $item->stock_qty)>+</button>
                            </div>
                            <button class="atk-btn atk-btn-primary" type="submit">Tambah</button>
                        </form>
                    @else
                        <div class="atk-actions atk-product-actions">
                            <a class="atk-btn atk-btn-secondary" href="{{ route('v2.atk.need-requests.create', ['item' => $item->id]) }}">Ajukan Restock</a>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
        <x-pagination :items="$items" preserve-query />
    @else
        <div class="atk-card atk-empty">Belum ada barang ATK yang aktif.</div>
    @endif
    </div>

    <script>
        (function () {
            const form = document.getElementById('atkCatalogSearchForm');
            const input = form ? form.querySelector('[data-async-search]') : null;
            const results = document.getElementById('atkCatalogResults');
            if (!form || !input || !results) return;

            let timer;

            function syncStepper(stepper) {
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
                fetch(url.toString())
                    .then(function (response) { return response.text(); })
                    .then(function (html) {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const next = doc.getElementById('atkCatalogResults');
                        if (!next) {
                            window.location.href = url.toString();
                            return;
                        }
                        results.innerHTML = next.innerHTML;
                        syncAllSteppers();
                        window.history.replaceState(null, '', url.toString());
                    })
                    .catch(function () {
                        window.location.href = url.toString();
                    });
            }

            function search() {
                const url = new URL(form.action, window.location.origin);
                const params = new URLSearchParams(new FormData(form));
                if (!params.get('q')) params.delete('q');
                url.search = params.toString();
                load(url);
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                search();
            });

            input.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(search, 350);
            });

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

                const link = event.target.closest('a');
                if (!link || !link.closest('.hrd-pagination')) return;

                event.preventDefault();
                load(new URL(link.href));
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
        /* Hallmark · pre-emit critique: P4 H4 E4 S5 R5 V4 */
        .atk-catalog-search {
            margin-bottom: 12px;
            padding: 8px;
            border-radius: 14px;
        }
        .atk-catalog-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }
        .atk-catalog-cart-link {
            display: none;
        }
        .atk-catalog-grid .atk-product {
            display: flex;
            min-width: 0;
            flex-direction: column;
            gap: 8px;
            padding: 8px;
            border-radius: 14px;
        }
        .atk-catalog-grid .atk-product-media {
            position: relative;
            border-radius: 12px;
            aspect-ratio: auto;
            height: clamp(104px, 30vw, 120px);
        }
        .atk-catalog-grid .atk-product-media img {
            object-fit: contain;
        }
        .atk-catalog-grid .atk-product-placeholder {
            font-size: 12px;
            letter-spacing: .08em;
        }
        .atk-catalog-grid .atk-product-copy {
            min-width: 0;
        }
        .atk-catalog-grid .atk-product-title {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            font-size: clamp(12px, 3.4vw, 14px);
            line-height: 1.35;
            margin-bottom: 3px;
            overflow-wrap: anywhere;
        }
        .atk-catalog-grid .atk-product-meta {
            font-size: 12px;
            line-height: 1.4;
        }
        .atk-catalog-grid .atk-product-stock {
            align-items: flex-start;
            flex-direction: column;
            gap: 5px;
        }
        .atk-catalog-grid .atk-product-stock strong {
            font-size: 12px;
        }
        .atk-catalog-grid .atk-badge {
            padding: 4px 7px;
            font-size: 10px;
        }
        .atk-catalog-grid .atk-stock-empty-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            white-space: nowrap;
        }
        .atk-catalog-grid .atk-qty-form {
            grid-template-columns: 1fr;
            margin-top: auto;
        }
        /* Hallmark component contract: quantity-stepper / default, hover, focus, active, disabled */
        .atk-stepper {
            display: grid;
            grid-template-columns: 44px minmax(0, 1fr) 44px;
            width: 100%;
            min-width: 0;
            min-height: 44px;
            border: 1px solid var(--atk-border);
            border-radius: 11px;
            background: var(--atk-surface);
        }
        .atk-stepper-btn,
        .atk-stepper-input {
            min-width: 0;
            min-height: 44px;
            border: 0;
            background: transparent;
            color: var(--atk-text);
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
        }
        .atk-stepper-btn {
            padding: 0;
            color: var(--atk-primary-dark);
            cursor: pointer;
        }
        .atk-stepper-btn:first-child {
            border-right: 1px solid var(--atk-border-soft);
            border-radius: 10px 0 0 10px;
        }
        .atk-stepper-btn:last-child {
            border-left: 1px solid var(--atk-border-soft);
            border-radius: 0 10px 10px 0;
        }
        .atk-stepper-input {
            width: 100%;
            padding: 0 2px;
            border-radius: 0;
            appearance: textfield;
            -moz-appearance: textfield;
        }
        .atk-stepper-input::-webkit-inner-spin-button,
        .atk-stepper-input::-webkit-outer-spin-button {
            margin: 0;
            appearance: none;
        }
        .atk-stepper-btn:focus-visible,
        .atk-stepper-input:focus-visible {
            position: relative;
            z-index: 1;
            outline: 2px solid var(--atk-primary);
            outline-offset: 2px;
        }
        .atk-stepper-btn:active:not(:disabled) {
            background: var(--atk-primary-soft);
        }
        .atk-stepper-btn:disabled {
            color: var(--atk-muted);
            cursor: not-allowed;
            opacity: .5;
        }
        .atk-catalog-grid .atk-product-actions {
            margin-top: auto;
        }
        .atk-catalog-grid .atk-product-actions .atk-btn {
            width: 100%;
        }
        .atk-catalog-grid .atk-btn {
            min-height: 44px;
            border-radius: 11px;
            font-size: 12px;
            padding-inline: 8px;
        }

        @media (hover: hover) {
            .atk-stepper-btn:hover:not(:disabled) {
                background: var(--atk-primary-softer);
            }
        }

        @media (min-width: 640px) {
            .atk-catalog-cart-link {
                display: inline-flex;
            }
            .atk-catalog-grid {
                grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
                gap: 14px;
            }
            .atk-catalog-grid .atk-product {
                display: grid;
                gap: 12px;
                padding: 12px;
                border-radius: 18px;
            }
            .atk-catalog-grid .atk-product-media {
                border-radius: 16px;
                aspect-ratio: 4 / 3;
                height: auto;
            }
            .atk-catalog-grid .atk-product-title {
                font-size: 15px;
                margin-bottom: 5px;
            }
            .atk-catalog-grid .atk-product-meta {
                font-size: 12px;
            }
            .atk-catalog-grid .atk-product-stock {
                align-items: center;
                flex-direction: row;
                gap: 10px;
            }
            .atk-catalog-grid .atk-product-stock strong {
                font-size: inherit;
            }
            .atk-catalog-grid .atk-badge {
                padding: 5px 10px;
                font-size: 11px;
            }
            .atk-catalog-grid .atk-btn {
                min-height: 44px;
                border-radius: 14px;
                font-size: 13px;
                padding-inline: 14px;
            }
        }
    </style>
</x-atk-app>
