<?php

namespace Database\Factories;

use App\Models\Providers;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Providers>
 */
class ProvidersFactory extends Factory
{
    protected $model = Providers::class;
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 10),
            'address_id' => $this->faker->numberBetween(1, 10),
            'rank' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
            'balance' => $this->faker->randomFloat(2, 100, 1000),
            'rating' => $this->faker->numberBetween(1, 5),
            'start_date' => $this->faker->dateTimeBetween('-1 years', 'now'),
            'resign_date' => $this->faker->dateTimeBetween('-1 years', 'now'),
            'salary' => $this->faker->randomFloat(2, 100, 1000),
            'commission_type' => $this->faker->numberBetween(1, 2), // Change to generate 1 or 2
            'commission_amount' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->randomElement([0, 1]), // Change to separate 0 and 1
            'created_at' => $this->faker->dateTimeBetween('-1 years', 'now'),
        ];
    }
}
