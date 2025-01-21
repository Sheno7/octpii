<?php

namespace Database\Factories;

use App\Models\BookingService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookingService>
 */
class BookingServiceFactory extends Factory
{
    protected $model = BookingService::class;
    public function definition(): array
    {
        return [
            'booking_id' => $this->faker->numberBetween(1, 10),
            'service_id' => $this->faker->numberBetween(1, 10),
            'price' => $this->faker->numberBetween(1, 10),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
