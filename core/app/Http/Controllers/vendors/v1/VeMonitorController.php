<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Models\Avaliabilty;
use App\Models\Booking;
use App\Models\BookingProvider;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class VeMonitorController extends Controller
{
    use ResponseTrait;

    public function booking()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $firstDayOfMonth = $currentMonth->copy()->startOfMonth();
        $lastDayOfMonth = $currentMonth->copy()->endOfMonth();
        $booking_list = Booking::whereDate('booking.date', '>=', $firstDayOfMonth)
            ->whereDate('booking.date', '<=', $lastDayOfMonth)
            ->selectRaw('count(*) as count,status , DATE(booking.date) as date')
            ->groupBy(DB::raw('DATE(booking.date)'),'status')
           // ->select(DB::raw('date_format(booking.date, "%Y-%m") as date'))
            ->get();
        $results = [];
        $date = $firstDayOfMonth->copy();
        while ($date <= $lastDayOfMonth) {
            $dateStr = $date->toDateString();
            $results[] = [
                'date' => $dateStr,
                'pending' => 0,
                'started' => 0,
                'completed' => 0,
                'canceled' => 0,
                'total' => $booking_list->filter(function ($item) use ($dateStr) {
                    return $item->date->toDateString() === $dateStr;
                })->sum('count'),
            ];
            $date->addDay();
        }

        foreach ($booking_list as $booking) {
            $date = $booking->date->toDateString();
            $status = $booking->status;
            $count = $booking->count;
            $id = array_search($date, array_column($results, 'date'));

            if ($id !== false) {
                $results[$id][$this->status($status)] = $count;
            }
        }

        return json_decode(json_encode($results));
    }

    protected function status($status)
    {
        $statusNames =
            [
                0 => 'pending',
                1 => 'started',
                2 => 'completed',
                3 => 'canceled',
            ];

        return $statusNames[$status];
    }

    public function providers()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastDayOfMonth = $currentMonth->copy()->endOfMonth();
        $responses = [];
        while ($currentMonth <= $lastDayOfMonth)
        {
            $total_providers_availability = Avaliabilty::where('date', $currentMonth->toDateString())
                ->distinct('provider_id')
                ->count('provider_id');
            $total_providers_booking = BookingProvider::join('booking', 'booking.id', '=', 'booking_provider.booking_id')
                ->whereDate('booking.date', $currentMonth->toDateString())
                ->distinct('booking_provider.provider_id')
                ->count('booking_provider.provider_id');
            $working_providers = $total_providers_booking;
            $response = [
                'date' => $currentMonth->toDateString(),
                'total_providers' => $total_providers_availability + $total_providers_booking,
                'working_providers' => $working_providers,
            ];
            $responses[] = $response;
            $currentMonth->addDay();
        }
        return response()->json($responses);
    }
    public function rating_average()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastDayOfMonth = $currentMonth->copy()->endOfMonth();
        $responses = [];
        while ($currentMonth <= $lastDayOfMonth)
        {
            $bookings = Booking::whereDate('date', $currentMonth->toDateString())
                ->where('status', 2)
                ->get(['rating']);
            $averageRating = $bookings->count() > 0 ? $bookings->avg('rating') : 0;
            $response = [
                'date' => $currentMonth->toDateString(),
                'average_rating' => $averageRating
            ];
            $responses[] = $response;
            $currentMonth->addDay();
        }
        return response()->json($responses);
    }

    public function total_earning()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastDayOfMonth = $currentMonth->copy()->endOfMonth();
        $responses = [];

        while ($currentMonth <= $lastDayOfMonth) {
            $totalEarning = Booking::whereDate('date', $currentMonth->toDateString())
                ->where('status', 2)
                ->sum('total');

            $responses[] = [
                'date' => $currentMonth->toDateString(),
                'total_earning' => $totalEarning
            ];

            $currentMonth->addDay();
        }

        return response()->json($responses);
    }



}
