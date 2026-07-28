<x-atk-app title="Keranjang ATK">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Keranjang</h1>
            <p class="atk-subtitle">Review barang sebelum mengajukan permintaan.</p>
        </div>
        <a class="atk-btn atk-btn-secondary" href="{{ route('v2.atk.catalog') }}">Tambah Barang</a>
    </div>

    @if($cartRows->isEmpty())
        <div class="atk-card atk-empty">
            <p>Keranjang masih kosong.</p>
            <a class="atk-btn atk-btn-primary" href="{{ route('v2.atk.catalog') }}">Lihat Katalog</a>
        </div>
    @else
        <div class="atk-card atk-cart-panel">
            <div class="atk-table-wrap atk-cart-table-wrap">
                <table class="atk-table atk-cart-table">
                    <thead><tr><th>Barang</th><th>Qty</th><th>Setara</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @foreach($cartRows as $row)
                            <tr class="atk-cart-row" data-unit-size="{{ $row['item']->unit_size }}" data-content-unit="{{ $row['item']->content_unit_name }}">
                                <td class="atk-cart-item-cell"><strong>{{ $row['item']->name }}</strong></td>
                                <td class="atk-cart-qty-cell">
                                    <span class="atk-cart-mobile-label">Jumlah</span>
                                    <div class="atk-stepper" data-min="1" data-max="{{ $row['item']->stock_qty }}">
                                        <button class="atk-stepper-btn atk-stepper-minus" type="button" aria-label="Kurangi jumlah {{ $row['item']->name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14"/></svg>
                                        </button>
                                        <form class="atk-cart-qty-form" method="POST" action="{{ route('v2.atk.cart.update', $row['item']) }}">
                                            @csrf
                                            @method('PUT')
                                            <input class="atk-stepper-input" type="number" name="qty" value="{{ $row['qty'] }}" min="1" max="{{ $row['item']->stock_qty }}" aria-label="Jumlah {{ $row['item']->name }}" readonly required>
                                            <span class="atk-stepper-unit">{{ $row['item']->unit_name }}</span>
                                        </form>
                                        <button class="atk-stepper-btn atk-stepper-plus" type="button" aria-label="Tambah jumlah {{ $row['item']->name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="atk-cart-equivalent-cell">
                                    <span class="atk-cart-mobile-label">Setara</span>
                                    <strong class="atk-cart-equivalent-value">{{ $row['qty'] * $row['item']->unit_size }} {{ $row['item']->content_unit_name }}</strong>
                                </td>
                                <td class="atk-cart-action-cell">
                                    <form method="POST" action="{{ route('v2.atk.cart.remove', $row['item']) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="atk-btn atk-btn-muted" type="submit">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <form method="POST" action="{{ route('v2.atk.cart.submit') }}" style="margin-top:14px">
                @csrf
                <label class="atk-label" for="notes">Catatan</label>
                <textarea class="atk-textarea" id="notes" name="notes" placeholder="Catatan kebutuhan barang..."></textarea>
                <div class="atk-actions" style="justify-content:flex-end;margin-top:14px">
                    <button class="atk-btn atk-btn-primary atk-cart-submit" type="submit">Ajukan Permintaan</button>
                </div>
            </form>
        </div>
    @endif

    <style>
        .atk-cart-mobile-label {
            display: block;
            margin-bottom: 6px;
            color: var(--atk-muted);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        /* Qty stepper: [−] input [+] */
        .atk-stepper {
            display: inline-flex;
            align-items: stretch;
            gap: 6px;
        }
        .atk-stepper-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            flex-shrink: 0;
            border: 1px solid var(--atk-border);
            border-radius: 12px;
            background: var(--atk-surface);
            color: var(--atk-primary-dark);
            cursor: pointer;
            transition: background .15s ease, transform .1s ease, opacity .15s ease;
        }
        .atk-stepper-btn svg {
            width: 18px;
            height: 18px;
        }
        .atk-stepper-btn:hover:not(:disabled) {
            background: var(--atk-primary-soft);
        }
        .atk-stepper-btn:active:not(:disabled) {
            transform: scale(.94);
        }
        .atk-stepper-btn:focus-visible {
            outline: none;
            box-shadow: 0 0 0 4px rgba(124, 77, 222, .14);
            border-color: var(--atk-primary);
        }
        .atk-stepper-btn:disabled {
            opacity: .4;
            cursor: not-allowed;
        }
        .atk-cart-qty-form {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
        }
        .atk-stepper-input {
            width: 52px;
            height: 44px;
            text-align: center;
            border: 1px solid var(--atk-border);
            border-radius: 12px;
            font: inherit;
            font-size: 15px;
            font-weight: 800;
            color: var(--atk-text);
            background: var(--atk-surface);
            -moz-appearance: textfield;
        }
        .atk-stepper-input::-webkit-outer-spin-button,
        .atk-stepper-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .atk-stepper-input[readonly] {
            color: var(--atk-text);
            background: var(--atk-surface);
        }
        .atk-stepper-unit {
            font-size: 10px;
            font-weight: 700;
            color: var(--atk-muted);
            line-height: 1;
        }
        .atk-cart-submit {
            width: 100%;
        }
        /* Toast feedback untuk pesan warning qty (mis. stok tidak cukup). */
        .atk-cart-toast {
            position: fixed;
            left: 50%;
            bottom: 24px;
            transform: translate(-50%, 16px);
            max-width: calc(100vw - 32px);
            padding: 12px 16px;
            border-radius: 12px;
            background: var(--warning);
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.35);
            opacity: 0;
            transition: opacity .2s ease, transform .2s ease;
            z-index: 1500;
            pointer-events: none;
        }
        .atk-cart-toast.show {
            opacity: 1;
            transform: translate(-50%, 0);
        }

        @media (max-width: 639px) {
            .atk-cart-panel {
                padding: 12px;
            }
            .atk-cart-table-wrap {
                overflow: visible;
                border: 0;
                border-radius: 0;
                background: transparent;
            }
            .atk-cart-table {
                display: block;
                min-width: 0;
            }
            .atk-cart-table thead {
                display: none;
            }
            .atk-cart-table tbody {
                display: grid;
                gap: 12px;
            }
            .atk-cart-table .atk-cart-row {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                gap: 14px 10px;
                padding: 14px;
                border: 1px solid var(--atk-border);
                border-radius: 16px;
                background: var(--atk-surface);
            }
            .atk-cart-table .atk-cart-row td {
                padding: 0;
                border: 0;
            }
            .atk-cart-item-cell,
            .atk-cart-qty-cell {
                grid-column: 1 / -1;
            }
            .atk-cart-item-cell strong {
                display: block;
                font-size: 14px;
                line-height: 1.4;
            }
            .atk-cart-equivalent-cell {
                align-self: center;
            }
            .atk-cart-equivalent-cell .atk-cart-mobile-label {
                margin-bottom: 3px;
            }
            .atk-cart-equivalent-cell strong {
                font-size: 13px;
            }
            .atk-cart-action-cell {
                align-self: end;
            }
            .atk-cart-action-cell .atk-btn {
                min-height: 40px;
                padding-inline: 14px;
            }
        }

        @media (min-width: 640px) {
            .atk-cart-mobile-label {
                display: none;
            }
            .atk-cart-submit {
                width: auto;
            }
        }
    </style>
    <script>
        (function () {
            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            csrfToken = csrfToken ? csrfToken.getAttribute('content') : '';

            function formatNumber(value) {
                return String(value).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            // Update badge total qty di topbar (dibagikan dengan layout atk-app).
            function updateCartBadge(count) {
                var badges = document.querySelectorAll('.atk-cart-badge');
                var label = document.querySelector('.atk-cart-shortcut');
                badges.forEach(function (badge) {
                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.style.display = '';
                    } else {
                        badge.style.display = 'none';
                    }
                });
                if (label) {
                    label.setAttribute('aria-label', 'Buka keranjang, ' + count + ' item');
                }
            }

            // Toast singkat untuk pesan warning (mis. stok tidak cukup).
            function showToast(message) {
                var existing = document.getElementById('atkCartToast');
                if (existing) existing.remove();
                var toast = document.createElement('div');
                toast.id = 'atkCartToast';
                toast.className = 'atk-cart-toast';
                toast.textContent = message;
                document.body.appendChild(toast);
                requestAnimationFrame(function () { toast.classList.add('show'); });
                setTimeout(function () {
                    toast.classList.remove('show');
                    setTimeout(function () { toast.remove(); }, 200);
                }, 2500);
            }

            document.querySelectorAll('.atk-stepper').forEach(function (stepper) {
                var min = parseInt(stepper.getAttribute('data-min'), 10) || 1;
                var max = parseInt(stepper.getAttribute('data-max'), 10) || 9999;
                var row = stepper.closest('.atk-cart-row');
                var minusBtn = stepper.querySelector('.atk-stepper-minus');
                var plusBtn = stepper.querySelector('.atk-stepper-plus');
                var input = stepper.querySelector('.atk-stepper-input');
                var form = stepper.querySelector('.atk-cart-qty-form');
                var equivalent = row ? row.querySelector('.atk-cart-equivalent-value') : null;
                var unitSize = row ? parseInt(row.getAttribute('data-unit-size'), 10) : 1;
                var contentUnit = row ? row.getAttribute('data-content-unit') : '';
                if (!minusBtn || !plusBtn || !input || !form) return;

                var busy = false;

                function syncButtons() {
                    var value = parseInt(input.value, 10) || min;
                    minusBtn.disabled = value <= min || busy;
                    plusBtn.disabled = value >= max || busy;
                }

                function updateEquivalent(qty) {
                    if (!equivalent) return;
                    equivalent.textContent = formatNumber(qty * unitSize) + ' ' + contentUnit;
                }

                // Ubah qty via fetch (AJAX) — tanpa reload halaman.
                function changeBy(delta) {
                    if (busy) return;
                    var current = parseInt(input.value, 10) || min;
                    var next = Math.min(max, Math.max(min, current + delta));
                    if (next === current) return;

                    busy = true;
                    input.value = next;
                    syncButtons();
                    updateEquivalent(next);

                    var formData = new FormData(form);
                    formData.set('qty', next);

                    fetch(form.getAttribute('action'), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    })
                        .then(function (response) { return response.json(); })
                        .then(function (data) {
                            busy = false;
                            if (data.success && data.qty !== null && data.qty !== undefined) {
                                input.value = data.qty;
                                updateEquivalent(data.qty);
                                updateCartBadge(data.cartCount);
                            } else {
                                // Gagal (mis. stok tidak cukup) — kembalikan ke nilai lama.
                                input.value = current;
                                updateEquivalent(current);
                                if (data.message) showToast(data.message);
                            }
                            syncButtons();
                        })
                        .catch(function () {
                            busy = false;
                            input.value = current;
                            updateEquivalent(current);
                            showToast('Gagal memperbarui jumlah. Coba lagi.');
                            syncButtons();
                        });
                }

                minusBtn.addEventListener('click', function () { changeBy(-1); });
                plusBtn.addEventListener('click', function () { changeBy(1); });
                syncButtons();
            });
        })();
    </script>
</x-atk-app>
