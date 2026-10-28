<x-atk-app title="Katalog ATK">
    <div class="atk-catalog-toolbar">
        <div class="atk-catalog-heading">
            <h1 class="atk-title">Katalog ATK</h1>
            <span class="atk-catalog-count">{{ $items->total() }} barang</span>
        </div>

        <div class="atk-catalog-controls">
            <form method="GET" id="atkCatalogSearchForm" action="{{ route('v2.atk.catalog') }}" class="atk-catalog-search">
                <svg class="atk-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <input class="atk-input atk-search-input" type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama barang ATK..." autocomplete="off" data-async-search>
            </form>

            <a class="atk-btn {{ $cartCount > 0 ? 'atk-btn-primary' : 'atk-btn-secondary' }} atk-catalog-cart-link" href="{{ route('v2.atk.cart.show') }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 4h2l2.2 10.1a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20.5 8H6"/>
                    <circle cx="10" cy="20" r="1"/>
                    <circle cx="18" cy="20" r="1"/>
                </svg>
                <span>Keranjang ({{ $cartCount }})</span>
            </a>
        </div>
    </div>

    <div id="atkCatalogResults">
    @if($items->count())
        <div class="atk-grid atk-catalog-grid">
            @foreach($items as $item)
                @php
                    $inCartQty = session('atk_cart', [])[$item->id] ?? null;
                    $hasUnitConversion = $item->unit_size != 1 || $item->unit_name !== $item->content_unit_name;
                @endphp
                <article class="atk-card atk-product {{ $item->stock_qty <= 0 ? 'is-out' : '' }}">
                    <div class="atk-product-media">
                        @if($item->image_path)
                            <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->name }}" loading="lazy" onerror="this.hidden = true; this.nextElementSibling.hidden = false;">
                            <span class="atk-product-placeholder" hidden>ATK</span>
                        @else
                            <span class="atk-product-placeholder">ATK</span>
                        @endif

                        @if($inCartQty)
                            <span class="atk-badge atk-badge-brand atk-in-cart-badge">Di Keranjang: {{ $inCartQty }}</span>
                        @endif

                        @if($item->stock_status === 'OUT')
                            <span class="atk-badge atk-badge-neutral atk-stock-empty-badge">Stok Kosong</span>
                        @endif
                    </div>

                    <div class="atk-product-copy">
                        <h2 class="atk-product-title" title="{{ $item->name }}">{{ $item->name }}</h2>
                    </div>

                    <div class="atk-product-stock">
                        <strong class="atk-stock-pill {{ $item->stock_qty <= 0 ? 'is-zero' : '' }}">
                            Stok: {{ $item->stock_qty }} {{ $item->unit_name }}
                        </strong>
                        @if($hasUnitConversion)
                            <span class="atk-product-meta atk-product-unit">1 {{ $item->unit_name }} = {{ $item->unit_size }} {{ $item->content_unit_name }}</span>
                        @endif
                    </div>

                    @if($item->stock_qty > 0)
                        <form method="POST" action="{{ route('v2.atk.cart.add') }}" class="atk-qty-form">
                            @csrf
                            <input type="hidden" name="atk_item_id" value="{{ $item->id }}">
                            <div class="atk-stepper" data-stepper>
                                <button class="atk-stepper-btn" type="button" data-stepper-decrease aria-label="Kurangi jumlah {{ $item->name }}" aria-disabled="true" disabled>&minus;</button>
                                <input class="atk-stepper-input" type="number" name="qty" min="{{ $item->min_request_qty }}" max="{{ $item->stock_qty }}" value="{{ $item->min_request_qty }}" inputmode="numeric" aria-label="Jumlah {{ $item->name }}" data-stepper-input>
                                <button class="atk-stepper-btn" type="button" data-stepper-increase aria-label="Tambah jumlah {{ $item->name }}" aria-disabled="{{ $item->min_request_qty >= $item->stock_qty ? 'true' : 'false' }}" @disabled($item->min_request_qty >= $item->stock_qty)>+</button>
                            </div>
                            <button class="atk-btn atk-btn-primary atk-add-btn" type="submit">Tambah</button>
                        </form>
                    @else
                        <div class="atk-actions atk-product-actions">
                            <a class="atk-btn atk-btn-secondary" href="{{ route('v2.atk.need-requests.create', ['item' => $item->id]) }}">Ajukan Tambah Stok</a>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
        <x-pagination :items="$items" preserve-query />
    @else
        <div class="atk-card atk-empty">Belum ada barang ATK yang ditemukan.</div>
    @endif
    </div>

    <script>
        (function () {
            const form = document.getElementById('atkCatalogSearchForm');
            const input = form ? form.querySelector('[data-async-search]') : null;
            const results = document.getElementById('atkCatalogResults');
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
            if (!form || !input || !results) return;

            let timer;
            let toastTimer;

            function updateCartBadges(cartCount) {
                if (typeof cartCount !== 'number') return;

                const catalogCartLink = document.querySelector('.atk-catalog-cart-link');
                if (catalogCartLink) {
                    const textSpan = catalogCartLink.querySelector('span');
                    if (textSpan) {
                        textSpan.textContent = 'Keranjang (' + cartCount + ')';
                    }
                    catalogCartLink.classList.toggle('atk-btn-primary', cartCount > 0);
                    catalogCartLink.classList.toggle('atk-btn-secondary', cartCount <= 0);
                }

                const topbarShortcut = document.querySelector('.atk-cart-shortcut');
                if (topbarShortcut) {
                    topbarShortcut.setAttribute('aria-label', 'Buka keranjang, ' + cartCount + ' item');
                    let badge = topbarShortcut.querySelector('.atk-cart-badge');
                    if (cartCount > 0) {
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'atk-cart-badge';
                            badge.setAttribute('aria-hidden', 'true');
                            topbarShortcut.appendChild(badge);
                        }
                        badge.textContent = cartCount > 99 ? '99+' : String(cartCount);
                        badge.style.display = '';
                    } else if (badge) {
                        badge.style.display = 'none';
                    }
                }
            }

            function updateProductInCartBadge(card, inCartQty) {
                if (!card || typeof inCartQty !== 'number') return;
                const media = card.querySelector('.atk-product-media');
                if (!media) return;

                let badge = media.querySelector('.atk-in-cart-badge');
                if (inCartQty > 0) {
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'atk-badge atk-badge-brand atk-in-cart-badge';
                        media.appendChild(badge);
                    }
                    badge.textContent = 'Di ' + 'Keranjang: ' + inCartQty;
                } else if (badge) {
                    badge.remove();
                }
            }

            function showCatalogFeedback(message, isSuccess) {
                if (!message) return;

                const contentEl = results.closest('.atk-content');
                if (contentEl) {
                    contentEl.querySelectorAll(':scope > .atk-alert').forEach(function (el) {
                        el.remove();
                    });
                    const alertEl = document.createElement('div');
                    alertEl.className = 'atk-alert ' + (isSuccess ? 'atk-alert-success' : 'atk-alert-warning');
                    alertEl.textContent = message;
                    contentEl.insertBefore(alertEl, contentEl.firstElementChild);
                }

                let toast = document.getElementById('atkCatalogToast');
                if (toast) toast.remove();
                clearTimeout(toastTimer);

                toast = document.createElement('div');
                toast.id = 'atkCatalogToast';
                toast.className = 'atk-catalog-toast ' + (isSuccess ? 'is-success' : 'is-warning');
                toast.textContent = message;
                document.body.appendChild(toast);
                requestAnimationFrame(function () {
                    toast.classList.add('show');
                });
                toastTimer = setTimeout(function () {
                    toast.classList.remove('show');
                    setTimeout(function () {
                        if (toast && toast.parentNode) toast.remove();
                    }, 200);
                }, 2200);
            }

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

            results.addEventListener('submit', function (event) {
                const qtyForm = event.target.closest('.atk-qty-form');
                if (!qtyForm || !results.contains(qtyForm)) return;

                event.preventDefault();
                if (qtyForm.dataset.submitting === '1') return;

                const quantityInput = qtyForm.querySelector('[data-stepper-input]');
                if (quantityInput) {
                    const value = Number(quantityInput.value);
                    const min = Number(quantityInput.min) || 1;
                    const max = Number(quantityInput.max) || 9999;
                    if (!Number.isFinite(value) || value < min) quantityInput.value = min;
                    if (Number(quantityInput.value) > max) quantityInput.value = max;
                }

                qtyForm.dataset.submitting = '1';
                const submitBtn = qtyForm.querySelector('.atk-add-btn, button[type="submit"]');
                const originalLabel = submitBtn ? submitBtn.textContent : 'Tambah';
                if (submitBtn) {
                    submitBtn.disabled = true;
                }

                fetch(qtyForm.getAttribute('action'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: new FormData(qtyForm),
                    credentials: 'same-origin'
                })
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (data) {
                        if (!data || typeof data.success !== 'boolean') {
                            throw new Error('Invalid response');
                        }

                        updateCartBadges(data.cartCount);
                        const card = qtyForm.closest('.atk-product');
                        if (typeof data.inCartQty === 'number') {
                            updateProductInCartBadge(card, data.inCartQty);
                        }

                        if (data.success && quantityInput) {
                            quantityInput.value = quantityInput.min || 1;
                            const stepper = qtyForm.querySelector('[data-stepper]');
                            if (stepper) syncStepper(stepper);
                        }

                        showCatalogFeedback(data.message, data.success);
                    })
                    .catch(function () {
                        showCatalogFeedback('Gagal menambahkan barang ke keranjang. Silakan coba lagi.', false);
                    })
                    .finally(function () {
                        delete qtyForm.dataset.submitting;
                        if (submitBtn && submitBtn.isConnected) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = originalLabel;
                        }
                    });
            });

            syncAllSteppers();
        })();
    </script>

    <style>
        /* ========== TOP TOOLBAR ========== */
        .atk-catalog-toolbar {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 14px;
        }
        .atk-catalog-heading {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .atk-catalog-count {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--atk-primary-dark);
            background: var(--atk-primary-soft);
            padding: 2px 9px;
            border-radius: 999px;
        }
        .atk-catalog-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
        }
        .atk-catalog-search {
            position: relative;
            flex: 1;
            min-width: 0;
            margin: 0;
        }
        .atk-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            pointer-events: none;
        }
        .atk-search-input {
            padding-left: 36px;
            height: 38px;
            min-height: 38px;
            border-radius: 10px;
        }
        .atk-catalog-cart-link {
            display: none;
            white-space: nowrap;
            height: 38px;
            min-height: 38px;
            flex-shrink: 0;
        }

        /* ========== PRODUCT GRID & CARDS ========== */
        .atk-catalog-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .atk-catalog-grid .atk-product {
            display: flex;
            min-width: 0;
            flex-direction: column;
            gap: 8px;
            padding: 10px;
            border-radius: 12px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .atk-catalog-grid .atk-product:hover {
            border-color: #D8B4FE;
            box-shadow: 0 4px 12px rgba(109, 40, 217, 0.07);
        }
        .atk-catalog-grid .atk-product.is-out {
            background: #FAFAFB;
        }

        /* Media */
        .atk-catalog-grid .atk-product-media {
            position: relative;
            border-radius: 9px;
            aspect-ratio: auto;
            height: clamp(96px, 26vw, 114px);
            background: #F8F7FC;
            border: 1px solid var(--atk-border-soft);
            padding: 6px;
        }
        .atk-catalog-grid .atk-product-media img {
            object-fit: contain;
        }
        .atk-catalog-grid .atk-product-placeholder {
            font-size: 11px;
            letter-spacing: .08em;
            color: #9CA3AF;
        }
        .atk-catalog-grid .atk-in-cart-badge {
            position: absolute;
            top: 6px;
            left: 6px;
            padding: 3px 7px;
            font-size: 10px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.06);
        }
        .atk-catalog-grid .atk-stock-empty-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            padding: 3px 7px;
            font-size: 10px;
            white-space: nowrap;
        }

        /* Title */
        .atk-catalog-grid .atk-product-copy {
            min-width: 0;
        }
        .atk-catalog-grid .atk-product-title {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            font-size: 12.5px;
            font-weight: 700;
            line-height: 1.35;
            min-height: 2.7em;
            margin: 0;
            color: var(--atk-text);
            overflow-wrap: anywhere;
        }

        /* Stock & Unit Row */
        .atk-catalog-grid .atk-product-stock {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 4px 6px;
            padding-top: 2px;
        }
        .atk-stock-pill {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--atk-primary-dark);
            background: var(--atk-primary-softer);
            border: 1px solid #EDE9FE;
            padding: 2px 7px;
            border-radius: 6px;
        }
        .atk-stock-pill.is-zero {
            color: var(--atk-muted);
            background: #F3F4F6;
            border-color: var(--atk-border);
        }
        .atk-catalog-grid .atk-product-meta {
            font-size: 11px;
            color: var(--atk-muted);
            line-height: 1.3;
        }

        /* Stepper & Action Row */
        .atk-catalog-grid .atk-qty-form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 6px;
            margin-top: auto;
            padding-top: 2px;
        }
        .atk-stepper {
            display: grid;
            grid-template-columns: 32px minmax(0, 1fr) 32px;
            width: 100%;
            min-width: 0;
            height: 34px;
            border: 1px solid var(--atk-border);
            border-radius: 8px;
            background: var(--atk-surface);
        }
        .atk-stepper-btn,
        .atk-stepper-input {
            min-width: 0;
            height: 100%;
            border: 0;
            background: transparent;
            color: var(--atk-text);
            font: inherit;
            font-size: 12.5px;
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
            border-radius: 7px 0 0 7px;
        }
        .atk-stepper-btn:last-child {
            border-left: 1px solid var(--atk-border-soft);
            border-radius: 0 7px 7px 0;
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
            outline-offset: 1px;
        }
        .atk-stepper-btn:active:not(:disabled) {
            background: var(--atk-primary-soft);
        }
        .atk-stepper-btn:disabled {
            color: #9CA3AF;
            cursor: not-allowed;
            opacity: .5;
        }
        .atk-catalog-grid .atk-product-actions {
            margin-top: auto;
            padding-top: 2px;
        }
        .atk-catalog-grid .atk-product-actions .atk-btn {
            width: 100%;
        }
        .atk-catalog-grid .atk-btn {
            height: 34px;
            min-height: 34px;
            border-radius: 8px;
            font-size: 12px;
            padding-inline: 10px;
        }

        .atk-catalog-toast {
            position: fixed;
            left: 50%;
            bottom: 24px;
            transform: translate(-50%, 16px);
            max-width: calc(100vw - 32px);
            padding: 11px 16px;
            border-radius: 10px;
            color: #FFFFFF;
            font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.35;
            opacity: 0;
            transition: opacity .2s ease, transform .2s ease;
            z-index: 1500;
            pointer-events: none;
        }
        .atk-catalog-toast.is-success {
            background: #5B21B6;
            border: 1px solid #4C1D95;
            box-shadow: 0 10px 25px rgba(91, 33, 182, 0.35);
        }
        .atk-catalog-toast.is-warning {
            background: #D97706;
            border: 1px solid #B45309;
            box-shadow: 0 10px 25px rgba(217, 119, 6, 0.35);
        }
        .atk-catalog-toast.show {
            opacity: 1;
            transform: translate(-50%, 0);
        }

        @media (hover: hover) {
            .atk-stepper-btn:hover:not(:disabled) {
                background: var(--atk-primary-softer);
            }
        }

        @media (min-width: 640px) {
            .atk-catalog-toolbar {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
            }
            .atk-catalog-controls {
                max-width: 480px;
                justify-content: flex-end;
            }
            .atk-catalog-cart-link {
                display: inline-flex;
            }
            .atk-catalog-grid {
                grid-template-columns: repeat(auto-fill, minmax(205px, 1fr));
                gap: 12px;
            }
            .atk-catalog-grid .atk-product {
                padding: 12px;
                gap: 8px;
            }
            .atk-catalog-grid .atk-product-media {
                height: 116px;
            }
            .atk-catalog-grid .atk-product-title {
                font-size: 13px;
            }
            /* Stepper dan tombol Tambah sejajar 1 baris di tablet & desktop */
            .atk-catalog-grid .atk-qty-form {
                grid-template-columns: 92px minmax(0, 1fr);
                align-items: center;
            }
        }
    </style>
</x-atk-app>
