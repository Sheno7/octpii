<?php

namespace App\Http\Requests\Booking;

use App\Enums\Status;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteBooking extends FormRequest {
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
            'id' => [
                'required',
                'integer',
                Rule::exists('booking')->where(function (Builder $query) {
                    return $query->whereIn('status', [Status::BOOKINGPENDING, Status::BOOKINGSTARTED]);
                }),
            ],
            'services' => [
                'array',
                'required',
                'min:1',
            ],
            'services.*.id' => [
                'required',
                'integer',
            ],
            'services.*.price' => [
                'integer',
                'required',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public static function services_rules($package_id): array {
        $exists_table = 'booking_service';
        if (!empty($package_id)) {
            $exists_table = 'package_services';
        }

        return [
            'services.*.id' => [
                'required',
                'integer',
                Rule::exists($exists_table, 'service_id')->where(function (Builder $query) use ($package_id) {
                    $q = $query
                        ->whereIn('status', [Status::PACKAGESERVICEPENDING, Status::PACKAGESERVICESTARTED]);
                    if (!empty($package_id)) {
                        $q = $q
                            ->where('package_id', $package_id);
                    }
                    return $q;
                }),
            ],
            'services.*.price' => [
                'integer',
                'required',
            ],
        ];
    }
}
