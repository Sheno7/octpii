<?php

namespace App\Http\Resources;

use App\Enums\Status;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource {

    /**
     * The flag indicating whether the resource is a single item.
     *
     * @var bool
     */
    private $isSingle;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  bool  $isSingle
     * @return void
     */
    public function __construct($resource, $isSingle = false) {
        $this->isSingle = $isSingle;
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $sector_id = null;
        if (count($this->services)) {
            $sector_id = $this->services[0]->category?->sector_id;
        }
        $total = $this->total;
        if (!empty($this->package_id)) {
            $total = 0;
            if ($this->status === Status::BOOKINGCOMPLETED) {
                $total = $this->package?->services->sum(function ($s) {
                    return $s->pivot->price;
                });
            }
        }
        $booking = [
            'id' => $this->id,
            'sector_id' => $sector_id,
            'date' => $this->date,
            'status' => $this->status,
            'total' => $this->total,
            'total_price' => $total,
            'markup' => $this->markup,
            'base_price' => $this->base_price,
            'customer_id' => $this->customer?->user ? $this->customer_id : '',
            'customer_phone' => $this->customer?->user?->phone,
            'customer_name' => isset($this->customer?->user) ?
                $this->customer?->user?->first_name . ' ' . $this->customer?->user?->last_name : 'Deleted User',
            'count_provider' => $this->providers->count(),
            'payment' => [
                'method' => 'cash',
                'status' => ($this->payment_status == Status::PAYMENTCOMPLETED)
                    ? __('payment.paid') : __('payment.unpaid'),
            ],
            'providers' => $this->providers->map(function ($provider) {
                return [
                    'id' => $provider->id,
                    'rank' => $provider->rank,
                    'rating' => $provider->rating,
                    'first_name' => $provider->user?->first_name,
                    'last_name' => $provider->user?->last_name,

                ];
            }),
            'services' => $this->services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'title' => $service->pluck(app()->getLocale() === "ar" ? 'title_ar' : 'title_en')->implode(', '),
                    'title_en' => $service->title_en,
                    'title_ar' => $service->title_ar,
                    'icon' => $service->icon,
                    'duration' => $service->duration,
                ];
            }),

            // Remove this
            'service_id' => $this->services?->first()?->id,
            'service_title' => $this->services?->pluck(app()->getLocale() === "ar" ? 'title_ar' : 'title_en')->implode(', '),
            'service_duration' => $this->services?->first()?->duration,
            'service_cost' => $this->services?->first()?->cost,
            'cities_id' => $this->area?->city_id,
            'cities_title' => $this->area?->city?->title_en,
            'areas_id' => $this->area_id,
            'area_title' => $this->area?->title_en,
            // End

        ];

        if ($this->isSingle) {
            $booking["gender_prefrence"] = $this->gender_prefrence;
            $booking["is_favorite"] = $this->is_favorite;
            $booking["booking_status"] = $this->status;
            $booking["booking_source"] = $this->source;
            $booking["created_at"] = $this->created_at;
            $booking["feedback"] = $this->feedback;
            $booking["notes"] = $this->notes;
            $booking["package_id"] = $this->package_id;
            $booking["country_code"] = $this->customer?->user?->country_id;
            $booking["ended_date"] =  Carbon::parse($this->date)
                ->addMinutes(($this->services?->first()?->duration ?? 0) * 60)->format('Y-m-d H:i:s');
            $booking["created_by"] = $this->created_by;
            $booking["cost_per_provider"] = $this->services?->first()?->cost;
            $booking["address"] = $this->address;
        }

        return $booking;
    }
}
