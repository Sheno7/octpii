<?php

namespace App\Http\Controllers\vendors\v1;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insights\GetInsights;
use App\Http\Resources\BookingResource;
use App\Http\Resources\ProviderResource;
use App\Http\Resources\ServiceResource;
use App\Models\Booking;
use App\Models\Customers;
use App\Models\Providers;
use App\Models\Transaction;
use App\Models\VeServices;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;

class VeInsightsController extends Controller {
    use ResponseTrait;
    public function index(GetInsights $request) {
        $inputs = $request->validated();
        try {
            if (!isset($inputs['start'])) {
                $inputs['start'] = '1900-01-01T00:00:00';
            }
            if (!isset($inputs['end'])) {
                $inputs['end'] = '2999-12-31T23:59:59';
            }

            $bookings = Booking::whereBetween(DB::raw('DATE(date)'), [$inputs['start'], $inputs['end']]);

            $bookingsByStatus =
                $bookings->get()->groupBy('status')->map(function ($bookings, $status) {
                    return [
                        'status' => $status,
                        'bookings' => $bookings->count(),
                    ];
                })->values();

            $transactions = Transaction::whereBetween(DB::raw('DATE(date)'), [$inputs['start'], $inputs['end']]);
            $all_customers = Customers::count();
            $new_customers = Customers::whereBetween(DB::raw('DATE(created_at)'), [$inputs['start'], $inputs['end']]);

            $providers = Providers::whereHas('bookings', function ($q) use ($inputs) {
                $q->whereBetween(DB::raw('DATE(date)'), [$inputs['start'], $inputs['end']]);
            })->withCount(['bookings as total_bookings' => function ($q) use ($inputs) {
                $q->whereBetween(DB::raw('DATE(date)'), [$inputs['start'], $inputs['end']]);
            }]);

            $branch_id = $request->get('branch_id', $request->get('selected_branch', null));

            $services = VeServices::whereHas('bookings', function ($q) use ($inputs, $branch_id) {
                if ($branch_id) {
                    $q->where('branch_id', $branch_id);
                }
                $q
                    ->where('booking.status', Status::BOOKINGCOMPLETED)
                    ->whereBetween(DB::raw('DATE(date)'), [$inputs['start'], $inputs['end']]);
            })->withCount(['bookings as total_bookings' => function ($q) use ($inputs, $branch_id) {
                if ($branch_id) {
                    $q->where('branch_id', $branch_id);
                }
                $q
                    ->whereNot('booking.status', Status::BOOKINGCANCELLED)
                    ->whereBetween(DB::raw('DATE(date)'), [$inputs['start'], $inputs['end']]);
            }]);

            if ($branch_id) {
                $transactions->whereHas('booking', function ($q) use ($branch_id) {
                    $q->where('branch_id', $branch_id);
                });
                $new_customers->where('branch_id', $branch_id);
                $providers->where('branch_id', $branch_id);
            }

            $top_providers = ProviderResource::collection(
                $providers
                    ->orderBy('total_bookings', 'desc')
                    ->take(3)
                    ->get()
            );

            $top_services = ServiceResource::collection(
                $services
                    ->orderBy('total_bookings', 'desc')
                    ->take(3)
                    ->get()
            );

            $bookings = BookingResource::collection(
                $bookings
                    ->orderBy('date', 'desc')
                    ->take(5)
                    ->get()

            );

            $data = [
                'all_customers' => $all_customers,
                'new_customers' => $new_customers->count(),
                'bookings_status' => $bookingsByStatus,
                'bookings' => $bookings,
                'revenue' => $transactions->sum('amount'),
                'providers' => $top_providers,
                'services' => $top_services,
            ];

            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            throw $th;
            return $this->getErrorResponse($th);
        }
    }
}
