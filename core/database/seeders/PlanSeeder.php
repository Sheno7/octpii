<?php

namespace Database\Seeders;

use App\Models\Vendors;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use LucasDotVin\Soulbscription\Models\Feature;
use LucasDotVin\Soulbscription\Models\Plan;

class PlanSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $ultimate = Plan::firstOrCreate([
            'name'             => 'ultimate',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity'      => 1,
            'grace_days'       => 7,
        ]);

        $pro = Plan::firstOrCreate([
            'name'             => 'pro',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity'      => 1,
            'grace_days'       => 7,
        ]);

        $basic = Plan::firstOrCreate([
            'name'             => 'basic',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity'      => 1,
            'grace_days'       => 7,
        ]);

        $free = Plan::firstOrCreate([
            'name'             => 'free',
            'periodicity_type' => null,
            'periodicity'      => null,
        ]);

        $admins = Feature::firstOrCreate([
            'consumable' => true,
            'quota'      => true,
            'name'       => 'admins',
        ]);
        $providers = Feature::firstOrCreate([
            'consumable' => true,
            'quota' => true,
            'name'       => 'expenses',
        ]);
        $expenses = Feature::firstOrCreate([
            'consumable' => false,
            'name'       => 'expenses',
        ]);
        $inventory = Feature::firstOrCreate([
            'consumable' => false,
            'name'       => 'inventory',
        ]);
        $customDomain = Feature::firstOrCreate([
            'consumable' => false,
            'name'       => 'custom-domain',
        ]);

        $free->features()->detach($admins);
        $free->features()->attach($admins, ['charges' => 1]);

        $basic->features()->detach($admins);
        $basic->features()->attach($admins, ['charges' => 3]);
        $basic->features()->detach($providers);
        $basic->features()->attach($providers, ['charges' => 10]);

        $pro->features()->detach($admins);
        $pro->features()->attach($admins, ['charges' => 10]);
        $pro->features()->detach($providers);
        $pro->features()->attach($providers, ['charges' => 15]);
        $pro->features()->detach($expenses);
        $pro->features()->attach($expenses);

        $ultimate->features()->detach($admins);
        $ultimate->features()->attach($admins, ['charges' => 15]);
        $pro->features()->detach($providers);
        $pro->features()->attach($providers, ['charges' => 25]);
        $ultimate->features()->detach($expenses);
        $ultimate->features()->attach($expenses);
        $ultimate->features()->detach($inventory);
        $ultimate->features()->attach($inventory);
        $ultimate->features()->detach($customDomain);
        $ultimate->features()->attach($customDomain);

        $vendors = Vendors::all();
        foreach ($vendors as $vendor) {
            if (empty($vendor->subscription)) {
                $vendor->subscribeTo($free);
            }
        }
    }
}
