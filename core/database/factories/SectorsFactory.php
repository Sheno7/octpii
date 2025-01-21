<?php

namespace Database\Factories;

use App\Models\Sectors;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sectors>
 */
class SectorsFactory extends Factory
{
    protected $model = Sectors::class;
    public function definition(): array
    {
        return [
            'title_ar' => $this->faker->name,
            'title_en' => $this->faker->name,
            //'pricing_model_id' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->numberBetween(0, 1),
            'icon' => 'https://www.flaticon.com/svg/static/icons/svg/3523/3523063.svg'

        ];
    }
}
