<?php

namespace Database\Factories;

use App\Models\AdditionalInformationMeta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdditionalInformationMeta>
 */
class AdditionalInformationMetaFactory extends Factory
{
   protected $model = AdditionalInformationMeta::class;
    public function definition(): array
    {
        return [
            'additional_info_id' => $this->faker->numberBetween(1, 100),
            'customer_id' => $this->faker->numberBetween(1, 100),
            'key' => $this->faker->word,
            'value' => $this->faker->word,
            'created_at' => $this->faker->dateTime,
            'updated_at' => $this->faker->dateTime,
        ];
    }
}
