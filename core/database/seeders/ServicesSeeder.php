<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('services')->insert([
            [
                'title_ar' => 'خدمة التنظيف العميق',
                'title_en' => 'Deep Cleaning Services',
            'description_ar' => 'خدمة التنظيف العميق',
            'description_en' => 'Deep Cleaning Services',
            'sector_id' => 1,
           // 'pricing_model_id' => 1, // 'per hour'
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now()
            ],
            [
                'title_ar' => 'خدمة التنظيف العادي',
                'title_en' => 'Normal Cleaning Services',
            'description_ar' => 'خدمة التنظيف العادي',
            'description_en' => 'Normal Cleaning Services',
            'sector_id' => 1,
          //  'pricing_model_id' => 1, // 'per hour'
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now()
            ],
            [
                'title_ar' => 'خدمات حشو الأسنان',
                'title_en' => 'Dental Filling Services',
            'description_ar' => 'خدمة حشو الأسنان',
            'description_en' => 'Dental Filling Services',
            'sector_id' => 2,
           // 'pricing_model_id' => 2, // 'per tooth
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now()
            ],
            [
                'title_ar' => 'خدمات تركيب الأسنان',
                'title_en' => 'Dental Implant Services',
            'description_ar' => 'خدمة تركيب الأسنان',
            'description_en' => 'Dental Implant Services',
            'sector_id' => 2,
          //  'pricing_model_id' => 2,
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now()
            ],
            [
                'title_ar' => 'خدمات تبييض الأسنان',
                'title_en' => 'Dental Whitening Services',
            'description_ar' => 'خدمة تبييض الأسنان',
            'description_en' => 'Dental Whitening Services',
            'sector_id' => 2,
          //  'pricing_model_id' => 2,
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now()
            ],
        ]);
    }
}
