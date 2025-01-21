<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProvidersAction>
 */
class ProvidersActionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_id' => $this->faker->numberBetween(1, 10),
            'provider_id' => $this->faker->numberBetween(1, 10),
            'action' => $this->faker->numberBetween(1, 4),
            'amount' => $this->faker->numberBetween(0, 100),
            'attachment' => $this->faker->word(),
            'notes' => $this->faker->word(),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
