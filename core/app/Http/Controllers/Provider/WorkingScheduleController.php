<?php

namespace App\Http\Controllers\Provider;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Controllers\vendors\v1\VeMatcherController;
use App\Models\ActionReason;
use App\Models\Avaliabilty;
use App\Models\Booking;
use App\Models\OffDays;
use App\Models\WorkingSchedule;
use App\Traits\DayTrait;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WorkingScheduleController extends Controller {
  use ResponseTrait, DayTrait;

  public function listSchedule() {
    try {
      $provider = auth()->user()->provider;
      if (!$provider) {
        return $this->getErrorResponse('error', 'provider not found');
      }
      $workingSchedule = WorkingSchedule::where('provider_id', $provider->id)->get();
      $schedule = [];
      foreach ($this->week_days() as $day) {
        $data = [
          'day' => $day,
          'off' => true,
        ];
        $match_schedule = $workingSchedule->first(function ($item) use ($day) {
          return $this->getDayNamefromId($item->day) === $day;
        });
        if ($match_schedule) {
          $data['from'] = $match_schedule->from;
          $data['to'] = $match_schedule->to;
          $data['off'] = false;
        }
        $schedule[] = $data;
      }
      return $this->getSuccessResponse('success', $schedule);
    } catch (\Throwable $th) {
      return $this->getErrorResponse('error', $th->getMessage());
    }
  }

  public function editSchedule(Request $request) {
    try {
      $validator = Validator::make($request->all(), [
        'schedule' => ['required', 'array'],
        'schedule.*.day' => [
          'required',
          'string',
          Rule::in($this->week_days())
        ],
        'schedule.*.off' => ['required', 'boolean'],
        'schedule.*.from' => ['required_if:schedule.*.off,false', 'numeric'],
        'schedule.*.to' => ['required_if:schedule.*.off,false', 'numeric', 'gt:schedule.*.from']
      ]);
      if ($validator->fails()) {
        return $this->getErrorResponse('validation_error', $validator->errors(), 422);
      }
      $provider = auth()->user()->provider;
      if (!$provider) {
        return $this->getErrorResponse('error', 'provider not found');
      }
      WorkingSchedule::where('provider_id', $provider->id)->delete();
      foreach ($request->schedule as $schedule_data) {
        if (isset($schedule_data['from']) && isset($schedule_data['to'])) {
          if (isset($schedule_data['off']) && !$schedule_data['off']) {
            WorkingSchedule::create([
              'day' => $this->getDayId($schedule_data['day']),
              'from' => $schedule_data['from'],
              'to' => $schedule_data['to'],
              'provider_id' => $provider->id,
              'branch_id' => $request->get('branch_id', $request->get('selected_branch', null)),
            ]);
          }
        }
      }
      $matcher = new VeMatcherController();
      $matcher->workingSchedule($provider->id);

      $bookings = Booking::whereDate(DB::raw('DATE(date)'), '>=', now())
        ->whereIn('status', [Status::BOOKINGPENDING])
        ->whereHas('providers', function ($p) use ($provider) {
          $p->where('provider_id', $provider->id);
        })->get()->filter(function ($booking) use ($request) {
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

      return $this->listSchedule();
    } catch (\Throwable $th) {
      Log::error($th->getMessage());
      return $this->getErrorResponse('error', $th->getMessage(), 422);
    }
  }

  public function listDaysOff(Request $request) {
    try {
      $provider = auth()->user()->provider;
      if (!$provider) {
        return $this->getErrorResponse('error', 'provider not found');
      }
      $currentMonthStart = now()->startOfMonth();
      $currentMonthEnd = now()->endOfMonth();
      $data = OffDays::where('provider_id', $provider->id)
        ->where(function ($query) use ($currentMonthStart, $currentMonthEnd) {
          $query->whereBetween('from', [$currentMonthStart, $currentMonthEnd])
            ->orWhereBetween('to', [$currentMonthStart, $currentMonthEnd]);
        })
        ->get();
      return $this->getSuccessResponse('success', $data);
    } catch (\Throwable $th) {
      Log::error($th->getMessage());
      return $this->getErrorResponse('error', $th->getMessage());
    }
  }

  public function addEditDayOff(Request $request) {
    try {
      // Validation rules
      $validator = Validator::make($request->all(), [
        'from' => 'required|date|after_or_equal:today',
        'to' => 'required|date|after_or_equal:from',
        'title' => 'required|string|max:20'
      ]);

      if ($validator->fails()) {
        return $this->getErrorResponse('validation_error', $validator->errors());
      }
      $provider = auth()->user()->provider;

      DB::beginTransaction();

      // Check if an off day for the given provider and date range already exists
      $day_off = OffDays::where('provider_id', $provider->id)
        ->where(function ($query) use ($request) {
          $query->whereBetween('from', [$request->from, $request->to])
            ->orWhereBetween('to', [$request->from, $request->to]);
        })->first();
      if ($day_off) {
        $day_off->update([
          'from' => $request->from,
          'to' => $request->to,
          'title' => $request->title,
          'updated_at' => now()
        ]);
      } else {
        $day_off = OffDays::create([
          'provider_id' => $provider->id,
          'from' => $request->from,
          'to' => $request->to,
          'title' => $request->title,
          'created_at' => now(),
          'updated_at' => now()
        ]);
      }
      $matcher = new VeMatcherController();
      $matcher->offdaysEditAll($day_off->id, $provider->id);
      DB::commit();

      Avaliabilty::truncate();
      Artisan::call('app:add-provider-availability');

      return $this->getSuccessResponse('success', $day_off);
    } catch (\Throwable $th) {
      DB::rollBack();
      Log::error($th->getMessage());
      return $this->getErrorResponse('error', $th->getMessage());
    }
  }

  public function destroyDayOff(Request $request) {
    try {
      $provider = auth()->user()->provider;
      if (!$provider) {
        return $this->getErrorResponse('error', 'provider not found');
      }
      $validate = Validator::make($request->all(), [
        'id' => 'required|exists:off_days,id'
      ]);
      if ($validate->fails()) {
        return $this->getErrorResponse('validation_error', $validate->errors());
      }
      $off_day = OffDays::find($request->id);
      if (empty($off_day)) {
        return $this->getErrorResponse('error', 'not found');
      }

      Artisan::call(
        'app:add-provider-availability',
        [
          '--start-date' => $off_day->from,
          '--end-date' => $off_day->to,
          '--providers' => $provider->id
        ]
      );
      $off_day->delete();

      Avaliabilty::truncate();
      Artisan::call('app:add-provider-availability');

      return $this->getSuccessResponse('success', 'deleted');
    } catch (\Exception $e) {
      return $this->getErrorResponse('error', $e->getMessage());
    }
  }
}
