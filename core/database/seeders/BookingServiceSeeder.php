<?php
namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingService;
use App\Models\VeServices;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class BookingServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookings = Booking::pluck('id')->toArray();
        $services = VeServices::pluck('id')->toArray();

        for ($i = 0; $i < 1000; $i++) {
            $booking_id = Arr::random($bookings);
            $service_id = Arr::random($services);

            // Use firstOrCreate to insert, avoiding duplicates.
            BookingService::firstOrCreate([
                'booking_id' => $booking_id,
                'service_id' => $service_id,
            ]);
        }
    }
}
