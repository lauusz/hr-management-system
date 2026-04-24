<?php

namespace Database\Factories;

use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanRequest>
 */
class LoanRequestFactory extends Factory
{
    protected $model = LoanRequest::class;

    public function definition(): array
    {
        $amount = $this->faker->numberBetween(500000, 50000000);
        $installment = $this->faker->numberBetween(100000, $amount);

        return [
            'user_id' => User::factory(),
            'snapshot_name' => $this->faker->name(),
            'snapshot_nik' => $this->faker->optional()->numerify('########'),
            'snapshot_position' => $this->faker->optional()->jobTitle(),
            'snapshot_division' => $this->faker->optional()->word(),
            'snapshot_company' => $this->faker->optional()->company(),
            'submitted_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'document_path' => null,
            'amount' => $amount,
            'purpose' => $this->faker->optional()->sentence(),
            'notes' => $this->faker->optional()->paragraph(),
            'monthly_installment' => $installment,
            'repayment_term' => (int) ceil($amount / $installment),
            'disbursement_date' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'payment_method' => $this->faker->randomElement(['TUNAI', 'CICILAN', 'POTONG_GAJI']),
            'status' => 'PENDING_HRD',
            'hrd_id' => null,
            'hrd_decided_at' => null,
            'hrd_note' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn() => [
            'status' => 'APPROVED',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn() => [
            'status' => 'REJECTED',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn() => [
            'status' => 'PENDING_HRD',
        ]);
    }

    public function lunas(): static
    {
        return $this->state(fn() => [
            'status' => 'LUNAS',
        ]);
    }
}