<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\BookingService;
use App\Models\VeServices;
use Illuminate\Console\Command;

class UpdateBookingStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-booking-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update booking Status based on time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $pending = Booking::where('status', '=', 0)->select('id', 'date')->get();

            foreach ($pending as $booking)
            {
                $bookingDateTime = now()->parse($booking->date);
                if ($bookingDateTime <= now())
                {
                    Booking::where('id', '=', $booking->id)->update(['status' => 1]);
                }
            }

            $stated = Booking::where('status', '=', 1)
                ->select('id', 'date')
               ->whereDate('date', '<=', now()->startOfDay())
                ->get();
            foreach ($stated as $value)
            {
                $time = date('H:i:s', strtotime($value->date));
                $booking_service = BookingService::where('booking_id', '=', $value->id)->select('service_id')->first();
                $duration = VeServices::where('id', '=', $booking_service->service_id)->select('duration')->first();
                $time = date('H:i:s', strtotime($time . '+' . $duration->duration . ' minutes'));
                $current_time = date('H:i:s', strtotime(now()));
                if ($value->date > now() && $time <= $current_time)
                {
                    Booking::where('id', '=', $value->id)->update(['status' => 2]);
                }
            }
        }catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
