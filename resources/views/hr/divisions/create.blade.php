<x-app title="Tambah Divisi">

    @if ($errors->any())
        <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;border-radius:8px;">
            {{ $errors->first() }}
        </div>
    @endif

    <div style="margin-bottom:16px;">
        <p style="font-size:.9rem;opacity:.75;">Buat divisi baru dan tentukan supervisor bila diperlukan.</p>
    </div>

    <form method="POST"
          action="{{ route('hr.divisions.store') }}"
          class="card"
          style="max-width:480px;margin:0 auto;padding:16px;display:flex;flex-direction:column;gap:14px;">
        @csrf

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="name" style="font-size:0.9rem;font-weight:500;">Nama Divisi</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                style="padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;width:100%;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="supervisor" style="font-size:0.9rem;font-weight:500;">Supervisor</label>
            <select
                id="supervisor"
                name="supervisor_id"
                style="padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;width:100%;">
                <option value="">Tidak ada / Belum ditentukan</option>
                @foreach ($supervisors as $sup)
                    <option value="{{ $sup->id }}" @selected(old('supervisor_id') == $sup->id)>
                        {{ $sup->name }} ({{ $sup->username }})
                    </option>
                @endforeach
            </select>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px;">
            <button type="submit"
                    style="padding:8px 16px;border-radius:999px;border:none;background:#1e4a8d;color:white;font-size:.9rem;cursor:pointer;">
                Simpan
            </button>

            <a href="{{ route('hr.organization') }}"
               style="padding:8px 16px;border-radius:999px;border:1px solid #d1d5db;font-size:.9rem;text-decoration:none;color:#111827;display:flex;align-items:center;">
                Batal
            </a>
        </div>
    </form>

</x-app>
