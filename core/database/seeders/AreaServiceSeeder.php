<?php
namespace Database\Seeders;

use App\Models\AreaService;
use App\Models\Areas;
use App\Models\VeServices;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreaServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $existingCombinations = [];

        for ($i = 0; $i < 500; $i++) {
            $areaId = Areas::pluck('id')->random();
            $serviceId = VeServices::pluck('id')->random();
            $combination = $areaId . '-' . $serviceId;

            // Check if the combination already exists
            if (!in_array($combination, $existingCombinations)) {
                $existingCombinations[] = $combination;

                DB::table('area_service')->insert([
                    'area_id' => $areaId,
                    'service_id' => $serviceId,
                    'status' => random_int(0, 1),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
