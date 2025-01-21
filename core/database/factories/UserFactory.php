<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->phoneNumber(),
//            'email_verified_at' => now(),
            'password' => fake()->password(), // password
//            'remember_token' => Str::random(10),
        'gender'=> fake()->randomElement([0,1]),
            'dob' => fake()->date(),
            'image' => fake()->imageUrl(),
            'status' => fake()->randomElement([0,1]),
            'updated_at' => now(),
            'created_at' => now(),
            'country_id' => fake()->randomElement([1,2]),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
