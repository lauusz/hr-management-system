<x-app title="Tambah PT">

    @if ($errors->any())
    <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;">
        {{ $errors->first() }}
    </div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <p style="font-size:.9rem;opacity:.75;margin:0;">
            Tambahkan nama PT yang akan digunakan di data karyawan.
        </p>
    </div>

    <form method="POST"
          action="{{ route('hr.pts.store') }}"
          class="card"
          style="max-width:520px;padding:16px;margin:0 auto;display:flex;flex-direction:column;gap:14px;">
        @csrf

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="name" style="font-size:.9rem;font-weight:500;">Nama PT</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                placeholder="Contoh: TRIGUNA"
                required
                style="padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:6px;">
            <button type="submit"
                style="padding:8px 16px;border-radius:999px;border:none;background:#1e4a8d;color:white;font-size:.9rem;cursor:pointer;">
                Simpan
            </button>
            <a href="{{ route('hr.pts.index') }}"
                style="padding:8px 16px;border-radius:999px;border:1px solid #d1d5db;font-size:.9rem;text-decoration:none;color:#111827;display:flex;align-items:center;">
                Batal
            </a>
        </div>
    </form>

</x-app>
