<?php

use App\Models\AttendanceLocation;
use App\Services\Attendance\AllowedAttendanceLocationResolver;
use Illuminate\Support\Collection;

it('memilih lokasi terdekat dari lokasi presensi yang diizinkan', function () {
    $firstLocation = new AttendanceLocation([
        'name' => 'Kantor Pusat',
        'latitude' => -6.200000,
        'longitude' => 106.816666,
        'radius_meters' => 100,
    ]);
    $secondLocation = new AttendanceLocation([
        'name' => 'Gudang',
        'latitude' => -6.210000,
        'longitude' => 106.816666,
        'radius_meters' => 100,
    ]);

    $result = app(AllowedAttendanceLocationResolver::class)->resolve(
        new Collection([$firstLocation, $secondLocation]),
        -6.210000,
        106.816666,
    );

    expect($result['location'])->toBe($secondLocation)
        ->and($result['distance'])->toBe(0);
});

it('menolak koordinat di luar semua lokasi presensi yang diizinkan', function () {
    $location = new AttendanceLocation([
        'name' => 'Kantor Pusat',
        'latitude' => -6.200000,
        'longitude' => 106.816666,
        'radius_meters' => 100,
    ]);

    $result = app(AllowedAttendanceLocationResolver::class)->resolve(
        new Collection([$location]),
        -6.250000,
        106.816666,
    );

    expect($result)->toBeNull();
});

it('mengembalikan jarak lokasi terdekat saat seluruh lokasi di luar radius', function () {
    $location = new AttendanceLocation([
        'name' => 'Kantor Pusat',
        'latitude' => -6.200000,
        'longitude' => 106.816666,
        'radius_meters' => 100,
    ]);

    $nearest = app(AllowedAttendanceLocationResolver::class)->nearest(
        new Collection([$location]),
        -6.205000,
        106.816666,
    );

    expect($nearest['distance'])->toBe(556);
});

it('menerima lokasi lain yang berada dalam radius meski bukan titik terdekat', function () {
    $nearbyLocation = new AttendanceLocation([
        'name' => 'Pos Kecil',
        'latitude' => -6.200500,
        'longitude' => 106.816666,
        'radius_meters' => 10,
    ]);
    $allowedLocation = new AttendanceLocation([
        'name' => 'Gudang',
        'latitude' => -6.201000,
        'longitude' => 106.816666,
        'radius_meters' => 200,
    ]);

    $result = app(AllowedAttendanceLocationResolver::class)->resolve(
        new Collection([$nearbyLocation, $allowedLocation]),
        -6.200000,
        106.816666,
    );

    expect($result['location'])->toBe($allowedLocation);
});
