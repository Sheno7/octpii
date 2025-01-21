<?php

namespace App\Http\Requests\WorkingSchedule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkingSchedule extends FormRequest {
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
            'timezone' => [
                'required',
                'timezone',
            ],
            'schedule' => [
                'required',
                'array',
                'size:7'
            ],
            'schedule.*.day' => [
                'required',
                'in:Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday'
            ],
            'schedule.*.off' => [
                'required',
                'boolean'
            ],
            'schedule.*.from' => [
                'nullable',
                'required_if:schedule.*.off,false',
                'numeric',
                'min:0'
            ],
            'schedule.*.to' => [
                'nullable',
                'required_if:schedule.*.off,false',
                'numeric',
                'gt:schedule.*.from',
                'max:24'
            ],
            // Custom rule to ensure each day is unique
            'schedule' => [function ($attribute, $value, $fail) {
                $days = array_column($value, 'day');
                if (count($days) !== count(array_unique($days))) {
                    $fail('The days in the schedule must be unique.');
                }
            }],
        ];
    }
}
