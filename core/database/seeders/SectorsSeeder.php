<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectorsSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('sectors')->insert(
            [
                [
                    'title_ar' => 'خدمات التنظيف',
                    'title_en' => 'Cleaning Services',
                    'icon' => 'https://svgsilh.com/svg_v2/145027.svg',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'title_ar' => 'عيادات الإسنان',
                    'title_en' => 'Dental Clinics',
                    'icon' => 'https://png.pngtree.com/png-clipart/20200701/original/pngtree-oral-teeth-brushing-png-image_5414405.jpg',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]
        );
    }
}
