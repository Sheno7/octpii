<?php

namespace Database\Factories;

use App\Models\Avaliabilty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Avaliabilty>
 */
class AvaliabiltyFactory extends Factory
{
    protected $model = Avaliabilty::class;
    public function definition(): array
    {
        return [
            'provider_id' => $this->faker->numberBetween(1, 10),
            'date' => $this->faker->date(),
            'from' => $this->faker->time(),
            'to' => $this->faker->time(),
            'area_id' => $this->faker->numberBetween(1, 10),
        ];
    }
}
