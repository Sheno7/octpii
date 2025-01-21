<?php

namespace App\Http\Requests\ProviderApp;

use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBooking extends FormRequest {
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
        $started = Status::BOOKINGSTARTED;
        $completed = Status::BOOKINGCOMPLETED;
        return [
            'status' => [
                'required',
                "in:$started,$completed"
            ]
        ];
    }
}
