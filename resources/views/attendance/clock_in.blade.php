<x-app title="Clock-in">
    <div class="card" style="max-width:520px;margin:0 auto;">
        <div style="margin-bottom:14px;">
            <h2 style="margin:0 0 4px 0;font-size:1.1rem;font-weight:700;">Presensi Masuk</h2>
            <p style="font-size:0.9rem;opacity:.8;margin:0;">
                Pastikan mengizinkan akses <b>kamera</b> dan <b>lokasi</b> pada perangkat Anda.
            </p>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;gap:8px;">
            <div id="locationStatusBadge"
                 style="font-size:0.8rem;padding:4px 10px;border-radius:999px;background:#fef3c7;color:#92400e;display:inline-flex;align-items:center;gap:6px;">
                <span style="width:8px;height:8px;border-radius:999px;background:#f97316;display:inline-block;"></span>
                <span>Menunggu lokasi...</span>
            </div>
            <div id="cameraStatusText" style="font-size:0.8rem;opacity:.75;">
                Kamera: menyiapkan...
            </div>
        </div>

        <div style="border-radius:12px;overflow:hidden;background:#000;margin-bottom:12px;position:relative;">
            <video id="video" autoplay playsinline style="width:100%;max-height:360px;object-fit:cover;"></video>
            <div id="videoOverlayLoading"
                 style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:linear-gradient(to bottom,rgba(15,23,42,0.55),rgba(15,23,42,0.85));color:#e5e7eb;font-size:0.9rem;">
                <div style="text-align:center;">
                    <div style="margin-bottom:6px;">Mengaktifkan kamera...</div>
                    <div style="font-size:0.8rem;opacity:.8;">Jika diminta izin kamera, pilih <b>Allow / Izinkan</b>.</div>
                </div>
            </div>
        </div>

        <div id="capturePreviewWrapper" style="display:none;margin-bottom:12px;">
            <p style="font-size:0.85rem;margin-bottom:6px;">Foto yang akan dikirim:</p>
            <img id="capturePreview" src="" alt="Captured" style="width:100%;border-radius:12px;border:1px solid #e5e7eb;">
        </div>

        <canvas id="canvas" style="display:none;"></canvas>

        <div id="statusBox" style="font-size:0.9rem;margin-bottom:14px;color:#4b5563;">
            Mengambil lokasi Anda. Mohon tunggu dan izinkan akses lokasi di browser.
        </div>

        <input type="hidden" id="hiddenLat">
        <input type="hidden" id="hiddenLng">

        <div style="display:flex;flex-direction:column;gap:8px;margin-top:4px;">
            <button id="btnCapture" type="button"
                style="width:100%;padding:11px 16px;border:none;border-radius:999px;background:#2563eb;color:#fff;cursor:not-allowed;font-size:0.95rem;font-weight:600;opacity:.6;">
                📍 Menunggu lokasi...
            </button>

            <button id="btnClockIn" type="button"
                style="width:100%;padding:11px 16px;border:none;border-radius:999px;background:#16a34a;color:#fff;cursor:not-allowed;font-size:0.95rem;font-weight:600;opacity:.6;">
                ✅ Clock-in
            </button>
        </div>
    </div>

    <x-modal
        id="attendance-success"
        title="Berhasil Clock-in"
        type="info"
        cancelLabel="Tutup"
    >
        <p style="margin:0 0 4px 0;">Presensi berhasil tercatat.</p>
        <p style="margin:0;font-size:0.9rem;opacity:.85;">
            Anda akan kembali ke halaman presensi setelah menekan tombol tutup.
        </p>
    </x-modal>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const capturePreviewWrapper = document.getElementById('capturePreviewWrapper');
        const capturePreview = document.getElementById('capturePreview');
        const statusBox = document.getElementById('statusBox');
        const hiddenLat = document.getElementById('hiddenLat');
        const hiddenLng = document.getElementById('hiddenLng');
        const btnCapture = document.getElementById('btnCapture');
        const btnClockIn = document.getElementById('btnClockIn');
        const locationStatusBadge = document.getElementById('locationStatusBadge');
        const cameraStatusText = document.getElementById('cameraStatusText');
        const videoOverlayLoading = document.getElementById('videoOverlayLoading');

        let stream = null;
        let currentBlob = null;
        let currentLat = null;
        let currentLng = null;
        let isSubmitting = false;
        let isCameraActive = false;
        let isLocationReady = false;

        function setButtonsDisabled(disabled, reason) {
            btnCapture.disabled = disabled;
            btnClockIn.disabled = disabled;

            const opacity = disabled ? 0.6 : 1;
            const cursor = disabled ? 'not-allowed' : 'pointer';

            btnCapture.style.opacity = opacity;
            btnClockIn.style.opacity = opacity;
            btnCapture.style.cursor = cursor;
            btnClockIn.style.cursor = cursor;

            if (disabled && reason === 'location') {
                btnCapture.textContent = '📍 Menunggu lokasi...';
            } else if (!disabled) {
                btnCapture.textContent = isCameraActive ? '📸 Ambil Foto' : '📸 Ambil Foto';
            }
        }

        async function initCamera() {
            try {
                cameraStatusText.textContent = 'Kamera: mengaktifkan...';
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user' },
                    audio: false
                });

                video.srcObject = stream;
                video.style.display = 'block';
                isCameraActive = true;
                cameraStatusText.textContent = 'Kamera: siap digunakan';
                if (videoOverlayLoading) {
                    videoOverlayLoading.style.display = 'none';
                }
                if (isLocationReady) {
                    setButtonsDisabled(false);
                    statusBox.textContent = 'Lokasi dan kamera siap. Silakan ambil foto lalu Clock-in.';
                    btnCapture.textContent = '📸 Ambil Foto';
                }
            } catch (err) {
                cameraStatusText.textContent = 'Kamera: gagal diakses';
                statusBox.textContent = 'Gagal mengakses kamera. Izinkan akses kamera di browser.';
            }
        }

        function stopCamera() {
            if (stream) {
                const tracks = stream.getTracks();
                tracks.forEach(track => track.stop());
            }
            isCameraActive = false;
            cameraStatusText.textContent = 'Kamera: nonaktif';
        }

        function setLocationBadge(state, text) {
            if (state === 'loading') {
                locationStatusBadge.style.background = '#fef3c7';
                locationStatusBadge.style.color = '#92400e';
                locationStatusBadge.querySelector('span:nth-child(1)').style.background = '#f97316';
            } else if (state === 'ready') {
                locationStatusBadge.style.background = '#dcfce7';
                locationStatusBadge.style.color = '#166534';
                locationStatusBadge.querySelector('span:nth-child(1)').style.background = '#22c55e';
            } else if (state === 'error') {
                locationStatusBadge.style.background = '#fee2e2';
                locationStatusBadge.style.color = '#b91c1c';
                locationStatusBadge.querySelector('span:nth-child(1)').style.background = '#ef4444';
            }

            const labelSpan = locationStatusBadge.querySelector('span:nth-child(2)');
            if (labelSpan) {
                labelSpan.textContent = text;
            }
        }

        function initLocation() {
            setLocationBadge('loading', 'Menunggu lokasi...');
            statusBox.textContent = 'Mengambil lokasi Anda. Mohon tunggu dan izinkan akses lokasi di browser.';

            if (!navigator.geolocation) {
                setLocationBadge('error', 'Lokasi tidak didukung');
                statusBox.textContent = 'Browser tidak mendukung geolokasi.';
                setButtonsDisabled(true, 'location');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    currentLat = pos.coords.latitude;
                    currentLng = pos.coords.longitude;
                    hiddenLat.value = currentLat;
                    hiddenLng.value = currentLng;
                    isLocationReady = true;

                    setLocationBadge('ready', 'Lokasi terkunci');
                    if (isCameraActive) {
                        setButtonsDisabled(false);
                        statusBox.textContent = 'Lokasi dan kamera siap. Silakan ambil foto lalu Clock-in.';
                        btnCapture.textContent = '📸 Ambil Foto';
                    } else {
                        statusBox.textContent = 'Lokasi siap. Menunggu kamera aktif.';
                    }
                },
                () => {
                    setLocationBadge('error', 'Lokasi gagal');
                    statusBox.textContent = 'Gagal mengambil lokasi. Izinkan akses lokasi di browser untuk melakukan presensi.';
                    setButtonsDisabled(true, 'location');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        function captureFrame() {
            if (!isCameraActive) {
                capturePreviewWrapper.style.display = 'none';
                video.style.display = 'block';
                initCamera();
                btnCapture.textContent = '📸 Ambil Foto';
                return;
            }

            const videoWidth = video.videoWidth || 640;
            const videoHeight = video.videoHeight || 480;

            canvas.width = videoWidth;
            canvas.height = videoHeight;

            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, videoWidth, videoHeight);

            canvas.toBlob((blob) => {
                if (!blob) {
                    statusBox.textContent = 'Gagal mengambil gambar.';
                    return;
                }

                currentBlob = blob;
                capturePreview.src = URL.createObjectURL(blob);
                capturePreviewWrapper.style.display = 'block';
                video.style.display = 'none';
                statusBox.textContent = 'Foto diambil. Silakan Clock-in atau ambil ulang.';

                stopCamera();
                btnCapture.textContent = '🔁 Ambil Ulang';
            }, 'image/jpeg', 0.9);
        }

        async function sendAttendance() {
            if (isSubmitting) return;

            if (!isLocationReady || currentLat === null || currentLng === null) {
                statusBox.textContent = 'Lokasi belum tersedia. Pastikan izin lokasi sudah diberikan.';
                return;
            }

            if (!currentBlob) {
                statusBox.textContent = 'Silakan ambil foto terlebih dahulu untuk Clock-in.';
                return;
            }

            const url = '{{ url('/attendance/clock-in') }}';

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('lat', currentLat);
            formData.append('lng', currentLng);
            formData.append('photo', currentBlob, 'attendance.jpg');

            isSubmitting = true;
            setButtonsDisabled(true);
            statusBox.textContent = 'Mengirim data presensi...';

            try {
                const res = await fetch(url, { method: 'POST', body: formData });
                let data = null;

                try {
                    data = await res.json();
                } catch (e) {
                    statusBox.textContent = 'Respon server tidak valid.';
                    return;
                }

                if (!res.ok) {
                    statusBox.textContent = data.error || data.message || 'Presensi gagal.';
                    setButtonsDisabled(false);
                    return;
                }

                statusBox.textContent = data.message || 'Presensi berhasil.';

                const successModal = document.getElementById('attendance-success');
                if (successModal) {
                    successModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            } catch (e) {
                statusBox.textContent = 'Terjadi kesalahan jaringan.';
                setButtonsDisabled(false);
            } finally {
                isSubmitting = false;
            }
        }

        btnCapture.addEventListener('click', captureFrame);
        btnClockIn.addEventListener('click', () => sendAttendance());

        document.addEventListener('DOMContentLoaded', () => {
            setButtonsDisabled(true, 'location');
            initCamera();
            initLocation();

            const modal = document.getElementById('attendance-success');
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
