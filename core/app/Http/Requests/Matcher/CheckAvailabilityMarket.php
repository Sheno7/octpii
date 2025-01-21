<?php

namespace App\Http\Requests\Matcher;

use Illuminate\Foundation\Http\FormRequest;

class CheckAvailabilityMarket extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'vendor_id' => 'required|exists:vendors,id',
            'service_ids' => 'required|array',
            'service_ids.*' => 'required|exists:service_vendor,ve_service_id',
        ];
        return $rules;
    }
}
