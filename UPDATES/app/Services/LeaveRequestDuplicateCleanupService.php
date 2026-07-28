<?php

namespace App\Services;

use App\Enums\LeaveType;
use App\Models\LeaveRequest;

/**
 * Service untuk membersihkan pengajuan cuti/izin duplikat yang masih pending
 * setelah sebuah pengajuan disetujui.
 *
 * Cleanup dijalankan sebagai afterUpdate callback dari state machine sehingga
 * berada di dalam transaction yang sama dengan penulisan status APPROVED.
 */
class LeaveRequestDuplicateCleanupService
{
    /**
     * Hapus pengajuan lain dari user yang sama dengan tipe dan rentang tanggal
     * yang overlap, yang masih berstatus pending.
     *
     * @return int Jumlah pengajuan duplikat yang dihapus.
     */
    public function deleteDuplicatePendingLeaveRequests(LeaveRequest $approvedLeave): int
    {
        if (! $approvedLeave->exists || ! $approvedLeave->id) {
            return 0;
        }

        $approvedType = $approvedLeave->type instanceof LeaveType
            ? $approvedLeave->type->value
            : (string) $approvedLeave->type;

        $duplicates = LeaveRequest::where('user_id', $approvedLeave->user_id)
            ->where('id', '!=', $approvedLeave->id)
            ->where('type', $approvedType)
            ->whereIn('status', [LeaveRequest::PENDING_HR, LeaveRequest::PENDING_SUPERVISOR])
            ->where(function ($query) use ($approvedLeave) {
                $query->whereBetween('start_date', [$approvedLeave->start_date, $approvedLeave->end_date])
                    ->orWhereBetween('end_date', [$approvedLeave->start_date, $approvedLeave->end_date])
                    ->orWhere(function ($q) use ($approvedLeave) {
                        $q->where('start_date', '<=', $approvedLeave->start_date)
                            ->where('end_date', '>=', $approvedLeave->end_date);
                    });
            })
            ->get();

        $deletedCount = 0;

        foreach ($duplicates as $duplicate) {
            if ($duplicate->delete()) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
