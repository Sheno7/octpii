<?php
namespace Database\Seeders;

use App\Models\Providers;
use App\Models\WorkingSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class WorkingScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $existingCombinations = [];

        for ($i = 0; $i < 100000; $i++) {
            $providerId = Providers::pluck('id')->random();
            $day = Arr::random([0, 1, 2, 3, 4, 5, 6]);
            $from = Arr::random([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18]);
            $to = Arr::random([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23]);
            $combination = "$providerId-$day-$from-$to";

            // Check if the combination already exists
            if (!in_array($combination, $existingCombinations)) {
                $existingCombinations[] = $combination;

                WorkingSchedule::create([
                    'provider_id' => $providerId,
                    'day' => $day,
                    'from' => $from,
                    'to' => $to,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
