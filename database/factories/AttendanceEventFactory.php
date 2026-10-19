<?php

namespace Database\Factories;

use App\Models\AttendanceEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceEventFactory extends Factory
{
    protected $model = AttendanceEvent::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'event_type' => AttendanceEvent::CLOCK_IN,
            'type' => 'WFO',
            'occurred_at' => now(),
            'approval_status' => 'PENDING',
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }

    public function clockOut(): static
    {
        return $this->state(fn () => ['event_type' => AttendanceEvent::CLOCK_OUT]);
    }
}
