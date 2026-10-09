<?php

namespace App\Services\Attendance;

use App\Models\AttendanceLocation;
use Illuminate\Support\Collection;

class AllowedAttendanceLocationResolver
{
    /**
     * @param  Collection<int, AttendanceLocation>  $locations
     * @return array{location: AttendanceLocation, distance: int}|null
     */
    public function resolve(Collection $locations, float $latitude, float $longitude): ?array
    {
        $match = null;

        foreach ($locations as $location) {
            $distance = $this->distanceFor($location, $latitude, $longitude);

            if ($distance > $location->radius_meters) {
                continue;
            }

            if ($match === null || $distance < $match['distance']) {
                $match = ['location' => $location, 'distance' => $distance];
            }
        }

        return $match;
    }

    /**
     * @param  Collection<int, AttendanceLocation>  $locations
     * @return array{location: AttendanceLocation, distance: int}|null
     */
    public function nearest(Collection $locations, float $latitude, float $longitude): ?array
    {
        $nearest = null;

        foreach ($locations as $location) {
            $distance = $this->distanceFor($location, $latitude, $longitude);

            if ($nearest === null || $distance < $nearest['distance']) {
                $nearest = ['location' => $location, 'distance' => $distance];
            }
        }

        return $nearest;
    }

    private function distanceFor(AttendanceLocation $location, float $latitude, float $longitude): int
    {
        return (int) round($this->calculateDistance(
            $latitude,
            $longitude,
            (float) $location->latitude,
            (float) $location->longitude,
        ));
    }

    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($lng1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($lng2);
        $latDelta = $lat2 - $lat1;
        $lngDelta = $lng2 - $lng1;
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($lngDelta / 2), 2)));

        return $earthRadius * $angle;
    }
}
