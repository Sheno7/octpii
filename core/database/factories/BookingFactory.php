<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;
    public function definition(): array
    {
        return [
            'customer_id' => $this->faker->numberBetween(1, 10),
            'date' => $this->faker->dateTimeBetween('-1 years', '+1 years'),
            'gender_prefrence' => $this->faker->numberBetween(0, 1),
            'is_favorite' => $this->faker->numberBetween(0, 1),
            'address_id' => $this->faker->numberBetween(1, 10),
            'area_id' => $this->faker->numberBetween(1, 10),
            'coupon_id' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->numberBetween(0, 1),
            'source' => $this->faker->numberBetween(0, 1),
            'total' => $this->faker->randomFloat(2, 0, 99999999999999999999.99),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
