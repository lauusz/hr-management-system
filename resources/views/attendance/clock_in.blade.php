<x-app title="Clock-in">
    {{-- CSS Custom untuk Tampilan Modern --}}
    <style>
        :root {
            --primary: #2563eb;
            --success: #16a34a;
            --danger: #dc2626;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #94a3b8;
        }

        .attendance-card {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }

        .header-nav {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            background: white;
            border-bottom: 1px solid #f1f5f9;
        }

        .btn-back {
            background: #f1f5f9;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--dark);
            transition: 0.2s;
        }

        .btn-back:hover {
            background: #e2e8f0;
        }

        .header-title {
            margin-left: 15px;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
        }

        .camera-wrapper {
            position: relative;
            width: 100%;
            background: black;
            height: 400px; /* Tinggi kamera fix */
            overflow: hidden;
        }

        #video, #capturePreview {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        #capturePreview {
            display: none; /* Hidden by default */
        }

        /* Overlay Status GPS */
        .gps-badge {
            position: absolute;
            top: 15px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            padding: 6px 14px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 10;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .gps-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--danger);
            animation: pulse 1.5s infinite;
        }

        .gps-text {
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .controls-area {
            padding: 20px;
            background: white;
        }

        .status-message {
            text-align: center;
            font-size: 0.9rem;
            color: var(--gray);
            margin-bottom: 15px;
            min-height: 20px;
        }

        /* Tombol Utama */
        .btn-main {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-capture {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-capture:active {
            transform: scale(0.98);
        }

        .action-buttons {
            display: none; /* Hidden by default */
            gap: 10px;
        }

        .btn-secondary {
            background: #f1f5f9;
            color: var(--dark);
            flex: 1;
        }

        .btn-success {
            background: var(--success);
            color: white;
            flex: 1;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
        }

        /* Loading & Disabled States */
        .btn-main:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            filter: grayscale(1);
        }

        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.7; }
            50% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.7; }
        }

        /* Flash Effect */
        .flash {
            position: absolute;
            inset: 0;
            background: white;
            z-index: 20;
            opacity: 0;
            pointer-events: none;
        }
        .flash-anim {
            animation: flashEffect 0.4s ease-out;
        }
        @keyframes flashEffect {
            0% { opacity: 1; }
            100% { opacity: 0; }
        }
    </style>

    <div class="attendance-card">
        
        <div class="header-nav">
            <a href="{{ url('/attendance') }}" class="btn-back">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
            </a>
            <div class="header-title">Presensi Masuk</div>
        </div>

        <div class="camera-wrapper">
            <div class="gps-badge">
                <div class="gps-dot" id="gpsIndicator"></div>
                <span class="gps-text" id="gpsText">Mencari Lokasi...</span>
            </div>

            <video id="video" autoplay playsinline muted></video>
            
            <img id="capturePreview" alt="Hasil Foto">
            
            <div id="flashOverlay" class="flash"></div>
            
            <canvas id="canvas" style="display:none;"></canvas>
        </div>

        <div class="controls-area">
            <div class="status-message" id="statusMessage">
                Menyiapkan kamera dan lokasi...
            </div>

            <input type="hidden" id="lat">
            <input type="hidden" id="lng">

            <div id="btnGroupCapture">
                <button type="button" id="btnCapture" class="btn-main btn-capture" disabled>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    Ambil Foto
                </button>
            </div>

            <div id="btnGroupAction" class="action-buttons">
                <button type="button" id="btnRetake" class="btn-main btn-secondary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                    Ulangi
                </button>
                <button type="button" id="btnSubmit" class="btn-main btn-success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Kirim Absen
                </button>
            </div>
        </div>
    </div>

    <x-modal id="attendance-success" title="Berhasil!" type="info" cancelLabel="Tutup">
        <div style="text-align: center;">
            <p style="font-size: 1.1rem; font-weight: 600;">Presensi Masuk Tercatat</p>
        </div>
    </x-modal>

    {{-- Javascript Logic --}}
    <script>
        // DOM Elements
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const imgPreview = document.getElementById('capturePreview');
        const flashOverlay = document.getElementById('flashOverlay');
        const statusMsg = document.getElementById('statusMessage');
        const gpsText = document.getElementById('gpsText');
        const gpsDot = document.getElementById('gpsIndicator');
        
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
                // Constraint: Kamera depan (user), Resolusi 720p ideal
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } }, 
                    audio: false 
                });
                video.srcObject = stream;
                currentStream = stream;
                
                // Event saat video siap play
                video.onloadedmetadata = () => {
                    checkReadiness();
                };
            } catch (err) {
                console.error("Camera Error:", err);
                statusMsg.textContent = "Gagal akses kamera. Pastikan izin diberikan.";
                statusMsg.style.color = "var(--danger)";
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

                        // Update Hidden Input
                        document.getElementById('lat').value = userLat;
                        document.getElementById('lng').value = userLng;

                        // Visual Feedback
                        if (acc <= 100) {
                            isLocationValid = true;
                            gpsText.textContent = `Akurat (±${Math.round(acc)}m)`;
                            gpsDot.style.backgroundColor = "var(--success)"; // Hijau
                        } else {
                            isLocationValid = true; // Tetap boleh absen tapi warning
                            gpsText.textContent = `Lemah (±${Math.round(acc)}m)`;
                            gpsDot.style.backgroundColor = "#f59e0b"; // Kuning
                        }
                        checkReadiness();
                    },
                    (error) => {
                        console.error("GPS Error:", error);
                        gpsText.textContent = "GPS Error";
                        gpsDot.style.backgroundColor = "var(--danger)";
                        statusMsg.textContent = "Aktifkan GPS & Izin Lokasi di Browser.";
                    },
                    {
                        enableHighAccuracy: true, // Wajib ON untuk presensi
                        timeout: 15000,
                        maximumAge: 0
                    }
                );
            } else {
                statusMsg.textContent = "Browser tidak support GPS.";
            }
        }

        // 3. Cek Kesiapan (Kamera + Lokasi)
        function checkReadiness() {
            if (video.srcObject && isLocationValid) {
                btnCapture.disabled = false;
                btnCapture.innerHTML = `
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    Ambil Foto
                `;
                statusMsg.textContent = "Siap untuk Clock-in.";
            }
        }

        // 4. Fungsi Capture (FIX LAYAR HITAM)
        btnCapture.addEventListener('click', () => {
            if (!video.srcObject) return;

            // Efek Flash
            flashOverlay.classList.add('flash-anim');
            setTimeout(() => flashOverlay.classList.remove('flash-anim'), 500);

            // Set ukuran canvas sama persis dengan source video (KUNCI FIX HITAM)
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            const ctx = canvas.getContext('2d');

            // Mirroring (Flip Horizontal) biar natural
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);

            // Gambar video ke canvas
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert ke Blob untuk dikirim
            canvas.toBlob((blob) => {
                imageBlob = blob;
                const url = URL.createObjectURL(blob);
                
                // Tampilkan hasil
                imgPreview.src = url;
                imgPreview.style.display = 'block';
                video.style.display = 'none'; // Sembunyikan video stream

                // Ganti tombol
                groupCapture.style.display = 'none';
                groupAction.style.display = 'flex';
                statusMsg.textContent = "Foto terambil. Kirim atau Ulangi?";
            }, 'image/jpeg', 0.85); // Quality 85%
        });

        // 5. Fungsi Ulangi (Retake)
        btnRetake.addEventListener('click', () => {
            imgPreview.style.display = 'none';
            video.style.display = 'block';
            imageBlob = null;

            groupCapture.style.display = 'block';
            groupAction.style.display = 'none';
            statusMsg.textContent = "Silakan ambil foto wajah Anda.";
        });

        // 6. Fungsi Submit (Kirim ke Server)
        btnSubmit.addEventListener('click', async () => {
            if (!imageBlob || !userLat) return;

            // Loading state
            const originalText = btnSubmit.innerHTML;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = "Mengirim...";
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
                    // Tampilkan modal sukses
                    const modal = document.getElementById('attendance-success');
                    modal.style.display = 'flex';
                    
                    // Redirect saat tutup modal
                    const closeBtns = modal.querySelectorAll('[data-modal-close="true"]');
                    closeBtns.forEach(btn => {
                        btn.onclick = () => window.location.href = '{{ url("/attendance") }}';
                    });
                } else {
                    alert(data.message || "Gagal melakukan presensi.");
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalText;
                    btnRetake.disabled = false;
                }
            } catch (error) {
                console.error(error);
                alert("Terjadi kesalahan koneksi.");
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;
                btnRetake.disabled = false;
            }
        });

        // Jalankan saat load
        document.addEventListener('DOMContentLoaded', () => {
            startCamera();
            initGPS();
        });
    </script>
</x-app>