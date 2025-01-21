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
        Schema::table('pricing_model_data', function (Blueprint $table) {
            $table->renameColumn('markup', 'price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_model_data', function (Blueprint $table) {
            //change markup to price
            $table->renameColumn('price', 'markup');
        });
    }
};
