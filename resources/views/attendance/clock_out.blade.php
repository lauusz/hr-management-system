<x-app title="Clock-out">

    <div class="card" style="max-width:480px;margin:0 auto;">
        <h2 style="font-size:1.25rem;font-weight:700;margin-bottom:10px;">Clock-out</h2>
        <p style="font-size:0.9rem;opacity:.8;margin-bottom:12px;">
            Pastikan Anda berada di lokasi kerja saat melakukan clock-out.
        </p>

        <div id="statusBoxOut" style="font-size:0.85rem;margin-bottom:10px;color:#4b5563;">
            Mengambil lokasi...
        </div>

        <div id="locationInfoOut" style="font-size:0.85rem;margin-bottom:12px;color:#4b5563;display:none;">
            <span id="coordsTextOut"></span>
        </div>

        <div id="mapOut" style="width: 100%; height: 180px; border-radius: 8px; margin-top: 10px;"></div>

        <form id="clockOutForm" style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap;">
            @csrf
            <input type="hidden" name="lat" id="latInputOut">
            <input type="hidden" name="lng" id="lngInputOut">

            <button id="btnClockOut" type="button"
                style="flex:1;padding:10px 12px;border:none;border-radius:8px;background:#059669;color:#fff;cursor:pointer;font-size:0.95rem;font-weight:600;">
                ✅ Clock-out Sekarang
            </button>
        </form>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        const mapLat = -7.22020;
        const mapLng = 112.72942;

        const mapOut = L.map('mapOut').setView([mapLat, mapLng], 18);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(mapOut);

        L.marker([mapLat, mapLng]).addTo(mapOut)
            .bindPopup("Lokasi Presensi")
            .openPopup();
    </script>

    <script>
        const latInputOut = document.getElementById('latInputOut');
        const lngInputOut = document.getElementById('lngInputOut');
        const statusBoxOut = document.getElementById('statusBoxOut');
        const locationInfoOut = document.getElementById('locationInfoOut');
        const coordsTextOut = document.getElementById('coordsTextOut');
        const btnClockOut = document.getElementById('btnClockOut');

        let currentLatOut = null;
        let currentLngOut = null;
        let isSubmittingOut = false;

        function initLocationOut() {
            if (!navigator.geolocation) {
                statusBoxOut.textContent = 'Browser tidak mendukung geolokasi.';
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    currentLatOut = pos.coords.latitude;
                    currentLngOut = pos.coords.longitude;

                    latInputOut.value = currentLatOut;
                    lngInputOut.value = currentLngOut;

                    locationInfoOut.style.display = 'block';
                    coordsTextOut.textContent = `Lokasi: ${currentLatOut.toFixed(6)}, ${currentLngOut.toFixed(6)}`;
                    statusBoxOut.textContent = 'Lokasi berhasil diambil. Silakan Clock-out.';
                },
                (err) => {
                    console.error(err);
                    statusBoxOut.textContent = 'Gagal mengambil lokasi. Izinkan akses lokasi di browser.';
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        async function sendClockOut() {
            if (isSubmittingOut) return;

            if (currentLatOut === null || currentLngOut === null) {
                statusBoxOut.textContent = 'Lokasi belum tersedia. Coba refresh halaman dan izinkan lokasi.';
                return;
            }

            const url = '{{ url('/attendance/clock-out') }}';

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('lat', currentLatOut);
            formData.append('lng', currentLngOut);

            isSubmittingOut = true;
            setDisabledOut(true);
            statusBoxOut.textContent = 'Mengirim data clock-out...';

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    body: formData,
                });

                let data = null;
                try {
                    data = await res.json();
                } catch (e) {
                    console.error('Gagal parse JSON dari server', e);
                    statusBoxOut.textContent = `Respon server tidak valid. Status: ${res.status}`;
                    return;
                }

                if (!res.ok) {
                    statusBoxOut.textContent = data.error || data.message || 'Clock-out gagal. Coba lagi.';
                    return;
                }

                statusBoxOut.textContent = data.message || 'Clock-out berhasil.';
            } catch (err) {
                console.error(err);
                statusBoxOut.textContent = 'Terjadi kesalahan jaringan. Coba lagi.';
            } finally {
                setDisabledOut(false);
                isSubmittingOut = false;
            }
        }

        function setDisabledOut(disabled) {
            btnClockOut.disabled = disabled;
            btnClockOut.style.opacity = disabled ? 0.6 : 1;
        }

        btnClockOut.addEventListener('click', sendClockOut);

        document.addEventListener('DOMContentLoaded', () => {
            initLocationOut();
        });
    </script>

</x-app>
