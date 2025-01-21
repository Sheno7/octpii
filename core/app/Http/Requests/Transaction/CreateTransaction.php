<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class CreateTransaction extends FormRequest {
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
            'id' => 'required|integer|exists:customers,id',
            'amount' => 'required|numeric|min:1',
            'payment_method_id' => 'required|integer|exists:payment_method,id',
            'date' => 'required|before_or_equal:today'
        ];
    }
}
