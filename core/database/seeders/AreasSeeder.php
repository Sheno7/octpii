<?php

namespace Database\Seeders;

use App\Models\Cities;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $areas = [
            [
                'city_id' => 1,
                'title_en' => 'Maadi',
                'title_ar' => 'المعادي',
                'lat' => '29.983999',
                'long' => '31.244630',
                'status' => 1,
                'created_at' => '2021-07-01 00:00:00',
                'updated_at' => '2021-07-01 00:00:00'
            ],
            [
                'city_id' => 1,
                'title_en' => 'Helwan',
                'title_ar' => 'حلوان',
                'lat' => '29.828717',
                'long' => '31.367404',
                'status' => 1,
                'created_at' => '2021-07-01 00:00:00',
                'updated_at' => '2021-07-01 00:00:00'
            ],
            [
                'city_id' => 1,
                'title_en' => 'Nasr City',
                'title_ar' => 'مدينة نصر',
                'lat' => '30.059052',
                'long' => '31.337499',
                'status' => 1,
                'created_at' => '2021-07-01 00:00:00',
                'updated_at' => '2021-07-01 00:00:00'
            ],
            [
                'city_id' => 1,
                'title_en' => 'Downtown',
                'title_ar' => 'وسط البلد',
                'lat' => '30.050330',
                'long' => '31.246153',
                'status' => 1,
                'created_at' => '2021-07-01 00:00:00',
                'updated_at' => '2021-07-01 00:00:00'
            ],
            [
                'city_id' => 1,
                'title_en' => 'Abbassia',
                'title_ar' => 'العباسية',
                'lat' => '30.075760',
                'long' => '31.280803',
                'status' => 1,
                'created_at' => '2021-07-01 00:00:00',
                'updated_at' => '2021-07-01 00:00:00'
            ],
            [
                'city_id' => 2,
                'title_en' => 'Wardeyan',
                'title_ar' => 'الورديان',
                'lat' => '31.200000',
                'long' => '29.916700',
                'status' => 1,
                'created_at' => '2021-07-01 00:00:00',
                'updated_at' => '2021-07-01 00:00:00'
            ]
        ];

        DB::table('areas')->insert($areas);
    }
}
