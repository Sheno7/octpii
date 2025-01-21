<?php

namespace App\Http\Requests\Market\Customers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class VerifyOtp extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void {
        if (!empty($this->input('phone'))) {
            $this->merge([
                'phone' => $this->addLeadingZero($this->input('phone')),
            ]);
        }
    }

    /**
     * Add a leading zero if it's missing.
     *
     * @param string $phone
     * @return string
     */
    private function addLeadingZero(string $phone): string {
        return Str::startsWith($phone, '0') ? $phone : '0' . $phone;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'otp' => [
                'required',
                'numeric',
            ],
            'phone' => [
                'required',
                'regex:/^(?:0)?(10|11|12|15)\d{8}$/',
            ],
            'country_id' => [
                'required',
                'integer',
                'exists:countries,id',
            ],
        ];
    }
}
