<x-app title="Tambah Lokasi Presensi">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Tambah Lokasi Presensi</h1>
                <p class="section-subtitle">Tentukan titik koordinat dan radius untuk lokasi absensi karyawan.</p>
            </div>
        </div>
    </x-slot>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <div class="loc-container">

        @if ($errors->any())
            <div class="loc-alert loc-alert--error">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="loc-cta-bar">
            <a href="{{ route('hr.locations.index') }}" class="loc-btn-back">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
        </div>

        <div class="loc-card">
            <form method="POST" action="{{ route('hr.locations.store') }}" class="loc-form-content">
                @csrf

                <div class="loc-form-group">
                    <label for="name">Nama Lokasi <span class="loc-req">*</span></label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="loc-form-control"
                        value="{{ old('name') }}"
                        placeholder="Contoh: Kantor Pusat, Cabang Surabaya, Gudang A"
                        required>
                </div>

                <div class="loc-form-group">
                    <label for="address-input">Alamat (Opsional)</label>
                    <textarea
                        name="address"
                        id="address-input"
                        rows="2"
                        class="loc-form-control"
                        placeholder="Masukkan nama jalan atau gedung untuk pencarian otomatis..."
                    >{{ old('address') }}</textarea>

                    <div class="loc-map-tools">
                        <button type="button" id="geocode-btn" class="loc-btn-tool loc-btn-tool--primary">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M21 21l-4.35-4.35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Cari di Peta
                        </button>
                        <button type="button" id="use-current-location-btn" class="loc-btn-tool loc-btn-tool--secondary">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 11l19-9-9 19-2-8-8-2z"/>
                            </svg>
                            Gunakan Lokasi Saya
                        </button>
                    </div>
                </div>

                <div class="loc-coordinate-grid">
                    <div class="loc-form-group">
                        <label for="lat-input">Latitude <span class="loc-req">*</span></label>
                        <input
                            type="text"
                            name="latitude"
                            id="lat-input"
                            class="loc-form-control"
                            value="{{ old('latitude') }}"
                            required>
                    </div>
                    <div class="loc-form-group">
                        <label for="lng-input">Longitude <span class="loc-req">*</span></label>
                        <input
                            type="text"
                            name="longitude"
                            id="lng-input"
                            class="loc-form-control"
                            value="{{ old('longitude') }}"
                            required>
                    </div>
                    <div class="loc-form-group">
                        <label for="radius-input">Radius (Meter) <span class="loc-req">*</span></label>
                        <input
                            type="number"
                            name="radius_meters"
                            id="radius-input"
                            class="loc-form-control"
                            value="{{ old('radius_meters', 30) }}"
                            min="5"
                            required>
                    </div>
                </div>

                <div class="loc-form-group">
                    <label>Preview Map</label>
                    <div id="map" class="loc-map-container"></div>
                    <small class="loc-helper-text">Geser marker merah untuk menyesuaikan titik lokasi presisi.</small>
                </div>

                <div class="loc-form-group">
                    <label class="loc-checkbox-wrapper">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span class="loc-checkbox-label">Lokasi Aktif</span>
                    </label>
                </div>

                <div class="loc-form-actions">
                    <button type="submit" class="loc-btn-primary">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan Lokasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const latInput = document.getElementById('lat-input');
            const lngInput = document.getElementById('lng-input');
            const radiusInput = document.getElementById('radius-input');
            const addressInput = document.getElementById('address-input');
            const geocodeBtn = document.getElementById('geocode-btn');
            const useCurrentLocationBtn = document.getElementById('use-current-location-btn');

            // Default location (bisa diset ke kantor pusat default jika kosong)
            const initialLat = parseFloat(latInput.value) || -6.200000;
            const initialLng = parseFloat(lngInput.value) || 106.816666;
            const initialRadius = parseInt(radiusInput.value) || 30;

            // Inisialisasi Map
            const map = L.map('map').setView([initialLat, initialLng], 18);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            // Marker Draggable
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

            // Event Listener Marker
            marker.on('dragend', function(e) {
                const pos = e.target.getLatLng();
                updatePosition(pos.lat, pos.lng);
            });

            // Event Listener Radius
            radiusInput.addEventListener('input', function() {
                const r = parseInt(this.value) || 10;
                circle.setRadius(r);
            });

            // Event Listener Geocode (Cari Alamat)
            geocodeBtn.addEventListener('click', async function() {
                const q = addressInput.value.trim();
                if (!q) return;

                const originalText = geocodeBtn.innerHTML;
                geocodeBtn.disabled = true;
                geocodeBtn.innerHTML = 'Mencari...';

                try {
                    const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q);
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();

                    if (!data || !data.length) {
                        window.showToast('Alamat tidak ditemukan. Mohon perjelas nama jalan/kota.', 'warning');
                    } else {
                        const lat = parseFloat(data[0].lat);
                        const lng = parseFloat(data[0].lon);
                        if (!isNaN(lat) && !isNaN(lng)) {
                            updatePosition(lat, lng);
                        }
                    }
                } catch (e) {
                    window.showToast('Gagal menghubungi layanan peta.', 'error');
                } finally {
                    geocodeBtn.disabled = false;
                    geocodeBtn.innerHTML = originalText;
                }
            });

            // Event Listener Current Location
            useCurrentLocationBtn.addEventListener('click', function() {
                if (!navigator.geolocation) {
                    window.showToast('Browser Anda tidak mendukung geolokasi.', 'warning');
                    return;
                }

                const originalText = useCurrentLocationBtn.innerHTML;
                useCurrentLocationBtn.disabled = true;
                useCurrentLocationBtn.innerHTML = 'Mendeteksi...';

                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        updatePosition(pos.coords.latitude, pos.coords.longitude);
                        useCurrentLocationBtn.disabled = false;
                        useCurrentLocationBtn.innerHTML = originalText;
                    },
                    function(err) {
                        console.error(err);
                        window.showToast('Gagal mengambil lokasi GPS. Pastikan izin lokasi aktif.', 'error');
                        useCurrentLocationBtn.disabled = false;
                        useCurrentLocationBtn.innerHTML = originalText;
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            });

            // Trigger update posisi awal agar input terisi
            if(!latInput.value) updatePosition(initialLat, initialLng);
        });
    </script>
    @endpush

    <style>
        /* --- SECTION HEADER (x-slot) --- */
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
            color: var(--text-primary);
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        .section-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-muted);
            font-weight: 500;
            line-height: 1.35;
        }
        .icon-navy  { background: rgba(10, 61, 98, 0.08);  color: var(--primary-dark); }

        /* --- CONTAINER --- */
        .loc-container {
            max-width: 800px;
            margin: 0 auto;
            padding-bottom: 40px;
        }

        /* --- ALERT --- */
        .loc-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        .loc-alert--error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        /* --- CARD --- */
        .loc-card {
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            overflow: hidden;
        }

        .loc-cta-bar {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 16px;
        }

        /* --- FORM --- */
        .loc-form-content { padding: 24px; }
        .loc-form-group { margin-bottom: 20px; display: flex; flex-direction: column; gap: 6px; }
        .loc-form-group label { font-size: 13.5px; font-weight: 600; color: var(--text-secondary); }
        .loc-req { color: var(--error); }

        /* --- INPUTS --- */
        .loc-form-control {
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            width: 100%;
            outline: none;
            background: var(--white);
            color: var(--text-primary);
            transition: all 0.2s ease;
            font-family: inherit;
        }
        .loc-form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(20, 93, 160, 0.1);
        }
        .loc-form-control[readonly] { background-color: var(--gray-100); cursor: default; }

        /* --- MAP TOOLS --- */
        .loc-map-tools { display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap; }
        .loc-btn-tool {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid transparent;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        .loc-btn-tool--primary {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
        }
        .loc-btn-tool--primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .loc-btn-tool--secondary {
            background: var(--white);
            color: var(--text-secondary);
            border-color: var(--border);
        }
        .loc-btn-tool--secondary:hover {
            background: var(--gray-50);
            border-color: var(--border);
            color: var(--primary);
        }

        /* --- COORDINATE GRID --- */
        .loc-coordinate-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        /* --- MAP --- */
        .loc-map-container {
            width: 100%;
            height: 260px;
            border-radius: 12px;
            border: 1.5px solid var(--border);
            z-index: 1;
        }
        .loc-helper-text { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

        /* --- CHECKBOX --- */
        .loc-checkbox-wrapper { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .loc-checkbox-wrapper input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--primary); cursor: pointer; }
        .loc-checkbox-label { font-size: 14px; color: var(--text-secondary); font-weight: 500; }

        /* --- BACK BUTTON --- */
        .loc-btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 18px;
            border-radius: 12px;
            border: 1.5px solid var(--border);
            background: var(--white);
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .loc-btn-back:hover {
            background: var(--gray-50);
            border-color: var(--border);
            color: var(--primary);
        }

        /* --- FORM ACTIONS --- */
        .loc-form-actions { margin-top: 10px; display: flex; justify-content: flex-end; }
        .loc-btn-primary {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 160px;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
            font-family: inherit;
        }
        .loc-btn-primary:hover {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .loc-btn-primary svg {
            flex-shrink: 0;
        }

        /* --- RESPONSIVE --- */
        @media (min-width: 480px) {
            .loc-coordinate-grid {
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }
            .loc-map-container {
                height: 320px;
            }
        }

        @media (min-width: 768px) {
            .loc-coordinate-grid {
                grid-template-columns: 1fr 1fr 1fr;
                gap: 16px;
            }
        }
    </style>
</x-app>
