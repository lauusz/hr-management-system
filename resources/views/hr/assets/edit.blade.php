<x-app title="Edit Asset">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.1 2.1 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Edit Inventaris Karyawan</h1>
                <p class="section-subtitle">Ubah data identitas asset. Perpindahan pemegang tetap dicatat dari halaman detail.</p>
            </div>
        </div>
    </x-slot>

    <div class="asset-edit-page">
        @if ($errors->any())
            <div class="alert-error">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                    <path d="M12 8v4M12 16h.01" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <div>
                    <strong>Terjadi Kesalahan</strong>
                    <span>{{ $errors->first() }}</span>
                </div>
            </div>
        @endif

        <div class="page-header">
            <a href="{{ route('hr.assets.show', $asset) }}" class="btn-back" aria-label="Kembali ke detail asset">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('hr.assets.update', $asset) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="section-card">
                <div class="section-card-header">
                    <div class="section-card-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h2 class="section-card-title">Data Asset</h2>
                </div>

                <div class="section-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="asset_code">Kode Asset <span class="req">*</span></label>
                            <input class="form-control" id="asset_code" name="asset_code" value="{{ old('asset_code', $asset->asset_code) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="category_name">Kategori</label>
                            <input class="form-control" id="category_name" name="category_name" value="{{ old('category_name', $asset->category->name ?? '') }}" placeholder="Laptop, Mouse, Tas">
                        </div>
                        <div class="form-group full-width">
                            <label for="name">Nama Asset <span class="req">*</span></label>
                            <input class="form-control" id="name" name="name" value="{{ old('name', $asset->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="brand">Brand</label>
                            <input class="form-control" id="brand" name="brand" value="{{ old('brand', $asset->brand) }}">
                        </div>
                        <div class="form-group">
                            <label for="model">Model</label>
                            <input class="form-control" id="model" name="model" value="{{ old('model', $asset->model) }}">
                        </div>
                        <div class="form-group">
                            <label for="serial_number">Serial Number</label>
                            <input class="form-control" id="serial_number" name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="hostname">Hostname</label>
                            <input class="form-control" id="hostname" name="hostname" value="{{ old('hostname', $asset->hostname) }}" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="email_laptop">Email Laptop</label>
                            <input class="form-control" id="email_laptop" type="email" name="email_laptop" value="{{ old('email_laptop', $asset->email_laptop) }}" placeholder="email.laptop@perusahaan.com" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="condition_status">Kondisi <span class="req">*</span></label>
                            <select class="form-control" id="condition_status" name="condition_status" required>
                                @foreach(['GOOD' => 'Baik', 'NEED_REPAIR' => 'Perlu Service', 'DAMAGED' => 'Rusak'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('condition_status', $asset->condition_status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="purchase_date">Tanggal Pembelian</label>
                            <input class="form-control" id="purchase_date" type="date" name="purchase_date" value="{{ old('purchase_date', optional($asset->purchase_date)->toDateString()) }}">
                        </div>
                        <div class="form-group">
                            <label for="current_pt_id">PT</label>
                            <select class="form-control" id="current_pt_id" name="current_pt_id">
                                <option value="">-</option>
                                @foreach($pts as $pt)
                                    <option value="{{ $pt->id }}" @selected((string) old('current_pt_id', $asset->current_pt_id) === (string) $pt->id)>{{ $pt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label for="notes">Catatan</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $asset->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-card-header">
                    <div class="section-card-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.6-4.6a2 2 0 012.8 0L16 16m-2-2l1.6-1.6a2 2 0 012.8 0L20 14m-6-6h.01M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/>
                        </svg>
                    </div>
                    <h2 class="section-card-title">Foto Asset</h2>
                </div>

                <div class="section-body">
                    <div class="photo-grid">
                        <div class="form-group">
                            <label for="photo">Upload Foto Baru</label>
                            <div class="asset-upload">
                                <input class="asset-upload__input js-upload-input" id="photo" type="file" name="photo" accept="image/*,.heic,.heif" data-title="photoUploadTitle" data-desc="photoUploadDesc" data-empty-title="Klik untuk upload foto" data-empty-desc="JPG, PNG, HEIC, WEBP, dan format foto lainnya, maksimal 2MB" data-filled-desc="Foto asset baru siap diupload">
                                <div class="asset-upload__content">
                                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <span class="asset-upload__title" id="photoUploadTitle">Klik untuk upload foto</span>
                                    <span class="asset-upload__desc" id="photoUploadDesc">JPG, PNG, HEIC, WEBP, dan format foto lainnya, maksimal 2MB</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <span class="current-photo-label">Foto Saat Ini</span>
                            @if($asset->photo_path)
                                <img class="current-photo" src="{{ asset('storage/'.$asset->photo_path) }}" alt="Foto {{ $asset->name }}">
                            @else
                                <div class="current-photo-placeholder">Belum ada foto.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a class="btn-secondary" href="{{ route('hr.assets.show', $asset) }}">Batal</a>
                <button class="btn-primary" type="submit">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <style>
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-header-inline .section-icon,
        .section-card-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .section-header-inline .section-icon svg,
        .section-card-icon svg {
            width: 16px;
            height: 16px;
        }
        .section-header-inline .section-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-header-inline .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy,
        .section-card-icon {
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark);
        }

        .asset-edit-page {
            max-width: 860px;
            margin: 0 auto;
            padding: 0 16px 84px;
        }
        .alert-error {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 14px;
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--error);
            font-size: 13px;
            margin-bottom: 16px;
        }
        .alert-error strong {
            display: block;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .alert-error span {
            color: var(--text-secondary);
        }
        .alert-error svg {
            flex-shrink: 0;
            margin-top: 1px;
        }

        .page-header {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 20px;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 40px;
            padding: 0 14px 0 12px;
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        .btn-back:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--gray-50);
        }
        .btn-back:hover svg {
            transform: translateX(-2px);
        }
        .btn-back svg {
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }

        .section-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            margin-bottom: 16px;
            overflow: hidden;
        }
        .section-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px 16px 12px;
            border-bottom: 1px solid var(--border);
        }
        .section-card-title {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.01em;
        }
        .section-body {
            padding: 16px;
        }
        .form-grid,
        .photo-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }
        .full-width {
            grid-column: 1 / -1;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group label,
        .current-photo-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-secondary);
        }
        .req {
            color: var(--error);
        }
        .form-control {
            width: 100%;
            min-height: 44px;
            padding: 10px 12px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font: inherit;
            font-size: 0.88rem;
            color: var(--text-primary);
            background: var(--white);
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .form-control::placeholder {
            color: var(--text-muted);
            opacity: 0.75;
        }
        select.form-control {
            cursor: pointer;
        }
        textarea.form-control {
            resize: vertical;
            min-height: 90px;
        }
        .asset-upload {
            position: relative;
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            background: var(--gray-50);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .asset-upload:hover,
        .asset-upload:focus-within {
            border-color: var(--primary);
            background: rgba(20, 93, 160, 0.03);
        }
        .asset-upload__input {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .asset-upload__content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            color: var(--text-muted);
            pointer-events: none;
        }
        .asset-upload__title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
        }
        .asset-upload__desc {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 500;
            word-break: break-word;
        }
        .current-photo,
        .current-photo-placeholder {
            width: 100%;
            min-height: 150px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--gray-50);
        }
        .current-photo {
            height: 180px;
            object-fit: cover;
        }
        .current-photo-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 600;
        }
        .form-actions {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 40;
            display: flex;
            gap: 10px;
            padding: 12px 16px;
            background: rgba(255,255,255,0.96);
            border-top: 1px solid var(--border);
            box-shadow: 0 -4px 20px rgba(17, 24, 39, 0.08);
            backdrop-filter: blur(8px);
        }
        .btn-primary,
        .btn-secondary {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 18px;
            border-radius: 12px;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            flex: 1;
        }
        .btn-primary {
            color: #fff;
            border: 0;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
        }
        .btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .btn-secondary {
            color: var(--text-secondary);
            background: var(--white);
            border: 1.5px solid var(--border);
        }
        .btn-secondary:hover {
            color: var(--primary);
            background: var(--gray-50);
            border-color: var(--primary);
        }

        @media (min-width: 640px) {
            .asset-edit-page {
                padding: 0 24px 40px;
            }
            .form-grid,
            .photo-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 16px;
            }
            .section-card {
                margin-bottom: 20px;
            }
            .section-card-header,
            .section-body {
                padding-inline: 20px;
            }
            .form-actions {
                position: static;
                justify-content: flex-end;
                padding: 16px 0 0;
                background: transparent;
                border-top: 1px solid var(--border);
                box-shadow: none;
                backdrop-filter: none;
            }
            .btn-primary,
            .btn-secondary {
                flex: 0 0 auto;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.js-upload-input').forEach(function(input) {
                const title = document.getElementById(input.dataset.title);
                const desc = document.getElementById(input.dataset.desc);
                if (!title || !desc) return;

                input.addEventListener('change', function() {
                    const file = this.files[0];
                    title.textContent = file ? file.name : this.dataset.emptyTitle;
                    desc.textContent = file ? this.dataset.filledDesc : this.dataset.emptyDesc;
                });
            });
        });
    </script>
</x-app>
