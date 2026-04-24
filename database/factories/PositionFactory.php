<?php

namespace Database\Factories;

use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionFactory extends Factory
{
    protected $model = Position::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Staff',
                'Senior Staff',
                'Supervisor',
                'Manager',
                'Senior Manager',
                'Director',
                'Manager HRD',
            ]),
            'description' => $this->faker->sentence(),
        ];
    }

    public function managerHrd(): static
    {
        return $this->state(fn() => ['name' => 'Manager HRD']);
    }
}
