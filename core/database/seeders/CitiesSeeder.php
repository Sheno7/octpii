<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *php artisan make:seeder
     * @return void
     */
    public function run()
    {
        $countryId = 1;
        DB::table('cities')->insert([
            [
                'title_en' => 'Cairo',
                'title_ar' => 'القاهرة',
                'status' => 1,
                'country_id' => $countryId,
                'created_at' => '2021-07-01 00:00:00'
            ],
            [
                'title_en' => 'Alexandria',
                'title_ar' => 'الإسكندرية',
                'status' => 1,
                'country_id' => $countryId,
                'created_at' => '2021-07-01 00:00:00'
            ],
        ]);
    }
}
