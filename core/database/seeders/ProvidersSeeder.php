<?php
namespace Database\Seeders;

use App\Models\Address;
use App\Models\Areas;
use App\Models\Providers;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProvidersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {

            $faker = FakerFactory::create();
            $names =  ['Hend', 'Ahmed', 'Ali', 'Mostafa', 'Zaid', 'Amr', 'Zain', 'Mohamed', 'Hassan', 'Jhaled', 'Abdullah', 'Abdulrahman', 'Andrew'];
            $domain = ['@gmail.com', '@yahoo.com', '@hotmail.com', '@outlook.com', '@mail.com', '@live.com'];
            for ($i = 0; $i < 5000; $i++) {

                $first = $faker->randomElement($names);
                $last = $faker->randomElement($names);
                $name = $first . ' ' . $last;

                $user = User::create([
                    'email' => null,
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

                $providers = Providers::Create(
                    [
                        'user_id' => $user->id,
                        'address_id' => 0,
                        'rank' => random_int(1, 3),
                        'rating' => random_int(31, 49) / 10, // random float between 3.1 and 4.9
                        'start_date' => now(),
                        'salary' => random_int(100, 5000),
                        'commission_type' => random_int(1, 2),
                        'commission_amount' => random_int(1, 10),
                        'balance' => random_int(100, 5000),
                        'status' => random_int(0, 1),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $address = Address::create([
                    'owner_id' => $providers->id,
                    'owner_type' => 2,
                    'area_id' => Areas::pluck('id')->random(),
                    'location_name' => 'House',
                    'unit_type' => random_int(1, 2),
                    'unit_size' => random_int(80, 300),
                    'street_name' => random_int(100, 300),
                    'building_number' => random_int(80, 300),
                    'floor_number' => random_int(1, 10),
                    'unit_number' => random_int(1, 10),
                    'notes' => 'Notes',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                // update providrs address id
//                $providers->update(['address_id' => $address->id]
//                );
                // update providers address id
                $providers->update(['address_id' => $address->id]);
            }
            DB::commit();
            Log::info('ProvidersSeeder: success');
        }catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
