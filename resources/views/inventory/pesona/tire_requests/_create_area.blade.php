@php
    use App\Services\Inventory\PesonaTireLayout as Layout;
    // Area dinamis Ajukan Ban (bergantung pada armada terpilih): halaman penuh atau partial AJAX (?partial=area).
    // Informasi posisi dan form pengajuan ada di modal (satu dialog di halaman), bukan di area ini.
    $href = fn (int $pos) => route('inventory.pesona.tire-requests.create', ['vehicle_id' => $vehicle?->id, 'pos' => $pos]);
@endphp
@if(! $vehicle)
    <div class="tire-card"><p style="margin:0;color:var(--pesona-muted);">Pilih armada terlebih dahulu untuk menampilkan gambar Ban Armada.</p></div>
@else
    <div>
        <h2 style="margin:0 0 4px;font-size:16px;font-weight:800;">Ban Armada — {{ $vehicle->nopol }}</h2>
        <p class="tire-hint" style="margin:0 0 8px;">Klik posisi ban pada gambar armada untuk mengajukan kebutuhan.</p>
        <div class="diag-legend" aria-label="Legenda gambar">
            <span><i style="background:#F0922F"></i>Terisi (tanda ●)</span>
            <span><i style="background:#fff;border-style:dashed;border-color:#6B7280"></i>Kosong (garis putus)</span>
            <span><i style="background:#fff;border-color:#2563EB;border-width:3px"></i>Terpilih (garis biru tebal)</span>
        </div>
    </div>

    @include('inventory.pesona._fleet_diagram', ['vehicle' => $vehicle, 'bySlot' => $bySlot, 'selected' => $selected, 'href' => $href])

    {{-- Alternatif aksesibel: memilih posisi dari daftar membuka modal yang sama dengan klik pada gambar. --}}
    <form method="GET" class="tire-card" id="req-position-form" action="{{ route('inventory.pesona.tire-requests.create') }}" style="display:flex;gap:8px;align-items:end;flex-wrap:wrap;">
        <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
        <label class="tire-field" style="min-width:260px;"><span class="tire-label">Atau pilih posisi dari daftar</span>
            <select class="tire-input" name="pos" id="req-position">
                <option value="">— pilih posisi —</option>
                @foreach(Layout::byPart() as $part => $numbers)
                    <optgroup label="{{ ucfirst(strtolower($part)) }}">
                        @foreach($numbers as $n)
                            <option value="{{ $n }}" @selected($selected === $n)>Posisi {{ $n }} — {{ $bySlot->has($n) ? 'terisi (ban '.($bySlot->get($n)->serial_number ?? '—').')' : 'kosong' }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select></label>
        <noscript><button class="tire-btn tire-btn-primary" type="submit">Tampilkan posisi</button></noscript>
    </form>
@endif
