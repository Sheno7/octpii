<?php

namespace App\Console\Commands;

use App\Models\Avaliabilty;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ClearAvailability extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-availability';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle() {
        Avaliabilty::whereDate('date', '<', Carbon::now())->delete();
    }
}
