<?php

namespace App\Http\Resources\Customer;

use App\Enums\Status;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $vendor = $this->vendors[0];
        $booking = [
            'id' => $this->id,
            'date' => $this->date,
            'status' => $this->status,
            'total' => $this->total,
            //'total_price' => $total,
            //'markup' => $this->markup,
            //'base_price' => $this->base_price,
            //'count_provider' => $this->providers->count(),
            'notes' => $this->notes,
            'address' => $this->address,
            'payment' => [
                'method' => 'cash',
                'status' => ($this->payment_status == Status::PAYMENTCOMPLETED)
                    ? __('payment.paid') : __('payment.unpaid'),
            ],
            /* 'providers' => $this->providers->map(function ($provider) {
                return [
                    'id' => $provider->id,
                    'rank' => $provider->rank,
                    'rating' => $provider->rating,
                    'first_name' => $provider->user?->first_name,
                    'last_name' => $provider->user?->last_name,
                ];
            }), */
            'vendor' => [
                'id' => $vendor->id,
                'org_name' => app()->getLocale() === "ar" ? $vendor->org_name_ar : $vendor->org_name_en,
                'description' => app()->getLocale() === "ar" ? $vendor->description_ar : $vendor->description_en,
                "image" => $vendor->image,
            ],
            'services' => $this->maServices->map(function ($service) {
                return [
                    'id' => $service->id,
                    'title' => app()->getLocale() === "ar" ? $service->title_ar : $service->title_en,
                    'description' => app()->getLocale() === "ar" ? $service->description_ar : $service->description_en,
                    'icon' => $service->icon,
                    'duration' => $service->duration,
                    'category_title' => app()->getLocale() === "ar" ? $service->category?->title_ar : $service->category?->title_en,
                    'sector_title' => app()->getLocale() === "ar" ? $service->category?->sector->title_ar : $service->category?->sector->title_en,
                    'sector_id' => $service->category?->sector_id,
                ];
            }),
        ];
        return $booking;
    }
}
