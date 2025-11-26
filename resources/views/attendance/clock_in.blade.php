<x-app title="Clock-in">
    <div class="card" style="max-width:480px;margin:0 auto;">
        <p style="font-size:0.9rem;opacity:.8;margin-bottom:16px;">
            Pastikan mengizinkan akses <b>kamera</b> dan <b>lokasi</b>.
        </p>

        <div style="border-radius:12px;overflow:hidden;background:#000;margin-bottom:12px;">
            <video id="video" autoplay playsinline style="width:100%;max-height:360px;object-fit:cover;"></video>
        </div>

        <div id="capturePreviewWrapper" style="display:none;margin-bottom:12px;">
            <p style="font-size:0.85rem;margin-bottom:6px;">Foto yang akan dikirim:</p>
            <img id="capturePreview" src="" alt="Captured" style="width:100%;border-radius:12px;border:1px solid #e5e7eb;">
        </div>

        <canvas id="canvas" style="display:none;"></canvas>

        <div id="statusBox" style="font-size:0.9rem;margin-bottom:14px;color:#4b5563;">
            Menunggu izin kamera dan lokasi dari perangkat...
        </div>

        <input type="hidden" id="hiddenLat">
        <input type="hidden" id="hiddenLng">

        <div style="display:flex;flex-direction:column;gap:10px;margin-top:4px;">
            <button id="btnCapture" type="button"
                style="width:100%;padding:12px 16px;border:none;border-radius:999px;background:#2563eb;color:#fff;cursor:pointer;font-size:1rem;font-weight:600;">
                📸 Ambil Foto
            </button>

            <button id="btnClockIn" type="button"
                style="width:100%;padding:12px 16px;border:none;border-radius:999px;background:#16a34a;color:#fff;cursor:pointer;font-size:1rem;font-weight:600;">
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
        <p style="margin:0 0 4px 0;">
            Presensi berhasil tercatat.
        </p>
        <p style="margin:0;font-size:0.9rem;opacity:.85;">
            Anda akan diarahkan ke halaman riwayat presensi sesaat lagi.
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

        let stream = null;
        let currentBlob = null;
        let currentLat = null;
        let currentLng = null;
        let isSubmitting = false;
        let isCameraActive = false;
        let hasRedirectScheduled = false;

        async function initCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user' },
                    audio: false
                });

                video.srcObject = stream;
                video.style.display = 'block';

                isCameraActive = true;
                statusBox.textContent = 'Kamera aktif. Silakan ambil foto.';
                btnCapture.textContent = "📸 Ambil Foto";
            } catch (err) {
                console.error(err);
                statusBox.textContent = 'Gagal mengakses kamera. Izinkan akses kamera di browser.';
            }
        }

        function stopCamera() {
            if (stream) {
                const tracks = stream.getTracks();
                tracks.forEach(track => track.stop());
            }
            isCameraActive = false;
        }

        function initLocation() {
            if (!navigator.geolocation) {
                statusBox.textContent = 'Browser tidak mendukung geolokasi.';
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    currentLat = pos.coords.latitude;
                    currentLng = pos.coords.longitude;
                    hiddenLat.value = currentLat;
                    hiddenLng.value = currentLng;
                    statusBox.textContent = 'Kamera dan lokasi siap. Silakan ambil foto lalu Clock-in.';
                },
                (err) => {
                    console.error(err);
                    statusBox.textContent = 'Gagal mengambil lokasi. Izinkan akses lokasi di browser.';
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
                btnCapture.textContent = "📸 Ambil Foto";
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
                btnCapture.textContent = "🔁 Ambil Ulang";
            }, 'image/jpeg', 0.9);
        }

        async function sendAttendance() {
            if (isSubmitting) return;

            if (currentLat === null || currentLng === null) {
                statusBox.textContent = 'Lokasi belum tersedia. Coba refresh halaman dan izinkan lokasi.';
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
                const res = await fetch(url, {
                    method: 'POST',
                    body: formData,
                });

                let data = null;
                try {
                    data = await res.json();
                } catch (e) {
                    console.error('Gagal parse JSON dari server', e);
                    statusBox.textContent = `Respon server tidak valid. Status: ${res.status}`;
                    return;
                }

                if (!res.ok) {
                    statusBox.textContent = data.error || data.message || 'Presensi gagal. Coba lagi.';
                    return;
                }

                statusBox.textContent = data.message || 'Presensi berhasil.';

                const successModal = document.getElementById('attendance-success');
                if (successModal) {
                    successModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }

                if (!hasRedirectScheduled) {
                    hasRedirectScheduled = true;
                    setTimeout(function () {
                        window.location.href = '{{ url('/attendance') }}';
                    }, 3500);
                }
            } catch (err) {
                console.error(err);
                statusBox.textContent = 'Terjadi kesalahan jaringan. Coba lagi.';
            } finally {
                setButtonsDisabled(false);
                isSubmitting = false;
            }
        }

        function setButtonsDisabled(disabled) {
            btnCapture.disabled = disabled;
            btnClockIn.disabled = disabled;

            const opacity = disabled ? 0.6 : 1;
            btnCapture.style.opacity = opacity;
            btnClockIn.style.opacity = opacity;
        }

        btnCapture.addEventListener('click', captureFrame);
        btnClockIn.addEventListener('click', () => sendAttendance());

        document.addEventListener('DOMContentLoaded', () => {
            initCamera();
            initLocation();
        });
    </script>
</x-app>
