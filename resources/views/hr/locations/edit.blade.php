<x-app title="Edit Lokasi Presensi">

    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Edit Lokasi Presensi</h1>
                <p class="section-subtitle">Perbarui nama, alamat, atau titik koordinat lokasi presensi</p>
            </div>
        </div>
    </x-slot>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <div class="apv-detail-page">

        {{-- Back Button --}}
        <a href="{{ route('hr.locations.index') }}" class="back-btn" aria-label="Kembali ke Master Lokasi Presensi">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="back-btn-text">Kembali</span>
        </a>

        {{-- Flash / Error Messages --}}
        @if (session('success'))
            <div class="apv-alert apv-alert--success">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="apv-alert apv-alert--error" role="alert">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                    <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form id="location-edit-form" method="POST" action="{{ route('hr.locations.update', $location->id) }}" class="apv-detail-body">
            @csrf
            @method('PUT')

            {{-- Summary Header Card (selaras dengan /hr/leave-requests/show & /hr/divisions/edit) --}}
            <div class="apv-card">
                <div class="apv-card-header">
                    <div class="apv-avatar apv-avatar--lg">{{ strtoupper(substr($location->name, 0, 1)) }}</div>
                    <div class="apv-card-header-info">
                        <span class="apv-entity-label">Lokasi Presensi</span>
                        <h2 class="apv-employee-name">{{ $location->name }}</h2>
                        <div class="apv-employee-meta">
                            <span class="apv-role-chip">RADIUS {{ $location->radius_meters }}M</span>
                            <span class="apv-meta-dot">•</span>
                            <span>{{ $location->latitude }}, {{ $location->longitude }}</span>
                        </div>
                    </div>
                </div>
                <div class="apv-card-header-footer">
                    @if($location->is_active)
                        <span class="apv-badge apv-badge--success">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            Lokasi Aktif
                        </span>
                    @else
                        <span class="apv-badge apv-badge--gray">
                            Lokasi Nonaktif
                        </span>
                    @endif
                    <span class="apv-submit-time">ID Lokasi #{{ $location->id }}</span>
                </div>
            </div>

            {{-- Card 1: Identitas & Status Lokasi --}}
            <div class="apv-card">
                <div class="apv-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Identitas &amp; Status Lokasi
                </div>

                <div class="edit-fields">
                    <div class="edit-form-group">
                        <label for="name" class="edit-form-label">
                            Nama Lokasi <span class="req">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="edit-form-input"
                            value="{{ old('name', $location->name) }}"
                            placeholder="Contoh: Kantor Pusat, Cabang Surabaya, Gudang A"
                            required>
                        <small class="form-helper">Gunakan nama yang mudah dikenali saat mengatur jadwal kerja dan lokasi presensi karyawan.</small>
                    </div>

                    <div class="edit-form-group">
                        <label for="address-input" class="edit-form-label">
                            Alamat (Opsional)
                        </label>
                        <textarea
                            name="address"
                            id="address-input"
                            rows="2"
                            class="edit-form-textarea"
                            placeholder="Catatan alamat, gedung, atau petunjuk lokasi..."
                        >{{ old('address', $location->address) }}</textarea>
                    </div>

                    <div class="edit-form-group">
                        <span class="edit-form-label">Status Lokasi</span>
                        <label class="edit-checkbox-card">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                @checked(old('is_active', $location->is_active))>
                            <span class="edit-checkbox-card__content">
                                <span class="edit-checkbox-card__title">Lokasi Aktif</span>
                                <span class="edit-checkbox-card__desc">Lokasi yang aktif dapat dipilih pada pengaturan jadwal kerja dan validasi presensi karyawan.</span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Card 2: Titik Koordinat & Radius --}}
            <div class="apv-card">
                <div class="apv-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Titik Koordinat &amp; Radius
                </div>

                <div class="edit-fields">
                    <div class="coordinate-grid">
                        <div class="edit-form-group">
                            <label for="lat-input" class="edit-form-label">
                                Latitude <span class="req">*</span>
                            </label>
                            <input
                                type="text"
                                name="latitude"
                                id="lat-input"
                                class="edit-form-input"
                                value="{{ old('latitude', $location->latitude) }}"
                                required>
                        </div>
                        <div class="edit-form-group">
                            <label for="lng-input" class="edit-form-label">
                                Longitude <span class="req">*</span>
                            </label>
                            <input
                                type="text"
                                name="longitude"
                                id="lng-input"
                                class="edit-form-input"
                                value="{{ old('longitude', $location->longitude) }}"
                                required>
                        </div>
                        <div class="edit-form-group">
                            <label for="radius-input" class="edit-form-label">
                                Radius (Meter) <span class="req">*</span>
                            </label>
                            <input
                                type="number"
                                name="radius_meters"
                                id="radius-input"
                                class="edit-form-input"
                                value="{{ old('radius_meters', $location->radius_meters) }}"
                                min="5"
                                required>
                        </div>
                    </div>

                    <div class="edit-form-group">
                        <span class="edit-form-label">Preview Map</span>
                        <div id="map" class="map-container"></div>
                        <small class="form-helper">Geser marker merah untuk mengubah titik lokasi.</small>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- FIXED ACTION BAR (selaras dengan /hr/leave-requests/show & /hr/divisions/edit) --}}
    <div class="apv-action-bar" aria-label="Aksi edit lokasi presensi">
        <div class="apv-action-dock">
            <div class="apv-action-main">
                <a href="{{ route('hr.locations.index') }}" class="apv-action-btn apv-action-btn--secondary">
                    Batal
                </a>
                <button type="submit" form="location-edit-form" class="apv-action-btn apv-action-btn--primary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const latInput = document.getElementById('lat-input');
            const lngInput = document.getElementById('lng-input');
            const radiusInput = document.getElementById('radius-input');

            // Ambil nilai awal dari database/input
            const initialLat = parseFloat(latInput.value) || -6.200000;
            const initialLng = parseFloat(lngInput.value) || 106.816666;
            const initialRadius = parseInt(radiusInput.value) || 30;

            // Inisialisasi Map
            const map = L.map('map').setView([initialLat, initialLng], 18);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            // Marker
            const marker = L.marker([initialLat, initialLng], {
                draggable: true
            }).addTo(map);

            // Circle Radius
            let circle = L.circle([initialLat, initialLng], {
                radius: initialRadius,
                color: '#2563eb',
                fillColor: '#3b82f6',
                fillOpacity: 0.2
            }).addTo(map);

            // Fungsi Update Input saat marker digeser
            function updatePosition(lat, lng) {
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
                marker.setLatLng([lat, lng]);
                circle.setLatLng([lat, lng]);
                map.setView([lat, lng], 18);
            }

            // Event Listener Marker Drag
            marker.on('dragend', function(e) {
                const pos = e.target.getLatLng();
                updatePosition(pos.lat, pos.lng);
            });

            // Event Listener Radius Change
            radiusInput.addEventListener('input', function() {
                const r = parseInt(this.value) || 10;
                circle.setRadius(r);
            });

            function syncMapFromCoordinates() {
                const latValue = latInput.value.trim();
                const lngValue = lngInput.value.trim();

                if (!latValue || !lngValue) {
                    return;
                }

                const lat = Number(latValue);
                const lng = Number(lngValue);

                if (!Number.isFinite(lat) || !Number.isFinite(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) {
                    return;
                }

                marker.setLatLng([lat, lng]);
                circle.setLatLng([lat, lng]);
                map.setView([lat, lng], 18);
            }

            latInput.addEventListener('input', syncMapFromCoordinates);
            lngInput.addEventListener('input', syncMapFromCoordinates);
        });
    </script>
    @endpush

    <style>
        /* ========================================== */
        /* HEADER (x-slot)                            */
        /* ========================================== */
        .section-header-inline {
            display: flex;
            align-items: center;
            gap: 10px;
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

        /* ========================================== */
        /* PAGE & BACK BUTTON                         */
        /* ========================================== */
        .apv-detail-page {
            max-width: 100%;
            margin: 0 auto;
            padding-bottom: 120px;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 12px 0 10px;
            background: var(--white, #FFFFFF);
            border: 1px solid var(--border-light, #E5E7EB);
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
        .back-btn svg {
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }
        .back-btn:hover svg {
            transform: translateX(-2px);
        }
        .back-btn-text {
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
        }

        /* ========================================== */
        /* ALERTS                                     */
        /* ========================================== */
        .apv-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 500;
        }
        .apv-alert svg { flex-shrink: 0; }
        .apv-alert--success {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.2);
            color: #15803d;
        }
        .apv-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        /* ========================================== */
        /* CARDS (selaras dengan /hr/leave-requests/show) */
        /* ========================================== */
        .apv-detail-body {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .apv-card {
            background: var(--white, #FFFFFF);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid var(--border-light, #E5E7EB);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .apv-card-header {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .apv-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }
        .apv-avatar--lg {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            font-size: 20px;
        }
        .apv-card-header-info {
            flex: 1;
            min-width: 0;
        }
        .apv-entity-label {
            display: block;
            color: var(--text-muted, #6B7280);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .apv-employee-name {
            margin: 0 0 4px 0;
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            line-height: 1.25;
        }
        .apv-employee-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            font-size: 12px;
            color: var(--text-muted, #6B7280);
            font-weight: 500;
        }
        .apv-role-chip {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 6px;
            background: rgba(20, 93, 160, 0.08);
            color: var(--primary, #145DA0);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .apv-meta-dot {
            color: var(--text-light, #9CA3AF);
        }
        .apv-card-header-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid var(--border-light, #E5E7EB);
        }
        .apv-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
        }
        .apv-badge--success {
            background: rgba(34, 197, 94, 0.1);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }
        .apv-badge--gray {
            background: var(--gray-100, #F3F4F6);
            color: var(--text-muted, #6B7280);
            border: 1px solid var(--border-light, #E5E7EB);
        }
        .apv-submit-time {
            font-size: 12px;
            color: var(--text-muted, #6B7280);
            font-weight: 500;
        }

        /* ========================================== */
        /* SECTION TITLE & FORM FIELDS                */
        /* ========================================== */
        .apv-section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--text-primary, #111827);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-light, #E5E7EB);
        }
        .apv-section-title svg {
            color: var(--primary, #145DA0);
            flex-shrink: 0;
        }
        .edit-fields {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .edit-form-group {
            margin: 0;
        }
        .edit-form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary, #374151);
            margin-bottom: 7px;
        }
        .req {
            color: var(--error, #EF4444);
            font-weight: 700;
        }
        .edit-form-input,
        .edit-form-textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            font-size: 14px;
            color: var(--text-primary, #111827);
            background: var(--white, #FFFFFF);
            transition: all 0.2s ease;
            font-family: inherit;
            box-sizing: border-box;
            outline: none;
        }
        .edit-form-input {
            min-height: 44px;
        }
        .edit-form-textarea {
            resize: vertical;
            min-height: 84px;
            line-height: 1.5;
        }
        .edit-form-input:focus,
        .edit-form-textarea:focus {
            border-color: var(--primary, #145DA0);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .form-helper {
            display: block;
            font-size: 12px;
            color: var(--text-muted, #6B7280);
            margin-top: 6px;
            line-height: 1.45;
        }

        /* ========================================== */
        /* COORDINATE GRID & MAP                      */
        /* ========================================== */
        .coordinate-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }
        .map-container {
            width: 100%;
            height: 320px;
            border-radius: 12px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            z-index: 1;
        }

        /* ========================================== */
        /* CHECKBOX CARD                              */
        /* ========================================== */
        .edit-checkbox-card {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border: 1.5px solid var(--border-light, #E5E7EB);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.15s ease;
            background: var(--white, #FFFFFF);
            box-sizing: border-box;
        }
        .edit-checkbox-card:hover {
            border-color: var(--primary, #145DA0);
            background: var(--gray-50, #F5F7FA);
        }
        .edit-checkbox-card:has(input:checked) {
            border-color: var(--primary, #145DA0);
            background: rgba(20, 93, 160, 0.06);
        }
        .edit-checkbox-card input[type="checkbox"] {
            accent-color: var(--primary, #145DA0);
            width: 17px;
            height: 17px;
            margin: 1px 0 0;
            flex-shrink: 0;
            cursor: pointer;
        }
        .edit-checkbox-card__content {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }
        .edit-checkbox-card__title {
            font-size: 13px;
            color: var(--text-primary, #111827);
            font-weight: 600;
            line-height: 1.35;
        }
        .edit-checkbox-card__desc {
            font-size: 12px;
            color: var(--text-muted, #6B7280);
            font-weight: 500;
            line-height: 1.45;
        }

        /* ========================================== */
        /* FIXED ACTION BAR (selaras dengan /hr/leave-requests/show) */
        /* ========================================== */
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

        /* ========================================== */
        /* RESPONSIVE                                 */
        /* ========================================== */
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
        @media (min-width: 768px) {
            .apv-card { padding: 24px; }
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
        @media (max-width: 767px) {
            .coordinate-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 639px) {
            .coordinate-grid { grid-template-columns: 1fr; gap: 10px; }
            .map-container { height: 260px; }
        }
        @media (max-width: 420px) {
            .apv-card-header { align-items: flex-start; }
            .apv-avatar--lg { width: 44px; height: 44px; border-radius: 12px; font-size: 17px; }
            .apv-employee-name { font-size: 1rem; }
        }
    </style>
</x-app>
