@php
    use App\Services\Inventory\PesonaTireLayout as Layout;
    $pending = $r->status === 'PENDING';
    $replace = $r->request_type === 'GANTI';
    $changed = $replace ? (! $current || (int) $current->id !== (int) $r->old_tire_id) : (bool) $current;
    $actionLabel = $replace ? 'Setujui & Ganti' : 'Setujui & Pasang';
@endphp
<x-inv-pesona-app title="Proses Pengajuan Ban — Inventory Pesona">
    @include('inventory.pesona.admin.tires._style')
    <div class="tire-wrap">
        <div>
            <a class="tire-btn" href="{{ route('inventory.pesona.admin.tire-requests.index') }}">← Approval Ban</a>
            <h1 class="pesona-title" style="margin-top:10px;">Pengajuan Ban {{ $r->request_number }}</h1>
        </div>

        @include('inventory.pesona.tire_requests._detail')

        @if($pending)
            <div class="tire-card">
                <h2 style="margin:0 0 8px;font-size:15px;">Keadaan {{ Layout::label($r->position_number) }} — {{ $r->nopol_snapshot }}</h2>
                <dl class="tire-dl">
                    <div><dt>Tercatat saat pengajuan</dt><dd>{{ $replace ? 'Terisi ban '.($r->old_tire_snapshot['serial_number'] ?? '—') : 'Kosong' }}</dd></div>
                    <div><dt>Keadaan aktual sekarang</dt><dd>{{ $current ? 'Terisi ban '.($current->serial_number ?? '—').' ('.$current->usableLabel().')' : 'Kosong' }}</dd></div>
                    <div><dt>Armada</dt><dd>{{ $vehicle?->is_active ? 'Aktif' : 'Nonaktif / tidak ditemukan' }}</dd></div>
                </dl>
                @if($changed)
                    <div class="pesona-alert pesona-alert-warning" role="alert" style="margin:12px 0 0;">
                        Keadaan posisi sudah berubah sejak pengajuan dibuat ({{ $replace ? 'ban lama tidak lagi terpasang di posisi ini' : 'posisi sudah terisi' }}). Persetujuan akan ditolak sistem; Anda dapat menolak pengajuan ini.
                    </div>
                @endif
            </div>

            <div class="tire-actions" style="display:flex;flex-direction:column;gap:10px;">
                <details open>
                    <summary>{{ $actionLabel }}</summary>
                    @if(! $vehicle?->is_active)
                        <p class="tire-hint" style="margin:10px 0 0;">Armada nonaktif tidak dapat menerima pemasangan atau penggantian.</p>
                    @elseif(! \App\Services\Inventory\PesonaTireLayout::has((int) $r->position_number))
                        <p class="tire-hint" style="margin:10px 0 0;">Posisi {{ $r->position_number }} tidak aktif pada skema 18 posisi (pengajuan lama). Pengajuan tidak dapat disetujui; Anda dapat menolaknya.</p>
                    @elseif($ready->isEmpty())
                        <p class="tire-hint" style="margin:10px 0 0;">Tidak ada ban siap pakai di gudang (aktif, layak, bernomor seri). Pengajuan belum dapat disetujui.</p>
                    @else
                        <form method="POST" action="{{ route('inventory.pesona.admin.tire-requests.approve', $r) }}">
                            @csrf
                            <div class="tire-grid">
                                <label class="tire-field"><span class="tire-label">Ban siap pakai (gudang)</span>
                                    <select class="tire-input" name="tire_id" required>
                                        <option value="">— pilih nomor seri —</option>
                                        @foreach($ready as $t)
                                            <option value="{{ $t->id }}" @selected((string) old('tire_id') === (string) $t->id)>{{ $t->serial_number }} — {{ \App\Models\InvPesonaTire::TYPES[$t->tire_type] ?? $t->tire_type }}{{ $t->brand || $t->size ? ' — '.trim($t->brand.' '.$t->size) : '' }} ({{ \App\Models\InvPesonaTire::CONDITIONS[$t->tire_condition] ?? $t->tire_condition }})</option>
                                        @endforeach
                                    </select></label>
                                @if($replace)
                                    <label class="tire-field"><span class="tire-label">Kondisi ban lama setelah dicopot</span>
                                        <select class="tire-input" name="tire_condition" required>
                                            <option value="">— pilih —</option>
                                            @foreach(\App\Models\InvPesonaTire::CONDITIONS as $value => $label)<option value="{{ $value }}" @selected(old('tire_condition') === $value)>{{ $label }}</option>@endforeach
                                        </select></label>
                                    <label class="tire-field"><span class="tire-label">Kelayakan ban lama</span>
                                        <select class="tire-input" name="is_usable" required>
                                            <option value="">— pilih —</option>
                                            @foreach(['1' => 'Layak', '0' => 'Tidak layak', 'unknown' => 'Belum diketahui'] as $value => $label)<option value="{{ $value }}" @selected((string) old('is_usable') === (string) $value)>{{ $label }}</option>@endforeach
                                        </select></label>
                                @endif
                            </div>
                            <p class="tire-hint" style="margin:10px 0 0;">{{ $replace ? 'Ban lama dicopot ke gudang dan ban pilihan dipasang pada posisi yang sama dalam satu proses.' : 'Ban pilihan dipasang pada posisi kosong ini.' }} Pengajuan hanya berstatus Disetujui bila pemrosesan berhasil.</p>
                            <button class="tire-btn tire-btn-primary" type="submit" style="margin-top:12px;">{{ $actionLabel }}</button>
                        </form>
                    @endif
                </details>

                <details @if($errors->has('rejection_reason')) open @endif>
                    <summary>Tolak pengajuan</summary>
                    <form method="POST" action="{{ route('inventory.pesona.admin.tire-requests.reject', $r) }}">
                        @csrf
                        <label class="tire-field"><span class="tire-label">Alasan penolakan (wajib)</span>
                            <textarea class="tire-input" name="rejection_reason" maxlength="1000" required>{{ old('rejection_reason') }}</textarea></label>
                        <button class="tire-btn tire-btn-danger" type="submit" style="margin-top:12px;">Tolak</button>
                    </form>
                </details>
            </div>
        @endif
    </div>
</x-inv-pesona-app>
