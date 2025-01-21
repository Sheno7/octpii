<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            "id" => $this->id,
            "name" => app()->getLocale() === "ar" && !empty($this->name_ar) ? $this->name_ar :  $this->name_en,
            "name_en" => $this->name_en,
            "name_ar" => $this->name_ar,
            "created_at" => $this->created_at,
        ];
    }
}
