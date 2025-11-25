<x-app title="Edit Jabatan">
    @if ($errors->any())
        <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;border-radius:8px;">
            {{ $errors->first() }}
        </div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <div>
            <p style="font-size:.9rem;opacity:.75;">Perbarui informasi jabatan.</p>
        </div>
        <a href="{{ route('hr.positions.index') }}"
           style="font-size:.9rem;padding:6px 10px;border-radius:999px;border:1px solid #d1d5db;text-decoration:none;color:#111827;">
            ← Kembali
        </a>
    </div>

    <form class="card"
          method="POST"
          action="{{ route('hr.positions.update', $item->id) }}"
          style="max-width:480px;margin:0 auto;padding:16px;display:flex;flex-direction:column;gap:14px;">
        @csrf
        @method('PUT')

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="name" style="font-size:.9rem;font-weight:500;">Nama Jabatan</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name', $item->name) }}"
                required
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="division_id" style="font-size:.9rem;font-weight:500;">Divisi</label>
            <select
                id="division_id"
                name="division_id"
                style="width:100%;padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
                <option value="">Tidak ada / Umum</option>
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}" @selected(old('division_id', $item->division_id) == $division->id)>
                        {{ $division->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="display:flex;align-items:center;gap:8px;margin-top:4px;">
            <input
                id="is_active"
                type="checkbox"
                name="is_active"
                value="1"
                @checked(old('is_active', $item->is_active))
                style="width:16px;height:16px;">
            <label for="is_active" style="font-size:.9rem;cursor:pointer;">
                Jabatan aktif dan dapat digunakan
            </label>
        </div>

        <div style="margin-top:8px;display:flex;gap:10px;flex-wrap:wrap;">
            <button type="submit"
                    style="padding:8px 16px;border-radius:999px;border:none;background:#1e4a8d;color:white;font-size:.9rem;cursor:pointer;">
                Simpan Perubahan
            </button>
            <a href="{{ route('hr.positions.index') }}"
               style="padding:8px 16px;border-radius:999px;border:1px solid #d1d5db;font-size:.9rem;text-decoration:none;color:#111827;display:flex;align-items:center;">
                Batal
            </a>
        </div>
    </form>
</x-app>
