<x-ops-app title="Request Barang OPS">
    <div class="ops-header ops-need-header">
        <div>
            <h1 class="ops-title">Request Barang</h1>
            <p class="ops-subtitle">Pantau request barang yang diproses oleh Admin ATK.</p>
        </div>
        <a class="ops-btn ops-btn-primary" href="{{ route('v2.ops.admin.need-requests.create') }}">Buat Request</a>
    </div>

    <div class="ops-need-list">
        @forelse($needRequests as $needRequest)
            @php($statusLabel = match($needRequest->status) {'PENDING'=>'Menunggu','DONE'=>'Selesai','REJECTED'=>'Ditolak',default=>$needRequest->status})
            <article class="ops-card ops-need-card">
                <div class="ops-need-top">
                    <div>
                        <strong>{{ $needRequest->requested_item_name }}</strong>
                        <span>{{ $needRequest->created_at?->format('d/m/Y H:i') }}</span>
                    </div>
                    <span class="ops-badge ops-need-{{ strtolower($needRequest->status) }}">{{ $statusLabel }}</span>
                </div>
                <dl>
                    <div><dt>Jumlah</dt><dd>{{ $needRequest->qty }} {{ $needRequest->unit_name }}</dd></div>
                    <div><dt>Pemohon</dt><dd>{{ $needRequest->user_name_snapshot }}</dd></div>
                </dl>
                <div class="ops-need-note"><span>Alasan</span><p>{{ $needRequest->reason }}</p></div>
                @if($needRequest->admin_note)<div class="ops-need-note"><span>Catatan Admin ATK</span><p>{{ $needRequest->admin_note }}</p></div>@endif
            </article>
        @empty
            <div class="ops-card ops-empty">Belum ada request barang.</div>
        @endforelse
    </div>
    <x-pagination :items="$needRequests" preserve-query />

    <style>
        .ops-need-header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
        .ops-need-list { display:grid; gap:12px; }
        .ops-need-top { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; padding-bottom:12px; border-bottom:1px solid var(--ops-border); }
        .ops-need-top strong,.ops-need-top span { display:block; }
        .ops-need-top strong { font-size:14px; overflow-wrap:anywhere; }
        .ops-need-top div > span { margin-top:4px; color:var(--ops-muted); font-size:11px; }
        .ops-need-card dl { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; margin:14px 0; }
        .ops-need-card dt,.ops-need-note span { color:var(--ops-muted); font-size:10px; font-weight:800; text-transform:uppercase; }
        .ops-need-card dd { margin:4px 0 0; font-size:12px; font-weight:700; }
        .ops-need-note { padding-top:12px; border-top:1px solid var(--ops-border); }
        .ops-need-note + .ops-need-note { margin-top:12px; }
        .ops-need-note p { margin:5px 0 0; font-size:12px; line-height:1.55; overflow-wrap:anywhere; }
        .ops-need-pending { color:#92400E; background:#FEF3C7; }
        .ops-need-done { color:var(--ops-dark); background:var(--ops-soft); }
        .ops-need-rejected { color:#991B1B; background:#FEE2E2; }
        @media (max-width:639px) { .ops-need-header { flex-direction:column; } .ops-need-header .ops-btn { width:100%; } }
        @media (min-width:768px) { .ops-need-list { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    </style>
</x-ops-app>
