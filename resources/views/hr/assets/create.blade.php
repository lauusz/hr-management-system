<x-app title="Tambah Asset">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Tambah Inventaris Karyawan</h1>
                <p class="section-subtitle">Catat perangkat kantor dan pemegang awal jika asset langsung diserahkan</p>
            </div>
        </div>
    </x-slot>

    <div class="mlc-container">

        <a href="{{ route('hr.assets.index') }}" class="back-btn" aria-label="Kembali ke daftar asset">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        {{-- Flash / Error Messages --}}
        @if (session('error'))
            <div class="mlc-alert mlc-alert--error">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mlc-alert mlc-alert--error" role="alert">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @if (session('success'))
            <div class="mlc-alert mlc-alert--success">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Info Banner --}}
        <div class="mlc-banner" role="note">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Kolom bertanda <strong>*</strong> wajib diisi. Isi bagian Pemegang Awal bila perangkat langsung diserahkan kepada karyawan.</span>
        </div>

        {{-- Form --}}
        <div class="manual-form-shell">
            <form method="POST" action="{{ route('hr.assets.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Langkah 1: Data Asset --}}
                <div class="mlc-step">
                    <div class="mlc-step__header">
                        <span class="mlc-step__num">1</span>
                        <div class="mlc-step__headtext">
                            <h2 class="mlc-step__title">Data Asset</h2>
                            <span class="mlc-step__sub">Langkah 1 dari 2 · Lengkapi identitas perangkat, spesifikasi, foto, dan kondisi awal</span>
                        </div>
                    </div>

                    <div class="mlc-grid-2">
                        <div class="mlc-field">
                            <label for="asset_code" class="mlc-label">
                                Kode Asset <span class="mlc-required">*</span>
                            </label>
                            <input
                                class="mlc-input @error('asset_code') mlc-input--invalid @enderror"
                                id="asset_code"
                                name="asset_code"
                                value="{{ old('asset_code') }}"
                                placeholder="Contoh: IT-LPT-0001"
                                required>
                        </div>

                        <div class="mlc-field">
                            <label for="category_name" class="mlc-label">
                                Kategori
                            </label>
                            <input
                                class="mlc-input @error('category_name') mlc-input--invalid @enderror"
                                id="category_name"
                                name="category_name"
                                value="{{ old('category_name') }}"
                                placeholder="Laptop, Mouse, Tas">
                        </div>

                        <div class="mlc-field mlc-field--full">
                            <label for="name" class="mlc-label">
                                Nama Asset <span class="mlc-required">*</span>
                            </label>
                            <input
                                class="mlc-input @error('name') mlc-input--invalid @enderror"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Contoh: Lenovo ThinkPad E14"
                                required>
                        </div>

                        <div class="mlc-field">
                            <label for="brand" class="mlc-label">
                                Brand
                            </label>
                            <input
                                class="mlc-input @error('brand') mlc-input--invalid @enderror"
                                id="brand"
                                name="brand"
                                value="{{ old('brand') }}"
                                placeholder="Contoh: Lenovo">
                        </div>

                        <div class="mlc-field">
                            <label for="model" class="mlc-label">
                                Model
                            </label>
                            <input
                                class="mlc-input @error('model') mlc-input--invalid @enderror"
                                id="model"
                                name="model"
                                value="{{ old('model') }}"
                                placeholder="Contoh: ThinkPad E14">
                        </div>

                        <div class="mlc-field">
                            <label for="serial_number" class="mlc-label">
                                Serial Number
                            </label>
                            <input
                                class="mlc-input @error('serial_number') mlc-input--invalid @enderror"
                                id="serial_number"
                                name="serial_number"
                                value="{{ old('serial_number') }}"
                                autocomplete="off">
                        </div>

                        <div class="mlc-field">
                            <label for="hostname" class="mlc-label">
                                Hostname
                            </label>
                            <input
                                class="mlc-input @error('hostname') mlc-input--invalid @enderror"
                                id="hostname"
                                name="hostname"
                                value="{{ old('hostname') }}"
                                autocomplete="off">
                        </div>

                        <div class="mlc-field">
                            <label for="email_laptop" class="mlc-label">
                                Email Laptop
                            </label>
                            <input
                                class="mlc-input @error('email_laptop') mlc-input--invalid @enderror"
                                id="email_laptop"
                                type="email"
                                name="email_laptop"
                                value="{{ old('email_laptop') }}"
                                placeholder="email.laptop@perusahaan.com"
                                autocomplete="off">
                        </div>

                        <div class="mlc-field">
                            <label for="condition_status" class="mlc-label">
                                Kondisi <span class="mlc-required">*</span>
                            </label>
                            <div class="mlc-select-wrap">
                                <select class="mlc-select @error('condition_status') mlc-input--invalid @enderror" id="condition_status" name="condition_status" required>
                                    @foreach(['GOOD' => 'Baik', 'NEED_REPAIR' => 'Perlu Service', 'DAMAGED' => 'Rusak'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('condition_status', 'GOOD') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <svg class="mlc-select__arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>

                        <div class="mlc-field">
                            <label for="purchase_date" class="mlc-label">
                                Tanggal Pembelian
                            </label>
                            <input
                                class="mlc-input @error('purchase_date') mlc-input--invalid @enderror"
                                id="purchase_date"
                                type="date"
                                name="purchase_date"
                                value="{{ old('purchase_date') }}">
                        </div>

                        <div class="mlc-field">
                            <label for="photo" class="mlc-label">
                                Foto Asset
                            </label>
                            <div class="asset-upload">
                                <input
                                    class="asset-upload__input js-upload-input"
                                    id="photo"
                                    type="file"
                                    name="photo"
                                    accept="image/*,.heic,.heif"
                                    data-title="photoUploadTitle"
                                    data-desc="photoUploadDesc"
                                    data-empty-title="Klik untuk upload foto"
                                    data-empty-desc="JPG, PNG, HEIC, WEBP, dan format foto lainnya, maksimal 2MB"
                                    data-filled-desc="Foto asset siap diupload">
                                <div class="asset-upload__content">
                                    <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <span class="asset-upload__title" id="photoUploadTitle">Klik untuk upload foto</span>
                                    <span class="asset-upload__desc" id="photoUploadDesc">JPG, PNG, HEIC, WEBP, dan format foto lainnya, maksimal 2MB</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Langkah 2: Pemegang Awal --}}
                <div class="mlc-step">
                    <div class="mlc-step__header">
                        <span class="mlc-step__num">2</span>
                        <div class="mlc-step__headtext">
                            <h2 class="mlc-step__title">Pemegang Awal</h2>
                            <span class="mlc-step__sub">Langkah 2 dari 2 · Opsional jika asset langsung diserahkan kepada karyawan</span>
                        </div>
                    </div>

                    <div class="mlc-grid-2">
                        <div class="mlc-field">
                            <label for="current_user_search" class="mlc-label">
                                Karyawan
                            </label>
                            <div class="asset-user-picker">
                                <input
                                    class="mlc-input @error('current_user_id') mlc-input--invalid @enderror"
                                    id="current_user_search"
                                    type="search"
                                    placeholder="Ketik nama karyawan..."
                                    autocomplete="off"
                                    aria-controls="current_user_suggestions"
                                    aria-expanded="false"
                                    value="{{ old('current_user_id') ? optional($users->firstWhere('id', (int) old('current_user_id')))->name : '' }}">
                                <div id="current_user_suggestions" class="asset-user-suggestions" role="listbox" aria-label="Saran karyawan" hidden></div>
                                <select id="current_user_id" name="current_user_id" class="asset-user-select-hidden" tabindex="-1" aria-hidden="true">
                                    <option value="">Belum diserahkan</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected((string) old('current_user_id') === (string) $user->id)>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mlc-helper">Ketik nama, lalu pilih karyawan dari daftar saran.</p>
                        </div>

                        <div class="mlc-field">
                            <label for="current_pt_id" class="mlc-label">
                                PT
                            </label>
                            <div class="mlc-select-wrap">
                                <select class="mlc-select @error('current_pt_id') mlc-input--invalid @enderror" id="current_pt_id" name="current_pt_id">
                                    <option value="">-</option>
                                    @foreach($pts as $pt)
                                        <option value="{{ $pt->id }}" @selected((string) old('current_pt_id') === (string) $pt->id)>{{ $pt->name }}</option>
                                    @endforeach
                                </select>
                                <svg class="mlc-select__arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>

                        <div class="mlc-field">
                            <label for="movement_date" class="mlc-label">
                                Tanggal Serah Terima
                            </label>
                            <input
                                class="mlc-input @error('movement_date') mlc-input--invalid @enderror"
                                id="movement_date"
                                type="text"
                                name="movement_date"
                                autocomplete="off"
                                value="{{ old('movement_date', now()->toDateString()) }}">
                        </div>

                        <div class="mlc-field">
                            <label for="handover_document" class="mlc-label">
                                Dokumen Serah Terima
                            </label>
                            <div class="asset-upload">
                                <input
                                    class="asset-upload__input js-upload-input"
                                    id="handover_document"
                                    type="file"
                                    name="handover_document"
                                    accept="image/*,.heic,.heif"
                                    data-title="handoverUploadTitle"
                                    data-desc="handoverUploadDesc"
                                    data-empty-title="Klik untuk upload dokumen"
                                    data-empty-desc="JPG, PNG, HEIC, WEBP, dan format foto lainnya, maksimal 2MB"
                                    data-filled-desc="Dokumen serah terima siap diupload">
                                <div class="asset-upload__content">
                                    <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <span class="asset-upload__title" id="handoverUploadTitle">Klik untuk upload dokumen</span>
                                    <span class="asset-upload__desc" id="handoverUploadDesc">JPG, PNG, HEIC, WEBP, dan format foto lainnya, maksimal 2MB</span>
                                </div>
                            </div>
                        </div>

                        <div class="mlc-field mlc-field--full">
                            <label for="notes" class="mlc-label">
                                Catatan
                            </label>
                            <textarea
                                class="mlc-textarea @error('notes') mlc-input--invalid @enderror"
                                id="notes"
                                name="notes"
                                rows="3"
                                placeholder="Catatan kondisi, kelengkapan, atau keterangan serah terima"
                            >{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="apv-action-bar" aria-label="Aksi simpan asset">
                    <div class="apv-action-dock">
                        <div class="apv-action-main">
                            <a href="{{ route('hr.assets.index') }}" class="apv-action-btn apv-action-btn--secondary">
                                Batal
                            </a>
                            <button type="submit" class="apv-action-btn apv-action-btn--primary">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Simpan Asset
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>

    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.css') }}">
    <script src="{{ asset('vendor/flatpickr/4.6.13/flatpickr.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof flatpickr === 'function') {
                flatpickr('#movement_date', {
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                });
            }

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

            const currentUserSelect = document.getElementById('current_user_id');
            const currentUserSearch = document.getElementById('current_user_search');
            const currentUserSuggestions = document.getElementById('current_user_suggestions');

            if (currentUserSelect && currentUserSearch && currentUserSuggestions) {
                const hideSuggestions = function() {
                    currentUserSuggestions.hidden = true;
                    currentUserSuggestions.replaceChildren();
                    currentUserSearch.setAttribute('aria-expanded', 'false');
                };

                const selectUser = function(value) {
                    currentUserSelect.value = value;
                    currentUserSearch.value = currentUserSelect.options[currentUserSelect.selectedIndex]?.textContent.trim() || '';
                    hideSuggestions();
                };

                const showSuggestions = function(keyword) {
                    const query = keyword.trim().toLowerCase();
                    if (!query) {
                        hideSuggestions();
                        return;
                    }

                    const matches = Array.from(currentUserSelect.options)
                        .filter(function(option) {
                            return option.value && option.textContent.toLowerCase().includes(query);
                        })
                        .slice(0, 30);
                    const fragment = document.createDocumentFragment();

                    if (matches.length === 0) {
                        const empty = document.createElement('div');
                        empty.className = 'asset-user-suggestion-empty';
                        empty.textContent = 'Karyawan tidak ditemukan.';
                        fragment.appendChild(empty);
                    } else {
                        matches.forEach(function(option) {
                            const button = document.createElement('button');
                            button.type = 'button';
                            button.className = 'asset-user-suggestion';
                            button.setAttribute('role', 'option');
                            button.textContent = option.textContent.trim();
                            button.addEventListener('click', function() {
                                selectUser(option.value);
                            });
                            fragment.appendChild(button);
                        });
                    }

                    currentUserSuggestions.replaceChildren(fragment);
                    currentUserSuggestions.hidden = false;
                    currentUserSearch.setAttribute('aria-expanded', 'true');
                };

                currentUserSearch.addEventListener('input', function() {
                    currentUserSelect.value = '';
                    showSuggestions(currentUserSearch.value);
                });

                currentUserSearch.addEventListener('focus', function() {
                    showSuggestions(currentUserSearch.value);
                });

                currentUserSearch.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape') hideSuggestions();
                });

                document.addEventListener('click', function(event) {
                    if (!event.target.closest('.asset-user-picker')) hideSuggestions();
                });
            }
        });
    </script>

    <style>
        /* === HEADER (selaras dengan /hr/leave/master/create & /hr/locations/create) === */
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0;
        }
        .section-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .section-icon svg {
            width: 16px;
            height: 16px;
        }
        .section-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-primary, #111827);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted, #6B7280);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy {
            background: rgba(10, 61, 98, 0.08);
            color: var(--primary-dark, #0A3D62);
        }

        /* === BACK BUTTON === */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 12px 0 10px;
            background: var(--white, #fff);
            border: 1px solid var(--border, #E5E7EB);
            border-radius: 10px;
            color: var(--text-muted, #6B7280);
            text-decoration: none;
            transition: all 0.15s ease;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            margin-bottom: 16px;
        }
        .back-btn:hover {
            border-color: var(--primary, #145DA0);
            color: var(--primary, #145DA0);
            background: var(--gray-50, #F5F7FA);
        }
        .back-btn:hover svg {
            transform: translateX(-2px);
        }
        .back-btn svg {
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }
        .back-btn-text {
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
        }

        /* === CONTAINER === */
        .mlc-container {
            max-width: 100%;
            margin: 0 auto;
            padding-bottom: 120px;
        }

        /* === ALERT === */
        .mlc-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 0.8125rem;
            font-weight: 500;
            line-height: 1.5;
        }
        .mlc-alert svg { flex-shrink: 0; margin-top: 1px; }
        .mlc-alert--error {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
        }
        .mlc-alert--success {
            background: #F0FDF4;
            border: 1px solid #BBF7D0;
            color: #15803D;
        }

        /* === INFO BANNER === */
        .mlc-banner {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            background: #EFF6FF;
            border: 1px solid #DBEAFE;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 0.8125rem;
            color: #1E40AF;
            line-height: 1.5;
        }
        .mlc-banner svg { flex-shrink: 0; margin-top: 1px; }

        /* === STEP CARD === */
        .mlc-step {
            background: var(--white, #fff);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 12px;
            border: 1px solid var(--border-light, #F3F4F6);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .mlc-step__header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }
        .mlc-step__num {
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-dark, #0A3D62);
            color: #fff;
            border-radius: 50%;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .mlc-step__headtext {
            display: flex;
            flex-direction: column;
            gap: 1px;
            min-width: 0;
        }
        .mlc-step__title {
            margin: 0;
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            line-height: 1.3;
        }
        .mlc-step__sub {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-muted, #6B7280);
            line-height: 1.35;
        }

        /* === GRID & FIELDS === */
        .mlc-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        .mlc-field { margin: 0; }
        .mlc-field--full { grid-column: 1 / -1; }
        .mlc-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin-bottom: 8px;
        }
        .mlc-required { color: var(--error, #EF4444); font-weight: 700; }

        /* === INPUT / SELECT / TEXTAREA === */
        .mlc-input,
        .mlc-select,
        .mlc-textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--border, #E5E7EB);
            border-radius: 10px;
            font-size: 0.9375rem;
            color: var(--text-primary, #111827);
            background: var(--white, #fff);
            transition: all 0.2s ease;
            outline: none;
            font-family: inherit;
            box-sizing: border-box;
        }
        .mlc-input::placeholder,
        .mlc-textarea::placeholder { color: var(--text-light, #9CA3AF); }
        .mlc-input:focus,
        .mlc-select:focus,
        .mlc-textarea:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .mlc-input--invalid,
        .mlc-input--invalid:focus {
            border-color: #EF4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.08);
        }
        .mlc-select-wrap { position: relative; }
        .mlc-select {
            padding-right: 40px;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
        }
        .mlc-select__arrow {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light, #9CA3AF);
            pointer-events: none;
        }
        .mlc-helper {
            margin: 6px 0 0;
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
            line-height: 1.4;
        }
        .asset-user-picker { position: relative; }
        .asset-user-select-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }
        .asset-user-suggestions {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            z-index: 100;
            max-height: 280px;
            overflow-y: auto;
            padding: 6px;
            background: var(--white, #fff);
            border: 1.5px solid var(--border, #E5E7EB);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .asset-user-suggestion {
            display: block;
            width: 100%;
            padding: 11px 12px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: var(--text-primary, #111827);
            cursor: pointer;
            font: inherit;
            font-size: 0.9375rem;
            text-align: left;
        }
        .asset-user-suggestion:hover,
        .asset-user-suggestion:focus {
            background: var(--gray-50, #F5F7FA);
            color: var(--primary, #145DA0);
            outline: none;
        }
        .asset-user-suggestion-empty {
            padding: 12px;
            color: var(--text-muted, #6B7280);
            font-size: 0.875rem;
            text-align: center;
        }
        .mlc-textarea {
            resize: vertical;
            min-height: 90px;
            line-height: 1.5;
        }

        /* === UPLOAD DROPZONE === */
        .asset-upload {
            position: relative;
            border: 2px dashed var(--border, #E5E7EB);
            border-radius: 12px;
            padding: 18px 16px;
            text-align: center;
            background: var(--gray-50, #F5F7FA);
            cursor: pointer;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        .asset-upload:hover,
        .asset-upload:focus-within {
            border-color: var(--primary, #145DA0);
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
            gap: 4px;
            color: var(--text-muted, #6B7280);
            pointer-events: none;
        }
        .asset-upload__title {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-secondary, #374151);
        }
        .asset-upload__desc {
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
            font-weight: 500;
            word-break: break-word;
            line-height: 1.4;
        }

        /* === FIXED ACTION BAR (selaras dengan /hr/leave-requests/show) === */
        .manual-form-shell { display: block; }
        .apv-action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
            background: transparent;
            border: none;
            box-shadow: none;
            padding: 0;
            pointer-events: none;
            transition: left 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        right 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        bottom 0.35s ease;
        }
        .apv-action-dock {
            background: var(--white, #FFFFFF);
            border-top: 1px solid var(--border-light, #E5E7EB);
            border-radius: 18px 18px 0 0;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.06);
            padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
            pointer-events: auto;
        }
        .apv-action-main {
            display: flex;
            flex-direction: column-reverse;
            gap: 8px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .apv-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 11px 18px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
            font-family: inherit;
            min-height: 44px;
        }
        .apv-action-btn svg { flex-shrink: 0; }
        .apv-action-btn--primary {
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: var(--white, #FFFFFF);
            box-shadow: 0 2px 8px rgba(10, 61, 98, 0.2);
        }
        .apv-action-btn--primary:hover {
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.3);
            transform: translateY(-1px);
        }
        .apv-action-btn--secondary {
            background: var(--gray-50, #F5F7FA);
            color: var(--text-secondary, #6B7280);
            border: 1.5px solid var(--border-light, #E5E7EB);
        }
        .apv-action-btn--secondary:hover {
            background: var(--gray-100, #E5E7EB);
        }

        /* === RESPONSIVE === */
        @media (min-width: 640px) {
            .apv-action-main {
                flex-direction: row;
                flex-wrap: wrap;
                align-items: center;
                gap: 12px;
                width: 100%;
            }
            .apv-action-btn {
                width: auto;
                flex: 1 1 0;
                min-width: 0;
                padding: 12px 24px;
                font-size: 14px;
            }
        }
        @media (min-width: 1025px) {
            .apv-action-bar {
                left: calc(var(--sidebar-width) + 12px);
                right: 0;
                bottom: 16px;
                padding: 0 16px;
            }
            .app.sidebar-collapsed .apv-action-bar {
                left: 0;
            }
            .apv-action-dock {
                border: 1px solid var(--border-light, #E5E7EB);
                border-radius: 18px;
                box-shadow: 0 -4px 20px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
                padding: 10px;
                max-width: 1200px;
                margin: 0 auto;
            }
        }
        @media (max-width: 639px) {
            .mlc-step {
                padding: 16px;
                margin-bottom: 10px;
                border-radius: 14px;
            }
            .mlc-grid-2 {
                grid-template-columns: 1fr;
            }
            .back-btn {
                height: 40px;
                padding: 0 14px 0 12px;
            }
            .back-btn-text { font-size: 0.8125rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .back-btn,
            .mlc-input,
            .mlc-select,
            .mlc-textarea,
            .asset-upload,
            .apv-action-btn { transition-duration: 0.01ms; }
            .apv-action-btn--primary:hover { transform: none; }
        }
    </style>
</x-app>
