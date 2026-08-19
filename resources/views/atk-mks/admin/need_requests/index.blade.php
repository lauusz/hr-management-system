<x-atk-mks-app title="Request Barang ATK MKS">
    <div class="atk-mks-header atk-mks-need-header">
        <div>
            <h1 class="atk-mks-title">Request Barang</h1>
            <p class="atk-mks-subtitle">Pantau request barang yang diproses oleh Admin ATK.</p>
        </div>
        <a class="atk-mks-btn atk-mks-btn-primary" href="{{ route('v2.atk-mks.admin.need-requests.create') }}">Buat Request</a>
    </div>

    <div class="atk-mks-need-list">
        @forelse($needRequests as $needRequest)
            @php($statusLabel = match($needRequest->status) {'PENDING'=>'Menunggu','DONE'=>'Selesai','REJECTED'=>'Ditolak',default=>$needRequest->status})
            <article class="atk-mks-card atk-mks-need-card">
                <div class="atk-mks-need-top">
                    <div>
                        <strong>{{ $needRequest->requested_item_name }}</strong>
                        <span>{{ $needRequest->created_at?->format('d/m/Y H:i') }}</span>
                    </div>
                    <span class="atk-mks-badge atk-mks-need-{{ strtolower($needRequest->status) }}">{{ $statusLabel }}</span>
                </div>
                <dl>
                    <div><dt>Jumlah</dt><dd>{{ $needRequest->qty }} {{ $needRequest->unit_name }}</dd></div>
                    <div><dt>Pemohon</dt><dd>{{ $needRequest->user_name_snapshot }}</dd></div>
                </dl>
                <div class="atk-mks-need-note"><span>Alasan</span><p>{{ $needRequest->reason }}</p></div>
                @if($needRequest->admin_note)<div class="atk-mks-need-note"><span>Catatan Admin ATK</span><p>{{ $needRequest->admin_note }}</p></div>@endif
            </article>
        @empty
            <div class="atk-mks-card atk-mks-empty">Belum ada request barang.</div>
        @endforelse
    </div>
    <x-pagination :items="$needRequests" preserve-query />

    <style>
        .atk-mks-need-header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
        .atk-mks-need-list { display:grid; gap:12px; }
        .atk-mks-need-top { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; padding-bottom:12px; border-bottom:1px solid var(--atk-mks-border); }
        .atk-mks-need-top strong,.atk-mks-need-top span { display:block; }
        .atk-mks-need-top strong { font-size:14px; overflow-wrap:anywhere; }
        .atk-mks-need-top div > span { margin-top:4px; color:var(--atk-mks-muted); font-size:11px; }
        .atk-mks-need-card dl { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; margin:14px 0; }
        .atk-mks-need-card dt,.atk-mks-need-note span { color:var(--atk-mks-muted); font-size:10px; font-weight:800; text-transform:uppercase; }
        .atk-mks-need-card dd { margin:4px 0 0; font-size:12px; font-weight:700; }
        .atk-mks-need-note { padding-top:12px; border-top:1px solid var(--atk-mks-border); }
        .atk-mks-need-note + .atk-mks-need-note { margin-top:12px; }
        .atk-mks-need-note p { margin:5px 0 0; font-size:12px; line-height:1.55; overflow-wrap:anywhere; }
        .atk-mks-need-pending { color:#92400E; background:#FEF3C7; }
        .atk-mks-need-done { color:var(--atk-mks-dark); background:var(--atk-mks-soft); }
        .atk-mks-need-rejected { color:#991B1B; background:#FEE2E2; }
        @media (max-width:639px) { .atk-mks-need-header { flex-direction:column; } .atk-mks-need-header .atk-mks-btn { width:100%; } }
        @media (min-width:768px) { .atk-mks-need-list { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    </style>
</x-atk-mks-app>


