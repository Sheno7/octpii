<?php

namespace App\Http\Resources\Provider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            "id" => $this->id,
            "amount" => $this->amount,
            "booking_id" => $this->booking_id,
            "created_by" => $this->created_by,
            "date" => $this->date,
            "payment_method" => $this->paymentMethod,
            "payment_method_id" => $this->payment_method_id,
            "provider_id" => $this->provider_id,
            "status" => $this->status,
            "type" => $this->type,
            "created_at" => $this->created_at,
        ];
    }
}
