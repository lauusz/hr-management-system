<x-app title="Presensi Masuk">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Presensi Masuk</h1>
                <p class="section-subtitle">{{ now()->translatedFormat('l, j F Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="capture-shell">

        {{-- Compact back nav --}}
        <a href="{{ url('/attendance') }}" class="capture-back" aria-label="Kembali ke presensi">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali</span>
        </a>

        {{-- Status strip: camera + GPS --}}
        <div class="capture-strip">
            <div class="capture-pill capture-pill--warn" id="cameraPill">
                <span class="capture-dot" id="cameraDot"></span>
                <span id="cameraText">Memuat kamera...</span>
            </div>
            <div class="capture-pill capture-pill--warn" id="gpsPill">
                <span class="capture-dot" id="gpsIndicator"></span>
                <span id="gpsText">Mencari lokasi...</span>
            </div>
        </div>

        {{-- Camera viewport --}}
        <div class="capture-viewport">
            <video id="video" autoplay playsinline muted></video>
            <img id="capturePreview" alt="">
            <div id="flashOverlay" class="capture-flash"></div>
            <canvas id="canvas" style="display:none;"></canvas>

            {{-- GPS overlay badge inside camera --}}
            <div class="capture-gps-badge" id="gpsCard">
                <span class="capture-gps-dot" id="gpsOverlayDot"></span>
                <span class="capture-gps-text" id="gpsOverlayText">Mencari lokasi...</span>
            </div>
        </div>

        {{-- Bottom controls --}}
        <div class="capture-controls">
            <p class="capture-hint" id="statusMessage">Menyiapkan kamera dan lokasi...</p>

            <input type="hidden" id="lat">
            <input type="hidden" id="lng">

            <div id="btnGroupCapture">
                <button type="button" id="btnCapture" class="capture-btn capture-btn--primary" disabled>
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Ambil Foto
                </button>
            </div>

            <div id="btnGroupAction" class="capture-actions">
                <button type="button" id="btnRetake" class="capture-btn capture-btn--secondary">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Ulangi
                </button>
                <button type="button" id="btnSubmit" class="capture-btn capture-btn--success">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Kirim Absen
                </button>
            </div>
        </div>
    </div>

    {{-- Success Modal --}}
    <x-modal id="attendance-success" title="Berhasil!" variant="success" type="info" cancelLabel="Tutup">
        <div style="text-align: center;">
            <div style="width:56px;height:56px;margin:0 auto 12px;background:rgba(34,197,94,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#16a34a;">
                <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p style="font-size: 1.0625rem; font-weight: 700; color: var(--text-primary, #111827); margin: 0 0 4px;">Presensi Masuk Tercatat</p>
            <p style="font-size: 0.8125rem; color: var(--text-muted, #6B7280); margin: 0;">Selamat bekerja, semoga harimu produktif!</p>
        </div>
    </x-modal>

    <style>
        /* ============================================= */
        /* HEADER SLOT                                   */
        /* ============================================= */
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
        .icon-navy  { background: rgba(10, 61, 98, 0.08);  color: var(--primary-dark, #0A3D62); }

        /* ============================================= */
        /* CAPTURE SHELL — mobile one-screen layout      */
        /* ============================================= */
        .capture-shell {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 8px 12px calc(8px + env(safe-area-inset-bottom));
        }

        /* ============================================= */
        /* BACK BUTTON                                   */
        /* ============================================= */
        .capture-back {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            height: 32px;
            padding: 0 10px 0 8px;
            background: var(--white, #fff);
            border: 1px solid var(--border, #E5E7EB);
            border-radius: 8px;
            color: var(--text-muted, #6B7280);
            text-decoration: none;
            transition: all 0.15s ease;
            flex-shrink: 0;
            align-self: flex-start;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            font-size: 0.6875rem;
            font-weight: 600;
        }
        .capture-back:hover {
            border-color: var(--primary, #145DA0);
            color: var(--primary, #145DA0);
            background: var(--gray-50, #F5F7FA);
        }
        .capture-back svg {
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }
        .capture-back:hover svg {
            transform: translateX(-2px);
        }

        /* ============================================= */
        /* STATUS STRIP                                  */
        /* ============================================= */
        .capture-strip {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
            flex-wrap: wrap;
        }

        .capture-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.6875rem;
            font-weight: 600;
            flex-shrink: 0;
            border: 1px solid transparent;
            line-height: 1.3;
        }
        .capture-pill--warn {
            background: rgba(245, 158, 11, 0.08);
            color: #a16207;
            border-color: rgba(245, 158, 11, 0.2);
        }
        .capture-pill--ok {
            background: rgba(34, 197, 94, 0.08);
            color: #15803d;
            border-color: rgba(34, 197, 94, 0.2);
        }
        .capture-pill--error {
            background: rgba(239, 68, 68, 0.08);
            color: #b91c1c;
            border-color: rgba(239, 68, 68, 0.2);
        }

        .capture-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .capture-dot--pulse {
            animation: dotPulse 1.5s infinite;
        }

        @keyframes dotPulse {
            0% { transform: scale(0.9); opacity: 0.7; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.7; }
        }

        /* ============================================= */
        /* CAMERA VIEWPORT                               */
        /* ============================================= */
        .capture-viewport {
            flex: 1 1 auto;
            min-height: 0;
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            background: #0a0f1e;
        }

        .capture-viewport video {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .capture-viewport #capturePreview {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none !important;
        }

        .capture-viewport #capturePreview.is-visible {
            display: block !important;
        }

        #video {
            transform: scaleX(-1);
        }

        .capture-flash {
            position: absolute;
            inset: 0;
            background: white;
            z-index: 20;
            opacity: 0;
            pointer-events: none;
        }
        .capture-flash.flash-anim {
            animation: flashEffect 0.4s ease-out;
        }
        @keyframes flashEffect {
            0% { opacity: 1; }
            100% { opacity: 0; }
        }

        /* ============================================= */
        /* GPS OVERLAY BADGE                             */
        /* ============================================= */
        .capture-gps-badge {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            background: rgba(0,0,0,0.55);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.12);
            color: #fff;
            font-size: 0.6875rem;
            font-weight: 600;
            line-height: 1.3;
        }
        .capture-gps-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .capture-gps-text {
            white-space: nowrap;
        }

        /* ============================================= */
        /* BOTTOM CONTROLS                               */
        /* ============================================= */
        .capture-controls {
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .capture-hint {
            text-align: center;
            font-size: 0.75rem;
            color: var(--text-muted, #6B7280);
            margin: 0;
            min-height: 18px;
            font-weight: 500;
            line-height: 1.4;
        }

        /* ============================================= */
        /* BUTTONS                                       */
        /* ============================================= */
        .capture-btn {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: inherit;
            line-height: 1.3;
        }
        .capture-btn svg {
            flex-shrink: 0;
        }

        .capture-btn--primary {
            background: linear-gradient(135deg, var(--primary-dark, #0A3D62), var(--primary, #145DA0));
            color: #fff;
            box-shadow: 0 4px 12px rgba(10, 61, 98, 0.22);
        }
        .capture-btn--primary:hover:not(:disabled) {
            box-shadow: 0 6px 20px rgba(10, 61, 98, 0.32);
            transform: translateY(-1px);
        }
        .capture-btn--primary:active:not(:disabled) {
            transform: scale(0.985);
        }

        .capture-btn--secondary {
            background: var(--gray-100, #F8FAFC);
            color: var(--text-secondary, #374151);
            border: 1.5px solid var(--border, #E5E7EB);
        }
        .capture-btn--secondary:hover:not(:disabled) {
            background: var(--gray-50, #F5F7FA);
            border-color: var(--gray-300, #D1D5DB);
        }

        .capture-btn--success {
            background: linear-gradient(135deg, #16a34a, #22C55E);
            color: #fff;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
        }
        .capture-btn--success:hover:not(:disabled) {
            box-shadow: 0 6px 20px rgba(22, 163, 74, 0.35);
            transform: translateY(-1px);
        }
        .capture-btn--success:active:not(:disabled) {
            transform: scale(0.985);
        }

        .capture-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            filter: grayscale(0.5);
        }

        .capture-actions {
            display: none;
            gap: 8px;
        }
        .capture-actions .capture-btn {
            flex: 1;
        }

        /* ============================================= */
        /* MOBILE ONE-SCREEN BEHAVIOR                    */
        /* ============================================= */
        @media (max-width: 767px) {
            .capture-shell {
                height: calc(100vh - 80px);
                height: calc(100dvh - 80px);
                height: calc(100svh - 80px);
                overflow: hidden;
            }
        }

        @media (max-width: 359px) {
            .capture-shell {
                gap: 6px;
                padding: 6px 10px calc(6px + env(safe-area-inset-bottom));
            }
            .capture-viewport {
                border-radius: 12px;
            }
            .capture-btn {
                padding: 11px;
                font-size: 0.8125rem;
            }
            .capture-pill {
                padding: 3px 8px;
                font-size: 0.625rem;
            }
        }

        /* ============================================= */
        /* TABLET & DESKTOP                              */
        /* ============================================= */
        @media (min-width: 768px) {
            .capture-shell {
                max-width: 480px;
                margin: 0 auto;
                gap: 12px;
                padding: 16px;
                min-height: 600px;
                max-height: 90vh;
            }

            .capture-back {
                height: 36px;
                padding: 0 12px 0 10px;
                font-size: 0.75rem;
            }

            .capture-viewport {
                min-height: 320px;
            }

            .capture-btn {
                padding: 14px;
                font-size: 0.9375rem;
            }
        }
    </style>

    <script>
        // DOM Elements — IDs preserved from original
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const imgPreview = document.getElementById('capturePreview');
        const flashOverlay = document.getElementById('flashOverlay');
        const statusMsg = document.getElementById('statusMessage');
        const gpsText = document.getElementById('gpsText');
        const gpsDot = document.getElementById('gpsIndicator');
        const gpsCard = document.getElementById('gpsCard');
        const gpsOverlayText = document.getElementById('gpsOverlayText');
        const gpsOverlayDot = document.getElementById('gpsOverlayDot');
        const cameraPill = document.getElementById('cameraPill');
        const cameraText = document.getElementById('cameraText');
        const cameraDot = document.getElementById('cameraDot');

        // Buttons
        const btnCapture = document.getElementById('btnCapture');
        const btnRetake = document.getElementById('btnRetake');
        const btnSubmit = document.getElementById('btnSubmit');
        const groupCapture = document.getElementById('btnGroupCapture');
        const groupAction = document.getElementById('btnGroupAction');

        // Data Variables
        let currentStream = null;
        let imageBlob = null;
        let userLat = null;
        let userLng = null;
        let isLocationValid = false;

        // 1. Inisialisasi Kamera
        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } },
                    audio: false
                });
                video.srcObject = stream;
                currentStream = stream;

                video.onloadedmetadata = () => {
                    cameraPill.className = 'capture-pill capture-pill--ok';
                    cameraText.textContent = 'Kamera aktif';
                    cameraDot.style.backgroundColor = 'var(--success, #22C55E)';
                    cameraDot.classList.remove('capture-dot--pulse');
                    checkReadiness();
                };
            } catch (err) {
                console.error('Camera Error:', err);
                cameraPill.className = 'capture-pill capture-pill--error';
                cameraText.textContent = 'Kamera error';
                cameraDot.style.backgroundColor = 'var(--error, #EF4444)';
                cameraDot.classList.remove('capture-dot--pulse');
                statusMsg.textContent = 'Gagal akses kamera. Pastikan izin diberikan.';
                statusMsg.style.color = 'var(--error, #EF4444)';
            }
        }

        // 2. Logic GPS (High Accuracy)
        function initGPS() {
            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    (position) => {
                        userLat = position.coords.latitude;
                        userLng = position.coords.longitude;
                        const acc = position.coords.accuracy;

                        document.getElementById('lat').value = userLat;
                        document.getElementById('lng').value = userLng;

                        if (acc <= 100) {
                            isLocationValid = true;
                            gpsText.textContent = `Akurat (±${Math.round(acc)}m)`;
                            gpsDot.style.backgroundColor = 'var(--success, #22C55E)';
                            gpsOverlayText.textContent = `Akurat (±${Math.round(acc)}m)`;
                            gpsOverlayDot.style.backgroundColor = 'var(--success, #22C55E)';
                            gpsCard.className = 'capture-gps-badge gps-ready';
                            gpsPill.className = 'capture-pill capture-pill--ok';
                        } else {
                            isLocationValid = true;
                            gpsText.textContent = `Lemah (±${Math.round(acc)}m)`;
                            gpsDot.style.backgroundColor = 'var(--warning, #F59E0B)';
                            gpsOverlayText.textContent = `Lemah (±${Math.round(acc)}m)`;
                            gpsOverlayDot.style.backgroundColor = 'var(--warning, #F59E0B)';
                            gpsCard.className = 'capture-gps-badge gps-weak';
                            gpsPill.className = 'capture-pill capture-pill--warn';
                        }
                        checkReadiness();
                    },
                    (error) => {
                        console.error('GPS Error:', error);
                        gpsText.textContent = 'GPS Error';
                        gpsDot.style.backgroundColor = 'var(--error, #EF4444)';
                        gpsOverlayText.textContent = 'GPS Error';
                        gpsOverlayDot.style.backgroundColor = 'var(--error, #EF4444)';
                        gpsCard.className = 'capture-gps-badge gps-error';
                        gpsPill.className = 'capture-pill capture-pill--error';
                        statusMsg.textContent = 'Aktifkan GPS & Izin Lokasi di Browser.';
                        statusMsg.style.color = 'var(--error, #EF4444)';
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 0
                    }
                );
            } else {
                statusMsg.textContent = 'Browser tidak support GPS.';
                gpsPill.className = 'capture-pill capture-pill--error';
                gpsText.textContent = 'GPS Error';
            }
        }

        // 3. Cek Kesiapan (Kamera + Lokasi)
        function checkReadiness() {
            if (video.srcObject && isLocationValid) {
                btnCapture.disabled = false;
                statusMsg.textContent = 'Siap untuk Clock In.';
                statusMsg.style.color = 'var(--success, #22C55E)';
            }
        }

        // 4. Fungsi Capture
        btnCapture.addEventListener('click', () => {
            if (!video.srcObject) return;

            flashOverlay.classList.add('flash-anim');
            setTimeout(() => flashOverlay.classList.remove('flash-anim'), 500);

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            const ctx = canvas.getContext('2d');
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob((blob) => {
                imageBlob = blob;
                const url = URL.createObjectURL(blob);

                imgPreview.src = url;
                imgPreview.classList.add('is-visible');
                video.style.display = 'none';

                groupCapture.style.display = 'none';
                groupAction.style.display = 'flex';
                statusMsg.textContent = 'Foto terambil. Kirim atau Ulangi?';
                statusMsg.style.color = 'var(--text-muted, #6B7280)';
            }, 'image/jpeg', 0.85);
        });

        // 5. Fungsi Ulangi (Retake)
        btnRetake.addEventListener('click', () => {
            imgPreview.classList.remove('is-visible');
            imgPreview.removeAttribute('src');
            video.style.display = 'block';
            imageBlob = null;

            groupCapture.style.display = 'block';
            groupAction.style.display = 'none';
            statusMsg.textContent = 'Silakan ambil foto wajah Anda.';
            statusMsg.style.color = 'var(--text-muted, #6B7280)';
        });

        // 6. Fungsi Submit (Kirim ke Server)
        btnSubmit.addEventListener('click', async () => {
            if (!imageBlob || !userLat) return;

            const originalText = btnSubmit.innerHTML;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = `
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation: spin 1s linear infinite;">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="32" stroke-dashoffset="12" stroke-linecap="round"/>
                </svg>
                Mengirim...
            `;
            btnRetake.disabled = true;

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('lat', userLat);
            formData.append('lng', userLng);
            formData.append('photo', imageBlob, 'clock-in.jpg');

            try {
                const response = await fetch('{{ url("/attendance/clock-in") }}', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    const modal = document.getElementById('attendance-success');
                    modal.style.display = 'flex';

                    const closeBtns = modal.querySelectorAll('[data-modal-close="true"]');
                    closeBtns.forEach(btn => {
                        btn.onclick = () => window.location.href = '{{ url("/attendance") }}';
                    });
                } else {
                    alert(data.message || 'Gagal melakukan presensi.');
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalText;
                    btnRetake.disabled = false;
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan koneksi.');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;
                btnRetake.disabled = false;
            }
        });

        // Jalankan saat load
        document.addEventListener('DOMContentLoaded', () => {
            cameraDot.classList.add('capture-dot--pulse');
            startCamera();
            initGPS();
        });
    </script>

    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>

</x-app>
