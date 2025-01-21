<?php

namespace App\Http\Controllers\markets\v1;

use App\Enums\BookingSource;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Controllers\vendors\v1\VeBookingController;
use App\Http\Controllers\vendors\v1\VeMatcherController;
use App\Http\Resources\Customer\BookingResource;
use App\Models\ActionReason;
use App\Models\Address;
use App\Models\Avaliabilty;
use App\Models\Booking;
use App\Models\BookingProvider;
use App\Models\BookingService;
use App\Models\BookingVendor;
use App\Models\MaVendor;
use App\Models\ServiceVendor;
use App\Models\Tenant;
use App\Models\VeServices;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaBookingController extends Controller {
    use ResponseTrait;

    public function index(Request $request) {
        $request->validate([
            'status' => ['nullable', 'in:0,1,2,3'],
        ]);
        try {
            $user = Auth::user();
            $bookings = Booking::where('customer_id', $user->customer?->id);
            if ($request->has('status')) {
                $bookings = $bookings->where('status', $request->get('status'));
            }
            $bookings = $bookings->orderBy('date', 'desc');
            $bookings = $bookings->paginate(10);
            $bookings->data = BookingResource::collection($bookings);

            return $this->getSuccessResponse('success', $bookings);
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function add(Request $request) {
        try {
            DB::beginTransaction();
            $data = $request->validate([
                'vendor_id' => 'required|exists:vendors,id',
                'address_id' => 'required|integer|exists:address,id',
                'payment_method_id' => 'required|integer|exists:payment_methods,id',
                'services' => 'required|array',
                'services.*.service_id' => 'required|integer|exists:service_vendor,ve_service_id',
                'services.*.availability_ids' => 'required|array',
                //'services.*.availability_ids.*' => 'required|integer|exists:availabilities,id',
                'services.*.availability_ids.*' => 'required|integer',
                'notes' => 'nullable|string|max:255',
            ]);

            if (!isset($data['address_id'])) {
                $data['address_id'] = 0;
            }

            $user = Auth::user();
            $source = BookingSource::MOBILE;

            $vendor = MaVendor::find($data['vendor_id']);
            $tenant = Tenant::find($vendor->tenant_id);

            foreach ($data['services'] as $srv) {
                $availability = $tenant->run(function () use ($srv) {
                    return Avaliabilty::where('id', $srv['availability_ids'][0])->first();
                });

                // Check if $availability is a valid from number
                if (is_numeric($availability->from)) {
                    // Convert to hours and minutes
                    $hours = floor($availability->from);
                    $minutes = ($availability->from - $hours) * 60;

                    // Use Carbon for date manipulation
                    $date = Carbon::parse($availability->date)->addHours($hours)->addMinutes($minutes)->format('Y-m-d H:i:s');
                } else {
                    $date = null;
                }

                $service = $tenant->run(function () use ($srv) {
                    return VeServices::find($srv['service_id']);
                });
                $maServiceId = ServiceVendor::where('vendor_id', $data['vendor_id'])->where('ve_service_id', $service->id)->first()->service_id;

                $booking = Booking::create([
                    'customer_id' => $user->customer->id,
                    'date' => $date,
                    'duration' => $service->duration,
                    'address_id' => $data['address_id'],
                    'area_id' => Address::where('id', $data['address_id'])->first()->area_id,
                    'coupon_id' => Status::INACTIVE,
                    'status' => Status::INACTIVE,
                    'source' => $source,
                    'total' => $service->cost + $service->base_price,
                    'notes' => isset($data['notes']) ? $data['notes'] : null,
                    'created_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                BookingService::create([
                    'booking_id' => $booking->id,
                    'service_id' => $maServiceId,
                    'price' => $service->cost + $service->base_price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                BookingVendor::firstOrCreate([
                    'booking_id' => $booking->id,
                    'vendor_id' => $data['vendor_id'],
                    // Calc Commission base vendor
                ], ['status' => Status::ACTIVE]);

                // Prepare data to send to VeBookingController
                $veBookingData = [
                    'service' => $service->id,
                    'date' => $booking->date,
                    'customer_id' => 0,
                    'gender' => 0,
                    'availability' => $srv['availability_ids'],
                    'total' => $booking->total,
                    'source' => BookingSource::MARKETPLACE,
                    'feedback' => [
                        'marketplace_booking_id' => $booking->id,
                    ],
                ];

                $providerIds = $tenant->run(function () use ($srv) {
                    // $matcher = new VeMatcherController();
                    $providerIds = [];
                    foreach ($srv['availability_ids'] as $availability) {
                        $availabilityModel = Avaliabilty::where('id', $availability)->first();
                        if (!$availabilityModel) {
                            return $this->getErrorResponse('error', 'No availability found.');
                        }
                        $providerIds[] = $availabilityModel->provider_id;
                        // $matcher->delete_availability($availability);
                    }
                    return $providerIds;
                });
                foreach ($providerIds as $id) {
                    BookingProvider::create([
                        'booking_id' => $booking->id,
                        'provider_id' => $id
                    ]);
                }

                $this->addBookingToVendor($veBookingData, $tenant);
            }
            DB::commit();
            return $this->getSuccessResponse('success');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function show(Request $request) {
        try {
            $request->validate([
                'id' => 'required|integer|exists:booking,id',
            ]);
            $booking = Booking::findOrFail($request->id);
            $booking = new BookingResource($booking);
            return $this->getSuccessResponse('Booking retrieved successfully', $booking);
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
                'action_by' => $user->id,
            ]);

            $bookingVendor = BookingVendor::where('booking_id', $request->id)->first();
            $vendor = MaVendor::find($bookingVendor->vendor_id);
            $tenant = Tenant::find($vendor->tenant_id);
            $tenant->run(function () use ($booking) {
                $this->add_availability($booking->date, $booking->bookingProvider->pluck('provider_id')->toArray(), $booking->duration);
            });

            return $this->getSuccessResponse('success', 'Booking cancelled successfully.');
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    private function addBookingToVendor($bookingData, $tenant) {
        $tenant->run(function () use ($bookingData) {
            $veBookingController = app(VeBookingController::class);
            $veBookingRequest = new Request($bookingData);
            $veBookingResponse = $veBookingController->add($veBookingRequest);

            // Handle the response (optional)
            if ($veBookingResponse->getStatusCode() !== 200) {
                throw new \Exception("Error in VeBookingController: " . $veBookingResponse->getContent());
            }
        });
    }

    private function add_availability($date, $providers, $duration) {
        foreach ($providers as $provider) {
            $provider_id = $provider;
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
}
