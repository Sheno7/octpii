<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectorResource extends JsonResource {
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
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'categories' => $this->categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'title' => app()->getLocale() === "ar" ? $category->title_ar : $category->title_en,
                    'description' => app()->getLocale() === "ar" ? $category->description_ar : $category->description_en,
                    'icon' => $category->icon,
                ];
            }),
        ];
    }
}
