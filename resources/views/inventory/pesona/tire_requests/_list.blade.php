{{-- Daftar pengajuan (tabel/empty state/pagination); dipakai halaman penuh dan partial AJAX (?partial=list). --}}
@if($items->isEmpty())
    <p style="margin:0;color:var(--pesona-muted);">Belum ada pengajuan ban{{ $status !== '' ? ' dengan status ini' : '' }}.</p>
@else
    <div class="tire-table-wrap">
        <table class="tire-table">
            <thead><tr><th>Nomor</th><th>Tanggal</th><th>Nopol</th><th>Posisi</th><th>Kebutuhan</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach($items as $r)
                    <tr>
                        <td class="tire-serial">{{ $r->request_number }}</td>
                        <td style="white-space:nowrap;">{{ $r->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $r->nopol_snapshot }}</td>
                        <td>{{ \App\Services\Inventory\PesonaTireLayout::label($r->position_number) }}</td>
                        <td>{{ \App\Models\InvPesonaTireRequest::TYPES[$r->request_type] ?? $r->request_type }}</td>
                        <td><span class="tire-badge {{ $r->status === 'APPROVED' ? 'tire-badge-ok' : ($r->status === 'REJECTED' ? 'tire-badge-bad' : 'tire-badge-warn') }}">{{ $r->statusLabel() }}</span></td>
                        <td style="text-align:right;"><a class="tire-btn" href="{{ route('inventory.pesona.tire-requests.show', $r) }}">Detail</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="margin-top:14px;"><x-pagination :items="$items" /></div>
@endif
