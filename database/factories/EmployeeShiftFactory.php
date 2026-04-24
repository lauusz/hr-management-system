<?php

namespace Database\Factories;

use App\Models\AttendanceLocation;
use App\Models\EmployeeShift;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeShiftFactory extends Factory
{
    protected $model = EmployeeShift::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'shift_id'    => Shift::factory(),
            'location_id' => AttendanceLocation::factory(),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn() => ['user_id' => $user->id]);
    }

    public function forShift(Shift $shift): static
    {
        return $this->state(fn() => ['shift_id' => $shift->id]);
    }

    public function atLocation(AttendanceLocation $location): static
    {
        return $this->state(fn() => ['location_id' => $location->id]);
    }
}
