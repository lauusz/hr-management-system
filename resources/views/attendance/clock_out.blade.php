<x-app title="Clock-out">
    <div class="card" style="max-width:520px;margin:0 auto;">
        <div style="margin-bottom:14px;">
            <h2 style="margin:0 0 4px 0;font-size:1.1rem;font-weight:700;">Presensi Pulang</h2>
            <p style="font-size:0.9rem;opacity:.8;margin:0;">
                Pastikan Anda berada di lokasi kerja saat melakukan clock-out.
            </p>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;gap:8px;">
            <div id="locationStatusBadgeOut"
                 style="font-size:0.8rem;padding:4px 10px;border-radius:999px;background:#fef3c7;color:#92400e;display:inline-flex;align-items:center;gap:6px;">
                <span style="width:8px;height:8px;border-radius:999px;background:#f97316;display:inline-block;"></span>
                <span>Menunggu lokasi...</span>
            </div>
            <div id="hintTextOut" style="font-size:0.8rem;opacity:.75;text-align:right;">
                Lokasi sedang diambil...
            </div>
        </div>

        <div id="statusBoxOut" style="font-size:0.9rem;margin-bottom:14px;color:#4b5563;">
            Mengambil lokasi Anda. Mohon tunggu dan izinkan akses lokasi di browser.
        </div>

        <input type="hidden" id="latInputOut">
        <input type="hidden" id="lngInputOut">

        <button id="btnClockOut" type="button"
            style="width:100%;padding:12px 16px;border:none;border-radius:999px;background:#059669;color:#fff;cursor:not-allowed;font-size:0.95rem;font-weight:600;opacity:.6;margin-top:4px;">
            📍 Menunggu lokasi...
        </button>
    </div>

    <x-modal
        id="clockout-success"
        title="Berhasil Clock-out"
        type="info"
        cancelLabel="Tutup"
    >
        <p style="margin:0 0 4px 0;">
            Clock-out berhasil tercatat.
        </p>
        <p style="margin:0;font-size:0.9rem;opacity:.85;">
            Anda akan diarahkan kembali ke halaman presensi setelah menekan tombol tutup.
        </p>
    </x-modal>

    <script>
        const latInputOut = document.getElementById('latInputOut');
        const lngInputOut = document.getElementById('lngInputOut');
        const statusBoxOut = document.getElementById('statusBoxOut');
        const btnClockOut = document.getElementById('btnClockOut');
        const locationStatusBadgeOut = document.getElementById('locationStatusBadgeOut');
        const hintTextOut = document.getElementById('hintTextOut');

        let currentLatOut = null;
        let currentLngOut = null;
        let isSubmittingOut = false;
        let isLocationReadyOut = false;

        function setClockOutButtonDisabled(disabled, reason) {
            btnClockOut.disabled = disabled;
            const opacity = disabled ? 0.6 : 1;
            const cursor = disabled ? 'not-allowed' : 'pointer';
            btnClockOut.style.opacity = opacity;
            btnClockOut.style.cursor = cursor;

            if (disabled && reason === 'location') {
                btnClockOut.textContent = '📍 Menunggu lokasi...';
            } else if (!disabled) {
                btnClockOut.textContent = '✅ Clock-out Sekarang';
            }
        }

        function setLocationBadgeOut(state, text) {
            if (state === 'loading') {
                locationStatusBadgeOut.style.background = '#fef3c7';
                locationStatusBadgeOut.style.color = '#92400e';
                locationStatusBadgeOut.querySelector('span:nth-child(1)').style.background = '#f97316';
            } else if (state === 'ready') {
                locationStatusBadgeOut.style.background = '#dcfce7';
                locationStatusBadgeOut.style.color = '#166534';
                locationStatusBadgeOut.querySelector('span:nth-child(1)').style.background = '#22c55e';
            } else if (state === 'error') {
                locationStatusBadgeOut.style.background = '#fee2e2';
                locationStatusBadgeOut.style.color = '#b91c1c';
                locationStatusBadgeOut.querySelector('span:nth-child(1)').style.background = '#ef4444';
            }

            const labelSpan = locationStatusBadgeOut.querySelector('span:nth-child(2)');
            if (labelSpan) {
                labelSpan.textContent = text;
            }
        }

        function initLocationOut() {
            setLocationBadgeOut('loading', 'Menunggu lokasi...');
            statusBoxOut.textContent = 'Mengambil lokasi Anda. Mohon tunggu dan izinkan akses lokasi di browser.';
            hintTextOut.textContent = 'Lokasi sedang diambil...';

            if (!navigator.geolocation) {
                setLocationBadgeOut('error', 'Lokasi tidak didukung');
                statusBoxOut.textContent = 'Browser tidak mendukung geolokasi.';
                hintTextOut.textContent = 'Perangkat ini tidak mendukung fitur lokasi.';
                setClockOutButtonDisabled(true, 'location');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    currentLatOut = pos.coords.latitude;
                    currentLngOut = pos.coords.longitude;

                    latInputOut.value = currentLatOut;
                    lngInputOut.value = currentLngOut;

                    isLocationReadyOut = true;

                    setLocationBadgeOut('ready', 'Lokasi terkunci');
                    statusBoxOut.textContent = 'Lokasi berhasil diambil. Anda dapat melakukan clock-out.';
                    hintTextOut.textContent = 'Lokasi siap. Tekan tombol untuk clock-out.';
                    setClockOutButtonDisabled(false);
                },
                () => {
                    setLocationBadgeOut('error', 'Lokasi gagal');
                    statusBoxOut.textContent = 'Gagal mengambil lokasi. Izinkan akses lokasi di browser untuk melakukan clock-out.';
                    hintTextOut.textContent = 'Periksa pengaturan lokasi dan coba muat ulang halaman.';
                    setClockOutButtonDisabled(true, 'location');
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

            if (!isLocationReadyOut || currentLatOut === null || currentLngOut === null) {
                statusBoxOut.textContent = 'Lokasi belum tersedia. Pastikan izin lokasi sudah diberikan.';
                return;
            }

            const url = '{{ url('/attendance/clock-out') }}';

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('lat', currentLatOut);
            formData.append('lng', currentLngOut);

            isSubmittingOut = true;
            setClockOutButtonDisabled(true);
            statusBoxOut.textContent = 'Mengirim data clock-out...';

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    body: formData
                });

                let data = null;
                try {
                    data = await res.json();
                } catch (e) {
                    statusBoxOut.textContent = 'Respon server tidak valid.';
                    setClockOutButtonDisabled(false);
                    return;
                }

                if (!res.ok) {
                    statusBoxOut.textContent = data.error || data.message || 'Clock-out gagal. Coba lagi.';
                    setClockOutButtonDisabled(false);
                    return;
                }

                statusBoxOut.textContent = data.message || 'Clock-out berhasil.';

                const modal = document.getElementById('clockout-success');
                if (modal) {
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            } catch (err) {
                statusBoxOut.textContent = 'Terjadi kesalahan jaringan. Coba lagi.';
                setClockOutButtonDisabled(false);
            } finally {
                isSubmittingOut = false;
            }
        }

        btnClockOut.addEventListener('click', sendClockOut);

        document.addEventListener('DOMContentLoaded', () => {
            setClockOutButtonDisabled(true, 'location');
            initLocationOut();

            const modal = document.getElementById('clockout-success');
            if (modal) {
                const closeButtons = modal.querySelectorAll('[data-modal-close="true"]');
                closeButtons.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        window.location.href = '{{ url('/attendance') }}';
                    });
                });
            }
        });
    </script>
</x-app>
