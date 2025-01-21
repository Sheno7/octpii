<?php

namespace Database\Seeders;

use App\Models\Countries;
use App\Models\Domains;
use App\Models\Tenant;
use App\Models\Vendors;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CountriesTenantSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run() {
        $countries = config('countries');

        $lastId = Countries::max('id');
        $id = $lastId + 1;
        foreach ($countries as $country) {
            $country['id'] = $id++;
            $existingCountry = Countries::where('isocode', $country['isocode'])->first();
            if (empty($existingCountry)) {
                Countries::Create($country);
            }
        }
    }
}
