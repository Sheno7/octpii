<?php

namespace App\Http\Resources;

use App\Models\VeServices;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $booking = [];
        if ($this->booking) {
            $booking = $this->booking->map(function ($booking) {
                if (property_exists($booking, 'status')) {
                    $booking->booking_status = $booking->status;
                    unset($booking->status);
                }

                $bookingService = $booking->bookingServices->first();
                if ($bookingService) {
                    $booking->service_status = $bookingService->status;

                    // Add the services information to the booking
                    $booking->services = $booking->bookingServices->map(function ($bookingService) {
                        $service = VeServices::find($bookingService->service_id);
                        $title = app()->getLocale() === 'ar' ? $service->title_ar : $service->title_en;
                        return [
                            'id'    => $bookingService->service_id,
                            'title' => $title,
                        ];
                    });

                    unset($booking->bookingServices);

                    return $booking;
                }
            });
        }
        return [
            "id" => $this->id,
            "title" => $this->title,
            "customer_id" => $this->customer_id,
            "provider_id" => $this->provider_id,
            "status" => $this->status,
            "payment_status" => $this->payment_status,
            "created_at" => $this->created_at,
            "booking" => $booking,
            "package_services" => $this->packageServices,
            "services" => ServiceResource::collection($this->services),
        ];
    }
}
