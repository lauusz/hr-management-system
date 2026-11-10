<x-inv-pesona-app title="Edit Barang — Inventory Pesona">
    <style>
        .pesona-item-form-page {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .pesona-form-head {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .pesona-form-head-top {
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
        .pesona-stock-pill-head {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 28px;
            padding: 0 11px;
            border-radius: 999px;
            background: var(--pesona-primary-soft, #EEF2F8);
            color: var(--pesona-primary, #28355A);
            font-size: 12px;
            font-weight: 700;
        }

        .pesona-form-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
            align-items: start;
        }
        .pesona-panel {
            padding: 18px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .pesona-panel + .pesona-panel {
            margin-top: 14px;
        }
        .pesona-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid #F1F5F9;
        }
        .pesona-panel-title {
            margin: 0;
            font-size: 13.5px;
            font-weight: 700;
            color: #0F172A;
        }
        .pesona-panel-sub {
            font-size: 11.5px;
            color: #64748B;
        }

        .pesona-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
        }
        .pesona-field + .pesona-field {
            margin-top: 14px;
        }
        .pesona-equation-box > .pesona-field,
        .pesona-limits-grid > .pesona-field {
            margin-top: 0;
        }
        .pesona-label {
            font-size: 12.5px;
            font-weight: 700;
            color: #334155;
        }
        .pesona-input,
        .pesona-textarea,
        .pesona-select {
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
        .pesona-input:disabled {
            background: #F1F5F9;
            color: #475569;
            cursor: not-allowed;
            opacity: 1;
        }
        .pesona-input:focus,
        .pesona-textarea:focus,
        .pesona-select:focus {
            outline: none;
            border-color: var(--pesona-primary, #28355A);
            box-shadow: 0 0 0 3px rgba(40, 53, 90, 0.10);
        }
        .pesona-textarea {
            min-height: 82px;
            resize: vertical;
        }
        .pesona-hint {
            margin: 0;
            font-size: 11.5px;
            color: #64748B;
            line-height: 1.4;
        }
        .pesona-locked-banner {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 12px;
            padding: 10px 12px;
            border: 1px solid #FDE68A;
            border-radius: 10px;
            background: #FFFBEB;
            color: #92400E;
            font-size: 12px;
            font-weight: 600;
        }
        .pesona-error {
            font-size: 12px;
            font-weight: 600;
            color: #B91C1C;
        }

        /* Unit Conversion Equation Box */
        .pesona-unit-preview {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            background: var(--pesona-primary-soft, #EEF2F8);
            color: var(--pesona-primary, #28355A);
            font-size: 11.5px;
            font-weight: 600;
        }
        .pesona-unit-preview strong {
            font-weight: 800;
        }
        .pesona-equation-box {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            padding: 14px;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            background: #F8FAFC;
        }
        .pesona-equation-op {
            display: none;
            height: 40px;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            font-weight: 800;
            color: #64748B;
        }

        /* Stock Limits Grid */
        .pesona-limits-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }
        .pesona-suffix-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .pesona-suffix-wrap .pesona-input {
            padding-right: 64px;
        }
        .pesona-input-suffix {
            position: absolute;
            right: 8px;
            padding: 3px 8px;
            border-radius: 6px;
            background: #F1F5F9;
            color: #475569;
            font-size: 11.5px;
            font-weight: 700;
            pointer-events: none;
            max-width: 52px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Current Photo & Dropzone */
        .pesona-current-photo-box {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            padding: 10px;
            border: 1px solid #F1F5F9;
            border-radius: 10px;
            background: #F8FAFC;
        }
        .pesona-current-photo {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #E2E8F0;
            background: #FFFFFF;
            flex-shrink: 0;
        }
        .pesona-dropzone {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 148px;
            padding: 16px 14px;
            border: 1.5px dashed #CBD5E1;
            border-radius: 12px;
            background: #F8FAFC;
            text-align: center;
            cursor: pointer;
            transition: border-color .15s ease, background-color .15s ease;
            overflow: hidden;
        }
        .pesona-dropzone:hover {
            border-color: var(--pesona-primary, #28355A);
            background: #F1F5F9;
        }
        .pesona-dropzone input[type="file"] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .pesona-dropzone-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid #E2E8F0;
            background: #FFFFFF;
            color: var(--pesona-primary, #28355A);
        }
        .pesona-dropzone-title {
            font-size: 12.5px;
            font-weight: 700;
            color: #0F172A;
        }
        .pesona-dropzone-sub {
            font-size: 11px;
            color: #64748B;
        }
        .pesona-dropzone-preview {
            display: none;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            width: 100%;
        }
        .pesona-dropzone-preview img {
            width: 104px;
            height: 104px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #E2E8F0;
            background: #FFFFFF;
        }
        .pesona-dropzone-filename {
            max-width: 100%;
            font-size: 11.5px;
            font-weight: 600;
            color: #0F172A;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .pesona-side-actions {
            display: grid;
            gap: 8px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #F1F5F9;
        }
        .pesona-btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            min-height: 40px;
            padding: 0 16px;
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
            width: 100%;
            min-height: 38px;
            padding: 0 16px;
            border: 1px solid var(--pesona-border, #E2E8F0);
            border-radius: 10px;
            background: #FFFFFF;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            box-sizing: border-box;
            transition: background-color .15s ease, color .15s ease;
        }
        .pesona-btn-cancel:hover {
            background: #F8FAFC;
            color: #0F172A;
        }

        @media (min-width: 640px) {
            .pesona-equation-box {
                grid-template-columns: 1fr auto 110px 1fr;
                align-items: end;
            }
            .pesona-equation-op {
                display: flex;
            }
            .pesona-limits-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 900px) {
            .pesona-form-layout {
                grid-template-columns: minmax(0, 1fr) 300px;
            }
        }
    </style>

    <div class="pesona-item-form-page">
        <div class="pesona-form-head">
            <div class="pesona-form-head-top">
                <a class="pesona-back-btn" href="{{ route('inventory.pesona.admin.items.index') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                    <span>Kembali</span>
                </a>
                <h1 class="pesona-title">Edit Barang Inventory Pesona</h1>
                <span class="pesona-stock-pill-head">Stok saat ini: {{ $item->stock_qty }} {{ $item->unit_name }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('inventory.pesona.admin.items.update', $item) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="pesona-form-layout">
                {{-- Kolom Kiri --}}
                <div>
                    <div class="pesona-panel">
                        <div class="pesona-panel-head">
                            <h2 class="pesona-panel-title">Detail &amp; Status Barang</h2>
                        </div>
                        <div class="pesona-field">
                            <label class="pesona-label" for="pesona_item_name">Nama Barang</label>
                            <input
                                class="pesona-input"
                                id="pesona_item_name"
                                name="name"
                                value="{{ old('name', $item->name) }}"
                                maxlength="150"
                                required
                                @error('name') aria-invalid="true" aria-describedby="error-name" @enderror
                            >
                            @error('name') <span class="pesona-error" id="error-name">{{ $message }}</span> @enderror
                        </div>
                        <div class="pesona-field">
                            <label class="pesona-label" for="pesona_item_desc">Deskripsi / Spesifikasi <span style="font-weight:500;color:#94A3B8">(Opsional)</span></label>
                            <textarea
                                class="pesona-textarea"
                                id="pesona_item_desc"
                                name="description"
                                @error('description') aria-invalid="true" aria-describedby="error-description" @enderror
                            >{{ old('description', $item->description) }}</textarea>
                            @error('description') <span class="pesona-error" id="error-description">{{ $message }}</span> @enderror
                        </div>
                        <div class="pesona-field">
                            <label class="pesona-label" for="pesona_item_status">Status Barang</label>
                            <select
                                class="pesona-select"
                                id="pesona_item_status"
                                name="is_active"
                                required
                                @error('is_active') aria-invalid="true" aria-describedby="error-status" @enderror
                            >
                                <option value="1" @selected((string) old('is_active', (int) $item->is_active) === '1')>Aktif (Tampil di Daftar Barang)</option>
                                <option value="0" @selected((string) old('is_active', (int) $item->is_active) === '0')>Nonaktif (Disembunyikan dari pengajuan baru)</option>
                            </select>
                            @error('is_active') <span class="pesona-error" id="error-status">{{ $message }}</span> @enderror
                            <p class="pesona-hint">Barang nonaktif tidak tersedia untuk pengajuan baru. Riwayat sebelumnya tetap tersimpan.</p>
                        </div>
                    </div>

                    <div class="pesona-panel">
                        <div class="pesona-panel-head">
                            <div>
                                <h2 class="pesona-panel-title">Satuan &amp; Isi Kemasan</h2>
                            </div>
                            <div class="pesona-unit-preview" id="pesonaUnitPreview" role="status" aria-live="polite">
                                <span>Rumus:</span>
                                <strong id="pesonaUnitPreviewVal">1 {{ $item->unit_name }} = {{ $item->unit_size }} {{ $item->content_unit_name }}</strong>
                            </div>
                        </div>

                        @if($unitsLocked)
                            <p class="pesona-locked-banner" id="unit-locked">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                <span>Satuan tidak dapat diubah karena barang sudah memiliki stok atau riwayat.</span>
                            </p>
                        @endif

                        <div class="pesona-equation-box">
                            <div class="pesona-field">
                                <label class="pesona-label" for="pesona_unit_name">1 Satuan Ambil</label>
                                <input
                                    class="pesona-input"
                                    id="pesona_unit_name"
                                    name="unit_name"
                                    value="{{ $unitsLocked ? $item->unit_name : old('unit_name', $item->unit_name) }}"
                                    maxlength="30"
                                    @disabled($unitsLocked)
                                    @if($unitsLocked) aria-describedby="unit-locked" @else required @endif
                                    @error('unit_name') aria-invalid="true" @enderror
                                >
                                @error('unit_name') <span class="pesona-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="pesona-equation-op">=</div>
                            <div class="pesona-field">
                                <label class="pesona-label" for="pesona_unit_size">Jumlah Isi</label>
                                <input
                                    class="pesona-input"
                                    id="pesona_unit_size"
                                    type="number"
                                    name="unit_size"
                                    value="{{ $unitsLocked ? $item->unit_size : old('unit_size', $item->unit_size) }}"
                                    min="1"
                                    max="4294967295"
                                    step="1"
                                    @disabled($unitsLocked)
                                    @if($unitsLocked) aria-describedby="unit-locked" @else required @endif
                                    @error('unit_size') aria-invalid="true" @enderror
                                >
                                @error('unit_size') <span class="pesona-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="pesona-field">
                                <label class="pesona-label" for="pesona_content_unit">Satuan Isi</label>
                                <input
                                    class="pesona-input"
                                    id="pesona_content_unit"
                                    name="content_unit_name"
                                    value="{{ $unitsLocked ? $item->content_unit_name : old('content_unit_name', $item->content_unit_name) }}"
                                    maxlength="30"
                                    @disabled($unitsLocked)
                                    @if($unitsLocked) aria-describedby="unit-locked" @else required @endif
                                    @error('content_unit_name') aria-invalid="true" @enderror
                                >
                                @error('content_unit_name') <span class="pesona-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <p class="pesona-hint" style="margin-top:8px">Contoh: 1 <strong>box</strong> berisi <strong>10</strong> <strong>pcs</strong>. Untuk barang satuan, isi 1.</p>
                    </div>

                    <div class="pesona-panel">
                        <div class="pesona-panel-head">
                            <h2 class="pesona-panel-title">Batas Stok &amp; Pengambilan</h2>
                            <span class="pesona-panel-sub">Mengikuti satuan ambil</span>
                        </div>
                        <div class="pesona-limits-grid">
                            <div class="pesona-field">
                                <label class="pesona-label" for="pesona_min_stock">Batas Stok Tipis</label>
                                <div class="pesona-suffix-wrap">
                                    <input
                                        class="pesona-input"
                                        id="pesona_min_stock"
                                        type="number"
                                        name="minimum_stock"
                                        value="{{ old('minimum_stock', $item->minimum_stock) }}"
                                        min="0"
                                        max="4294967295"
                                        step="1"
                                        @error('minimum_stock') aria-invalid="true" aria-describedby="error-minimum-stock" @enderror
                                    >
                                    <span class="pesona-input-suffix js-pesona-unit-suffix">{{ $item->unit_name }}</span>
                                </div>
                                @error('minimum_stock') <span class="pesona-error" id="error-minimum-stock">{{ $message }}</span> @enderror
                            </div>
                            <div class="pesona-field">
                                <label class="pesona-label" for="pesona_min_req">Minimum Ambil per Pengajuan</label>
                                <div class="pesona-suffix-wrap">
                                    <input
                                        class="pesona-input"
                                        id="pesona_min_req"
                                        type="number"
                                        name="min_request_qty"
                                        value="{{ old('min_request_qty', $item->min_request_qty) }}"
                                        min="1"
                                        max="4294967295"
                                        step="1"
                                        @error('min_request_qty') aria-invalid="true" aria-describedby="error-min-request" @enderror
                                    >
                                    <span class="pesona-input-suffix js-pesona-unit-suffix">{{ $item->unit_name }}</span>
                                </div>
                                @error('min_request_qty') <span class="pesona-error" id="error-min-request">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan --}}
                <div class="pesona-panel">
                    <div class="pesona-panel-head">
                        <h2 class="pesona-panel-title">Foto Barang</h2>
                        <span class="pesona-panel-sub">Opsional</span>
                    </div>

                    <div class="pesona-current-photo-box">
                        @if($item->image_path)
                            <img class="pesona-current-photo" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}" alt="Foto {{ $item->name }}">
                            <div>
                                <div style="font-size:12px;font-weight:700;color:#0F172A">Foto Saat Ini</div>
                                <div style="font-size:11px;color:#64748B">Unggah foto baru di bawah bila ingin mengganti.</div>
                            </div>
                        @else
                            <div style="font-size:12px;color:#64748B">Belum ada foto barang.</div>
                        @endif
                    </div>

                    <div class="pesona-dropzone" id="pesonaDropzone">
                        <input
                            id="pesona_image_input"
                            type="file"
                            name="image"
                            accept=".jpg,.jpeg,.png,.webp,.heic,.heif"
                            @error('image') aria-invalid="true" aria-describedby="error-image" @enderror
                        >
                        <div id="pesonaDropzoneEmpty" style="display:flex;flex-direction:column;align-items:center;gap:6px">
                            <span class="pesona-dropzone-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="17 8 12 3 7 8"/>
                                    <line x1="12" x2="12" y1="3" y2="15"/>
                                </svg>
                            </span>
                            <span class="pesona-dropzone-title">Klik untuk pilih foto pengganti</span>
                            <span class="pesona-dropzone-sub">JPG, PNG, WebP, HEIC (Maks. 2 MB)</span>
                        </div>
                        <div class="pesona-dropzone-preview" id="pesonaDropzonePreview">
                            <img id="pesonaDropzoneImg" src="" alt="Preview foto pengganti">
                            <span class="pesona-dropzone-filename" id="pesonaDropzoneFilename"></span>
                            <span style="font-size:11px;font-weight:700;color:var(--pesona-primary, #28355A)">Klik untuk ganti foto</span>
                        </div>
                    </div>
                    @error('image') <span class="pesona-error" id="error-image" style="display:block;margin-top:6px">{{ $message }}</span> @enderror
                    <p class="pesona-hint" style="margin-top:6px">Pilih ulang foto jika penyimpanan gagal.</p>

                    <div class="pesona-side-actions">
                        <button class="pesona-btn-submit" type="submit">Simpan Perubahan</button>
                        <a class="pesona-btn-cancel" href="{{ route('inventory.pesona.admin.items.index') }}">Batal</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const unitEl = document.getElementById('pesona_unit_name');
            const sizeEl = document.getElementById('pesona_unit_size');
            const contentEl = document.getElementById('pesona_content_unit');
            const previewEl = document.getElementById('pesonaUnitPreviewVal');
            const suffixes = document.querySelectorAll('.js-pesona-unit-suffix');

            function syncUnitPreview() {
                const unit = (unitEl?.value || '').trim() || 'unit';
                let size = parseInt(sizeEl?.value || '1', 10);
                if (!Number.isFinite(size) || size < 1) size = 1;
                const content = (contentEl?.value || '').trim() || 'pcs';
                if (previewEl) {
                    previewEl.textContent = '1 ' + unit + ' = ' + size + ' ' + content;
                }
                suffixes.forEach((el) => {
                    el.textContent = unit;
                });
            }

            [unitEl, sizeEl, contentEl].forEach((el) => {
                el?.addEventListener('input', syncUnitPreview);
                el?.addEventListener('change', syncUnitPreview);
            });
            syncUnitPreview();

            const fileInput = document.getElementById('pesona_image_input');
            const emptyBox = document.getElementById('pesonaDropzoneEmpty');
            const previewBox = document.getElementById('pesonaDropzonePreview');
            const previewImg = document.getElementById('pesonaDropzoneImg');
            const filenameEl = document.getElementById('pesonaDropzoneFilename');

            fileInput?.addEventListener('change', function () {
                const file = fileInput.files && fileInput.files[0];
                if (!file) {
                    if (emptyBox) emptyBox.style.display = 'flex';
                    if (previewBox) previewBox.style.display = 'none';
                    return;
                }
                if (filenameEl) filenameEl.textContent = file.name;
                if (file.type && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (ev) {
                        if (previewImg) previewImg.src = ev.target.result;
                        if (emptyBox) emptyBox.style.display = 'none';
                        if (previewBox) previewBox.style.display = 'flex';
                    };
                    reader.readAsDataURL(file);
                } else {
                    if (emptyBox) emptyBox.style.display = 'none';
                    if (previewBox) previewBox.style.display = 'flex';
                }
            });
        })();
    </script>
</x-inv-pesona-app>
