<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;
    public function definition(): array
    {
        return [
            'booking_id' => $this->faker->numberBetween(1, 10),
            'payment_method_id'=>   $this->faker->numberBetween(0, 3),
            'status' => $this->faker->numberBetween(0, 1),
            'type' => $this->faker->numberBetween(0, 1),
            'date' => $this->faker->dateTime(),
            'amount' => $this->faker->numberBetween(0, 100),
            'provider_id' => $this->faker->numberBetween(1, 10),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
