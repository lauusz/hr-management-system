<x-app title="Daftar Karyawan">

    @if(session('success'))
        <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <div style="font-size:0.9rem;opacity:.7;">
            Daftar karyawan yang terdaftar di sistem. HR dapat mengelola data karyawan.
        </div>

        <div style="display:flex;gap:8px;align-items:center;">
            <a href="{{ route('hr.employees.create') }}"
               style="padding:6px 10px;border-radius:8px;background:#1e4a8d;color:#fff;font-size:0.85rem;text-decoration:none;">
                + Tambah Karyawan
            </a>

            <form method="GET" action="{{ route('hr.employees.index') }}" style="display:flex;gap:6px;align-items:center;">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="Cari nama..."
                    style="padding:6px 8px;border-radius:8px;border:1px solid #ddd;font-size:0.85rem;"
                >
                <button
                    type="submit"
                    style="padding:6px 10px;border-radius:8px;border:none;background:#1e4a8d;color:#fff;font-size:0.85rem;cursor:pointer;">
                    Cari
                </button>
            </form>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Role</th>
                <th>Status</th>
                <th style="width:180px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $emp)
                <tr>
                    <td>{{ $emp->name }}</td>
                    <td>{{ $emp->role ?? '-' }}</td>
                    <td>{{ $emp->status }}</td>
                    <td style="display:flex;gap:10px;">
                        <a href="{{ route('hr.employees.edit', $emp->id) }}"
                           style="font-size:0.85rem;color:#1e4a8d;text-decoration:none;">
                            Edit
                        </a>

                        <form action="{{ route('hr.employees.destroy', $emp->id) }}" method="POST"
                              onsubmit="return confirm('Yakin ingin menghapus karyawan ini?');">
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
                        Belum ada karyawan terdaftar.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <x-pagination :items="$items" />

</x-app>
