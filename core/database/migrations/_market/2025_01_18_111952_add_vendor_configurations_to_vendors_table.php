<?php

use App\Enums\CommissionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('vendors', function (Blueprint $table) {
            $table->tinyInteger('commission_type')->default(CommissionType::FIXED);
            $table->double('commission_amount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('commission_type');
            $table->dropColumn('commission_amount');
        });
    }
};
