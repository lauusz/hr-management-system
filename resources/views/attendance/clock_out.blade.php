<x-app title="Clock-out">
    <div class="card" style="max-width:480px;margin:0 auto;">
        <h2 style="font-size:1.25rem;font-weight:700;margin-bottom:10px;">Clock-out</h2>

        <p style="font-size:0.9rem;opacity:.8;margin-bottom:16px;">
            Pastikan Anda berada di lokasi kerja saat melakukan clock-out.
        </p>

        <div id="statusBoxOut" style="font-size:0.9rem;margin-bottom:14px;color:#4b5563;">
            Mengambil lokasi...
        </div>

        <input type="hidden" id="latInputOut">
        <input type="hidden" id="lngInputOut">

        <button id="btnClockOut" type="button"
            style="width:100%;padding:14px 20px;border:none;border-radius:999px;background:#059669;color:#fff;cursor:pointer;font-size:1rem;font-weight:600;margin-top:10px;">
            ✅ Clock-out Sekarang
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
            Anda akan diarahkan kembali ke halaman presensi sesaat lagi.
        </p>
    </x-modal>

    <script>
        const latInputOut = document.getElementById('latInputOut');
        const lngInputOut = document.getElementById('lngInputOut');
        const statusBoxOut = document.getElementById('statusBoxOut');
        const btnClockOut = document.getElementById('btnClockOut');

        let currentLatOut = null;
        let currentLngOut = null;
        let isSubmittingOut = false;
        let redirectScheduled = false;

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

                    statusBoxOut.textContent = 'Lokasi berhasil diambil. Silakan Clock-out.';
                },
                (err) => {
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
            btnClockOut.disabled = true;
            btnClockOut.style.opacity = 0.6;
            statusBoxOut.textContent = 'Mengirim data clock-out...';

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    body: formData,
                });

                let data = await res.json().catch(() => null);

                if (!res.ok) {
                    statusBoxOut.textContent = data?.error || data?.message || 'Clock-out gagal. Coba lagi.';
                    return;
                }

                statusBoxOut.textContent = data?.message || 'Clock-out berhasil.';

                const modal = document.getElementById('clockout-success');
                if (modal) {
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }

                if (!redirectScheduled) {
                    redirectScheduled = true;
                    setTimeout(() => {
                        window.location.href = '{{ url('/attendance') }}';
                    }, 3500);
                }

            } catch (err) {
                statusBoxOut.textContent = 'Terjadi kesalahan jaringan. Coba lagi.';
            } finally {
                isSubmittingOut = false;
                btnClockOut.disabled = false;
                btnClockOut.style.opacity = 1;
            }
        }

        btnClockOut.addEventListener('click', sendClockOut);

        document.addEventListener('DOMContentLoaded', () => {
            initLocationOut();
        });
    </script>
</x-app>
