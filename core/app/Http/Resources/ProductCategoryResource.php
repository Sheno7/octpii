<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCategoryResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'title' => app()->getLocale() === 'ar' ? $this->title_ar : $this->title_en,
            'title_en' => $this->title_en,
            'title_ar' => $this->title_ar,
            'color' => $this->color,
            'created_at' => $this->created_at,
        ];
    }
}
