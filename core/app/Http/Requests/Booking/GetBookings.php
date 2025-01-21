<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class GetBookings extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'id' => 'nullable|integer|exists:booking,id',
            'status' => 'nullable|integer|in:0,1,2,3',
            'services.*' => 'nullable|integer|exists:services,id',
            'booking_at_start' => 'nullable|date_format:Y-m-d',
            'booking_at_end' => 'nullable|date_format:Y-m-d|after_or_equal:booking_at_start',
            'created_at_start' => 'nullable|date_format:Y-m-d',
            'created_at_end' => 'nullable|date_format:Y-m-d',
            'customer_phone' => 'nullable|string',
            'provider_phone' => 'nullable|string',
            'source' => 'nullable|integer|in:1,2',
            'coupon_id' => 'nullable|integer',
            'canceled' => 'nullable|in:true,false',
        ];
    }
}
