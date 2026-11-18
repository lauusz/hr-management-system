<?php

namespace App\Services;

use App\Models\AttendanceEvent;
use App\Models\EmployeeShift;
use App\Models\OvertimeRequest;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Aturan tunggal zona waktu lembur (PRD-024 / Q-003).
 * Jam mulai/selesai pengajuan adalah jam lokal sesuai zona lembur (is_wita);
 * waktu absensi tetap instant absolut dan hanya dikonversi saat dibandingkan/ditampilkan.
 */
class OvertimeTimezone
{
    public const WIB = 'Asia/Jakarta';

    public const WITA = 'Asia/Makassar';

    private static ?bool $columnReady = null;

    /** Kolom snapshot belum tentu sudah diterapkan (SQL manual); semua jalur wajib tahan terhadap ketiadaannya. */
    public static function columnReady(): bool
    {
        return self::$columnReady ??= Schema::hasColumn('overtime_requests', 'is_wita');
    }

    public static function zoneName(bool $isWita): string
    {
        return $isWita ? self::WITA : self::WIB;
    }

    public static function label(?bool $isWita): ?string
    {
        return $isWita === null ? null : ($isWita ? 'WITA' : 'WIB');
    }

    /** Label dari nama zona sebuah Carbon, untuk jam absensi. */
    public static function format(Carbon $time): string
    {
        return $time->format('H:i').' '.($time->timezoneName === self::WITA ? 'WITA' : 'WIB');
    }

    /**
     * Zona untuk pengajuan baru/diubah. Urutan: snapshot absensi pada tanggal kerja,
     * Jadwal Kerja aktif, lalu shift reguler. Kandidat berbeda zona ditolak, bukan dipilih diam-diam.
     */
    public static function resolveForNew(int $userId, string $date): bool
    {
        $evidence = Schema::hasColumns('attendance_events', ['work_date', 'is_wita'])
            ? self::eventEvidence(AttendanceEvent::query()->with('attendanceShift')->where('user_id', $userId)->where('work_date', $date)->get())
            : collect();
        if ($evidence->count() === 1) {
            return $evidence->first();
        }

        $candidates = $evidence;
        if ($candidates->isEmpty() && WorkSchedule::scheduleReadable()) {
            $candidates = WorkSchedule::active()->where('user_id', $userId)->whereDate('work_date', $date)
                ->pluck('is_wita')->map(fn ($v) => (bool) $v)->unique()->values();
        }

        if ($candidates->count() > 1) {
            throw ValidationException::withMessages([
                'overtime_date' => 'Zona waktu (WIB/WITA) pada tanggal ini tidak konsisten di data absensi atau jadwal. Hubungi HR.',
            ]);
        }

        if ($candidates->count() === 1) {
            return $candidates->first();
        }

        return (bool) EmployeeShift::with('shift')->where('user_id', $userId)->first()?->shift?->is_wita;
    }

    /** Zona tersimpan; pengajuan lama (null) ditafsirkan hanya dari bukti absensi, bukan dari shift saat ini. */
    public static function forRequest(OvertimeRequest $overtime): ?bool
    {
        if (self::columnReady() && $overtime->getAttribute('is_wita') !== null) {
            return (bool) $overtime->getAttribute('is_wita');
        }

        if (! Schema::hasColumns('attendance_events', ['work_date', 'is_wita'])) {
            return null;
        }

        // shortcut: satu query per pengajuan lama pada halaman daftar, optimalkan bila halaman terasa lambat.
        $date = $overtime->overtime_date->toDateString();
        $events = AttendanceEvent::query()->with('attendanceShift')
            ->where('user_id', $overtime->user_id)
            ->where(fn ($q) => $q->where('work_date', $date)->orWhere(fn ($q) => $q->whereNull('work_date')->whereDate('occurred_at', $date)))
            ->get();

        return self::singleEvidence($events);
    }

    /** Bukti zona satu event: snapshot is_wita, atau shift tercatat pada event; null bila tidak ada. */
    public static function eventZone(AttendanceEvent $event): ?bool
    {
        $zone = $event->is_wita ?? $event->attendanceShift?->is_wita;

        return $zone === null ? null : (bool) $zone;
    }

    public static function singleEvidence($events): ?bool
    {
        $evidence = self::eventEvidence($events);

        return $evidence->count() === 1 ? $evidence->first() : null;
    }

    private static function eventEvidence($events)
    {
        return collect($events)->map(fn ($e) => self::eventZone($e))->filter(fn ($z) => $z !== null)->unique()->values();
    }
}
