<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Matcher\CheckAvailability;
use App\Models\Address;
use App\Models\Avaliabilty;
use App\Models\Booking;
use App\Models\BookingProvider;
use App\Models\BookingService;
use App\Models\OffDays;
use App\Models\Providers;
use App\Models\VeServices;
use App\Models\WorkingSchedule;
use App\Traits\ResponseTrait;
use App\Traits\DayTrait;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Date;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class VeMatcherController extends Controller {

    use ResponseTrait, DayTrait;

    private function check_address($service_id) {
        return VeServices::where('id', $service_id)->value('service_location');
    }

    public function check_availability(CheckAvailability $request) {
        $validated = $request->validated();

        try {
            // Start by querying the ServiceProvider model with necessary relationships
            $matchingProviders = Providers::whereHas('user', function ($query) use ($validated) {
                // Apply user's gender preference
                if (intval($validated['gender']) !== -1) {
                    $query->where('gender', $validated['gender']);
                }
            })
                // where provider status is active with value 1
                ->where('status', 1)
                ->where(function ($query) use ($validated) {
                    if ($this->check_address($validated['service_id']) == 1) {
                        $query->whereHas('areas', function ($query) use ($validated) {
                            $query->where('area_id', $validated['area_id']);
                        });
                    }
                })
                ->whereHas('services', function ($query) use ($validated) {
                    // Apply selected service
                    $query->where('service_id', $validated['service_id']);
                })->get();
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage(), 500);
        }
        $providerIds = $matchingProviders->filter(function ($p) use ($validated) {
            return !isset($validated['provider_id']) || $p->id === $validated['provider_id'];
        })->pluck('id');
        $service = VeServices::find($validated['service_id']);
        $pricing_model = $service->pricingModel;
        $address = null;
        $providerCount = 1;
        if ($service->service_location == 1) {
            $address = Address::find($validated['address_id']);

            if ($service->capacity) {
                $providerCount = ceil($address->unit_size / $service->capacity);
            }
            $total = (($service->cost + $service->markup) * $providerCount) + $service->base_price;
            if ($pricing_model->pricing_type == 'variable') {
                $name = strtolower($pricing_model->variable_name);
                if ($name == 'hour') {
                    $total = ($service->duration * $service->cost + $service->markup + $service->base_price);
                } elseif ($name == 'meter') {
                    $total = ($address->unit_size * $service->cost + $service->markup + $service->base_price);
                }
            }
        }
        $total = $service->cost + $service->base_price;
        return $this->filter_availability(
            $providerIds,
            $validated['date'],
            $service->duration,
            $providerCount,
            $total
        );
    }

    public function delete_availability($availability_id) {
        try {
            Avaliabilty::where('id', $availability_id)->delete();
            return $this->getSuccessResponse('success');
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }
    public function addProvider(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|int|exists:providers,id',
            ]);
            if ($validator->fails()) {
                return $this->getErrorResponse('error', $validator->errors()->first());
            }
            // get duration for service
            $workingSchedules = WorkingSchedule::where('provider_id', $request->id)->orderBy('id', 'asc')->get();
            $data = [];
            $workingDays = $workingSchedules->pluck('day')->toArray();
            // Get the current date
            $currentDate = Carbon::now();
            // Initialize an empty array to store the dates
            $nextDates = [];
            // Loop through the next 30 days (1 month)
            for ($i = 0; $i < 90; $i++) {
                // Check if the current day is Monday, Tuesday, or Thursday
                if (in_array($currentDate->dayOfWeek, $workingDays)) {
                    // Add the current date to the array
                    $nextDates[] = $currentDate->copy()->toDateString();
                }
                // Move to the next day
                $currentDate->addDay();
            }

            foreach ($nextDates as $date) {
                $wd = $workingSchedules->where('day', '=', Carbon::parse($date)->dayOfWeek)->first();
                $startTime = Date::createFromTimeString($wd->from);
                $endTime = Date::createFromTimeString($wd->to);
                $duration = $startTime->diff($endTime);
                $hours_diff = $duration->h + ($duration->days * 24);
                for ($i = 0; $i < $hours_diff; $i++) {
                    $checkData = Avaliabilty::where([
                        'provider_id' => $request->id,
                        'date' => $date,
                        'from' => $wd->from + $i,
                        'to' => $wd->from + $i + 1,
                        'duration' => 1,
                    ])->first();
                    if (!$checkData) {
                        $data[] = [
                            'provider_id' => $request->id,
                            'date' => $date,
                            'from' => $wd->from + $i,
                            'to' => $wd->from + $i + 1,
                            'duration' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
            Avaliabilty::insert($data);
            return $this->getSuccessResponse('success');
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    protected function filter_availability($providers, $date, $duration, $provider_count, $total) {
        try {
            $selectedDate = Carbon::parse($date)->startOfDay();
            $today = now()->startOfDay();
            $daysDifference = $selectedDate->diffInDays($today);
            if ($daysDifference > 3) {
                $daysDifference = 3;
            }
            $startDate = Carbon::parse($date)->subDays($daysDifference);
            $endDate = Carbon::parse($date)->addDays(6 - $daysDifference);

            $data = Avaliabilty::whereIn('provider_id', $providers)
                ->select(
                    'avaliabilty.id as availability_id',
                    'avaliabilty.date',
                    'avaliabilty.from',
                    'avaliabilty.to',
                    'avaliabilty.provider_id',
                    'avaliabilty.duration',
                    'users.first_name',
                    'users.last_name',
                    'providers.rank',
                    'providers.rating'
                )
                ->join('providers', 'providers.id', '=', 'avaliabilty.provider_id')
                ->join('users', 'users.id', '=', 'providers.user_id')
                ->whereDate('avaliabilty.date', '>=', $startDate)
                ->whereDate('avaliabilty.date', '<=', $endDate)
                ->groupBy(
                    'avaliabilty.id',
                    'avaliabilty.date',
                    'avaliabilty.from',
                    'avaliabilty.to',
                    'avaliabilty.provider_id',
                    'avaliabilty.duration',
                    'users.first_name',
                    'users.last_name',
                    'providers.rank',
                    'providers.rating'
                );
            $data = $data->get();

            $available_slots = $this->getAvailableSlots($data, $duration);
            $dates = $this->prioritizeSlots($available_slots, $provider_count, $total);

            $sortedData = collect($dates)
                ->map(function ($slots) use ($data) {
                    $sortedSlots = collect($slots)->sortBy('from')->values();
                    return $sortedSlots->map(function ($slot) use ($data) {
                        $tmp = $slot;
                        $tmp['providers'] = collect($slot['providers'])->map(function ($p) use ($data) {
                            $id = $p['provider_id'];
                            $provider_data = $data->firstWhere('provider_id', '=', $id);
                            $provider = $p;
                            $provider['id'] = $id;
                            if ($provider_data) {
                                $provider['name'] = substr($provider_data->first_name, 0, 1)
                                    . '. ' . $provider_data->last_name;
                                $provider['rank'] = intval($provider_data->rank);
                                $provider['rating'] = intval($provider_data->rating);
                            }
                            return $provider;
                        });
                        return $tmp;
                    });
                })
                ->flatten(1);

            return $this->getSuccessResponse('success', $sortedData);
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }
    // search in avaliabilty table for provider_id and date and return it
    public function get_availability($booking_id, $service_id, $area_id, $date, $gender, $provider_id) {
        $data = Providers::join('area_providers', 'area_providers.provider_id', '=', 'providers.id')
            ->join('service_provider', 'service_provider.provider_id', '=', 'providers.id')
            ->join('users', 'users.id', '=', 'providers.user_id')
            ->join('avaliabilty', 'avaliabilty.provider_id', '=', 'providers.id')
            ->where('area_providers.area_id', $area_id)
            ->where('service_provider.service_id', $service_id)
            ->when(intval($gender) !== -1, function ($subquery) use ($gender) {
                $subquery->where('gender', $gender);
            })
            ->where('avaliabilty.date', $date)
            ->where('providers.id', '!=', $provider_id)
            ->whereNotIn('providers.id', function ($query) use ($booking_id) {
                $query->select('provider_id')
                    ->from('booking_provider')
                    ->where('booking_id', $booking_id);
            })
            ->select(
                'providers.id as id',
                'avaliabilty.id as avaliabilty_id',
                DB::raw("CONCAT(users.first_name, ' ', SUBSTRING(users.last_name, 1, 1), '.') as name"),
                'providers.rank',
                'providers.rating'
            )
            ->first();
        return $data;
    }

    public function add_availability($booking_id) {
        $booking = Booking::with(['services', 'providers'])->where('id', $booking_id)->first();
        $date = $booking->date;
        $providers = $booking->providers;
        $duration = $booking->duration;

        foreach ($providers as $provider) {
            $provider_id = $provider->id;
            $slots = $duration / 0.5;
            for ($i = 0; $i < $slots; $i++) {
                $from = Carbon::parse($date)->hour + Carbon::parse($date)->minute / 60 + ($i * 0.5);
                $to = $from + 0.5;
                $availability = Avaliabilty::where('provider_id', $provider_id)
                    ->where('date', date('Y-m-d', strtotime($date)))
                    ->where('from', $from)
                    ->where('to', $to)
                    ->first();
                if (!$availability) {
                    Avaliabilty::create([
                        'provider_id' => $provider_id,
                        'date' => date('Y-m-d', strtotime($date)),
                        'from' => $from,
                        'to' => $to,
                        'duration' => 0.5,
                    ]);
                }
            }
        }
    }

    // switch availability by remove new provider availability_id and add old provider to availability table
    public function switch_availability($new_provider_id, $provider_id, $booking_id) {
        // get data from booking like service duration and date
        $bookingData = Booking::where('booking.id', $booking_id)
            ->join('booking_service', 'booking_service.booking_id', '=', 'booking.id')
            ->join('ve_services', 've_services.id', '=', 'booking_service.service_id')
            ->select('booking.date as date', 've_services.duration as duration')
            ->first();
        $duration = $bookingData->duration;
        $date = $bookingData->date;
        //        $date = date('Y-m-d', strtotime($date->date));
        // return date with from date
        $date = date('Y-m-d', strtotime($date));
        $from = date('H:i:s', strtotime($bookingData->date));
        $from = ltrim($from, '0');
        $from = str_replace(':00', '', $from);
        //$hours_diff = $duration->h + ($duration->days * 24);
        for ($i = 0; $i < $duration; $i++) {
            Avaliabilty::where('provider_id', $new_provider_id)
                ->where('date', $date)
                ->where('from', $from + $i)
                ->where('to', $from + $i + 1)
                ->where('duration', 1)
                ->delete();
        }
        // count number of hours so will create rows for new provider based on it
        for ($i = 0; $i < $duration; $i++) {
            Avaliabilty::create([
                'provider_id' => $provider_id,
                'date' => $date,
                'from' => $from + $i,
                'to' => $from + $i + 1,
                'duration' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }


    private function getAvailableSlots($data, $duration,) {
        $available_slots = [];
        $slots = [];
        foreach ($data->groupBy('date') as $day => $slots) {
            $date_key = Carbon::parse($day)->format('j/n/Y');
            $provider_slots = $slots->groupBy('provider_id');
            foreach ($provider_slots as $provider_id => $ps) {
                if (count($ps) >= $duration) {
                    $sortedSlots = $ps->sortBy('from');
                    if (Carbon::parse($day)->isToday()) {
                        $sortedSlots = $sortedSlots->filter(function ($s) {
                            return $s->from > Carbon::now()->hour;
                        });
                    }
                    $requiredSlots = $duration / 0.5;
                    $chunks = $sortedSlots->chunk($requiredSlots);
                    if (!isset($available_slots[$date_key])) {
                        $available_slots[$date_key] = [];
                    }
                    $filteredChunks = $chunks->filter(function ($chunk) use ($requiredSlots) {
                        return count($chunk) == $requiredSlots;
                    });
                    foreach ($filteredChunks as $chunk) {
                        $from = $chunk->first()->from;
                        $to = $chunk->last()->to;
                        array_push($available_slots[$date_key], [
                            'from' => $from,
                            'to' => $to,
                            'provider_id' => $provider_id,
                            'availability_ids' => $chunk->pluck('availability_id'),
                        ]);
                    }
                }
            }
        }
        return $available_slots;
    }

    private function prioritizeSlots($available_slots, $count, $total) {
        $data = [];
        foreach ($available_slots as $date => $available_slot) {
            $groupedSlots = collect($available_slot)->groupBy('from')->filter(function ($available_slot) use ($count) {
                return count($available_slot) >= $count;
            });
            foreach ($groupedSlots as $slot) {
                if (!isset($data[$date])) {
                    $data[$date] = [];
                }
                $tmp = [
                    'date' => $date,
                    'from' => $slot[0]['from'],
                    'to' => $slot[0]['to'],
                    'total' => $total,
                    'providers' => collect($slot->slice(0, $count))->map(function ($p) {
                        return $p;
                    })
                ];
                array_push($data[$date], $tmp);
            }
        }
        return $data;
    }

    public function checkAvailabilityOneProvider($booking_id) {
        $service = BookingService::where('booking_service.booking_id', $booking_id)
            ->join('ve_services', 've_services.id', '=', 'booking_service.service_id')
            ->select('ve_services.id as service_id', 've_services.duration as duration')
            ->first();
        $area_id = Booking::where('id', $booking_id)->pluck('area_id')->first();
        $datetime = Booking::where('id', $booking_id)->first();
        return $this->getAvailableProviders($area_id, $service, $datetime);
    }

    private function getAvailableProviders($area_id, $service, $datetime) {
        $date = date('Y-m-d', strtotime($datetime->date));
        $time = date('H:i:s', strtotime($datetime->date));
        $time = ltrim($time, '0');
        $time = str_replace(':00', '', $time);
        $to = date('H:i:s', strtotime($datetime->date . ' + ' . ($service->duration - 1) . ' hours'));
        $to = ltrim($to, '0');
        $to = str_replace(':00', '', $to);
        $data = Avaliabilty::join('providers', 'providers.id', '=', 'avaliabilty.provider_id')
            ->join('area_providers', 'area_providers.provider_id', '=', 'providers.id')
            ->join('service_provider', 'service_provider.provider_id', '=', 'providers.id')
            ->join('users', 'users.id', '=', 'providers.user_id')
            ->where('service_provider.service_id', '=', $service->service_id)
            ->where('area_providers.area_id', $area_id)
            ->whereDate('avaliabilty.date', '=', $date)
            ->where('avaliabilty.from', '=', $time)
            ->where('avaliabilty.from', '<=', $to)
            ->whereNotIn('providers.id', function ($query) use ($datetime) {
                $query->select('provider_id')
                    ->from('booking_provider')
                    ->join('booking', 'booking.id', '=', 'booking_provider.booking_id')
                    ->where('booking.date', '=', $datetime->date);
            })
            ->select(
                'providers.id as id',
                DB::raw("CONCAT(SUBSTRING(users.first_name, 1, 1), '.', (users.last_name), '.') as name"),
                'providers.rank',
                'providers.rating',
            )
            ->groupBy(
                'providers.id',
                'users.first_name',
                'users.last_name',
            )
            ->orderBy('providers.id', 'desc')
            ->get();

        $data = $data->map(function ($item) {
            $item->rank = intval($item->rank);
            $item->rating = intval($item->rating);
            return $item;
        });

        return $data;
    }


    public function offdaysAdd($id, $provider_id = null) {
        $data = OffDays::find($id);
        if ($data) {
            $query = Avaliabilty::whereDate('date', '>=', $data->from)
                ->whereDate('date', '<=', $data->to);
            if ($provider_id) {
                $query->where('provider_id', $provider_id);
            }
            $query->delete();
        }
    }

    public function offdaysEditAll($id, $provider_id = null) {
        if ($provider_id) {
            // then will add avaliability for provider from date to date
            $this->offdaysDelete($id, $provider_id);
            $this->offdaysAdd($id, $provider_id);
        } else {
            $this->offdaysDelete($id);
            $this->offdaysAdd($id);
        }
    }
    public function offdaysDelete($id, $provider_id = null) {
        $data = OffDays::where('id', $id)->first();
        if ($data != null) {
            if (!$provider_id) {
                Artisan::call(
                    'app:add-provider-availability',
                    [
                        '--start-date' => $data->from,
                        '--end-date' => $data->to,
                    ]
                );
            } else {
                Artisan::call(
                    'app:add-provider-availability',
                    [
                        '--start-date' => $data->from,
                        '--end-date' => $data->to,
                        '--providers' => $provider_id
                    ]
                );
            }
        }
    }


    public function workingSchedule($provider_id = null) {
        if ($provider_id) {
            Avaliabilty::where('provider_id', $provider_id)->delete();
            Artisan::call(
                'app:add-provider-availability',
                [
                    '--latest' => false,
                    '--start-date' => now(),
                    '--end-date' => now()->addDays(90),
                    '--providers' => $provider_id,
                ]
            );
        }
        // this will handle for vendor working schedule
        //        Avaliabilty::truncate();
        //        Artisan::call('app:add-provider-availability',
        //            [
        //                     '--latest' => false,
        //                     '--start-date' => now(),
        //                     '--end-date' => now()->addDays(30),
        //                    // '--providers' =>
        //            ]);
    }
}
