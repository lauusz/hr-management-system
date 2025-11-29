<x-app title="Tambah Jadwal Karyawan">

    <div class="card" style="max-width:720px;margin:0 auto;">
        <form action="{{ route('hr.schedules.store') }}" method="POST">
            @csrf

            <div style="margin-bottom:12px;">
                <label><b>Karyawan</b></label>
                <select name="user_id" required
                        style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;">
                    <option value="">-- pilih karyawan --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('user_id')==$u->id)>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:12px;">
                <label><b>Shift</b></label>
                <select name="shift_id" required
                        style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;">
                    <option value="">-- pilih shift --</option>
                    @foreach($shifts as $s)
                        <option value="{{ $s->id }}" @selected(old('shift_id')==$s->id)>
                            {{ $s->name }} ({{ $s->start_time }} - {{ $s->end_time }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:12px;">
                <label><b>Lokasi Presensi</b></label>
                <select name="location_id" required
                        style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;">
                    <option value="">-- pilih lokasi --</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" @selected(old('location_id')==$loc->id)>
                            {{ $loc->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-top:8px;display:flex;gap:10px;flex-wrap:wrap;">
                <button type="submit"
                    style="padding:8px 16px;border-radius:999px;border:none;background:#1e4a8d;color:white;font-size:.9rem;cursor:pointer;">
                    Simpan Jadwal
                </button>
                 <a href="{{ route('hr.schedules.index') }}"
                    style="padding:8px 16px;border-radius:999px;border:1px solid #d1d5db;font-size:.9rem;text-decoration:none;color:#111827;display:flex;align-items:center;">
                    Batal
                </a>
            </div>
        </form>
    </div>

</x-app>
