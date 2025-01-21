<?php
namespace Database\Seeders;

use App\Models\AreaProviders;
use App\Models\Areas;
use App\Models\Providers;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreaProvidersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $existingCombinations = [];

        for ($i = 0; $i < 10000; $i++) {
            $areaId = Areas::pluck('id')->random();
            $providerId = Providers::pluck('id')->random();
            $combination = $areaId . '-' . $providerId;

            // Check if the combination already exists
            if (!in_array($combination, $existingCombinations)) {
                $existingCombinations[] = $combination;

                AreaProviders::create([
                    'area_id' => $areaId,
                    'provider_id' => $providerId,
                    'status' => random_int(0, 1),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
