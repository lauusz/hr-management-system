<x-app title="Edit Lokasi Presensi">
    <div class="card" style="max-width:720px;margin:0 auto;">
        <form method="POST" action="{{ route('hr.locations.update', $location->id) }}">
            @csrf
            @method('PUT')

            <div style="margin-bottom:10px;">
                <label><b>Nama Lokasi</b></label>
                <input type="text" name="name" value="{{ old('name', $location->name) }}" required
                    style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;">
            </div>

            <div style="margin-bottom:10px;">
                <label><b>Alamat (opsional)</b></label>
                <div style="display:flex;gap:8px;align-items:flex-start;">
                    <textarea name="address" id="address-input" rows="2"
                        style="flex:1;width:100%;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;">{{ old('address', $location->address) }}</textarea>
                    <button type="button"
                        id="geocode-btn"
                        style="padding:8px 12px;border-radius:8px;border:1px solid #1e4a8d;background:#1e4a8d;color:#fff;cursor:pointer;white-space:nowrap;">
                        Cari di Peta
                    </button>
                </div>
                <div style="font-size:0.8rem;opacity:.7;margin-top:4px;">
                    Ubah alamat lalu klik "Cari di Peta" agar peta dan koordinat mengikuti.
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:10px;">
                <div>
                    <label><b>Latitude</b></label>
                    <input type="text" name="latitude" id="lat-input"
                        value="{{ old('latitude', $location->latitude) }}" required
                        style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;">
                </div>
                <div>
                    <label><b>Longitude</b></label>
                    <input type="text" name="longitude" id="lng-input"
                        value="{{ old('longitude', $location->longitude) }}" required
                        style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;">
                </div>
                <div>
                    <label><b>Radius (meter)</b></label>
                    <input type="number" name="radius_meters" id="radius-input"
                        value="{{ old('radius_meters', $location->radius_meters) }}" min="5" required
                        style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #e5e7eb;">
                </div>
            </div>

            <div style="margin-bottom:10px;">
                <label style="display:flex;align-items:center;gap:6px;font-size:0.9rem;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $location->is_active) ? 'checked' : '' }}>
                    Lokasi aktif
                </label>
            </div>

            <div style="margin-bottom:10px;">
                <label><b>Preview Map</b></label>
                <div id="map" style="width:100%;height:260px;border-radius:10px;border:1px solid #e5e7eb;margin-top:6px;"></div>
                <div style="font-size:0.8rem;opacity:.7;margin-top:4px;">
                    Marker dapat digeser untuk mengubah titik lokasi.
                </div>
            </div>

            <div style="margin-top:8px;display:flex;gap:10px;flex-wrap:wrap;">
                <button type="submit"
                    style="padding:8px 16px;border-radius:999px;border:none;background:#1e4a8d;color:white;font-size:.9rem;cursor:pointer;">
                    Update Lokasi
                </button>
                <a href="{{ route('hr.locations.index') }}"
                    style="padding:8px 16px;border-radius:999px;border:1px solid #d1d5db;font-size:.9rem;text-decoration:none;color:#111827;display:flex;align-items:center;">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    @push('scripts')
    <script>
        const latInput = document.getElementById('lat-input');
        const lngInput = document.getElementById('lng-input');
        const radiusInput = document.getElementById('radius-input');
        const addressInput = document.getElementById('address-input');
        const geocodeBtn = document.getElementById('geocode-btn');

        const initialLat = parseFloat(latInput.value) || -7.22020;
        const initialLng = parseFloat(lngInput.value) || 112.72942;
        const initialRadius = parseInt(radiusInput.value) || 30;

        const map = L.map('map').setView([initialLat, initialLng], 18);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const marker = L.marker([initialLat, initialLng], {
            draggable: true
        }).addTo(map);
        let circle = L.circle([initialLat, initialLng], {
            radius: initialRadius,
            color: '#2563eb',
            fillColor: '#3b82f6',
            fillOpacity: 0.2
        }).addTo(map);

        marker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            latInput.value = pos.lat.toFixed(6);
            lngInput.value = pos.lng.toFixed(6);
            circle.setLatLng([pos.lat, pos.lng]);
        });

        radiusInput.addEventListener('input', function() {
            const r = parseInt(this.value) || 10;
            circle.setRadius(r);
        });

        async function geocodeAddress() {
            const q = addressInput.value.trim();
            if (!q) return;

            geocodeBtn.disabled = true;
            const originalText = geocodeBtn.textContent;
            geocodeBtn.textContent = 'Mencari...';

            try {
                const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q);
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();

                if (!data || !data.length) {
                    alert('Alamat tidak ditemukan. Coba perjelas alamatnya.');
                } else {
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);

                    latInput.value = lat.toFixed(6);
                    lngInput.value = lng.toFixed(6);

                    marker.setLatLng([lat, lng]);
                    circle.setLatLng([lat, lng]);
                    map.setView([lat, lng], 18);
                }
            } catch (e) {
                alert('Terjadi kesalahan saat mencari alamat.');
            } finally {
                geocodeBtn.disabled = false;
                geocodeBtn.textContent = originalText;
            }
        }

        geocodeBtn.addEventListener('click', function() {
            geocodeAddress();
        });
    </script>
    @endpush
</x-app>