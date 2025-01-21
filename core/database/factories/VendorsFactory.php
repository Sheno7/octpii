<?php

namespace Database\Factories;

use App\Models\Vendors;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendors>
 */
class VendorsFactory extends Factory
{
   protected $model = Vendors::class;
    public function definition(): array
    {
        return [
            'org_name_ar' => $this->faker->name,
            'org_name_en' => $this->faker->name,
            'sector_id' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->numberBetween(0, 1),
            'user_id' => $this->faker->numberBetween(1, 10),
            'services_count' => $this->faker->numberBetween(0, 10)
        ];
    }
}
