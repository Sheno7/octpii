<?php

namespace App\Console\Commands;

use App\Models\Avaliabilty;
use App\Models\OffDays;
use App\Models\Providers;
use App\Models\WorkingSchedule;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddProviderAvailability extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-provider-availability
                            {--latest : Add only missing dates starting from the latest record}
                            {--start-date= : The start date for availability}
                            {--end-date= : The end date for availability}
                            {--providers= : The list of provider ids separated by comma: 1,2,3}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle() {
        try {
            // Get the current date
            $currentDate = Carbon::now();

            // Calculate the date 90 days from now
            $endDate = $currentDate->copy()->addDays(90);

            // Initialize an array to store availability records
            $availabilityRecords = [];

            // Get vendor's working schedule
            $vendorWorkingSchedule = WorkingSchedule::whereNull('provider_id')->get();

            // Get all days off
            $daysOff = OffDays::all();

            // if ($this->option('latest')) {
            $latestRecord = Avaliabilty::orderBy('date', 'desc')->first();
            if ($latestRecord) {
                $currentDate = $latestRecord->date->copy()->addDay();
            }
            // }

            if ($this->option('start-date')) {
                $currentDate = Carbon::parse($this->option('start-date'));
            }
            if ($this->option('end-date')) {
                $endDate = Carbon::parse($this->option('end-date'));
            }
            if ($this->option('providers')) {
                $providersWithNoAvailability = explode(',', $this->option('providers'));
            } else {
                // Create a query to find providers with no availability records
                $providersWithNoAvailability = DB::table('providers')
                    ->leftJoin('avaliabilty', function ($join) use ($currentDate, $endDate) {
                        $join->on('providers.id', '=', 'avaliabilty.provider_id')
                            ->whereBetween(
                                'avaliabilty.date',
                                [$currentDate->toDateString(), $endDate->toDateString()]
                            );
                    })
                    ->whereNull('avaliabilty.provider_id')
                    ->select('providers.id')
                    ->get()
                    ->pluck('id');
            }

            // Retrieve providers and their working schedules & Bookings
            $providersWithSchedules = Providers::with([
                'workingSchedules',
                'bookings' => function ($q) use ($currentDate, $endDate) {
                    $q->whereBetween('date', [$currentDate->toDateString(), $endDate->toDateString()])
                        ->with(['bookingServices.vservice' => function ($q) {
                            $q->select('id', 'duration');
                        }]);
                }
            ])->whereIn('id', $providersWithNoAvailability)->get();
            
            // TODO: Query marketplace bookings

            while ($currentDate->lte($endDate)) {
                // Calculate the day of the week (1 for Monday, 2 for Tuesday, etc.)
                $dayOfWeek = $currentDate->dayOfWeek;

                // Find the working schedule for the vendor on the current day
                $workingScheduleVendor = $vendorWorkingSchedule->where('day', $dayOfWeek)->first();

                // Check if day is off
                $isWorkingDay = $this->isWorkingDay($daysOff, $currentDate);

                if (!empty($workingScheduleVendor) && $isWorkingDay) {
                    foreach ($providersWithSchedules as $provider) {
                        if ($this->isWorkingDay($daysOff, $currentDate, $provider->id)) {
                            // Find the working schedule for the provider on the current day
                            $workingScheduleProvider = $provider->workingSchedules
                                ->where('day', $dayOfWeek)
                                ->first();

                            if ($workingScheduleProvider) {
                                // Calculate available time slots based on the provider's working hours
                                $startHour = $workingScheduleProvider->from;
                                $endHour = $workingScheduleProvider->to;

                                if ($startHour < $workingScheduleVendor->from) {
                                    $startHour = $workingScheduleVendor->from;
                                }
                                if ($endHour > $workingScheduleVendor->to) {
                                    $endHour = $workingScheduleVendor->to;
                                }

                                // Generate availability records for each one-hour slot
                                for ($hour = $startHour; $hour < $endHour; $hour += 0.5) {
                                    $availabilityRecord = [
                                        'provider_id' => $provider->id,
                                        'date' => $currentDate->toDateString(),
                                        'from' => $hour,
                                        'to' => $hour + 0.5,
                                        'duration' => 0.5,
                                    ];

                                    $availabilityRecords[] = $availabilityRecord;
                                }
                            }
                        }
                    }
                }

                // Move to the next day
                $currentDate->addDay();
            }

            // Insert the availability records into the database in chunks
            $chunkSize = 1000; // Adjust the chunk size as needed
            $chunks = array_chunk($availabilityRecords, $chunkSize);

            foreach ($chunks as $chunk) {
                Avaliabilty::insert($chunk);
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    private function isWorkingDay($daysOff, $currentDate, $provider_id = null) {
        return $daysOff->filter(function ($offDay) use ($currentDate, $provider_id) {
            $from = Carbon::parse($offDay->from)->setTime(0, 0, 0);
            $to = Carbon::parse($offDay->to)->setTime(23, 59, 59);

            // Check if $currentDate is between $from and $to, inclusive
            return $currentDate->between($from, $to) && $offDay->provider_id === $provider_id;
        })->isEmpty();
    }
}
