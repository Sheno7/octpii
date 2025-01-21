<?php

namespace App\Console\Commands;

use Database\Seeders\AdminMarketSeeder;
use Illuminate\Console\Command;

class SeedTenantMarket extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-tenant-market {user_id} {market_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(AdminMarketSeeder $seeder) {
        $seeder->run(intval($this->argument('user_id')), intval($this->argument('market_id')));
    }
}
