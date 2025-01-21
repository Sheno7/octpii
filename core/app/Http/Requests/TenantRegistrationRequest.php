<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class TenantRegistrationRequest extends FormRequest {
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
            'user.first_name' => ['required', 'string', 'max:255'],
            'user.last_name' => ['required', 'string', 'max:255'],
            'user.email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'user.phone' => [
                'required',
                'unique:users,phone',
                'regex:/^(?:0)?(10|11|12|15)\d{8}$/',
            ],
            'user.country_id' => ['required', 'integer', 'exists:countries,id'],
            // 'user.password' => [
            //     'required',
            //     'confirmed',
            //     Password::min(8)
            //         ->letters()
            //         ->mixedCase()
            //         ->numbers()
            //         ->symbols(),
            // ],
        ];
    }
}
