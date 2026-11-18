@php
    use App\Services\Inventory\PesonaTireLayout as Layout;
    // Isi modal satu posisi (pengguna): hanya informasi ban terpasang dan form pengajuan. Tanpa aksi/endpoint admin.
    // Kebutuhan ditentukan dari keadaan posisi pada saat modal dimuat; server tetap memvalidasi ulang saat kirim.
    $need = $tire ? 'GANTI' : 'PASANG';
    $ctx = (int) old('position_number') === (int) $selected && (int) old('inv_pesona_vehicle_id') === (int) $vehicle->id;
    $dash = fn ($v) => ($v === null || trim((string) $v) === '') ? '—' : $v;
@endphp
<div data-title="{{ $vehicle->nopol }} — {{ Layout::part($selected) }}, Posisi {{ $selected }}" hidden></div>
@if($ctx && $errors->any())<div class="pesona-alert pesona-alert-error" role="alert">{{ $errors->first() }}</div>@endif

@if($tire)
    <dl class="tire-dl">
        <div><dt>Nomor seri</dt><dd>@if($tire->serial_number !== null)<span class="tire-serial">{{ $tire->serial_number }}</span>@else — @endif</dd></div>
        <div><dt>Tipe</dt><dd>{{ $dash(\App\Models\InvPesonaTire::TYPES[$tire->tire_type] ?? $tire->tire_type) }}</dd></div>
        <div><dt>Merek / ukuran</dt><dd>{{ $dash(trim(($tire->brand ?? '').' '.($tire->size ?? ''))) }}</dd></div>
        <div><dt>Kondisi</dt><dd>{{ $dash(\App\Models\InvPesonaTire::CONDITIONS[$tire->tire_condition] ?? $tire->tire_condition) }}</dd></div>
        <div><dt>Kelayakan</dt><dd>{{ $tire->usableLabel() }}</dd></div>
        <div><dt>Keterangan</dt><dd>{{ $dash($tire->notes) }}</dd></div>
    </dl>
@else
    <p style="margin:0 0 6px;font-weight:800;font-size:15px;">Posisi kosong</p>
    <p class="tire-hint" style="margin:0;">Tujuan pemasangan: <strong>{{ $vehicle->nopol }}</strong>, {{ Layout::part($selected) }} posisi <strong>{{ $selected }}</strong>.</p>
@endif

<form method="POST" data-draft="request" action="{{ route('inventory.pesona.tire-requests.store') }}" style="margin-top:14px;">
    @csrf
    <input type="hidden" name="inv_pesona_vehicle_id" value="{{ $vehicle->id }}">
    <input type="hidden" name="position_number" value="{{ $selected }}">
    <input type="hidden" name="request_type" value="{{ $need }}">
    <div class="req-summary">Kebutuhan: <span class="req-need">{{ $need === 'PASANG' ? 'Ajukan Pasang Ban' : 'Ajukan Ganti Ban' }}</span></div>
    <div class="tire-grid" style="margin-top:12px;">
        <label class="tire-field"><span class="tire-label">Keterangan kebutuhan</span>
            <textarea class="tire-input" name="need_notes" maxlength="1000" required>{{ $ctx ? old('need_notes') : '' }}</textarea>
            <span class="tire-hint">Jelaskan alasan atau kondisi ban saat ini.</span></label>
    </div>
    <p class="tire-hint" style="margin:12px 0 0;">Mengirim pengajuan tidak memasang atau memindahkan ban. Pengajuan diproses setelah disetujui admin.</p>
    <button class="tire-btn tire-btn-primary" type="submit" style="margin-top:12px;">Kirim Pengajuan</button>
</form>
