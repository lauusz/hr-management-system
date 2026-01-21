<x-app title="Dinas Luar (Remote)">

    {{-- 
        CSS INI MENGADOPSI STYLE DARI clock_in.blade.php 
        AGAR TAMPILAN KONSISTEN (ATTENDANCE CARD STYLE)
    --}}
    <style>
        :root {
            --primary: #2563eb;
            --success: #16a34a;
            --danger: #dc2626;
            --warning: #f59e0b;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #94a3b8;
        }

        /* --- DASHBOARD STYLES --- */
        .card { background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); overflow: hidden; border: 1px solid #f3f4f6; margin-bottom: 20px; }
        .bg-gradient { background: linear-gradient(135deg, #1e4a8d 0%, #163a75 100%); padding: 24px; border: none; }
        .card-header { padding: 20px; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center; }
        .card-title { margin: 0; font-size: 1.1rem; font-weight: 700; color: #1f2937; }
        .card-body { padding: 20px; }

        .status-detail-box { display: flex; flex-direction: column; gap: 16px; }
        .detail-row { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f3f4f6; padding-bottom: 12px; }
        .detail-row:last-child { border-bottom: none; padding-bottom: 0; }
        .detail-label { font-size: 0.85rem; color: #6b7280; font-weight: 600; }
        .detail-value { font-size: 1rem; color: #1f2937; font-weight: 700; }
        .note-box { background: #f9fafb; padding: 12px; border-radius: 8px; font-size: 0.9rem; color: #374151; line-height: 1.5; border: 1px solid #e5e7eb; margin-top: 4px; }
        .rejection-alert { background: #fef2f2; border: 1px solid #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; font-size: 0.9rem; margin-top: 10px; }

        .badge-status { padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block; }
        .bg-green { background: #dcfce7; color: #166534; }
        .bg-red { background: #fee2e2; color: #991b1b; }
        .bg-yellow { background: #fef3c7; color: #92400e; }
        .bg-gray { background: #f3f4f6; color: #6b7280; }
        
        .empty-state-box { text-align: center; padding: 30px 20px; color: #9ca3af; }
        .empty-icon { width: 48px; height: 48px; margin-bottom: 10px; opacity: 0.5; }

        .action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 8px; }
        .btn-clock { display: flex; align-items: center; gap: 16px; padding: 20px; border-radius: 16px; transition: transform 0.2s, box-shadow 0.2s; position: relative; overflow: hidden; border: none; cursor: pointer; text-align: left; width: 100%; font-family: inherit; }
        .btn-clock:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
        .btn-in { background: #1e4a8d; color: #fff; }
        .btn-out { background: #fff; border: 2px solid #e5e7eb; color: #374151; }
        .btn-clock.disabled { opacity: 0.5; pointer-events: none; filter: grayscale(100%); cursor: not-allowed; }
        .icon-circle { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
        .btn-in .icon-circle { background: rgba(255,255,255,0.2); }
        .btn-out .icon-circle { background: #f3f4f6; color: #1f2937; }
        .btn-text { display: flex; flex-direction: column; }
        .btn-title { font-size: 1.1rem; font-weight: 700; line-height: 1.2; }
        .btn-desc { font-size: 0.85rem; opacity: 0.8; margin-top: 2px; font-weight: 400; }

        /* ============================================================
           MODAL STYLING (MENGADOPSI STYLE CLOCK_IN.BLADE.PHP)
           ============================================================ */
        .modal-backdrop { 
            display: none; 
            position: fixed; 
            inset: 0; 
            background: rgba(0,0,0,0.6); 
            z-index: 9999; 
            align-items: center; /* Center Vertikal */
            justify-content: center; /* Center Horizontal */
            padding: 16px; 
            backdrop-filter: blur(4px); 
        }
        
        .modal-content { 
            background: white; 
            width: 100%; 
            max-width: 500px; /* Lebar sama dengan clock_in card */
            border-radius: 20px; /* Radius sama dengan clock_in */
            overflow: hidden; 
            display: flex; 
            flex-direction: column; 
            max-height: 95vh; /* Sedikit lebih tinggi agar muat di HP */
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        .modal-header { 
            padding: 16px 20px; 
            border-bottom: 1px solid #f1f5f9; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            background: white;
            flex-shrink: 0;
        }
        .modal-title-text { font-weight: 700; font-size: 1.1rem; color: var(--dark); }
        
        /* Body bisa di-scroll jika konten panjang (Camera + Notes + Buttons) */
        .modal-body { 
            padding: 0; 
            overflow-y: auto; 
            background: white;
            display: flex;
            flex-direction: column;
        }

        #notesSection { padding: 20px 20px 0; }

        /* --- CAMERA WRAPPER (FIXED HEIGHT LIKE CLOCK_IN) --- */
        .camera-wrapper { 
            position: relative; 
            width: 100%; 
            background: black;
            /* Height disamakan dengan clock_in agar konsisten */
            height: 400px; 
            flex-shrink: 0; /* Jangan menyusut */
            margin-top: 15px;
        }

        /* Base Video & Preview Styles */
        video, #capturePreview { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; /* KUNCI: Gambar mengisi penuh area 400px */
            display: block; 
            position: absolute;
            inset: 0;
        }

        /* [UPDATED] MIRROR EFFECT KHUSUS VIDEO */
        video {
            transform: scaleX(-1);
        }

        #capturePreview { display: none; z-index: 5; }

        .controls-area { 
            padding: 20px; 
            background: white; 
        }

        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--dark); margin-bottom: 6px; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; font-family: inherit; font-size: 0.95rem; resize: none; }
        
        /* BUTTONS (STYLE CLOCK_IN) */
        .btn-main { width: 100%; padding: 14px; border: none; border-radius: 16px; font-size: 1rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s; }
        .btn-capture { background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); }
        .btn-capture:active { transform: scale(0.98); }
        .btn-secondary { background: #f1f5f9; color: var(--dark); flex: 1; }
        .btn-success { background: var(--success); color: white; flex: 1; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3); }
        .btn-main:disabled { opacity: 0.6; cursor: not-allowed; filter: grayscale(1); }

        /* GPS Badge */
        .gps-badge { position: absolute; top: 15px; left: 50%; transform: translateX(-50%); background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px); padding: 6px 14px; border-radius: 30px; display: flex; align-items: center; gap: 8px; z-index: 10; border: 1px solid rgba(255,255,255,0.1); }
        .gps-dot { width: 8px; height: 8px; border-radius: 50%; background-color: var(--danger); animation: pulse 1.5s infinite; }
        .gps-text { color: white; font-size: 0.75rem; font-weight: 600; }
        .flash { position: absolute; inset: 0; background: white; z-index: 20; opacity: 0; pointer-events: none; }
        .flash-anim { animation: flashEffect 0.4s ease-out; }
        @keyframes pulse { 0% { transform: scale(0.95); opacity: 0.7; } 50% { transform: scale(1.1); opacity: 1; } 100% { transform: scale(0.95); opacity: 0.7; } }
        @keyframes flashEffect { 0% { opacity: 1; } 100% { opacity: 0; } }
        
        .status-message { text-align: center; font-size: 0.9rem; color: var(--gray); margin-bottom: 15px; min-height: 20px; }

        /* Responsive Mobile Tweak */
        @media (max-width: 640px) {
            .action-grid { grid-template-columns: 1fr; }
            .modal-content {
                width: 95%;
                margin: 0 auto;
                max-height: 90vh;
            }
            /* Di HP kecil, tinggi kamera sedikit disesuaikan agar tombol muat */
            .camera-wrapper {
                height: 350px; 
            }
        }
    </style>

    <div style="max-width:600px; margin:0 auto; display:flex; flex-direction:column; gap:24px;">

        <div class="card bg-gradient">
            <div style="color:#fff;">
                <h2 style="margin:0; font-size:1.5rem; font-weight:700;">Halo, {{ auth()->user()->name }}!</h2>
                <p style="margin:4px 0 0; opacity:0.9; font-size:0.95rem;">
                    {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}
                </p>
                <div style="margin-top:12px; background:rgba(255,255,255,0.2); padding:6px 12px; border-radius:8px; font-size:0.8rem; display:inline-flex; align-items:center; gap:6px; width:fit-content;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Mode: Dinas Luar (Bebas Radius)
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Status Pengajuan</h3>
                @if($todayAttendance)
                    @if($todayAttendance->approval_status === 'PENDING')
                        <span class="badge-status bg-yellow">Menunggu Approval</span>
                    @elseif($todayAttendance->approval_status === 'REJECTED')
                        <span class="badge-status bg-red">Ditolak</span>
                    @elseif($todayAttendance->approval_status === 'APPROVED')
                        <span class="badge-status bg-green">Disetujui</span>
                    @endif
                @else
                    <span class="badge-status bg-gray">Belum Ada</span>
                @endif
            </div>

            <div class="card-body">
                @if($todayAttendance)
                    <div class="status-detail-box">
                        <div class="detail-row">
                            <span class="detail-label">Waktu Mulai</span>
                            <span class="detail-value text-primary">
                                {{ $todayAttendance->clock_in_at ? \Carbon\Carbon::parse($todayAttendance->clock_in_at)->format('H:i') : '-' }}
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Waktu Selesai</span>
                            <span class="detail-value {{ $todayAttendance->clock_out_at ? 'text-primary' : 'text-muted' }}">
                                {{ $todayAttendance->clock_out_at ? \Carbon\Carbon::parse($todayAttendance->clock_out_at)->format('H:i') : '-' }}
                            </span>
                        </div>
                        <div class="note-container">
                            <span class="detail-label" style="display:block; margin-bottom:6px;">Keperluan / Lokasi:</span>
                            <div class="note-box">
                                {{ $todayAttendance->notes ?? '-' }}
                            </div>
                        </div>
                        @if($todayAttendance->approval_status === 'REJECTED')
                            <div class="rejection-alert">
                                <strong>Pengajuan Ditolak:</strong><br>
                                {{ $todayAttendance->rejection_note ?? 'Tidak ada keterangan.' }}
                            </div>
                        @endif
                    </div>
                @else
                    <div class="empty-state-box">
                        <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <p>Anda belum membuat pengajuan dinas luar hari ini.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="action-grid">
            <button type="button" 
                class="btn-clock btn-in {{ $todayAttendance ? 'disabled' : '' }}" 
                onclick="{{ !$todayAttendance ? "openModal('in')" : '' }}">
                <div class="icon-circle">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                </div>
                <div class="btn-text">
                    <span class="btn-title">Mulai Dinas</span>
                    <span class="btn-desc">Catat jam & lokasi</span>
                </div>
            </button>

            <button type="button" 
                class="btn-clock btn-out {{ (!$todayAttendance || $todayAttendance->clock_out_at) ? 'disabled' : '' }}"
                onclick="{{ ($todayAttendance && !$todayAttendance->clock_out_at) ? "openModal('out')" : '' }}">
                <div class="icon-circle">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </div>
                <div class="btn-text">
                    <span class="btn-title">Selesai Tugas</span>
                    <span class="btn-desc">Lapor selesai kerja</span>
                </div>
            </button>
        </div>

    </div>

    {{-- MODAL CAMERA (STRUKTUR SAMA SEPERTI CLOCK_IN CARD) --}}
    <div id="cameraModal" class="modal-backdrop">
        <div class="modal-content">
            <div class="modal-header">
                <span id="modalTitle" class="modal-title-text">Form Dinas Luar</span>
                <button type="button" onclick="closeCameraModal()" style="background:none; border:none; cursor:pointer; padding:4px; color: #64748b;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="modal-body">
                <div id="notesSection">
                    <div class="form-group">
                        <label>Keperluan / Lokasi Tugas <span style="color:var(--danger)">*</span></label>
                        <textarea id="notesInput" class="form-control" rows="2" placeholder="Contoh: Meeting di PT. ABC"></textarea>
                    </div>
                </div>

                <div class="camera-wrapper">
                    <div class="gps-badge">
                        <div class="gps-dot" id="gpsIndicator"></div>
                        <span class="gps-text" id="gpsText">Mencari Lokasi...</span>
                    </div>

                    <video id="video" autoplay playsinline muted></video>
                    <img id="capturePreview">
                    <div id="flashOverlay" class="flash"></div>
                    <canvas id="canvas" style="display:none;"></canvas>
                </div>

                <div class="controls-area">
                    <div class="status-message" id="statusMessage">
                        Menyiapkan kamera...
                    </div>
                    
                    <div id="groupCapture">
                        <button type="button" id="btnCapture" class="btn-main btn-capture" disabled>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                            Ambil Foto
                        </button>
                    </div>

                    <div id="groupAction" style="display:none; gap:10px; display:flex;">
                        <button type="button" id="btnRetake" class="btn-main btn-secondary">Ulangi</button>
                        <button type="button" id="btnSubmit" class="btn-main btn-success">Kirim Absen</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL SUKSES --}}
    <x-modal id="success-modal" title="Berhasil!" type="info" cancelLabel="Tutup">
        <div style="text-align: center;">
            <div style="background:#ecfdf5; width:60px; height:60px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                <svg width="32" height="32" fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <p style="font-size: 1.1rem; font-weight: 600; color: #1f2937;">Pengajuan Berhasil!</p>
            <p style="font-size: 0.9rem; color: #6b7280; margin-top: 4px;">Data Anda telah dikirim untuk diproses.</p>
        </div>
    </x-modal>

    {{-- JAVASCRIPT --}}
    <script>
        let currentType = 'in'; 
        let stream = null;
        let imageBlob = null;
        let userLat = null, userLng = null;
        let isLocationValid = false;
        let watchId = null;

        const modal = document.getElementById('cameraModal');
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const imgPreview = document.getElementById('capturePreview');
        const flashOverlay = document.getElementById('flashOverlay');
        const statusMsg = document.getElementById('statusMessage');
        const notesSection = document.getElementById('notesSection');
        const notesInput = document.getElementById('notesInput');
        const gpsText = document.getElementById('gpsText');
        const gpsDot = document.getElementById('gpsIndicator');
        const btnCapture = document.getElementById('btnCapture');
        const btnRetake = document.getElementById('btnRetake');
        const btnSubmit = document.getElementById('btnSubmit');
        const groupCapture = document.getElementById('groupCapture');
        const groupAction = document.getElementById('groupAction');

        function toggleActionButtons(show) {
            if(show) {
                groupCapture.style.display = 'none';
                groupAction.style.display = 'flex';
            } else {
                groupCapture.style.display = 'block';
                groupAction.style.display = 'none';
            }
        }

        function openModal(type) {
            currentType = type;
            document.getElementById('modalTitle').innerText = (type === 'in') ? 'Form Dinas Luar' : 'Selesai Tugas';
            
            if(type === 'in') {
                notesSection.style.display = 'block';
                notesInput.value = '';
            } else {
                notesSection.style.display = 'none';
            }

            modal.style.display = 'flex';
            resetUI();
            startCamera();
            initGPS();
        }

        function closeCameraModal() {
            modal.style.display = 'none';
            stopHardware();
        }

        function resetUI() {
            video.style.display = 'block';
            imgPreview.style.display = 'none';
            toggleActionButtons(false);
            btnCapture.disabled = true;
            statusMsg.innerText = "Menyiapkan lokasi...";
            imageBlob = null;
        }

        async function startCamera() {
            try {
                // Constraints: Meminta 1280x720 ideal
                const s = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } } 
                });
                stream = s;
                video.srcObject = stream;
                video.onloadedmetadata = checkReadiness;
            } catch (err) {
                statusMsg.innerText = "Gagal akses kamera.";
                statusMsg.style.color = "var(--danger)";
            }
        }

        function stopHardware() {
            if (stream) stream.getTracks().forEach(t => t.stop());
            if (watchId) navigator.geolocation.clearWatch(watchId);
            stream = null;
            watchId = null;
        }

        function initGPS() {
            if (navigator.geolocation) {
                watchId = navigator.geolocation.watchPosition(
                    (pos) => {
                        userLat = pos.coords.latitude;
                        userLng = pos.coords.longitude;
                        const acc = pos.coords.accuracy;

                        if (acc <= 100) {
                            isLocationValid = true;
                            gpsText.innerText = `Akurat (±${Math.round(acc)}m)`;
                            gpsDot.style.backgroundColor = "var(--success)";
                        } else {
                            isLocationValid = true; 
                            gpsText.innerText = `Lemah (±${Math.round(acc)}m)`;
                            gpsDot.style.backgroundColor = "var(--warning)";
                        }
                        checkReadiness();
                    },
                    (err) => {
                        gpsText.innerText = "GPS Error";
                        gpsDot.style.backgroundColor = "var(--danger)";
                        statusMsg.innerText = "Aktifkan GPS!";
                    },
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                );
            }
        }

        function checkReadiness() {
            if (video.srcObject && isLocationValid) {
                btnCapture.disabled = false;
                statusMsg.innerText = "Siap mengambil foto.";
                statusMsg.style.color = "#64748b";
            }
        }

        btnCapture.addEventListener('click', () => {
            if (!video.srcObject) return;

            flashOverlay.classList.add('flash-anim');
            setTimeout(() => flashOverlay.classList.remove('flash-anim'), 500);

            // Canvas size match video source size
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');

            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(blob => {
                imageBlob = blob;
                imgPreview.src = URL.createObjectURL(blob);
                video.style.display = 'none';
                imgPreview.style.display = 'block';
                
                toggleActionButtons(true);
                statusMsg.innerText = "Foto tersimpan. Kirim sekarang?";
            }, 'image/jpeg', 0.85);
        });

        btnRetake.addEventListener('click', () => {
            imgPreview.style.display = 'none';
            video.style.display = 'block';
            imageBlob = null;
            toggleActionButtons(false);
            checkReadiness();
        });

        btnSubmit.addEventListener('click', async () => {
            if (!imageBlob || !userLat) return;

            if (currentType === 'in' && !notesInput.value.trim()) {
                alert("Harap isi Keterangan/Keperluan Dinas!");
                return;
            }

            const btnOriginal = btnSubmit.innerText;
            btnSubmit.disabled = true;
            btnSubmit.innerText = "Mengirim...";
            btnRetake.disabled = true;

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('lat', userLat);
            formData.append('lng', userLng);
            formData.append('photo', imageBlob, (currentType === 'in' ? 'in.jpg' : 'out.jpg'));
            
            if(currentType === 'in') {
                formData.append('notes', notesInput.value);
            }

            const url = (currentType === 'in') 
                ? "{{ route('remote-attendance.clockIn') }}"
                : "{{ route('remote-attendance.clockOut') }}";

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();

                if (res.ok) {
                    const successModal = document.getElementById('success-modal');
                    closeCameraModal(); 
                    successModal.style.display = 'flex';
                    const closeBtns = successModal.querySelectorAll('[data-modal-close="true"]');
                    closeBtns.forEach(btn => {
                        btn.onclick = () => window.location.reload();
                    });
                } else {
                    alert(data.error || "Terjadi kesalahan.");
                    btnSubmit.disabled = false;
                    btnSubmit.innerText = btnOriginal;
                    btnRetake.disabled = false;
                }
            } catch (err) {
                alert("Gagal koneksi ke server.");
                btnSubmit.disabled = false;
                btnSubmit.innerText = btnOriginal;
                btnRetake.disabled = false;
            }
        });
    </script>
</x-app>