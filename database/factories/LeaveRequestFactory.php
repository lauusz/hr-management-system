<?php

namespace Database\Factories;

use App\Enums\LeaveType;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'type'       => LeaveType::IZIN,
            'start_date' => fn() => now()->addDays(1)->toDateString(),
            'end_date'   => fn() => now()->addDays(1)->toDateString(),
            'reason'     => fn() => $this->faker->sentence(),
            'status'     => LeaveRequest::PENDING_SUPERVISOR,
        ];
    }

    public function cuti(): static
    {
        return $this->state(fn() => ['type' => LeaveType::CUTI]);
    }

    public function sakit(): static
    {
        return $this->state(fn() => ['type' => LeaveType::SAKIT]);
    }

    public function izin(): static
    {
        return $this->state(fn() => ['type' => LeaveType::IZIN]);
    }

    public function pendingSupervisor(): static
    {
        return $this->state(fn() => ['status' => LeaveRequest::PENDING_SUPERVISOR]);
    }

    public function pendingHr(): static
    {
        return $this->state(fn() => ['status' => LeaveRequest::PENDING_HR]);
    }

    public function approved(): static
    {
        return $this->state(fn() => ['status' => LeaveRequest::STATUS_APPROVED]);
    }

    public function rejected(): static
    {
        return $this->state(fn() => ['status' => LeaveRequest::STATUS_REJECTED]);
    }

    public function batal(): static
    {
        return $this->state(fn() => ['status' => 'BATAL']);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn() => ['user_id' => $user->id]);
    }

    public function offSpv(): static
    {
        return $this->state(fn() => ['type' => LeaveType::OFF_SPV]);
    }
}
