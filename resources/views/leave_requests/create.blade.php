<x-app title="Buat Pengajuan Izin">
    @if ($errors->any())
    <div class="alert-error">
        {{ $errors->first() }}
    </div>
    @endif

    <form class="card form-leave" method="POST" action="{{ route('leave-requests.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="grid-form">
            <div class="field full">
                <label><b>Jenis Pengajuan:</b></label>
                <div class="card" style="padding:10px; display:grid; gap:6px">
                    @foreach (\App\Enums\LeaveType::cases() as $case)
                    <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer;">
                        <input
                            type="radio"
                            name="type"
                            value="{{ $case->value }}"
                            @if ($loop->first) required @endif
                        @checked(old('type') === $case->value)
                        >
                        <span style="line-height:1.4">{{ $case->label() }}</span>
                    </label>
                    @endforeach
                </div>
                @error('type') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label><b>Tanggal Mulai:</b></label>
                <input type="date" name="start_date" value="{{ old('start_date') }}" required>
                @error('start_date') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field full">
                <small id="cuti-rule" style="display:none; color:#6b7280;"></small>
                <div id="h7-warning"
                    role="alert"
                    aria-live="polite"
                    style="display:none; margin-top:6px; background:#fef9c3; color:#854d0e; padding:8px 10px; border-radius:8px;">
                </div>
            </div>

            <div class="field">
                <label><b>Tanggal Selesai:</b></label>
                <input type="date" name="end_date" value="{{ old('end_date') }}" required>
                @error('end_date') <div class="error">{{ $message }}</div> @enderror
            </div>

            <!-- Lokasi -->

            <div class="field full" id="location">
                <label for="lokasi"><b>Lokasi:</b></label>
                <button type="button" id="btn-get-location">
                    Ambil Lokasi Saat Ini
                </button>

                <div id="loc-status"></div>
                <div id="map-preview" style="width:100%;height:240px;margin-top:8px;border-radius:8px;display:none;">
                </div>

                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
                <input type="hidden" name="accuracy_m" id="accuracy_m">
                <input type="hidden" name="location_captured_at" id="location_captured_at">
            </div>

            <!-- Bukti Pendukung -->
            <div class="field full">
                <label><b>Bukti Pendukung:</b></label>
                <input type="file" name="photo" id="photoInput" accept="image/*">
                <div class="hint">Format gambar (JPG/PNG/WebP), maks 4 MB.</div>
                @error('photo') <div class="error">{{ $message }}</div> @enderror

                <div id="photoPreviewContainer" class="preview-container">
                    <p>Preview:</p>
                    <img id="photoPreview" src="#" alt="Preview foto">
                </div>
            </div>

            <div class="field full">
                <label><b>Alasan:</b></label>
                <textarea name="reason" rows="4" placeholder="Tulis alasan">{{ old('reason') }}</textarea>
                @error('reason') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="actions">
            <button class="btn primary" type="submit" style="text-decoration: none;">Kirim</button>
            <button type="button" class="btn" onclick="window.location='{{ route('leave-requests.index') }}'">Batal</button>
        </div>
    </form>

    {{-- Styles --}}
    <style>
        .alert-error {
            background: #ffecec;
            color: #a40000;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 14px;
            font-size: 14px;
        }

        .form-leave {
            max-width: 520px;
            margin: auto;
        }

        #btn-get-location {
            display: inline-block;
            font-weight: 600;
            font-size: 15px;
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .grid-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .field label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }

        .card input[type="radio"] {
            margin-top: 3px;
            transform: scale(1.1);
            accent-color: #1b3e7f;
            /* warna corporate */
        }

        .card label {
            transition: background-color .2s;
        }

        .card label:hover {
            background: #f8f9fa;
            border-radius: 6px;
        }

        input[type=text],
        input[type=date],
        select,
        textarea,
        input[type=file] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
        }

        select option {
            white-space: normal !important;
            line-height: 1.4;
        }

        .hint {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }

        .error {
            font-size: 12px;
            color: #a40000;
            margin-top: 4px;
        }

        .full {
            grid-column: 1 / -1;
        }

        .actions {
            margin-top: 18px;
            display: flex;
            gap: 8px;
        }

        /* Preview foto */
        .preview-container {
            display: none;
            margin-top: 10px;
        }

        .preview-container p {
            font-size: 14px;
            color: #333;
            margin-bottom: 6px;
        }

        .preview-container img {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
        }

        #map-preview { height: 240px; }
        #map-preview .leaflet-container { height: 100% !important; }


        /* Responsif */
        @media (max-width: 600px) {
            .grid-form {
                grid-template-columns: 1fr;
            }

            .form-leave {
                padding: 12px;
            }
        }
    </style>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>



    <!-- Location Capture -->
    <script>
        (function() {
            const typeRadios = document.querySelectorAll('input[name="type"]');
            const IZIN_TELAT = @json(\App\Enums\LeaveType::IZIN_TELAT->value);
            const section = document.getElementById('location');
            const btn = document.getElementById('btn-get-location');
            const statusEl = document.getElementById('loc-status');
            const mapDiv = document.getElementById('map-preview');

            const latEl = document.getElementById('latitude');
            const lngEl = document.getElementById('longitude');
            const accEl = document.getElementById('accuracy_m');
            const tsEl = document.getElementById('location_captured_at');

            let map, marker, circle;

            function selectedType() {
                const r = document.querySelector('input[name="type"]:checked');
                return r ? r.value : null;
            }

            function toggleSection() {
                const val = selectedType();
                const show = (val === IZIN_TELAT);
                section.style.display = show ? 'grid' : 'none';
                if (!show) {
                    latEl.value = lngEl.value = accEl.value = tsEl.value = '';
                    statusEl.textContent = '';
                    mapDiv.style.display = 'none';
                }
            }

            typeRadios.forEach(r => r.addEventListener('change', toggleSection));
            toggleSection();

            btn?.addEventListener('click', () => {
                if (!('geolocation' in navigator)) {
                    statusEl.textContent = 'Geolocation is not supported in this browser.';
                    return;
                }

                statusEl.textContent = 'Mengambil lokasi...';
                mapDiv.style.display = 'block';

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const {
                            latitude,
                            longitude,
                            accuracy
                        } = pos.coords;
                        latEl.value = latitude.toFixed(7);
                        lngEl.value = longitude.toFixed(7);
                        accEl.value = (accuracy ?? 0).toFixed(2);
                        tsEl.value = new Date(pos.timestamp).toISOString().slice(0, 19).replace('T', ' ');

                        statusEl.textContent = `Lokasi berhasil diambil ${latitude.toFixed(5)}, ${longitude.toFixed(5)} (±${Math.round(accuracy)}m)`;

                        // Show map
                        mapDiv.style.display = 'block';

                        // Initialize map if not yet
                        if (!map) {
                            map = L.map('map-preview', {
                                zoomControl: true
                            });
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors'
                            }).addTo(map);
                        }
                        map.setView([latitude, longitude], 18);


                        if (marker) marker.setLatLng([latitude, longitude]);
                        else marker = L.marker([latitude, longitude]).addTo(map);

                        if (circle) circle.setLatLng([latitude, longitude]).setRadius(accuracy);
                        else circle = L.circle([latitude, longitude], {
                            radius: accuracy, color: '#1b3e7f', fillColor: '#1b3e7f', fillOpacity: 0.2
                        }).addTo(map);

                        setTimeout(() => map.invalidateSize(), 100);

                        const gmapsUrl = `https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}`;
                        mapDiv.style.cursor = 'pointer';
                        mapDiv.onclick = () => window.open(gmapsUrl, '_blank', 'noopener');


                    },
                    (err) => {
                        const mapErr = {
                            1: 'Permission denied',
                            2: 'Position unavailable',
                            3: 'Timeout'
                        };
                        statusEl.textContent = `Gagal mengambil lokasi: ${mapErr[err.code] || 'Unknown error'}`;
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            });
        })();
    </script>

    {{-- Script preview foto --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('photoInput');
            const previewContainer = document.getElementById('photoPreviewContainer');
            const previewImg = document.getElementById('photoPreview');

            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        previewContainer.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.style.display = 'none';
                    previewImg.src = '';
                }
            });
        });
    </script>
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            (function() {
                const CUTI_VALUE = @json(\App\Enums\LeaveType::CUTI->value);
                const startInput = document.querySelector('input[name="start_date"]');
                const ruleEl = document.getElementById('cuti-rule');
                const warnEl = document.getElementById('h7-warning');
                const typeRadios = document.querySelectorAll('input[name="type"]');

                function parseYMD(ymd) {
                    if (!ymd) return null;
                    const [y, m, d] = ymd.split('-').map(Number);
                    const dt = new Date(y, m - 1, d);
                    dt.setHours(0, 0, 0, 0);
                    return dt;
                }

                function todayStart() {
                    const t = new Date();
                    t.setHours(0, 0, 0, 0);
                    return t;
                }

                function boundaryDateH7() {
                    const t = todayStart();
                    const b = new Date(t);
                    b.setDate(b.getDate() + 7);
                    return b;
                }

                function formatID(d) {
                    return d.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    });
                }

                function getSelectedType() {
                    const r = document.querySelector('input[name="type"]:checked');
                    return r ? r.value : null;
                }

                function isCutiSelected() {
                    return getSelectedType() === CUTI_VALUE;
                }

                function renderRuleVisibility() {
                    if (!ruleEl || !warnEl) return;

                    if (isCutiSelected()) {
                        ruleEl.style.display = 'block';
                        ruleEl.innerHTML = `Ketentuan: pengajuan minimal H-7 dari hari ini (≥ <b>${formatID(boundaryDateH7())}</b>).`;
                        updateWarning();
                    } else {
                        ruleEl.style.display = 'none';
                        ruleEl.textContent = '';
                        warnEl.style.display = 'none';
                        warnEl.textContent = '';
                    }
                }

                function updateWarning() {
                    if (!isCutiSelected()) {
                        warnEl.style.display = 'none';
                        warnEl.textContent = '';
                        return;
                    }

                    const today = todayStart();
                    const start = parseYMD(startInput?.value || '');
                    if (!(start instanceof Date) || isNaN(start)) {
                        warnEl.style.display = 'none';
                        warnEl.textContent = '';
                        return;
                    }

                    const diffDays = Math.round((start - today) / (1000 * 60 * 60 * 24));
                    if (diffDays < 7 && diffDays >= 0) {
                        warnEl.style.display = 'block';
                        warnEl.textContent =
                            `Pengajuan dilakukan ${diffDays} hari sebelum tanggal mulai cuti (kurang dari H-7). ` +
                            `Pengajuan tetap bisa diproses, namun akan ada potongan sesuai kebijakan perusahaan.`;
                    } else {
                        warnEl.style.display = 'none';
                        warnEl.textContent = '';
                    }
                }

                startInput?.addEventListener('input', updateWarning);
                startInput?.addEventListener('change', updateWarning);
                startInput?.addEventListener('focus', updateWarning);

                typeRadios.forEach(r => {
                    r.addEventListener('change', () => {
                        renderRuleVisibility();
                    });
                });

                renderRuleVisibility();
            })();
        });
    </script>
    @endpush
</x-app>