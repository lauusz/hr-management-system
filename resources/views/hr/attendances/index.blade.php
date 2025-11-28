<x-app title="Master Absensi">

    <div class="card" style="margin-bottom:14px;">
        <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <div>
                <label style="font-size:0.85rem;display:block;margin-bottom:4px;">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}"
                       style="padding:6px 10px;border-radius:8px;border:1px solid #ddd;font-size:0.85rem;">
            </div>

            <div>
                <label style="font-size:0.85rem;display:block;margin-bottom:4px;">Status</label>
                <select name="status" style="padding:6px 10px;border-radius:8px;border:1px solid #ddd;font-size:0.85rem;min-width:120px;">
                    <option value="">Semua</option>
                    <option value="HADIR" @selected($status=='HADIR')>Hadir</option>
                    <option value="TERLAMBAT" @selected($status=='TERLAMBAT')>Terlambat</option>
                </select>
            </div>

            <div>
                <label style="font-size:0.85rem;display:block;margin-bottom:4px;">Nama</label>
                <input type="text"
                       name="q"
                       value="{{ $q ?? '' }}"
                       placeholder="Cari nama karyawan..."
                       style="padding:6px 10px;border-radius:8px;border:1px solid #ddd;font-size:0.85rem;min-width:200px;">
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
                                    <span style="color:#b91c1c;font-weight:600;font-size:0.85rem;">
                                        {{ $at->late_minutes }} menit
                                    </span>
                                @else
                                    <span style="opacity:.6;font-size:0.85rem;">0</span>
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

                            <td style="padding:10px 12px;vertical-align:middle%;">
                                @if($at->clock_in_photo)
                                    <button type="button"
                                            class="btn-view-photo"
                                            data-photo-url="{{ asset('storage/'.$at->clock_in_photo) }}"
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

    <div id="photoModal"
         style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.75);
                z-index:9999;align-items:center;justify-content:center;">
        <div style="background:#ffffff;border-radius:10px;padding:10px;max-width:90%;max-height:90%;
                    box-shadow:0 10px 25px rgba(0,0,0,0.25);position:relative;">
            <button type="button"
                    id="photoModalClose"
                    style="position:absolute;top:6px;right:8px;border:none;background:transparent;
                           font-size:20px;cursor:pointer;line-height:1;">
                ×
            </button>
            <img id="photoModalImg"
                 src=""
                 alt="Attendance Photo"
                 style="max-width:100%;max-height:80vh;display:block;border-radius:6px;">
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('photoModal');
            var modalImg = document.getElementById('photoModalImg');
            var modalClose = document.getElementById('photoModalClose');

            document.querySelectorAll('.btn-view-photo').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var url = this.getAttribute('data-photo-url');
                    if (!url) return;
                    modalImg.src = url;
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                });
            });

            function closeModal() {
                modal.style.display = 'none';
                modalImg.src = '';
                document.body.style.overflow = '';
            }

            modalClose.addEventListener('click', function () {
                closeModal();
            });

            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && modal.style.display === 'flex') {
                    closeModal();
                }
            });
        });
    </script>

</x-app>
