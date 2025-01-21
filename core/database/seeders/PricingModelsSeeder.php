<?php

namespace Database\Seeders;

use App\Models\PricingModels;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PricingModelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PricingModels::where('status', 1);
        foreach (range(1, 5) as $i) {
            PricingModels::create([
                'name' => 'Pricing Model ' . $i,
                'capacity' => true,
                'variable_name' => 'per hour',
                'pricing_type' => 'fixed',
                'capacity_threshold' => true,
                'additional_cost' => false,
                'markup' => true,
                // 'status' => 1,
            ]);
        }
//        DB::table('pricing_models')->insert([
//            [
//                'name' => 'First',
//                'capacity' => true,
//                'variable_name' => 'per hour',
//                'pricing_type' => 'fixed',
//                'capacity_threshold' => true,
//                'additional_cost' => false,
//                'markup' => true,
//                'created_at' => now(),
//                'updated_at' => now()
//            ],
//            [
//                'name' => 'Second',
//                'capacity' => true,
//                'variable_name' => 'per hour',
//                'pricing_type' => 'fixed',
//                'capacity_threshold' => true,
//                'additional_cost' => false,
//                'markup' => true,
//                'created_at' => now(),
//                'updated_at' => now()
//            ],
//            [
//                    'name' => 'Third',
//                    'capacity' => true,
//                    'variable_name' => 'per hour',
//                    'pricing_type' => 'fixed',
//                    'capacity_threshold' => true,
//                    'additional_cost' => false,
//                    'markup' => true,
//                    'created_at' => now(),
//                    'updated_at' => now()
//            ],
//            [
//                'name' => 'Fourth',
//                'capacity' => true,
//                'variable_name' => 'per hour',
//                'pricing_type' => 'fixed',
//                'capacity_threshold' => true,
//                'additional_cost' => false,
//                'markup' => true,
//                'created_at' => now(),
//                'updated_at' => now()
//            ],
//            [
//                'name' => 'Fifth',
//                'capacity' => true,
//                'variable_name' => 'per hour',
//                'pricing_type' => 'fixed',
//                'capacity_threshold' => true,
//                'additional_cost' => false,
//                'markup' => true,
//                'created_at' => now(),
//                'updated_at' => now()
//            ],
//            ]
//        );
    }
}

