<x-app title="Master Shift Kerja">

    @if(session('success'))
        <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;">
        <div style="font-size:0.9rem;opacity:.7;">
            Master Shift digunakan untuk mengatur jadwal kerja karyawan.
        </div>
        <a href="{{ route('hr.shifts.create') }}"
           style="padding:8px 10px;border-radius:8px;background:#1e4a8d;color:#fff;font-size:0.9rem;text-decoration:none;">
            + Tambah Shift
        </a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Nama Shift</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $sh)
                <tr>
                    <td>{{ $sh->name }}</td>
                    <td>{{ $sh->start_time_label }}</td>
                    <td>{{ $sh->end_time_label }}</td>
                    <td>
                        <a href="{{ route('hr.shifts.edit', $sh->id) }}"
                           style="font-size:0.85rem;margin-right:6px;">Edit</a>

                        <form action="{{ route('hr.shifts.destroy', $sh->id) }}"
                              method="POST"
                              style="display:inline;"
                              onsubmit="return confirm('Hapus shift ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                style="border:none;background:none;color:#b91c1c;font-size:0.85rem;cursor:pointer;">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <x-pagination :items="$items" />

</x-app>
