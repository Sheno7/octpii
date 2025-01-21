<?php

namespace App\Http\Resources;

use App\Models\Providers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CancellationReasonResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'text' => app()->getLocale() === 'ar' ? $this->text_ar : $this->text_en,
            'role' => $this->role === Providers::class ? 'provider' : 'customer',
        ];
    }
}
