@php
    $canInstall = $tire->is_active && $tire->location_status === 'GUDANG' && $tire->serial_number !== null && $tire->is_usable === true;
    $today = now()->toDateString();
@endphp
<x-inv-pesona-app title="Detail Ban — Inventory Pesona">
    @include('inventory.pesona.admin.tires._style')
    <div class="tire-wrap">
        <div class="tire-head">
            <div>
                <a class="tire-btn" href="{{ route('inventory.pesona.admin.tires.index') }}">← Master Ban</a>
                <h1 class="pesona-title" style="margin-top:10px;">
                    @if($tire->serial_number !== null) <span class="tire-serial">{{ $tire->serial_number }}</span>
                    @else Ban tanpa nomor seri @endif
                </h1>
                @if($tire->serial_number === null)
                    <span class="tire-badge tire-badge-warn">Nomor seri belum diisi — perlu dilengkapi sebelum dipasang</span>
                @endif
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                @if($tire->inv_pesona_vehicle_id)<a class="tire-btn" href="{{ route('inventory.pesona.admin.tires.diagram', ['vehicle_id' => $tire->inv_pesona_vehicle_id, 'pos' => $tire->position_number]) }}">Lihat di Ban Armada</a>@endif
                <a class="tire-btn" href="{{ route('inventory.pesona.admin.tires.edit', $tire) }}">Edit Data</a>
            </div>
        </div>

        <div class="tire-card">
            <dl class="tire-dl">
                <div><dt>Tipe</dt><dd>{{ \App\Models\InvPesonaTire::TYPES[$tire->tire_type] ?? $tire->tire_type }}</dd></div>
                <div><dt>Merek / ukuran</dt><dd>{{ trim(($tire->brand ?? '').' '.($tire->size ?? '')) ?: '—' }}</dd></div>
                <div><dt>Kondisi</dt><dd>{{ \App\Models\InvPesonaTire::CONDITIONS[$tire->tire_condition] ?? $tire->tire_condition }}</dd></div>
                <div><dt>Kelayakan</dt><dd>{{ $tire->usableLabel() }}</dd></div>
                <div><dt>Lokasi saat ini</dt><dd>
                    @if($tire->location_status === 'ARMADA') Armada — {{ $tire->vehicle?->nopol ?? '—' }}, posisi {{ $tire->position_number }}
                    @else Gudang @endif</dd></div>
                <div><dt>Status</dt><dd>{{ $tire->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div>
                <div><dt>Tanggal penerimaan</dt><dd>{{ $tire->received_on?->format('d/m/Y') ?? '—' }}</dd></div>
                <div><dt>Pemasok</dt><dd>{{ $tire->supplier_name ?? '—' }}</dd></div>
                <div><dt>Nomor dokumen</dt><dd>{{ $tire->document_number ?? '—' }}</dd></div>
                <div style="grid-column:1/-1;"><dt>Catatan</dt><dd>{{ $tire->notes ?? '—' }}</dd></div>
            </dl>
        </div>

        <div class="tire-actions" style="display:flex;flex-direction:column;gap:10px;">
            @if($tire->location_status === 'GUDANG' && $tire->is_active)
                <details @if($errors->has('position_number') || $errors->has('inv_pesona_vehicle_id') || $errors->has('tire')) open @endif>
                    <summary>Pasang ke armada</summary>
                    @if($canInstall)
                        <form method="POST" action="{{ route('inventory.pesona.admin.tires.install', $tire) }}">
                            @csrf
                            <div class="tire-grid">
                                <label class="tire-field"><span class="tire-label">Kendaraan aktif</span>
                                    <select class="tire-input" name="inv_pesona_vehicle_id" required>
                                        <option value="">— pilih —</option>
                                        @foreach($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}" @selected((string) old('inv_pesona_vehicle_id') === (string) $vehicle->id)>{{ $vehicle->nopol }}</option>
                                        @endforeach
                                    </select></label>
                                <label class="tire-field"><span class="tire-label">Nomor posisi</span>
                                    <select class="tire-input" name="position_number" required>
<option value="">— pilih posisi —</option>
@foreach(\App\Services\Inventory\PesonaTireLayout::byPart() as $part => $numbers)
<optgroup label="{{ ucfirst(strtolower($part)) }}">
@foreach($numbers as $n)<option value="{{ $n }}" @selected((string) old('position_number') === (string) $n)>{{ $n }}</option>@endforeach
</optgroup>
@endforeach
</select>
                                    <span class="tire-hint">Posisi 1–18 sesuai diagram ban. Satu posisi hanya untuk satu ban.</span></label>
                                <label class="tire-field"><span class="tire-label">Tanggal kejadian</span>
                                    <input class="tire-input" type="date" name="occurred_on" required max="{{ $today }}" value="{{ old('occurred_on', $today) }}"></label>
                                <label class="tire-field"><span class="tire-label">Catatan</span>
                                    <input class="tire-input" name="event_notes" maxlength="1000" value="{{ old('event_notes') }}"></label>
                            </div>
                            <button class="tire-btn tire-btn-primary" type="submit" style="margin-top:12px;">Pasang</button>
                        </form>
                    @else
                        <p style="margin:10px 0 0;color:var(--pesona-muted);font-size:13px;">
                            Ban belum dapat dipasang:
                            @if($tire->serial_number === null) nomor seri belum diisi.
                            @else hanya ban berstatus layak yang dapat dipasang (saat ini: {{ $tire->usableLabel() }}). @endif
                        </p>
                    @endif
                </details>
            @endif

            @if($tire->location_status === 'ARMADA')
                <details open>
                    <summary>Kembalikan ke gudang</summary>
                    <form method="POST" action="{{ route('inventory.pesona.admin.tires.return', $tire) }}">
                        @csrf
                        <p class="tire-hint" style="margin:0 0 10px;">Pilih hasil pemeriksaan petugas. Kondisi dan kelayakan tidak diisi otomatis.</p>
                        <div class="tire-grid">
                            <label class="tire-field"><span class="tire-label">Kondisi</span>
                                <select class="tire-input" name="tire_condition" required>
                                    <option value="">— pilih —</option>
                                    @foreach(\App\Models\InvPesonaTire::CONDITIONS as $value => $label)
                                        <option value="{{ $value }}" @selected(old('tire_condition') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select></label>
                            <label class="tire-field"><span class="tire-label">Kelayakan</span>
                                <select class="tire-input" name="is_usable" required>
                                    <option value="">— pilih —</option>
                                    <option value="1" @selected(old('is_usable') === '1')>Layak</option>
                                    <option value="0" @selected(old('is_usable') === '0')>Tidak layak</option>
                                    <option value="unknown" @selected(old('is_usable') === 'unknown')>Belum diketahui</option>
                                </select></label>
                            <label class="tire-field"><span class="tire-label">Tanggal kejadian</span>
                                <input class="tire-input" type="date" name="occurred_on" required max="{{ $today }}" value="{{ old('occurred_on', $today) }}"></label>
                            <label class="tire-field"><span class="tire-label">Catatan</span>
                                <input class="tire-input" name="event_notes" maxlength="1000" value="{{ old('event_notes') }}"></label>
                        </div>
                        <button class="tire-btn tire-btn-primary" type="submit" style="margin-top:12px;">Kembalikan</button>
                    </form>
                </details>
                <p class="tire-hint" style="margin:0;">Ban yang masih terpasang harus dikembalikan ke gudang sebelum dapat dinonaktifkan.</p>
            @else
                <details>
                    <summary>{{ $tire->is_active ? 'Nonaktifkan ban' : 'Aktifkan kembali ban' }}</summary>
                    <form method="POST" action="{{ route($tire->is_active ? 'inventory.pesona.admin.tires.deactivate' : 'inventory.pesona.admin.tires.activate', $tire) }}"
                          onsubmit="return confirm('{{ $tire->is_active ? 'Nonaktifkan ban ini? Data dan riwayat tetap tersimpan.' : 'Aktifkan kembali ban ini?' }}')">
                        @csrf
                        <div class="tire-grid">
                            <label class="tire-field"><span class="tire-label">Tanggal kejadian</span>
                                <input class="tire-input" type="date" name="occurred_on" required max="{{ $today }}" value="{{ old('occurred_on', $today) }}"></label>
                            <label class="tire-field"><span class="tire-label">Catatan</span>
                                <input class="tire-input" name="event_notes" maxlength="1000" value="{{ old('event_notes') }}"></label>
                        </div>
                        <button class="tire-btn {{ $tire->is_active ? 'tire-btn-danger' : 'tire-btn-primary' }}" type="submit" style="margin-top:12px;">{{ $tire->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    </form>
                </details>
            @endif
        </div>

        <div class="tire-card">
            <h2 style="margin:0 0 10px;font-size:15px;">Riwayat</h2>
            @if($histories->isEmpty())
                <p style="margin:0;color:var(--pesona-muted);">Belum ada riwayat.</p>
            @else
                <div class="tire-table-wrap">
                    <table class="tire-table">
                        <thead><tr><th>Tgl kejadian</th><th>Dicatat</th><th>Kejadian</th><th>Dari</th><th>Ke</th><th>Pelaksana</th><th>Catatan</th></tr></thead>
                        <tbody>
                            @foreach($histories as $h)
                                @php $b = $h->before_snapshot; $a = $h->after_snapshot; @endphp
                                <tr>
                                    <td style="white-space:nowrap;">{{ $h->occurred_at?->format('d/m/Y') }}</td>
                                    <td style="white-space:nowrap;">{{ $h->created_at?->format('d/m/Y H:i') }}</td>
                                    <td><strong>{{ \App\Models\InvPesonaTireHistory::EVENTS[$h->event_type] ?? $h->event_type }}</strong>
                                        @foreach($h->changedFields() as $line)<div class="tire-hint">{{ $line }}</div>@endforeach</td>
                                    <td>{{ $h->from_vehicle_id ? (($b['nopol'] ?? '—').', posisi '.$h->from_position_number) : '—' }}</td>
                                    <td>{{ $h->to_vehicle_id ? (($a['nopol'] ?? '—').', posisi '.$h->to_position_number) : ($h->event_type === 'KEMBALI' ? 'Gudang' : '—') }}</td>
                                    <td>{{ $h->created_by_name_snapshot ?? '—' }}</td>
                                    <td>{{ $h->notes ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:14px;"><x-pagination :items="$histories" /></div>
            @endif
        </div>
    </div>
</x-inv-pesona-app>
