<x-atk-app title="Tambah Barang ATK">
    <style>
        .atk-create-wrap {
            width: 100%;
        }
        .atk-create-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }
        .atk-create-head-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .atk-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            height: 32px;
            padding: 0 10px;
            border-radius: 7px;
            border: 1px solid var(--atk-line);
            background: #FFFFFF;
            color: var(--atk-muted);
            text-decoration: none;
            font-size: 12.5px;
            font-weight: 600;
            transition: all .14s ease;
        }
        .atk-back-btn:hover {
            color: var(--atk-text);
            border-color: #CBD5E1;
            background: #F8FAFC;
        }
        .atk-create-layout {
            display: grid;
            grid-template-columns: 1fr 290px;
            gap: 16px;
            align-items: start;
        }
        .atk-panel {
            background: #FFFFFF;
            border: 1px solid var(--atk-line);
            border-radius: 10px;
            padding: 18px;
        }
        .atk-panel + .atk-panel {
            margin-top: 14px;
        }
        .atk-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid #F1F5F9;
        }
        .atk-panel-title {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            color: var(--atk-text);
        }
        .atk-panel-sub {
            font-size: 11.5px;
            color: var(--atk-muted);
        }
        .atk-field + .atk-field {
            margin-top: 14px;
        }
        .atk-field-hint {
            margin: 4px 0 0;
            font-size: 11px;
            color: #94A3B8;
            line-height: 1.35;
        }

        /* Visual Unit Equation Box */
        .atk-equation-box {
            display: grid;
            grid-template-columns: 1fr auto 110px 1fr;
            align-items: end;
            gap: 10px;
            padding: 14px;
            border-radius: 8px;
            background: #F8FAFC;
            border: 1px solid var(--atk-line);
        }
        .atk-equation-op {
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: #64748B;
            padding: 0 2px;
        }
        .atk-unit-preview {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 6px;
            background: #F1F5F9;
            font-size: 11.5px;
            color: #475569;
        }
        .atk-unit-preview strong {
            color: var(--atk-text);
            font-weight: 700;
        }

        /* Input with dynamic unit suffix */
        .atk-stock-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }
        .atk-input-suffix-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .atk-input-suffix-wrap .atk-input {
            padding-right: 54px;
        }
        .atk-input-suffix {
            position: absolute;
            right: 8px;
            padding: 2px 6px;
            border-radius: 4px;
            background: #F1F5F9;
            color: #64748B;
            font-size: 11px;
            font-weight: 600;
            pointer-events: none;
        }

        /* Right Column: Dropzone & Actions */
        .atk-dropzone {
            position: relative;
            border: 1.5px dashed #CBD5E1;
            border-radius: 8px;
            background: #F8FAFC;
            padding: 18px 14px;
            text-align: center;
            cursor: pointer;
            transition: all .15s ease;
            min-height: 170px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            overflow: hidden;
        }
        .atk-dropzone:hover {
            border-color: #94A3B8;
            background: #F1F5F9;
        }
        .atk-dropzone input[type="file"] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .atk-dropzone-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: #FFFFFF;
            border: 1px solid var(--atk-line);
            display: grid;
            place-items: center;
            color: #64748B;
        }
        .atk-dropzone-title {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--atk-text);
        }
        .atk-dropzone-sub {
            font-size: 11px;
            color: #94A3B8;
        }
        .atk-dropzone-preview {
            display: none;
            width: 100%;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        .atk-dropzone-preview img {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--atk-line);
            background: #FFFFFF;
        }
        .atk-dropzone-filename {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--atk-text);
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .atk-side-actions {
            display: grid;
            gap: 8px;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid #F1F5F9;
        }
        .atk-side-actions .atk-btn {
            width: 100%;
            height: 36px;
        }

        @media (max-width: 820px) {
            .atk-create-layout {
                grid-template-columns: 1fr;
            }
            .atk-equation-box {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .atk-equation-op {
                display: none;
            }
            .atk-stock-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="atk-create-wrap">
        <div class="atk-create-head">
            <div class="atk-create-head-left">
                <a class="atk-back-btn" href="{{ route('v2.atk.admin.items.index') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Kembali
                </a>
                <h1 class="atk-title">Tambah Barang Baru</h1>
            </div>
        </div>

        <form method="POST" action="{{ route('v2.atk.admin.items.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="atk-create-layout">
                {{-- Kolom Kiri: Input Utama --}}
                <div>
                    <div class="atk-panel">
                        <div class="atk-panel-head">
                            <h2 class="atk-panel-title">Detail Barang</h2>
                        </div>
                        <div class="atk-field">
                            <label class="atk-label" for="item_name">Nama Barang</label>
                            <input class="atk-input" id="item_name" name="name" value="{{ old('name') }}" placeholder="Contoh: Kertas HVS A4 80gr / Pulpen Hitam" required autofocus>
                        </div>
                        <div class="atk-field">
                            <label class="atk-label" for="item_description">Deskripsi / Spesifikasi <span style="font-weight:400;color:#94A3B8">(Opsional)</span></label>
                            <textarea class="atk-textarea" id="item_description" name="description" rows="2" placeholder="Merek, ukuran, atau keterangan singkat..." style="min-height:64px">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <div class="atk-panel">
                        <div class="atk-panel-head">
                            <div>
                                <h2 class="atk-panel-title">Satuan &amp; Isi Kemasan</h2>
                            </div>
                            <div class="atk-unit-preview" id="unitPreview" role="status" aria-live="polite">
                                <span>Rumus:</span>
                                <strong id="unitPreviewValue">1 pcs = 1 pcs</strong>
                            </div>
                        </div>

                        <div class="atk-equation-box">
                            <div>
                                <label class="atk-label" for="unit_name">1 Satuan Ambil</label>
                                <select class="atk-select" id="unit_name" name="unit_name" required>
                                    @foreach(App\Models\AtkItem::UNIT_OPTIONS as $option)
                                        <option value="{{ $option }}" @selected(old('unit_name', 'pcs') === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="atk-equation-op">=</div>
                            <div>
                                <label class="atk-label" for="unit_size">Jumlah Isi</label>
                                <input class="atk-input" id="unit_size" type="number" min="1" name="unit_size" value="{{ old('unit_size', 1) }}" required>
                            </div>
                            <div>
                                <label class="atk-label" for="content_unit_name">Satuan Isi</label>
                                <select class="atk-select" id="content_unit_name" name="content_unit_name" required>
                                    @foreach(App\Models\AtkItem::UNIT_OPTIONS as $option)
                                        <option value="{{ $option }}" @selected(old('content_unit_name', 'pcs') === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <p class="atk-field-hint" style="margin-top:8px">Contoh: Jika barang diambil per <strong>box</strong> dan 1 box berisi <strong>20 pcs</strong>, pilih Satuan Ambil <strong>box</strong>, Jumlah Isi <strong>20</strong>, dan Satuan Isi <strong>pcs</strong>.</p>
                    </div>

                    <div class="atk-panel">
                        <div class="atk-panel-head">
                            <h2 class="atk-panel-title">Stok &amp; Batas Pengajuan</h2>
                            <span class="atk-panel-sub">Mengikuti satuan ambil</span>
                        </div>
                        <div class="atk-stock-grid">
                            <div>
                                <label class="atk-label" for="stock_qty">Stok Awal</label>
                                <div class="atk-input-suffix-wrap">
                                    <input class="atk-input" id="stock_qty" type="number" min="0" name="stock_qty" value="{{ old('stock_qty', 0) }}" required>
                                    <span class="atk-input-suffix js-unit-suffix">pcs</span>
                                </div>
                                <p class="atk-field-hint">Saldo awal di gudang.</p>
                            </div>
                            <div>
                                <label class="atk-label" for="minimum_stock">Batas Stok Tipis</label>
                                <div class="atk-input-suffix-wrap">
                                    <input class="atk-input" id="minimum_stock" type="number" min="0" name="minimum_stock" value="{{ old('minimum_stock', 0) }}">
                                    <span class="atk-input-suffix js-unit-suffix">pcs</span>
                                </div>
                                <p class="atk-field-hint">Indikator perlu restock.</p>
                            </div>
                            <div>
                                <label class="atk-label" for="min_request_qty">Min. Ambil per Request</label>
                                <div class="atk-input-suffix-wrap">
                                    <input class="atk-input" id="min_request_qty" type="number" min="1" name="min_request_qty" value="{{ old('min_request_qty', 1) }}">
                                    <span class="atk-input-suffix js-unit-suffix">pcs</span>
                                </div>
                                <p class="atk-field-hint">Kuantitas minimal order.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Foto & Simpan --}}
                <div class="atk-panel">
                    <div class="atk-panel-head">
                        <h2 class="atk-panel-title">Foto Barang</h2>
                        <span class="atk-panel-sub">Opsional</span>
                    </div>

                    <div class="atk-dropzone" id="dropzoneBox">
                        <input id="item_image" type="file" name="image" accept="image/*,.heic,.heif">
                        <div id="dropzoneEmpty" style="display:flex;flex-direction:column;align-items:center;gap:6px">
                            <div class="atk-dropzone-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                            </div>
                            <div class="atk-dropzone-title">Klik atau tarik foto ke sini</div>
                            <div class="atk-dropzone-sub">Format JPG, PNG, WEBP, HEIC</div>
                        </div>
                        <div class="atk-dropzone-preview" id="dropzonePreview">
                            <img id="dropzoneImg" src="" alt="Preview">
                            <div class="atk-dropzone-filename" id="dropzoneFilename"></div>
                            <span style="font-size:11px;color:var(--atk-primary);font-weight:600">Klik untuk ganti foto</span>
                        </div>
                    </div>

                    <div class="atk-side-actions">
                        <button class="atk-btn atk-btn-primary" type="submit">Simpan Barang</button>
                        <a class="atk-btn atk-btn-muted" href="{{ route('v2.atk.admin.items.index') }}">Batal</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        (function () {
            var nameEl = document.getElementById('unit_name');
            var sizeEl = document.getElementById('unit_size');
            var contentEl = document.getElementById('content_unit_name');
            var outEl = document.getElementById('unitPreviewValue');
            var suffixes = document.querySelectorAll('.js-unit-suffix');

            if (nameEl && sizeEl && contentEl && outEl) {
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
                    suffixes.forEach(function (el) {
                        el.textContent = unit;
                    });
                }

                [nameEl, sizeEl, contentEl].forEach(function (el) {
                    el.addEventListener('input', update);
                    el.addEventListener('change', update);
                });
                update();
            }

            var fileInput = document.getElementById('item_image');
            var emptyBox = document.getElementById('dropzoneEmpty');
            var previewBox = document.getElementById('dropzonePreview');
            var previewImg = document.getElementById('dropzoneImg');
            var filenameEl = document.getElementById('dropzoneFilename');

            if (fileInput && emptyBox && previewBox && previewImg && filenameEl) {
                fileInput.addEventListener('change', function () {
                    var file = fileInput.files && fileInput.files[0];
                    if (!file) {
                        emptyBox.style.display = 'flex';
                        previewBox.style.display = 'none';
                        previewImg.src = '';
                        return;
                    }
                    filenameEl.textContent = file.name;
                    if (file.type && file.type.startsWith('image/')) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            previewImg.src = e.target.result;
                            emptyBox.style.display = 'none';
                            previewBox.style.display = 'flex';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        emptyBox.style.display = 'none';
                        previewBox.style.display = 'flex';
                    }
                });
            }
        })();
    </script>
</x-atk-app>
