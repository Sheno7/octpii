<?php

namespace Database\Factories;

use App\Models\PricingModelSector;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PricingModelSector>
 */
class PricingModelSectorFactory extends Factory
{
    protected $model = PricingModelSector::class;
    public function definition(): array
    {
        return [
            'pricing_model_id' => $this->faker->numberBetween(1, 10),
            'sector_id' => $this->faker->numberBetween(1, 10),
            'created_at' => $this->faker->dateTimeBetween('-1 years', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 years', 'now'),
        ];
    }
}
