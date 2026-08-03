<x-ops-app title="Periksa Request OPS">
    <div class="ops-header"><h1 class="ops-title">Periksa Request</h1><p class="ops-subtitle">{{ $atkRequest->request_number }} · {{ $atkRequest->user_name_snapshot }}</p></div>
    <div class="ops-card" style="margin-bottom:14px"><span class="ops-badge">{{ match($atkRequest->status) {'PENDING'=>'Menunggu','APPROVED'=>'Disetujui','PARTIAL'=>'Sebagian','REJECTED'=>'Ditolak',default=>$atkRequest->status} }}</span>@if($atkRequest->notes)<p>{{ $atkRequest->notes }}</p>@endif</div>
    <div class="ops-grid" data-ops-mobile-cards>
        @foreach($atkRequest->items as $row)
            <article class="ops-card">
                <h2>{{ $row->item_name_snapshot }}</h2>
                <p class="ops-muted">Jumlah: {{ $row->qty }} {{ $row->unit_name_snapshot }} · Stok: {{ $row->item?->stock_qty ?? 0 }}</p>
                <span class="ops-badge">{{ match($row->status) {'PENDING'=>'Belum diperiksa','APPROVED'=>'Disetujui','REJECTED'=>'Ditolak',default=>$row->status} }}</span>
                @if($row->admin_note)<p class="ops-muted">{{ $row->admin_note }}</p>@endif
                @if($atkRequest->status === 'PENDING')
                    <form method="POST" action="{{ route('v2.ops.admin.requests.items.review', [$atkRequest, $row]) }}" style="margin-top:12px">
                        @csrf
                        <textarea class="ops-textarea" name="admin_note" placeholder="Alasan wajib jika ditolak"></textarea>
                        <div class="ops-actions" style="margin-top:8px">
                            <button class="ops-btn ops-btn-primary" name="status" value="APPROVED" type="submit">Setujui</button>
                            <button class="ops-btn ops-btn-danger" name="status" value="REJECTED" type="submit">Tolak</button>
                        </div>
                    </form>
                @endif
            </article>
        @endforeach
    </div>
    @if($atkRequest->status === 'PENDING')
        <form method="POST" action="{{ route('v2.ops.admin.requests.finalize', $atkRequest) }}" style="margin-top:14px">@csrf<button class="ops-btn ops-btn-primary" style="width:100%" type="submit">Selesaikan</button></form>
    @endif
    <a class="ops-btn ops-btn-soft" style="margin-top:12px" href="{{ route('v2.ops.admin.requests.index') }}">Kembali</a>
</x-ops-app>
