<x-ops-app title="Keranjang OPS">
    <div class="ops-cart-header">
        <div>
            <h1 class="ops-title">Keranjang</h1>
            <p class="ops-subtitle">Periksa barang sebelum mengajukan.</p>
        </div>
        <a class="ops-btn ops-btn-soft" href="{{ route('v2.ops.catalog') }}">Tambah Barang</a>
    </div>

    @if($cartRows->isEmpty())
        <div class="ops-card ops-empty">
            <p>Keranjang masih kosong.</p>
            <a class="ops-btn ops-btn-primary" href="{{ route('v2.ops.catalog') }}">Lihat Katalog</a>
        </div>
    @else
        <div class="ops-card ops-cart-panel">
            <div class="ops-cart-table-wrap">
                <table class="ops-cart-table">
                    <thead>
                        <tr><th>Barang</th><th>Jumlah</th><th>Stok</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach($cartRows as $row)
                            <tr class="ops-cart-row">
                                <td class="ops-cart-item-cell"><strong>{{ $row['item']->name }}</strong></td>
                                <td class="ops-cart-qty-cell">
                                    <span class="ops-cart-mobile-label">Jumlah</span>
                                    <div class="ops-cart-stepper" data-ops-cart-stepper data-min="1" data-max="{{ $row['item']->stock_qty }}">
                                        <button class="ops-cart-stepper-btn" data-ops-cart-decrease type="button" aria-label="Kurangi jumlah {{ $row['item']->name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/></svg>
                                        </button>
                                        <form class="ops-cart-qty-form" method="POST" action="{{ route('v2.ops.cart.update', $row['item']) }}">
                                            @csrf
                                            @method('PUT')
                                            <input class="ops-cart-stepper-input" type="number" name="qty" value="{{ $row['qty'] }}" min="1" max="{{ $row['item']->stock_qty }}" aria-label="Jumlah {{ $row['item']->name }}" readonly required>
                                            <span class="ops-cart-stepper-unit">{{ $row['item']->unit_name }}</span>
                                        </form>
                                        <button class="ops-cart-stepper-btn" data-ops-cart-increase type="button" aria-label="Tambah jumlah {{ $row['item']->name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="ops-cart-stock-cell">
                                    <span class="ops-cart-mobile-label">Stok</span>
                                    <strong>{{ $row['item']->stock_qty }} {{ $row['item']->unit_name }}</strong>
                                </td>
                                <td class="ops-cart-action-cell">
                                    <form method="POST" action="{{ route('v2.ops.cart.remove', $row['item']) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ops-btn ops-cart-remove" type="submit">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <form method="POST" action="{{ route('v2.ops.cart.submit') }}" class="ops-cart-submit-form">
                @csrf
                <label class="ops-label" for="notes">Catatan (opsional)</label>
                <textarea class="ops-textarea" id="notes" name="notes" maxlength="1000" placeholder="Catatan kebutuhan barang..."></textarea>
                <div class="ops-cart-submit-actions">
                    <button class="ops-btn ops-btn-primary ops-cart-submit" type="submit">Ajukan Permintaan</button>
                </div>
            </form>
        </div>
    @endif

    <style>
        .ops-cart-header { display:flex; flex-direction:column; align-items:stretch; justify-content:space-between; gap:14px; margin-bottom:18px; }
        .ops-cart-table-wrap { overflow-x:auto; border:1px solid var(--ops-border); border-radius:16px; background:#fff; }
        .ops-cart-table { width:100%; min-width:680px; border-collapse:collapse; }
        .ops-cart-table th,.ops-cart-table td { padding:12px 14px; border-bottom:1px solid var(--ops-border); text-align:left; font-size:13px; }
        .ops-cart-table th { color:var(--ops-muted); background:#F4FBF8; font-size:11px; letter-spacing:.04em; text-transform:uppercase; }
        .ops-cart-table tr:last-child td { border-bottom:0; }
        .ops-cart-mobile-label { display:none; }
        .ops-cart-stepper { display:inline-flex; align-items:stretch; gap:6px; }
        .ops-cart-stepper-btn { display:inline-flex; align-items:center; justify-content:center; width:44px; height:44px; flex-shrink:0; border:1px solid var(--ops-border); border-radius:12px; background:#fff; color:var(--ops-dark); cursor:pointer; transition:background .15s ease,transform .1s ease,opacity .15s ease; }
        .ops-cart-stepper-btn svg { width:18px; height:18px; }
        .ops-cart-stepper-btn:hover:not(:disabled) { background:var(--ops-soft); }
        .ops-cart-stepper-btn:active:not(:disabled) { transform:scale(.94); }
        .ops-cart-stepper-btn:focus-visible { outline:none; border-color:var(--ops-primary); box-shadow:0 0 0 4px rgba(15,118,110,.14); }
        .ops-cart-stepper-btn:disabled { cursor:not-allowed; opacity:.4; }
        .ops-cart-qty-form { display:inline-flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; }
        .ops-cart-stepper-input { width:52px; height:44px; border:1px solid var(--ops-border); border-radius:12px; background:#fff; color:var(--ops-text); text-align:center; font:inherit; font-size:15px; font-weight:800; -moz-appearance:textfield; }
        .ops-cart-stepper-input::-webkit-outer-spin-button,.ops-cart-stepper-input::-webkit-inner-spin-button { margin:0; -webkit-appearance:none; }
        .ops-cart-stepper-unit { color:var(--ops-muted); font-size:10px; font-weight:700; line-height:1; }
        .ops-cart-remove { color:var(--ops-muted); background:#F1F1F3; }
        .ops-cart-submit-form { margin-top:14px; }
        .ops-cart-submit-actions { display:flex; justify-content:flex-end; margin-top:14px; }
        .ops-cart-submit { width:100%; }
        .ops-cart-toast { position:fixed; left:50%; bottom:24px; z-index:1500; max-width:calc(100vw - 32px); padding:12px 16px; border-radius:12px; background:#F59E0B; color:#fff; font-size:13px; font-weight:600; opacity:0; pointer-events:none; transform:translate(-50%,16px); transition:opacity .2s ease,transform .2s ease; box-shadow:0 8px 24px rgba(245,158,11,.35); }
        .ops-cart-toast.show { opacity:1; transform:translate(-50%,0); }

        @media (max-width:639px) {
            .ops-cart-panel { padding:12px; }
            .ops-cart-table-wrap { overflow:visible; border:0; border-radius:0; background:transparent; }
            .ops-cart-table { display:block; min-width:0; }
            .ops-cart-table thead { display:none; }
            .ops-cart-table tbody { display:grid; gap:12px; }
            .ops-cart-table .ops-cart-row { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:14px 10px; padding:14px; border:1px solid var(--ops-border); border-radius:16px; background:#fff; }
            .ops-cart-table .ops-cart-row td { padding:0; border:0; }
            .ops-cart-item-cell,.ops-cart-qty-cell { grid-column:1/-1; }
            .ops-cart-mobile-label { display:block; margin-bottom:6px; color:var(--ops-muted); font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
            .ops-cart-stock-cell { align-self:center; }
            .ops-cart-action-cell { align-self:end; }
        }

        @media (min-width:640px) {
            .ops-cart-header { flex-direction:row; align-items:center; }
            .ops-cart-submit { width:auto; }
        }
    </style>

    <script>
        (function () {
            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            csrfToken = csrfToken ? csrfToken.getAttribute('content') : '';

            function showToast(message) {
                var toast = document.getElementById('opsCartToast');
                if (toast) toast.remove();
                toast = document.createElement('div');
                toast.id = 'opsCartToast';
                toast.className = 'ops-cart-toast';
                toast.textContent = message;
                document.body.appendChild(toast);
                requestAnimationFrame(function () { toast.classList.add('show'); });
                setTimeout(function () {
                    toast.classList.remove('show');
                    setTimeout(function () { toast.remove(); }, 200);
                }, 2500);
            }

            function updateCartBadge(count) {
                var badge = document.querySelector('.ops-cart-count');
                if (badge) badge.textContent = count > 99 ? '99+' : count;
            }

            document.querySelectorAll('[data-ops-cart-stepper]').forEach(function (stepper) {
                var min = parseInt(stepper.dataset.min, 10) || 1;
                var max = parseInt(stepper.dataset.max, 10) || 9999;
                var decrease = stepper.querySelector('[data-ops-cart-decrease]');
                var increase = stepper.querySelector('[data-ops-cart-increase]');
                var input = stepper.querySelector('.ops-cart-stepper-input');
                var form = stepper.querySelector('.ops-cart-qty-form');
                var busy = false;

                function syncButtons() {
                    var value = parseInt(input.value, 10) || min;
                    decrease.disabled = busy || value <= min;
                    increase.disabled = busy || value >= max;
                }

                function changeBy(delta) {
                    if (busy) return;
                    var current = parseInt(input.value, 10) || min;
                    var next = Math.min(max, Math.max(min, current + delta));
                    if (next === current) return;

                    busy = true;
                    input.value = next;
                    syncButtons();
                    var formData = new FormData(form);
                    formData.set('qty', next);

                    fetch(form.action, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN':csrfToken, 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' },
                        body: formData,
                    })
                        .then(function (response) { return response.json(); })
                        .then(function (data) {
                            busy = false;
                            if (data.success) {
                                input.value = data.qty;
                                updateCartBadge(data.cartCount);
                            } else {
                                input.value = current;
                                showToast(data.message || 'Jumlah tidak dapat diperbarui.');
                            }
                            syncButtons();
                        })
                        .catch(function () {
                            busy = false;
                            input.value = current;
                            showToast('Gagal memperbarui jumlah. Coba lagi.');
                            syncButtons();
                        });
                }

                decrease.addEventListener('click', function () { changeBy(-1); });
                increase.addEventListener('click', function () { changeBy(1); });
                syncButtons();
            });
        })();
    </script>
</x-ops-app>
