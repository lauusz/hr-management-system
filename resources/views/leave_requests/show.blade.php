<x-app title="Detail Pengajuan Izin">

    @if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert-error">
        {{ $errors->first() }}
    </div>
    @endif

    {{-- [GLOBAL NORMALIZATION] Pastikan Type selalu string agar pengecekan IF di bawah valid --}}
    @php
        $typeValue = $item->type;
        if ($typeValue instanceof \App\Enums\LeaveType) {
            $typeValue = $typeValue->value;
        }
        $typeValue = (string) $typeValue;
    @endphp

    <div class="card">
        <div class="profile-header">
            <div class="profile-main">
                <div class="profile-avatar">
                    {{ substr($item->user->name, 0, 1) }}
                </div>
                <div class="profile-info">
                    <h2 class="profile-name">{{ $item->user->name }}</h2>
                    <div class="profile-meta">
                        <span class="chip-role">{{ $item->user->role }}</span>
                        <span class="dot">•</span>
                        <span>Diajukan: {{ $item->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>

            @php
                // [LOGIC STATUS]
                $status = $item->status;
                $badgeClass = 'badge-gray';
                $statusLabel = $item->status_label ?? $status; 
                
                if ($status === \App\Models\LeaveRequest::STATUS_APPROVED) {
                    $badgeClass = 'badge-green';
                    $statusLabel = 'Disetujui HRD';
                } elseif ($status === \App\Models\LeaveRequest::STATUS_REJECTED) {
                    $badgeClass = 'badge-red';
                    $statusLabel = 'Ditolak';
                } elseif ($status === \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
                    $badgeClass = 'badge-yellow';
                    $statusLabel = '⏳ Menunggu Persetujuan Atasan';
                } elseif ($status === \App\Models\LeaveRequest::PENDING_HR) {
                    $badgeClass = 'badge-teal';
                    $statusLabel = '✅ Atasan Mengetahui';
                }
            @endphp
            <div class="status-wrapper">
                <span class="badge-status {{ $badgeClass }}">
                    {{ $statusLabel }}
                </span>
            </div>
        </div>

        <div class="divider-full"></div>

        <div class="detail-container">
            
            <div class="detail-section">
                <h4 class="section-title">Informasi Pengajuan</h4>

                <div class="info-row">
                    <div class="info-label">Jenis Pengajuan</div>
                    <div class="info-value">
                        <span class="badge-basic">{{ $item->type_label ?? $item->type }}</span>
                        
                        {{-- [TAMPILKAN DETAIL KATEGORI CUTI KHUSUS] --}}
                        @if($typeValue === 'CUTI_KHUSUS' && $item->special_leave_category)
                            @php
                                $catMap = [
                                    'NIKAH_KARYAWAN'   => 'Menikah (4 Hari)',
                                    'ISTRI_MELAHIRKAN' => 'Istri Melahirkan (2 Hari)',
                                    'ISTRI_KEGUGURAN'  => 'Istri Keguguran (2 Hari)',
                                    'KHITANAN_ANAK'    => 'Khitanan Anak (2 Hari)',
                                    'PEMBAPTISAN_ANAK' => 'Pembaptisan Anak (2 Hari)',
                                    'NIKAH_ANAK'       => 'Pernikahan Anak (2 Hari)',
                                    'DEATH_EXTENDED'   => 'Kematian Adik/Kakak/Ipar (2 Hari)',
                                    'DEATH_CORE'       => 'Kematian Inti (2 Hari)',
                                    'DEATH_HOUSE'      => 'Kematian Anggota Rumah (1 Hari)',
                                    'HAJI'             => 'Ibadah Haji (40 Hari)',
                                    'UMROH'            => 'Ibadah Umroh (14 Hari)',
                                ];
                                $catLabel = $catMap[$item->special_leave_category] ?? $item->special_leave_category;
                            @endphp
                            <div style="margin-top:6px;">
                                <span style="font-size:12px; font-weight:600; color:#1e4a8d; background:#eff6ff; padding:4px 10px; border-radius:6px; border:1px solid #dbeafe; display: inline-block;">
                                    Detail: {{ $catLabel }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Periode</div>
                    <div class="info-value">
                        {{ $item->start_date->format('d M Y') }}
                        @if($item->end_date && $item->end_date->ne($item->start_date))
                            – {{ $item->end_date->format('d M Y') }}
                        @endif
                    </div>
                </div>

                {{-- [LOGIC LABEL JAM] --}}
                @php
                     $startTimeLabel = $item->start_time ? $item->start_time->format('H:i') : null;
                     $endTimeLabel   = $item->end_time ? $item->end_time->format('H:i') : null;
                @endphp

                @if($startTimeLabel)
                    <div class="info-row">
                        <div class="info-label">
                            @if($endTimeLabel)
                                Jam Izin
                            @elseif($typeValue === 'IZIN_TELAT')
                                Estimasi Jam Tiba
                            @elseif($typeValue === 'IZIN_PULANG_AWAL')
                                Jam Pulang Awal
                            @else
                                Jam Mulai
                            @endif
                        </div>
                        <div class="info-value">
                            {{ $startTimeLabel }}
                            @if($endTimeLabel)
                                – {{ $endTimeLabel }}
                            @endif
                        </div>
                    </div>
                @endif

                @if($item->approved_by)
                <div class="info-row">
                    <div class="info-label">Diputus Oleh</div>
                    <div class="info-value">
                        {{ $item->approver?->name }}
                        @if($item->approved_at)
                            <div class="text-muted" style="font-size:12px; margin-top:2px;">
                                {{ $item->approved_at->format('d M Y H:i') }}
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- [INFO PIC PENGGANTI] --}}
                @if($item->substitute_pic)
                <div class="info-row">
                    <div class="info-label">PIC Pengganti</div>
                    <div class="info-value">
                        {{ $item->substitute_pic }}
                        @if($item->substitute_phone)
                            <div style="font-size:12px; color:#6b7280; margin-top:2px;">
                                {{ $item->substitute_phone }}
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- [SYSTEM NOTES - CATATAN SISTEM] --}}
                @if($item->notes)
                <div class="system-note-box">
                    <div class="note-label">Catatan Sistem:</div>
                    <div class="note-content">{!! nl2br(e($item->notes)) !!}</div>
                </div>
                @endif
            </div>

            <div class="detail-section">
                <h4 class="section-title">Keterangan & Bukti</h4>

                <div class="info-row">
                    <div class="info-label">Alasan</div>
                    <div class="info-value box-reason">
                        {{ $item->reason }}
                    </div>
                </div>

                @php
                    $url = $item->photo
                        ? asset('storage/leave_photos/' . ltrim($item->photo, '/'))
                        : null;
                @endphp

                <div class="info-row">
                    <div class="info-label">Lampiran Foto</div>
                    <div class="info-value">
                        @if($url)
                            <div class="photo-preview js-view-photo" data-url="{{ $url }}">
                                <img src="{{ $url }}" alt="Bukti Izin">
                                <div class="overlay">
                                    <svg width="24" height="24" fill="none" stroke="#fff" viewBox="0 0 24 24" style="margin-bottom:4px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Lihat Full Screen</span>
                                </div>
                            </div>
                        @else
                            <span class="text-muted" style="font-style:italic;">Tidak ada lampiran foto.</span>
                        @endif
                    </div>
                </div>

                @if($item->latitude && $item->longitude)
                <div class="info-row" style="margin-top:20px;">
                    <div class="info-label">
                        Lokasi Pengajuan
                        <span style="font-weight:400; color:#6b7280; font-size:11px;">(±{{ (int)$item->accuracy_m }}m)</span>
                    </div>
                    <div class="map-container">
                        <iframe
                            src="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}&z=16&output=embed"
                            loading="lazy"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <div style="margin-top:6px;">
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $item->latitude }},{{ $item->longitude }}" 
                           target="_blank" class="link-map">
                           Buka di Google Maps ↗
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="action-footer">
            <div class="left-action">
                <a href="{{ route('leave-requests.index') }}" class="btn-modern btn-back">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali
                </a>
            </div>

            <div class="right-action">
                @can('delete', $item)
                    <button type="button" data-modal-target="modal-delete" class="btn-modern btn-danger-outline">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Batalkan Pengajuan
                    </button>
                @endcan

                @if(!$item->is_pending && !auth()->user()->can('approve', $item))
                    <div class="processed-info">
                        @if($item->status == \App\Models\LeaveRequest::PENDING_SUPERVISOR)
                             <span style="color:#ca8a04; font-weight:600;">⏳ Menunggu Persetujuan Atasan</span>
                        @elseif($item->status == \App\Models\LeaveRequest::PENDING_HR)
                             <span style="color:#0f766e; font-weight:600;">✅ Atasan Mengetahui (Menunggu Verifikasi HRD)</span>
                        @elseif($item->status == \App\Models\LeaveRequest::STATUS_APPROVED)
                             <span style="color:#166534; font-weight:600;">✅ Disetujui HRD</span>
                        @elseif($item->status == \App\Models\LeaveRequest::STATUS_REJECTED)
                             <span style="color:#991b1b; font-weight:600;">❌ Ditolak</span>
                        @else
                             Status: <strong>{{ $statusLabel }}</strong>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- [SIMPLE FULL SCREEN VIEWER] --}}
    <div id="simple-viewer" class="simple-viewer-overlay" style="display: none;">
        <button type="button" id="btn-close-simple" class="btn-close-simple">
            <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <img id="simple-viewer-img" src="" alt="Full Preview">
    </div>

    @can('delete', $item)
    <x-modal
        id="modal-delete"
        title="Batalkan Pengajuan?"
        type="confirm"
        confirmLabel="Ya, Batalkan"
        cancelLabel="Tidak"
        :confirmFormAction="route('leave-requests.destroy', $item)"
        confirmFormMethod="DELETE">
        <p style="margin:0; color:#374151;">
            Apakah Anda yakin ingin membatalkan dan menghapus pengajuan izin ini?
        </p>
        <p style="margin:8px 0 0 0; font-size:0.85rem; color:#6b7280;">
            Data yang dihapus tidak dapat dikembalikan.
        </p>
    </x-modal>
    @endcan

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const viewer = document.getElementById('simple-viewer');
            const viewerImg = document.getElementById('simple-viewer-img');
            const closeBtn = document.getElementById('btn-close-simple');

            // Fungsi Buka Viewer
            document.querySelectorAll('.js-view-photo').forEach(el => {
                el.addEventListener('click', () => {
                    const url = el.getAttribute('data-url');
                    if(url && viewer && viewerImg) {
                        viewerImg.src = url;
                        viewer.style.display = 'flex';
                        document.body.style.overflow = 'hidden'; 
                    }
                });
            });

            // Fungsi Tutup Viewer
            function closeViewer() {
                if (viewer) viewer.style.display = 'none';
                if (viewerImg) viewerImg.src = '';
                document.body.style.overflow = ''; 
            }

            if (closeBtn) closeBtn.addEventListener('click', closeViewer);

            if (viewer) {
                viewer.addEventListener('click', (e) => {
                    if (e.target === viewer) {
                        closeViewer();
                    }
                });
            }

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && viewer.style.display === 'flex') {
                    closeViewer();
                }
            });
        });
    </script>

    <style>
        /* --- SIMPLE FULL SCREEN VIEWER --- */
        .simple-viewer-overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.95);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-close-simple {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: #fff;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
            z-index: 100000;
        }
        .btn-close-simple:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        #simple-viewer-img {
            max-width: 95vw;
            max-height: 95vh;
            object-fit: contain;
            border-radius: 4px;
            box-shadow: 0 0 50px rgba(0,0,0,0.5);
        }

        /* --- ALERTS --- */
        .alert-success { background: #ecfdf5; color: #065f46; padding: 12px 16px; border-radius: 8px; border: 1px solid #a7f3d0; margin-bottom: 16px; font-size: 14px; }
        .alert-error { background: #fef2f2; color: #991b1b; padding: 12px 16px; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 16px; font-size: 14px; }

        /* --- CARD --- */
        .card { background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid #f3f4f6; overflow: hidden; }

        /* --- HEADER --- */
        .profile-header { padding: 24px; display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap; background: #fff; }
        .profile-main { display: flex; gap: 16px; align-items: center; }
        .profile-avatar { width: 56px; height: 56px; background: #e0e7ff; color: #1e4a8d; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 700; }
        .profile-name { margin: 0 0 4px 0; font-size: 18px; font-weight: 700; color: #111827; }
        .profile-meta { font-size: 13px; color: #6b7280; display: flex; align-items: center; gap: 8px; }
        .dot { color: #d1d5db; }
        .chip-role { background: #f3f4f6; padding: 2px 8px; border-radius: 4px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; }
        
        .divider-full { height: 1px; background: #f3f4f6; width: 100%; }

        /* --- GRID DETAIL --- */
        .detail-container { padding: 24px; display: grid; grid-template-columns: 1fr 1.5fr; gap: 40px; }
        @media(max-width: 768px) { .detail-container { grid-template-columns: 1fr; gap: 24px; } }

        .section-title { font-size: 14px; font-weight: 700; color: #111827; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 16px 0; padding-bottom: 8px; border-bottom: 2px solid #f3f4f6; display: inline-block; }

        .info-row { margin-bottom: 16px; }
        .info-label { font-size: 12px; color: #6b7280; margin-bottom: 4px; font-weight: 500; }
        .info-value { font-size: 14.5px; color: #1f2937; font-weight: 500; line-height: 1.5; }

        .box-reason { background: #f9fafb; padding: 12px; border-radius: 8px; border: 1px solid #f3f4f6; color: #374151; font-size: 14px; }
        
        /* --- SYSTEM NOTE --- */
        .system-note-box { background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; padding: 12px; margin-top: 10px; }
        .note-label { font-size: 12px; font-weight: 700; color: #92400e; margin-bottom: 4px; text-transform: uppercase; }
        .note-content { font-size: 13.5px; color: #b45309; line-height: 1.4; }

        /* --- BADGES --- */
        .badge-basic { background: #f3f4f6; color: #374151; padding: 4px 10px; border-radius: 6px; font-size: 13px; font-weight: 500; border: 1px solid #e5e7eb; display: inline-block; }
        .badge-status { padding: 6px 14px; border-radius: 30px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fefce8; color: #a16207; border: 1px solid #fef08a; }
        .badge-blue { background: #eff6ff; color: #1d4ed8; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        /* Badge Teal */
        .badge-teal { background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; }

        /* --- PHOTO PREVIEW THUMBNAIL --- */
        .photo-preview { position: relative; width: 100%; max-width: 300px; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; cursor: pointer; }
        .photo-preview img { width: 100%; height: auto; display: block; }
        .photo-preview .overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.4); display: flex; flex-direction:column; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s; }
        .photo-preview:hover .overlay { opacity: 1; }
        .photo-preview .overlay span { color: #fff; font-size: 12px; font-weight: 600; background: rgba(0,0,0,0.6); padding: 4px 10px; border-radius: 20px; }

        /* --- MAPS --- */
        .map-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; border: 1px solid #e5e7eb; margin-top: 4px; }
        .map-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }
        .link-map { font-size: 13px; color: #1e4a8d; text-decoration: none; font-weight: 500; }
        .link-map:hover { text-decoration: underline; }

        /* --- FOOTER ACTIONS --- */
        .action-footer { background: #f9fafb; padding: 16px 24px; border-top: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
        .right-action { display: flex; gap: 12px; align-items: center; }

        /* --- BUTTONS --- */
        .btn-modern { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; border: 1px solid transparent; text-decoration: none; line-height: 1; }
        
        .btn-back { background: #fff; border-color: #d1d5db; color: #374151; }
        .btn-back:hover { background: #f3f4f6; border-color: #9ca3af; color: #111827; }

        .btn-danger-outline { background: #fff; border-color: #fca5a5; color: #dc2626; }
        .btn-danger-outline:hover { background: #fef2f2; border-color: #dc2626; color: #b91c1c; }

        .processed-info { font-size: 13.5px; color: #6b7280; background: #fff; padding: 8px 16px; border-radius: 8px; border: 1px solid #e5e7eb; }
    </style>

</x-app>