<?php

namespace App\Services;

use App\Models\AttendanceLocation;
use App\Models\EmployeeShift;
use App\Models\EmployeeShiftChange;
use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class OperationalScheduleService
{
    public function applyNow(
        array $userIds,
        Shift $shift,
        AttendanceLocation $location,
        User $actor
    ): void {
        DB::transaction(function () use ($userIds, $shift, $location, $actor) {
            foreach ($userIds as $userId) {
                $user = $this->lockOpsMember($userId);

                EmployeeShift::updateOrCreate(
                    ['user_id' => $user->id],
                    ['shift_id' => $shift->id, 'location_id' => $location->id]
                );

                EmployeeShiftChange::create([
                    'user_id' => $user->id,
                    'shift_id' => $shift->id,
                    'location_id' => $location->id,
                    'effective_date' => now()->toDateString(),
                    'status' => EmployeeShiftChange::STATUS_APPLIED,
                    'pending_slot' => null,
                    'created_by' => $actor->id,
                    'shift_name_snapshot' => $shift->name,
                    'location_name_snapshot' => $location->name,
                    'applied_at' => now(),
                ]);
            }
        });
    }

    public function scheduleNextMonth(
        array $userIds,
        Shift $shift,
        AttendanceLocation $location,
        User $actor,
        CarbonInterface $now
    ): void {
        DB::transaction(function () use ($userIds, $shift, $location, $actor, $now) {
            foreach ($userIds as $userId) {
                $user = $this->lockOpsMember($userId);
                $pending = EmployeeShiftChange::query()
                    ->where('user_id', $user->id)
                    ->where('status', EmployeeShiftChange::STATUS_PENDING)
                    ->lockForUpdate()
                    ->first();

                if ($pending) {
                    $pending->update([
                        'status' => EmployeeShiftChange::STATUS_CANCELLED,
                        'pending_slot' => null,
                        'cancelled_at' => now(),
                    ]);
                }

                EmployeeShiftChange::create([
                    'user_id' => $user->id,
                    'shift_id' => $shift->id,
                    'location_id' => $location->id,
                    'effective_date' => $now->copy()->addMonthNoOverflow()->startOfMonth(),
                    'status' => EmployeeShiftChange::STATUS_PENDING,
                    'pending_slot' => 1,
                    'created_by' => $actor->id,
                    'shift_name_snapshot' => $shift->name,
                    'location_name_snapshot' => $location->name,
                ]);
            }
        });
    }

    public function applyDueForUser(
        User $user,
        CarbonInterface $date
    ): ?EmployeeShift {
        return DB::transaction(function () use ($user, $date) {
            $change = EmployeeShiftChange::query()
                ->where('user_id', $user->id)
                ->where('status', EmployeeShiftChange::STATUS_PENDING)
                ->whereDate('effective_date', '<=', $date->toDateString())
                ->lockForUpdate()
                ->first();

            if (! $change) {
                return EmployeeShift::query()
                    ->with(['shift', 'location'])
                    ->where('user_id', $user->id)
                    ->first();
            }

            $assignment = EmployeeShift::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'shift_id' => $change->shift_id,
                    'location_id' => $change->location_id,
                ]
            );

            $change->update([
                'status' => EmployeeShiftChange::STATUS_APPLIED,
                'pending_slot' => null,
                'applied_at' => now(),
            ]);

            return $assignment->load(['shift', 'location']);
        });
    }

    public function applyAllDue(CarbonInterface $date): int
    {
        $userIds = EmployeeShiftChange::query()
            ->where('status', EmployeeShiftChange::STATUS_PENDING)
            ->whereDate('effective_date', '<=', $date->toDateString())
            ->distinct()
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            $user = User::find($userId);

            if ($user) {
                $this->applyDueForUser($user, $date);
            }
        }

        return $userIds->count();
    }

    private function lockOpsMember(int $userId): User
    {
        return User::query()
            ->whereKey($userId)
            ->where('is_ops_schedule_member', true)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
