@php
    $mapping = $overtime->user?->overtimeApproverAssignment;
    $currentActions = $overtime->currentApprovalActions();
    $compact = $compact ?? false;
    $showHistory = $showHistory ?? true;
    $showTitle = $showTitle ?? true;
    $class = $class ?? '';
@endphp
<div class="{{ $class }}" style="{{ $compact ? 'margin:4px 0 0;font-size:11px;line-height:1.4' : 'margin:12px 0;font-size:13px;line-height:1.7' }}">
    @if($showTitle)
        <strong>Persetujuan lembur</strong>
    @endif
    @php
        $supervisorAction = $currentActions->firstWhere('decision_slot', 'SUPERVISOR');
        $supervisorName = $supervisorAction?->actor_name ?? $mapping?->supervisor?->name ?? ($compact ? '-' : 'Belum ditentukan');
        $managerName = $mapping?->manager?->name ?? ($compact ? '-' : 'Belum ditentukan');
        $supervisorStatus = $supervisorAction
            ? ($supervisorAction->action === 'APPROVE' ? 'Disetujui' : 'Ditolak')
            : (in_array($overtime->status, [\App\Models\OvertimeRequest::STATUS_APPROVED_SUPERVISOR, \App\Models\OvertimeRequest::STATUS_APPROVED_HRD]) ? 'Disetujui' : 'Menunggu');
    @endphp
    <div>Disetujui: {{ $supervisorName }} — {{ $supervisorStatus }}</div>
    <div>Mengetahui: {{ $managerName }}</div>
    @if($showHistory && $overtime->approvalActions->isNotEmpty())
        <details><summary>Riwayat keputusan</summary>
            @foreach($overtime->approvalActions as $action)
                <div>Putaran {{ $action->approval_round }} — {{ $action->actor_name }} ({{ $action->decision_slot ?? $action->actor_role }}) — {{ match($action->action) { 'APPROVE' => 'menyetujui', 'REJECT' => 'menolak', default => 'mengulang persetujuan' } }} — {{ $action->acted_at?->format('d/m/Y H:i') }} @if($action->notes) — {{ $action->notes }} @endif</div>
            @endforeach
        </details>
    @endif
</div>
