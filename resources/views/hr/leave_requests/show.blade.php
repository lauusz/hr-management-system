<x-app title="Detail Pengajuan">
    <div class="lr-detail">
        <div class="back-btn">
            <a href="{{ route('hr.leave.index') }}" class="btn-back">← Kembali</a>
        </div>

        @php
            $url = $item->photo
                ? asset('storage/leave_photos/' . ltrim($item->photo, '/'))
                : null;

            $isIzinTengahKerja = $item->type === \App\Enums\LeaveType::IZIN_TENGAH_KERJA->value;
            $startTimeLabel = $item->start_time ? $item->start_time->format('H:i') : null;
            $endTimeLabel = $item->end_time ? $item->end_time->format('H:i') : null;

            $needsHrdAction = ($item->status === \App\Models\LeaveRequest::PENDING_HR);
        @endphp

        <div class="card lr-card">
            <div class="lr-header">
                <div>
                    <div class="lr-title">Detail Pengajuan Izin</div>
                    <div class="lr-subtitle">
                        Diajukan pada {{ $item->created_at?->format('d M Y H:i') }}
                    </div>
                </div>
                <div class="lr-status">
                    <span class="badge {{ strtolower($item->status) }}">
                        {{ $item->status_label ?? $item->status }}
                    </span>
                </div>
            </div>

            <div class="lr-section">
                <div class="lr-row">
                    <div class="lr-label">Pemohon</div>
                    <div class="lr-value">
                        {{ $item->user->name }}
                        <span class="lr-chip-role">{{ $item->user->role }}</span>
                    </div>
                </div>

                <div class="lr-row">
                    <div class="lr-label">Jenis</div>
                    <div class="lr-value">{{ $item->type_label ?? $item->type }}</div>
                </div>

                <div class="lr-row">
                    <div class="lr-label">Periode</div>
                    <div class="lr-value">
                        {{ $item->start_date->format('d M Y') }}
                        @if($item->end_date && $item->end_date->ne($item->start_date))
                            – {{ $item->end_date->format('d M Y') }}
                        @endif
                    </div>
                </div>

                @if($isIzinTengahKerja && $startTimeLabel && $endTimeLabel)
                    <div class="lr-row">
                        <div class="lr-label">Jam Izin</div>
                        <div class="lr-value">
                            {{ $startTimeLabel }} – {{ $endTimeLabel }}
                        </div>
                    </div>
                @endif

                @if($item->approved_by)
                    <div class="lr-row">
                        <div class="lr-label">Diputus Oleh</div>
                        <div class="lr-value">
                            {{ $item->approver?->name }}
                            @if($item->approved_at)
                                <div class="lr-muted">
                                    pada {{ $item->approved_at->format('d M Y H:i') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            @if($item->notes)
                <div class="lr-section lr-section-note">
                    <div class="lr-note-title">Catatan Sistem</div>
                    <div class="lr-note-body">
                        {!! nl2br(e($item->notes)) !!}
                    </div>
                </div>
            @endif

            @if($item->reason)
                <div class="lr-section">
                    <div class="lr-label">Alasan</div>
                    <div class="lr-reason">
                        {{ $item->reason }}
                    </div>
                </div>
            @endif

            @if($item->latitude && $item->longitude)
                <div class="lr-section">
                    <div class="lr-label">Lokasi Pengajuan</div>
                    <div class="lr-location-block">
                        <div class="lr-location-meta">
                            Location captured {{ $item->location_captured_at?->format('Y-m-d H:i') }}
                            (±{{ (int) $item->accuracy_m }}m)
                        </div>

                        <div class="map-embed">
                            <iframe
                                src="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}&z=16&output=embed"
                                loading="lazy"
                                allowfullscreen
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>

                        <div class="lr-location-link">
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $item->latitude }},{{ $item->longitude }}"
                               target="_blank"
                               rel="noopener">
                                Buka di Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            @if ($url)
                <div class="lr-section">
                    <div class="lr-label">Lampiran</div>
                    <div class="lr-attachment">
                        <div class="photo-box js-view-leave-photo" data-photo-url="{{ $url }}">
                            <img src="{{ $url }}" alt="Foto Izin">
                        </div>
                    </div>
                </div>
            @elseif ($item->photo)
                <div class="lr-section">
                    <div class="lr-alert-error">
                        File foto tidak ditemukan di storage. ({{ $item->photo }})
                    </div>
                </div>
            @endif

            

            <div class="actions">
                @if ($needsHrdAction)
                    <form
                        class="inline js-confirm"
                        method="POST"
                        action="{{ route('hr.leave.approve', $item) }}"
                        data-msg="Setujui pengajuan ini?">
                        @csrf
                        <button class="btn btn-success" type="submit">Setujui</button>
                    </form>

                    <form
                        class="inline js-confirm"
                        method="POST"
                        action="{{ route('hr.leave.reject', $item) }}"
                        data-msg="Tolak pengajuan ini?">
                        @csrf
                        <button class="btn btn-danger" type="submit">Tolak</button>
                    </form>
                @else
                    <em style="opacity:.7;">Tidak ada aksi untuk HRD pada pengajuan ini.</em>
                @endif
            </div>
        </div>
    </div>

    <x-modal
        id="leave-photo-modal"
        title="Foto Pengajuan Izin"
        type="info"
        cancelLabel="Tutup">
        <div style="display:flex;flex-direction:column;gap:10px;">
            <div style="font-size:0.85rem;color:#4b5563;">
                Foto saat karyawan mengajukan izin.
            </div>

            <div style="
                border-radius:10px;
                overflow:hidden;
                border:1px solid #e5e7eb;
                max-height:70vh;
                display:flex;
                align-items:center;
                justify-content:center;
                background:#000;
            ">
                <img
                    id="leave-photo-img"
                    src=""
                    alt="Foto Pengajuan Izin"
                    style="
                        max-width:100%;
                        max-height:70vh;
                        width:auto;
                        height:auto;
                        display:block;
                        object-fit:contain;
                    ">
            </div>
        </div>
    </x-modal>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.querySelectorAll('.js-confirm').forEach(function(f) {
            f.addEventListener('submit', function(e) {
                var msg = f.dataset.msg || 'Lanjutkan aksi?';
                if (!confirm(msg)) {
                    e.preventDefault();
                    return;
                }
                var b = f.querySelector('button[type="submit"]');
                if (b) {
                    b.disabled = true;
                    b.style.opacity = '.7';
                }
            });
        });

        var leavePhotoModal = document.getElementById('leave-photo-modal');
        var leavePhotoImg = document.getElementById('leave-photo-img');

        document.querySelectorAll('.js-view-leave-photo').forEach(function(box) {
            box.addEventListener('click', function() {
                var url = box.getAttribute('data-photo-url');
                if (!url || !leavePhotoModal || !leavePhotoImg) return;
                leavePhotoImg.src = url;
                leavePhotoModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
        });
    </script>

    <style>
        .lr-detail {
            max-width: 720px;
            margin: 0 auto;
        }

        .back-btn {
            margin-bottom: 12px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            font-size: 13px;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #374151;
            text-decoration: none;
            gap: 4px;
            transition: background .15s ease, color .15s ease, border-color .15s ease, transform .1s ease;
        }

        .btn-back:hover {
            background: #f3f4f6;
            color: #1e4a8d;
            border-color: #cbd5e1;
            transform: translateX(-1px);
        }

        .lr-card {
            padding: 18px 18px 16px;
        }

        .lr-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
        }

        .lr-title {
            font-weight: 600;
            font-size: 1.05rem;
            color: #111827;
        }

        .lr-subtitle {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 2px;
        }

        .lr-status {
            display: flex;
            align-items: center;
        }

        .lr-section {
            border-top: 1px solid #e5e7eb;
            padding-top: 12px;
            margin-top: 10px;
        }

        .lr-row {
            display: grid;
            grid-template-columns: 110px minmax(0, 1fr);
            column-gap: 12px;
            row-gap: 4px;
            font-size: 0.9rem;
            padding: 4px 0;
        }

        .lr-label {
            font-weight: 600;
            color: #4b5563;
            font-size: 0.85rem;
        }

        .lr-value {
            color: #111827;
        }

        .lr-chip-role {
            display: inline-flex;
            margin-left: 6px;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 0.7rem;
            background: #eef2ff;
            color: #3730a3;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .lr-muted {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 2px;
        }

        .lr-section-note {
            background: #fffbeb;
            border-radius: 10px;
            border: 1px solid #fef3c7;
            padding: 10px 12px;
            margin-top: 14px;
        }

        .lr-note-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: #92400e;
            margin-bottom: 4px;
        }

        .lr-note-body {
            font-size: 0.85rem;
            color: #92400e;
            white-space: pre-line;
        }

        .lr-location-block {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .lr-location-meta {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .lr-location-link a {
            font-size: 0.8rem;
            color: #1e40af;
            text-decoration: none;
        }

        .lr-location-link a:hover {
            text-decoration: underline;
        }

        .lr-attachment {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .photo-box {
            margin: 4px 0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 14px rgba(15, 23, 42, .15);
            max-width: 360px;
            cursor: pointer;
        }

        .photo-box img {
            width: 100%;
            height: auto;
            display: block;
        }

        .lr-link {
            font-size: 0.8rem;
            color: #1e40af;
            text-decoration: none;
        }

        .lr-link:hover {
            text-decoration: underline;
        }

        .lr-reason {
            margin-top: 4px;
            font-size: 0.9rem;
            line-height: 1.5;
            color: #111827;
            padding: 8px 10px;
            border-radius: 8px;
            background: #f9fafb;
        }

        .lr-alert-error {
            background: #fef2f2;
            color: #b91c1c;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 0.85rem;
            border: 1px solid #fecaca;
        }

        .actions {
            margin-top: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .actions .btn {
            font-size: 0.85rem;
        }

        .btn.btn-success {
            background: #169b62;
            color: #fff;
            border-color: #169b62;
        }

        .btn.btn-success:hover {
            background: #128054;
            border-color: #128054;
        }

        .btn.btn-danger {
            background: #c62828;
            color: #fff;
            border-color: #c62828;
        }

        .btn.btn-danger:hover {
            background: #b71c1c;
            border-color: #b71c1c;
        }

        .btn.btn-danger-outline {
            background: #fff;
            color: #c62828;
            border-color: #e3a4a4;
        }

        .btn.btn-danger-outline:hover {
            background: #fff5f5;
        }

        .map-embed {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
            margin-top: 6px;
        }

        .map-embed iframe {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border: 0;
            display: block;
        }

        @media(max-width: 600px) {
            .lr-detail {
                padding: 0 8px;
            }

            .lr-card {
                padding: 14px;
            }

            .lr-row {
                grid-template-columns: 96px minmax(0, 1fr);
            }

            .actions {
                flex-direction: column;
                align-items: stretch;
            }

            .actions .btn {
                width: 100%;
            }
        }
    </style>
</x-app>
