<?php

namespace App\Enums;

enum LeaveType: string
{
    case IZIN_TELAT          = 'IZIN_TELAT';
    case IZIN_PULANG_AWAL    = 'IZIN_PULANG_AWAL';
    case IZIN_TENGAH_KERJA   = 'IZIN_TENGAH_KERJA';
    case CUTI                = 'CUTI';
    case SAKIT               = 'SAKIT';
    case IZIN                = 'IZIN';
    case CUTI_KHUSUS         = 'CUTI_KHUSUS';
    case DINAS_LUAR          = 'DINAS_LUAR';
    case OFF_SPV             = 'OFF_SPV';

    public function label(): string
    {
        return match ($this) {
            self::IZIN_TELAT          => 'Izin Telat',
            self::IZIN_PULANG_AWAL    => 'Izin Pulang Awal',
            self::IZIN_TENGAH_KERJA   => 'Izin Tengah Kerja',
            self::CUTI                => 'Cuti',
            self::SAKIT               => 'Sakit',
            self::IZIN                => 'Izin',
            self::CUTI_KHUSUS         => 'Cuti Khusus (menikah, melahirkan/istri melahirkan/keguguran, mengkhitankan/membaptiskan anak, keluarga meninggal — suami/istri/mertua/orang tua/anak/menantu, anggota keluarga serumah meninggal dunia)',
            self::DINAS_LUAR          => 'Dinas Luar',
            self::OFF_SPV             => 'Off SPV',
        };
    }

    public static function values(): array
    {
        return array_map(fn(self $c) => $c->value, self::cases());
    }
}
