<?php

namespace App\Http\Resources\Customer;

use App\Enums\Status;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $bookings = $this->customer?->bookings?->map(function ($booking) {
            return [
                'total' => $booking->status === Status::BOOKINGCOMPLETED ?  (float)$booking->total : 0,
                'completed_transactions' => $booking->completed_transactions->sum('amount'),
            ];
        });

        $spent = $bookings?->sum('completed_transactions');
        $outstanding = $bookings?->sum('total') - $spent;

        return [
            'image' => $this->image,
            'name' => "{$this->first_name} {$this->last_name}",
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'email' => $this->email,
            'phone' => $this->phone,
            'country' => [
                'id' => $this->country?->id,
                "title_ar" => $this->country?->title_ar,
                "title_en" => $this->country?->title_en,
                "code" => $this->country?->code,
                "flag" => $this->country?->flag,
            ],
            'bookings' => $this->customer?->bookings->count(),
            'spent' => $spent,
            'outstanding' => $outstanding
        ];
    }
}
