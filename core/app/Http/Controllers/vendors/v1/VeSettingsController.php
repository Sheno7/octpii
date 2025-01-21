<?php

namespace App\Http\Controllers\vendors\v1;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\WorkingSchedule\UpdateWorkingSchedule;
use App\Models\ActionReason;
use App\Models\Avaliabilty;
use App\Models\Booking;
use App\Models\Setting;
use App\Models\WorkingSchedule;
use App\Traits\DayTrait;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class VeSettingsController extends Controller {
    use DayTrait, ResponseTrait;

    public function get_working_schedule() {
        // Retrieve working schedule from the database for the vendor
        $vendorWorkingSchedule = WorkingSchedule::whereNull('provider_id')->get();

        // Generate schedule for all days of the week
        $schedule = [];
        for ($i = 0; $i < 7; $i++) {
            $schedule[] = [
                'day' => $this->week_days()[$i],
                'off' => true, // Default to off
            ];
        }

        // Merge database data with generated schedule
        foreach ($vendorWorkingSchedule as $workingDay) {
            $schedule[$workingDay->day]['off'] = false; // Set off to false for working days
            $schedule[$workingDay->day]['from'] = $workingDay->from;
            $schedule[$workingDay->day]['to'] = $workingDay->to;
        }

        return $this->getSuccessResponse(__('retrieved_successfully'), $schedule);
    }

    public function update_working_schedule(UpdateWorkingSchedule $request) {
        $inputs = $request->validated();

        Setting::where('key', 'setting')
            ->update(['value->timezone' => $inputs['timezone']]);

        foreach ($inputs['schedule'] as $day) {
            if ($day['off']) {
                $workingDay = WorkingSchedule::whereNull('provider_id')->where('day', $this->getDayId($day['day']));
                $workingDay->delete();
            } else {
                WorkingSchedule::updateOrCreate([
                    'provider_id' => null,
                    'day' => $this->getDayId($day['day']),
                    'branch_id' => $request->get('branch_id', $request->get('selected_branch', null)),
                ], [
                    'from' => $day['from'] ?? null,
                    'to' => $day['to'] ?? null,
                ]);
            }
        }

        $bookings = Booking::whereDate(DB::raw('DATE(date)'), '>=', now())
            ->whereIn('status', [Status::BOOKINGPENDING])
            ->get()->filter(function ($booking) use ($request) {
                $date = Carbon::parse($booking->date);
                $day = $date->format('l');
                $hour = $date->format('H');
                $minute = $date->format('i');

                $from = (int)$hour;
                if ($minute >= 30) {
                    $from += 0.5;
                }
                $to = $from + $booking->duration;
                $schedule = $request->get('schedule', []);

                foreach ($schedule as $item) {
                    if (
                        $item['day'] === $day
                        && !$item['off']
                        && $from >= $item['from']
                        && $to <= $item['to']
                    ) {
                        return false;
                    }
                }

                return true;
            });

        $bookings->map(function ($booking) {
            $booking->update(['status' => Status::BOOKINGCANCELLED]);
            // add action reason with booking ID
            ActionReason::create([
                'action' => 1,
                'reason' => 'schedule_conflict',
                'booking_id' => $booking->id,
                'status' => Status::ACTIVE,
                'action_by' => auth()->user()->id,
            ]);
        });

        Avaliabilty::truncate();
        Artisan::call('app:add-provider-availability');

        return $this->getSuccessResponse(__('updated_successfully'));
    }
}
