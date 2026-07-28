<?php

namespace App\Helpers;

class TerbilangHelper
{
    public static function convert($x)
    {
        $x = abs((int) $x);
        $abil = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
        if ($x < 12)
            return " " . $abil[$x];
        elseif ($x < 20)
            return self::convert($x - 10) . " Belas";
        elseif ($x < 100)
            return self::convert($x / 10) . " Puluh" . self::convert($x % 10);
        elseif ($x < 200)
            return " Seratus" . self::convert($x - 100);
        elseif ($x < 1000)
            return self::convert($x / 100) . " Ratus" . self::convert($x % 100);
        elseif ($x < 2000)
            return " Seribu" . self::convert($x - 1000);
        elseif ($x < 1000000)
            return self::convert($x / 1000) . " Ribu" . self::convert($x % 1000);
        elseif ($x < 1000000000)
            return self::convert($x / 1000000) . " Juta" . self::convert($x % 1000000);
        elseif ($x < 1000000000000)
            return self::convert($x / 1000000000) . " Milyar" . self::convert($x % 1000000000);
    }
}
