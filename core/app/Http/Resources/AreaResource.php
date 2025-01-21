<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            "id" => $this->id,
            "title" => app()->getLocale() === "ar" ? $this->title_ar :  $this->title_en,
            "city_id" => $this->city_id,
            "city_title" => app()->getLocale() === "ar" ? $this->city_title_ar :  $this->city_title_en,
            "status" => $this->status,
        ];
    }
}
