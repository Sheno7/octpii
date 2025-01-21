<?php

namespace App\Http\Resources\Customer;

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
            "rank" => $this->rank,
            "rating" => $this->rating,
            "first_name" => $this->user?->first_name,
            "last_name" => $this->user?->last_name,
            "phone" => $this->user?->phone,
            "avatar" => "",
            "services" => $services,
            "areas" => $areas,
        ];
    }
}
