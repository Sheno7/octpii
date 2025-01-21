<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;
    public function definition(): array
    {
        return [
            'owner_id' => $this->faker->numberBetween(1, 100),
            'owner_type' => $this->faker->numberBetween(1, 2),
            'area_id' => $this->faker->numberBetween(1, 100),
            'location_name' => $this->faker->city,
            'unit_type' => $this->faker->numberBetween(1, 2),
            'unit_size' => $this->faker->numberBetween(1, 100),
            'street_name' => $this->faker->streetName,
            'building_number' => $this->faker->numberBetween(1, 100),
            'floor_number' => $this->faker->numberBetween(1, 100),
            'unit_number' => $this->faker->numberBetween(1, 100),
            'notes' => $this->faker->text,
            'created_at' => $this->faker->dateTime,
            'updated_at' => $this->faker->dateTime,


        ];
    }
}
