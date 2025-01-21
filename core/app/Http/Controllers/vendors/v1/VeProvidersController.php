<?php

namespace App\Http\Controllers\vendors\v1;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Resources\ProviderResource;
use App\Models\ActionReason;
use App\Models\Address;
use App\Models\AreaProviders;
use App\Models\Areas;
use App\Models\Avaliabilty;
use App\Models\Booking;
use App\Models\BookingProvider;
use App\Models\OffDays;
use App\Models\Providers;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\VeServices;
use App\Models\WorkingSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Validation\Rule;
use App\Traits\DayTrait;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Artisan;

class VeProvidersController extends Controller {

    use DayTrait, ResponseTrait;
    public function index(Request $request) {
        try {
            $query = Providers::with([
                'user:id,first_name,last_name',
                'services:id,title_en,title_ar',
                'areas:id,title_en,title_ar',
            ])->withCount([
                'bookings as total_bookings',
                'services as services_count',
                'areas as areas_count',
            ]);

            if ($request->has('phone')) {
                $query->whereHas('user', function ($userQuery) use ($request) {
                    $userQuery->where('phone', 'like', "%{$request->input('phone')}%");
                });
            }

            if ($request->has('rank')) {
                $query->where('rank', $request->input('rank'));
            }

            if ($request->has('service_id')) {
                $query->whereHas('services', function ($serviceQuery) use ($request) {
                    $serviceQuery->where('service_id', $request->input('service_id'));
                });
            }

            if ($request->has('area_id')) {
                $query->whereHas('areas', function ($areaQuery) use ($request) {
                    $areaQuery->where('area_id', $request->input('area_id'));
                });
            }

            $data = $query->paginate(10);
            $data->data = ProviderResource::collection($data);

            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function dropdown(Request $request) {
        try {
            // $data = DB::table('providers')
            //     ->join('users', 'users.id', 'providers.user_id')
            //     ->select('providers.id', 'users.first_name', 'users.last_name', 'users.phone')
            //     ->where(DB::raw('LOWER(users.first_name)'), 'LIKE', '%' . strtolower($request->search) . '%')
            //     ->orWhereRaw('LOWER(users.last_name) LIKE ?', ['%' . strtolower($request->search) . '%'])
            //     ->orWhereRaw('users.phone LIKE ?', ['%' . $request->search . '%'])
            //     ->orderBy('providers.id', 'desc')
            //     ->paginate(10);


            $matchedProviders = Providers::whereHas('user', function ($query) use ($request) {
                if (!empty($request->search)) {
                    $query->where(DB::raw('LOWER(users.first_name)'), 'LIKE', '%' . strtolower($request->search) . '%')
                        ->orWhereRaw('LOWER(users.last_name) LIKE ?', ['%' . strtolower($request->search) . '%'])
                        ->orWhereRaw('users.phone LIKE ?', ['%' . $request->search . '%']);
                }
            })
                ->where('status', 1)
                ->whereHas('services', function ($query) use ($request) {
                    if (!empty($request->service_id)) {
                        $query->where('service_id', $request->service_id);
                    }
                })->paginate(10);

            $matchedProviders->data = ProviderResource::collection($matchedProviders);

            return $this->getSuccessResponse('success', $matchedProviders);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function add(Request $request) {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['nullable', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:255'],
                'country_id' => ['required', 'integer', 'exists:countries,id'],
                'gender' => ['required', 'integer'],
                'start_date' => ['date'],
                'dob' => ['nullable', 'date'],
                'salary' => ['required', 'numeric'],
                'rank' => ['required', 'integer'],
                'commission_type' => ['required', 'integer'],
                'commission_amount' => ['required', 'numeric'],
                'area_id' => ['nullable', 'integer', 'exists:areas,id'],
                'location_name' => ['required', 'string'],
                'unit_type' => ['integer'],
                'unit_size' => ['required', 'integer'],
                //  'street_name' => ['string'],
                // 'building_number' => ['string'],
                'services' => ['required', 'array'],
                'services.*' => ['required', 'integer', 'exists:ve_services,id'],
                'areas' => ['sometimes', 'array'],
                'areas.*' => ['sometimes', 'integer', 'exists:areas,id'],
                'schedule' => ['required', 'array'],
                'schedule.*.day' => [
                    'required',
                    'string',
                    'in:Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday'
                ],
                'schedule.*.off' => ['required', 'boolean'],
                'schedule.*.from' => ['required_if:schedule.*.off,false', 'numeric'],
                'schedule.*.to' => ['required_if:schedule.*.off,false', 'numeric', 'gt:schedule.*.from'],
            ]);
            if ($validator->fails()) {
                return $this->getValidationErrorResponse($validator->errors(), 422);
            }
            $inputs = $validator->validated();

            $user = User::where('phone', $inputs['phone'])->where('country_id', $inputs['country_id'])->first();
            if (empty($user)) {
                $user = User::create([
                    'first_name' => $inputs['first_name'],
                    'last_name' => $inputs['last_name'],
                    'name' => $inputs['first_name'] . ' ' . $inputs['last_name'],
                    'phone' => $inputs['phone'],
                    'country_id' => $inputs['country_id'],
                    'email' => isset($inputs['email']) ? $inputs['email'] : null,
                    'gender' => $inputs['gender'],
                    'dob' => isset($inputs['dob']) ? $inputs['dob'] : null,
                    'password' => bcrypt(random_int(1, 1000)),
                ]);
            }

            $user->assignRole('provider');
            // add provider
            $provider = $user->provider()->create([
                'rank' => $inputs['rank'],
                'salary' => $inputs['salary'],
                'commission_type' => $inputs['commission_type'],
                'commission_amount' => $inputs['commission_amount'],
                'rating' => $inputs['rating'] ?? 0,
                'start_date' => $inputs['start_date'] ?? null,
                'branch_id' => $request->get('branch_id', $request->get('selected_branch', null)),
                'address_id' => 0,
                'status' => 1,
            ]);
            // add provider address
            if (empty($inputs['area_id'])) {
                $inputs['area_id'] = 0; // Areas::first()->id;
            }
            $address = $provider->address()->create([
                'area_id' => $inputs['area_id'],
                'owner_id' => $provider->id,
                'owner_type' => 2,
                'location_name' => $inputs['location_name'],
                'unit_type' => $inputs['unit_type'],
                'unit_size' => $inputs['unit_size'],
                'street_name' => $inputs['street_name'] ?? '',
                'building_number' => $inputs['especial_marque'] ?? '',
                'floor_number' => isset($inputs['floor_number']) ? $inputs['floor_number'] : null,
                'unit_number' => isset($inputs['unit_number']) ? $inputs['unit_number'] : null,
                'notes' => isset($inputs['notes']) ? $inputs['notes'] : null,
            ]);
            // update provider with address id
            $provider->address_id = $address->id;
            $provider->save();
            // insert data to table service_provider
            foreach ($request->services as $service) {
                ServiceProvider::create([
                    'service_id' => $service,
                    'provider_id' => $provider->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($request->services ?? [] as $service) {
                $service_location = VeServices::where('id', $service)->value('service_location');
                if ($service_location == 1) {
                    foreach ($request->areas ?? [$inputs['area_id']] as $area) {
                        AreaProviders::create([
                            'area_id' => $area,
                            'provider_id' => $provider->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            foreach ($request->schedule as $schedule) {
                if (!$schedule['off']) {
                    WorkingSchedule::create([
                        'day' => $this->getDayId($schedule['day']),
                        'from' => $schedule['from'] ?? null,
                        'to' => $schedule['to'] ?? null,
                        'provider_id' => $provider->id,
                        'branch_id' => $request->get('branch_id', $request->get('selected_branch', null)),
                    ]);
                }
            }
            DB::commit();
            Avaliabilty::truncate();
            Artisan::call('app:add-provider-availability');
            return $this->getSuccessResponse('success', $provider);
        } catch (\Throwable $th) {
            DB::rollBack();
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function edit(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'integer', 'exists:providers,id'],
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:255'],
                'country_id' => ['required', 'integer', 'exists:countries,id'],
                'gender' => ['integer'],
                'salary' => ['numeric'],
                'rank' => ['integer'],
                'commission_type' => ['integer'],
                'commission_amount' => ['numeric'],
                // 'area_id' => ['required', 'integer', 'exists:areas,id'],
                'location_name' => ['string'],
                // 'unit_type' => ['integer'],
                //'unit_size' => ['integer'],
                //  'street_name' => ['string'],
            ]);
            if ($validator->fails()) {
                return $this->getValidationErrorResponse($validator->errors());
            }
            $provider = Providers::find($request->id);
            if (!$provider) {
                return $this->getErrorResponse('not_found', 'Provider not found');
            }
            // update user table
            $user = User::find($provider->user_id);
            if (!$user) {
                return $this->getErrorResponse('not_found', 'User not found');
            }
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->name = $request->first_name . ' ' . $request->last_name;
            $user->phone = $request->phone;
            $user->country_id = $request->country_id;
            $user->email = $request->email;
            $user->gender = $request->gender;
            $user->dob = $request->dob;
            $user->updated_at = now();
            $user->save();
            $provider->salary = $request->salary;
            $provider->rank = $request->rank;
            $provider->commission_type = $request->commission_type ?? 0;
            $provider->commission_amount = $request->commission_amount ?? 0;
            $provider->start_date = $request->start_date;
            $provider->resign_date = $request->resign_date;
            $provider->status = $request->status;
            $provider->branch_id = $request->get('branch_id', $request->get('selected_branch'));
            $provider->updated_at = now();
            $provider->save();
            $service_location = VeServices::where('service_location', 0)->count();
            if ($service_location ==  0) {
                $addressId = Address::where('owner_id', $provider->id)
                    ->where('owner_type', 2)->first()->id;
                $address = Address::find($addressId);
                if (!$addressId) {
                    return $this->getErrorResponse('not_found', 'Address not found');
                }
                // $address->area_id = $request->area_id;
                $address->location_name = $request->location_name;
                $address->unit_type = $request->unit_type ?? 0;
                $address->unit_size = $request->unit_size ?? 0;
                $address->street_name = $request->street_name ??
                    $address->building_number = $request->building_number;
                $address->floor_number = $request->floor_number;
                $address->unit_number = $request->unit_number;
                $address->notes = $request->notes;
                $address->updated_at = now();
                $address->save();
            }
            return $this->getSuccessResponse('success');
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function show(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'integer', 'exists:providers,id']
            ]);
            if ($validator->fails()) {
                return $this->getValidationErrorResponse($validator->errors(), 422);
            }
            $provider = Providers::find($request->id);
            if (!$provider) {
                return $this->getErrorResponse('not_found', 'Provider not found');
            }
            $data = Providers::select(
                'providers.id as id',
                'users.id as user_id',
                'users.phone as phone',
                //  'countries.code as code',
                'users.gender as gender',
                'users.dob as dob',
                'users.email as email',
                'users.first_name as first_name',
                'users.last_name as last_name',
                'providers.start_date as start_date',
                'providers.resign_date as resign_date',
                'providers.rank as rank',
                'providers.rating as rating',
                'providers.salary as salary',
                'providers.commission_type',
                'providers.commission_amount',
                'providers.address_id as address_id',
                // 'address.location_name as location_name',
                // 'address.unit_type as unit_type',
                // 'address.unit_size as unit_size',
                // 'address.street_name as street_name',
                // 'address.building_number as building_number',
                // 'address.floor_number as floor_number',
                // 'address.unit_number as unit_number',
                // 'address.notes as notes',
                // 'cities.id as city_id',
                // 'cities.title_en as city_title',
                // 'areas.id as area_id',
                // 'areas.title_en as area_title',
                'providers.status as status',
                'providers.created_at as created_at'
            )
                ->join('users', 'users.id', '=', 'providers.user_id')
                // ->join('address', 'address.owner_id', '=', 'providers.id')
                // ->leftjoin('areas', 'areas.id', '=', 'address.area_id')
                ->join('countries', 'countries.id', '=', 'users.country_id')
                // ->join('cities', 'cities.id', '=', 'areas.city_id')
                // ->where('address.owner_type', 2)
                ->where('providers.id', $request->id)->first();
            $data->total_booking = BookingProvider::where('provider_id', $request->id)->count();
            $data->total_eanings = 0;
            $data->balance = 0;
            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function listAreasCoverd(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'integer', 'exists:providers,id']
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ]);
            }
            $data = DB::table('area_providers')
                ->select(
                    'area_providers.id',
                    'provider_id',
                    'area_providers.id',
                    'areas.id as area_id',
                    'areas.title_en as area_title',
                    'cities.id as city_id',
                    'cities.title_en as city_title',
                    'area_providers.created_at',
                    'area_providers.updated_at'
                )
                ->join('areas', 'areas.id', '=', 'area_providers.area_id')
                ->join('cities', 'cities.id', '=', 'areas.city_id')
                ->where('provider_id', $request->id)
                ->paginate(10);
            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return response()->json([
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function addAndRemoveArea(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'integer', 'exists:providers,id'],
                'area_id' => ['required', 'integer', 'exists:areas,id'],
            ]);
            if ($validator->fails()) {
                return $this->getErrorResponse('validation_error', $validator->errors());
            }
            $check = DB::table('area_providers')
                ->where('provider_id', $request->id)
                ->where('area_id', $request->area_id)->first();
            $provider = Providers::find($request->id);
            if ($check) {
                $provider->areas()->detach($request->area_id);
                return $this->getSuccessResponse('success', 'area removed');
            } else {
                $provider->areas()->attach($request->area_id);
                return $this->getSuccessResponse('success', 'area added');
            }
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function ListServiceCoveredProviders(Request $request) {
        try {
            // validation
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'integer', 'exists:providers,id'],
            ]);
            if ($validator->fails()) {
                return $this->getErrorResponse('validation_error', $validator->errors());
            }
            $provider = Providers::find($request->id);
            if (!$provider) {
                return $this->getErrorResponse('not_found', 'Provider not found');
            }
            $service_title_column = app()->getLocale() === "ar" ?
                've_services.title_ar as service_title' : 've_services.title_en as service_title';
            $data = DB::table('service_provider')
                ->select(
                    'service_provider.id',
                    'service_provider.provider_id',
                    'service_provider.service_id',
                    $service_title_column,
                    'service_provider.created_at',
                    'service_provider.updated_at',
                    've_services.status as status',
                    'service_categories.sector_id as sector_id',
                )
                ->join('ve_services', 've_services.id', '=', 'service_provider.service_id')
                ->join('service_categories', 've_services.category_id', '=', 'service_categories.id')
                ->where('provider_id', $request->id)
                ->paginate(-1);
            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    // add and remove serviceid from provider
    public function addAndRemoveService(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'integer', 'exists:providers,id'],
                'service_id' => ['required', 'integer', 'exists:ve_services,id'],
            ]);
            if ($validator->fails()) {
                return $this->getErrorResponse('validation_error', $validator->errors());
            }
            $check = DB::table('service_provider')
                ->where('provider_id', $request->id)
                ->where('service_id', $request->service_id)->first();
            $provider = Providers::find($request->id);
            if ($check) {
                $provider->services()->detach($request->service_id);
                return $this->getSuccessResponse('success', 'service removed');
            } else {
                $provider->services()->attach($request->service_id);
                return $this->getSuccessResponse('success', 'service added');
            }
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }
    public function list_schedule(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'integer', 'exists:providers,id'],
            ]);
            if ($validator->fails()) {
                return $this->getErrorResponse('validation_error', $validator->errors());
            }
            $provider = Providers::find($request->id);
            if (!$provider) {
                return $this->getErrorResponse('error', 'provider not found');
            }
            $workingSchedule = WorkingSchedule::where('provider_id', $request->id)->get();
            $schedule = [];
            foreach ($this->week_days() as $day) {
                $dataarry = [
                    'day' => $day,
                    'off' => true,
                ];
                $match_schedule = $workingSchedule->first(function ($item) use ($day) {
                    return $this->getDayNamefromId($item->day) === $day;
                });
                if ($match_schedule) {
                    $dataarry['from'] = $match_schedule->from;
                    $dataarry['to'] = $match_schedule->to;
                    $dataarry['off'] = false;
                    $dataarry['created_at'] = $match_schedule->created_at;
                }
                $schedule[] = $dataarry;
            }
            return $this->getSuccessResponse('success', $schedule);
        } catch (\Throwable $th) {
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }
    public function list_bookings(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'integer', 'exists:providers,id'],
            ]);
            if ($validator->fails()) {
                return $this->getErrorResponse('validation_error', $validator->errors());
            }
            $provider = Providers::find($request->id);
            if (!$provider) {
                return $this->getErrorResponse('error', 'provider not found');
            }
            $bookings = $provider->bookings()->paginate(5);
            $bookings->data = BookingResource::collection($bookings);
            return $this->getSuccessResponse('success', $bookings);
        } catch (\Throwable $th) {
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function edit_schedule(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'provider_id' => ['required', 'integer', 'exists:providers,id'],
                'schedule' => ['required', 'array'],
                'schedule.*.day' => [
                    'required',
                    'string',
                    Rule::in($this->week_days())
                ],
                'schedule.*.from' => ['required_if:schedule.*.off,false', 'numeric'],
                'schedule.*.to' => ['required_if:schedule.*.off,false', 'numeric', 'gt:schedule.*.from']
            ]);
            if ($validator->fails()) {
                return $this->getErrorResponse('validation_error', $validator->errors(), 422);
            }
            $provider = Providers::find($request->provider_id);
            if (!$provider) {
                return $this->getErrorResponse('error', 'provider not found');
            }
            WorkingSchedule::where('provider_id', $request->provider_id)->delete();
            foreach ($request->schedule as $schedule_data) {
                if (isset($schedule_data['from']) && isset($schedule_data['to'])) {
                    WorkingSchedule::create([
                        'day' => $this->getDayId($schedule_data['day']),
                        'from' => $schedule_data['from'],
                        'to' => $schedule_data['to'],
                        'provider_id' => $request->provider_id,
                        'branch_id' => $request->get('branch_id', $request->get('selected_branch', null)),
                    ]);
                }
            }
            $matcher = new VeMatcherController();
            $matcher->workingSchedule($request->provider_id);

            $bookings = Booking::whereDate(DB::raw('DATE(date)'), '>=', now())
                ->whereIn('status', [Status::BOOKINGPENDING])
                ->whereHas('providers', function ($p) use ($request) {
                    $p->where('provider_id', $request->provider_id);
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

            return $this->getSuccessResponse('success');
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage(), 422);
        }
    }

    public function list_off_days(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
                'id' => ['required', 'integer'],
            ]);
            if ($validate->fails()) {
                return $this->getErrorResponse('validation_error', $validate->errors());
            }
            $provider = Providers::find($request->id);
            if (!$provider) {
                return $this->getErrorResponse('error', 'provider not found');
            }
            $currentMonthStart = now()->startOfMonth();
            $currentMonthEnd = now()->endOfMonth();
            $data = OffDays::where('provider_id', $request->id)
                ->where(function ($query) use ($currentMonthStart, $currentMonthEnd) {
                    $query->whereBetween('from', [$currentMonthStart, $currentMonthEnd])
                        ->orWhereBetween('to', [$currentMonthStart, $currentMonthEnd]);
                })
                ->get();
            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function add_edit_off_day(Request $request) {
        try {
            // Validation rules
            $validator = Validator::make($request->all(), [
                'provider_id' => 'required|integer|exists:providers,id',
                'from' => 'required|date|after_or_equal:today',
                'to' => 'required|date|after_or_equal:from',
                'title' => 'required|string|max:20'
            ]);

            if ($validator->fails()) {
                return $this->getErrorResponse('validation_error', $validator->errors());
            }

            DB::beginTransaction();

            // Check if an off day for the given provider and date range already exists
            $off_day = OffDays::where('provider_id', $request->provider_id)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('from', [$request->from, $request->to])
                        ->orWhereBetween('to', [$request->from, $request->to]);
                })->first();
            if ($off_day) {
                $off_day->update([
                    'from' => $request->from,
                    'to' => $request->to,
                    'title' => $request->title,
                    'updated_at' => now()
                ]);
            } else {
                $off_day = OffDays::create([
                    'provider_id' => $request->provider_id,
                    'from' => $request->from,
                    'to' => $request->to,
                    'title' => $request->title,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            $matcher = new VeMatcherController();
            $matcher->offdaysEditAll($off_day->id, $request->provider_id);
            DB::commit();

            Avaliabilty::truncate();
            Artisan::call('app:add-provider-availability');

            return $this->getSuccessResponse('success', $off_day);
        } catch (\Throwable $th) {
            DB::rollBack();
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function destroy_off_day(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
                'id' => 'required|exists:off_days,id'
            ]);
            if ($validate->fails()) {
                return $this->getErrorResponse('validation_error', $validate->errors());
            }
            $off_days = OffDays::where('id', $request->id)->exists();
            if (!$off_days) {
                return $this->getErrorResponse('error', 'not found');
            }
            //            $matcher = new VeMatcherController();
            //            $matcher->offdaysDelete($request->id, $request->provider_id);
            Artisan::call(
                'app:add-provider-availability',
                [
                    '--start-date' => $off_days->from,
                    '--end-date' => $off_days->to,
                    '--providers' => $request->id
                ]
            );
            OffDays::where('id', $request->id)->delete();

            Avaliabilty::truncate();
            Artisan::call('app:add-provider-availability');

            return $this->getSuccessResponse('success', 'deleted');
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }
}
