<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Areas;
use App\Models\Customers;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = FakerFactory::create();
        $names =  ['Hend', 'Ahmed', 'Ali', 'Mostafa', 'Zaid', 'Amr', 'Zain', 'Mohamed', 'Hassan', 'Jhaled', 'Abdullah', 'Abdulrahman', 'Andrew'];
        for ($i = 0; $i < 30000; $i++)
        {

                $first = $faker->randomElement($names);
                $last = $faker->randomElement($names);
                $name = $first . ' ' . $last;

                $user = User::create([
                    'email' => $faker->unique()->safeEmail,
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
            $customer = Customers::create([
                'user_id' => $user->id,
                'rating' => random_int(1, 5),
                'status' => random_int(0, 1),
                'wallet_balance' => random_int(100, 5000),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
             // return customer id after insert
            Address::create([
                'owner_id' => $customer->id,
                'owner_type' => 1,
                'area_id' => Areas::pluck('id')->random(),
                'location_name' => 'Home',
                'unit_type' => random_int(1, 2),
                'unit_size' => random_int(80 , 300),
                'street_name' => random_int(100, 300),
                'building_number' => random_int(80 , 300),
                'floor_number' => random_int(1 , 10),
                'unit_number' => random_int(1 , 10),
                'notes' => 'Notes',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
