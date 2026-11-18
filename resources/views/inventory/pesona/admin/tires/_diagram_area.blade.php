@php
    use App\Services\Inventory\PesonaTireLayout as Layout;
    // Area dinamis Ban Armada admin (bergantung pada armada terpilih): dirender penuh atau sebagai partial AJAX (?partial=area).
    // Informasi dan aksi posisi ada di modal (satu dialog di halaman), bukan di area ini.
    $href = fn (int $pos) => route('inventory.pesona.admin.tires.diagram', ['vehicle_id' => $vehicle?->id, 'pos' => $pos]);
@endphp
@if(! $vehicle)
    <div class="tire-card"><p style="margin:0;color:var(--pesona-muted);">Pilih armada terlebih dahulu untuk menampilkan gambar Ban Armada.</p></div>
@else
    <div class="tire-card diag-bar">
        <div><span class="tire-hint">Armada</span> <strong>{{ $vehicle->nopol }}</strong></div>
        <span class="tire-badge {{ $vehicle->is_active ? 'tire-badge-ok' : 'tire-badge-warn' }}">{{ $vehicle->is_active ? 'Armada aktif' : 'Armada nonaktif — hanya lihat dan kembalikan ban' }}</span>
        <span style="font-size:13px;">Terisi <strong>{{ $bySlot->count() }}</strong>/{{ count(Layout::all()) }} posisi</span>
        <div class="diag-legend" aria-label="Legenda gambar">
            <span><i style="background:#F0922F"></i>Terisi (tanda ●)</span>
            <span><i style="background:#fff;border-style:dashed;border-color:#6B7280"></i>Kosong (garis putus)</span>
            <span><i style="background:#fff;border-color:#2563EB;border-width:3px"></i>Terpilih (garis biru tebal)</span>
        </div>
    </div>

    @include('inventory.pesona._fleet_diagram', ['vehicle' => $vehicle, 'bySlot' => $bySlot, 'selected' => $selected, 'href' => $href])

    <div class="tire-card">
        <h2 style="margin:0 0 6px;font-size:15px;">Ban Terpasang pada Armada</h2>
        @if($installed->contains('legacy', true))
            <p class="tire-hint" style="margin:0 0 10px;">Posisi bertanda “Posisi lama/di luar skema” tidak digambar pada diagram (skema aktif: 18 posisi). Data tidak diubah; buka Detail &amp; Riwayat untuk memeriksa atau mengembalikan ban ke gudang.</p>
        @endif
        @if($installed->isEmpty())
            <p class="tire-hint" style="margin:0;">Belum ada ban terpasang pada armada ini.</p>
        @else
            <div class="tire-table-wrap">
                <table class="tire-table">
                    <thead><tr><th>Posisi</th><th>Nomor seri</th><th>Tipe, merek, ukuran</th><th>Kondisi / kelayakan</th><th>Tanggal pemasangan</th><th></th></tr></thead>
                    <tbody>
                        @foreach($installed as $t)
                            @php($ev = $t->install_event)
                            <tr>
                                <td style="white-space:nowrap;"><strong>{{ $t->position_number }}</strong>
                                    @if($t->legacy)<br><span class="tire-badge tire-badge-warn">Posisi lama/di luar skema</span>@else<br><span class="tire-hint">{{ Layout::part($t->position_number) }}</span>@endif</td>
                                <td class="tire-serial">{{ $t->serial_number ?? '—' }}</td>
                                <td>{{ \App\Models\InvPesonaTire::TYPES[$t->tire_type] ?? $t->tire_type }}@if(trim($t->brand.' '.$t->size) !== '')<br><span class="tire-hint">{{ trim($t->brand.' '.$t->size) }}</span>@endif</td>
                                <td>{{ \App\Models\InvPesonaTire::CONDITIONS[$t->tire_condition] ?? $t->tire_condition }}<br><span class="tire-hint">{{ $t->usableLabel() }}</span></td>
                                <td style="white-space:nowrap;">@if($ev && $ev['date']){{ $ev['date']->format('d/m/Y') }}@else Belum tercatat @if($ev && $ev['initial'])<br><span class="tire-hint">Dicatat sudah terpasang</span>@endif @endif</td>
                                <td style="text-align:right;"><a class="tire-btn" href="{{ route('inventory.pesona.admin.tires.show', $t) }}">Detail &amp; Riwayat</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endif
