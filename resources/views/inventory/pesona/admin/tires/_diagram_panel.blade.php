@php
    use App\Services\Inventory\PesonaTireLayout as Layout;
    $today = now()->toDateString();
    // Input lama (setelah validasi gagal) hanya dipakai bila untuk posisi yang sedang dipilih.
    $ctx = $selected !== null && (int) old('position_number', old('expected_position')) === (int) $selected;
    $act = $ctx ? old('form_action') : null;
    $o = fn ($key, $default = null) => $ctx ? old($key, $default) : $default;
    // Pilihan ban lama setelah validasi gagal hanya dipulihkan bila masih siap pakai (server tetap memvalidasi ulang saat transaksi).
    $oldPick = fn (string $form, string $key) => $act === $form && ctype_digit((string) $o($key)) ? \App\Models\InvPesonaTire::query()->ready()->find((int) $o($key)) : null;
@endphp
@if($selected === null)
    <p class="tire-hint" style="margin:0;">Klik salah satu ban pada gambar (atau fokus lalu Enter/Spasi) untuk melihat informasi dan aksinya: pasang, copot ke gudang, atau ganti ban.</p>
@else
    {{-- Isi modal posisi. Judul modal dibaca JS dari data-title; keterangan hasil aksi/validasi ditampilkan di sini. --}}
    <div data-title="{{ $vehicle->nopol }} — {{ Layout::part($selected) }}, Posisi {{ $selected }}" hidden></div>
    @if(session('success'))<div class="pesona-alert pesona-alert-success" role="status">{{ session('success') }}</div>@endif
    @if($ctx && $errors->any())<div class="pesona-alert pesona-alert-error" role="alert">{{ $errors->first() }}</div>@endif

    @if($selectedTire)
        <dl class="tire-dl">
            <div><dt>Nomor seri</dt><dd>@if($selectedTire->serial_number !== null)<span class="tire-serial">{{ $selectedTire->serial_number }}</span>@else — @endif</dd></div>
            <div><dt>Tipe</dt><dd>{{ \App\Models\InvPesonaTire::TYPES[$selectedTire->tire_type] ?? $selectedTire->tire_type }}</dd></div>
            <div><dt>Merek / ukuran</dt><dd>{{ trim(($selectedTire->brand ?? '').' '.($selectedTire->size ?? '')) ?: '—' }}</dd></div>
            <div><dt>Kondisi</dt><dd>{{ \App\Models\InvPesonaTire::CONDITIONS[$selectedTire->tire_condition] ?? $selectedTire->tire_condition }}</dd></div>
            <div><dt>Kelayakan</dt><dd>{{ $selectedTire->usableLabel() }}</dd></div>
            <div><dt>Keterangan</dt><dd>{{ $selectedTire->notes ?? '—' }}</dd></div>
        </dl>
        <p style="margin:12px 0;"><a class="tire-btn" href="{{ route('inventory.pesona.admin.tires.show', $selectedTire) }}">Detail &amp; riwayat</a></p>

        <div class="tire-actions" style="display:flex;flex-direction:column;gap:8px;">
            <details @if($act === 'return') open @endif>
                <summary>Copot ke gudang</summary>
                <form method="POST" data-draft="return" action="{{ route('inventory.pesona.admin.tires.return', $selectedTire) }}">
                    @csrf
                    <input type="hidden" name="form_action" value="return">
                    <input type="hidden" name="expected_vehicle_id" value="{{ $vehicle->id }}">
                    <input type="hidden" name="expected_position" value="{{ $selected }}">
                    <p class="tire-hint" style="margin:0 0 10px;">Pilih hasil pemeriksaan petugas. Kondisi dan kelayakan tidak diisi otomatis.</p>
                    <div class="tire-grid">
                        <label class="tire-field"><span class="tire-label">Kondisi</span>
                            <select class="tire-input" name="tire_condition" required>
                                <option value="">— pilih —</option>
                                @foreach(\App\Models\InvPesonaTire::CONDITIONS as $value => $label)<option value="{{ $value }}" @selected($act === 'return' && $o('tire_condition') === $value)>{{ $label }}</option>@endforeach
                            </select></label>
                        <label class="tire-field"><span class="tire-label">Kelayakan</span>
                            <select class="tire-input" name="is_usable" required>
                                <option value="">— pilih —</option>
                                @foreach(['1' => 'Layak', '0' => 'Tidak layak', 'unknown' => 'Belum diketahui'] as $value => $label)<option value="{{ $value }}" @selected($act === 'return' && (string) $o('is_usable') === (string) $value)>{{ $label }}</option>@endforeach
                            </select></label>
                        <label class="tire-field"><span class="tire-label">Tanggal kejadian</span>
                            <input class="tire-input" type="date" name="occurred_on" required max="{{ $today }}" value="{{ $act === 'return' ? $o('occurred_on', $today) : $today }}"></label>
                        <label class="tire-field"><span class="tire-label">Catatan</span>
                            <input class="tire-input" name="event_notes" maxlength="1000" value="{{ $act === 'return' ? $o('event_notes') : '' }}"></label>
                    </div>
                    <button class="tire-btn tire-btn-primary" type="submit" style="margin-top:12px;">Copot ke gudang</button>
                </form>
            </details>

            <details @if($act === 'replace') open @endif>
                <summary>Ganti ban</summary>
                @if(! $vehicle->is_active)
                    <p class="tire-hint" style="margin:10px 0 0;">Armada nonaktif tidak dapat menerima penggantian. Ban masih dapat dicopot ke gudang.</p>
                @elseif($ready->isEmpty())
                    <p class="tire-hint" style="margin:10px 0 0;">Tidak ada ban pengganti siap pakai di gudang (aktif, layak, bernomor seri). Ban masih dapat dicopot ke gudang.</p>
                @else
                    <form method="POST" data-draft="replace" action="{{ route('inventory.pesona.admin.tires.diagram.replace') }}">
                        @csrf
                        <input type="hidden" name="form_action" value="replace">
                        <input type="hidden" name="old_tire_id" value="{{ $selectedTire->id }}">
                        <input type="hidden" name="inv_pesona_vehicle_id" value="{{ $vehicle->id }}">
                        <input type="hidden" name="position_number" value="{{ $selected }}">
                        <p class="tire-hint" style="margin:0 0 10px;">Ban lama <strong>{{ $selectedTire->serial_number }}</strong> dicopot ke gudang, lalu ban pengganti dari gudang dipasang pada <strong>{{ $vehicle->nopol }}</strong>, posisi <strong>{{ $selected }}</strong> (satu proses).</p>
                        <div class="tire-grid">
                            @php($pickNew = $oldPick('replace', 'new_tire_id'))
                            @include('inventory.pesona.admin.tires._ready_combo', ['name' => 'new_tire_id', 'caption' => 'Ban pengganti (gudang)', 'pick' => $pickNew, 'replace' => true])
                            <label class="tire-field"><span class="tire-label">Kondisi ban lama setelah dicopot</span>
                                <select class="tire-input" name="tire_condition" required>
                                    <option value="">— pilih —</option>
                                    @foreach(\App\Models\InvPesonaTire::CONDITIONS as $value => $label)<option value="{{ $value }}" @selected($act === 'replace' && $o('tire_condition') === $value)>{{ $label }}</option>@endforeach
                                </select></label>
                            <label class="tire-field"><span class="tire-label">Kelayakan ban lama</span>
                                <select class="tire-input" name="is_usable" required>
                                    <option value="">— pilih —</option>
                                    @foreach(['1' => 'Layak', '0' => 'Tidak layak', 'unknown' => 'Belum diketahui'] as $value => $label)<option value="{{ $value }}" @selected($act === 'replace' && (string) $o('is_usable') === (string) $value)>{{ $label }}</option>@endforeach
                                </select></label>
                            <label class="tire-field"><span class="tire-label">Tanggal kejadian</span>
                                <input class="tire-input" type="date" name="occurred_on" required max="{{ $today }}" value="{{ $act === 'replace' ? $o('occurred_on', $today) : $today }}"></label>
                            <label class="tire-field"><span class="tire-label">Catatan (opsional)</span>
                                <input class="tire-input" name="event_notes" maxlength="1000" value="{{ $act === 'replace' ? $o('event_notes') : '' }}"></label>
                        </div>
                        <p class="tire-hint" style="margin:10px 0 0;" data-replace-summary data-old="{{ $selectedTire->serial_number }}" data-place="{{ $vehicle->nopol }}, posisi {{ $selected }}">
                            Ban {{ $selectedTire->serial_number }} dikembalikan ke gudang. Ban {{ $pickNew?->serial_number ?? '[pilih ban pengganti]' }} dipasang pada {{ $vehicle->nopol }}, posisi {{ $selected }}.
                        </p>
                        <button class="tire-btn tire-btn-primary" type="submit" style="margin-top:12px;">Ganti ban</button>
                    </form>
                @endif
            </details>
        </div>
    @else
        <p style="margin:0 0 6px;font-weight:800;font-size:15px;">Posisi kosong</p>
        <p class="tire-hint" style="margin:0 0 12px;">Tujuan pemasangan: <strong>{{ $vehicle->nopol }}</strong>, {{ Layout::part($selected) }} posisi <strong>{{ $selected }}</strong>.</p>
        @if(! $vehicle->is_active)
            <p class="tire-hint" style="margin:0;">Armada nonaktif tidak dapat menerima pemasangan baru.</p>
        @elseif($ready->isEmpty())
            <p class="tire-hint" style="margin:0;">Tidak ada ban siap pakai di gudang (aktif, layak, bernomor seri).</p>
        @else
            <form method="POST" data-draft="install" action="{{ route('inventory.pesona.admin.tires.diagram.install') }}">
                @csrf
                <input type="hidden" name="form_action" value="install">
                <input type="hidden" name="inv_pesona_vehicle_id" value="{{ $vehicle->id }}">
                <input type="hidden" name="position_number" value="{{ $selected }}">
                <div class="tire-grid">
                    @include('inventory.pesona.admin.tires._ready_combo', ['name' => 'tire_id', 'caption' => 'Ban siap pakai (gudang)', 'pick' => $oldPick('install', 'tire_id')])
                    <label class="tire-field"><span class="tire-label">Tanggal kejadian</span>
                        <input class="tire-input" type="date" name="occurred_on" required max="{{ $today }}" value="{{ $act === 'install' ? $o('occurred_on', $today) : $today }}"></label>
                    <label class="tire-field"><span class="tire-label">Catatan</span>
                        <input class="tire-input" name="event_notes" maxlength="1000" value="{{ $act === 'install' ? $o('event_notes') : '' }}"></label>
                </div>
                <button class="tire-btn tire-btn-primary" type="submit" style="margin-top:12px;">Pasang ban</button>
            </form>
        @endif
    @endif
@endif
