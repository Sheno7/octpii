<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            "id" => $this->id,
            "title" => app()->getLocale() === "ar" ? $this->title_ar : $this->title_en,
            "country_title" => app()->getLocale() === "ar" ? $this->country?->title_ar : $this->country?->title_en,
            "status" => $this->status,
        ];
    }
}
