<?php

namespace Database\Factories;

use App\Models\Areas;
use Illuminate\Database\Eloquent\Factories\Factory;


class AreasFactory extends Factory
{
    protected $model = Areas::class;

    public function definition(): array
    {
        return [
            'title_ar' => $this->faker->city,
            'title_en' => $this->faker->city ,
            'lat' => $this->faker->latitude,
            'long' => $this->faker->longitude,
            'city_id' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->numberBetween(0, 1),
        ];
    }
}
