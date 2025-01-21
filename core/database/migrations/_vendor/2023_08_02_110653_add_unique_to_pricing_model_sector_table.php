<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pricing_model_sector', function (Blueprint $table) {
            $table->unique(['pricing_model_id', 'sector_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_model_sector', function (Blueprint $table) {
            $table->dropUnique(['pricing_model_id', 'sector_id']);
        });
    }
};
