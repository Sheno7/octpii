<?php

namespace Database\Seeders;

use App\Models\Sectors;
use App\Models\Services;
use App\Models\VeServices;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $services = Services::all();
        $ve_services = VeServices::all();
        $this->fillDefaults($services);
        $this->fillDefaults($ve_services);
    }

    private function fillDefaults($services): void {
        $sectors = Sectors::count();
        foreach ($services as $service) {
            if (empty($sectors)) {
                $sector = $service->sectors()->updateOrCreate([
                    'title_en' => 'Default',
                ], [
                    'title_ar' => 'افتراضي',
                    'status' => 1,
                    'icon' => '',
                ]);
                $category = $sector->categories()->updateOrCreate([
                    'title_en' => 'uncategorized',
                ], [
                    'title_ar' => 'غير مصنف',
                ]);
                $service->category_id = $category->id;
                $service->save();
            } elseif (empty($service->category_id)) {
                if (empty($service->sectors)) {
                    $sector = $service->sectors()->updateOrCreate([
                        'title_en' => 'Default',
                    ], [
                        'title_ar' => 'افتراضي',
                        'status' => 1,
                        'icon' => '',
                    ]);
                    $category = $sector->categories()->updateOrCreate([
                        'title_en' => 'uncategorized',
                    ], [
                        'title_ar' => 'غير مصنف',
                    ]);
                } else {
                    $category = $service->sectors->categories()->updateOrCreate([
                        'title_en' => 'uncategorized',
                    ], [
                        'title_ar' => 'غير مصنف',
                    ]);
                }
                $service->category_id = $category->id;
                $service->save();
            }
        }
    }
}
