<?php

namespace App\Http\Resources\up;

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
            "id" => $this->id,
            "title" => app()->getLocale() === 'ar' ? $this->title_ar : $this->title_en,
            "title_ar" => $this->title_ar,
            "title_en" => $this->title_en,
            "icon" => $this->icon,
            "status" => $this->status,
            "description_ar" => $this->description_ar,
            "description_en" => $this->description_en,
            "service_location" => $this->service_location,
            "category_id" => $this->category_id,
            "sector_id" => $this->category?->sector_id,
            "sector_title" => app()->getLocale() === 'ar' ? $this->category?->sector?->title_ar : $this->category?->sector?->title_en,
            "created_at" => $this->created_at,
            "category_id" => $this->category_id,
            "sector" => $this->sectors?->title_en,
            "category" => $this->category?->title_en,
        ];
    }
}
