@php
    $isClockIn = $mode === 'in';
    $backRoute = $isClockIn ? route('remote-attendance.purpose') : route('remote-attendance.index');
@endphp

<x-app :title="$isClockIn ? 'Foto & Lokasi' : 'Selesaikan Dinas'">
    <x-slot name="header">
        <div class="remote-capture-header">
            <a href="{{ $backRoute }}" class="remote-capture-back" aria-label="Kembali">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1>{{ $isClockIn ? 'Foto & Lokasi' : 'Selesaikan Dinas' }}</h1>
                <p>{{ $isClockIn ? 'Langkah 2 dari 2' : 'Foto & Lokasi' }}</p>
            </div>
        </div>
    </x-slot>

    <main class="remote-capture">
        <div class="remote-capture__status" aria-live="polite">
            <div class="remote-capture-pill is-loading" id="cameraPill">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <circle cx="12" cy="13" r="3" stroke-width="2"/>
                </svg>
                <span id="cameraText">Memuat kamera...</span>
            </div>
            <div class="remote-capture-pill is-loading" id="gpsPill">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11z"/>
                    <circle cx="12" cy="10" r="2.5" stroke-width="2"/>
                </svg>
                <span id="gpsText">Mencari lokasi...</span>
            </div>
        </div>

        <div class="remote-camera">
            <video id="video" autoplay playsinline muted></video>
            <img id="capturePreview" alt="Pratinjau foto dinas luar">
            <canvas id="canvas" hidden></canvas>
            <div class="remote-camera__flash" id="flashOverlay"></div>

            <div class="remote-camera__guide" aria-hidden="true">
                <span class="top-left"></span>
                <span class="top-right"></span>
                <span class="bottom-left"></span>
                <span class="bottom-right"></span>
            </div>

            <div class="remote-camera__location">
                <span id="gpsOverlayDot"></span>
                <span id="gpsOverlayText">Mencari lokasi...</span>
            </div>
        </div>

        <div class="remote-capture__controls">
            <p id="statusMessage">Menyiapkan kamera dan lokasi...</p>

            <div id="captureGroup">
                <button type="button" id="captureButton" class="remote-capture-button" disabled>
                    <svg width="21" height="21" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <circle cx="12" cy="13" r="3" stroke-width="2"/>
                    </svg>
                    Ambil Foto
                </button>
            </div>

            <div id="submitGroup" class="remote-capture__actions" hidden>
                <button type="button" id="retakeButton" class="remote-capture-button remote-capture-button--secondary">Ulangi</button>
                <button type="button" id="submitButton" class="remote-capture-button">
                    {{ $isClockIn ? 'Mulai Dinas' : 'Selesai Tugas' }}
                </button>
            </div>
        </div>
    </main>

    <x-modal id="remote-success" title="Berhasil" variant="success" type="info" cancelLabel="Tutup">
        <div style="text-align:center;">
            <p style="margin:0 0 5px; font-weight:700; color:var(--text-primary);">
                {{ $isClockIn ? 'Dinas luar dimulai' : 'Dinas luar selesai' }}
            </p>
            <p style="margin:0; color:var(--text-muted); font-size:.8125rem;">Data Anda sudah tercatat.</p>
        </div>
    </x-modal>

    @if ($isClockIn && $officeHoliday)
        <x-modal id="remote-office-holiday-confirmation" title="Hari Libur Kantor" variant="warning" type="form">
            <p style="margin:0 0 16px;">Hari ini adalah <strong>{{ $officeHoliday->name }}</strong>. Apakah Anda yakin ingin tetap melakukan presensi?</p>
            <div style="display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;">
                <button type="button" class="modal-btn modal-btn-secondary" data-modal-close="true">Batal</button>
                <button type="button" id="confirmRemoteOfficeHolidayClockIn" class="modal-btn modal-btn-primary">Ya, Tetap Absen</button>
            </div>
        </x-modal>
    @endif

    <style>
        .remote-capture-header { display:flex; align-items:center; gap:14px; }
        .remote-capture-back { width:48px; height:48px; display:grid; place-items:center; flex:0 0 auto; border:1px solid var(--border); border-radius:14px; background:var(--white); color:var(--primary-dark); box-shadow:0 4px 12px rgba(15,23,42,.07); }
        .remote-capture-back:focus-visible { outline:3px solid rgba(20,93,160,.25); outline-offset:2px; }
        .remote-capture-header h1 { margin:0; font-size:1.25rem; font-weight:700; line-height:1.2; }
        .remote-capture-header p { margin:3px 0 0; color:var(--text-muted); font-size:.8125rem; }
        .remote-capture { width:100%; max-width:520px; min-height:650px; display:flex; flex-direction:column; gap:12px; margin:0 auto; }
        .remote-capture__status { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }
        .remote-capture-pill { min-height:42px; display:flex; align-items:center; justify-content:center; gap:7px; padding:8px 10px; border:1px solid #cfe7d8; border-radius:14px; background:#edf7f1; color:#27704a; font-size:.75rem; font-weight:600; }
        .remote-capture-pill.is-loading { border-color:#eadfbd; background:#faf6eb; color:#946b18; }
        .remote-capture-pill.is-error { border-color:#f2cccc; background:#fdf1f1; color:#b42318; }
        .remote-camera { position:relative; flex:1 1 auto; min-height:440px; overflow:hidden; border-radius:20px; background:#070b17; box-shadow:inset 0 0 0 1px rgba(255,255,255,.08); }
        .remote-camera video, .remote-camera img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
        .remote-camera img { display:none; }
        .remote-camera img.is-visible { display:block; }
        .remote-camera__flash { position:absolute; inset:0; z-index:20; pointer-events:none; background:#fff; opacity:0; }
        .remote-camera__flash.is-active { animation:remoteFlash .4s ease-out; }
        @keyframes remoteFlash { from { opacity:1; } to { opacity:0; } }
        .remote-camera__guide { position:absolute; top:50%; left:50%; z-index:5; width:min(78%,330px); height:68%; transform:translate(-50%,-50%); pointer-events:none; }
        .remote-camera__guide span { position:absolute; width:48px; height:48px; border-color:rgba(255,255,255,.92); border-style:solid; border-width:0; }
        .remote-camera__guide .top-left { top:0; left:0; border-top-width:3px; border-left-width:3px; border-radius:16px 0 0; }
        .remote-camera__guide .top-right { top:0; right:0; border-top-width:3px; border-right-width:3px; border-radius:0 16px 0 0; }
        .remote-camera__guide .bottom-left { bottom:0; left:0; border-bottom-width:3px; border-left-width:3px; border-radius:0 0 0 16px; }
        .remote-camera__guide .bottom-right { right:0; bottom:0; border-right-width:3px; border-bottom-width:3px; border-radius:0 0 16px; }
        .remote-camera__location { position:absolute; bottom:16px; left:50%; z-index:10; display:flex; align-items:center; gap:7px; padding:7px 12px; transform:translateX(-50%); border:1px solid rgba(255,255,255,.12); border-radius:999px; background:rgba(3,7,18,.72); color:#fff; font-size:.75rem; font-weight:600; white-space:nowrap; backdrop-filter:blur(6px); }
        .remote-camera__location span:first-child { width:8px; height:8px; flex:0 0 auto; border-radius:50%; background:#f59e0b; }
        .remote-capture__controls { display:grid; gap:8px; flex:0 0 auto; }
        .remote-capture__controls > p { min-height:18px; margin:0; color:var(--text-muted); font-size:.75rem; font-weight:500; text-align:center; }
        .remote-capture-button { width:100%; min-height:64px; display:flex; align-items:center; justify-content:center; gap:8px; padding:13px 18px; border:1px solid transparent; border-radius:14px; background:var(--primary-dark); color:#fff; font:inherit; font-size:1rem; font-weight:700; cursor:pointer; box-shadow:0 6px 16px rgba(10,61,98,.18); }
        .remote-capture-button:disabled { background:#aab3bf; box-shadow:none; cursor:not-allowed; }
        .remote-capture-button--secondary { border-color:var(--border); background:#f8fafc; color:var(--text-secondary); box-shadow:none; }
        .remote-capture__actions { display:grid; grid-template-columns:minmax(105px,.55fr) minmax(0,1fr); gap:10px; }
        .remote-capture__actions[hidden] { display:none; }
        @media (max-width:767px) {
            .main-content, .content-wrapper { overflow:hidden; }
            .content-wrapper { height:100dvh; display:flex; flex-direction:column; padding:16px 16px max(16px,env(safe-area-inset-bottom)); }
            .topbar { flex:0 0 auto; margin-bottom:16px; }
            .burger { display:none; }
            .remote-capture { flex:1 1 auto; min-height:0; }
            .remote-camera { min-height:0; }
        }
        @media (max-width:359px), (max-height:700px) and (max-width:767px) {
            .content-wrapper { padding-top:10px; padding-bottom:max(10px,env(safe-area-inset-bottom)); }
            .topbar { margin-bottom:10px; }
            .remote-capture-header { gap:10px; }
            .remote-capture-back { width:44px; height:44px; }
            .remote-capture-header h1 { font-size:1.0625rem; }
            .remote-capture-header p { font-size:.6875rem; }
            .remote-capture { gap:8px; }
            .remote-capture-pill { min-height:34px; padding:5px 7px; font-size:.625rem; }
            .remote-camera { border-radius:16px; }
            .remote-camera__location { bottom:10px; padding:5px 9px; font-size:.6875rem; }
            .remote-capture-button { min-height:56px; font-size:.875rem; }
        }
    </style>

    <script>
        const mode = @json($mode);
        const notesKey = 'remoteAttendanceNotes';
        const notes = sessionStorage.getItem(notesKey) || '';

        if (mode === 'in' && !notes.trim()) {
            window.location.replace(@json(route('remote-attendance.purpose')));
        }

        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const preview = document.getElementById('capturePreview');
        const flash = document.getElementById('flashOverlay');
        const cameraPill = document.getElementById('cameraPill');
        const cameraText = document.getElementById('cameraText');
        const gpsPill = document.getElementById('gpsPill');
        const gpsText = document.getElementById('gpsText');
        const gpsOverlayText = document.getElementById('gpsOverlayText');
        const gpsOverlayDot = document.getElementById('gpsOverlayDot');
        const statusMessage = document.getElementById('statusMessage');
        const captureButton = document.getElementById('captureButton');
        const retakeButton = document.getElementById('retakeButton');
        const submitButton = document.getElementById('submitButton');
        const captureGroup = document.getElementById('captureGroup');
        const submitGroup = document.getElementById('submitGroup');
        const officeHolidayModal = document.getElementById('remote-office-holiday-confirmation');
        const confirmRemoteOfficeHolidayClockIn = document.getElementById('confirmRemoteOfficeHolidayClockIn');

        let stream = null;
        let watchId = null;
        let photoBlob = null;
        let latitude = null;
        let longitude = null;
        let locationReady = false;
        let cameraReady = false;

        function checkReady() {
            captureButton.disabled = !(cameraReady && locationReady);
            if (cameraReady && locationReady) {
                statusMessage.textContent = 'Siap untuk mengambil foto.';
                statusMessage.style.color = 'var(--success)';
            }
        }

        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 960 } },
                    audio: false
                });
                video.srcObject = stream;
                video.onloadedmetadata = () => {
                    cameraReady = true;
                    cameraPill.className = 'remote-capture-pill';
                    cameraText.textContent = 'Kamera aktif';
                    checkReady();
                };
            } catch (error) {
                cameraPill.className = 'remote-capture-pill is-error';
                cameraText.textContent = 'Kamera error';
                statusMessage.textContent = 'Izinkan akses kamera untuk melanjutkan.';
                statusMessage.style.color = 'var(--error)';
            }
        }

        function startLocation() {
            if (!navigator.geolocation) {
                gpsPill.className = 'remote-capture-pill is-error';
                gpsText.textContent = 'GPS tidak tersedia';
                return;
            }

            watchId = navigator.geolocation.watchPosition((position) => {
                latitude = position.coords.latitude;
                longitude = position.coords.longitude;
                locationReady = true;
                const accuracy = Math.round(position.coords.accuracy);
                const label = `${accuracy <= 100 ? 'Akurat' : 'Lemah'} (±${accuracy}m)`;

                gpsPill.className = `remote-capture-pill${accuracy <= 100 ? '' : ' is-loading'}`;
                gpsText.textContent = label;
                gpsOverlayText.textContent = `Lokasi ${label.toLowerCase()}`;
                gpsOverlayDot.style.background = accuracy <= 100 ? 'var(--success)' : 'var(--warning)';
                checkReady();
            }, () => {
                gpsPill.className = 'remote-capture-pill is-error';
                gpsText.textContent = 'GPS error';
                gpsOverlayText.textContent = 'Lokasi tidak tersedia';
                gpsOverlayDot.style.background = 'var(--error)';
                statusMessage.textContent = 'Izinkan akses lokasi untuk melanjutkan.';
                statusMessage.style.color = 'var(--error)';
            }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
        }

        captureButton.addEventListener('click', () => {
            if (!video.videoWidth) return;

            flash.classList.add('is-active');
            setTimeout(() => flash.classList.remove('is-active'), 450);

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            canvas.toBlob((blob) => {
                if (!blob) return;
                photoBlob = blob;
                preview.src = URL.createObjectURL(blob);
                preview.classList.add('is-visible');
                video.style.display = 'none';
                captureGroup.hidden = true;
                submitGroup.hidden = false;
                statusMessage.textContent = 'Foto siap dikirim.';
                statusMessage.style.color = 'var(--text-muted)';
            }, 'image/jpeg', .85);
        });

        retakeButton.addEventListener('click', () => {
            if (preview.src) URL.revokeObjectURL(preview.src);
            preview.classList.remove('is-visible');
            preview.removeAttribute('src');
            video.style.display = 'block';
            photoBlob = null;
            submitGroup.hidden = true;
            captureGroup.hidden = false;
            checkReady();
        });

        async function submitRemoteAttendance() {
            if (!photoBlob || latitude === null || longitude === null) return;

            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            retakeButton.disabled = true;
            submitButton.textContent = 'Mengirim...';

            const payload = new FormData();
            payload.append('_token', @json(csrf_token()));
            payload.append('photo', photoBlob, mode === 'in' ? 'remote-in.jpg' : 'remote-out.jpg');
            payload.append('lat', latitude);
            payload.append('lng', longitude);
            if (mode === 'in') payload.append('notes', notes);

            try {
                const response = await fetch(mode === 'in' ? @json(route('remote-attendance.clockIn')) : @json(route('remote-attendance.clockOut')), {
                    method: 'POST',
                    body: payload,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();

                if (!response.ok) throw new Error(data.message || 'Data gagal dikirim.');

                if (mode === 'in') sessionStorage.removeItem(notesKey);
                const modal = document.getElementById('remote-success');
                modal.style.display = 'flex';
                modal.querySelectorAll('[data-modal-close="true"]').forEach((button) => {
                    button.onclick = () => window.location.href = @json(route('remote-attendance.index'));
                });
            } catch (error) {
                window.showToast(error.message || 'Gagal terhubung ke server.', 'error');
                submitButton.disabled = false;
                retakeButton.disabled = false;
                submitButton.textContent = originalText;
            }
        }

        submitButton.addEventListener('click', () => {
            if (!photoBlob || latitude === null || longitude === null) return;

            if (mode === 'in' && officeHolidayModal) {
                officeHolidayModal.style.display = 'flex';
                return;
            }

            submitRemoteAttendance();
        });

        confirmRemoteOfficeHolidayClockIn?.addEventListener('click', () => {
            officeHolidayModal.style.display = 'none';
            submitRemoteAttendance();
        });

        officeHolidayModal?.querySelectorAll('[data-modal-close="true"]').forEach((button) => {
            button.addEventListener('click', () => {
                officeHolidayModal.style.display = 'none';
            });
        });

        window.addEventListener('pagehide', () => {
            if (stream) stream.getTracks().forEach((track) => track.stop());
            if (watchId !== null) navigator.geolocation.clearWatch(watchId);
        });

        document.addEventListener('DOMContentLoaded', () => {
            startCamera();
            startLocation();
        });
    </script>
</x-app>
