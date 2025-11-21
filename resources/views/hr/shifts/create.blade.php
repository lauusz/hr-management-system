<x-app title="Tambah Shift">

    @if ($errors->any())
        <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;border-radius:8px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST"
          action="{{ route('hr.shifts.store') }}"
          class="card"
          style="max-width:480px;margin:0 auto;padding:16px;display:flex;flex-direction:column;gap:14px;">
        @csrf

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="name" style="font-size:0.9rem;font-weight:500;">Nama Shift</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                style="padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:0.9rem;width:100%;"
            >
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="start_time" style="font-size:0.9rem;font-weight:500;">Jam Masuk</label>
            <input
                id="start_time"
                type="time"
                name="start_time"
                value="{{ old('start_time') }}"
                required
                style="padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:0.9rem;width:100%;"
            >
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="end_time" style="font-size:0.9rem;font-weight:500;">Jam Pulang</label>
            <input
                id="end_time"
                type="time"
                name="end_time"
                value="{{ old('end_time') }}"
                required
                style="padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:0.9rem;width:100%;"
            >
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
            <button type="submit"
                    style="padding:8px 16px;border-radius:999px;border:none;background:#1e4a8d;color:white;font-size:.9rem;cursor:pointer;">
                Simpan
            </button>
            <a href="{{ route('hr.shifts.index') }}"
               style="padding:8px 16px;border-radius:999px;border:1px solid #d1d5db;font-size:.9rem;text-decoration:none;color:#111827;display:inline-flex;align-items:center;justify-content:center;">
                Batal
            </a>
        </div>
    </form>

</x-app>
