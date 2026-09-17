<a href="{{ route('hr.approvers.show', $approver) }}" class="approval-map-row">
    <div class="approval-map-person">
        <div class="approval-map-avatar">{{ substr($approver->name, 0, 1) }}</div>
        <div>
            <div class="approval-map-name">{{ $approver->name }}</div>
            <div class="approval-map-meta">
                {{ $approver->position->name ?? 'Tanpa jabatan' }} · {{ $approver->division->name ?? 'Tanpa divisi' }}
                @if($approver->profile?->pt)
                    · {{ $approver->profile->pt->name }}
                @endif
            </div>
        </div>
    </div>
    <span class="approval-map-count">{{ $employeeCount }} {{ \Illuminate\Support\Str::plural('user', $employeeCount) }}</span>
    <span class="approval-map-badge {{ $roleClass }}">{{ $roleLabel }}</span>
</a>
