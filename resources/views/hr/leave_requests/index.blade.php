<x-app title="Pengajuan Menunggu HRD">
    @if(session('success'))
    <div class="card" style="margin-bottom:12px;background:#e6ffec;color:#065f46;padding:8px 10px;border-radius:8px;">
        {{ session('success') }}
    </div>
    @endif

    <table class="table" style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="text-align:left;border-bottom:1px solid #eee;">
                <th>Karyawan</th>
                <th>Jenis</th>
                <th>Tanggal</th>
                <th>Alasan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leaves as $lv)
            <tr style="border-bottom:1px solid #f3f4f6;">
                <td>{{ $lv->user->name }}</td>
                <td>{{ $lv->type }}</td>
                <td>{{ $lv->start_date->format('d M Y') }} – {{ $lv->end_date->format('d M Y') }}</td>
                <td>{{ Str::limit($lv->reason, 80) }}</td>
                <td style="padding:14px 8px;text-align:center;vertical-align:middle;">
                    <a href="{{ route('hr.leave.show', $lv) }}" style="text-decoration:none;margin:4px 0;display:inline-block;">
                        Detail
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="opacity:.7;">Tidak ada pengajuan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <x-pagination :items="$leaves" />

</x-app>