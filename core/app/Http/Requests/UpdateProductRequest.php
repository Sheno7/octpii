<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return auth()->user()->can('products.update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'id' => [
                'required',
                'exists:products,id',
            ],
            'name_en' => [
                'required',
                'string',
            ],
            'name_ar' => [
                'required',
                'string'
            ],
            'category_id' => [
                'required',
                'exists:product_categories,id',
            ],
            'price' => [
                'numeric',
                'min:0',
            ],
            'quantity' => [
                'numeric',
                'min:0',
            ],
            'minimum_quantity' => [
                'numeric',
                'min:0',
            ],
        ];
    }
}
