<table>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>MASTER IZIN DAN CUTI</td>
        <td></td>
        <td></td>
        <td>Dicetak: {{ now()->format('d M Y H:i') }}</td>
    </tr>
    <tr>
        <td></td>
        <td>TOTAL DATA</td>
        <td></td>
        <td></td>
        <td>{{ $items->count() }}</td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>NO</td>
        <td>NAMA KARYAWAN</td>
        <td>NIK</td>
        <td>DIVISI</td>
        <td>JABATAN</td>
        <td>TANGGAL PENGAJUAN</td>
        <td>PERIODE IZIN</td>
        <td>JENIS</td>
        <td>ALASAN</td>
        <td>STATUS</td>
        <td>APPROVED BY</td>
        <td>TANGGAL APPROVE</td>
        <td>NOTES HRD</td>
    </tr>
    @foreach($items as $index => $item)
    <tr>
        <td></td>
        <td>{{ $index + 1 }}</td>
        <td>{{ $item->user->name ?? '-' }}</td>
        <td>{{ $item->user->profile->nik ?? '' }}</td>
        <td>{{ $item->user->division->name ?? '-' }}</td>
        <td>{{ $item->user->position->name ?? '-' }}</td>
        <td>{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}</td>
        <td>{{ $item->start_date->format('d/m/Y') }} @if($item->end_date && $item->end_date->ne($item->start_date)) s/d {{ $item->end_date->format('d/m/Y') }} @endif</td>
        <td>{{ $item->type_label }}</td>
        <td>{{ $item->reason ?? '-' }}</td>
        <td>{{ $statusLabels[$item->status] ?? $item->status }}</td>
        <td>{{ $item->approver->name ?? '-' }}</td>
        <td>{{ $item->approved_at ? Carbon\Carbon::parse($item->approved_at)->format('d/m/Y H:i') : '-' }}</td>
        <td>{{ $item->notes_hrd ?? '-' }}</td>
    </tr>
    @endforeach
</table>