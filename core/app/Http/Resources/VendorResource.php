<?php

namespace App\Http\Resources;

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
            "id" => $this->id,
            "org_name_en" => $this->org_name_en,
            "org_name_ar" => $this->org_name_ar,
            "description_en" => $this->description_en,
            "description_ar" => $this->description_ar,
            "image" => $this->image,
            "sectors" => $this->sectors->map(function ($sector) {
                return [
                    'id' => $sector->id,
                    'title' => app()->getLocale() === 'ar' ? $sector->title_ar : $sector->title_en,
                ];
            }),
            "domain" => $this->domain?->domain,
            "full_domain" => $this->domain?->domain . "." . env('MAIN_DOMAIN'),
            "subscription" => $this->subscription?->load(['plan.features']),
            "status" => $this->status,
            "created_at" => $this->created_at,
        ];
    }
}
