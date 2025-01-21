<?php

namespace Database\Seeders;

use App\Http\Resources\AdditionalInfoResource;
use App\Models\Areas;
use App\Models\Cities;
use App\Models\Countries;
use App\Models\Markets;
use App\Models\PaymentMethod;
use App\Models\PricingModels;
use App\Models\Sectors;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vendors;
use App\Models\VeServices;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\ClientRepository;

class AdminMarketSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run($user_id = 0, $market_id = 0) {
        $lastUser = User::find($user_id);
        
        if ($lastUser) {
            $market = Markets::find($market_id);
            $countries = Countries::all();
            $cities = Cities::all();
            $areas = Areas::all();

            $tenant = Tenant::whereHas('domains', function ($q) use ($market_id) {
                $q->where('market_id', $market_id);
            })->first();

            $tenant->run(function () use (
                $lastUser,
                $countries,
                $cities,
                $areas,
                $market,
            ) {
                $clientRepository = new ClientRepository();
                $clientRepository->createPasswordGrantClient(null, 'Default password grant client', env('APP_URL'));
                $clientRepository->createPersonalAccessClient(null, 'Default personal access client', env('APP_URL'));

                User::updateOrCreate([
                    'phone' => $lastUser->phone,
                    'country_id' => $lastUser->country_id,
                ], [
                    'first_name' => $lastUser->first_name,
                    'last_name' => $lastUser->last_name,
                    'name' => $lastUser->first_name . ' ' . $lastUser->last_name,
                    'email' => $lastUser->email,
                    'dob' => $lastUser->dob ?? null,
                    'gender' => $lastUser->gender ?? null,
                    'image' => $lastUser->image ?? null,
                    'password' => $lastUser->password ?? Hash::make(rand(10000000, 99999999)),
                    'status' => 1,
                ]);
                
                $this->updateGeographicalData($countries, $cities, $areas);
                
                foreach ($market->sectors as $sector) {
                    $this->setupNewSector($sector);
                }

                $available_payment_methods = [
                    'Cash',
                    'Visa',
                    'InstaPay',
                ];
                foreach ($available_payment_methods as $payment_method) {
                    PaymentMethod::updateOrCreate([
                        'name' => $payment_method,
                    ], [
                        'icon' => 'icon',
                        'status' => 1,
                    ]);
                }

                $initial_settings = [
                    'wizard_status' => Setting::where('key', 'wizard_status')->value('value') ?? true,
                    'market_name' => $market->org_name_en ?? '',
                ];

                foreach ($initial_settings as $key => $value) {
                    Setting::updateOrCreate([
                        'key' => $key,
                    ], [
                        'value' => $value,
                    ]);
                }

                $this->call([
                    TenantSeeder::class,
                ]);
            });
        } else {
            return false;
        }
    }

    private function setupNewSector($sector) {
        $new_sector = Sectors::updateOrCreate([
            'upid' => $sector->id,
        ], $sector->toArray());
        $new_sector->upid = $sector->id;
        $new_sector->save();

        /* $pricingModels = $sector->pricingModelsSector;
        foreach ($pricingModels as $pricingModel) {
            PricingModels::updateOrCreate([
                'upid' => $pricingModel->id,
            ], [
                'upid' => $pricingModel->id,
                'name' => $pricingModel->name,
                'capacity' => $pricingModel->capacity,
                'variable_name' => $pricingModel->variable_name,
                'pricing_type' => $pricingModel->pricing_type,
                'capacity_threshold' => $pricingModel->capacity_threshold,
                'additional_cost' => $pricingModel->additional_cost,
                'markup' => $pricingModel->markup,
            ]);
        }

        $categories = $sector->categories;
        foreach ($categories as $category) {
            $new_category = ServiceCategory::updateOrCreate([
                'upid' => $category->id,
            ], [
                'sector_id' => $new_sector->id,
                'title_en' => $category->title_en,
                'title_ar' => $category->title_ar,
                'description_en' => $category->description_en,
                'description_ar' => $category->description_ar,
            ]);
            $new_category->upid = $category->id;
            $new_category->save();

            $services = $category->services;
            foreach ($services as $service) {
                $veService = VeServices::withTrashed()->updateOrCreate([
                    'upid' => $service->id,
                    'category_id' => $new_category->id,
                ], [
                    'sector_id' => $new_sector->id,
                    'upid' => $service->id,
                    'title_en' => $service->title_en,
                    'title_ar' => $service->title_ar,
                    'description_ar' => $service->description_ar,
                    'description_en' => $service->description_en,
                    'status' => $service->status,
                    'icon' => $service->icon ?? '',
                    'pricing_model_id' => $service->pricing_model_id ?? 0,
                ]);
                $veService->delete();
            }
        } */

        $sector_settings = [
            'additional_info' => AdditionalInfoResource::collection(
                $sector->additionalInformation->where('hasfiles', false)
            ),
            'customer_add' => AdditionalInfoResource::collection($sector->additionalInformation),
            'setting' => [
                'customer_rating' => $sector->customer_rating,
                'multi_sessions' => $sector->multi_sessions,
            ],
        ];

        foreach ($sector_settings as $key => $value) {
            Setting::updateOrCreate([
                'key' => $key,
            ], [
                'value' => $value,
            ]);
        }
    }

    private function updateGeographicalData($countries, $cities, $areas) {
        foreach ($countries as $country) {
            $new_country = Countries::updateOrCreate([
                'upid' => $country->id,
            ], [
                'upid' => $country->id,
                'title_en' => $country->title_en,
                'title_ar' => $country->title_ar,
                'code' => $country->code,
                'flag' => $country->flag,
                'currency' => $country->currency,
                'isocode' => $country->isocode,
                'status' => $country->status,
            ]);
            $new_country->upid = $country->id;
            $new_country->save();
        }

        foreach ($cities as $city) {
            $new_city = Cities::updateOrCreate([
                'upid' => $city->id,
            ], [
                'upid' => $city->id,
                'title_en' => $city->title_en,
                'title_ar' => $city->title_ar,
                'country_id' => $city->country_id,
                'status' => $city->status,
            ]);
            $new_city->upid = $city->id;
            $new_city->save();
        }

        foreach ($areas as $area) {
            $new_area = Areas::updateOrCreate([
                'upid' => $area->id,
            ], [
                'upid' => $area->id,
                'title_en' => $area->title_en,
                'title_ar' => $area->title_ar,
                'lat' => $area->lat,
                'long' => $area->long,
                'city_id' => $area->city_id,
                'status' => $area->status,
            ]);
            $new_area->upid = $area->id;
            $new_area->save();
        }
    }
}
