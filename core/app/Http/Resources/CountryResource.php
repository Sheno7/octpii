<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            "id" => $this->id,
            "title" => app()->getLocale() === "ar" ? $this->title_ar : $this->title_en,
            "code" => $this->code,
            "created_at" => $this->created_at,
            "currency" => $this->currency,
            "flag" => $this->flag,
            "isocode" => $this->isocode,
            "status" => $this->status,
            "updated_at" => $this->updated_at,
        ];
    }
}
