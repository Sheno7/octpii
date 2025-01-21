<?php

namespace Database\Factories;

use App\Models\AreaProviders;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AreaProviders>
 */
class AreaProvidersFactory extends Factory
{
    protected $model = AreaProviders::class;
    public function definition(): array
    {
        return [
            'area_id' => $this->faker->numberBetween(1, 10),
            'provider_id' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->numberBetween(0, 1),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
