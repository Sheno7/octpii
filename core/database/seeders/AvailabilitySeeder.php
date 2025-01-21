<?php

namespace Database\Seeders;

use App\Models\Avaliabilty;
use App\Models\Providers;
use App\Models\WorkingSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use App\Traits\DayTrait;
use Illuminate\Support\Facades\Date;

class AvailabilitySeeder extends Seeder
{
   use DayTrait;
    public function run(): void
    {
        $providerId = Providers::pluck('id')->toArray();
        for ($i = 0; $i < 10; $i++) {
            $workingSchedules = WorkingSchedule::where('provider_id', Arr::random($providerId))->get();
            $data = [];
            foreach ($workingSchedules as $value) {
                $startTime = Date::createFromTimeString($value->from);
                $endTime = Date::createFromTimeString($value->to);
                $duration = $startTime->diff($endTime);
                $hours_diff = $duration->h + ($duration->days * 24);
                $nextDates = $this->get_next_dates_for_days($value->day, 1);
                foreach ($nextDates as $date) {
                    $checkData = Avaliabilty::where([
                        'provider_id' => $providerId,
                        'date' => $date,
                        'from' => $value->from,
                        'to' => $value->to,
                        'duration' => $hours_diff,
                    ])->first();
                    if (!$checkData) {
                        $data[] = [
                            'provider_id' => Arr::random($providerId),
                            'date' => $date,
                            'from' => $value->from,
                            'to' => $value->to,
                            'duration' => $hours_diff,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
            Avaliabilty::insert($data);
        }
    }
}
