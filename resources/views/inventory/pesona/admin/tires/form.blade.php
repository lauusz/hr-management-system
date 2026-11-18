@php
    $editing = $tire->exists;
    $err = fn ($k) => $errors->first($k);
    $mode = old('mode', 'AWAL');
    $location = old('location_status', 'GUDANG');
    $usableValue = old('is_usable', $tire->is_usable === null ? '' : (int) $tire->is_usable);
    $backUrl = $editing ? route('inventory.pesona.admin.tires.show', $tire) : route('inventory.pesona.admin.tires.index');
@endphp
<x-inv-pesona-app :title="($editing ? 'Edit Ban' : 'Catat Ban').' — Inventory Pesona'">
    @include('inventory.pesona.admin.tires._style')
    <div class="tire-wrap">
        <div>
            <a class="tire-btn" href="{{ $backUrl }}">← Kembali</a>
            <h1 class="pesona-title" style="margin-top:10px;">{{ $editing ? 'Edit Data Ban' : 'Catat Ban' }}</h1>
            <p class="pesona-subtitle">{{ $editing ? 'Hanya mengubah data ban. Untuk memindahkan lokasi atau mengarsipkan, gunakan aksi pada halaman detail.' : 'Pilih pencatatan awal (keadaan aktual saat ini) atau penerimaan ban baru ke gudang.' }}</p>
        </div>

        @if($err('form_token'))<div class="pesona-alert pesona-alert-error" role="alert">{{ $err('form_token') }}</div>@endif

        <form method="POST" class="tire-card" id="tire-form"
              action="{{ $editing ? route('inventory.pesona.admin.tires.update', $tire) : route('inventory.pesona.admin.tires.store') }}">
            @csrf
            @if($editing) @method('PUT') @else <input type="hidden" name="form_token" value="{{ $token }}"> @endif

            <div class="tire-grid">
                @unless($editing)
                    <fieldset class="tire-field full" style="border:0;padding:0;margin:0;">
                        <legend class="tire-label">Jenis pencatatan</legend>
                        <label><input type="radio" name="mode" value="AWAL" @checked($mode === 'AWAL')> Pencatatan awal (ban yang sudah ada)</label>
                        <label><input type="radio" name="mode" value="MASUK" @checked($mode === 'MASUK')> Penerimaan ban baru (masuk gudang)</label>
                        @if($err('mode'))<span class="tire-error">{{ $err('mode') }}</span>@endif
                    </fieldset>
                    <div class="tire-field full" id="loc-group">
                        <span class="tire-label">Lokasi saat ini (pencatatan awal)</span>
                        <div>
                            <label><input type="radio" name="location_status" value="GUDANG" @checked($location === 'GUDANG')> Gudang</label>
                            <label style="margin-left:14px;"><input type="radio" name="location_status" value="ARMADA" @checked($location === 'ARMADA')> Sudah terpasang pada armada</label>
                        </div>
                        @if($err('location_status'))<span class="tire-error">{{ $err('location_status') }}</span>@endif
                    </div>
                    <label class="tire-field veh-field"><span class="tire-label">Kendaraan (nopol)</span>
                        <select class="tire-input" name="inv_pesona_vehicle_id">
                            <option value="">— pilih —</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" @selected((string) old('inv_pesona_vehicle_id') === (string) $vehicle->id)>{{ $vehicle->nopol }}</option>
                            @endforeach
                        </select>
                        @if($err('inv_pesona_vehicle_id'))<span class="tire-error">{{ $err('inv_pesona_vehicle_id') }}</span>@endif</label>
                    <label class="tire-field veh-field"><span class="tire-label">Nomor posisi</span>
                        <select class="tire-input" name="position_number">
<option value="">— pilih posisi —</option>
@foreach(\App\Services\Inventory\PesonaTireLayout::byPart() as $part => $numbers)
<optgroup label="{{ ucfirst(strtolower($part)) }}">
@foreach($numbers as $n)<option value="{{ $n }}" @selected((string) old('position_number') === (string) $n)>{{ $n }}</option>@endforeach
</optgroup>
@endforeach
</select>
                        <span class="tire-hint">Posisi 1–18 sesuai diagram ban (HEAD 6, EKOR 12).</span>
                        @if($err('position_number'))<span class="tire-error">{{ $err('position_number') }}</span>@endif</label>
                @endunless

                <label class="tire-field"><span class="tire-label">Nomor seri</span>
                    <input class="tire-input" name="serial_number" maxlength="100" value="{{ old('serial_number', $tire->serial_number) }}" autocomplete="off">
                    <span class="tire-hint">Ditulis apa adanya (nol awal, huruf, tanda hubung dipertahankan). Boleh kosong hanya untuk pencatatan awal; ban tanpa seri tidak dapat dipasang.</span>
                    @if($err('serial_number'))<span class="tire-error">{{ $err('serial_number') }}</span>@endif</label>
                <label class="tire-field"><span class="tire-label">Tipe</span>
                    <select class="tire-input" name="tire_type" required>
                        <option value="">— pilih —</option>
                        @foreach(\App\Models\InvPesonaTire::TYPES as $value => $label)
                            <option value="{{ $value }}" @selected(old('tire_type', $tire->tire_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @if($err('tire_type'))<span class="tire-error">{{ $err('tire_type') }}</span>@endif</label>
                <label class="tire-field"><span class="tire-label">Merek</span>
                    <input class="tire-input" name="brand" maxlength="100" value="{{ old('brand', $tire->brand) }}">
                    @if($err('brand'))<span class="tire-error">{{ $err('brand') }}</span>@endif</label>
                <label class="tire-field"><span class="tire-label">Ukuran</span>
                    <input class="tire-input" name="size" maxlength="30" value="{{ old('size', $tire->size) }}">
                    @if($err('size'))<span class="tire-error">{{ $err('size') }}</span>@endif</label>
                <label class="tire-field"><span class="tire-label">Kondisi</span>
                    <select class="tire-input" name="tire_condition" required>
                        @foreach(\App\Models\InvPesonaTire::CONDITIONS as $value => $label)
                            <option value="{{ $value }}" @selected(old('tire_condition', $tire->tire_condition) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @if($err('tire_condition'))<span class="tire-error">{{ $err('tire_condition') }}</span>@endif</label>
                <label class="tire-field"><span class="tire-label">Kelayakan</span>
                    <select class="tire-input" name="is_usable">
                        <option value="" @selected((string) $usableValue === '')>Belum diketahui</option>
                        <option value="1" @selected((string) $usableValue === '1')>Layak</option>
                        <option value="0" @selected((string) $usableValue === '0')>Tidak layak</option>
                    </select>
                    @if($err('is_usable'))<span class="tire-error">{{ $err('is_usable') }}</span>@endif</label>
                <label class="tire-field"><span class="tire-label">Tanggal penerimaan</span>
                    <input class="tire-input" type="date" name="received_on" max="{{ now()->toDateString() }}" value="{{ old('received_on', $tire->received_on?->format('Y-m-d')) }}">
                    <span class="tire-hint">Kosongkan bila tidak diketahui. Bukan tanggal input.</span>
                    @if($err('received_on'))<span class="tire-error">{{ $err('received_on') }}</span>@endif</label>
                <label class="tire-field"><span class="tire-label">Pemasok</span>
                    <input class="tire-input" name="supplier_name" maxlength="150" value="{{ old('supplier_name', $tire->supplier_name) }}">
                    @if($err('supplier_name'))<span class="tire-error">{{ $err('supplier_name') }}</span>@endif</label>
                <label class="tire-field"><span class="tire-label">Nomor dokumen (SJ / nota / invoice)</span>
                    <input class="tire-input" name="document_number" maxlength="100" value="{{ old('document_number', $tire->document_number) }}">
                    @if($err('document_number'))<span class="tire-error">{{ $err('document_number') }}</span>@endif</label>
                <label class="tire-field full"><span class="tire-label">Catatan ban</span>
                    <textarea class="tire-input" name="notes" maxlength="1000">{{ old('notes', $tire->notes) }}</textarea>
                    @if($err('notes'))<span class="tire-error">{{ $err('notes') }}</span>@endif</label>

                <label class="tire-field"><span class="tire-label">Tanggal kejadian</span>
                    <input class="tire-input" type="date" name="occurred_on" required max="{{ now()->toDateString() }}" value="{{ old('occurred_on', now()->toDateString()) }}">
                    @if($err('occurred_on'))<span class="tire-error">{{ $err('occurred_on') }}</span>@endif</label>
                <label class="tire-field"><span class="tire-label">Catatan riwayat</span>
                    <input class="tire-input" name="event_notes" maxlength="1000" value="{{ old('event_notes') }}">
                    @if($err('event_notes'))<span class="tire-error">{{ $err('event_notes') }}</span>@endif</label>
            </div>
            <div style="display:flex;gap:8px;margin-top:16px;">
                <button class="tire-btn tire-btn-primary" type="submit">Simpan</button>
                <a class="tire-btn" href="{{ $backUrl }}">Batal</a>
            </div>
        </form>
    </div>
    @unless($editing)
        <script>
            (() => {
                const form = document.getElementById('tire-form');
                const sync = () => {
                    const awal = form.querySelector('[name=mode]:checked')?.value === 'AWAL';
                    const armada = awal && form.querySelector('[name=location_status]:checked')?.value === 'ARMADA';
                    document.getElementById('loc-group').style.display = awal ? '' : 'none';
                    form.querySelectorAll('.veh-field').forEach((el) => { el.style.display = armada ? '' : 'none'; });
                };
                form.addEventListener('change', sync);
                sync();
            })();
        </script>
    @endunless
</x-inv-pesona-app>
