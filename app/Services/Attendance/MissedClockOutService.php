<?php

namespace App\Services\Attendance;

use App\Models\Attendance;

class MissedClockOutService
{
    public function flagPreviousOpenAttendances(?int $userId = null): int
    {
        $now = now('Asia/Jakarta');

        if ($now->hour < 12) {
            return 0;
        }

        return Attendance::query()
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->whereDate('date', '<', $now->toDateString())
            ->whereNotNull('clock_in_at')
            ->whereNull('clock_out_at')
            ->where('completion_status', Attendance::COMPLETION_OPEN)
            ->update(['completion_status' => Attendance::COMPLETION_MISSED_CLOCK_OUT]);
    }
}
