<?php

namespace Database\Seeders;

use App\Models\Areas;
use App\Models\Customers;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 5000; $i++) {
            DB::table('address')->insert([
                'owner_id' => Customers::pluck('id')->random(),
                'owner_type' => random_int(1,2),
                'area_id' => Areas::pluck('id')->random(),
                'location_name' => 'Home',
                'unit_type' => random_int(1, 2),
                'unit_size' => random_int(80 , 300),
                'street_name' => 'Street 5',
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
