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
        Schema::create('pricing_model_data', function (Blueprint $table) {
            $table->id();
            $table->integer('pricing_models_id')->notNullable();
            $table->double('min')->notNullable();
            $table->double('max')->notNullable();
            $table->double('markup')->notNullable();
            $table->double('additional_cost')->notNullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_model_data');
    }
};
