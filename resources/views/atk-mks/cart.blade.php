<x-atk-mks-app title="Keranjang ATK MKS">
    <div class="atk-mks-cart-header">
        <div>
            <h1 class="atk-mks-title">Keranjang</h1>
            <p class="atk-mks-subtitle">Periksa barang sebelum mengajukan.</p>
        </div>
        <a class="atk-mks-btn atk-mks-btn-soft" href="{{ route('v2.atk-mks.catalog') }}">Tambah Barang</a>
    </div>

    @if($cartRows->isEmpty())
        <div class="atk-mks-card atk-mks-empty">
            <p>Keranjang masih kosong.</p>
            <a class="atk-mks-btn atk-mks-btn-primary" href="{{ route('v2.atk-mks.catalog') }}">Lihat Katalog</a>
        </div>
    @else
        <div class="atk-mks-card atk-mks-cart-panel">
            <div class="atk-mks-cart-table-wrap">
                <table class="atk-mks-cart-table">
                    <thead>
                        <tr><th>Barang</th><th>Jumlah</th><th>Stok</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach($cartRows as $row)
                            <tr class="atk-mks-cart-row">
                                <td class="atk-mks-cart-item-cell"><strong>{{ $row['item']->name }}</strong></td>
                                <td class="atk-mks-cart-qty-cell">
                                    <span class="atk-mks-cart-mobile-label">Jumlah</span>
                                    <div class="atk-mks-cart-stepper" data-atk-mks-cart-stepper data-min="1" data-max="{{ $row['item']->stock_qty }}">
                                        <button class="atk-mks-cart-stepper-btn" data-atk-mks-cart-decrease type="button" aria-label="Kurangi jumlah {{ $row['item']->name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/></svg>
                                        </button>
                                        <form class="atk-mks-cart-qty-form" method="POST" action="{{ route('v2.atk-mks.cart.update', $row['item']) }}">
                                            @csrf
                                            @method('PUT')
                                            <input class="atk-mks-cart-stepper-input" type="number" name="qty" value="{{ $row['qty'] }}" min="1" max="{{ $row['item']->stock_qty }}" aria-label="Jumlah {{ $row['item']->name }}" readonly required>
                                            <span class="atk-mks-cart-stepper-unit">{{ $row['item']->unit_name }}</span>
                                        </form>
                                        <button class="atk-mks-cart-stepper-btn" data-atk-mks-cart-increase type="button" aria-label="Tambah jumlah {{ $row['item']->name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="atk-mks-cart-stock-cell">
                                    <span class="atk-mks-cart-mobile-label">Stok</span>
                                    <strong>{{ $row['item']->stock_qty }} {{ $row['item']->unit_name }}</strong>
                                </td>
                                <td class="atk-mks-cart-action-cell">
                                    <form method="POST" action="{{ route('v2.atk-mks.cart.remove', $row['item']) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="atk-mks-btn atk-mks-cart-remove" type="submit">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <form method="POST" action="{{ route('v2.atk-mks.cart.submit') }}" class="atk-mks-cart-submit-form">
                @csrf
                <label class="atk-mks-label" for="notes">Catatan (opsional)</label>
                <textarea class="atk-mks-textarea" id="notes" name="notes" maxlength="1000" placeholder="Catatan kebutuhan barang..."></textarea>
                <div class="atk-mks-cart-submit-actions">
                    <button class="atk-mks-btn atk-mks-btn-primary atk-mks-cart-submit" type="submit">Ajukan Permintaan</button>
                </div>
            </form>
        </div>
    @endif

    <style>
        .atk-mks-cart-header { display:flex; flex-direction:column; align-items:stretch; justify-content:space-between; gap:14px; margin-bottom:18px; }
        .atk-mks-cart-table-wrap { overflow-x:auto; border:1px solid var(--atk-mks-border); border-radius:16px; background:#fff; }
        .atk-mks-cart-table { width:100%; min-width:680px; border-collapse:collapse; }
        .atk-mks-cart-table th,.atk-mks-cart-table td { padding:12px 14px; border-bottom:1px solid var(--atk-mks-border); text-align:left; font-size:13px; }
        .atk-mks-cart-table th { color:var(--atk-mks-muted); background:#F4FBF8; font-size:11px; letter-spacing:.04em; text-transform:uppercase; }
        .atk-mks-cart-table tr:last-child td { border-bottom:0; }
        .atk-mks-cart-mobile-label { display:none; }
        .atk-mks-cart-stepper { display:inline-flex; align-items:stretch; gap:6px; }
        .atk-mks-cart-stepper-btn { display:inline-flex; align-items:center; justify-content:center; width:44px; height:44px; flex-shrink:0; border:1px solid var(--atk-mks-border); border-radius:12px; background:#fff; color:var(--atk-mks-dark); cursor:pointer; transition:background .15s ease,transform .1s ease,opacity .15s ease; }
        .atk-mks-cart-stepper-btn svg { width:18px; height:18px; }
        .atk-mks-cart-stepper-btn:hover:not(:disabled) { background:var(--atk-mks-soft); }
        .atk-mks-cart-stepper-btn:active:not(:disabled) { transform:scale(.94); }
        .atk-mks-cart-stepper-btn:focus-visible { outline:none; border-color:var(--atk-mks-primary); box-shadow:0 0 0 4px rgba(15,118,110,.14); }
        .atk-mks-cart-stepper-btn:disabled { cursor:not-allowed; opacity:.4; }
        .atk-mks-cart-qty-form { display:inline-flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; }
        .atk-mks-cart-stepper-input { width:52px; height:44px; border:1px solid var(--atk-mks-border); border-radius:12px; background:#fff; color:var(--atk-mks-text); text-align:center; font:inherit; font-size:15px; font-weight:800; -moz-appearance:textfield; }
        .atk-mks-cart-stepper-input::-webkit-outer-spin-button,.atk-mks-cart-stepper-input::-webkit-inner-spin-button { margin:0; -webkit-appearance:none; }
        .atk-mks-cart-stepper-unit { color:var(--atk-mks-muted); font-size:10px; font-weight:700; line-height:1; }
        .atk-mks-cart-remove { color:var(--atk-mks-muted); background:#F1F1F3; }
        .atk-mks-cart-submit-form { margin-top:14px; }
        .atk-mks-cart-submit-actions { display:flex; justify-content:flex-end; margin-top:14px; }
        .atk-mks-cart-submit { width:100%; }
        .atk-mks-cart-toast { position:fixed; left:50%; bottom:24px; z-index:1500; max-width:calc(100vw - 32px); padding:12px 16px; border-radius:12px; background:#F59E0B; color:#fff; font-size:13px; font-weight:600; opacity:0; pointer-events:none; transform:translate(-50%,16px); transition:opacity .2s ease,transform .2s ease; box-shadow:0 8px 24px rgba(245,158,11,.35); }
        .atk-mks-cart-toast.show { opacity:1; transform:translate(-50%,0); }

        @media (max-width:639px) {
            .atk-mks-cart-panel { padding:12px; }
            .atk-mks-cart-table-wrap { overflow:visible; border:0; border-radius:0; background:transparent; }
            .atk-mks-cart-table { display:block; min-width:0; }
            .atk-mks-cart-table thead { display:none; }
            .atk-mks-cart-table tbody { display:grid; gap:12px; }
            .atk-mks-cart-table .atk-mks-cart-row { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:14px 10px; padding:14px; border:1px solid var(--atk-mks-border); border-radius:16px; background:#fff; }
            .atk-mks-cart-table .atk-mks-cart-row td { padding:0; border:0; }
            .atk-mks-cart-item-cell,.atk-mks-cart-qty-cell { grid-column:1/-1; }
            .atk-mks-cart-mobile-label { display:block; margin-bottom:6px; color:var(--atk-mks-muted); font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
            .atk-mks-cart-stock-cell { align-self:center; }
            .atk-mks-cart-action-cell { align-self:end; }
        }

        @media (min-width:640px) {
            .atk-mks-cart-header { flex-direction:row; align-items:center; }
            .atk-mks-cart-submit { width:auto; }
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
                toast.className = 'atk-mks-cart-toast';
                toast.textContent = message;
                document.body.appendChild(toast);
                requestAnimationFrame(function () { toast.classList.add('show'); });
                setTimeout(function () {
                    toast.classList.remove('show');
                    setTimeout(function () { toast.remove(); }, 200);
                }, 2500);
            }

            function updateCartBadge(count) {
                document.querySelectorAll('.atk-mks-cart-count, .atk-mks-cart-nav-badge').forEach(function (badge) {
                    badge.textContent = count > 99 ? '99+' : count;
                });
            }

            document.querySelectorAll('[data-atk-mks-cart-stepper]').forEach(function (stepper) {
                var min = parseInt(stepper.dataset.min, 10) || 1;
                var max = parseInt(stepper.dataset.max, 10) || 9999;
                var decrease = stepper.querySelector('[data-atk-mks-cart-decrease]');
                var increase = stepper.querySelector('[data-atk-mks-cart-increase]');
                var input = stepper.querySelector('.atk-mks-cart-stepper-input');
                var form = stepper.querySelector('.atk-mks-cart-qty-form');
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
</x-atk-mks-app>


