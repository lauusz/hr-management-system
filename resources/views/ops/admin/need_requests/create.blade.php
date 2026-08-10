<x-ops-app title="Request Barang OPS">
    <div class="ops-header">
        <h1 class="ops-title">Request Barang</h1>
        <p class="ops-subtitle">Minta barang baru atau tambahan stok kepada Admin ATK.</p>
    </div>

    <form class="ops-card" method="POST" action="{{ route('v2.ops.admin.need-requests.store') }}">
        @csrf
        <input type="hidden" name="atk_item_id" value="{{ $item?->id }}">
        <div class="ops-form-grid">
            <div>
                <label class="ops-label" for="requested_item_name">Nama Barang</label>
                <input class="ops-input" id="requested_item_name" name="requested_item_name" value="{{ old('requested_item_name', $item?->name) }}" required>
            </div>
            <div>
                <label class="ops-label" for="qty">Jumlah</label>
                <div class="ops-need-stepper" data-need-stepper>
                    <button type="button" data-decrease aria-label="Kurangi jumlah">&minus;</button>
                    <input id="qty" type="number" name="qty" min="1" step="1" value="{{ old('qty', 1) }}" inputmode="numeric" required data-qty>
                    <button type="button" data-increase aria-label="Tambah jumlah">+</button>
                </div>
            </div>
            <div>
                <label class="ops-label" for="unit_name">Satuan</label>
                <input class="ops-input" id="unit_name" name="unit_name" value="{{ old('unit_name', $item?->unit_name ?? 'pcs') }}" required>
            </div>
        </div>
        <div style="margin-top:12px">
            <label class="ops-label" for="reason">Alasan</label>
            <textarea class="ops-textarea" id="reason" name="reason" required>{{ old('reason') }}</textarea>
        </div>
        <div class="ops-actions" style="justify-content:flex-end;margin-top:14px">
            <a class="ops-btn ops-btn-soft" href="{{ route('v2.ops.admin.need-requests.index') }}">Batal</a>
            <button class="ops-btn ops-btn-primary" type="submit">Kirim Request</button>
        </div>
    </form>

    <style>
        .ops-need-stepper { display:grid; grid-template-columns:48px minmax(0,1fr) 48px; min-height:44px; overflow:hidden; border:1px solid var(--ops-border); border-radius:12px; background:#fff; }
        .ops-need-stepper button,.ops-need-stepper input { min-width:0; min-height:44px; border:0; background:transparent; color:var(--ops-text); font:inherit; font-weight:800; text-align:center; }
        .ops-need-stepper button { color:var(--ops-dark); cursor:pointer; }
        .ops-need-stepper button:first-child { border-right:1px solid var(--ops-border); }
        .ops-need-stepper button:last-child { border-left:1px solid var(--ops-border); }
        .ops-need-stepper button:disabled { opacity:.4; cursor:not-allowed; }
        .ops-need-stepper input { width:100%; appearance:textfield; -moz-appearance:textfield; }
        .ops-need-stepper input::-webkit-inner-spin-button,.ops-need-stepper input::-webkit-outer-spin-button { appearance:none; margin:0; }
        @media (max-width:639px) { .ops-actions { display:grid; grid-template-columns:1fr 1fr; } }
    </style>
    <script>
        (() => {
            const stepper = document.querySelector('[data-need-stepper]');
            if (!stepper) return;
            const input = stepper.querySelector('[data-qty]');
            const decrease = stepper.querySelector('[data-decrease]');
            const value = () => Math.max(1, Number.parseInt(input.value, 10) || 1);
            const sync = () => decrease.disabled = value() <= 1;
            decrease.addEventListener('click', () => { input.value = Math.max(1, value() - 1); sync(); });
            stepper.querySelector('[data-increase]').addEventListener('click', () => { input.value = value() + 1; sync(); });
            input.addEventListener('input', sync);
            input.addEventListener('blur', () => { input.value = value(); sync(); });
            sync();
        })();
    </script>
</x-ops-app>
