<?php

namespace Database\Seeders;

use App\Models\PricingModels;
use App\Models\PricingModelSector;
use App\Models\Sectors;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PricingModelSectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $existingCombinations = DB::table('pricing_model_sector')
            ->select('pricing_model_id', 'sector_id')
            ->get()
            ->toArray();

        // Generate and insert new unique combinations
        $uniqueCombinations = [];
        $numberOfCombinationsToGenerate = 2; // You can adjust this as needed

        while (count($uniqueCombinations) < $numberOfCombinationsToGenerate) {
            $pricingModelId = PricingModels::pluck('id')->random();
            $sectorId = Sectors::pluck('id')->random();
            $combination = ['pricing_model_id' => $pricingModelId, 'sector_id' => $sectorId];

            // Check if the combination already exists
            if (!in_array($combination, $existingCombinations)) {
                $uniqueCombinations[] = $combination;
                $existingCombinations[] = $combination;

                // Insert the unique combination
                DB::table('pricing_model_sector')->insert([
                    'pricing_model_id' => $pricingModelId,
                    'sector_id' => $sectorId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
