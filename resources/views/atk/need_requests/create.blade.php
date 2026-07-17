<x-atk-app title="Ajukan Barang">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Ajukan Barang</h1>
            <p class="atk-subtitle">Gunakan untuk restock atau kebutuhan barang baru.</p>
        </div>
    </div>
    <form class="atk-card" method="POST" action="{{ route('v2.atk.need-requests.store') }}">
        @csrf
        <input type="hidden" name="atk_item_id" value="{{ $item?->id }}">
        <div class="atk-form-grid">
            <div>
                <label class="atk-label">Nama Barang</label>
                <input class="atk-input" name="requested_item_name" value="{{ old('requested_item_name', $item?->name) }}" required>
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
                <label class="atk-label">Satuan</label>
                <input class="atk-input" name="unit_name" value="{{ old('unit_name', $item?->unit_name ?? 'pcs') }}" required>
            </div>
        </div>
        <div style="margin-top:14px">
            <label class="atk-label">Alasan</label>
            <textarea class="atk-textarea" name="reason" required>{{ old('reason') }}</textarea>
        </div>
        <div class="atk-actions" style="justify-content:flex-end;margin-top:14px">
            <button class="atk-btn atk-btn-primary" type="submit">Kirim Pengajuan</button>
        </div>
    </form>

    <style>
        .atk-need-qty-stepper {
            display: grid;
            grid-template-columns: 48px minmax(0, 1fr) 48px;
            min-height: 46px;
            border: 1.5px solid var(--atk-border);
            border-radius: 14px;
            background: var(--atk-surface);
            overflow: hidden;
        }
        .atk-need-qty-btn,
        .atk-need-qty-input {
            min-width: 0;
            min-height: 44px;
            border: 0;
            background: transparent;
            color: var(--atk-text);
            font: inherit;
            font-size: 14px;
            font-weight: 800;
            text-align: center;
        }
        .atk-need-qty-btn {
            color: var(--atk-primary-dark);
            cursor: pointer;
        }
        .atk-need-qty-btn:first-child {
            border-right: 1px solid var(--atk-border-soft);
        }
        .atk-need-qty-btn:last-child {
            border-left: 1px solid var(--atk-border-soft);
        }
        .atk-need-qty-btn:disabled {
            color: var(--atk-muted);
            cursor: not-allowed;
            opacity: .45;
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
    </style>
    <script>
        (function () {
            const stepper = document.querySelector('[data-need-qty-stepper]');
            if (!stepper) return;

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
        })();
    </script>
</x-atk-app>
