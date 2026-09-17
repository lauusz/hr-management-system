@php
    $employeeCount = $employees->count();
    $pendingInitialCount = $employees->sum('pending_initial_count');
    $pendingHrCount = $employees->sum('pending_hr_count');
@endphp

<article class="approval-map-card">
    <div class="approval-map-card__head">
        <div class="approval-map-person">
            <div class="approval-map-avatar">{{ substr($leader->name, 0, 1) }}</div>
            <div>
                <div class="approval-map-name">{{ $leader->name }}</div>
                <div class="approval-map-meta">
                    {{ $leader->position->name ?? 'Tanpa jabatan' }} · {{ $leader->division->name ?? 'Tanpa divisi' }}
                    @if($leader->profile?->pt)
                        · {{ $leader->profile->pt->name }}
                    @endif
                </div>
            </div>
        </div>
        <span class="approval-map-badge {{ $roleClass }}">{{ $roleLabel }}</span>
    </div>

    <div class="approval-map-card__stats">
        <div class="approval-map-mini-stat">
            <span>Karyawan</span>
            <strong>{{ $employeeCount }}</strong>
        </div>
        <div class="approval-map-mini-stat">
            <span>Pending Awal</span>
            <strong>{{ $pendingInitialCount }}</strong>
        </div>
        <div class="approval-map-mini-stat">
            <span>Pending HR</span>
            <strong>{{ $pendingHrCount }}</strong>
        </div>
    </div>

    <div class="approval-map-list">
        @forelse($employees as $employee)
            <div class="approval-map-employee">
                <div>
                    <div class="approval-map-employee__name">{{ $employee->name }}</div>
                    <div class="approval-map-employee__meta">
                        {{ $employee->position->name ?? 'Tanpa jabatan' }} · {{ $employee->division->name ?? 'Tanpa divisi' }}
                        @if($employee->profile?->pt)
                            · {{ $employee->profile->pt->name }}
                        @endif
                    </div>
                </div>
                @if((int) $employee->pending_initial_count > 0)
                    <span class="approval-map-employee__pending">{{ $employee->pending_initial_count }} pending</span>
                @elseif((int) $employee->pending_hr_count > 0)
                    <span class="approval-map-employee__pending">{{ $employee->pending_hr_count }} HR</span>
                @endif
            </div>
        @empty
            <div class="approval-map-empty-list">Belum ada karyawan pada mapping ini.</div>
        @endforelse
    </div>
</article>
