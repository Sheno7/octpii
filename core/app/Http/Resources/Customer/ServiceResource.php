<?php

namespace App\Http\Resources\Customer;

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
            'description' => app()->getLocale() === "ar" ? $this->description_ar : $this->description_en,
            'duration' => $this->duration,
            'capacity' => $this->capacity,
            'capacity_threshold' => $this->capacity_threshold,
            'status' => $this->status,
            'icon' => $this->icon,
            'markup' => $this->markup,
            'base_price' => $this->base_price,
            'visible' => $this->visible,

            'cost' => $this->cost + $this->markup + $this->base_price,
            'remote' => $this->visible,
            
            'service_location' => $this->service_location,
            'total_bookings' => $this->total_bookings ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'category_title' => app()->getLocale() === "ar" ? $this->category?->title_ar : $this->category?->title_en,
            'sector_title' => app()->getLocale() === "ar" ?
                $this->category?->sector->title_ar : $this->category?->sector->title_en,
            'sector_id' => $this->category?->sector_id,
        ];
    }
}
