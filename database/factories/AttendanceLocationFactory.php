<?php

namespace Database\Factories;

use App\Models\AttendanceLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceLocationFactory extends Factory
{
    protected $model = AttendanceLocation::class;

    public function definition(): array
    {
        return [
            'name'           => 'Kantor Pusat',
            'address'        => $this->faker->address(),
            'latitude'       => -6.200000,
            'longitude'      => 106.816666,
            'radius_meters'  => 100,
            'is_active'      => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn() => ['is_active' => false]);
    }

    public function withRadius(int $radius): static
    {
        return $this->state(fn() => ['radius_meters' => $radius]);
    }
}
