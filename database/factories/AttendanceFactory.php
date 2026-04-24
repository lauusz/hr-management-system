<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'date'      => now()->toDateString(),
            'shift_id'  => Shift::factory(),
            'status'    => 'HADIR',
            'type'      => 'WFO',
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn() => ['user_id' => $user->id]);
    }

    public function today(): static
    {
        return $this->state(fn() => ['date' => now()->toDateString()]);
    }

    public function clockedIn(): static
    {
        return $this->state(fn() => ['clock_in_at' => now()]);
    }

    public function clockedOut(): static
    {
        return $this->state(fn() => [
            'clock_in_at'  => now()->subHours(8),
            'clock_out_at' => now(),
        ]);
    }

    public function wfo(): static
    {
        return $this->state(fn() => ['type' => 'WFO', 'approval_status' => 'APPROVED']);
    }

    public function dinasLuar(): static
    {
        return $this->state(fn() => ['type' => 'DINAS_LUAR', 'approval_status' => 'PENDING']);
    }
}
