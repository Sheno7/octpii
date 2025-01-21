<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProcurementRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return auth()->user()->can('procurements.store');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'product_id' => [
                'required',
                'exists:products,id'
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'payment_method_id' => [
                'required',
                'exists:payment_method,id',
            ],
            'date' => [
                'required',
                'date',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }
}
