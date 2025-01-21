<?php

namespace Database\Factories;

use App\Models\AreaService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AreaService>
 */
class AreaServiceFactory extends Factory
{
    protected $model = AreaService::class;
    public function definition(): array
    {
        return [
            'area_id' => $this->faker->numberBetween(1, 10),
            'service_id' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->numberBetween(0, 1),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
