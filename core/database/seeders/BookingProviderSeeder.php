<?php
namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingProvider;
use App\Models\Providers;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class BookingProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookings = Booking::pluck('id')->toArray();
        $providers = Providers::pluck('id')->toArray();

        for ($i = 0; $i < 500; $i++) {
            // Get a random booking and provider.
            $booking_id = Arr::random($bookings);
            $provider_id = Arr::random($providers);

            // Check if there are already two entries with the same booking_id.
            $existingEntries = BookingProvider::where('booking_id', $booking_id)->count();
            if ($existingEntries < 2) {
                BookingProvider::create([
                    'booking_id' => $booking_id,
                    'provider_id' => $provider_id,
                ]);
            }
        }
    }
}
