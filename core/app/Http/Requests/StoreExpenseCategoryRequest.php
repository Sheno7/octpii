<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseCategoryRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return auth()->user()->can('expense_categories.store');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'title_en' => [
                'required',
                'string',
                'unique:expense_categories,title_en',
            ],
            'title_ar' => [
                'required',
                'string'
            ],
            'color' => [
                'nullable',
                'string',
                'regex:/^#([a-fA-F0-9]{3}){1,2}$/',
            ],
        ];
    }
}
