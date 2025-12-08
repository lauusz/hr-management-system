<x-app title="Master Absensi">

    <div class="card" style="margin-bottom:14px;">
        <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">

            @php
            $rangeValue = '';
            if (!empty($date_start) && !empty($date_end)) {
            $rangeValue = $date_start . ' sampai ' . $date_end;
            } elseif (!empty($date_start)) {
            $rangeValue = $date_start;
            }
            @endphp

            <div>
                <label style="font-size:0.85rem;display:block;margin-bottom:4px;">Tanggal</label>
                <input
                    type="text"
                    id="date_range"
                    name="date_range"
                    value="{{ $rangeValue }}"
                    placeholder="Pilih rentang tanggal"
                    autocomplete="off"
                    style="padding:6px 10px;border-radius:8px;border:1px solid #ddd;font-size:0.85rem;min-width:200px;">

                <input type="hidden" name="date_start" id="date_start" value="{{ $date_start ?? '' }}">
                <input type="hidden" name="date_end" id="date_end" value="{{ $date_end ?? '' }}">
            </div>

            <div>
                <label style="font-size:0.85rem;display:block;margin-bottom:4px;">Status</label>
                <select name="status" style="padding:6px 10px;border-radius:8px;border:1px solid #ddd;font-size:0.85rem;min-width:120px;">
                    <option value="">Semua</option>
                    <option value="HADIR" @selected(($status ?? '' )=='HADIR' )>Hadir</option>
                    <option value="TERLAMBAT" @selected(($status ?? '' )=='TERLAMBAT' )>Terlambat</option>
                </select>
            </div>

            <div>
                <label style="font-size:0.85rem;display:block;margin-bottom:4px;">Nama</label>
                <input type="text"
                    name="q"
                    value="{{ $q ?? '' }}"
                    placeholder="Cari nama karyawan..."
                    style="padding:6px 10px;border-radius:8px;border:1px solid:#ddd;font-size:0.85rem;min-width:200px;">
            </div>

            <button style="padding:8px 14px;background:#1e4a8d;color:#fff;border:none;
                       border-radius:999px;cursor:pointer;font-size:0.85rem;white-space:nowrap;">
                Filter
            </button>
            <a href="{{ route('hr.attendances.index') }}"
                style="padding:6px 10px;border-radius:999px;border:1px solid #d1d5db;background:#fff;color:#374151;font-size:0.8rem;text-decoration:none;white-space:nowrap;">
                Reset
            </a>
        </form>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <div style="overflow-x:auto;">
            <table class="table" style="width:100%;min-width:1200px;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Nama
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Shift
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Jam Kerja
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Clock-in
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Clock-out
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Telat
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Status
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Foto In
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Lokasi In
                        </th>
                        <th style="text-align:left;padding:10px 12px;border-bottom:1px solid #e5e7eb;font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
                            Lokasi Out
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($items as $at)
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:10px 12px;vertical-align:middle;">
                            <span style="font-size:0.9rem;font-weight:500;color:#111827;">
                                {{ $at->user->name }}
                            </span>
                        </td>

                        <td style="padding:10px 12px;vertical-align:middle;">
                            <span style="font-size:0.85rem;color:#374151;">
                                {{ $at->shift->name ?? '-' }}
                            </span>
                        </td>

                        <td style="padding:10px 12px;vertical-align:middle;">
                            @if($at->shift)
                            <span style="font-size:0.85rem;color:#111827;">
                                {{ $at->shift->start_time_label }} - {{ $at->shift->end_time_label }}
                            </span>
                            @else
                            <span style="opacity:.6;">-</span>
                            @endif
                        </td>

                        <td style="padding:10px 12px;vertical-align:middle;">
                            @if($at->clock_in_at)
                            <div style="font-size:0.83rem;color:#111827;line-height:1.3;">
                                <div style="font-weight:600;">
                                    {{ $at->clock_in_at->format('d/m/Y') }}
                                </div>
                                <div style="opacity:.8;">
                                    {{ $at->clock_in_at->format('H:i') }}
                                </div>
                            </div>
                            @else
                            <span style="opacity:.6;font-size:0.85rem;">-</span>
                            @endif
                        </td>

                        <td style="padding:10px 12px;vertical-align:middle;">
                            @if($at->clock_out_at)
                            <div style="font-size:0.83rem;color:#111827;line-height:1.3;">
                                <div style="font-weight:600;">
                                    {{ $at->clock_out_at->format('d/m/Y') }}
                                </div>
                                <div style="opacity:.8;">
                                    {{ $at->clock_out_at->format('H:i') }}
                                </div>
                            </div>
                            @else
                            <span style="opacity:.6;font-size:0.85rem;">-</span>
                            @endif
                        </td>

                        <td style="padding:10px 12px;vertical-align:middle;">
                            @if($at->late_minutes > 0)
                            @php
                            $jam = floor($at->late_minutes / 60);
                            $menit = $at->late_minutes % 60;
                            $hasil = '';

                            if($jam > 0){
                            $hasil .= $jam . ' jam';
                            }
                            if($menit > 0){
                            $hasil .= ($hasil ? ' ' : '') . $menit . ' menit';
                            }
                            @endphp

                            <span style="color:#b91c1c;font-weight:600;font-size:0.85rem;">
                                {{ $hasil }}
                            </span>
                            @else
                            <span style="opacity:.6;font-size:0.85rem;">-</span>
                            @endif
                        </td>

                        <td style="padding:10px 12px;vertical-align:middle;">
                            @if ($at->status === 'TERLAMBAT')
                            <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;background:#fee2e2;color:#b91c1c;font-weight:600;font-size:0.78rem;">
                                TERLAMBAT
                            </span>
                            @elseif ($at->status === 'HADIR')
                            <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;background:#dcfce7;color:#166534;font-weight:600;font-size:0.78rem;">
                                HADIR
                            </span>
                            @else
                            <span style="opacity:.6;font-size:0.85rem;">
                                {{ $at->status ?? '-' }}
                            </span>
                            @endif
                        </td>

                        <td style="padding:10px 12px;vertical-align:middle;">
                            @if($at->clock_in_photo)
                            <button type="button"
                                class="btn-view-photo"
                                data-photo-url="{{ asset('storage/'.$at->clock_in_photo) }}"
                                data-employee-name="{{ $at->user->name }}"
                                data-datetime="{{ $at->clock_in_at ? $at->clock_in_at->format('d/m/Y H:i') : '' }}"
                                style="padding:4px 10px;border-radius:999px;background:#1e40af;color:#fff;
                                           font-size:0.8rem;border:none;cursor:pointer;white-space:nowrap;">
                                View
                            </button>
                            @else
                            <span style="opacity:.6;font-size:0.85rem;">-</span>
                            @endif
                        </td>

                        <td style="padding:10px 12px;vertical-align:middle;">
                            @if($at->clock_in_lat && $at->clock_in_lng)
                            <a href="https://www.google.com/maps?q={{ $at->clock_in_lat }},{{ $at->clock_in_lng }}"
                                target="_blank"
                                style="padding:4px 10px;border-radius:999px;background:#0369a1;color:#fff;
                                      font-size:0.8rem;text-decoration:none;display:inline-block;white-space:nowrap;">
                                Maps
                            </a>
                            @else
                            <span style="opacity:.6;font-size:0.85rem;">-</span>
                            @endif
                        </td>

                        <td style="padding:10px 12px;vertical-align:middle;">
                            @if($at->clock_out_lat && $at->clock_out_lng)
                            <a href="https://www.google.com/maps?q={{ $at->clock_out_lat }},{{ $at->clock_out_lng }}"
                                target="_blank"
                                style="padding:4px 10px;border-radius:999px;background:#0369a1;color:#fff;
                                      font-size:0.8rem;text-decoration:none;display:inline-block;white-space:nowrap;">
                                Maps
                            </a>
                            @else
                            <span style="opacity:.6;font-size:0.85rem;">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" style="text-align:center;opacity:.7;padding:12px;font-size:0.9rem;">
                            Tidak ada data absensi pada tanggal ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:12px;">
        <x-pagination :items="$items" />
    </div>

    <x-modal
        id="attendance-photo-modal"
        title="Foto Presensi"
        type="info"
        cancelLabel="Tutup">
        <div style="display:flex;flex-direction:column;gap:10px;">
            <div id="attendancePhotoMeta" style="font-size:0.85rem;color:#4b5563;">
            </div>
            <div style="border-radius:10px;border:1px solid #e5e7eb;max-height:70vh;display:flex;align-items:center;justify-content:center;background:#000;">
                <img id="attendancePhotoImg"
                    src=""
                    alt="Foto presensi"
                    style="max-width:100%;max-height:70vh;width:auto;height:auto;display:block;">
            </div>
        </div>
    </x-modal>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('attendance-photo-modal');
            var modalImg = document.getElementById('attendancePhotoImg');
            var modalMeta = document.getElementById('attendancePhotoMeta');

            document.querySelectorAll('.btn-view-photo').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var url = this.getAttribute('data-photo-url');
                    var name = this.getAttribute('data-employee-name') || '';
                    var datetime = this.getAttribute('data-datetime') || '';

                    if (!url) return;

                    modalImg.src = url;

                    var metaText = '';
                    if (name) {
                        metaText += '<div><strong>' + name + '</strong></div>';
                    }
                    if (datetime) {
                        metaText += '<div style="opacity:.8;">Clock-in: ' + datetime + '</div>';
                    }
                    modalMeta.innerHTML = metaText;

                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                });
            });
        });
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var rangeInput = document.getElementById('date_range');
            var startHidden = document.getElementById('date_start');
            var endHidden = document.getElementById('date_end');

            if (typeof flatpickr === 'function' && rangeInput) {
                flatpickr(rangeInput, {
                    mode: "range",
                    dateFormat: "Y-m-d",
                    allowInput: true,
                    locale: {
                        rangeSeparator: " sampai "
                    },
                    onChange: function(selectedDates, dateStr) {
                        if (!dateStr) {
                            startHidden.value = "";
                            endHidden.value = "";
                            return;
                        }

                        var parts = dateStr.split(" sampai ");

                        if (parts.length === 1) {
                            startHidden.value = parts[0];
                            endHidden.value = parts[0];
                        } else {
                            startHidden.value = parts[0];
                            endHidden.value = parts[1];
                        }
                    }
                });
            }
        });
    </script>


</x-app>