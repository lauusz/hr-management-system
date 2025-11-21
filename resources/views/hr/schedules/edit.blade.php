<x-app title="Edit Jadwal Karyawan">

    <div class="card" style="max-width:720px;margin:0 auto;">
        <form action="{{ route('hr.schedules.update', $schedule->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="margin-bottom:12px;">
                <label><b>Karyawan</b></label>
                <select name="user_id" required
                        style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;">
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected($schedule->user_id==$u->id)>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:12px;">
                <label><b>Shift</b></label>
                <select name="shift_id" required
                        style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;">
                    @foreach($shifts as $s)
                        <option value="{{ $s->id }}" @selected($schedule->shift_id==$s->id)>
                            {{ $s->name }} ({{ $s->start_time }} - {{ $s->end_time }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:12px;">
                <label><b>Lokasi Presensi</b></label>
                <select name="location_id" required
                        style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;">
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" @selected($schedule->location_id==$loc->id)>
                            {{ $loc->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                    style="padding:10px 14px;border-radius:8px;background:#1e4a8d;color:#fff;border:none;cursor:pointer;">
                Update Jadwal
            </button>
        </form>
    </div>

</x-app>
