@php
    $mapping = $overtime->user?->overtimeApproverAssignment;
    $currentActions = $overtime->currentApprovalActions();
    $compact = $compact ?? false;
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
    @endphp
    <div>Disetujui: {{ $supervisorName }}{{ $overtime->status === \App\Models\OvertimeRequest::STATUS_PENDING_SUPERVISOR ? ' - Menunggu' : '' }}</div>
    <div>Mengetahui: {{ $managerName }}</div>
</div>
