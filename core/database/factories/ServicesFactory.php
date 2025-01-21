<?php

namespace Database\Factories;

use App\Models\Services;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Services>
 */
class ServicesFactory extends Factory
{
    protected $model = Services::class;
    public function definition(): array
    {
        return [
            'title_ar' => $this->faker->name,
            'title_en' => $this->faker->name,
            'description_ar' => $this->faker->text,
            'description_en' => $this->faker->text,
            'sector_id' => $this->faker->numberBetween(1, 10),
            //'pricing_model_id' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->numberBetween(0, 1),
            'icon' => 'https://www.flaticon.com/svg/static/icons/svg/3523/3523063.svg',
            'upid' => $this->faker->numberBetween(1, 10),
            'service_location' => $this->faker->numberBetween(0, 1),
        ];
    }
}
