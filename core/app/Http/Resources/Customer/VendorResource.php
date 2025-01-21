<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'org_name' => app()->getLocale() === "ar" ? $this->org_name_ar : $this->org_name_en,
            'description' => app()->getLocale() === "ar" ? $this->description_ar : $this->description_en,
            "image" => $this->image,
            "sectors" => $this->sectors->map(function ($sector) {
                return [
                    'id' => $sector->id,
                    'title' => app()->getLocale() === 'ar' ? $sector->title_ar : $sector->title_en,
                ];
            }),
        ];
    }
}
