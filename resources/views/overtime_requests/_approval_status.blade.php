@php
    $mapping = $overtime->user?->overtimeApproverAssignment;
    $currentActions = $overtime->currentApprovalActions();
    $compact = $compact ?? false;
@endphp
<div style="{{ $compact ? 'margin:4px 0 0;font-size:11px;line-height:1.4' : 'margin:12px 0;font-size:13px;line-height:1.7' }}">
    <strong>Persetujuan lembur</strong>
    @if($currentActions->isEmpty() && in_array($overtime->status, [\App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR, \App\Models\OvertimeRequest::STATUS_APPROVED_HRD]))
        <div>Pengajuan lama telah disetujui.</div>
    @else
        @foreach(['SUPERVISOR' => 'Supervisor', 'MANAGER' => 'Manager'] as $slot => $label)
            @php
                $action = $currentActions->firstWhere('decision_slot', $slot);
                $approverName = $slot === 'SUPERVISOR' ? $mapping?->supervisor?->name : $mapping?->manager?->name;
            @endphp
            <div>{{ $label }}: @if($action) {{ $action->actor_name }} — {{ $action->action === 'APPROVE' ? 'Disetujui' : 'Ditolak' }} {{ $action->acted_at?->format('d/m/Y H:i') }} @elseif($compact && !$approverName) - @else {{ $approverName ?? 'Belum ditentukan' }} — {{ $overtime->status === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR ? 'Menunggu' : 'Tidak diperlukan' }} @endif</div>
        @endforeach
    @endif
    @if($overtime->approvalActions->isNotEmpty())
        <details><summary>Riwayat keputusan</summary>
            @foreach($overtime->approvalActions as $action)
                <div>Putaran {{ $action->approval_round }} — {{ $action->actor_name }} ({{ $action->decision_slot ?? $action->actor_role }}) — {{ match($action->action) { 'APPROVE' => 'menyetujui', 'REJECT' => 'menolak', default => 'mengulang persetujuan' } }} — {{ $action->acted_at?->format('d/m/Y H:i') }} @if($action->notes) — {{ $action->notes }} @endif</div>
            @endforeach
        </details>
    @endif
</div>
