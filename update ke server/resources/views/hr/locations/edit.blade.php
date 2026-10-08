<x-app title="Edit Lokasi Presensi">

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <div class="main-container">

        @if ($errors->any())
            <div class="alert-error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="form-title">Edit Lokasi Presensi</h2>
                    <p class="form-subtitle">Perbarui nama, alamat, atau titik koordinat lokasi.</p>
                </div>
                <a href="{{ route('hr.locations.index') }}" class="btn-back">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                    Kembali
                </a>
            </div>

            <div class="divider"></div>

            <form method="POST" action="{{ route('hr.locations.update', $location->id) }}" class="form-content">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Nama Lokasi <span class="req">*</span></label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control"
                        value="{{ old('name', $location->name) }}"
                        required>
                </div>

                <div class="form-group">
                    <label for="address-input">Alamat (Opsional)</label>
                    <textarea
                        name="address"
                        id="address-input"
                        rows="2"
                        class="form-control"
                        placeholder="Catatan alamat, gedung, atau petunjuk lokasi..."
                    >{{ old('address', $location->address) }}</textarea>
                </div>

                <div class="coordinate-grid">
                    <div class="form-group">
                        <label for="lat-input">Latitude <span class="req">*</span></label>
                        <input
                            type="text"
                            name="latitude"
                            id="lat-input"
                            class="form-control"
                            value="{{ old('latitude', $location->latitude) }}"
                            required
                            >
                    </div>
                    <div class="form-group">
                        <label for="lng-input">Longitude <span class="req">*</span></label>
                        <input
                            type="text"
                            name="longitude"
                            id="lng-input"
                            class="form-control"
                            value="{{ old('longitude', $location->longitude) }}"
                            required
                            >
                    </div>
                    <div class="form-group">
                        <label for="radius-input">Radius (Meter) <span class="req">*</span></label>
                        <input
                            type="number"
                            name="radius_meters"
                            id="radius-input"
                            class="form-control"
                            value="{{ old('radius_meters', $location->radius_meters) }}"
                            min="5"
                            required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Preview Map</label>
                    <div id="map" class="map-container"></div>
                    <small class="helper-text">Geser marker merah untuk mengubah titik lokasi.</small>
                </div>

                <div class="form-group">
                    <label class="checkbox-wrapper">
                        <input 
                            type="checkbox" 
                            name="is_active" 
                            value="1" 
                            {{ old('is_active', $location->is_active) ? 'checked' : '' }}>
                        <span class="checkbox-label">Lokasi Aktif</span>
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        Update Lokasi
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
        /* Container */
        .main-container {
            max-width: 800px;
            margin: 0 auto;
            padding-bottom: 40px;
        }

        /* Alert */
        .alert-error {
            background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
            padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;
            display: flex; align-items: center; gap: 10px; font-size: 14px;
        }

        /* Card */
        .card {
            background: #fff; border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            border: 1px solid #f3f4f6; overflow: hidden;
        }

        .card-header {
            padding: 24px; display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;
        }

        .form-title { margin: 0; font-size: 18px; font-weight: 700; color: #111827; }
        .form-subtitle { margin: 4px 0 0; font-size: 13.5px; color: #6b7280; }
        .divider { height: 1px; background: #f3f4f6; width: 100%; }

        /* Form Layout */
        .form-content { padding: 24px; }
        .form-group { margin-bottom: 20px; display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13.5px; font-weight: 600; color: #374151; }
        .req { color: #dc2626; }

        /* Inputs */
        .form-control {
            padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;
            font-size: 14px; width: 100%; outline: none; background: #fff; color: #111827;
            transition: border-color 0.2s, box-shadow 0.2s; font-family: inherit;
        }
        .form-control:focus { border-color: #1e4a8d; box-shadow: 0 0 0 3px rgba(30, 74, 141, 0.1); }
        .form-control[readonly] { background-color: #f9fafb; cursor: default; }

        /* Grid for Coordinates */
        .coordinate-grid {
            display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;
        }

        /* Map Container */
        .map-container {
            width: 100%; height: 320px; border-radius: 10px; border: 1px solid #d1d5db;
            z-index: 1;
        }
        .helper-text { font-size: 12px; color: #6b7280; margin-top: 4px; }

        /* Checkbox */
        .checkbox-wrapper { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .checkbox-wrapper input[type="checkbox"] { width: 16px; height: 16px; accent-color: #1e4a8d; cursor: pointer; }
        .checkbox-label { font-size: 14px; color: #374151; font-weight: 500; }

        /* Actions */
        .btn-back {
            display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px;
            border-radius: 8px; border: 1px solid #d1d5db; background: #fff; color: #374151;
            font-size: 13px; font-weight: 500; text-decoration: none; transition: all 0.2s; white-space: nowrap;
        }
        .btn-back:hover { background: #f9fafb; border-color: #9ca3af; }

        .form-actions { margin-top: 10px; display: flex; justify-content: flex-end; }
        .btn-primary {
            display: inline-flex; justify-content: center; align-items: center;
            padding: 12px 24px; background: #1e4a8d; color: #fff; border: none; border-radius: 8px;
            font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s; min-width: 140px;
        }
        .btn-primary:hover { background: #163a75; }

        /* Mobile Adjustments */
        @media (max-width: 600px) {
            .card-header { flex-direction: column; gap: 12px; }
            .btn-back { align-self: flex-start; }
            .form-content { padding: 16px; }
            
            /* Stack coordinates on mobile */
            .coordinate-grid { grid-template-columns: 1fr; gap: 10px; }
            
            .map-container { height: 260px; }
            .btn-primary { width: 100%; }
        }
    </style>
</x-app>
