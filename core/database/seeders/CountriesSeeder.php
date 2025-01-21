<?php

namespace Database\Seeders;

use App\Models\Countries;
use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $countries = config('countries');

        foreach ($countries as $country) {
            $existingCountry = Countries::where('isocode', $country['isocode'])->first();
            if (empty($existingCountry)) {
                Countries::Create($country);
            }
        }
    }
}
