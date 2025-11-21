<x-app title="Master Lokasi Presensi">
    @if(session('success'))
        <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;">
        <div style="font-size:0.9rem;opacity:.7;">
            Master lokasi yang dipakai untuk presensi karyawan (gudang, depo, dsb).
        </div>
        <a href="{{ route('hr.locations.create') }}"
               style="padding:6px 10px;border-radius:8px;background:#1e4a8d;color:#fff;font-size:0.85rem;text-decoration:none;white-space:nowrap;">
                + Tambah Lokasi
            </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama Lokasi</th>
                <th>Alamat</th>
                <th>Koordinat</th>
                <th>Radius (m)</th>
                <th>Status</th>
                <th style="width:130px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $loc)
                <tr>
                    <td>{{ $loc->name }}</td>
                    <td>{{ $loc->address }}</td>
                    <td>{{ $loc->latitude }}, {{ $loc->longitude }}</td>
                    <td>{{ $loc->radius_meters }}</td>
                    <td>
                        <span style="padding:4px 8px;border-radius:999px;font-size:0.8rem;
                        {{ $loc->is_active ? 'background:#dcfce7;color:#166534;' : 'background:#fee2e2;color:#991b1b;' }}">
                            {{ $loc->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('hr.locations.edit', $loc->id) }}"
                           style="font-size:0.85rem;margin-right:6px;">Edit</a>

                        <form action="{{ route('hr.locations.destroy', $loc->id) }}"
                              method="POST"
                              style="display:inline;"
                              onsubmit="return confirm('Hapus lokasi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                style="border:none;background:none;color:#b91c1c;font-size:0.85rem;cursor:pointer;">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:16px;font-size:0.9rem;">
                        Belum ada lokasi presensi.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <x-pagination :items="$items" />

</x-app>
