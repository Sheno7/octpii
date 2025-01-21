<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdjustmentResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'date' => $this->date,
            'quantity' => $this->quantity,
            'product' => new ProductResource($this->product),
        ];
    }
}
