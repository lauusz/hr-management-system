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

        /* Custom Unit Combobox (Dropdown + Manual Input) */
        .atk-unit-combobox {
            position: relative;
            width: 100%;
        }
        .atk-unit-combobox-control {
            position: relative;
            display: flex;
            align-items: center;
        }
        .atk-unit-combobox-input {
            padding-right: 34px;
        }
        .atk-unit-combobox-toggle {
            position: absolute;
            right: 4px;
            width: 28px;
            height: 28px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #64748B;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: color .14s ease, background-color .14s ease;
        }
        .atk-unit-combobox-toggle:hover {
            color: var(--atk-text);
            background: #F1F5F9;
        }
        .atk-unit-combobox-menu {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            z-index: 40;
            background: #FFFFFF;
            border: 1px solid var(--atk-line);
            border-radius: 9px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.10);
            padding: 4px;
            max-height: 220px;
            overflow-y: auto;
        }
        .atk-unit-combobox-list {
            display: grid;
            gap: 2px;
        }
        .atk-unit-combobox-option {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 7px 10px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: var(--atk-text);
            font: inherit;
            font-size: 12.5px;
            font-weight: 500;
            text-align: left;
            cursor: pointer;
        }
        .atk-unit-combobox-option:hover,
        .atk-unit-combobox-option.is-focused {
            background: #F1F5F9;
        }
        .atk-unit-combobox-option[aria-selected="true"] {
            background: var(--atk-primary-soft);
            color: var(--atk-primary-dark);
            font-weight: 600;
        }
        .atk-unit-combobox-check {
            opacity: 0;
            color: var(--atk-primary);
            flex-shrink: 0;
        }
        .atk-unit-combobox-option[aria-selected="true"] .atk-unit-combobox-check {
            opacity: 1;
        }
        .atk-unit-combobox-create {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 2px;
            padding: 7px 10px;
            border: 1px dashed #C4B5FD;
            border-radius: 6px;
            background: var(--atk-primary-softer);
            color: var(--atk-primary-dark);
            font: inherit;
            font-size: 12px;
            font-weight: 600;
            text-align: left;
            cursor: pointer;
        }
        .atk-unit-combobox-create:hover,
        .atk-unit-combobox-create.is-focused {
            background: var(--atk-primary-soft);
        }
        .atk-unit-combobox-menu[hidden],
        .atk-unit-combobox-option[hidden],
        .atk-unit-combobox-create[hidden] {
            display: none !important;
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

                        @php
                            $availableUnits = $unitOptions ?? App\Models\AtkItem::availableUnitOptions();
                            $selectedUnitName = old('unit_name', 'pcs');
                            $selectedContentUnitName = old('content_unit_name', 'pcs');
                            if ($selectedUnitName !== '' && ! in_array($selectedUnitName, $availableUnits, true)) {
                                $availableUnits[] = $selectedUnitName;
                            }
                            if ($selectedContentUnitName !== '' && ! in_array($selectedContentUnitName, $availableUnits, true)) {
                                $availableUnits[] = $selectedContentUnitName;
                            }
                        @endphp
                        <div class="atk-equation-box">
                            <div>
                                <label class="atk-label" for="unit_name">1 Satuan Ambil</label>
                                <div class="atk-unit-combobox" data-unit-combobox>
                                    <div class="atk-unit-combobox-control">
                                        <input
                                            class="atk-input atk-unit-combobox-input"
                                            id="unit_name"
                                            name="unit_name"
                                            type="text"
                                            value="{{ $selectedUnitName }}"
                                            maxlength="30"
                                            placeholder="Pilih / ketik satuan..."
                                            autocomplete="off"
                                            role="combobox"
                                            aria-autocomplete="list"
                                            aria-expanded="false"
                                            aria-controls="unit_name_listbox"
                                            required
                                            data-unit-input
                                        >
                                        <button
                                            class="atk-unit-combobox-toggle"
                                            type="button"
                                            tabindex="-1"
                                            aria-label="Tampilkan daftar satuan ambil"
                                            data-unit-toggle
                                        >
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                                        </button>
                                    </div>
                                    <div class="atk-unit-combobox-menu" id="unit_name_listbox" role="listbox" hidden data-unit-menu>
                                        <div class="atk-unit-combobox-list" data-unit-list>
                                            @foreach($availableUnits as $option)
                                                <button
                                                    type="button"
                                                    role="option"
                                                    class="atk-unit-combobox-option"
                                                    data-unit-option="{{ $option }}"
                                                    aria-selected="{{ $selectedUnitName === $option ? 'true' : 'false' }}"
                                                >
                                                    <span>{{ $option }}</span>
                                                    <svg class="atk-unit-combobox-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                                                </button>
                                            @endforeach
                                        </div>
                                        <button type="button" class="atk-unit-combobox-create" hidden data-unit-create>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            <span>Tambah satuan baru: "<strong data-unit-create-label></strong>"</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="atk-equation-op">=</div>
                            <div>
                                <label class="atk-label" for="unit_size">Jumlah Isi</label>
                                <input class="atk-input" id="unit_size" type="number" min="1" name="unit_size" value="{{ old('unit_size', 1) }}" required>
                            </div>
                            <div>
                                <label class="atk-label" for="content_unit_name">Satuan Isi</label>
                                <div class="atk-unit-combobox" data-unit-combobox>
                                    <div class="atk-unit-combobox-control">
                                        <input
                                            class="atk-input atk-unit-combobox-input"
                                            id="content_unit_name"
                                            name="content_unit_name"
                                            type="text"
                                            value="{{ $selectedContentUnitName }}"
                                            maxlength="30"
                                            placeholder="Pilih / ketik satuan..."
                                            autocomplete="off"
                                            role="combobox"
                                            aria-autocomplete="list"
                                            aria-expanded="false"
                                            aria-controls="content_unit_name_listbox"
                                            required
                                            data-unit-input
                                        >
                                        <button
                                            class="atk-unit-combobox-toggle"
                                            type="button"
                                            tabindex="-1"
                                            aria-label="Tampilkan daftar satuan isi"
                                            data-unit-toggle
                                        >
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                                        </button>
                                    </div>
                                    <div class="atk-unit-combobox-menu" id="content_unit_name_listbox" role="listbox" hidden data-unit-menu>
                                        <div class="atk-unit-combobox-list" data-unit-list>
                                            @foreach($availableUnits as $option)
                                                <button
                                                    type="button"
                                                    role="option"
                                                    class="atk-unit-combobox-option"
                                                    data-unit-option="{{ $option }}"
                                                    aria-selected="{{ $selectedContentUnitName === $option ? 'true' : 'false' }}"
                                                >
                                                    <span>{{ $option }}</span>
                                                    <svg class="atk-unit-combobox-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                                                </button>
                                            @endforeach
                                        </div>
                                        <button type="button" class="atk-unit-combobox-create" hidden data-unit-create>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            <span>Tambah satuan baru: "<strong data-unit-create-label></strong>"</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="atk-field-hint" style="margin-top:8px">Pilih dari daftar atau ketik manual jika satuan belum tersedia (otomatis tersimpan ke daftar satuan). Contoh: 1 <strong>box</strong> = <strong>20</strong> <strong>pcs</strong>.</p>
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

            var comboboxes = Array.prototype.slice.call(document.querySelectorAll('[data-unit-combobox]'));

            function addOptionToAllComboboxes(newUnit) {
                var clean = String(newUnit || '').trim();
                if (!clean) return;
                comboboxes.forEach(function (box) {
                    var listEl = box.querySelector('[data-unit-list]');
                    if (!listEl) return;
                    var exists = Array.prototype.some.call(listEl.querySelectorAll('[data-unit-option]'), function (btn) {
                        return (btn.getAttribute('data-unit-option') || '').toLowerCase() === clean.toLowerCase();
                    });
                    if (!exists) {
                        var btn = document.createElement('button');
                        btn.type = 'button';
                        btn.setAttribute('role', 'option');
                        btn.className = 'atk-unit-combobox-option';
                        btn.setAttribute('data-unit-option', clean);
                        btn.setAttribute('aria-selected', 'false');
                        var span = document.createElement('span');
                        span.textContent = clean;
                        btn.appendChild(span);
                        btn.insertAdjacentHTML('beforeend', '<svg class="atk-unit-combobox-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>');
                        listEl.appendChild(btn);
                    }
                });
            }

            comboboxes.forEach(function (box) {
                var input = box.querySelector('[data-unit-input]');
                var toggle = box.querySelector('[data-unit-toggle]');
                var menu = box.querySelector('[data-unit-menu]');
                var listEl = box.querySelector('[data-unit-list]');
                var createBtn = box.querySelector('[data-unit-create]');
                var createLabel = box.querySelector('[data-unit-create-label]');
                if (!input || !menu || !listEl || !createBtn || !createLabel) return;

                function syncSelectedState() {
                    var current = input.value.trim().toLowerCase();
                    Array.prototype.forEach.call(listEl.querySelectorAll('[data-unit-option]'), function (btn) {
                        var val = (btn.getAttribute('data-unit-option') || '').toLowerCase();
                        btn.setAttribute('aria-selected', val === current ? 'true' : 'false');
                    });
                }

                function filterOptions(filterText) {
                    var query = String(filterText || '').trim();
                    var lowerQuery = query.toLowerCase();
                    var hasExactMatch = false;

                    Array.prototype.forEach.call(listEl.querySelectorAll('[data-unit-option]'), function (btn) {
                        var val = btn.getAttribute('data-unit-option') || '';
                        var lowerVal = val.toLowerCase();
                        if (lowerVal === lowerQuery) {
                            hasExactMatch = true;
                        }
                        btn.hidden = lowerQuery !== '' && lowerVal.indexOf(lowerQuery) === -1;
                    });

                    if (query !== '' && !hasExactMatch) {
                        createLabel.textContent = query;
                        createBtn.hidden = false;
                    } else {
                        createBtn.hidden = true;
                    }
                    syncSelectedState();
                }

                function openMenu(filterText) {
                    comboboxes.forEach(function (other) {
                        if (other !== box) {
                            var otherMenu = other.querySelector('[data-unit-menu]');
                            var otherInput = other.querySelector('[data-unit-input]');
                            if (otherMenu) otherMenu.hidden = true;
                            if (otherInput) otherInput.setAttribute('aria-expanded', 'false');
                        }
                    });
                    filterOptions(filterText);
                    menu.hidden = false;
                    input.setAttribute('aria-expanded', 'true');
                }

                function closeMenu() {
                    menu.hidden = true;
                    input.setAttribute('aria-expanded', 'false');
                }

                function selectUnit(val) {
                    var clean = String(val || '').trim();
                    if (!clean) return;
                    addOptionToAllComboboxes(clean);
                    input.value = clean;
                    syncSelectedState();
                    closeMenu();
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }

                input.addEventListener('focus', function () {
                    openMenu('');
                });

                input.addEventListener('click', function () {
                    if (menu.hidden) {
                        openMenu('');
                    }
                });

                input.addEventListener('input', function () {
                    openMenu(input.value);
                });

                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        closeMenu();
                    } else if (e.key === 'Enter' && !menu.hidden) {
                        e.preventDefault();
                        var typed = input.value.trim();
                        if (typed) {
                            selectUnit(typed);
                        }
                    }
                });

                input.addEventListener('blur', function () {
                    setTimeout(function () {
                        if (!box.contains(document.activeElement)) {
                            var typed = input.value.trim();
                            if (typed) {
                                addOptionToAllComboboxes(typed);
                                syncSelectedState();
                            }
                            closeMenu();
                        }
                    }, 150);
                });

                if (toggle) {
                    toggle.addEventListener('click', function () {
                        if (menu.hidden) {
                            input.focus();
                            openMenu('');
                        } else {
                            closeMenu();
                        }
                    });
                }

                listEl.addEventListener('mousedown', function (e) {
                    var optionBtn = e.target.closest('[data-unit-option]');
                    if (!optionBtn) return;
                    e.preventDefault();
                    selectUnit(optionBtn.getAttribute('data-unit-option'));
                });

                createBtn.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    selectUnit(input.value.trim());
                });
            });

            document.addEventListener('click', function (e) {
                comboboxes.forEach(function (box) {
                    if (!box.contains(e.target)) {
                        var menu = box.querySelector('[data-unit-menu]');
                        var input = box.querySelector('[data-unit-input]');
                        if (menu) menu.hidden = true;
                        if (input) input.setAttribute('aria-expanded', 'false');
                    }
                });
            });

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
