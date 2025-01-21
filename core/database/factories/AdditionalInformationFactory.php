<?php

namespace Database\Factories;

use App\Models\AdditionalInformation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdditionalInformation>
 */
class AdditionalInformationFactory extends Factory
{
   protected $model = AdditionalInformation::class;
    public function definition(): array
    {
        return [
            'type' => $this->faker->word,
            'hasfile' => $this->faker->boolean,
            'value' => $this->faker->word,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
