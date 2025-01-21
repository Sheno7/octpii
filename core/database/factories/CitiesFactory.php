<?php

namespace Database\Factories;

use App\Models\Cities;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cities>
 */
class CitiesFactory extends Factory
{
 protected $model = Cities::class;

 public function definition(): array
    {
        return [
            'title_ar' => $this->faker->city,
            'title_en' => $this->faker->city,
            'country_id' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->numberBetween(0, 1),
        ];
    }
}
