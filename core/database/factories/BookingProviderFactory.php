<?php

namespace Database\Factories;

use App\Models\BookingProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookingProvider>
 */
class BookingProviderFactory extends Factory
{
    protected $model = BookingProvider::class;
    public function definition(): array
    {
        return [
            'booking_id' => $this->faker->numberBetween(1, 10),
            'provider_id' => $this->faker->numberBetween(1, 10),
            'commission_type' => $this->faker->numberBetween(0, 1),
            'commission_amount' => $this->faker->numberBetween(100, 1000),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime()
        ];
    }
}
