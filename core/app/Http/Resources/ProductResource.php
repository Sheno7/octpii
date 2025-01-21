<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'name' => app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'price' => $this->price,
            'quantity' => $this->quantity + $this->stock,
            'minimum_quantity' => $this->minimum_quantity,
            'category_id' => $this->category_id,
            'category' => new ProductCategoryResource($this->category),
            'created_at' => $this->created_at,
        ];
    }
}
