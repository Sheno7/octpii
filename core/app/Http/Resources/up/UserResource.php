<?php

namespace App\Http\Resources\up;

use App\Models\Booking;
use App\Models\Customers;
use App\Models\Providers;
use App\Models\User;
use App\Models\VeServices;
use App\Models\WorkingSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $vendor = $this->vendor;
        $details = [];

        if (!empty($vendor)) {
            $tenant = $vendor->domain->tenant;
            $details = $tenant->run(function () {
                return [
                    'users' => User::count(),
                    'bookings' => Booking::count(),
                    'services' => VeServices::count(),
                    'providers' => Providers::count(),
                    'customers' => Customers::count(),
                    'working_hours' => WorkingSchedule::whereNull('provider_id')->count(),
                ];
            });
        }


        return [
            ...parent::toArray($request),
            'main_domain' => env('MAIN_DOMAIN'),
            'country_code' => $this->country?->code,
            'vendor' => $vendor,
            'details' => $details,
        ];
    }
}
