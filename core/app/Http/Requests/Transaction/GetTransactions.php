<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class GetTransactions extends FormRequest {
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
            'booking_id' => 'nullable|integer|exists:booking,id',
            'transaction_at_start' => 'nullable|date_format:Y-m-d',
            'transaction_at_end' => 'nullable|date_format:Y-m-d|after_or_equal:transaction_at_start',
            'created_at_start' => 'nullable|date_format:Y-m-d',
            'created_at_end' => 'nullable|date_format:Y-m-d|after_or_equal:created_at_start',
            'customer_id' => 'nullable|integer|exists:customers,id',
            'provider_id' => 'nullable|integer|exists:providers,id',
            'payment_status' => 'nullable|integer',
            'services' => 'nullable',
        ];
    }
}
