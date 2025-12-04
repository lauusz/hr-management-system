<x-app title="Edit PT">

    @if ($errors->any())
    <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;">
        {{ $errors->first() }}
    </div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <p style="font-size:.9rem;opacity:.75;margin:0;">
            Perbarui nama PT yang digunakan di sistem.
        </p>
    </div>

    <form method="POST"
          action="{{ route('hr.pts.update', $item->id) }}"
          class="card"
          style="max-width:520px;padding:16px;margin:0 auto;display:flex;flex-direction:column;gap:14px;">
        @csrf
        @method('PUT')

        <div style="display:flex;flex-direction:column;gap:4px;">
            <label for="name" style="font-size:.9rem;font-weight:500;">Nama PT</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name', $item->name) }}"
                required
                style="padding:8px 10px;border-radius:8px;border:1px solid #d1d5db;font-size:.9rem;">
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:6px;">
            <button type="submit"
                style="padding:8px 16px;border-radius:999px;border:none;background:#1e4a8d;color:white;font-size:.9rem;cursor:pointer;">
                Simpan Perubahan
            </button>
            <a href="{{ route('hr.pts.index') }}"
                style="padding:8px 16px;border-radius:999px;border:1px solid #d1d5db;font-size:.9rem;text-decoration:none;color:#111827;display:flex;align-items:center;">
                Batal
            </a>
        </div>
    </form>

</x-app>
