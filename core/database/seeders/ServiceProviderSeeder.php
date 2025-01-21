<?php
namespace Database\Seeders;

use App\Models\ServiceProvider;
use App\Models\Providers;
use App\Models\VeServices;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $existingCombinations = [];

        for ($i = 0; $i < 10000; $i++) {
            $providerId = Providers::pluck('id')->random();
            $serviceId = VeServices::pluck('id')->random();
            $combination = $providerId . '-' . $serviceId;

            // Check if the combination already exists
            if (!in_array($combination, $existingCombinations)) {
                $existingCombinations[] = $combination;

                DB::table('service_provider')->insert([
                    'provider_id' => $providerId,
                    'service_id' => $serviceId,
                    'status' => random_int(0, 1),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
