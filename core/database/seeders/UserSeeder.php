<?php

namespace Database\Seeders;

use App\Models\Countries;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as FakerFactory;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = FakerFactory::create();
        $names =  ['Hend', 'Ahmed', 'Ali', 'Mostafa', 'Zaid', 'Amr', 'Zain', 'Mohamed', 'Hassan', 'Jhaled', 'Abdullah', 'Abdulrahman', 'Andrew'];

        for ($i = 0; $i < 7000; $i++) {
            $first = $faker->randomElement($names);
            $last = $faker->randomElement($names);
            $name = $first . ' ' . $last;

            User::create([
                'email' => $names[$i]. $i. '@' . $faker->unique()->safeEmail,
                'first_name' => $first,
                'last_name' => $last,
                'name' => $name,
                'phone' => $faker->unique()->phoneNumber,
                'country_id' => 1,
                'dob' => $faker->date(),
                'gender' => $faker->randomElement(['0', '1']),
                'password' => bcrypt('123456'),
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }
    }
}
