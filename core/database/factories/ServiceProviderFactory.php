<?php

namespace Database\Factories;

use App\Models\ServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceProvider>
 */
class ServiceProviderFactory extends Factory
{
    protected $model = ServiceProvider::class;
    public function definition(): array
    {
        return [
            'provider_id' => $this->faker->numberBetween(1, 10),
            'service_id' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->numberBetween(0, 1),
        ];
    }
}
