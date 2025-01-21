<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void {
        $this->call([
            RolesAndPermissionsSeeder::class,
            //ServiceCategorySeeder::class,
            //BranchSeeder::class,
            //CancellationReasonSeeder::class,
            CountriesSeeder::class,
        ]);
        if (env('APP_DEBUG', false)) {
            $this->call([MobileNotificationSeeder::class]);
        }
    }
}
