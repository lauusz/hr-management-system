<x-inv-pesona-app title="Barang Masuk — Inventory Pesona">
    <style>
        .pesona-stockin-page {
            display: flex;
            flex-direction: column;
            gap: 16px;
            max-width: 780px;
        }
        .pesona-stockin-head {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .pesona-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            height: 34px;
            padding: 0 11px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 9px;
            background: #FFFFFF;
            color: #475569;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color .14s ease, color .14s ease, border-color .14s ease;
        }
        .pesona-back-btn:hover {
            background: #F8FAFC;
            color: #0F172A;
            border-color: #CBD5E1;
        }

        .pesona-stockin-card {
            padding: 18px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .pesona-stockin-item-banner {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px;
            margin-bottom: 18px;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            background: #F8FAFC;
        }
        .pesona-stockin-thumb {
            width: 64px;
            height: 64px;
            flex: 0 0 64px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid #E2E8F0;
            background: #FFFFFF;
        }
        .pesona-stockin-thumb-placeholder {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #94A3B8;
        }
        .pesona-stockin-meta {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .pesona-stockin-name {
            font-size: 15px;
            font-weight: 800;
            color: #0F172A;
            overflow-wrap: anywhere;
        }
        .pesona-stockin-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 2px;
        }
        .pesona-meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 999px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            color: #334155;
            font-size: 11.5px;
            font-weight: 600;
        }
        .pesona-inactive-note {
            margin: 4px 0 0;
            font-size: 11.5px;
            color: #92400E;
            font-weight: 600;
        }

        .pesona-stockin-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }
        .pesona-stockin-full {
            grid-column: 1 / -1;
        }
        .pesona-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
        }
        .pesona-label {
            font-size: 12.5px;
            font-weight: 700;
            color: #334155;
        }
        .pesona-input,
        .pesona-textarea {
            width: 100%;
            min-width: 0;
            min-height: 40px;
            padding: 8px 12px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 10px;
            background: #FFFFFF;
            color: #0F172A;
            font: inherit;
            font-size: 13.5px;
            box-sizing: border-box;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .pesona-input:focus,
        .pesona-textarea:focus {
            outline: none;
            border-color: var(--pesona-primary, #28355A);
            box-shadow: 0 0 0 3px rgba(40, 53, 90, 0.10);
        }
        .pesona-textarea {
            min-height: 86px;
            resize: vertical;
        }
        .pesona-hint {
            margin: 0;
            font-size: 11.5px;
            color: #64748B;
            line-height: 1.4;
        }
        .pesona-error {
            font-size: 12px;
            font-weight: 600;
            color: #B91C1C;
        }

        .pesona-total-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border: 1px solid rgba(40, 53, 90, 0.16);
            border-radius: 10px;
            background: var(--pesona-primary-soft, #EEF2F8);
        }
        .pesona-total-label {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--pesona-primary, #28355A);
        }
        .pesona-total-val {
            font-size: 15px;
            font-weight: 800;
            color: var(--pesona-primary, #28355A);
        }

        .pesona-form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid #F1F5F9;
        }
        .pesona-btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 40px;
            padding: 0 18px;
            border: 0;
            border-radius: 10px;
            background: var(--pesona-primary, #28355A);
            color: #FFFFFF;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color .15s ease;
        }
        .pesona-btn-submit:hover {
            background: var(--pesona-primary-hover, #1E2846);
        }
        .pesona-btn-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 16px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 10px;
            background: #FFFFFF;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            box-sizing: border-box;
        }
        .pesona-btn-cancel:hover {
            background: #F8FAFC;
            color: #0F172A;
        }

        @media (min-width: 640px) {
            .pesona-stockin-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>

    <div class="pesona-stockin-page">
        <div class="pesona-stockin-head">
            <a class="pesona-back-btn" href="{{ route('inventory.pesona.admin.items.index') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                <span>Kembali</span>
            </a>
            <h1 class="pesona-title">Barang Masuk Inventory Pesona</h1>
        </div>

        <div class="pesona-stockin-card">
            <div class="pesona-stockin-item-banner">
                @if($item->image_path)
                    <img class="pesona-stockin-thumb" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}" alt="Foto {{ $item->name }}">
                @else
                    <span class="pesona-stockin-thumb pesona-stockin-thumb-placeholder" aria-label="Tanpa foto">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                            <path d="m3.3 7 8.7 5 8.7-5M12 22V12"/>
                        </svg>
                    </span>
                @endif
                <div class="pesona-stockin-meta">
                    <strong class="pesona-stockin-name">{{ $item->name }}</strong>
                    <div class="pesona-stockin-badges">
                        <span class="pesona-meta-chip">Stok saat ini: <strong>{{ $item->stock_qty }} {{ $item->unit_name }}</strong></span>
                        <span class="pesona-meta-chip">Satuan: <strong>{{ $item->unit_name }}@if($item->unit_size > 1) (1 {{ $item->unit_name }} = {{ $item->unit_size }} {{ $item->content_unit_name }})@endif</strong></span>
                        <span class="pesona-meta-chip">Status: <strong>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</strong></span>
                    </div>
                    @if(! $item->is_active)
                        <p class="pesona-inactive-note">Barang nonaktif. Penerimaan stok tidak mengaktifkan barang ini.</p>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('inventory.pesona.admin.stock-in.store', $item) }}">
                @csrf
                <input type="hidden" name="unit_snapshot" value="{{ session()->hasOldInput() ? (is_string(old('unit_snapshot')) ? old('unit_snapshot') : '') : $unitSignature }}">
                @error('unit_snapshot') <p class="pesona-error" role="alert" style="margin:0 0 12px">{{ $message }}</p> @enderror

                <div class="pesona-stockin-grid">
                    <label class="pesona-field">
                        <span class="pesona-label">Jumlah Masuk ({{ $item->unit_name }})</span>
                        <input
                            class="pesona-input"
                            id="stock-in-qty"
                            name="qty"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]+"
                            maxlength="10"
                            value="{{ old('qty') }}"
                            placeholder="Contoh: 5"
                            required
                            @error('qty') aria-invalid="true" aria-describedby="stock-error-qty" @enderror
                        >
                        @error('qty') <span class="pesona-error" id="stock-error-qty">{{ $message }}</span> @enderror
                    </label>

                    <label class="pesona-field">
                        <span class="pesona-label">Harga Beli per {{ $item->unit_name }} (Rp)</span>
                        <input
                            class="pesona-input"
                            id="stock-in-price"
                            name="unit_price"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]+"
                            maxlength="{{ strlen((string) PHP_INT_MAX) }}"
                            value="{{ old('unit_price') }}"
                            placeholder="Opsional, contoh: 25000"
                            @error('unit_price') aria-invalid="true" aria-describedby="stock-error-price" @enderror
                        >
                        @error('unit_price') <span class="pesona-error" id="stock-error-price">{{ $message }}</span> @enderror
                        <span class="pesona-hint">Harga berlaku untuk satu satuan ambil. Kosongkan jika harga belum dicatat.</span>
                    </label>

                    <div class="pesona-field pesona-stockin-full">
                        <div class="pesona-total-box">
                            <span class="pesona-total-label">Total Pembelian</span>
                            <output class="pesona-total-val" id="stock-in-total" aria-live="polite">—</output>
                        </div>
                    </div>

                    <label class="pesona-field pesona-stockin-full">
                        <span class="pesona-label">Catatan <span style="font-weight:500;color:#94A3B8">(Opsional)</span></span>
                        <textarea
                            class="pesona-textarea"
                            name="notes"
                            maxlength="1000"
                            placeholder="Contoh: Pembelian nota toko sparepart..."
                            @error('notes') aria-invalid="true" aria-describedby="stock-error-notes" @enderror
                        >{{ old('notes') }}</textarea>
                        @error('notes') <span class="pesona-error" id="stock-error-notes">{{ $message }}</span> @enderror
                    </label>
                </div>

                <div class="pesona-form-actions">
                    <button class="pesona-btn-submit" type="submit">Simpan Barang Masuk</button>
                    <a class="pesona-btn-cancel" href="{{ route('inventory.pesona.admin.items.index') }}">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const qty = document.getElementById('stock-in-qty');
            const price = document.getElementById('stock-in-price');
            const total = document.getElementById('stock-in-total');
            function updateTotal() {
                total.textContent = '—';
                if (!/^(0|[1-9]\d*)$/.test(qty.value) || !/^(0|[1-9]\d*)$/.test(price.value)) return;
                if (typeof BigInt !== 'function') {
                    total.textContent = 'Total dihitung saat disimpan';
                    return;
                }
                const quantity = BigInt(qty.value), unitPrice = BigInt(price.value);
                if (quantity < BigInt(1) || quantity > BigInt('{{ min(PHP_INT_MAX, 4294967295) }}') || unitPrice > BigInt('{{ PHP_INT_MAX }}')) return;
                const result = quantity * unitPrice;
                if (result > BigInt('{{ PHP_INT_MAX }}')) return;
                total.textContent = 'Rp' + result.toLocaleString('id-ID');
            }
            qty.addEventListener('input', updateTotal);
            price.addEventListener('input', updateTotal);
            updateTotal();
        })();
    </script>
</x-inv-pesona-app>
