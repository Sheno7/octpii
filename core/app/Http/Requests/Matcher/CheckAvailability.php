<?php

namespace App\Http\Requests\Matcher;

use App\Models\VeServices;
use Illuminate\Foundation\Http\FormRequest;

class CheckAvailability extends FormRequest
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
        $service_id = $this->service_id ?? 0;
        $rules = [
            'date' => 'required|date|after_or_equal:today',
            'service_id' => 'required|exists:ve_services,id',
            'gender' => 'required|in:-1,0,1',
            'provider_id' => 'sometimes|exists:providers,id',
        ];
        if ($this->service_location($service_id) == 1) {
            $rules['area_id'] = 'required|exists:area_service,area_id,service_id,' . $service_id;
            $rules['address_id'] = 'required|exists:address,id';
        }
        return $rules;
    }

    protected function service_location($service_id)
    {
        return VeServices::where('id', $service_id)->value('service_location');
    }
}
