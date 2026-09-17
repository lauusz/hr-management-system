<x-atk-mks-app title="Katalog ATK MKS">
    <div class="atk-mks-header"><h1 class="atk-mks-title">Katalog</h1><p class="atk-mks-subtitle">Pilih barang yang dibutuhkan.</p></div>
    <form method="GET" class="atk-mks-card atk-mks-actions atk-mks-catalog-search">
        <input class="atk-mks-input" style="flex:1" name="q" value="{{ request('q') }}" placeholder="Cari barang" autocomplete="off">
        <button class="atk-mks-btn atk-mks-btn-primary" type="submit">Cari</button>
    </form>

    <div class="atk-mks-grid atk-mks-catalog-grid" data-atk-mks-mobile-cards>
        @forelse($items as $item)
            <article class="atk-mks-card atk-mks-product">
                <div class="atk-mks-product-media">
                    @if($item->image_path)
                        <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->name }}" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                        <span hidden>Tanpa foto</span>
                    @else
                        <span>Tanpa foto</span>
                    @endif
                </div>
                <h2 class="atk-mks-product-title">{{ $item->name }}</h2>
                <strong class="atk-mks-product-stock">{{ $item->stock_qty }} {{ $item->unit_name }}</strong>

                @if($item->stock_qty > 0)
                    <form method="POST" action="{{ route('v2.atk-mks.cart.add') }}" class="atk-mks-qty-form">
                        @csrf
                        <input type="hidden" name="atk_item_id" value="{{ $item->id }}">
                        <div class="atk-mks-stepper" data-atk-mks-stepper>
                            <button class="atk-mks-stepper-btn" type="button" data-atk-mks-stepper-decrease aria-label="Kurangi jumlah {{ $item->name }}" aria-disabled="true" disabled>&minus;</button>
                            <input class="atk-mks-stepper-input" type="number" name="qty" min="1" max="{{ $item->stock_qty }}" value="1" inputmode="numeric" aria-label="Jumlah {{ $item->name }}" data-atk-mks-stepper-input>
                            <button class="atk-mks-stepper-btn" type="button" data-atk-mks-stepper-increase aria-label="Tambah jumlah {{ $item->name }}" aria-disabled="{{ $item->stock_qty <= 1 ? 'true' : 'false' }}" @disabled($item->stock_qty <= 1)>+</button>
                        </div>
                        <button class="atk-mks-btn atk-mks-btn-primary atk-mks-add-button" type="submit">Tambah</button>
                    </form>
                @else
                    <button class="atk-mks-btn atk-mks-btn-soft atk-mks-add-button" type="button" disabled>Stok Habis</button>
                @endif
            </article>
        @empty
            <div class="atk-mks-card atk-mks-empty">Barang belum tersedia.</div>
        @endforelse
    </div>
    <x-pagination :items="$items" preserve-query />

    <script>
        (() => {
            const syncStepper = stepper => {
                const quantityInput = stepper.querySelector('[data-atk-mks-stepper-input]');
                const decrease = stepper.querySelector('[data-atk-mks-stepper-decrease]');
                const increase = stepper.querySelector('[data-atk-mks-stepper-increase]');
                if (!quantityInput || !decrease || !increase) return;
                const value = Number(quantityInput.value), min = Number(quantityInput.min), max = Number(quantityInput.max);
                decrease.disabled = !Number.isFinite(value) || value <= min;
                increase.disabled = !Number.isFinite(value) || value >= max;
                decrease.setAttribute('aria-disabled', decrease.disabled ? 'true' : 'false');
                increase.setAttribute('aria-disabled', increase.disabled ? 'true' : 'false');
            };
            const syncAllSteppers = () => document.querySelectorAll('[data-atk-mks-stepper]').forEach(syncStepper);
            document.addEventListener('click', event => {
                const button = event.target.closest('[data-atk-mks-stepper-decrease], [data-atk-mks-stepper-increase]');
                if (!button) return;
                const stepper = button.closest('[data-atk-mks-stepper]');
                const quantityInput = stepper.querySelector('[data-atk-mks-stepper-input]');
                button.hasAttribute('data-atk-mks-stepper-decrease') ? quantityInput.stepDown() : quantityInput.stepUp();
                syncStepper(stepper);
            });
            document.addEventListener('change', event => {
                if (!event.target.matches('[data-atk-mks-stepper-input]')) return;
                const quantityInput = event.target;
                const value = Number(quantityInput.value), min = Number(quantityInput.min), max = Number(quantityInput.max);
                if (!Number.isFinite(value) || value < min) quantityInput.value = min;
                if (Number(quantityInput.value) > max) quantityInput.value = max;
                syncStepper(quantityInput.closest('[data-atk-mks-stepper]'));
            });
            syncAllSteppers();
        })();
    </script>
    <style>
        .atk-mks-catalog-search { margin-bottom:12px; padding:8px; }
        .atk-mks-catalog-grid { grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; }
        .atk-mks-product { display:flex; min-width:0; flex-direction:column; gap:8px; padding:8px; border-radius:14px; }
        .atk-mks-product-media { display:flex; align-items:center; justify-content:center; width:100%; height:clamp(104px,30vw,120px); overflow:hidden; border-radius:12px; background:var(--atk-mks-soft); color:var(--atk-mks-muted); font-size:11px; font-weight:700; }
        .atk-mks-product-media img { width:100%; height:100%; object-fit:contain; }
        .atk-mks-product-title { display:-webkit-box; overflow:hidden; -webkit-box-orient:vertical; -webkit-line-clamp:2; min-height:2.7em; margin:0; font-size:clamp(12px,3.4vw,14px)!important; line-height:1.35; overflow-wrap:anywhere; }
        .atk-mks-product-stock { font-size:12px; }
        .atk-mks-qty-form { display:grid; grid-template-columns:1fr; gap:8px; margin-top:auto; }
        .atk-mks-stepper { display:grid; grid-template-columns:44px minmax(0,1fr) 44px; min-height:44px; overflow:hidden; border:1px solid var(--atk-mks-border); border-radius:11px; background:#fff; }
        .atk-mks-stepper-btn,.atk-mks-stepper-input { min-width:0; min-height:44px; border:0; background:transparent; color:var(--atk-mks-text); font:inherit; font-size:13px; font-weight:700; text-align:center; }
        .atk-mks-stepper-btn { padding:0; color:var(--atk-mks-primary); cursor:pointer; }
        .atk-mks-stepper-btn:first-child { border-right:1px solid var(--atk-mks-border); }
        .atk-mks-stepper-btn:last-child { border-left:1px solid var(--atk-mks-border); }
        .atk-mks-stepper-btn:active:not(:disabled) { background:var(--atk-mks-soft); }
        .atk-mks-stepper-btn:disabled { color:var(--atk-mks-muted); cursor:not-allowed; opacity:.45; }
        .atk-mks-stepper-input { width:100%; padding:0 2px; appearance:textfield; -moz-appearance:textfield; }
        .atk-mks-stepper-input::-webkit-inner-spin-button,.atk-mks-stepper-input::-webkit-outer-spin-button { margin:0; appearance:none; }
        .atk-mks-stepper-btn:focus-visible,.atk-mks-stepper-input:focus-visible { position:relative; z-index:1; outline:2px solid var(--atk-mks-primary); outline-offset:-2px; }
        .atk-mks-add-button { width:100%; border-radius:11px; }
        @media (hover:hover) { .atk-mks-stepper-btn:hover:not(:disabled) { background:var(--atk-mks-soft); } }
        @media (min-width:640px) {
            .atk-mks-catalog-grid { grid-template-columns:repeat(auto-fill,minmax(210px,1fr)); gap:14px; }
            .atk-mks-product { gap:12px; padding:12px; border-radius:18px; }
            .atk-mks-product-media { height:auto; aspect-ratio:4/3; border-radius:16px; }
            .atk-mks-product-title { min-height:0; font-size:15px!important; }
            .atk-mks-product-stock { font-size:14px; }
            .atk-mks-add-button { border-radius:14px; }
        }
    </style>
</x-atk-mks-app>


