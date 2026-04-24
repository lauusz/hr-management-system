<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
       return [
            'name'        => $this->faker->name(),
            'username'    => $this->faker->unique()->userName(),
            'email'       => $this->faker->unique()->safeEmail(),
            'password'    => static::$password ??= Hash::make('password'),
            'phone'       => '08' . $this->faker->numerify('##########'),
            'role'        => UserRole::EMPLOYEE,
            'division_id' => null,
            'position_id' => null,
            'status'      => 'ACTIVE',
            'leave_balance' => 12,
            'last_login_at' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function hrd(): static
    {
        return $this->state(fn() => [
            'role' => UserRole::HRD,
        ]);
    }

    public function hrStaff(): static
    {
        return $this->state(fn() => [
            'role' => UserRole::HR_STAFF,
        ]);
    }

    public function manager(): static
    {
        return $this->state(fn() => [
            'role' => UserRole::MANAGER,
        ]);
    }

    public function supervisor(): static
    {
        return $this->state(fn() => [
            'role' => UserRole::SUPERVISOR,
        ]);
    }

    public function employee(): static
    {
        return $this->state(fn() => [
            'role' => UserRole::EMPLOYEE,
        ]);
    }
}
