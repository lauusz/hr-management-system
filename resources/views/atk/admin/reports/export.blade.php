<table>
    <tr>
        <td colspan="6"><strong>REKAP PEMAKAIAN ATK PER PT</strong></td>
    </tr>
    <tr>
        <td colspan="2">Periode</td>
        <td colspan="4">{{ $periodLabel }}</td>
    </tr>
    <tr>
        <td colspan="2">Dicetak</td>
        <td colspan="4">{{ now()->translatedFormat('j F Y H:i') }}</td>
    </tr>
    <tr><td></td></tr>

    <tr>
        <td colspan="6"><strong>RINGKASAN</strong></td>
    </tr>
    <tr>
        <td>Jumlah Pengajuan</td>
        <td>{{ $summary['request_count'] }}</td>
        <td>Total Qty</td>
        <td>{{ $summary['total_qty'] }}</td>
        <td>Jumlah PT</td>
        <td>{{ $summary['pt_count'] }}</td>
    </tr>
    <tr><td></td></tr>

    <tr>
        <td colspan="6"><strong>REKAP PER PT</strong></td>
    </tr>
    <tr>
        <td>No</td>
        <td colspan="3">PT</td>
        <td>Jumlah Pengajuan</td>
        <td>Total Qty</td>
    </tr>
    @forelse($ptRows as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td colspan="3">{{ $row->pt_name_snapshot ?: '(Tanpa PT)' }}</td>
            <td>{{ $row->request_count }}</td>
            <td>{{ $row->total_qty }}</td>
        </tr>
    @empty
        <tr><td colspan="6">Tidak ada data.</td></tr>
    @endforelse
    <tr><td></td></tr>

    <tr>
        <td colspan="6"><strong>REKAP PER BARANG</strong></td>
    </tr>
    <tr>
        <td>No</td>
        <td colspan="3">Barang</td>
        <td>Satuan</td>
        <td>Total Qty</td>
    </tr>
    @forelse($itemRows as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td colspan="3">{{ $row->item_name_snapshot }}</td>
            <td>{{ $row->unit_name_snapshot }}</td>
            <td>{{ $row->total_qty }}</td>
        </tr>
    @empty
        <tr><td colspan="6">Tidak ada data.</td></tr>
    @endforelse
    <tr><td></td></tr>

    <tr>
        <td colspan="6"><strong>RINCIAN PEMAKAIAN</strong></td>
    </tr>
    <tr>
        <td>No</td>
        <td>Tanggal Approve</td>
        <td>No Request</td>
        <td>User / PT</td>
        <td>Barang</td>
        <td>Qty</td>
    </tr>
    @forelse($detailRows as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ optional($row->approved_at)->format('d/m/Y H:i') ?: '-' }}</td>
            <td>{{ $row->request_number }}</td>
            <td>{{ $row->user_name_snapshot }} / {{ $row->pt_name_snapshot ?: '-' }}</td>
            <td>{{ $row->item_name_snapshot }}</td>
            <td>{{ $row->qty }} {{ $row->unit_name_snapshot }}</td>
        </tr>
    @empty
        <tr><td colspan="6">Tidak ada data.</td></tr>
    @endforelse
</table>
