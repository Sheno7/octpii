<?php

namespace App\Http\Controllers\vendors\v1;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
use App\Http\Resources\ServiceResource;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Package;
use App\Models\PackageServices;
use App\Models\VeServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logger;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Validator;

class VePackagesController extends Controller {
    use ResponseTrait;

    public function add(Request $request) {
        try {
            DB::beginTransaction();
            $validatedData = Validator::make($request->all(), [
                'title' => 'required|string',
                'customer_id' => 'required|integer|exists:customers,id',
                'provider_id' => 'required|integer|exists:providers,id',
                'booking_id' => 'sometimes|integer|exists:booking,id',
                'services' => 'required|array',
                'services.*.service_id' => 'required|integer|exists:ve_services,id',
                'services.*.duration' => 'required|numeric',
            ]);

            if ($validatedData->fails()) {
                return $this->getErrorValidationResponse($validatedData->errors());
            }

            $package = Package::create([
                'title' => $request->title,
                'customer_id' => $request->customer_id,
                'provider_id' => $request->provider_id,
            ]);

            foreach ($request->services as $service) {
                PackageServices::create([
                    'package_id' => $package->id,
                    'service_id' => $service['service_id'],
                    'duration' => $service['duration'],
                    // get base price from ve_services table
                    'price' => $this->get_service_price($service['service_id']),
                ]);
            }
            if ($request->has('booking_id')) {
                Booking::where('id', $request->booking_id)->update([
                    'package_id' => $package->id,
                    'updated_at' => now(),
                ]);
            }
            DB::commit();
            return $this->getSuccessResponse('success', $package);
        } catch (\Exception $e) {
            DB::rollBack();
            Logger::error($e);
            return $this->getErrorResponse('error', $e);
        }
    }

    protected function get_service_price($service_id) {
        $veService = VeServices::where('id', $service_id)->first();
        return $veService->base_price + $veService->cost;
    }

    public function edit_package(Request $request) {
        try {
            DB::beginTransaction();
            $validatedData = Validator::make($request->all(), [
                'id' => 'required|integer|exists:package,id',
                'title' => 'required|string',
                'customer_id' => 'required|integer|exists:customers,id',
                'provider_id' => 'required|integer|exists:providers,id',
                'booking_id' => 'sometimes|integer|exists:bookings,id',
                'services' => 'required|array',
                'services.*.service_id' => 'required|integer|exists:ve_services,id',
                'services.*.duration' => 'required|numeric',
            ]);
            if ($validatedData->fails()) {
                return $this->getErrorValidationResponse($validatedData->errors());
            }
            $package = Package::find($request->id);
            if (!$package) {
                return $this->getErrorResponse('error', 'package not found');
            }
            $package->update([
                'title' => $request->title,
                'customer_id' => $request->customer_id,
                'provider_id' => $request->provider_id,
            ]);
            PackageServices::where('package_id', $package->id)->delete();
            foreach ($request->services as $service) {
                PackageServices::create([
                    'package_id' => $package->id,
                    'service_id' => $service['service_id'],
                    'duration' => $service['duration'],
                    'price' => $this->get_service_price($service['service_id']),
                ]);
            }
            if ($request->has('booking_id')) {
                Booking::where('id', $request->booking_id)->update([
                    'package_id' => $package->id,
                    'updated_at' => now(),
                ]);
            }
            DB::commit();
            return $this->getSuccessResponse('success', $package);
        } catch (\Exception $e) {
            DB::rollBack();
            Logger::error($e);
            return $this->getErrorResponse('error', $e);
        }
    }
    public function show(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:package,id',
            ]);

            if ($validator->fails()) {
                return $this->getErrorResponse('error', $validator->errors(), 422);
            }

            $data = Package::with('packageServices', 'services')
                ->where('id', $request->id)
                ->first();

            $data = new PackageResource($data);

            return $this->getSuccessResponse('success', $data);
        } catch (\Exception $e) {
            Logger::error($e);
            return $this->getErrorResponse('error', $e);
        }
    }

    public function check_service_package(Request $request) {
        try {
            $auth_user = $request->user();
            $customer_id = null;
            if (!empty($auth_user->customer)) {
                $customer_id =  $auth_user->customer->id;
            } else {
                $validator = Validator::make($request->all(), [
                    'id' => 'required|integer|exists:customers,id',
                ]);
                if ($validator->fails()) {
                    return $this->getValidationErrorResponse('error', $validator->errors());
                }
                $customer_id = $request->id;
            }

            $active_package = Package::where('customer_id', $customer_id)->whereIn('status', [
                Status::PACKAGESERVICEPENDING,
                Status::PACKAGESERVICESTARTED,
            ])->first();

            $package_services = collect([]);
            if (!empty($active_package)) {
                $package_services = $active_package->services;
            }

            $active_services = VeServices::where('visible', Status::SERVICEVISIBLE)
                ->whereNotIn(
                    'id',
                    $package_services->pluck('id'),
                )->get();

            $data = [
                'active_service' => ServiceResource::collection($active_services),
                'package_service' => ServiceResource::collection($package_services),
            ];
            return $this->getSuccessResponse('success', $data);
        } catch (\Exception $e) {
            Logger::error($e);
            return $this->getErrorResponse('error', $e);
        }
    }

    public function update_service(Request $request) {
        try {
            $validation = Validator::make($request->all(), [
                'id' => 'required|integer|exists:package,id',
                'service_id' => 'required|integer|exists:ve_services,id',
                'action' => 'required|integer|in:0,1'
            ]);
            if ($validation->fails()) {
                return $this->getValidationErrorResponse('error', $validation->errors());
            }
            $package = Package::where('id', $request->id)->first();
            if (empty($package)) {
                return $this->getErrorResponse('error', 'package not found');
            }
            if ($request->action == 0) {
                $package->services()->detach($request->service_id);
                $this->cancelPackage($package);
            } else {
                $check = PackageServices::where('package_id', $request->id)
                    ->where('service_id', $request->service_id)
                    ->first();
                if ($check) {
                    return $this->getErrorResponse('error', 'you cannot add this service');
                }
                // total price = base price + cost
                $total = VeServices::where('id', $request->service_id)->first()->base_price +
                    VeServices::where('id', $request->service_id)->first()->cost;
                PackageServices::create([
                    'package_id' => $request->id,
                    'service_id' => $request->service_id,
                    'duration' => 0,
                    'price' => $total,
                ]);
            }
            return $this->getSuccessResponse('success');
        } catch (\Exception $e) {
            Logger::error($e);
            return $this->getErrorResponse('error', $e);
        }
    }
    public function update_status(Request $request) {
        Validator::validate($request->all(), [
            'id' => 'required|integer|exists:package,id',
            'service_id' => 'required|integer|exists:ve_services,id',
            //'status' => 'required|integer|in:1,2'
        ]);
        try {
            $check = PackageServices::where('package_id', $request->id)
                ->where('status', '>=', $request->status)
                ->where('service_id', $request->service_id)
                ->first();
            if ($check) {
                return $this->getErrorResponse('error', 'you can not update');
            }
            PackageServices::where('package_id', $request->id)
                ->where('service_id', $request->service_id)
                ->update([
                    'status' => $request->status,
                    'price' => $request->price,
                    'duration' => $request->duration ?? 0,
                    'updated_at' => now(),
                ]);
            // update booking related to this package
            BookingService::join('booking', 'booking.id', '=', 'booking_service.booking_id')
                ->where('booking.package_id', $request->id)
                ->where('booking_service.service_id', $request->service_id)
                ->update([
                    'booking_service.status' => $request->status,
                    'booking_service.price' => $request->price,
                    'booking_service.duration' => $request->duration ?? 0,
                    'booking_service.updated_at' => now(),
                ]);
            return $this->getSuccessResponse('success');
        } catch (\Exception $e) {
            Logger::error($e);
            return $this->getErrorResponse('error', $e);
        }
    }

    public function complete_package(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:package,id',
            ]);
            if ($validator->fails()) {
                return $this->getValidationErrorResponse('error', $validator->errors());
            }
            $package = Package::find($request->id);
            $pendingServicesCount = $package->services()->wherePivotIn('status', [
                Status::PACKAGESERVICEPENDING,
                Status::PACKAGESERVICESTARTED
            ])->count();
            if ($pendingServicesCount > 0) {
                return $this->getErrorResponse('error', 'you cannot complete this package not all service completed');
            }
            $package->update([
                'status' => Status::PACKAGESERVICECOMPLETED,
                'updated_at' => now(),
            ]);
            return $this->getSuccessResponse('success');
        } catch (\Exception $e) {
            Logger::error($e);
            return $this->getErrorResponse('error', $e);
        }
    }

    // remove plan if no service exist
    private function cancelPackage($package) {
        try {
            $activeServices = $package->services()->wherePivotIn(
                'status',
                [
                    Status::PACKAGESERVICEPENDING,
                    Status::PACKAGESERVICESTARTED,
                    Status::PACKAGESERVICECOMPLETED
                ]
            )->count();
            if ($activeServices == 0) {
                $booking = Booking::where('package_id', $package->id)->first();
                if (!empty($booking)) {
                    $booking->update(['package_id' => null]);
                }
            }
            return $this->getSuccessResponse('success');
        } catch (\Exception $e) {
            Logger::error($e);
            return $this->getErrorResponse('error', $e);
        }
    }

    public function completePlan($packageid) {
        try {
            $count = PackageServices::where('package_id', $packageid)->count();
            $completed = PackageServices::where('package_id', $packageid)
                ->where('status', Status::PACKAGESERVICECOMPLETED)
                ->count();
            if ($count == $completed) {
                Package::where('id', $packageid)->update([
                    'status' => Status::PACKAGESERVICECOMPLETED
                ]);
            } else {
                Package::where('id', $packageid)->update([
                    'status' => Status::PACKAGESERVICESTARTED
                ]);
            }
        } catch (\Exception $e) {
            Logger::error($e);
            return $this->getErrorResponse('error', $e);
        }
    }
}
