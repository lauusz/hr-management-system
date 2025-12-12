<x-app title="Edit Jadwal Karyawan">

    <div class="card" style="max-width:720px;margin:0 auto;padding:20px;">

        <form action="{{ route('hr.schedules.update', $schedule->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="margin-bottom:14px;">
                <label style="font-size:0.9rem;font-weight:600;display:block;margin-bottom:4px;">
                    Karyawan
                </label>
                <div style="padding:10px 12px;border:1px solid #ddd;border-radius:8px;background:#f9fafb;font-size:0.9rem;">
                    {{ $schedule->user->name }}
                </div>
                <input type="hidden" name="user_id" value="{{ $schedule->user_id }}">
            </div>

            <div style="margin-bottom:14px;">
                <label style="font-size:0.9rem;font-weight:600;display:block;margin-bottom:4px;">
                    Shift
                </label>

                <select name="shift_id" required
                        style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid #ddd;font-size:0.9rem;">
                    @foreach($shifts as $s)
                        <option value="{{ $s->id }}" @selected($schedule->shift_id == $s->id)>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:14px;">
                <label style="font-size:0.9rem;font-weight:600;display:block;margin-bottom:4px;">
                    Lokasi Presensi
                </label>

                <select name="location_id" required
                        style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid #ddd;font-size:0.9rem;">
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" @selected($schedule->location_id == $loc->id)>
                            {{ $loc->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap;">
                <button type="submit"
                    style="padding:8px 18px;border-radius:999px;border:none;background:#1e4a8d;color:white;font-size:.9rem;cursor:pointer;">
                    Update Jadwal
                </button>

            <a href="{{ route('hr.schedules.index') }}"
                   style="padding:8px 18px;border-radius:999px;border:1px solid #d1d5db;font-size:.9rem;text-decoration:none;color:#111827;display:flex;align-items:center;">
                    Batal
                </a>
            </div>

        </form>

    </div>

</x-app>
