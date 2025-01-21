<?php

namespace Database\Factories;

use App\Models\Customers;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customers>
 */
class CustomersFactory extends Factory
{
    protected $model = Customers::class;
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(0, 10),
            'rating' => $this->faker->numberBetween(1, 5),
            'status' => $this->faker->numberBetween(0, 1),
            'wallet_balance' => $this->faker->numberBetween(0,1000),
            'created_at' => $this->faker->dateTimeBetween('-1 years', now()),
            'updated_at' => $this->faker->dateTimeBetween('-1 years', now())
        ];

    }
}
