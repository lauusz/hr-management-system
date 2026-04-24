<?php

namespace Database\Factories;

use App\Models\Shift;
use App\Models\ShiftDay;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftDayFactory extends Factory
{
    protected $model = ShiftDay::class;

    public function definition(): array
    {
        return [
            'shift_id'    => Shift::factory(),
            'day_of_week' => 1, // Monday
            'start_time'  => '08:00:00',
            'end_time'    => '17:00:00',
            'is_holiday'  => false,
        ];
    }

    public function monday(): static
    {
        return $this->state(fn() => ['day_of_week' => 1, 'is_holiday' => false]);
    }

    public function tuesday(): static
    {
        return $this->state(fn() => ['day_of_week' => 2, 'is_holiday' => false]);
    }

    public function wednesday(): static
    {
        return $this->state(fn() => ['day_of_week' => 3, 'is_holiday' => false]);
    }

    public function thursday(): static
    {
        return $this->state(fn() => ['day_of_week' => 4, 'is_holiday' => false]);
    }

    public function friday(): static
    {
        return $this->state(fn() => ['day_of_week' => 5, 'is_holiday' => false]);
    }

    public function saturday(): static
    {
        return $this->state(fn() => ['day_of_week' => 6, 'is_holiday' => false]);
    }

    public function sunday(): static
    {
        return $this->state(fn() => ['day_of_week' => 7, 'is_holiday' => true]);
    }

    public function holiday(): static
    {
        return $this->state(fn() => ['is_holiday' => true]);
    }

    public function forShift(Shift $shift): static
    {
        return $this->state(fn() => ['shift_id' => $shift->id]);
    }
}
