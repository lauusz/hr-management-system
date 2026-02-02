<x-app title="Permintaan Approval Dinas Luar">

    {{-- CSS Khusus Halaman Approval --}}
    <style>
        /* Card & Layout */
        .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 20px; }
        .container-xl { padding-top: 20px; max-width: 1200px; margin: 0 auto; }
        
        /* Table Styles */
        .table-responsive { width: 100%; overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; min-width: 900px; }
        .table th { background: #f8fafc; padding: 14px 16px; text-align: left; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0; }
        .table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #1e293b; font-size: 0.9rem; }
        .table tr:last-child td { border-bottom: none; }
        .table tr:hover td { background: #fcfcfc; }

        /* User Info Column */
        .user-name { font-weight: 700; color: #1e293b; font-size: 0.95rem; }
        .user-role { font-size: 0.75rem; color: #64748b; margin-top: 2px; text-transform: uppercase; font-weight: 600; }

        /* Buttons & Badges */
        .btn-pill { padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; border: 1px solid transparent; transition: all 0.2s; }
        .btn-blue { background: #eef2ff; color: #1e4a8d; border-color: #e0e7ff; }
        .btn-blue:hover { background: #1e4a8d; color: #fff; }
        .btn-sky { background: #f0f9ff; color: #0369a1; border-color: #e0f2fe; }
        .btn-sky:hover { background: #0369a1; color: #fff; }

        .btn-action { border: none; padding: 8px 14px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; display: inline-flex; gap: 6px; align-items: center; transition: 0.2s; }
        .btn-approve { background: #dcfce7; color: #166534; }
        .btn-approve:hover { background: #bbf7d0; transform: translateY(-1px); }
        .btn-reject { background: #fee2e2; color: #991b1b; }
        .btn-reject:hover { background: #fecaca; transform: translateY(-1px); }

        /* Note Box */
        .note-box { background: #f8fafc; padding: 8px 12px; border-radius: 8px; border: 1px solid #f1f5f9; font-size: 0.85rem; color: #475569; line-height: 1.5; max-width: 280px; }

        /* Modal Styles (Custom for Reject Form which has textarea) */
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
        .modal-content-custom { background: white; width: 100%; max-width: 420px; padding: 24px; border-radius: 16px; animation: pop 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
        @keyframes pop { from{transform:scale(0.9);opacity:0;} to{transform:scale(1);opacity:1;} }
        
        .form-control { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; margin-bottom: 16px; font-size: 0.9rem; outline: none; transition: border-color 0.2s; }
        .form-control:focus { border-color: #2563eb; }
    </style>

    <div class="container-xl">
        
        <div style="margin-bottom: 24px;">
            <p style="color:#64748b; margin:4px 0 0; font-size:0.95rem;">Validasi kehadiran Dinas Luar (Remote Attendance).</p>
        </div>

        @if(session('success'))
            <div style="background:#ecfdf5; color:#065f46; padding:14px 16px; border-radius:10px; margin-bottom:20px; font-weight:600; border:1px solid #a7f3d0; display:flex; align-items:center; gap:10px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="card">
            @if($pendingAttendances->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th>Waktu & Tanggal</th>
                                <th>Bukti Validasi</th>
                                <th>Lokasi Maps</th>
                                <th>Keperluan / Notes</th>
                                <th style="text-align:right;">Keputusan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingAttendances as $item)
                            <tr>
                                <td>
                                    <div class="user-name">{{ $item->user->name }}</div>
                                    <div class="user-role">{{ $item->user->role instanceof \App\Enums\UserRole ? $item->user->role->label() : $item->user->role }}</div>
                                </td>

                                <td>
                                    <div style="font-weight:600; font-size:0.95rem;">{{ $item->clock_in_at ? $item->clock_in_at->format('H:i') : '-' }}</div>
                                    <div style="font-size:0.8rem; color:#64748b;">{{ $item->date->format('d M Y') }}</div>
                                </td>

                                <td>
                                    @if($item->clock_in_photo)
                                        <button type="button" class="btn-pill btn-blue"
                                            onclick="openPhoto('{{ asset('storage/'.$item->clock_in_photo) }}', '{{ $item->user->name }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            Lihat Foto
                                        </button>
                                    @else
                                        <span style="color:#94a3b8; font-size:0.85rem;">-</span>
                                    @endif
                                </td>

                                <td>
                                    @if($item->clock_in_lat)
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $item->clock_in_lat }},{{ $item->clock_in_lng }}" target="_blank" class="btn-pill btn-sky">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            Cek Lokasi
                                        </a>
                                    @else
                                        <span style="color:#94a3b8; font-size:0.85rem;">-</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="note-box">
                                        {{ $item->notes ?? 'Tidak ada keterangan' }}
                                    </div>
                                </td>

                                <td style="text-align:right;">
                                    <div style="display:flex; gap:8px; justify-content:flex-end;">
                                        {{-- TOMBOL TERIMA (Pake x-modal) --}}
                                        <button type="button" class="btn-action btn-approve" 
                                            onclick="openApproveModal('{{ route('hr.approval_attendance.approve', $item->id) }}', '{{ $item->user->name }}')">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Terima
                                        </button>
                                        
                                        {{-- TOMBOL TOLAK (Custom Modal) --}}
                                        <button type="button" class="btn-action btn-reject" 
                                            onclick="openRejectModal('{{ $item->id }}', '{{ $item->user->name }}')">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            Tolak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="padding:60px; text-align:center; color:#94a3b8;">
                    <div style="background:#f1f5f9; width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                        <svg width="40" height="40" fill="none" stroke="#cbd5e1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 style="margin:0; font-size:1.1rem; color:#475569;">Semua Beres!</h3>
                    <p style="margin:8px 0 0; font-size:0.95rem;">Tidak ada permintaan persetujuan absensi saat ini.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL APPROVE (FIXED: Added confirmFormAction="#") --}}
    <x-modal 
        id="approveModal" 
        title="Konfirmasi Persetujuan" 
        type="confirm" 
        confirmLabel="Ya, Terima" 
        cancelLabel="Batal"
        confirmFormAction="#"
        confirmFormMethod="POST">
        <div style="padding: 10px 0;">
            <p style="margin:0; color:#374151; font-size:1rem;">
                Apakah Anda yakin ingin menerima pengajuan Dinas Luar dari <strong id="approveUserName" style="color:#1e293b;"></strong>?
            </p>
            <p style="margin-top:8px; font-size:0.85rem; color:#6b7280;">
                Absensi akan tercatat sebagai <strong>HADIR</strong>.
            </p>
        </div>
    </x-modal>

    {{-- MODAL REJECT (Custom HTML untuk Input Textarea) --}}
    <div id="rejectModal" class="modal-backdrop">
        <div class="modal-content-custom">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 style="margin:0; font-size:1.2rem; color:#1e293b; font-weight:700;">Tolak Absensi</h3>
                <button onclick="closeRejectModal()" style="background:none; border:none; color:#94a3b8; cursor:pointer;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <p style="margin-top:-10px; margin-bottom:20px; font-size:0.9rem; color:#64748b;">
                Anda akan menolak pengajuan dari <span id="rejectUserName" style="font-weight:700; color:#1e293b;"></span>.
            </p>

            <form id="rejectForm" method="POST">
                @csrf
                <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.85rem; color:#334155;">Alasan Penolakan <span style="color:#dc2626;">*</span></label>
                <textarea name="rejection_note" class="form-control" rows="3" placeholder="Contoh: Foto tidak jelas, Lokasi tidak sesuai..." required></textarea>
                
                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                    <button type="button" onclick="closeRejectModal()" style="border:none; background:#f1f5f9; color:#475569; padding:10px 18px; border-radius:8px; font-weight:600; cursor:pointer;">Batal</button>
                    <button type="submit" style="border:none; background:#dc2626; color:white; padding:10px 18px; border-radius:8px; font-weight:600; cursor:pointer;">Konfirmasi Tolak</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL VIEW PHOTO --}}
    <div id="photoModal" class="modal-backdrop" onclick="closePhotoModal(event)">
        <div class="modal-content-custom" style="max-width:500px; padding:0; overflow:hidden;">
            <div style="padding:16px 20px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
                <h4 id="photoTitle" style="margin:0; font-size:1rem; color:#1e293b;">Foto Bukti</h4>
                <button onclick="document.getElementById('photoModal').style.display='none'" style="border:none; background:none; cursor:pointer; color:#64748b;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div style="padding:20px; background:#000; display:flex; justify-content:center;">
                <img id="photoImg" src="" style="max-width:100%; max-height:60vh; border-radius:4px; object-fit:contain;">
            </div>
        </div>
    </div>

    {{-- JAVASCRIPT LOGIC --}}
    <script>
        // --- Logic Approve Modal (X-Modal) ---
        function openApproveModal(url, name) {
            // Set nama user di text modal
            document.getElementById('approveUserName').innerText = name;
            
            // Cari form di dalam component x-modal dan ubah action-nya
            const modalEl = document.getElementById('approveModal');
            const form = modalEl.querySelector('form');
            if(form) {
                form.action = url;
            } else {
                console.error("Form tidak ditemukan dalam modal approve!");
            }

            // Tampilkan modal
            modalEl.style.display = 'flex';
        }

        // --- Logic Reject Modal (Custom) ---
        function openRejectModal(id, name) {
            document.getElementById('rejectModal').style.display = 'flex';
            document.getElementById('rejectUserName').innerText = name;
            document.getElementById('rejectForm').action = "/hr/approval-attendance/" + id + "/reject";
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }

        // --- Logic Photo Modal ---
        function openPhoto(url, name) {
            document.getElementById('photoModal').style.display = 'flex';
            document.getElementById('photoImg').src = url;
            document.getElementById('photoTitle').innerText = "Bukti: " + name;
        }

        function closePhotoModal(event) {
            if (event.target.id === 'photoModal') {
                document.getElementById('photoModal').style.display = 'none';
            }
        }
    </script>

</x-app>