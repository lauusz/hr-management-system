<?php

namespace App\Services\Inventory;

/**
 * Satu-satunya definisi 18 posisi ban aktif (6 HEAD + 12 EKOR): dipakai untuk label,
 * gambar diagram, pilihan form, dan validasi penempatan baru. Serep tidak termasuk.
 * Posisi 19–22 (skema 22 posisi lama) tidak lagi aktif; ban lama yang masih tercatat di sana tetap terbaca dan dapat
 * dikembalikan ke gudang (has() hanya untuk penempatan baru, bukan untuk pengembalian).
 *
 * Koordinat SVG (viewBox diagram) dihitung dari parameter bersama di bawah agar simetris terhadap
 * garis tengah rangka: ban berpusat pada rel kiri/kanan, ban ganda berjarak tetap, dan semua ban
 * satu baris memiliki pusat Y yang sama. Area klik memakai koordinat yang sama dengan gambar.
 */
class PesonaTireLayout
{
    public const HEAD = 'HEAD';

    public const EKOR = 'EKOR';

    // Rel (tepi rangka) tempat ban berpusat; garis tengah rangka = (kiri + kanan) / 2.
    private const HEAD_RAIL = [208, 358];

    private const EKOR_RAIL = [600, 750];

    private const GAP = 2; // jarak antarban ganda

    private const HEAD_SINGLE = ['w' => 32, 'h' => 68, 'cy' => 156];

    private const HEAD_PAIR = ['w' => 22, 'h' => 66, 'rows' => [311]]; // 3-4/5-6

    private const EKOR_PAIR = ['w' => 29, 'h' => 58, 'rows' => [242, 326, 424]]; // 7-10, 11-14, 15-18

    private static ?array $slots = null;

    /** @return array<int, array{part:string,x:int,y:int,w:int,h:int,tone:string}> urut nomor posisi */
    public static function all(): array
    {
        if (self::$slots !== null) {
            return self::$slots;
        }
        $s = [];
        $single = function (int $n, string $part, int $rail, array $d) use (&$s) {
            $s[$n] = ['part' => $part, 'x' => $rail - intdiv($d['w'], 2), 'y' => $d['cy'] - intdiv($d['h'], 2), 'w' => $d['w'], 'h' => $d['h'], 'tone' => 'orange'];
        };
        // Pasangan berpusat pada rel: [kiri-luar, kiri-dalam] atau [kanan-dalam, kanan-luar].
        $pair = function (array $numbers, string $part, int $rail, int $cy, array $d, string $tone) use (&$s) {
            $start = $rail - intdiv(2 * $d['w'] + self::GAP, 2);
            foreach ($numbers as $i => $n) {
                $s[$n] = ['part' => $part, 'x' => $start + $i * ($d['w'] + self::GAP), 'y' => $cy - intdiv($d['h'], 2), 'w' => $d['w'], 'h' => $d['h'], 'tone' => $tone];
            }
        };

        [$hl, $hr] = self::HEAD_RAIL;
        $single(1, self::HEAD, $hl, self::HEAD_SINGLE);
        $single(2, self::HEAD, $hr, self::HEAD_SINGLE);
        [$r1] = self::HEAD_PAIR['rows'];
        $pair([3, 4], self::HEAD, $hl, $r1, self::HEAD_PAIR, 'orange');
        $pair([5, 6], self::HEAD, $hr, $r1, self::HEAD_PAIR, 'orange');

        [$el, $er] = self::EKOR_RAIL;
        foreach ([[7, 8, 9, 10], [11, 12, 13, 14], [15, 16, 17, 18]] as $row => [$a, $b, $c, $d]) {
            $cy = self::EKOR_PAIR['rows'][$row];
            $tone = $row === 2 ? 'cream' : 'orange';
            $pair([$a, $b], self::EKOR, $el, $cy, self::EKOR_PAIR, $tone);
            $pair([$c, $d], self::EKOR, $er, $cy, self::EKOR_PAIR, $tone);
        }
        ksort($s);

        return self::$slots = $s;
    }

    /**
     * Rangka, garis sumbu, dan titik acuan label; semuanya diturunkan dari geometri ban di all().
     *
     * @return array{head:array,ekor:array,axles:array<int,array{part:string,x1:int,x2:int,y:int}>,rails:array<int,array{x:int,y1:int,y2:int}>}
     */
    public static function guides(): array
    {
        $slots = self::all();
        [$hl, $hr] = self::HEAD_RAIL;
        [$el, $er] = self::EKOR_RAIL;

        // Satu garis sumbu per baris: dari tepi dalam ban kiri ke tepi dalam ban kanan, tepat di pusat Y ban.
        $rows = [];
        foreach ($slots as $slot) {
            $rows[$slot['part'].'|'.($slot['y'] * 2 + $slot['h'])][] = $slot;
        }
        $axles = [];
        foreach ($rows as $tires) {
            $mid = (min(array_column($tires, 'x')) + max(array_map(fn ($t) => $t['x'] + $t['w'], $tires))) / 2;
            $left = array_filter($tires, fn ($t) => $t['x'] + $t['w'] / 2 < $mid);
            $right = array_filter($tires, fn ($t) => $t['x'] + $t['w'] / 2 >= $mid);
            $axles[] = [
                'part' => $tires[0]['part'],
                'x1' => max(array_map(fn ($t) => $t['x'] + $t['w'], $left)),
                'x2' => min(array_column($right, 'x')),
                'y' => (int) ($tires[0]['y'] + $tires[0]['h'] / 2),
            ];
        }

        $ekorBodyBottom = 372;
        // Rangka HEAD berakhir tepat di bawah baris ban terakhir; garis belakang mengikuti.
        $headLast = max(array_map(fn ($t) => $t['y'] + $t['h'], array_filter($slots, fn ($t) => $t['part'] === self::HEAD)));
        $rearTop = min(array_column(array_filter($slots, fn ($t) => $t['part'] === self::EKOR && $t['tone'] === 'cream'), 'y'));

        return [
            'head' => [
                'cx' => ($hl + $hr) / 2,
                'body' => ['x' => $hl, 'y' => 90, 'w' => $hr - $hl, 'h' => $headLast + 23 - 90],
                'frame' => ['x' => 165, 'y' => 60, 'w' => 236, 'h' => 298],
                'bottomLine' => ['x1' => 150, 'x2' => 416, 'y' => $headLast + 52],
            ],
            'ekor' => [
                'cx' => ($el + $er) / 2,
                'body' => ['x' => $el, 'y' => 58, 'w' => $er - $el, 'h' => $ekorBodyBottom - 58],
                'sumbuLine' => ['x1' => 504, 'x2' => 852, 'y' => 385],
            ],
            'axles' => $axles,
            // Penghubung rangka ke sumbu belakang (di belakang ban).
            'rails' => [
                ['x' => $el, 'y1' => $ekorBodyBottom, 'y2' => $rearTop],
                ['x' => $er, 'y1' => $ekorBodyBottom, 'y2' => $rearTop],
            ],
        ];
    }

    /** @return int[] */
    public static function numbers(): array
    {
        return array_keys(self::all());
    }

    public static function has(int $number): bool
    {
        return isset(self::all()[$number]);
    }

    public static function part(int $number): ?string
    {
        return self::all()[$number]['part'] ?? null;
    }

    /** Contoh: "Head posisi 3". */
    public static function label(int $number): string
    {
        $part = self::part($number);

        return $part === null ? "Posisi {$number}" : ucfirst(strtolower($part))." posisi {$number}";
    }

    /** @return array<string, int[]> nomor posisi dikelompokkan per bagian, untuk pilihan form. */
    public static function byPart(): array
    {
        $groups = [self::HEAD => [], self::EKOR => []];
        foreach (self::numbers() as $number) {
            $groups[self::part($number)][] = $number;
        }

        return $groups;
    }
}
