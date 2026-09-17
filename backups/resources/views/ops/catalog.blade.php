<x-ops-app title="Katalog OPS">
    <div class="ops-header"><h1 class="ops-title">Katalog</h1><p class="ops-subtitle">Pilih barang yang dibutuhkan.</p></div>
    <form method="GET" class="ops-card ops-actions ops-catalog-search">
        <input class="ops-input" style="flex:1" name="q" value="{{ request('q') }}" placeholder="Cari barang" autocomplete="off">
        <button class="ops-btn ops-btn-primary" type="submit">Cari</button>
    </form>

    <div class="ops-grid ops-catalog-grid" data-ops-mobile-cards>
        @forelse($items as $item)
            <article class="ops-card ops-product">
                <div class="ops-product-media">
                    @if($item->image_path)
                        <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->name }}" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                        <span hidden>Tanpa foto</span>
                    @else
                        <span>Tanpa foto</span>
                    @endif
                </div>
                <h2 class="ops-product-title">{{ $item->name }}</h2>
                <strong class="ops-product-stock">{{ $item->stock_qty }} {{ $item->unit_name }}</strong>

                @if($item->stock_qty > 0)
                    <form method="POST" action="{{ route('v2.ops.cart.add') }}" class="ops-qty-form">
                        @csrf
                        <input type="hidden" name="atk_item_id" value="{{ $item->id }}">
                        <div class="ops-stepper" data-ops-stepper>
                            <button class="ops-stepper-btn" type="button" data-ops-stepper-decrease aria-label="Kurangi jumlah {{ $item->name }}" aria-disabled="true" disabled>&minus;</button>
                            <input class="ops-stepper-input" type="number" name="qty" min="1" max="{{ $item->stock_qty }}" value="1" inputmode="numeric" aria-label="Jumlah {{ $item->name }}" data-ops-stepper-input>
                            <button class="ops-stepper-btn" type="button" data-ops-stepper-increase aria-label="Tambah jumlah {{ $item->name }}" aria-disabled="{{ $item->stock_qty <= 1 ? 'true' : 'false' }}" @disabled($item->stock_qty <= 1)>+</button>
                        </div>
                        <button class="ops-btn ops-btn-primary ops-add-button" type="submit">Tambah</button>
                    </form>
                @else
                    <button class="ops-btn ops-btn-soft ops-add-button" type="button" disabled>Stok Habis</button>
                @endif
            </article>
        @empty
            <div class="ops-card ops-empty">Barang belum tersedia.</div>
        @endforelse
    </div>
    <x-pagination :items="$items" preserve-query />

    <script>
        (() => {
            const syncStepper = stepper => {
                const quantityInput = stepper.querySelector('[data-ops-stepper-input]');
                const decrease = stepper.querySelector('[data-ops-stepper-decrease]');
                const increase = stepper.querySelector('[data-ops-stepper-increase]');
                if (!quantityInput || !decrease || !increase) return;
                const value = Number(quantityInput.value), min = Number(quantityInput.min), max = Number(quantityInput.max);
                decrease.disabled = !Number.isFinite(value) || value <= min;
                increase.disabled = !Number.isFinite(value) || value >= max;
                decrease.setAttribute('aria-disabled', decrease.disabled ? 'true' : 'false');
                increase.setAttribute('aria-disabled', increase.disabled ? 'true' : 'false');
            };
            const syncAllSteppers = () => document.querySelectorAll('[data-ops-stepper]').forEach(syncStepper);
            document.addEventListener('click', event => {
                const button = event.target.closest('[data-ops-stepper-decrease], [data-ops-stepper-increase]');
                if (!button) return;
                const stepper = button.closest('[data-ops-stepper]');
                const quantityInput = stepper.querySelector('[data-ops-stepper-input]');
                button.hasAttribute('data-ops-stepper-decrease') ? quantityInput.stepDown() : quantityInput.stepUp();
                syncStepper(stepper);
            });
            document.addEventListener('change', event => {
                if (!event.target.matches('[data-ops-stepper-input]')) return;
                const quantityInput = event.target;
                const value = Number(quantityInput.value), min = Number(quantityInput.min), max = Number(quantityInput.max);
                if (!Number.isFinite(value) || value < min) quantityInput.value = min;
                if (Number(quantityInput.value) > max) quantityInput.value = max;
                syncStepper(quantityInput.closest('[data-ops-stepper]'));
            });
            syncAllSteppers();
        })();
    </script>
    <style>
        .ops-catalog-search { margin-bottom:12px; padding:8px; }
        .ops-catalog-grid { grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; }
        .ops-product { display:flex; min-width:0; flex-direction:column; gap:8px; padding:8px; border-radius:14px; }
        .ops-product-media { display:flex; align-items:center; justify-content:center; width:100%; height:clamp(104px,30vw,120px); overflow:hidden; border-radius:12px; background:var(--ops-soft); color:var(--ops-muted); font-size:11px; font-weight:700; }
        .ops-product-media img { width:100%; height:100%; object-fit:contain; }
        .ops-product-title { display:-webkit-box; overflow:hidden; -webkit-box-orient:vertical; -webkit-line-clamp:2; min-height:2.7em; margin:0; font-size:clamp(12px,3.4vw,14px)!important; line-height:1.35; overflow-wrap:anywhere; }
        .ops-product-stock { font-size:12px; }
        .ops-qty-form { display:grid; grid-template-columns:1fr; gap:8px; margin-top:auto; }
        .ops-stepper { display:grid; grid-template-columns:44px minmax(0,1fr) 44px; min-height:44px; overflow:hidden; border:1px solid var(--ops-border); border-radius:11px; background:#fff; }
        .ops-stepper-btn,.ops-stepper-input { min-width:0; min-height:44px; border:0; background:transparent; color:var(--ops-text); font:inherit; font-size:13px; font-weight:700; text-align:center; }
        .ops-stepper-btn { padding:0; color:var(--ops-primary); cursor:pointer; }
        .ops-stepper-btn:first-child { border-right:1px solid var(--ops-border); }
        .ops-stepper-btn:last-child { border-left:1px solid var(--ops-border); }
        .ops-stepper-btn:active:not(:disabled) { background:var(--ops-soft); }
        .ops-stepper-btn:disabled { color:var(--ops-muted); cursor:not-allowed; opacity:.45; }
        .ops-stepper-input { width:100%; padding:0 2px; appearance:textfield; -moz-appearance:textfield; }
        .ops-stepper-input::-webkit-inner-spin-button,.ops-stepper-input::-webkit-outer-spin-button { margin:0; appearance:none; }
        .ops-stepper-btn:focus-visible,.ops-stepper-input:focus-visible { position:relative; z-index:1; outline:2px solid var(--ops-primary); outline-offset:-2px; }
        .ops-add-button { width:100%; border-radius:11px; }
        @media (hover:hover) { .ops-stepper-btn:hover:not(:disabled) { background:var(--ops-soft); } }
        @media (min-width:640px) {
            .ops-catalog-grid { grid-template-columns:repeat(auto-fill,minmax(210px,1fr)); gap:14px; }
            .ops-product { gap:12px; padding:12px; border-radius:18px; }
            .ops-product-media { height:auto; aspect-ratio:4/3; border-radius:16px; }
            .ops-product-title { min-height:0; font-size:15px!important; }
            .ops-product-stock { font-size:14px; }
            .ops-add-button { border-radius:14px; }
        }
    </style>
</x-ops-app>
