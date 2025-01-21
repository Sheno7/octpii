<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class UpdateUserRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return auth()->user()->can('users.update');
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
        $user_id = $this->id ?? 0;
        return [
            'id' => [
                'required',
                'exists:users,id',
            ],
            'first_name' => [
                'required',
                'string',
                'max:255',
            ],
            'last_name' => [
                'required',
                'string',
                'max:255',
            ],
            'password' => [
                'sometimes',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'phone' => [
                'required',
                'unique:users,phone,' . $user_id,
                'regex:/^(?:0)?\d{10}$/',
            ],
            'country_id' => [
                'required',
                'integer',
                'exists:countries,id',
            ],
        ];
    }
}
