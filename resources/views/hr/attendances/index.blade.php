<x-app title="Master Absensi">

    <div class="card" style="margin-bottom:14px;">
        <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <div>
                <label style="font-size:0.85rem;">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}"
                       style="padding:6px 8px;border-radius:8px;border:1px solid #ddd;">
            </div>

            <div>
                <label style="font-size:0.85rem;">Status</label>
                <select name="status" style="padding:6px 8px;border-radius:8px;border:1px solid:#ddd;">
                    <option value="">Semua</option>
                    <option value="HADIR" @selected($status=='HADIR')>Hadir</option>
                    <option value="TERLAMBAT" @selected($status=='TERLAMBAT')>Terlambat</option>
                </select>
            </div>

            <div>
                <label style="font-size:0.85rem;">Nama</label>
                <input type="text"
                       name="q"
                       value="{{ $q ?? '' }}"
                       placeholder="Cari nama karyawan..."
                       style="padding:6px 8px;border-radius:8px;border:1px solid #ddd;">
            </div>

            <button style="padding:8px 12px;background:#1e4a8d;color:#fff;border:none;
                           border-radius:8px;cursor:pointer;">
                Filter
            </button>
        </form>
    </div>

    <div style="overflow-x:auto;">
        <table class="table" style="min-width:1200px;">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Shift</th>
                    <th>Jam Kerja</th>
                    <th>Clock-in</th>
                    <th>Clock-out</th>
                    <th>Telat</th>
                    <th>Status</th>
                    <th>Foto In</th>
                    <th>Lokasi In</th>
                    <th>Lokasi Out</th>
                </tr>
            </thead>

            <tbody>
                @forelse($items as $at)
                    <tr>
                        <td>{{ $at->user->name }}</td>

                        <td>{{ $at->shift->name ?? '-' }}</td>

                        <td>
                            @if($at->shift)
                                {{ $at->shift->start_time_label }}-{{ $at->shift->end_time_label }}
                            @else
                                -
                            @endif
                        </td>

                        <td>{{ $at->clock_in_at ? $at->clock_in_at->format('H:i') : '-' }}</td>
                        <td>{{ $at->clock_out_at ? $at->clock_out_at->format('H:i') : '-' }}</td>

                        <td>
                            @if($at->late_minutes > 0)
                                <span style="color:#b91c1c;font-weight:600;">
                                    {{ $at->late_minutes }} menit
                                </span>
                            @else
                                <span style="opacity:.6;">0</span>
                            @endif
                        </td>

                        <td>
                            @if ($at->status === 'TERLAMBAT')
                                <span style="color:#b91c1c;font-weight:600;">TERLAMBAT</span>
                            @elseif ($at->status === 'HADIR')
                                <span style="color:#065f46;font-weight:600;">HADIR</span>
                            @else
                                <span style="opacity:.6;">{{ $at->status ?? '-' }}</span>
                            @endif
                        </td>

                        <td>
                            @if($at->clock_in_photo)
                                <button type="button"
                                        class="btn-view-photo"
                                        data-photo-url="{{ asset('storage/'.$at->clock_in_photo) }}"
                                        style="padding:4px 10px;border-radius:999px;background:#1e40af;color:#fff;
                                               font-size:0.8rem;border:none;cursor:pointer;">
                                    View
                                </button>
                            @else
                                <span style="opacity:.6;">-</span>
                            @endif
                        </td>

                        <td>
                            @if($at->clock_in_lat && $at->clock_in_lng)
                                <a href="https://www.google.com/maps?q={{ $at->clock_in_lat }},{{ $at->clock_in_lng }}"
                                   target="_blank"
                                   style="padding:4px 10px;border-radius:999px;background:#0369a1;color:#fff;
                                          font-size:0.8rem;text-decoration:none;display:inline-block;">
                                    Maps
                                </a>
                            @else
                                <span style="opacity:.6;">-</span>
                            @endif
                        </td>

                        <td>
                            @if($at->clock_out_lat && $at->clock_out_lng)
                                <a href="https://www.google.com/maps?q={{ $at->clock_out_lat }},{{ $at->clock_out_lng }}"
                                   target="_blank"
                                   style="padding:4px 10px;border-radius:999px;background:#0369a1;color:#fff;
                                          font-size:0.8rem;text-decoration:none;display:inline-block;">
                                    Maps
                                </a>
                            @else
                                <span style="opacity:.6;">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align:center;opacity:.7;padding:12px;">
                            Tidak ada data absensi pada tanggal ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :items="$items" />

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
                });
            });

            modalClose.addEventListener('click', function () {
                modal.style.display = 'none';
                modalImg.src = '';
            });

            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    modalImg.src = '';
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && modal.style.display === 'flex') {
                    modal.style.display = 'none';
                    modalImg.src = '';
                }
            });
        });
    </script>

</x-app>
