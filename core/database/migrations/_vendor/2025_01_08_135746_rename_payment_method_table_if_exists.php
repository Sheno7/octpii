<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (Schema::hasTable('payment_method')) {
            Schema::rename('payment_method', 'payment_methods');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        if (Schema::hasTable('payment_methods')) {
            Schema::rename('payment_methods', 'payment_method');
        }
    }
};
