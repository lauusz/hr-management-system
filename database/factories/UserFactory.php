<?php

namespace Database\Factories;

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
            'password'    => 'password',
            'phone'       => '08' . $this->faker->numerify('##########'), 
            'role'        => $this->faker->randomElement(['HRD', 'HEAD', 'STAFF']),
            'division_id' => null,                     
            'status'      => $this->faker->randomElement(['ACTIVE', 'INACTIVE']),
            'last_login_at' => null,
            'remember_token' => str()->random(10),
        ];
    }

    public function hrd(): self{
        return $this->state(fn() => [
            'role' => 'HRD',
        ]);
    }

    public function head(): self{
        return $this->state(fn() => [
            'role' => 'HEAD',
        ]);
    }
}
