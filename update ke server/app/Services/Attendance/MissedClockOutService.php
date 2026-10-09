<?php

namespace App\Services\Attendance;

use App\Models\Attendance;

class MissedClockOutService
{
    public function flagPreviousOpenAttendances(?int $userId = null): int
    {
        $now = now('Asia/Jakarta');

        $attendances = Attendance::query()
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->whereDate('date', '<', $now->toDateString())
            ->whereNotNull('clock_in_at')
            ->whereNull('clock_out_at')
            ->where('completion_status', Attendance::COMPLETION_OPEN)
            ->get();

        $attendanceIds = $attendances
            ->filter(fn (Attendance $attendance) => $now->hour >= 12 || ! $attendance->is_overnight)
            ->modelKeys();

        if ($attendanceIds === []) {
            return 0;
        }

        return Attendance::query()
            ->whereKey($attendanceIds)
            ->update(['completion_status' => Attendance::COMPLETION_MISSED_CLOCK_OUT]);
    }
}
