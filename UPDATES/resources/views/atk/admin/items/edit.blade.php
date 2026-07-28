<x-atk-app title="Edit Barang ATK">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Edit Barang</h1>
            <p class="atk-subtitle">Ubah informasi barang dan koreksi stok aktual bila terjadi salah input.</p>
        </div>
    </div>
    <form class="atk-card atk-stock-correction" method="POST" action="{{ route('v2.atk.admin.items.stock.store', $item) }}">
        @csrf
        <input type="hidden" name="movement_type" value="ADJUSTMENT">
        <div class="atk-stock-correction-header">
            <div>
                <h2 class="atk-section-title">Koreksi Stok Aktual</h2>
                <p class="atk-form-helper">Gunakan hanya ketika jumlah sistem berbeda dari stok fisik. Perubahan akan tercatat di riwayat stok.</p>
            </div>
            <div class="atk-stock-current">
                <span>Stok Sistem</span>
                <strong>{{ $item->stock_qty }} {{ $item->unit_name }}</strong>
            </div>
        </div>
        <div class="atk-stock-correction-grid">
            <div>
                <label class="atk-label" for="actual_stock">Stok Aktual</label>
                <input class="atk-input" id="actual_stock" type="number" min="0" inputmode="numeric" name="qty" value="{{ old('movement_type') === 'ADJUSTMENT' ? old('qty', $item->stock_qty) : $item->stock_qty }}" required>
                <p class="atk-form-helper">Masukkan jumlah akhir yang benar, bukan jumlah yang ingin dikurangi.</p>
            </div>
            <div>
                <label class="atk-label" for="stock_notes">Alasan Koreksi</label>
                <input class="atk-input" id="stock_notes" type="text" name="notes" value="{{ old('movement_type') === 'ADJUSTMENT' ? old('notes') : '' }}" maxlength="1000" placeholder="Opsional, contoh: Koreksi salah input stok awal">
            </div>
        </div>
        <div class="atk-actions atk-stock-correction-actions">
            <button class="atk-btn atk-btn-secondary" type="submit">Simpan Koreksi Stok</button>
        </div>
    </form>

    <form class="atk-card" method="POST" action="{{ route('v2.atk.admin.items.update', $item) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="atk-form-grid">
            <div>
                <label class="atk-label">Nama Barang</label>
                <input class="atk-input" name="name" value="{{ old('name', $item->name) }}" required>
            </div>
            <div>
                <label class="atk-label">Kategori</label>
                <select class="atk-select" name="atk_category_id">
                    <option value="">Tanpa kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('atk_category_id', $item->atk_category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="atk-label">Satuan Ambil</label>
                @php($currentUnit = old('unit_name', $item->unit_name))
                <select class="atk-select" id="unit_name" name="unit_name" required>
                    @foreach(App\Models\AtkItem::UNIT_OPTIONS as $option)
                        <option value="{{ $option }}" @selected($option === $currentUnit)>{{ $option }}</option>
                    @endforeach
                    @if(! in_array($currentUnit, App\Models\AtkItem::UNIT_OPTIONS))
                        <option value="{{ $currentUnit }}" selected>{{ $currentUnit }} (di luar daftar)</option>
                    @endif
                </select>
                <p class="atk-form-helper">Satuan yang dipakai user saat meminta barang.</p>
            </div>
            <div>
                <label class="atk-label">Isi per Satuan</label>
                <input class="atk-input" id="unit_size" type="number" min="1" name="unit_size" value="{{ old('unit_size', $item->unit_size) }}" required>
                <p class="atk-form-helper">Jumlah satuan isi dalam satu satuan ambil. Contoh: 1 box berisi <strong>20</strong> pcs.</p>
            </div>
            <div>
                <label class="atk-label">Satuan Isi</label>
                @php($currentContentUnit = old('content_unit_name', $item->content_unit_name))
                <select class="atk-select" id="content_unit_name" name="content_unit_name" required>
                    @foreach(App\Models\AtkItem::UNIT_OPTIONS as $option)
                        <option value="{{ $option }}" @selected($option === $currentContentUnit)>{{ $option }}</option>
                    @endforeach
                    @if(! in_array($currentContentUnit, App\Models\AtkItem::UNIT_OPTIONS))
                        <option value="{{ $currentContentUnit }}" selected>{{ $currentContentUnit }} (di luar daftar)</option>
                    @endif
                </select>
                <p class="atk-form-helper">Satuan terkecil yang menyusun isi. Biasanya <strong>pcs</strong> atau <strong>lembar</strong>.</p>
            </div>
        </div>
        <div class="atk-unit-preview atk-card atk-card-soft" id="unitPreview" role="status" aria-live="polite">
            <span class="atk-unit-preview-label">Hasil konversi satuan</span>
            <strong class="atk-unit-preview-value" id="unitPreviewValue">{{ $item->unit_conversion_label }}</strong>
            <span class="atk-unit-preview-note">Stok &amp; permintaan akan ditampilkan dalam satuan ambil, dengan nilai setara satuan isi.</span>
        </div>
        <div class="atk-form-grid" style="margin-top:14px">
            <div>
                <label class="atk-label">Minimum Stok</label>
                <input class="atk-input" type="number" min="0" name="minimum_stock" value="{{ old('minimum_stock', $item->minimum_stock) }}">
            </div>
            <div>
                <label class="atk-label">Minimal Pengambilan</label>
                <input class="atk-input" type="number" min="1" name="min_request_qty" value="{{ old('min_request_qty', $item->min_request_qty) }}">
            </div>
            <div>
                <label class="atk-label">Gambar Barang</label>
                <input class="atk-input" type="file" name="image" accept="image/*,.heic,.heif">
            </div>
        </div>
        <div style="margin-top:14px">
            <label class="atk-label">Deskripsi</label>
            <textarea class="atk-textarea" name="description">{{ old('description', $item->description) }}</textarea>
        </div>
        <label class="atk-actions" style="margin-top:14px">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active))>
            <span>Barang aktif di katalog</span>
        </label>
        <div class="atk-actions" style="justify-content:flex-end;margin-top:14px">
            <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.items.index') }}">Batal</a>
            <button class="atk-btn atk-btn-primary" type="submit">Simpan Perubahan</button>
        </div>
    </form>
    <style>
        .atk-form-helper {
            margin: 6px 0 0;
            color: var(--atk-muted);
            font-size: 11px;
            line-height: 1.45;
        }
        .atk-card-soft {
            margin-top: 14px;
            padding: 12px 14px;
            background: var(--atk-primary-softer);
            border: 1px solid #EEE8FF;
            border-radius: 14px;
        }
        .atk-unit-preview {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 4px 10px;
        }
        .atk-unit-preview-label {
            width: 100%;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--atk-primary-dark);
        }
        .atk-unit-preview-value {
            font-size: 14px;
            font-weight: 800;
            color: var(--atk-text);
        }
        .atk-unit-preview-note {
            font-size: 11px;
            color: var(--atk-muted);
        }
        .atk-stock-correction {
            margin-bottom: 14px;
            border-left: 4px solid var(--warning);
        }
        .atk-stock-correction-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 14px;
        }
        .atk-stock-correction-header .atk-section-title {
            margin: 0;
        }
        .atk-stock-current {
            flex: 0 0 auto;
            padding: 9px 12px;
            border-radius: 12px;
            background: var(--atk-primary-softer);
            text-align: right;
        }
        .atk-stock-current span,
        .atk-stock-current strong {
            display: block;
        }
        .atk-stock-current span {
            color: var(--atk-muted);
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .atk-stock-current strong {
            margin-top: 3px;
            font-size: 14px;
        }
        .atk-stock-correction-grid {
            display: grid;
            gap: 12px;
        }
        .atk-stock-correction-actions {
            justify-content: flex-end;
            margin-top: 14px;
        }
        @media (max-width: 639px) {
            .atk-stock-correction-header {
                flex-direction: column;
            }
            .atk-stock-current,
            .atk-stock-correction-actions,
            .atk-stock-correction-actions .atk-btn {
                width: 100%;
            }
            .atk-stock-current {
                text-align: left;
            }
        }
        @media (min-width: 768px) {
            .atk-stock-correction-grid {
                grid-template-columns: minmax(160px, .45fr) minmax(0, 1fr);
            }
        }
    </style>
    <script>
        (function () {
            var nameEl = document.getElementById('unit_name');
            var sizeEl = document.getElementById('unit_size');
            var contentEl = document.getElementById('content_unit_name');
            var outEl = document.getElementById('unitPreviewValue');
            if (!nameEl || !sizeEl || !contentEl || !outEl) return;

            function escapeText(value) {
                return String(value || '').replace(/[<>&]/g, function (ch) {
                    return { '<': '&lt;', '>': '&gt;', '&': '&amp;' }[ch];
                }).trim();
            }

            function update() {
                var unit = escapeText(nameEl.value) || 'pcs';
                var size = parseInt(sizeEl.value, 10);
                var content = escapeText(contentEl.value) || 'pcs';
                if (!size || size < 1) size = 1;
                outEl.textContent = '1 ' + unit + ' = ' + size + ' ' + content;
            }

            [nameEl, sizeEl, contentEl].forEach(function (el) {
                el.addEventListener('input', update);
            });
        })();
    </script>
</x-atk-app>
