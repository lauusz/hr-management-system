<?php

namespace App\Services;

use App\Models\EmployeeShift;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ShiftRotationService
{
    public function ensureNotRotating(array $userIds): void
    {
        if (Schema::hasTable('shift_rotation_members') && DB::table('shift_rotation_members')->whereIn('user_id', $userIds)->exists()) {
            throw ValidationException::withMessages(['shift_id' => 'Karyawan mengikuti rotasi. Hentikan rotasi terlebih dahulu untuk mengubah shift secara manual.']);
        }
    }

    public function ensureShiftNotInUse(int $shiftId, bool $includeHistory = false): void
    {
        $usedByRotation = Schema::hasTable('shift_rotations') && DB::table('shift_rotations')->where('is_active', true)->pluck('shift_ids')
            ->contains(fn ($ids) => in_array($shiftId, array_map('intval', json_decode($ids, true) ?: []), true));
        $usedByHistory = $includeHistory && Schema::hasTable('shift_rotation_snapshots') && DB::table('shift_rotation_snapshots')->where('shift_id', $shiftId)->exists();
        if ($usedByRotation || $usedByHistory) {
            throw ValidationException::withMessages(['shift_id' => 'Shift digunakan oleh rotasi atau riwayat rotasi dan tidak dapat diubah untuk tindakan ini.']);
        }
    }

    public function process(Carbon $date): void
    {
        $ids = DB::table('shift_rotations')->where('is_active', true)->whereDate('start_date', '<=', $date->toDateString())->pluck('id');

        foreach ($ids as $id) {
            try {
                DB::transaction(function () use ($id, $date) {
                    $rotation = DB::table('shift_rotations')->where('id', $id)->lockForUpdate()->first();
                    if (! $rotation || ! $rotation->is_active || $rotation->start_date > $date->toDateString()) {
                        return;
                    }

                    $shiftIds = json_decode($rotation->shift_ids, true, 512, JSON_THROW_ON_ERROR);
                    if (count($shiftIds) < 2) {
                        throw new RuntimeException('Urutan shift rotasi tidak lengkap.');
                    }

                    $shifts = Shift::with('days')->whereIn('id', $shiftIds)->lockForUpdate()->get()->keyBy('id');
                    if ($shifts->count() !== count($shiftIds) || $shifts->pluck('is_wita')->map(fn ($value) => (bool) $value)->unique()->count() !== 1) {
                        throw new RuntimeException('Shift rotasi harus tersedia dan menggunakan zona waktu yang sama.');
                    }
                    if ($shifts->contains(fn ($shift) => ! $shift->is_active)) {
                        throw new RuntimeException('Shift rotasi tidak aktif. Periksa pengaturan shift.');
                    }
                    $period = $this->period($rotation, $date);
                    $members = DB::table('shift_rotation_members')->where('shift_rotation_id', $id)->get();
                    $completed = DB::table('shift_rotation_snapshots')->where('shift_rotation_id', $id)->whereDate('work_date', $date->toDateString())->pluck('user_id')->map(fn ($value) => (int) $value)->all();
                    foreach ($members as $member) {
                        if (in_array((int) $member->user_id, $completed, true)) {
                            continue;
                        }

                        $offset = array_search((int) $member->start_shift_id, array_map('intval', $shiftIds), true);
                        if ($offset === false) {
                            throw new RuntimeException('Shift awal karyawan tidak ada pada urutan rotasi.');
                        }

                        $shiftId = $shiftIds[($offset + $period) % count($shiftIds)];
                        $shift = $shifts->get($shiftId);
                        $schedule = EmployeeShift::where('user_id', $member->user_id)->lockForUpdate()->first();
                        if (! $schedule) {
                            throw new RuntimeException('Jadwal karyawan rotasi tidak ditemukan.');
                        }

                        $day = $shift->days->first(fn ($item) => (int) $item->day_of_week === $date->dayOfWeekIso);
                        $schedule->update(['shift_id' => $shift->id]);
                        DB::table('shift_rotation_snapshots')->insert([
                            'shift_rotation_id' => $id,
                            'user_id' => $member->user_id,
                            'work_date' => $date->toDateString(),
                            'shift_id' => $shift->id,
                            'shift_name' => $shift->name,
                            'start_time' => $day?->start_time,
                            'end_time' => $day?->end_time,
                            'is_holiday' => $day?->is_holiday ?? true,
                            'is_overnight' => $day?->is_overnight ?? false,
                            'is_wita' => $shift->is_wita ?? false,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                });
            } catch (Throwable $error) {
                report($error);
            }
        }
    }

    private function period(object $rotation, Carbon $date): int
    {
        $start = Carbon::parse($rotation->start_date, $date->timezoneName)->startOfDay();

        if ($rotation->frequency === 'monthly') {
            return ((int) $date->year - (int) $start->year) * 12 + (int) $date->month - (int) $start->month;
        }

        if ($rotation->frequency === 'days') {
            return intdiv((int) $start->diffInDays($date), (int) $rotation->interval_days);
        }

        $next = $start->copy()->addDays(((int) $rotation->weekly_day - $start->dayOfWeekIso + 7) % 7 ?: 7);

        return $date->lt($next) ? 0 : 1 + intdiv((int) $next->diffInDays($date), 7);
    }
}
