<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductCategoryRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return auth()->user()->can('product_categories.update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        $id = $this->input('id');

        return [
            'id' => [
                'exists:product_categories',
            ],
            'title_en' => [
                'sometimes',
                'string',
                Rule::unique('product_categories')->ignore($id),
            ],
            'title_ar' => [
                'sometimes',
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
