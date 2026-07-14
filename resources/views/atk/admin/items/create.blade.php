<x-atk-app title="Tambah Barang ATK">
    <div class="atk-header">
        <div>
            <h1 class="atk-title">Tambah Barang</h1>
            <p class="atk-subtitle">Input master barang dan stok awal.</p>
        </div>
    </div>
    <form class="atk-card" method="POST" action="{{ route('v2.atk.admin.items.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="atk-form-grid">
            <div>
                <label class="atk-label">Nama Barang</label>
                <input class="atk-input" name="name" required>
            </div>
            <div>
                <label class="atk-label">Kategori</label>
                <select class="atk-select" name="atk_category_id">
                    <option value="">Tanpa kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="atk-label">Satuan Ambil</label>
                <select class="atk-select" id="unit_name" name="unit_name" required>
                    @foreach(App\Models\AtkItem::UNIT_OPTIONS as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
                <p class="atk-form-helper">Satuan yang dipakai user saat meminta barang.</p>
            </div>
            <div>
                <label class="atk-label">Isi per Satuan</label>
                <input class="atk-input" id="unit_size" type="number" min="1" name="unit_size" value="1" required>
                <p class="atk-form-helper">Jumlah satuan isi dalam satu satuan ambil. Contoh: 1 box berisi <strong>20</strong> pcs.</p>
            </div>
            <div>
                <label class="atk-label">Satuan Isi</label>
                <select class="atk-select" id="content_unit_name" name="content_unit_name" required>
                    @foreach(App\Models\AtkItem::UNIT_OPTIONS as $option)
                        <option value="{{ $option }}" @selected($option === 'pcs')>{{ $option }}</option>
                    @endforeach
                </select>
                <p class="atk-form-helper">Satuan terkecil yang menyusun isi. Biasanya <strong>pcs</strong> atau <strong>lembar</strong>.</p>
            </div>
        </div>
        <div class="atk-unit-preview atk-card atk-card-soft" id="unitPreview" role="status" aria-live="polite">
            <span class="atk-unit-preview-label">Hasil konversi satuan</span>
            <strong class="atk-unit-preview-value" id="unitPreviewValue">1 pcs = 1 pcs</strong>
            <span class="atk-unit-preview-note">Stok &amp; permintaan akan ditampilkan dalam satuan ambil, dengan nilai setara satuan isi.</span>
        </div>
        <div class="atk-form-grid" style="margin-top:14px">
            <div>
                <label class="atk-label">Stok Awal</label>
                <input class="atk-input" type="number" min="0" name="stock_qty" value="0" required>
            </div>
            <div>
                <label class="atk-label">Minimum Stok</label>
                <input class="atk-input" type="number" min="0" name="minimum_stock" value="0">
            </div>
            <div>
                <label class="atk-label">Gambar Barang</label>
                <input class="atk-input" type="file" name="image" accept="image/*,.heic,.heif">
            </div>
        </div>
        <div style="margin-top:14px">
            <label class="atk-label">Deskripsi</label>
            <textarea class="atk-textarea" name="description"></textarea>
        </div>
        <div class="atk-actions" style="justify-content:flex-end;margin-top:14px">
            <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.items.index') }}">Batal</a>
            <button class="atk-btn atk-btn-primary" type="submit">Simpan Barang</button>
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
            update();
        })();
    </script>
</x-atk-app>
