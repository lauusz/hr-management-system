@php
    use App\Services\Inventory\PesonaTireLayout as Layout;
    // Gambar armada bersama (halaman admin Ban Armada dan Ajukan Ban pengguna): hanya gambar dan pemilihan posisi.
    // Butuh: $vehicle, $bySlot (posisi => ban terpasang), $selected (posisi terpilih atau null), $href (fn posisi => URL).
    // HEAD dan EKOR adalah dua SVG (masing-masing dirender sekali; total 18 area klik): berdampingan di desktop, bertumpuk di mobile.
    $slots = Layout::all();
    $g = Layout::guides();
    [$hb, $hf, $hl, $eb, $sl] = [$g['head']['body'], $g['head']['frame'], $g['head']['bottomLine'], $g['ekor']['body'], $g['ekor']['sumbuLine']];
    $ex = 505;
    // viewBox tiap bagian (skala sama, sehingga desktop tampak seperti satu gambar): x, y, lebar, tinggi
    $views = ['HEAD' => [60, 20, 450, $hl['y'] + 34 - 20], 'EKOR' => [500, 20, 380, 450]];
@endphp
<style>
    .diag-legend { display: flex; flex-wrap: wrap; gap: 4px 16px; font-size: 12.5px; color: #374151; }
    .diag-legend i { display: inline-block; width: 15px; height: 15px; vertical-align: -3px; margin-right: 5px; border: 1.6px solid #1F1F1F; border-radius: 3px; }
    .diag-scroll { border: 1px solid var(--pesona-border); border-radius: 14px; background: #fff; padding: 8px; }
    /* Desktop: HEAD kiri, EKOR kanan, skala sama (lebar mengikuti flex 450:380 = lebar viewBox). Atribut width/height pada <svg> menghindari aturan global svg:not([width]) { width:1em }. */
    .diag-pair { display: flex; align-items: flex-start; max-width: 1200px; margin: 0 auto; font-family: 'Instrument Sans', system-ui, sans-serif; }
    .diag-svg { display: block; height: auto; min-width: 0; }
    .diag-svg.diag-head { flex: 450 1 0; }
    .diag-svg.diag-ekor { flex: 380 1 0; }
    /* Mobile: HEAD lalu EKOR, vertikal, masing-masing selebar layar (tanpa scroll horizontal). */
    @@media (max-width: 767px) {
        .diag-pair { flex-direction: column; align-items: stretch; gap: 12px; }
        .diag-svg.diag-head, .diag-svg.diag-ekor { flex: none; width: 100%; max-width: 480px; margin: 0 auto; }
    }
    /* Dua kolom (gambar + panel) hanya bila lebar WADAH cukup; ruang sidebar otomatis ikut diperhitungkan. */
    .fleet-split { container-type: inline-size; }
    @container (min-width: 1100px) { .fleet-split > .diag-layout, .fleet-split > .req-layout { grid-template-columns: minmax(0, 1fr) var(--fleet-panel, 340px); } }
    .diag-svg .slot { cursor: pointer; outline: none; }
    .diag-svg .slot rect { stroke: #1F1F1F; stroke-width: 1.6; }
    .diag-svg .slot.tone-orange.filled rect { fill: #F0922F; }
    .diag-svg .slot.tone-cream.filled rect { fill: #FFF1C2; }
    .diag-svg .slot.empty rect { fill: #FFFFFF; stroke-dasharray: 4 3; stroke: #6B7280; }
    .diag-svg .slot:hover rect { stroke-width: 3; }
    .diag-svg .slot:focus-visible rect, .diag-svg .slot.selected rect { stroke: #2563EB; stroke-width: 4.5; stroke-dasharray: none; }
    .diag-svg .slot text { font-weight: 800; fill: #111; pointer-events: none; }
    .diag-svg .slot .mark { fill: #111; pointer-events: none; }
</style>
<div class="diag-scroll" role="group" aria-label="Ban Armada {{ $vehicle->nopol }}">
    <div class="diag-pair">
        @foreach($views as $part => [$vx, $vy, $vw, $vh])
            <svg class="diag-svg diag-{{ strtolower($part) }}" width="{{ $vw }}" height="{{ $vh }}" viewBox="{{ $vx }} {{ $vy }} {{ $vw }} {{ $vh }}" xmlns="http://www.w3.org/2000/svg" role="group" aria-label="Bagian {{ $part }}, tampak atas">
                {{-- Semua garis di belakang ban; koordinat berasal dari PesonaTireLayout::guides() --}}
                @if($part === 'HEAD')
                    <g fill="none" stroke="#1F1F1F" stroke-width="2">
                        <rect x="{{ $hb['x'] }}" y="{{ $hb['y'] }}" width="{{ $hb['w'] }}" height="{{ $hb['h'] }}" fill="#FFF3CC"/>
                        @foreach($g['axles'] as $ax)@if($ax['part'] === 'HEAD')<line x1="{{ $ax['x1'] }}" y1="{{ $ax['y'] }}" x2="{{ $ax['x2'] }}" y2="{{ $ax['y'] }}"/>@endif @endforeach
                    </g>
                    <g fill="none" stroke="#3B82F6" stroke-width="2" stroke-dasharray="6 5">
                        <rect x="{{ $hf['x'] }}" y="{{ $hf['y'] }}" width="{{ $hf['w'] }}" height="{{ $hf['h'] }}"/>
                        <line x1="{{ $hl['x1'] }}" y1="{{ $hl['y'] }}" x2="{{ $hl['x2'] }}" y2="{{ $hl['y'] }}"/>
                    </g>
                    <g font-weight="700" font-size="14" fill="#111">
                        <text x="70" y="58" font-size="30" font-weight="800">HEAD</text>
                        <text x="{{ $g['head']['cx'] }}" y="80" text-anchor="middle">DEPAN</text>
                        <text x="{{ $hf['x'] + $hf['w'] + 17 }}" y="{{ $hl['y'] - 24 }}">TENGAH</text>
                        <text x="{{ $g['head']['cx'] }}" y="{{ $hl['y'] + 24 }}" text-anchor="middle">BELAKANG</text>
                    </g>
                    <g font-size="10" fill="#374151">
                        <text x="{{ $hf['x'] + $hf['w'] + 17 }}" y="343">ENGINE / HEAD</text>
                    </g>
                @else
                    <g fill="none" stroke="#1F1F1F" stroke-width="2">
                        <rect x="{{ $eb['x'] }}" y="{{ $eb['y'] }}" width="{{ $eb['w'] }}" height="{{ $eb['h'] }}" fill="#F4F8FF"/>
                        @foreach($g['rails'] as $r)<line x1="{{ $r['x'] }}" y1="{{ $r['y1'] }}" x2="{{ $r['x'] }}" y2="{{ $r['y2'] }}"/>@endforeach
                        @foreach($g['axles'] as $ax)@if($ax['part'] === 'EKOR')<line x1="{{ $ax['x1'] }}" y1="{{ $ax['y'] }}" x2="{{ $ax['x2'] }}" y2="{{ $ax['y'] }}"/>@endif @endforeach
                    </g>
                    <line x1="{{ $sl['x1'] }}" y1="{{ $sl['y'] }}" x2="{{ $sl['x2'] }}" y2="{{ $sl['y'] }}" stroke="#3B82F6" stroke-width="2" stroke-dasharray="6 5"/>
                    <circle cx="{{ $g['ekor']['cx'] }}" cy="80" r="7" fill="#2563EB"/>
                    <g font-weight="700" font-size="14" fill="#111">
                        <text x="{{ $ex }}" y="58" font-size="30" font-weight="800">EKOR</text>
                        <text x="{{ $sl['x2'] - 62 }}" y="{{ $sl['y'] - 8 }}" font-size="13">SUMBU 2</text>
                    </g>
                @endif

                {{-- Area klik per posisi (GET saja, tidak mengubah data); tiap posisi hanya dirender sekali, pada bagiannya --}}
                @foreach($slots as $number => $slot)
                    @continue($slot['part'] !== $part)
                    @php
                        $tire = $bySlot->get($number);
                        $name = Layout::label($number).', '.($tire ? 'ban nomor seri '.$tire->serial_number : 'kosong');
                        $cx = $slot['x'] + $slot['w'] / 2;
                        $cy = $slot['y'] + $slot['h'] / 2;
                    @endphp
                    <a class="slot tone-{{ $slot['tone'] }} {{ $tire ? 'filled' : 'empty' }} {{ $selected === $number ? 'selected' : '' }}"
                       href="{{ $href($number) }}" data-slot="{{ $number }}" aria-label="{{ $name }}" @if($selected === $number) aria-current="true" @endif>
                        <title>{{ $name }}</title>
                        <rect x="{{ $slot['x'] }}" y="{{ $slot['y'] }}" width="{{ $slot['w'] }}" height="{{ $slot['h'] }}" rx="4"/>
                        <text x="{{ $cx }}" y="{{ $cy }}" dy="0.35em" font-size="{{ $slot['w'] < 25 ? 13 : 16 }}" text-anchor="middle">{{ $number }}</text>
                        @if($tire)<circle class="mark" cx="{{ $cx }}" cy="{{ $slot['y'] + $slot['h'] - 9 }}" r="3"/>@endif
                    </a>
                @endforeach
            </svg>
        @endforeach
    </div>
</div>
