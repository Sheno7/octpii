<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {

        $services = $this->services->map(function ($service) {
            return [
                "id" => $service->id,
                "service_title" => app()->getLocale() === "ar" ? $service->title_ar : $service->title_en,
            ];
        });

        $areas = $this->areas->map(function ($area) {
            return [
                "id" => $area->id,
                "area_title" => app()->getLocale() === "ar" ? $area->title_ar : $area->title_en,
            ];
        });

        return [
            "id" => $this->id,
            "branch_id" => $this->branch_id,
            "user_id" => $this->user_id,
            "address_id" => $this->address_id,
            "rank" => $this->rank,
            "rating" => $this->rating,
            "start_date" => $this->start_date,
            "resign_date" => $this->resign_date,
            "salary" => $this->salary,
            "commission_type" => $this->commission_type,
            "commission_amount" => $this->commission_amount,
            "balance" => $this->balance,
            "status" => $this->status,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "deleted_at" => $this->deleted_at,
            "wallet" => $this->wallet,
            "total_bookings" => $this->total_bookings,
            "services_count" => $this->services_count,
            "areas_count" => $this->areas_count,
            "user" => [
                "id" => $this->user?->id,
                "first_name" => $this->user?->first_name,
                "last_name" => $this->user?->last_name,
                "phone" => $this->user?->phone
            ],
            "service_offered" => $services,
            "services" => $services,
            "area_covered" => $areas,
            "areas" => $areas,
        ];
    }
}
