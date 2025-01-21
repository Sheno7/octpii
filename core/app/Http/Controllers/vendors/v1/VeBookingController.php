<?php

namespace App\Http\Controllers\vendors\v1;

use App\Enums\BookingSource;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\CompleteBooking;
use App\Http\Requests\Booking\GetBookings;
use App\Http\Resources\BookingResource;
use App\Models\ActionReason;
use App\Models\Address;
use App\Models\Avaliabilty;
use App\Models\Booking;
use App\Models\BookingProvider;
use App\Models\BookingService;
use App\Models\Package;
use App\Models\PackageServices;
use App\Models\Providers;
use App\Models\VeServices;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VeBookingController extends Controller {
    use ResponseTrait;

    public function index(GetBookings $request) {
        try {
            $bookings = Booking::with(['providers', 'services', 'customer', 'area.city'])->orderBy('date', 'desc');

            $this->applyFilters($bookings, $request);

            $bookings = $bookings->paginate(10);

            $bookings->data = BookingResource::collection($bookings);

            return $this->getSuccessResponse('success', $bookings);
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function listCustomerBookings(Request $request) {
        try {
            $bookings = Booking::with(['providers', 'services', 'customer', 'area.city'])->orderBy('date', 'desc');

            $bookings->where('customer_id', $request->id);

            $bookings = $bookings->paginate(10);

            $bookings->data = BookingResource::collection($bookings);

            return $this->getSuccessResponse('success', $bookings);
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    private function applyFilters($query, $request) {
        $query->when($request->has('id'), function ($q) use ($request) {
            $q->where('booking.id', $request->input('id'));
        });

        $query->when($request->has('booking_at_start'), function ($q) use ($request) {
            $startDate = $request->input('booking_at_start');

            $q->when($request->has('booking_at_end'), function ($q) use ($request, $startDate) {
                $endDate = $request->input('booking_at_end');
                $q->whereBetween(DB::raw('DATE(booking.date)'), [$startDate, $endDate]);
            }, function ($q) use ($startDate) {
                $q->where(DB::raw('DATE(booking.date)'), '>=', $startDate);
            });
        });

        $query->when($request->has('created_at_start'), function ($q) use ($request) {
            $startDate = $request->input('created_at_start');

            $q->when($request->has('created_at_end'), function ($q) use ($request, $startDate) {
                $endDate = $request->input('created_at_end');
                $q->whereBetween(DB::raw('DATE(booking.created_at)'), [$startDate, $endDate]);
            }, function ($q) use ($startDate) {
                $q->whereDate(DB::raw('DATE(booking.created_at)'), '>=', $startDate);
            });
        });

        $query->when($request->has('status'), function ($q) use ($request) {
            $q->where('booking.status', $request->input('status'));
        });

        $query->when($request->has('services'), function ($q) use ($request) {
            $serviceIds = explode(',', $request->input('services'));
            $q->whereHas('services', function ($qs) use ($serviceIds) {
                $qs->whereIn('service_id', $serviceIds);
            });
        });

        $query->when($request->has('customer_phone'), function ($q) use ($request) {
            $q->whereHas('customer.user', function ($qc) use ($request) {
                $qc->where('phone', 'like', "%{$request->input('customer_phone')}%");
            });
        });

        $query->when(!empty($request->user()->customer), function ($q) use ($request) {
            $q->where('customer_id', $request->user()->customer->id);
        });

        $query->when($request->has('provider_phone'), function ($q) use ($request) {
            $providerPhone = $request->input('provider_phone');
            $q->whereHas('providers.user', function ($query) use ($providerPhone) {
                $query->where('phone', 'like', "%{$providerPhone}%");
            });
        });

        $query->when($request->has('source'), function ($q) use ($request) {
            $q->where('source', $request->input('source'));
        });

        $query->when($request->has('coupon_id'), function ($q) use ($request) {
            $q->where('coupon_id', $request->input('coupon_id'));
        });

        $query->when($request->filled('canceled') && $request->input('canceled') === 'true', function () {
            // Include canceled bookings
        }, function ($q) {
            // Exclude canceled bookings
            $q->where('status', '!=', Status::BOOKINGCANCELLED);
        });
    }

    private function random_payment_method() {
        $paymentMethods = ['cash'];
        return $paymentMethods[array_rand($paymentMethods)];
    }

    private function check_address($service_id) {
        return VeServices::where('id', $service_id)->value('service_location');
    }

    public function add(Request $request) {
        try {
            DB::beginTransaction();
            $data = $request->validate([
                'service' => 'required|integer|exists:ve_services,id',
                'date' => 'required|date',
                'customer_id' => 'integer',
                //'address_id' => 'required|integer|exists:address,id',
                'gender' => 'required|integer|in:-1,0,1',
                'availability' => ['required', 'array'],
                'availability.*' => ['required', 'integer'],
                'total' => 'required|integer',
                'notes' => 'nullable|string|max:255',
                'package_id' => 'nullable|integer|exists:package,id',
            ]);
            if (!isset($data['address_id'])) {
                $data['address_id'] = 0;
            }

            $source = $request->get('source', BookingSource::WEB);
            if ($source !== BookingSource::MARKETPLACE) {
                $user = Auth::user();
                if (empty($user->customer)) {
                    $request->validate([
                        'customer_id' => 'required|integer|exists:customers,id',
                    ]);
                } else {
                    $data['customer_id'] = $user->customer->id;
                    $source = BookingSource::MOBILE;
                }
            }

            $service_location = $this->check_address($data['service']);

            $availabilityHours = Avaliabilty::where('id', $data['availability'][0])->pluck('from')->first();

            // Check if $availabilityHours is a valid number
            if (is_numeric($availabilityHours)) {
                // Convert to hours and minutes
                $hours = floor($availabilityHours);
                $minutes = ($availabilityHours - $hours) * 60;

                // Use Carbon for date manipulation
                $date = Carbon::parse($data['date'])->addHours($hours)->addMinutes($minutes)->format('Y-m-d H:i:s');
            } else {
                $date = null;
            }

            $service = VeServices::find($data['service']);

            $booking = Booking::create([
                'branch_id' => $request->get('branch_id', $request->get('selected_branch', null)),
                'customer_id' => $data['customer_id'],
                'date' => $date,
                'duration' => $service->duration,
                'gender_prefrence' => $data['gender'],
                'is_favourite' => Status::INACTIVE,
                'address_id' => $service_location == 1 ? $data['address_id'] : 0,
                'area_id' => $service_location == 1 ? Address::where('id', $data['address_id'])->first()->area_id : 0,
                'coupon_id' => Status::INACTIVE,
                'status' => Status::INACTIVE,
                'source' => $source,
                'notes' => isset($data['notes']) ? $data['notes'] : null,
                'total' => $data['total'],
                'package_id' => isset($data['package_id']) ? $data['package_id'] : null,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            BookingService::create([
                'booking_id' => $booking->id,
                'service_id' => $data['service'],
                'price' => $data['total'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $package_id = $this->check_existing_package($request->customer_id, $service->category?->sector_id);
            if ($package_id !== null) {
                $this->add_service_to_package($package_id, $request->service);
                $booking->update(['package_id' => $package_id]);
            }
            $matcher = new VeMatcherController();
            foreach ($data['availability'] as $availability) {
                $availabilityModel = Avaliabilty::where('id', $availability)->first();
                if (!$availabilityModel) {
                    return $this->getErrorResponse('error', 'No availability found.');
                }
                BookingProvider::firstOrCreate([
                    'booking_id' => $booking->id,
                    'provider_id' => $availabilityModel->provider_id,
                ], ['status' => Status::ACTIVE]);
                $matcher->delete_availability($availability);
            }
            DB::commit();
            return $this->getSuccessResponse('success', $booking);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    private function check_existing_package($customer_id, $sector_id) {
        try {
            $package = Package::where('customer_id', $customer_id)
                ->whereIn('status', [Status::PACKAGESERVICEPENDING, Status::PACKAGESERVICESTARTED])
                ->first();
            if (!empty($package) && $sector_id === $package->services[0]->category?->sector_id) {
                return $package->id;
            }
            return null;
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }
    private function add_service_to_package($package_id, $service_id) {
        try {
            $packageService = PackageServices::where('package_id', $package_id)
                ->where('service_id', $service_id)
                ->first();
            if (!$packageService) {
                $service = VeServices::find($service_id);
                PackageServices::create([
                    'package_id' => $package_id,
                    'service_id' => $service_id,
                    'price' => $service->cost,
                    'status' => Status::ACTIVE
                ]);
            }
            return $this->getSuccessResponse('success');
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function show(Request $request) {
        try {
            $request->validate([
                'id' => 'required|integer|exists:booking,id',
            ]);
            $booking = Booking::findOrFail($request->id);
            $booking = new BookingResource($booking, true);
            return $this->getSuccessResponse('Booking retrieved successfully', $booking);
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function edit(Request $request) {
        try {
            $request->validate([
                'booking_id' => 'required|integer|exists:booking,id',
                'provider_id' => [
                    'integer',
                    'exists:providers,id',
                    Rule::exists('booking_provider')->where(function ($query) use ($request) {
                        $query->where('booking_id', $request->booking_id)
                            ->where('provider_id', $request->provider_id);
                    }),
                ],
                'feedback' => 'string|max:255',
            ]);

            $booking = Booking::find($request->booking_id)
                ->where('status', Status::BOOKINGPENDING)
                ->orWhere('status', Status::BOOKINGSTARTED)->first();

            if (!$booking) {
                return $this->getErrorResponse('error', 'No booking found.');
            }

            if ($request->has('feedback')) {
                Booking::where('id', $request->booking_id)
                    ->update(['feedback' => $request->feedback, 'updated_at' => now()]);
                return $this->getSuccessResponse('success', 'Feedback updated successfully.');
            }

            $matcher = new VeMatcherController();

            if ($request->has('booking_id') && !$request->has(['provider_id', 'new_provider_id'])) {
                $data = $matcher->checkAvailabilityOneProvider($request->booking_id);
                if (empty($data)) {
                    return $this->getErrorResponse('error', 'No provider found.');
                }
                return $this->getSuccessResponse('success', $data);
            }

            if ($request->has('provider_id') && $request->has('booking_id') && $request->has('new_provider_id')) {
                DB::table('booking_provider')
                    ->where('booking_id', $request->booking_id)
                    ->where('provider_id', $request->provider_id)->delete();

                // chceck before insert to BookingProvider
                $bookingNewProvider = BookingProvider::where('booking_id', $request->booking_id)
                    ->where('provider_id', $request->new_provider_id)->first();
                if (!$bookingNewProvider) {
                    BookingProvider::create([
                        'booking_id' => $request->booking_id,
                        'provider_id' => $request->new_provider_id,
                        'commission_type' => $request->commission_typ ?? 0,
                        'commission_amount' => $request->commission_amount ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Make sure to define and use the correct arguments in switch_availability
                $matcher->switch_availability($request->new_provider_id, $request->provider_id, $request->booking_id);
                return $this->getSuccessResponse('success', 'Provider switched successfully.');
            }
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function cancel(Request $request) {
        $request->validate([
            'id' => 'required|integer|exists:booking,id',
            'reason' => 'required|string|max:255',
        ]);
        try {

            $user = Auth::user();
            if (!empty($user->customer)) {
                $request->validate([
                    'id' => 'required|integer|exists:booking,id,customer_id,' . $user->customer->id,
                ]);
            }

            $booking = Booking::findorfail($request->id);

            if (!$booking || !in_array($booking->status, [Status::BOOKINGPENDING, Status::BOOKINGSTARTED])) {
                return $this->getErrorResponse('error', 'No booking found');
            }
            $booking->update(['status' => Status::BOOKINGCANCELLED, 'updated_at' => now()]);
            // add action reason with booking ID
            ActionReason::create([
                'action' => 1,
                'reason' => $request->reason,
                'booking_id' => $request->id,
                'status' => Status::ACTIVE,
                'action_by' => auth()->user()->id,
            ]);
            $matcher = new VeMatcherController();
            $matcher->add_availability($booking->id);

            return $this->getSuccessResponse('success', 'Booking cancelled successfully.');
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function list_canceled() {
        try {
            $bookings = Booking::join('action_reason', 'action_reason.booking_id', '=', 'booking.id')
                ->join('users', 'users.id', '=', 'action_reason.action_by')
                ->select(
                    'booking.id as id',
                    'action_reason.id as reason_id',
                    'action_reason.reason as reason',
                    'action_reason.created_at',
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS action_by")
                )
                //   'users.first_name as action_by')
                ->where('booking.status', Status::BOOKINGCANCELLED)
                ->orderBy('booking.updated_at', 'desc')
                ->paginate(10);
            return $this->getSuccessResponse('success', $bookings);
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function assign_provider(Request $request) {
        try {
            $check = Providers::where('id', $request->id)->first();
            if (!$check) {
                return $this->getErrorResponse('error', 'No provider found.');
            }
            // update booking provider table assign collector in booking provider table
            BookingProvider::where('booking_id', $request->booking_id)
                ->update(['provider_id' => $request->id, 'updated_at' => now()]);
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function update_status(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:booking,id',
                'status' => 'required|integer|in:1,2',
            ]);
            if ($validator->fails()) {
                return $this->getValidationErrorResponse($validator->errors()->first());
            }
            $booking = Booking::findOrFail($request->id);
            if ($booking->status == Status::BOOKINGCOMPLETED) {
                return $this->getErrorResponse('error', 'Booking already completed.');
            }
            if ($request->status < $booking->status) {
                return $this->getErrorResponse('error', 'Status should be greater than current status.');
            }
            $booking->update(['status' => $request->status, 'updated_at' => now()]);
            return $this->getSuccessResponse('success', 'Booking status updated successfully.');
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function complete_booking(CompleteBooking $request) {
        $inputs = $request->validated();
        $booking = Booking::where('id', $inputs['id'])->first();
        if (!empty($booking->package_id)) {
            $request->validate(CompleteBooking::services_rules($booking->package_id));
        }
        try {
            // start transaction
            DB::beginTransaction();
            $booking->status = Status::BOOKINGCOMPLETED;
            if (!empty($inputs['feedback'])) {
                $booking->feedback = $booking->feedback . ' --- ' . now() . ' --- ' . $inputs['notes'];
            }
            $total = 0;

            foreach ($inputs['services'] as $service) {
                $booking_service_updates = [
                    'status' => Status::BOOKINGSTARTED,
                ];
                $package_service_updates = [
                    'status' => Status::PACKAGESERVICESTARTED,
                ];
                if ($service['price'] >= 0) {
                    $total += floatval($service['price']);
                    $booking_service_updates = [
                        'status' => Status::BOOKINGCOMPLETED,
                        'price' => $service['price'],
                    ];
                    $package_service_updates = [
                        'status' => Status::PACKAGESERVICECOMPLETED,
                        'price' => $service['price'],
                    ];
                }

                if ($total > 0) {
                    $booking->total = $total;
                }
                $booking->save();

                BookingService::updateOrCreate(
                    [
                        'booking_id' => $request->id,
                        'service_id' => $service['id'],
                    ],
                    $booking_service_updates
                );

                if ($booking->package_id) {
                    PackageServices::where('package_id', $booking->package_id)
                        ->where('service_id', $service['id'])
                        ->update($package_service_updates);
                }
            }
            // add check for complete package if all of service is completed
            $packages = new VePackagesController();
            $packages->completePlan($booking->package_id);
            DB::commit();
            return $this->getSuccessResponse('success', 'Booking completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }
}
