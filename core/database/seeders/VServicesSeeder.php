<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//        DB::table('ve_services')->insert([
//            [
//                'title_ar' => 'خدمة التنظيف العميق',
//                'title_en' => 'Deep Cleaning Services',
//                'description_ar' => 'خدمة التنظيف العميق',
//                'description_en' => 'Deep Cleaning Services',
//                'duration' => 2,
//                'pricing_model_id' => 1, // 'per hour'
//                'capacity' => 1,
//                'capacity_threshold' => 1,
//                'status' => 0,
//                'cost' => 0,
//                'markup' => 0,
//                'icon' => 'https://svgsilh.com/svg_v2/145027.svg',
//                'created_at' => now(),
//                'updated_at' => now(),
//                'deleted_at' => now()
//            ],
//            [
//                'title_ar' => 'خدمات تركيب الأسنان',
//                'title_en' => 'Dental Implant Services',
//                'description_ar' => 'خدمة تركيب الأسنان',
//                'description_en' => 'Dental Implant Services',
//                'duration' => 2,
//                'pricing_model_id' => 2, // 'per tooth
//                'capacity' => 1,
//                'capacity_threshold' => 1,
//                'status' => 0,
//                'cost' => 0,
//                'markup' => 0,
//                'icon' => 'https://svgsilh.com/svg_v2/145027.svg',
//                'created_at' => now(),
//                'updated_at' => now(),
//                'deleted_at' => now()
//            ],
//            [
//                'title_ar' => 'خدمات تركيب الأسنان',
//                'title_en' => 'Dental Implant Services',
//                'description_ar' => 'خدمة تركيب الأسنان',
//                'description_en' => 'Dental Implant Services',
//                'duration' => 2,
//                'pricing_model_id' => 2, // 'per tooth
//                'capacity' => 1,
//                'capacity_threshold' => 1,
//                'status' => 0,
//                'cost' => 0,
//                'markup' => 0,
//                'icon' => 'https://svgsilh.com/svg_v2/145027.svg',
//                'created_at' => now(),
//                'updated_at' => now(),
//                'deleted_at' => null,
//            ]
//        ]);
        // insert all services from table services into table ve_services
        $services = DB::table('services')->get();
        foreach ($services as $service) {
            DB::table('ve_services')->insert([
                'title_ar' => $service->title_ar,
                'title_en' => $service->title_en,
                'description_ar' => $service->description_ar,
                'description_en' => $service->description_en,
                'duration' => $service->duration,
                'pricing_model_id' => $service->pricing_model_id,
                'capacity' => $service->capacity,
                'capacity_threshold' => $service->capacity_threshold,
                'status' => $service->status,
                'cost' => $service->cost,
                'markup' => $service->markup,
                'icon' => $service->icon,
                'created_at' => $service->created_at,
                'updated_at' => $service->updated_at,
                'deleted_at' => $service->deleted_at,
            ]);
        }
//        DB::table('ve_services')->insert([
//            [
    }
}
