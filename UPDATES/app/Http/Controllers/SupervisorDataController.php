<?php

namespace App\Http\Controllers;

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\LeaveRequest;
use App\Models\OffSpvChange;
use App\Models\OffSpvPeriod;
use App\Models\User;
use App\Services\OffSpvQuotaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupervisorDataController extends Controller
{
    public function __construct(protected OffSpvQuotaService $offSpvQuotaService) {}

    public function index(Request $request)
    {
        $allowedRoles = [
            UserRole::SUPERVISOR->value,
            UserRole::MANAGER->value,
        ];
        $requestedRoles = array_values(array_intersect(
            $allowedRoles,
            (array) $request->query('roles', [])
        ));
        $selectedRoles = $requestedRoles ?: [UserRole::SUPERVISOR->value];
        $isRoleFilterActive = $selectedRoles !== [UserRole::SUPERVISOR->value];
        $q = trim((string) $request->query('q'));

        $supervisors = User::with(['division', 'position'])
            ->active()
            ->whereIn('role', $selectedRoles)
            ->when($q !== '', fn ($query) => $query->where('name', 'like', '%'.$q.'%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        if ($this->offSpvQuotaService->rolloutStarted()) {
            $supervisors->getCollection()->each(function (User $user) {
                if (! $user->isSupervisor() || ! $user->isActive()) {
                    return;
                }

                $period = $this->offSpvQuotaService->currentPeriod($user);
                $user->off_spv_stats = $this->offSpvQuotaService->stats($period);
            });
        }

        return view('hr.supervisors.index', compact(
            'supervisors',
            'q',
            'selectedRoles',
            'isRoleFilterActive'
        ));
    }

    public function show(Request $request, User $user)
    {
        abort_unless($user->isSupervisor(), 404);

        if ($user->isActive() && $this->offSpvQuotaService->rolloutStarted()) {
            $this->offSpvQuotaService->currentPeriod($user);
        }

        $defaultYear = $this->offSpvQuotaService->periodForDate(now())['year'];
        $year = $request->integer('year', $defaultYear);
        if ($year < 2026 || $year > 2100) {
            $year = $defaultYear;
        }

        $periods = $user->offSpvPeriods()
            ->where('period_year', $year)
            ->orderBy('period_month')
            ->get()
            ->keyBy('period_month');
        $historicalPeriods = collect();
        $historicalDates = collect();

        if ($year === 2026) {
            foreach (range(1, 7) as $month) {
                $historicalPeriods->put(
                    $month,
                    $this->offSpvQuotaService->periodForDate(Carbon::create(2026, $month, 1)),
                );
            }

            $historicalDates = $user->leaveRequests()
                ->where('type', LeaveType::OFF_SPV->value)
                ->whereBetween('start_date', ['2025-12-26', '2026-07-25'])
                ->whereNotIn('status', [LeaveRequest::STATUS_REJECTED, LeaveRequest::STATUS_CANCELLED])
                ->get()
                ->keyBy(fn (LeaveRequest $leave) => $leave->start_date->toDateString());
        }

        $totals = [
            'quota' => 0,
            'approved' => 0,
            'pending' => 0,
            'remaining' => 0,
            'expired' => 0,
        ];

        foreach ($periods as $period) {
            $period->analytics = $this->offSpvQuotaService->stats($period);
            $totals['quota'] += $period->effective_quota;
            $totals['approved'] += $period->analytics['approved'];
            $totals['pending'] += $period->analytics['pending'];
            if ($period->period_start->lte(today()) && $period->period_end->gte(today())) {
                $totals['remaining'] += $period->analytics['remaining'];
            }
            $totals['expired'] += $period->analytics['expired'];
        }

        $changes = OffSpvChange::query()
            ->with(['actor', 'period'])
            ->whereHas('period', fn ($query) => $query
                ->where('user_id', $user->id)
                ->where('period_year', $year))
            ->latest('created_at')
            ->get();

        $requests = $user->leaveRequests()
            ->with('offSpvPeriod')
            ->where('type', \App\Enums\LeaveType::OFF_SPV->value)
            ->whereHas('offSpvPeriod', fn ($query) => $query->where('period_year', $year))
            ->latest('start_date')
            ->get();

        return view('hr.supervisors.details', compact(
            'user',
            'year',
            'periods',
            'historicalPeriods',
            'historicalDates',
            'totals',
            'changes',
            'requests',
        ));
    }

    public function updateOffSpvPeriod(Request $request, User $user, OffSpvPeriod $period)
    {
        abort_unless($user->isSupervisor() && $period->user_id === $user->id, 404);

        $validated = $request->validate([
            'effective_quota' => ['required', 'integer', 'max:366'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);
        $newQuota = max(0, (int) $validated['effective_quota']);

        DB::transaction(function () use ($period, $newQuota, $validated) {
            $locked = OffSpvPeriod::lockForUpdate()->findOrFail($period->id);
            $before = $locked->effective_quota;
            $locked->update(['effective_quota' => $newQuota]);
            $locked->changes()->create([
                'change_type' => OffSpvChange::TYPE_MANUAL_ADJUSTMENT,
                'quota_before' => $before,
                'quota_after' => $newQuota,
                'reason' => $validated['reason'] ?? null,
                'changed_by' => auth()->id(),
                'created_at' => now(),
            ]);
        });

        return back()->with('success', 'Jatah OFF SPV berhasil diperbarui.');
    }

    public function storeHistoricalOffSpv(Request $request, User $user)
    {
        abort_unless($user->isSupervisor(), 404);

        $validated = $request->validate([
            'period_year' => ['required', 'integer', 'in:2026'],
            'period_month' => ['required', 'integer', 'between:1,7'],
            'effective_quota' => ['required', 'integer', 'max:366'],
            'dates' => ['nullable', 'array'],
            'dates.*' => ['date', 'distinct'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $definition = $this->offSpvQuotaService->periodForDate(
            Carbon::create((int) $validated['period_year'], (int) $validated['period_month'], 1),
        );
        $selectedDates = $validated['dates'] ?? [];

        foreach ($selectedDates as $index => $date) {
            if (! in_array($date, $definition['saturdays'], true)) {
                throw ValidationException::withMessages([
                    "dates.{$index}" => 'Tanggal OFF historis harus hari Sabtu dalam cutoff periode terpilih.',
                ]);
            }
        }

        DB::transaction(function () use ($user, $validated, $definition, $selectedDates) {
            $period = $this->offSpvQuotaService->getOrCreatePeriod($user, $definition['start']);
            $period = OffSpvPeriod::lockForUpdate()->findOrFail($period->id);
            $newQuota = max(0, (int) $validated['effective_quota']);

            if ($period->effective_quota !== $newQuota) {
                $before = $period->effective_quota;
                $period->update(['effective_quota' => $newQuota]);
                $period->changes()->create([
                    'change_type' => OffSpvChange::TYPE_MANUAL_ADJUSTMENT,
                    'quota_before' => $before,
                    'quota_after' => $newQuota,
                    'reason' => $validated['reason'] ?? 'Input data lampau OFF SPV',
                    'changed_by' => auth()->id(),
                    'created_at' => now(),
                ]);
            }

            $activeOff = LeaveRequest::query()
                ->where('user_id', $user->id)
                ->where('type', LeaveType::OFF_SPV->value)
                ->whereBetween('start_date', [
                    $definition['start']->toDateString(),
                    $definition['end']->toDateString(),
                ])
                ->whereNotIn('status', [LeaveRequest::STATUS_REJECTED, LeaveRequest::STATUS_CANCELLED]);

            (clone $activeOff)->update(['off_spv_period_id' => $period->id]);

            $historicalOff = LeaveRequest::query()
                ->where('user_id', $user->id)
                ->where('type', LeaveType::OFF_SPV->value)
                ->whereBetween('start_date', [
                    $definition['start']->toDateString(),
                    $definition['end']->toDateString(),
                ])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->filter(fn (LeaveRequest $leave) => in_array(
                    $leave->start_date->toDateString(),
                    $definition['saturdays'],
                    true,
                ))
                ->keyBy(fn (LeaveRequest $leave) => $leave->start_date->toDateString());

            foreach ($historicalOff as $date => $leave) {
                if (in_array($date, $selectedDates, true)) {
                    continue;
                }

                if (! in_array($leave->status, [LeaveRequest::STATUS_REJECTED, LeaveRequest::STATUS_CANCELLED], true)) {
                    $leave->update([
                        'off_spv_period_id' => $period->id,
                        'status' => LeaveRequest::STATUS_CANCELLED,
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);
                }
            }

            foreach ($selectedDates as $date) {
                if ($existing = $historicalOff->get($date)) {
                    $existing->update([
                        'off_spv_period_id' => $period->id,
                        'start_date' => $date,
                        'end_date' => $date,
                        'start_time' => null,
                        'end_time' => null,
                        'status' => LeaveRequest::STATUS_APPROVED,
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'supervisor_ack_at' => now(),
                    ]);

                    continue;
                }

                LeaveRequest::create([
                    'user_id' => $user->id,
                    'off_spv_period_id' => $period->id,
                    'type' => LeaveType::OFF_SPV->value,
                    'start_date' => $date,
                    'end_date' => $date,
                    'reason' => $validated['reason'] ?? 'Input data lampau OFF SPV',
                    'status' => LeaveRequest::STATUS_APPROVED,
                    'notes_hrd' => 'Input data lampau oleh '.auth()->user()->name,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'supervisor_ack_at' => now(),
                ]);
            }
        });

        return redirect()
            ->route('hr.supervisors.show', ['user' => $user, 'year' => 2026])
            ->with('success', 'Data lampau OFF SPV berhasil disimpan.');
    }

    public function create()
    {
        $candidates = User::with(['division', 'position'])
            ->where('role', UserRole::EMPLOYEE)
            ->orderBy('name')
            ->get();

        return view('hr.supervisors.create', compact('candidates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:SUPERVISOR,MANAGER',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->update(['role' => $request->role]);

        if ($user->isSupervisor() && $user->isActive() && $this->offSpvQuotaService->rolloutStarted()) {
            $this->offSpvQuotaService->initializeYearPeriods(supervisor: $user);
        }

        // FIX: Route jadi plural
        return redirect()->route('hr.supervisors.index')
            ->with('success', "{$user->name} berhasil diangkat menjadi {$request->role}.");
    }

    public function edit(User $user)
    {
        if (! in_array($user->role, [UserRole::SUPERVISOR, UserRole::MANAGER])) {
            return back()->with('error', 'User ini bukan Supervisor/Manager.');
        }

        return view('hr.supervisors.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:SUPERVISOR,MANAGER']);
        $user->update(['role' => $request->role]);

        if ($user->isSupervisor() && $user->isActive() && $this->offSpvQuotaService->rolloutStarted()) {
            $this->offSpvQuotaService->initializeYearPeriods(supervisor: $user);
        }

        // FIX: Route jadi plural
        return redirect()->route('hr.supervisors.index')
            ->with('success', "Jabatan {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menurunkan jabatan diri sendiri.');
        }
        $user->update(['role' => UserRole::EMPLOYEE]);

        return back()->with('success', "{$user->name} telah dikembalikan menjadi Staff biasa.");
    }
}
