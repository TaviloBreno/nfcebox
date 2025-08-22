<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CompanyConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'number' => null,
            'total' => $this->faker->randomFloat(2, 10, 1000),
            'payment_method' => $this->faker->randomElement(['money', 'card', 'pix']),
            'status' => 'draft',
            'nfce_key' => null,
            'protocol' => null,
            'authorized_at' => null,
            'error_message' => null,
        ];
    }

    /**
     * Indicate that the sale is authorized and pending.
     */
    public function authorizedPending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'authorized_pending',
        ]);
    }

    /**
     * Indicate that the sale is authorized.
     */
    public function authorized(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'authorized',
            'number' => $this->faker->numberBetween(1, 999999),
            'access_key' => $this->faker->numerify('####################'),
            'protocol' => $this->faker->numerify('###############'),
            'authorized_at' => now(),
        ]);
    }

    /**
     * Indicate that the sale has an error.
     */
    public function withError(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'error',
            'error_message' => $this->faker->sentence(),
        ]);
    }
}