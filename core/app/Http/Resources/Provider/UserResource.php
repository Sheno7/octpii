<?php

namespace App\Http\Resources\Provider;

use App\Enums\CommissionType;
use App\Enums\Status;
use App\Models\ProvidersAction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $provider = $this->provider;

        $commissions = 0;
        $commission_type = $provider?->commission_type;
        $commission_amount = $provider?->commission_amount;
        $salary = $provider?->salary;

        $startedDate = Carbon::parse($provider?->start_date);
        $currentDate = Carbon::now();
        $monthsDifference = $startedDate->diffInMonths($currentDate);

        if ($commission_amount > 0) {
            $commissions = $provider?->bookings->where('status', Status::BOOKINGCOMPLETED)->reduce(function ($accumulator, $booking) use ($commission_type, $commission_amount) {
                $amount = $commission_amount;
                if ($commission_type === CommissionType::PERCENTAGE) {
                    $amount = $booking->total * $commission_amount / 100;
                }
                return $accumulator + $amount;
            }, 0);
        }

        $salaries = $salary * $monthsDifference;
        $earnings = $commissions + $salaries;

        $sumReceived = ProvidersAction::where('provider_id', $provider?->id)
            ->where('action', 1)
            ->sum('amount');

        $received = doubleval($sumReceived);
        $outstanding = $earnings - $received;

        return [
            'image' => $this->image,
            'name' => "{$this->first_name} {$this->last_name}",
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'email' => $this->email,
            'phone' => $this->phone,
            'country' => [
                'id' => $this->country?->id,
                "title_ar" => $this->country?->title_ar,
                "title_en" => $this->country?->title_en,
                "code" => $this->country?->code,
                "flag" => $this->country?->flag,
            ],
            'bookings' => $provider?->bookings->count(),
            'commissions' => $commissions,
            'salaries' => $salaries,
            'earnings' => $earnings,
            'outstanding' => $outstanding,
        ];
    }
}
