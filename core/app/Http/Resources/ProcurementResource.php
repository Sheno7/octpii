<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcurementResource extends JsonResource {
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
            'price' => $this->price,
            'transaction' => $this->transaction,
            'product' => new ProductResource($this->product),
        ];
    }
}
