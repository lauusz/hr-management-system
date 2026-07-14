<x-atk-app title="Katalog ATK">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Katalog ATK</h1>
            <p class="atk-subtitle">Pilih barang tersedia, lalu ajukan melalui keranjang.</p>
        </div>
        <a class="atk-btn atk-btn-secondary atk-catalog-cart-link" href="{{ route('v2.atk.cart.show') }}">Keranjang ({{ $cartCount }})</a>
    </div>

    <form method="GET" id="atkCatalogSearchForm" action="{{ route('v2.atk.catalog') }}" class="atk-card" style="margin-bottom:14px">
        <input class="atk-input" type="search" name="q" value="{{ request('q') }}" placeholder="Cari barang ATK..." autocomplete="off" data-async-search>
    </form>

    <div id="atkCatalogResults">
    @if($items->count())
        <div class="atk-grid atk-catalog-grid">
            @foreach($items as $item)
                <article class="atk-card atk-product">
                    <div class="atk-product-media">
                        @if($item->image_path)
                            <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->name }}">
                        @else
                            <span>ATK</span>
                        @endif
                    </div>
                    <div>
                        <h2 class="atk-product-title">{{ $item->name }}</h2>
                        <p class="atk-product-meta">{{ $item->category?->name ?? 'Tanpa kategori' }}</p>
                    </div>
                    <div class="atk-product-stock">
                        <div>
                            @if($item->stock_status === 'OUT')
                                <span class="atk-badge atk-badge-neutral">Stok Habis</span>
                            @elseif($item->stock_status === 'LOW')
                                <span class="atk-badge atk-badge-warning">Stok Menipis</span>
                            @else
                                <span class="atk-badge atk-badge-success">Tersedia</span>
                            @endif
                            @php
                                $inCartQty = session('atk_cart', [])[$item->id] ?? null;
                            @endphp
                            @if($inCartQty)
                                <span class="atk-badge atk-badge-brand">Di Keranjang: {{ $inCartQty }}</span>
                            @endif
                        </div>
                        <strong>{{ $item->stock_qty }} {{ $item->unit_name }}</strong>
                    </div>
                    <p class="atk-product-meta">1 {{ $item->unit_name }} = {{ $item->unit_size }} {{ $item->content_unit_name }}</p>
                    @if($item->stock_qty > 0)
                        <form method="POST" action="{{ route('v2.atk.cart.add') }}" class="atk-qty-form">
                            @csrf
                            <input type="hidden" name="atk_item_id" value="{{ $item->id }}">
                            <input class="atk-input" type="number" name="qty" min="{{ $item->min_request_qty }}" max="{{ $item->stock_qty }}" value="{{ $item->min_request_qty }}" aria-label="Jumlah {{ $item->name }}">
                            <button class="atk-btn atk-btn-primary" type="submit">Tambah</button>
                        </form>
                    @else
                        <div class="atk-actions">
                            <button class="atk-btn atk-btn-muted" disabled>Stok Habis</button>
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
                const link = event.target.closest('a');
                if (!link || !link.closest('.hrd-pagination')) return;

                event.preventDefault();
                load(new URL(link.href));
            });
        })();
    </script>
    <style>
        .atk-catalog-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .atk-catalog-cart-link {
            display: none;
        }
        .atk-catalog-grid .atk-product {
            gap: 8px;
            padding: 10px;
            border-radius: 14px;
        }
        .atk-catalog-grid .atk-product-media {
            border-radius: 12px;
            aspect-ratio: 1 / 1;
        }
        .atk-catalog-grid .atk-product-media img {
            object-fit: contain;
        }
        .atk-catalog-grid .atk-product-title {
            font-size: 12px;
            line-height: 1.3;
            margin-bottom: 3px;
        }
        .atk-catalog-grid .atk-product-meta {
            font-size: 10px;
        }
        .atk-catalog-grid .atk-product-stock {
            align-items: flex-start;
            flex-direction: column;
            gap: 5px;
        }
        .atk-catalog-grid .atk-product-stock strong {
            font-size: 11px;
        }
        .atk-catalog-grid .atk-badge {
            padding: 4px 7px;
            font-size: 9px;
        }
        .atk-catalog-grid .atk-qty-form {
            grid-template-columns: 1fr;
        }
        .atk-catalog-grid .atk-btn,
        .atk-catalog-grid .atk-input {
            min-height: 38px;
            border-radius: 11px;
            font-size: 11px;
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
                gap: 12px;
                padding: 12px;
                border-radius: 18px;
            }
            .atk-catalog-grid .atk-product-media {
                border-radius: 16px;
                aspect-ratio: 4 / 3;
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
            .atk-catalog-grid .atk-qty-form {
                grid-template-columns: 92px minmax(0, 1fr);
            }
            .atk-catalog-grid .atk-btn,
            .atk-catalog-grid .atk-input {
                min-height: 44px;
                border-radius: 14px;
                font-size: 13px;
            }
        }
    </style>
</x-atk-app>
