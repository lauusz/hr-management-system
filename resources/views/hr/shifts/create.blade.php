<x-app title="Tambah Shift">

    @if ($errors->any())
    <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;border-radius:8px;">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('hr.shifts.store') }}" class="card" style="max-width:400px">
        @csrf

        <label>Nama Shift</label>
        <input type="text" name="name" value="{{ old('name') }}" required>

        <label>Jam Masuk</label>
        <input type="time" name="start_time" value="{{ old('start_time') }}" required>

        <label>Jam Pulang</label>
        <input type="time" name="end_time" value="{{ old('end_time') }}" required>

        <br><br>
        <button type="submit"
            style="padding:8px 16px;border-radius:999px;border:none;background:#1e4a8d;color:white;font-size:.9rem;cursor:pointer;">
            Simpan
        </button>
        <a href="{{ route('hr.shifts.index') }}"
            style="padding:8px 16px;border-radius:999px;border:1px solid #d1d5db;font-size:.9rem;text-decoration:none;color:#111827;">
            Batal
        </a>
    </form>

</x-app>