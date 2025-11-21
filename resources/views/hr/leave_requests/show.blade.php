<x-app title="Detail Pengajuan">
    <div class="lr-detail">
        <div class="back-btn">
            <a href="{{ route('hr.leave.index') }}" class="btn-back">← Kembali</a>
        </div>

        <div class="card">
            <p><b>Pemohon:</b> {{ $item->user->name }} ({{ $item->user->role }})</p>

            @if($item->notes)
            <div class="card" style="background:#fff3cd;color:#856404;border:1px solid #ffeeba">
                {{ $item->notes }}
            </div>
            @endif

            <p><b>Jenis:</b> {{ $item->type_label ?? $item->type }}</p>

            <p><b>Periode:</b>
                {{ $item->start_date->format('d M Y') }}
                @if($item->end_date && $item->end_date->ne($item->start_date))
                – {{ $item->end_date->format('d M Y') }}
                @endif
            </p>

            <p><b>Status:</b>
                <span class="badge {{ strtolower($item->status) }}">{{ $item->status_label ?? $item->status }}</span>
            </p>

            @php
            $raw = $item->photo;
            $rel = $raw ? (\Illuminate\Support\Str::startsWith($raw, 'leave_photos/') ? $raw : ('leave_photos/'.$raw)) : null;
            $exists = $rel ? \Illuminate\Support\Facades\Storage::disk('public')->exists($rel) : false;
            $url = $exists ? \Illuminate\Support\Facades\Storage::url($rel) : null;
            @endphp

            @if ($item->latitude && $item->longitude)
            <p><b>Lokasi Pengajuan:</b></p>
            <div class="card" style="margin-top:10px;">
                <div style="font-size:.9rem;opacity:.8;">
                    Location captured {{ $item->location_captured_at?->format('Y-m-d H:i') }} (±{{ (int) $item->accuracy_m }}m)
                </div>

                <div class="map-embed">
                    <iframe
                        src="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}&z=16&output=embed"
                        loading="lazy"
                        allowfullscreen
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>

                <div style="margin-top:6px;">
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $item->latitude }},{{ $item->longitude }}" target="_blank" rel="noopener">
                        Open in Google Maps
                    </a>
                </div>
            </div>
            @endif


            @if ($url)
            <p><b>Foto:</b></p>
            <div class="photo-box">
                <a href="{{ $url }}" target="_blank" rel="noopener">
                    <img src="{{ $url }}" alt="Foto Izin">
                </a>
            </div>
            @elseif ($item->photo)
            <div class="card" style="background:#ffecec;color:#a40000;border:1px solid #f5c2c7">
                File foto tidak ditemukan di storage. ({{ $item->photo }})
            </div>
            @endif

            @if($item->reason)
            <p><b>Alasan:</b> {{ $item->reason }}</p>
            @endif

            @if($item->approved_by)
            <p><b>Disetujui/Terakhir Diputus:</b>
                {{ $item->approver?->name }} pada {{ $item->approved_at?->format('d M Y H:i') }}
            </p>
            @endif

            @php
            $needsHrdAction = ($item->status === \App\Models\LeaveRequest::PENDING_HR);
            @endphp

            <div class="actions">
                @if ($needsHrdAction)
                <form class="inline js-confirm" method="POST" action="{{ route('hr.leave.approve', $item) }}" data-msg="Setujui pengajuan ini?">
                    @csrf
                    <button class="btn btn-success" type="submit">Setujui</button>
                </form>

                <form class="inline js-confirm" method="POST" action="{{ route('hr.leave.reject', $item) }}" data-msg="Tolak pengajuan ini?">
                    @csrf
                    <button class="btn btn-danger" type="submit">Tolak</button>
                </form>
                @else
                <em style="opacity:.7;">Tidak ada aksi untuk HRD pada pengajuan ini.</em>
                @endif
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.querySelectorAll('.js-confirm').forEach(f => {
            f.addEventListener('submit', e => {
                const msg = f.dataset.msg || 'Lanjutkan aksi?';
                if (!confirm(msg)) {
                    e.preventDefault();
                    return;
                }
                const b = f.querySelector('button[type="submit"]');
                if (b) {
                    b.disabled = true;
                    b.style.opacity = '.7';
                }
            });
        });
    </script>

    <style>
        .lr-detail {
            max-width: 640px;
            margin: auto;
        }

        .back-btn {
            margin-bottom: 10px;
        }

        .btn-back {
            display: inline-block;
            font-size: 13px;
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid #ddd;
            background: #fff;
            color: #333;
            text-decoration: none;
            transition: background .2s, color .2s;
        }

        .btn-back:hover {
            background: #f1f1f1;
            color: #1e4a8d;
        }

        .photo-box {
            margin: 8px 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
            max-width: 320px;
        }

        .photo-box img {
            width: 100%;
            height: auto;
            display: block;
        }

        .actions {
            margin-top: 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .btn.btn-success {
            background: #169b62;
            color: #fff;
            border-color: #169b62;
        }

        .btn.btn-danger {
            background: #c62828;
            color: #fff;
            border-color: #c62828;
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
            margin-top: 8px;
        }

        .map-embed iframe {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border: 0;
            display: block;
        }


        @media(max-width:600px) {
            .lr-detail {
                padding: 0 8px;
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