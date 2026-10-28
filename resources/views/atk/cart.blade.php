<x-atk-app title="Keranjang ATK">
    @php
        $totalItems = $cartRows->count();
        $totalQty = $cartRows->sum('qty');
    @endphp

    <div class="atk-cart-toolbar">
        <div class="atk-cart-heading">
            <h1 class="atk-title">Keranjang</h1>
            @if($totalItems > 0)
                <span class="atk-cart-count-pill" id="atkCartKindCount">{{ $totalItems }} barang</span>
            @endif
        </div>
        <a class="atk-btn atk-btn-secondary atk-cart-back-btn" href="{{ route('v2.atk.catalog') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            <span>Tambah Barang</span>
        </a>
    </div>

    @if($cartRows->isEmpty())
        <div class="atk-card atk-empty atk-cart-empty">
            <div class="atk-cart-empty-icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 4h2l2.2 10.1a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20.5 8H6"/>
                    <circle cx="10" cy="20" r="1"/>
                    <circle cx="18" cy="20" r="1"/>
                </svg>
            </div>
            <p class="atk-cart-empty-title">Keranjang masih kosong</p>
            <a class="atk-btn atk-btn-primary" href="{{ route('v2.atk.catalog') }}">Lihat Katalog</a>
        </div>
    @else
        <div class="atk-cart-layout">
            {{-- Daftar Barang --}}
            <div class="atk-card atk-cart-panel">
                <div class="atk-table-wrap atk-cart-table-wrap">
                    <table class="atk-table atk-cart-table">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th>Setara</th>
                                <th style="width:1%;white-space:nowrap;text-align:right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cartRows as $row)
                                @php
                                    $item = $row['item'];
                                    $hasConversion = $item->unit_size != 1 || $item->unit_name !== $item->content_unit_name;
                                @endphp
                                <tr class="atk-cart-row" data-unit-size="{{ $item->unit_size }}" data-content-unit="{{ $item->content_unit_name }}">
                                    <td class="atk-cart-item-cell">
                                        <strong class="atk-cart-item-name">{{ $item->name }}</strong>
                                        <span class="atk-cart-item-meta">
                                            Stok tersedia: {{ $item->stock_qty }} {{ $item->unit_name }}
                                            @if($hasConversion)
                                                · 1 {{ $item->unit_name }} = {{ $item->unit_size }} {{ $item->content_unit_name }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="atk-cart-qty-cell">
                                        <span class="atk-cart-mobile-label">Jumlah</span>
                                        <div class="atk-cart-qty-wrap">
                                            <div class="atk-stepper" data-min="1" data-max="{{ $item->stock_qty }}">
                                                <button class="atk-stepper-btn atk-stepper-minus" type="button" aria-label="Kurangi jumlah {{ $item->name }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14"/></svg>
                                                </button>
                                                <form class="atk-cart-qty-form" method="POST" action="{{ route('v2.atk.cart.update', $item) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <input class="atk-stepper-input" type="number" name="qty" value="{{ $row['qty'] }}" min="1" max="{{ $item->stock_qty }}" aria-label="Jumlah {{ $item->name }}" readonly required>
                                                </form>
                                                <button class="atk-stepper-btn atk-stepper-plus" type="button" aria-label="Tambah jumlah {{ $item->name }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                                                </button>
                                            </div>
                                            <span class="atk-stepper-unit">{{ $item->unit_name }}</span>
                                        </div>
                                    </td>
                                    <td class="atk-cart-equivalent-cell">
                                        <span class="atk-cart-mobile-label">Setara</span>
                                        <strong class="atk-cart-equivalent-value">{{ number_format($row['qty'] * $item->unit_size, 0, ',', '.') }} {{ $item->content_unit_name }}</strong>
                                    </td>
                                    <td class="atk-cart-action-cell">
                                        <form method="POST" action="{{ route('v2.atk.cart.remove', $item) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="atk-btn atk-cart-remove-btn" type="submit" title="Hapus {{ $item->name }}">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Ringkasan & Submit --}}
            <aside class="atk-card atk-cart-summary">
                <h2 class="atk-cart-summary-title">Ringkasan Pengajuan</h2>
                <div class="atk-cart-summary-stats">
                    <div class="atk-cart-stat-row">
                        <span>Jenis Barang</span>
                        <strong>{{ $totalItems }} item</strong>
                    </div>
                    <div class="atk-cart-stat-row">
                        <span>Total Kuantitas</span>
                        <strong id="atkCartTotalQty">{{ $totalQty }}</strong>
                    </div>
                </div>

                <form method="POST" action="{{ route('v2.atk.cart.submit') }}" class="atk-cart-submit-form">
                    @csrf
                    <label class="atk-label" for="notes">Catatan <span class="atk-optional">(opsional)</span></label>
                    <textarea class="atk-textarea atk-cart-notes" id="notes" name="notes" rows="3" maxlength="1000" placeholder="Tulis keterangan jika diperlukan..."></textarea>
                    <button class="atk-btn atk-btn-primary atk-cart-submit" type="submit">Ajukan Permintaan</button>
                </form>
            </aside>
        </div>
    @endif

    <style>
        /* ========== TOOLBAR ========== */
        .atk-cart-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 14px;
        }
        .atk-cart-heading {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .atk-cart-count-pill {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--atk-primary-dark);
            background: var(--atk-primary-soft);
            padding: 2px 9px;
            border-radius: 999px;
        }
        .atk-cart-back-btn {
            height: 36px;
            min-height: 36px;
            padding: 0 12px;
            font-size: 12.5px;
        }

        /* ========== EMPTY STATE ========== */
        .atk-cart-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 40px 20px;
        }
        .atk-cart-empty-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--atk-primary-soft);
            color: var(--atk-primary);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .atk-cart-empty-title {
            margin: 0 0 4px;
            font-size: 14px;
            font-weight: 600;
            color: var(--atk-text-secondary);
        }

        /* ========== LAYOUT ========== */
        .atk-cart-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
            align-items: start;
        }
        .atk-cart-panel {
            padding: 0;
            overflow: hidden;
        }
        .atk-cart-table-wrap {
            border: 0;
            border-radius: 0;
        }
        .atk-cart-table {
            min-width: 0;
        }
        .atk-cart-table th {
            padding: 10px 16px;
        }
        .atk-cart-table td {
            padding: 12px 16px;
            vertical-align: middle;
        }
        .atk-cart-item-name {
            display: block;
            font-size: 13.5px;
            font-weight: 700;
            color: var(--atk-text);
            line-height: 1.35;
        }
        .atk-cart-item-meta {
            display: block;
            margin-top: 2px;
            font-size: 11.5px;
            color: var(--atk-muted);
        }

        .atk-cart-mobile-label {
            display: block;
            margin-bottom: 4px;
            color: var(--atk-muted);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        /* ========== UNIFIED STEPPER ========== */
        .atk-cart-qty-wrap {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .atk-stepper {
            display: inline-grid;
            grid-template-columns: 32px 46px 32px;
            height: 34px;
            border: 1px solid var(--atk-border);
            border-radius: 8px;
            background: var(--atk-surface);
            overflow: hidden;
        }
        .atk-stepper-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 100%;
            padding: 0;
            border: 0;
            background: transparent;
            color: var(--atk-primary-dark);
            cursor: pointer;
            transition: background .12s ease, opacity .12s ease;
        }
        .atk-stepper-minus { border-right: 1px solid var(--atk-border-soft); }
        .atk-stepper-plus { border-left: 1px solid var(--atk-border-soft); }
        .atk-stepper-btn svg { width: 14px; height: 14px; }
        .atk-stepper-btn:hover:not(:disabled) { background: var(--atk-primary-softer); }
        .atk-stepper-btn:active:not(:disabled) { background: var(--atk-primary-soft); }
        .atk-stepper-btn:disabled { opacity: .38; cursor: not-allowed; }
        .atk-cart-qty-form {
            display: flex;
            margin: 0;
            height: 100%;
        }
        .atk-stepper-input {
            width: 100%;
            height: 100%;
            text-align: center;
            border: 0;
            padding: 0;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            color: var(--atk-text);
            background: transparent;
            -moz-appearance: textfield;
        }
        .atk-stepper-input::-webkit-outer-spin-button,
        .atk-stepper-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .atk-stepper-input:focus { outline: none; }
        .atk-stepper-unit {
            font-size: 12px;
            font-weight: 600;
            color: var(--atk-muted);
        }

        .atk-cart-equivalent-value {
            font-size: 13px;
            font-weight: 700;
            color: var(--atk-text-secondary);
            white-space: nowrap;
        }

        .atk-cart-action-cell {
            text-align: right;
        }
        .atk-cart-remove-btn {
            height: 30px;
            min-height: 30px;
            padding: 0 10px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            background: var(--atk-surface);
            color: #B91C1C;
            border: 1px solid #FECACA;
        }
        .atk-cart-remove-btn:hover {
            background: #FEF2F2;
        }

        /* ========== SUMMARY CARD ========== */
        .atk-cart-summary {
            padding: 16px;
        }
        .atk-cart-summary-title {
            margin: 0 0 12px;
            font-size: 14px;
            font-weight: 700;
            color: var(--atk-text);
        }
        .atk-cart-summary-stats {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding-bottom: 12px;
            margin-bottom: 12px;
            border-bottom: 1px solid var(--atk-border-soft);
        }
        .atk-cart-stat-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            color: var(--atk-muted);
        }
        .atk-cart-stat-row strong {
            color: var(--atk-text);
            font-weight: 700;
        }
        .atk-optional {
            font-weight: 400;
            color: var(--atk-muted);
        }
        .atk-cart-notes {
            min-height: 76px;
            margin-bottom: 12px;
        }
        .atk-cart-submit {
            width: 100%;
        }

        /* Toast feedback */
        .atk-cart-toast {
            position: fixed;
            left: 50%;
            bottom: 24px;
            transform: translate(-50%, 16px);
            max-width: calc(100vw - 32px);
            padding: 11px 16px;
            border-radius: 10px;
            background: #D97706;
            border: 1px solid #B45309;
            color: #FFFFFF;
            font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(217, 119, 6, 0.35);
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
                padding: 0;
                border: 0;
                background: transparent;
                box-shadow: none;
            }
            .atk-cart-table-wrap {
                overflow: visible;
                background: transparent;
            }
            .atk-cart-table {
                display: block;
            }
            .atk-cart-table thead {
                display: none;
            }
            .atk-cart-table tbody {
                display: grid;
                gap: 10px;
            }
            .atk-cart-table .atk-cart-row {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                gap: 10px;
                padding: 12px;
                border: 1px solid var(--atk-border);
                border-radius: 12px;
                background: var(--atk-surface);
            }
            .atk-cart-table .atk-cart-row td {
                padding: 0;
                border: 0;
            }
            .atk-cart-item-cell {
                grid-column: 1;
            }
            .atk-cart-action-cell {
                grid-column: 2;
                align-self: start;
            }
            .atk-cart-qty-cell {
                grid-column: 1;
                align-self: end;
            }
            .atk-cart-equivalent-cell {
                grid-column: 2;
                text-align: right;
                align-self: end;
            }
        }

        @media (min-width: 640px) {
            .atk-cart-mobile-label {
                display: none;
            }
        }

        @media (min-width: 960px) {
            .atk-cart-layout {
                grid-template-columns: minmax(0, 1fr) 320px;
                gap: 16px;
            }
            .atk-cart-summary {
                position: sticky;
                top: 22px;
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

            // Update badge total qty di topbar dan ringkasan
            function updateCartBadge(count) {
                var badges = document.querySelectorAll('.atk-cart-badge');
                var label = document.querySelector('.atk-cart-shortcut');
                var totalQtyEl = document.getElementById('atkCartTotalQty');
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
                if (totalQtyEl && typeof count === 'number') {
                    totalQtyEl.textContent = formatNumber(count);
                }
            }

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
