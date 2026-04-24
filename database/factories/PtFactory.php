<?php

namespace Database\Factories;

use App\Models\Pt;
use Illuminate\Database\Eloquent\Factories\Factory;

class PtFactory extends Factory
{
    protected $model = Pt::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
        ];
    }
}
