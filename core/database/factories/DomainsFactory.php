<?php

namespace Database\Factories;

use App\Models\Domains;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Domains>
 */
class DomainsFactory extends Factory
{
    protected $model = Domains::class;
    public function definition(): array
    {
        return [
            'domain' => $this->faker->unique()->domainName,
            'tenant_id' => $this->faker->numberBetween(1, 10),
            'created_at' => $this->faker->dateTimeBetween('-1 years', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 years', 'now'),
            'vendor_id' => $this->faker->numberBetween(1, 10),
        ];
    }
}
