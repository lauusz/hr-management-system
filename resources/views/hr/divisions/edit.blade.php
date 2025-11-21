<x-app title="Edit Divisi">

    @if ($errors->any())
        <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;border-radius:8px;">
            {{ $errors->first() }}
        </div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <div>
            <h1 style="font-size:1.4rem;font-weight:700;margin-bottom:4px;">Edit Divisi</h1>
            <p style="font-size:.9rem;opacity:.75;">Perbarui nama divisi dan supervisor yang bertanggung jawab.</p>
        </div>
        <a href="{{ route('hr.divisions.index') }}"
           style="font-size:.9rem;padding:6px 10px;border-radius:999px;border:1px solid #d1d5db;text-decoration:none;color:#111827;">
            ← Kembali
        </a>
    </div>

    <form class="card" method="POST" action="{{ route('hr.divisions.update', $item->id) }}" style="max-width:520px;">
        @csrf
        @method('PUT')

        <div style="display:grid;grid-template-columns:1fr;gap:12px;">
            <div>
                <label style="display:block;font-size:.9rem;margin-bottom:4px;">Nama Divisi</label>
                <input type="text" name="name" value="{{ old('name', $item->name) }}" required
                       style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
            </div>

            <div>
                <label style="display:block;font-size:.9rem;margin-bottom:4px;">Supervisor</label>
                <select name="supervisor_id"
                        style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
                    <option value="">Tidak ada / Belum ditentukan</option>
                    @foreach ($supervisors as $sup)
                        <option value="{{ $sup->id }}"
                            @selected(old('supervisor_id', $item->supervisor_id) == $sup->id)>
                            {{ $sup->name }} ({{ $sup->username }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="margin-top:16px;display:flex;gap:10px;">
            <button type="submit"
                    style="padding:8px 16px;border-radius:999px;border:none;background:#1e4a8d;color:white;font-size:.9rem;cursor:pointer;">
                Simpan Perubahan
            </button>
            <a href="{{ route('hr.divisions.index') }}"
               style="padding:8px 16px;border-radius:999px;border:1px solid #d1d5db;font-size:.9rem;text-decoration:none;color:#111827;">
                Batal
            </a>
        </div>
    </form>

</x-app>
