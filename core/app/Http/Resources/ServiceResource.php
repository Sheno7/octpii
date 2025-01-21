<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'title' => app()->getLocale() === "ar" ? $this->title_ar : $this->title_en,
            'icon' => $this->icon,
            'visible' => $this->visible,
            'status' => $this->status,
            'duration' => $this->duration,
            'description' => app()->getLocale() === "ar" ? $this->description_ar : $this->description_en,
            'cost' => $this->cost + $this->markup + $this->base_price,
            'available_for_booking' => $this->visible,
            'base_price' => $this->base_price,
            'capacity' => $this->capacity,
            'capacity_threshold' => $this->capacity_threshold,
            'markup' => $this->markup,
            'service_location' => $this->service_location,
            'total_bookings' => $this->total_bookings ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'category_title' => app()->getLocale() === "ar" ? $this->category?->title_ar : $this->category?->title_en,
            'sector_title' => app()->getLocale() === "ar" ?
                $this->category?->sector->title_ar : $this->category?->sector->title_en,
            'sector_id' => $this->category?->sector_id,
            'providers' => $this->providers?->map(function ($provider) {
                return [
                    'id' => $provider->id,
                    'rank' => $provider->rank,
                    'rating' => $provider->rating,
                    'first_name' => $provider->user?->first_name,
                    'last_name' => $provider->user?->last_name,
                ];
            }),
        ];
    }
}
