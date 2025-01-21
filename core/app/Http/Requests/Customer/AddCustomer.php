<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class AddCustomer extends FormRequest {
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['nullable', 'email', 'max:50', 'unique:users'],
            'gender' => ['required', 'integer'],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'location_name' => ['nullable', 'string'],
            'unit_type' => ['nullable', 'integer'],
            'unit_size' => ['nullable', 'integer'],
            'street_name' => ['nullable', 'string'],
            'building_number' => ['nullable', 'string']
        ];
    }
}
