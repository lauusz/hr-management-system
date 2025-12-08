<?php

namespace App\Models\Concerns;

use Carbon\Carbon;

trait HasMasaKerja
{
    public function getMasaKerjaAttribute(): ?string
    {
        if (! $this->tgl_bergabung) {
            return null;
        }

        $start = Carbon::parse($this->tgl_bergabung)->startOfDay();
        $end = Carbon::today();

        if ($end->lessThan($start)) {
            return null;
        }

        $years = $start->diffInYears($end);
        $afterYears = $start->copy()->addYears($years);
        $months = $afterYears->diffInMonths($end);

        if ($years > 0 && $months > 0) {
            return $years.' Tahun '.$months.' Bulan';
        }

        if ($years > 0) {
            return $years.' Tahun';
        }

        if ($months > 0) {
            return $months.' Bulan';
        }

        return '0 Bulan';
    }

    public function getMasaKerjaInYearsAttribute(): ?int
    {
        if (! $this->tgl_bergabung) {
            return null;
        }

        $start = Carbon::parse($this->tgl_bergabung)->startOfDay();
        $end = Carbon::today();

        if ($end->lessThan($start)) {
            return null;
        }

        return $start->diffInYears($end);
    }

    public function getMasaKerjaInMonthsAttribute(): ?int
    {
        if (! $this->tgl_bergabung) {
            return null;
        }

        $start = Carbon::parse($this->tgl_bergabung)->startOfDay();
        $end = Carbon::today();

        if ($end->lessThan($start)) {
            return null;
        }

        return $start->diffInMonths($end);
    }
}
