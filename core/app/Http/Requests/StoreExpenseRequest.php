<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return auth()->user()->can('expenses.store');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'category_id' => 'required|exists:expense_categories,id',
            'sector_id' => 'nullable|exists:sectors,id',
            'payment_method_id' => 'required|exists:payment_method,id',
            'amount' => 'required|numeric|gt:0',
            'date' => 'required|before_or_equal:now',
            'attachment' => 'nullable|file',
            'notes' => 'nullable',
        ];
    }
}
