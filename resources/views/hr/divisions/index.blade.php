<x-app title="Master Divisi">

    @if(session('success'))
    <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="card" style="margin-bottom:12px;background:#ffecec;color:#a40000;padding:8px 10px;">
        {{ session('error') }}
    </div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:10px;flex-wrap:wrap;">
        <div style="font-size:0.9rem;opacity:.7;">
            Daftar divisi yang terdaftar di sistem. Setiap divisi dapat memiliki supervisor.
        </div>

        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <form method="GET" action="{{ route('hr.divisions.index') }}" style="display:flex;gap:6px;align-items:center;">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="Cari divisi..."
                    style="padding:6px 8px;border-radius:8px;border:1px solid #ddd;font-size:0.85rem;">
                <button
                    type="submit"
                    style="padding:6px 10px;border-radius:8px;border:none;background:#1e4a8d;color:#fff;font-size:0.85rem;cursor:pointer;">
                    Cari
                </button>
            </form>

            <a href="{{ route('hr.divisions.create') }}"
               style="padding:6px 10px;border-radius:8px;background:#1e4a8d;color:#fff;font-size:0.85rem;text-decoration:none;white-space:nowrap;">
                + Tambah Divisi
            </a>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Nama Divisi</th>
                <th>Supervisor</th>
                <th style="width:160px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $division)
            <tr>
                <td>{{ $division->name }}</td>
                <td>{{ $division->supervisor->name ?? '-' }}</td>
                <td style="display:flex;gap:10px;">
                    <a href="{{ route('hr.divisions.edit', $division->id) }}"
                       style="font-size:0.85rem;color:#1e4a8d;text-decoration:none;">
                        Edit
                    </a>

                    <form action="{{ route('hr.divisions.destroy', $division->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus divisi ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                style="font-size:0.85rem;color:#b91c1c;background:none;border:none;cursor:pointer;">
                            Hapus
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding:12px;text-align:center;opacity:.7;">
                    Belum ada divisi ditambahkan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <x-pagination :items="$items" />

</x-app>
