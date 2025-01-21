<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // List of tables to drop
        $tables = [
            'action_reason',
            'address',
            'area_providers',
            'area_service',
            'avaliabilty',
            'block',
            'booking',
            'booking_provider',
            'booking_service',
            'customer_favorite',
            'customers',
            'off_days',
            'providers',
            'providers_action',
            'service_provider',
            'setting',
            'transaction',
            've_services',
            'working_schedule',
        ];

        // Drop tables
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //
    }
};
