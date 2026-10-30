<x-atk-app title="Ajukan Barang">
    <style>
        .atk-need-wrap {
            width: 100%;
        }
        .atk-need-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .atk-need-head-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .atk-need-card {
            width: 100%;
            background: #FFFFFF;
            border: 1px solid var(--atk-border);
            border-radius: 12px;
            box-shadow: var(--atk-shadow);
            overflow: hidden;
        }
        .atk-need-prefill-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            padding: 10px 18px;
            background: #F8FAFC;
            border-bottom: 1px solid var(--atk-border);
            font-size: 12px;
            color: #475569;
        }
        .atk-need-prefill-bar strong {
            color: var(--atk-text);
            font-weight: 700;
        }
        .atk-need-body {
            padding: 20px;
            display: grid;
            gap: 18px;
        }
        .atk-need-top-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) 170px minmax(0, 1.3fr);
            gap: 16px;
            align-items: start;
        }
        .atk-field-hint {
            margin: 5px 0 0;
            font-size: 11px;
            color: #94A3B8;
            line-height: 1.35;
        }

        /* Quantity Stepper */
        .atk-need-qty-stepper {
            display: grid;
            grid-template-columns: 40px minmax(0, 1fr) 40px;
            height: 38px;
            border: 1.5px solid var(--atk-border);
            border-radius: 10px;
            background: #FFFFFF;
            overflow: hidden;
        }
        .atk-need-qty-btn,
        .atk-need-qty-input {
            min-width: 0;
            height: 100%;
            border: 0;
            background: transparent;
            color: var(--atk-text);
            font: inherit;
            font-size: 13.5px;
            font-weight: 700;
            text-align: center;
            padding: 0;
        }
        .atk-need-qty-btn {
            color: #475569;
            cursor: pointer;
            transition: background .12s ease;
        }
        .atk-need-qty-btn:hover:not(:disabled) {
            background: #F1F5F9;
            color: var(--atk-text);
        }
        .atk-need-qty-btn:first-child {
            border-right: 1px solid var(--atk-border);
        }
        .atk-need-qty-btn:last-child {
            border-left: 1px solid var(--atk-border);
        }
        .atk-need-qty-btn:disabled {
            color: #CBD5E1;
            cursor: not-allowed;
        }
        .atk-need-qty-btn:focus-visible,
        .atk-need-qty-input:focus-visible {
            position: relative;
            z-index: 1;
            outline: 2px solid var(--atk-primary);
            outline-offset: -2px;
        }
        .atk-need-qty-input {
            width: 100%;
            appearance: textfield;
            -moz-appearance: textfield;
        }
        .atk-need-qty-input::-webkit-inner-spin-button,
        .atk-need-qty-input::-webkit-outer-spin-button {
            margin: 0;
            appearance: none;
        }

        /* Quick Chips */
        .atk-quick-chips {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-wrap: wrap;
            margin-top: 6px;
        }
        .atk-quick-chip {
            border: 1px solid var(--atk-border);
            background: #F8FAFC;
            color: #475569;
            border-radius: 6px;
            padding: 3px 9px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all .12s ease;
        }
        .atk-quick-chip:hover {
            border-color: #CBD5E1;
            background: #F1F5F9;
            color: var(--atk-text);
        }
        .atk-quick-chip.is-active {
            border-color: var(--atk-primary);
            background: #F5F2FF;
            color: var(--atk-primary-dark);
        }

        .atk-need-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            padding: 12px 20px;
            background: #F8FAFC;
            border-top: 1px solid var(--atk-border);
        }

        @media (max-width: 960px) {
            .atk-need-top-grid {
                grid-template-columns: 170px minmax(0, 1fr);
            }
            .atk-need-name-col {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 600px) {
            .atk-need-top-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }
            .atk-need-body {
                padding: 14px;
                gap: 14px;
            }
            .atk-need-footer {
                padding: 12px 14px;
            }
            .atk-need-footer .atk-btn {
                flex: 1;
            }
        }
    </style>

    <div class="atk-need-wrap">
        <div class="atk-need-head">
            <div class="atk-need-head-left">
                <h1 class="atk-title">Ajukan Barang</h1>
            </div>
            <a class="atk-btn atk-btn-secondary atk-btn-sm" href="{{ route('v2.atk.need-requests.index') }}">
                Riwayat Pengajuan Saya
            </a>
        </div>

        <form class="atk-need-card" method="POST" action="{{ route('v2.atk.need-requests.store') }}">
            @csrf
            <input type="hidden" name="atk_item_id" value="{{ $item?->id }}">

            @if($item)
                <div class="atk-need-prefill-bar">
                    <span>Pengajuan stok untuk katalog: <strong>{{ $item->name }}</strong> (Sisa stok: {{ $item->stock_qty }} {{ $item->unit_name }})</span>
                    <a href="{{ route('v2.atk.need-requests.create') }}" style="color:var(--atk-primary);font-weight:600;text-decoration:none">Reset</a>
                </div>
            @endif

            <div class="atk-need-body">
                <div class="atk-need-top-grid">
                    <div class="atk-need-name-col">
                        <label class="atk-label" for="requested_item_name">Nama Barang</label>
                        <input
                            class="atk-input"
                            id="requested_item_name"
                            name="requested_item_name"
                            value="{{ old('requested_item_name', $item?->name) }}"
                            placeholder="Contoh: Tinta Printer Hitam / Spidol Papan Tulis"
                            required
                            autofocus
                        >
                        <p class="atk-field-hint">Tulis nama dan spesifikasi singkat barang yang dibutuhkan.</p>
                    </div>

                    <div>
                        <label class="atk-label">Jumlah</label>
                        <div class="atk-need-qty-stepper" data-need-qty-stepper>
                            <button class="atk-need-qty-btn" type="button" data-need-qty-decrease aria-label="Kurangi jumlah" disabled>&minus;</button>
                            <input class="atk-need-qty-input" type="number" name="qty" min="1" step="1" value="{{ old('qty', 1) }}" inputmode="numeric" aria-label="Jumlah barang" required data-need-qty-input>
                            <button class="atk-need-qty-btn" type="button" data-need-qty-increase aria-label="Tambah jumlah">+</button>
                        </div>
                    </div>

                    <div>
                        <label class="atk-label" for="need_unit_name">Satuan</label>
                        @php($currentUnit = old('unit_name', $item?->unit_name ?? 'pcs'))
                        <input
                            class="atk-input"
                            id="need_unit_name"
                            name="unit_name"
                            list="atkNeedUnitOptions"
                            value="{{ $currentUnit }}"
                            placeholder="pcs, box, pack..."
                            required
                        >
                        <datalist id="atkNeedUnitOptions">
                            @foreach(App\Models\AtkItem::availableUnitOptions() as $option)
                                <option value="{{ $option }}"></option>
                            @endforeach
                        </datalist>
                        <div class="atk-quick-chips" id="needUnitChips">
                            @foreach(['pcs', 'box', 'pack', 'rim', 'botol', 'roll'] as $quickUnit)
                                <button
                                    type="button"
                                    class="atk-quick-chip {{ $currentUnit === $quickUnit ? 'is-active' : '' }}"
                                    data-unit-chip="{{ $quickUnit }}"
                                >
                                    {{ $quickUnit }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div>
                    <label class="atk-label" for="need_reason">Alasan Kebutuhan</label>
                    <textarea
                        class="atk-textarea"
                        id="need_reason"
                        name="reason"
                        rows="3"
                        placeholder="Jelaskan alasan pengajuan atau keperluan penggunaan..."
                        required
                        style="min-height:84px"
                    >{{ old('reason') }}</textarea>
                    <div class="atk-quick-chips" id="needReasonChips">
                        <button type="button" class="atk-quick-chip" data-reason-chip="Stok barang di katalog sedang habis.">Stok katalog habis</button>
                        <button type="button" class="atk-quick-chip" data-reason-chip="Barang belum tersedia di katalog ATK.">Belum ada di katalog</button>
                        <button type="button" class="atk-quick-chip" data-reason-chip="Kebutuhan operasional rutin divisi.">Kebutuhan operasional</button>
                    </div>
                </div>
            </div>

            <div class="atk-need-footer">
                <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.catalog') }}">Batal</a>
                <button class="atk-btn atk-btn-primary" type="submit">Kirim Pengajuan</button>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const stepper = document.querySelector('[data-need-qty-stepper]');
            if (stepper) {
                const input = stepper.querySelector('[data-need-qty-input]');
                const decrease = stepper.querySelector('[data-need-qty-decrease]');
                const increase = stepper.querySelector('[data-need-qty-increase]');

                function currentValue() {
                    const value = Number.parseInt(input.value, 10);
                    return Number.isInteger(value) && value >= 1 ? value : 1;
                }

                function sync() {
                    decrease.disabled = currentValue() <= 1;
                }

                decrease.addEventListener('click', function () {
                    input.value = Math.max(1, currentValue() - 1);
                    sync();
                });
                increase.addEventListener('click', function () {
                    input.value = currentValue() + 1;
                    sync();
                });
                input.addEventListener('input', sync);
                input.addEventListener('blur', function () {
                    input.value = currentValue();
                    sync();
                });
                input.addEventListener('keydown', function (event) {
                    if (['e', 'E', '+', '-', '.'].includes(event.key)) event.preventDefault();
                });
                sync();
            }

            const unitInput = document.getElementById('need_unit_name');
            const unitChips = document.querySelectorAll('[data-unit-chip]');
            if (unitInput && unitChips.length) {
                function syncUnitChips() {
                    const val = (unitInput.value || '').trim().toLowerCase();
                    unitChips.forEach(function (chip) {
                        chip.classList.toggle('is-active', chip.getAttribute('data-unit-chip') === val);
                    });
                }
                unitChips.forEach(function (chip) {
                    chip.addEventListener('click', function () {
                        unitInput.value = chip.getAttribute('data-unit-chip') || 'pcs';
                        syncUnitChips();
                    });
                });
                unitInput.addEventListener('input', syncUnitChips);
            }

            const reasonInput = document.getElementById('need_reason');
            const reasonChips = document.querySelectorAll('[data-reason-chip]');
            if (reasonInput && reasonChips.length) {
                reasonChips.forEach(function (chip) {
                    chip.addEventListener('click', function () {
                        const text = chip.getAttribute('data-reason-chip') || '';
                        const current = (reasonInput.value || '').trim();
                        reasonInput.value = current ? (current + ' ' + text) : text;
                        reasonInput.focus();
                    });
                });
            }
        })();
    </script>
</x-atk-app>
