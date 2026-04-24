<?php

namespace Database\Factories;

use App\Models\LoanRepayment;
use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanRepayment>
 */
class LoanRepaymentFactory extends Factory
{
    protected $model = LoanRepayment::class;

    public function definition(): array
    {
        return [
            'loan_request_id' => LoanRequest::factory(),
            'user_id' => User::factory(),
            'paid_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'amount' => $this->faker->numberBetween(100000, 5000000),
            'method' => $this->faker->randomElement(['TUNAI', 'TRANSFER', 'POTONG_GAJI']),
            'note' => $this->faker->optional()->paragraph(),
        ];
    }
}