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

    <div class="card">
        <div class="profile-header">
            <div class="profile-main">
                <div class="profile-avatar">
                    {{ substr($item->user->name, 0, 1) }}
                </div>
                <div class="profile-info">
                    <h2 class="profile-name">{{ $item->user->name }}</h2>
                    <div class="profile-meta">
                        <span>{{ $item->user->division->name ?? 'Divisi Tidak Diketahui' }}</span>
                        <span class="dot">•</span>
                        <span>Diajukan: {{ $item->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>

            @php
                $status = $item->status;
                $badgeClass = 'badge-gray';
                
                if ($status === \App\Models\LeaveRequest::STATUS_APPROVED) {
                    $badgeClass = 'badge-green';
                } elseif ($status === \App\Models\LeaveRequest::STATUS_REJECTED) {
                    $badgeClass = 'badge-red';
                } elseif ($status === \App\Models\LeaveRequest::PENDING_SUPERVISOR) {
                    $badgeClass = 'badge-yellow';
                } elseif ($status === \App\Models\LeaveRequest::PENDING_HR) {
                    $badgeClass = 'badge-blue';
                }
            @endphp
            <div class="status-wrapper">
                <span class="badge-status {{ $badgeClass }}">
                    {{ $item->status_label ?? $status }}
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

                @php
                     $startTimeLabel = $item->start_time ? $item->start_time->format('H:i') : null;
                     $endTimeLabel = $item->end_time ? $item->end_time->format('H:i') : null;
                @endphp

                @if($startTimeLabel && $endTimeLabel)
                <div class="info-row">
                    <div class="info-label">Jam Izin</div>
                    <div class="info-value">{{ $startTimeLabel }} – {{ $endTimeLabel }}</div>
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
                                    <span>Klik untuk memperbesar</span>
                                </div>
                            </div>
                        @else
                            <span class="text-muted" style="font-style:italic;">Tidak ada lampiran foto.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="action-footer">
            <div class="left-action">
                <a href="{{ route('supervisor.leave.index') }}" class="btn-modern btn-back">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali
                </a>
            </div>

            <div class="right-action">
                @if($item->status === \App\Models\LeaveRequest::PENDING_SUPERVISOR)
                    
                    <button type="button" data-modal-target="modal-reject" class="btn-modern btn-reject">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Tolak
                    </button>

                    <button type="button" data-modal-target="modal-approve" class="btn-modern btn-approve">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Setujui & Teruskan
                    </button>

                @else
                    <div class="processed-info">
                        Status saat ini: <strong>{{ $item->status_label }}</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-modal id="photo-modal" title="Lampiran Foto" type="info" cancelLabel="Tutup">
        <div style="display:flex; justify-content:center; background:#000; border-radius:8px; overflow:hidden;">
            <img id="modal-img-preview" src="" style="max-width:100%; max-height:80vh; object-fit:contain;">
        </div>
    </x-modal>

    {{-- MODAL REJECT (TOLAK) --}}
    <x-modal
        id="modal-reject"
        title="Tolak Pengajuan?"
        type="confirm"
        variant="danger"
        confirmLabel="Tolak Pengajuan"
        cancelLabel="Batal"
        :confirmFormAction="route('supervisor.leave.reject', $item->id)"
        confirmFormMethod="POST">
        <p style="margin:0; color:#374151;">
            Apakah Anda yakin ingin menolak pengajuan izin dari <strong>{{ $item->user->name }}</strong>?
        </p>
        <p style="margin:8px 0 0 0; font-size:0.85rem; color:#dc2626; font-weight:500;">
            Status akan berubah menjadi Ditolak dan tidak dapat dikembalikan.
        </p>
    </x-modal>

    {{-- MODAL APPROVE (SETUJUI) --}}
    {{-- PERBAIKAN: Mengganti .ack menjadi .approve --}}
    <x-modal
        id="modal-approve"
        title="Setujui Pengajuan?"
        type="confirm"
        variant="primary"
        confirmLabel="Ya, Setujui"
        cancelLabel="Batal"
        :confirmFormAction="route('supervisor.leave.approve', $item->id)"
        confirmFormMethod="POST">
        <p style="margin:0; color:#374151;">
            Anda akan menyetujui pengajuan izin ini.
        </p>
        <p style="margin:8px 0 0 0; font-size:0.85rem; color:#6b7280;">
            Pengajuan akan diteruskan ke <strong>HRD</strong> untuk proses verifikasi akhir.
        </p>
    </x-modal>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Logic Modal Foto
            const modal = document.getElementById('photo-modal');
            const modalImg = document.getElementById('modal-img-preview');
            
            document.querySelectorAll('.js-view-photo').forEach(el => {
                el.addEventListener('click', () => {
                    const url = el.getAttribute('data-url');
                    if(url && modal && modalImg) {
                        modalImg.src = url;
                        // Trigger modal display manually if needed
                        if(modal) modal.style.display = 'flex';
                        document.body.style.overflow = 'hidden';
                    }
                });
            });
        });
    </script>

    <style>
        /* --- ALERTS --- */
        .alert-success { background: #ecfdf5; color: #065f46; padding: 12px 16px; border-radius: 8px; border: 1px solid #a7f3d0; margin-bottom: 16px; font-size: 14px; }
        .alert-error { background: #fef2f2; color: #991b1b; padding: 12px 16px; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 16px; font-size: 14px; }

        /* --- CARD & LAYOUT --- */
        .card { background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid #f3f4f6; overflow: hidden; }
        
        /* HEADER PROFILE */
        .profile-header { padding: 24px; display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap; background: #fff; }
        .profile-main { display: flex; gap: 16px; align-items: center; }
        .profile-avatar { width: 56px; height: 56px; background: #e0e7ff; color: #1e4a8d; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 700; }
        .profile-name { margin: 0 0 4px 0; font-size: 18px; font-weight: 700; color: #111827; }
        .profile-meta { font-size: 13px; color: #6b7280; display: flex; align-items: center; gap: 8px; }
        .dot { color: #d1d5db; }
        .divider-full { height: 1px; background: #f3f4f6; width: 100%; }

        /* DETAIL GRID */
        .detail-container { padding: 24px; display: grid; grid-template-columns: 1fr 1.5fr; gap: 40px; }
        @media(max-width: 768px) { .detail-container { grid-template-columns: 1fr; gap: 24px; } }

        .section-title { font-size: 14px; font-weight: 700; color: #111827; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 16px 0; padding-bottom: 8px; border-bottom: 2px solid #f3f4f6; display: inline-block; }
        
        .info-row { margin-bottom: 16px; }
        .info-label { font-size: 12px; color: #6b7280; margin-bottom: 4px; font-weight: 500; }
        .info-value { font-size: 14.5px; color: #1f2937; font-weight: 500; line-height: 1.5; }
        
        .box-reason { background: #f9fafb; padding: 12px; border-radius: 8px; border: 1px solid #f3f4f6; color: #374151; font-size: 14px; }

        /* ATTACHMENT */
        .attachment-preview { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; text-decoration: none; color: #374151; font-size: 13px; font-weight: 500; transition: all 0.2s; }
        .attachment-preview:hover { border-color: #1e4a8d; color: #1e4a8d; background: #eff6ff; }

        /* BADGES */
        .badge-basic { background: #f3f4f6; color: #374151; padding: 4px 10px; border-radius: 6px; font-size: 13px; font-weight: 500; border: 1px solid #e5e7eb; display: inline-block; }
        .badge-status { padding: 6px 14px; border-radius: 30px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fefce8; color: #a16207; border: 1px solid #fef08a; }
        .badge-blue { background: #eff6ff; color: #1d4ed8; }
        .badge-gray { background: #f3f4f6; color: #374151; }

        /* FOOTER ACTIONS */
        .action-footer { background: #f9fafb; padding: 16px 24px; border-top: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
        .right-action { display: flex; gap: 12px; align-items: center; }
        
        /* MODERN BUTTONS */
        .btn-modern {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            text-decoration: none;
            line-height: 1;
        }

        .btn-back { background: #fff; border-color: #d1d5db; color: #374151; }
        .btn-back:hover { background: #f3f4f6; border-color: #9ca3af; color: #111827; }

        .btn-approve { background: #1e4a8d; color: #fff; box-shadow: 0 2px 4px rgba(30, 74, 141, 0.2); }
        .btn-approve:hover { background: #163a75; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(30, 74, 141, 0.3); }

        .btn-reject { background: #fff; border-color: #fee2e2; color: #dc2626; }
        .btn-reject:hover { background: #fef2f2; border-color: #fca5a5; color: #b91c1c; }

        .processed-info { font-size: 13.5px; color: #6b7280; background: #fff; padding: 8px 16px; border-radius: 8px; border: 1px solid #e5e7eb; }
    </style>

</x-app>