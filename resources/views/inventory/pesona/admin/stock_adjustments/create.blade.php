<x-inv-pesona-app title="Koreksi Stok — Inventory Pesona">
    <style>
        .pesona-adjust-page {
            display: flex;
            flex-direction: column;
            gap: 16px;
            max-width: 780px;
        }
        .pesona-adjust-head {
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

        .pesona-adjust-card {
            padding: 18px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-left: 4px solid #F59E0B;
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .pesona-adjust-banner {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 14px;
            margin-bottom: 16px;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            background: #F8FAFC;
        }
        .pesona-adjust-name {
            font-size: 15px;
            font-weight: 800;
            color: #0F172A;
            overflow-wrap: anywhere;
        }
        .pesona-adjust-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
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
        .pesona-adjust-note {
            margin: 0;
            font-size: 12px;
            color: #64748B;
            line-height: 1.45;
        }

        .pesona-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-top: 14px;
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
            min-height: 90px;
            resize: vertical;
        }
        .pesona-error {
            font-size: 12px;
            font-weight: 600;
            color: #B91C1C;
        }

        .adjust-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 18px;
        }
        .adjust-actions a {
            text-decoration: none;
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
    </style>

    <div class="pesona-adjust-page">
        <div class="pesona-adjust-head">
            <a class="pesona-back-btn" href="{{ route('inventory.pesona.admin.items.index') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                <span>Kembali</span>
            </a>
            <h1 class="pesona-title">Koreksi Stok Inventory Pesona</h1>
        </div>

        <div class="pesona-adjust-card">
            <div class="pesona-adjust-banner">
                <strong class="pesona-adjust-name">{{ $item->name }}</strong>
                <div class="pesona-adjust-chips">
                    <span class="pesona-meta-chip">Stok saat form dibuka: <strong>{{ old('expected_stock', $item->stock_qty) }} {{ $item->unit_name }}</strong></span>
                    <span class="pesona-meta-chip">Satuan: <strong>{{ $item->unit_name }}@if($item->unit_size > 1) · 1 {{ $item->unit_name }} = {{ $item->unit_size }} {{ $item->content_unit_name }}@endif</strong></span>
                    <span class="pesona-meta-chip">Status: <strong>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</strong></span>
                </div>
                <p class="pesona-adjust-note">Isi jumlah fisik yang tersedia di garasi. Sistem akan menetapkan stok menjadi jumlah tersebut dan mencatat selisihnya di Riwayat Stok.</p>
            </div>

            <form id="pesona-adjust-form" method="POST" action="{{ route('inventory.pesona.admin.stock-adjustment.store', $item) }}">
                @csrf
                <input type="hidden" name="unit_snapshot" value="{{ session()->hasOldInput() ? (is_string(old('unit_snapshot')) ? old('unit_snapshot') : '') : $unitSignature }}">
                @error('unit_snapshot') <p class="pesona-error" role="alert">{{ $message }}</p> @enderror
                <input type="hidden" name="expected_stock" value="{{ old('expected_stock', $item->stock_qty) }}">
                @error('expected_stock') <p class="pesona-error" role="alert">{{ $message }}</p> @enderror

                <label class="pesona-field">
                    <span class="pesona-label">Stok Aktual ({{ $item->unit_name }})</span>
                    <input
                        class="pesona-input"
                        type="number"
                        name="actual_stock"
                        min="0"
                        max="{{ min(PHP_INT_MAX, 4294967295) }}"
                        step="1"
                        value="{{ old('actual_stock') }}"
                        placeholder="Masukkan jumlah fisik aktual..."
                        required
                        @error('actual_stock') aria-invalid="true" aria-describedby="adjust-error-stock" @enderror
                    >
                    @error('actual_stock') <span class="pesona-error" id="adjust-error-stock">{{ $message }}</span> @enderror
                </label>

                <label class="pesona-field">
                    <span class="pesona-label">Alasan Koreksi</span>
                    <textarea
                        class="pesona-textarea"
                        name="notes"
                        maxlength="1000"
                        placeholder="Contoh: Penyesuaian hasil stock opname fisik..."
                        required
                        @error('notes') aria-invalid="true" aria-describedby="adjust-error-notes" @enderror
                    >{{ old('notes') }}</textarea>
                    @error('notes') <span class="pesona-error" id="adjust-error-notes">{{ $message }}</span> @enderror
                </label>

                <div class="adjust-actions">
                    <button class="pesona-btn-submit" id="pesona-adjust-submit" type="submit" aria-haspopup="dialog" aria-controls="confirm-pesona-adjustment">Simpan Koreksi</button>
                    <a class="pesona-btn-cancel" href="{{ route('inventory.pesona.admin.items.index') }}">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <x-modal id="confirm-pesona-adjustment" title="Simpan Koreksi Stok?" type="form" variant="warning">
        <p>Stok akan disesuaikan dengan jumlah aktual yang dimasukkan.</p>
        <div class="adjust-actions">
            <button class="pesona-button" type="button" data-modal-close>Batal</button>
            <button class="pesona-button pesona-button-primary" type="button" id="pesona-adjust-confirm">Ya, Simpan</button>
        </div>
    </x-modal>

    <script>
        (() => {
            const form = document.getElementById('pesona-adjust-form');
            const trigger = document.getElementById('pesona-adjust-submit');
            const modal = document.getElementById('confirm-pesona-adjustment');
            const confirm = document.getElementById('pesona-adjust-confirm');
            let open = false, confirmed = false;
            function close() {
                if (!open) return;
                open = false;
                modal.style.display = 'none';
                document.body.style.overflow = '';
                trigger.focus();
            }
            form.addEventListener('submit', (event) => {
                if (confirmed) return;
                event.preventDefault();
                open = true;
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                modal.querySelector('[data-modal-close]').focus();
            });
            confirm.addEventListener('click', () => { confirmed = true; form.requestSubmit(); });
            modal.addEventListener('click', (event) => {
                if (event.target === modal || event.target.closest('[data-modal-close]')) close();
            });
            document.addEventListener('keydown', (event) => {
                if (!open) return;
                if (event.key === 'Escape') { close(); return; }
                if (event.key !== 'Tab') return;
                const controls = Array.from(modal.querySelectorAll('button:not([disabled])')).filter((element) => element.getClientRects().length);
                const first = controls[0], last = controls[controls.length - 1];
                if (event.shiftKey && (document.activeElement === first || !modal.contains(document.activeElement))) { event.preventDefault(); last.focus(); }
                else if (!event.shiftKey && (document.activeElement === last || !modal.contains(document.activeElement))) { event.preventDefault(); first.focus(); }
            });
        })();
    </script>
</x-inv-pesona-app>
