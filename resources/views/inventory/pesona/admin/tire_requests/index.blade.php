<x-inv-pesona-app title="Approval Ban — Inventory Pesona">
    @include('inventory.pesona.admin.tires._style')
    <div class="tire-wrap">
        <div>
            <h1 class="pesona-title">Approval Ban</h1>
            <p class="pesona-subtitle">Pengajuan pasang/ganti ban dari pengguna. Setujui dengan memilih nomor seri dari ban siap pakai di gudang, atau tolak dengan alasan.</p>
        </div>

        <form method="GET" class="tire-card" action="{{ route('inventory.pesona.admin.tire-requests.index') }}" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
            <label class="tire-field"><span class="tire-label">Cari nomor / nopol / pemohon</span>
                <input class="tire-input" type="search" name="q" value="{{ $q }}" maxlength="100"></label>
            <label class="tire-field"><span class="tire-label">Status</span>
                <select class="tire-input" name="status">
                    <option value="">Semua</option>
                    @foreach(\App\Models\InvPesonaTireRequest::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select></label>
            <button class="tire-btn tire-btn-primary" type="submit">Terapkan</button>
            <a class="tire-btn" href="{{ route('inventory.pesona.admin.tire-requests.index') }}">Reset</a>
        </form>

        <div class="tire-card">
            @if($items->isEmpty())
                <p style="margin:0;color:var(--pesona-muted);">Tidak ada pengajuan ban yang sesuai.</p>
            @else
                <div class="tire-table-wrap">
                    <table class="tire-table">
                        <thead><tr><th>Nomor</th><th>Tanggal</th><th>Pemohon</th><th>Nopol</th><th>Posisi</th><th>Kebutuhan</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @foreach($items as $r)
                                <tr>
                                    <td class="tire-serial">{{ $r->request_number }}</td>
                                    <td style="white-space:nowrap;">{{ $r->created_at?->format('d/m/Y H:i') }}</td>
                                    <td>{{ $r->requested_by_name_snapshot }}</td>
                                    <td>{{ $r->nopol_snapshot }}</td>
                                    <td>{{ \App\Services\Inventory\PesonaTireLayout::label($r->position_number) }}</td>
                                    <td>{{ \App\Models\InvPesonaTireRequest::TYPES[$r->request_type] ?? $r->request_type }}</td>
                                    <td><span class="tire-badge {{ $r->status === 'APPROVED' ? 'tire-badge-ok' : ($r->status === 'REJECTED' ? 'tire-badge-bad' : 'tire-badge-warn') }}">{{ $r->statusLabel() }}</span></td>
                                    <td style="text-align:right;"><a class="tire-btn" href="{{ route('inventory.pesona.admin.tire-requests.show', $r) }}">{{ $r->status === 'PENDING' ? 'Proses' : 'Detail' }}</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:14px;"><x-pagination :items="$items" /></div>
            @endif
        </div>
    </div>
</x-inv-pesona-app>
