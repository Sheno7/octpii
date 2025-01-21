<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('providers', function (Blueprint $table) {
            $table->renameColumn('resgin_date', 'resign_date');
            $table->renameColumn('comission_type', 'commission_type');
            $table->renameColumn('comission_amount', 'commission_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('providers', function (Blueprint $table) {
            $table->renameColumn('resign_date', 'resgin_date');
            $table->renameColumn('commission_type', 'comission_type');
            $table->renameColumn('commission_amount', 'comission_amount');
        });
    }
};
