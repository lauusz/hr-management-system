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
        @php
            $availableUnits = $unitOptions ?? App\Models\AtkItem::availableUnitOptions();
            $currentUnit = old('unit_name', $item->unit_name);
            $currentContentUnit = old('content_unit_name', $item->content_unit_name);
            if ($currentUnit !== '' && ! in_array($currentUnit, $availableUnits, true)) {
                $availableUnits[] = $currentUnit;
            }
            if ($currentContentUnit !== '' && ! in_array($currentContentUnit, $availableUnits, true)) {
                $availableUnits[] = $currentContentUnit;
            }
        @endphp
        <div class="atk-form-grid">
            <div>
                <label class="atk-label">Nama Barang</label>
                <input class="atk-input" name="name" value="{{ old('name', $item->name) }}" required>
            </div>
            <div>
                <label class="atk-label" for="unit_name">Satuan Ambil</label>
                <div class="atk-unit-combobox" data-unit-combobox>
                    <div class="atk-unit-combobox-control">
                        <input
                            class="atk-input atk-unit-combobox-input"
                            id="unit_name"
                            name="unit_name"
                            type="text"
                            value="{{ $currentUnit }}"
                            maxlength="30"
                            placeholder="Pilih / ketik satuan..."
                            autocomplete="off"
                            role="combobox"
                            aria-autocomplete="list"
                            aria-expanded="false"
                            aria-controls="edit_unit_name_listbox"
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
                    <div class="atk-unit-combobox-menu" id="edit_unit_name_listbox" role="listbox" hidden data-unit-menu>
                        <div class="atk-unit-combobox-list" data-unit-list>
                            @foreach($availableUnits as $option)
                                <button
                                    type="button"
                                    role="option"
                                    class="atk-unit-combobox-option"
                                    data-unit-option="{{ $option }}"
                                    aria-selected="{{ $option === $currentUnit ? 'true' : 'false' }}"
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
                <p class="atk-form-helper">Satuan yang dipakai user saat meminta barang. Bisa pilih atau ketik baru.</p>
            </div>
            <div>
                <label class="atk-label" for="unit_size">Isi per Satuan</label>
                <input class="atk-input" id="unit_size" type="number" min="1" name="unit_size" value="{{ old('unit_size', $item->unit_size) }}" required>
                <p class="atk-form-helper">Jumlah satuan isi dalam satu satuan ambil. Contoh: 1 box berisi <strong>20</strong> pcs.</p>
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
                            value="{{ $currentContentUnit }}"
                            maxlength="30"
                            placeholder="Pilih / ketik satuan..."
                            autocomplete="off"
                            role="combobox"
                            aria-autocomplete="list"
                            aria-expanded="false"
                            aria-controls="edit_content_unit_name_listbox"
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
                    <div class="atk-unit-combobox-menu" id="edit_content_unit_name_listbox" role="listbox" hidden data-unit-menu>
                        <div class="atk-unit-combobox-list" data-unit-list>
                            @foreach($availableUnits as $option)
                                <button
                                    type="button"
                                    role="option"
                                    class="atk-unit-combobox-option"
                                    data-unit-option="{{ $option }}"
                                    aria-selected="{{ $option === $currentContentUnit ? 'true' : 'false' }}"
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
                <p class="atk-form-helper">Satuan terkecil yang menyusun isi. Bisa pilih atau ketik manual.</p>
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
                el.addEventListener('change', update);
            });

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
        })();
    </script>
</x-atk-app>
