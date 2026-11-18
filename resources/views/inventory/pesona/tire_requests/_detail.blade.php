@php
    use App\Services\Inventory\PesonaTireLayout as Layout;
    $old = $r->old_tire_snapshot;
    $assigned = $r->assigned_tire_snapshot;
    $tireText = fn ($s) => $s ? (($s['serial_number'] ?? '—').' — '.(\App\Models\InvPesonaTire::TYPES[$s['tire_type'] ?? ''] ?? ($s['tire_type'] ?? '')).(trim(($s['brand'] ?? '').' '.($s['size'] ?? '')) ? ' — '.trim(($s['brand'] ?? '').' '.($s['size'] ?? '')) : '')) : '—';
@endphp
<div class="tire-card">
    <dl class="tire-dl">
        <div><dt>Nomor pengajuan</dt><dd class="tire-serial">{{ $r->request_number }}</dd></div>
        <div><dt>Tanggal pengajuan</dt><dd>{{ $r->created_at?->format('d/m/Y H:i') }}</dd></div>
        <div><dt>Status</dt><dd><span class="tire-badge {{ $r->status === 'APPROVED' ? 'tire-badge-ok' : ($r->status === 'REJECTED' ? 'tire-badge-bad' : 'tire-badge-warn') }}">{{ $r->statusLabel() }}</span></dd></div>
        <div><dt>Pemohon</dt><dd>{{ $r->requested_by_name_snapshot }}</dd></div>
        <div><dt>Armada</dt><dd>{{ $r->nopol_snapshot }}</dd></div>
        <div><dt>Posisi</dt><dd>{{ Layout::label($r->position_number) }}</dd></div>
        <div><dt>Kebutuhan</dt><dd>{{ \App\Models\InvPesonaTireRequest::TYPES[$r->request_type] ?? $r->request_type }}</dd></div>
        <div><dt>Ban lama saat pengajuan</dt><dd>{{ $r->request_type === 'GANTI' ? $tireText($old) : 'Posisi kosong saat pengajuan' }}</dd></div>
        <div style="grid-column:1/-1;"><dt>Keterangan kebutuhan</dt><dd style="white-space:pre-line;">{{ $r->need_notes }}</dd></div>
        @if($r->status !== 'PENDING')
            <div><dt>Diproses oleh</dt><dd>{{ $r->reviewed_by_name_snapshot ?? '—' }}</dd></div>
            <div><dt>Waktu pemrosesan</dt><dd>{{ $r->reviewed_at?->format('d/m/Y H:i') ?? '—' }}</dd></div>
            @if($r->status === 'APPROVED')
                <div><dt>Ban yang diberikan</dt><dd>{{ $tireText($assigned) }}</dd></div>
            @else
                <div style="grid-column:1/-1;"><dt>Alasan penolakan</dt><dd style="white-space:pre-line;">{{ $r->rejection_reason }}</dd></div>
            @endif
        @endif
    </dl>
    @if($r->status === 'PENDING')
        <p class="tire-hint" style="margin:12px 0 0;">Menunggu admin. Pengajuan belum memasang atau memindahkan ban.</p>
    @endif
</div>
