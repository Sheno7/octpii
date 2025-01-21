<?php

namespace App\Http\Controllers\Customer;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\GetBookings;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller {
    use ResponseTrait;

    public function index(GetBookings $request) {
        try {
            $customer_id = Auth::user()->customer->id;
            $bookings = Booking::with([
                'providers.user',
                'services',
            ])->where('customer_id', $customer_id);
            if ($request->get('history', 0)) {
                $bookings = $bookings
                    ->where(function ($q) {
                        $q
                            ->whereDate('date', '<', now())
                            ->orWhereIn('status', [Status::BOOKINGCANCELLED, Status::BOOKINGCOMPLETED]);
                    });
            } else {
                $bookings = $bookings
                    ->whereDate('date', '>=', now())
                    ->whereNotIn('status', [Status::BOOKINGCANCELLED]);
            }
            $bookings = $bookings->paginate(10);
            $bookings->data = BookingResource::collection($bookings);

            return $this->getSuccessResponse('Bookings Retrieved Successfully', $bookings);
        } catch (\Throwable $th) {
            return $this->getErrorResponse('Error: Please Try again!', $th);
        }
    }
}
