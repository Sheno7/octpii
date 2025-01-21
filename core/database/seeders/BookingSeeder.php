<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Customers;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customers::pluck('id')->toArray();
        $addresss = Address::pluck('id')->toArray();
        $areas = Address::pluck('area_id')->toArray();
        // random date between two dates
        $min = strtotime('2023-10-01');
        $max = strtotime('2023-12-01');
        for ($i = 0; $i < 500; $i++) {
            DB::table('booking')->insert([
                'customer_id' => Arr::random($customers),
                'date' => date('Y-m-d', rand($min, $max)),
                'gender_prefrence' => random_int(0,1),
                'is_favorite' => random_int(0,1),
                'address_id' => Arr::random($addresss),
                'area_id' => Arr::random($areas),
                'coupon_id' => random_int(0,1),
                'status' => random_int(0,3),
                'source' => random_int(0,1),
                'total' => random_int(100,5000),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
