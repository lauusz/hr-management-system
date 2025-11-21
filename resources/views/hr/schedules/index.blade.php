<x-app title="Master Jadwal Karyawan">

    @if(session('success'))
        <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;">
        <div style="opacity:.7;font-size:.9rem;">
            Daftar jadwal shift karyawan berdasarkan tanggal.
        </div>
        
        <a href="{{ route('hr.schedules.create') }}"
               style="padding:6px 10px;border-radius:8px;background:#1e4a8d;color:#fff;font-size:0.85rem;text-decoration:none;white-space:nowrap;">
                + Tambah Jadwal
            </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Karyawan</th>
                <th>Shift</th>
                <th>Lokasi</th>
                <th style="width:120px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item->user->name }}</td>
                    <td>{{ $item->shift->name ?? '-' }}</td>
                    <td>{{ $item->location->name ?? '-' }}</td>
                    <td>
                        <a href="{{ route('hr.schedules.edit', $item->id) }}"
                           style="margin-right:6px;font-size:.85rem;">Edit</a>

                        <form action="{{ route('hr.schedules.destroy', $item->id) }}"
                              method="POST"
                              style="display:inline;"
                              onsubmit="return confirm('Hapus jadwal ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    style="border:none;background:none;color:#b91c1c;font-size:.85rem;cursor:pointer;">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:12px;font-size:.9rem;opacity:.7;">
                        Belum ada jadwal karyawan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <x-pagination :items="$items" />


</x-app>
