<x-inv-pesona-app title="Master Ban — Inventory Pesona">
    @include('inventory.pesona.admin.tires._style')
    <div class="tire-wrap">
        <div class="tire-head">
            <div>
                <h1 class="pesona-title">Master Ban</h1>
                <p class="pesona-subtitle">Setiap ban dicatat berdasarkan nomor seri. Siap pakai (aktif, di gudang, layak, bernomor seri): <strong>{{ $readyCount }}</strong> ban.</p>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a class="tire-btn" href="{{ route('inventory.pesona.admin.tires.diagram', array_filter(['vehicle_id' => $filters['vehicle_id']])) }}">Ban Armada</a>
                <a class="tire-btn tire-btn-primary" href="{{ route('inventory.pesona.admin.tires.create') }}">+ Catat Ban</a>
            </div>
        </div>

        <form method="GET" class="tire-card" action="{{ route('inventory.pesona.admin.tires.index') }}">
            <div class="tire-grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));">
                <label class="tire-field"><span class="tire-label">Nomor seri</span>
                    <input class="tire-input" type="search" name="q" value="{{ $filters['q'] }}" maxlength="100" placeholder="Cari nomor seri"></label>
                <label class="tire-field"><span class="tire-label">Status</span>
                    <select class="tire-input" name="status">
                        <option value="active" @selected($filters['status'] === 'active')>Aktif</option>
                        <option value="inactive" @selected($filters['status'] === 'inactive')>Nonaktif</option>
                        <option value="all" @selected($filters['status'] === 'all')>Semua</option>
                    </select></label>
                <label class="tire-field"><span class="tire-label">Lokasi</span>
                    <select class="tire-input" name="location">
                        <option value="all">Semua</option>
                        <option value="GUDANG" @selected($filters['location'] === 'GUDANG')>Gudang</option>
                        <option value="ARMADA" @selected($filters['location'] === 'ARMADA')>Armada</option>
                    </select></label>
                <label class="tire-field"><span class="tire-label">Kendaraan</span>
                    <select class="tire-input" name="vehicle_id">
                        <option value="">Semua</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected((string) $filters['vehicle_id'] === (string) $vehicle->id)>{{ $vehicle->nopol }}{{ $vehicle->is_active ? '' : ' (nonaktif)' }}</option>
                        @endforeach
                    </select></label>
                <label class="tire-field"><span class="tire-label">Kelayakan</span>
                    <select class="tire-input" name="usable">
                        <option value="all">Semua</option>
                        <option value="1" @selected($filters['usable'] === '1')>Layak</option>
                        <option value="0" @selected($filters['usable'] === '0')>Tidak layak</option>
                        <option value="unknown" @selected($filters['usable'] === 'unknown')>Belum diketahui</option>
                    </select></label>
            </div>
            <div style="display:flex;gap:8px;margin-top:12px;">
                <button class="tire-btn tire-btn-primary" type="submit">Terapkan</button>
                <a class="tire-btn" href="{{ route('inventory.pesona.admin.tires.index') }}">Reset</a>
            </div>
        </form>

        <div class="tire-card">
            @if($tires->isEmpty())
                <p style="margin:0;color:var(--pesona-muted);">Tidak ada ban yang sesuai. Gunakan “Catat Ban” untuk menambah data.</p>
            @else
                <div class="tire-table-wrap">
                    <table class="tire-table">
                        <thead><tr><th>Nomor seri</th><th>Tipe</th><th>Merek / ukuran</th><th>Kondisi</th><th>Kelayakan</th><th>Lokasi</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @foreach($tires as $tire)
                                <tr>
                                    <td>
                                        @if($tire->serial_number !== null)
                                            <span class="tire-serial">{{ $tire->serial_number }}</span>
                                        @else
                                            <span class="tire-badge tire-badge-warn">Seri belum diisi — perlu dilengkapi</span>
                                        @endif
                                    </td>
                                    <td>{{ \App\Models\InvPesonaTire::TYPES[$tire->tire_type] ?? $tire->tire_type }}</td>
                                    <td>{{ trim(($tire->brand ?? '').' '.($tire->size ?? '')) ?: '—' }}</td>
                                    <td>{{ \App\Models\InvPesonaTire::CONDITIONS[$tire->tire_condition] ?? $tire->tire_condition }}</td>
                                    <td><span class="tire-badge {{ $tire->is_usable === true ? 'tire-badge-ok' : ($tire->is_usable === false ? 'tire-badge-bad' : '') }}">{{ $tire->usableLabel() }}</span></td>
                                    <td>
                                        @if($tire->location_status === 'ARMADA')
                                            Armada — {{ $tire->vehicle?->nopol ?? '—' }}, posisi {{ $tire->position_number }}
                                        @else
                                            Gudang
                                        @endif
                                    </td>
                                    <td><span class="tire-badge {{ $tire->is_active ? 'tire-badge-ok' : '' }}">{{ $tire->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                    <td style="text-align:right;"><a class="tire-btn" href="{{ route('inventory.pesona.admin.tires.show', $tire) }}">Detail</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:14px;"><x-pagination :items="$tires" /></div>
            @endif
        </div>
    </div>
</x-inv-pesona-app>
