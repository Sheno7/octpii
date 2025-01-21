<?php

namespace Database\Factories;

use App\Models\PricingModels;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PricingModels>
 */
class PricingModelsFactory extends Factory
{
    protected $model = PricingModels::class;
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'capacity' => $this->faker->boolean,
            'variable_name' => $this->faker->name,
            'pricing_type' => $this->faker->randomElement(['fixed', 'variable']),
            'capacity_threshold' => $this->faker->boolean,
            'additional_cost' => $this->faker->boolean,
            'markup' => $this->faker->boolean,
        ];
    }
}
