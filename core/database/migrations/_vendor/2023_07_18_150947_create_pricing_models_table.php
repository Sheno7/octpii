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
        Schema::create('pricing_models', function (Blueprint $table) {
            $table->id();
            $table->string('name')->notNullable();
            $table->boolean('capacity')->default(false)->notNullable();
            $table->string('variable_name')->notNullable();
            $table->enum('pricing_type', ['fixed', 'variable'])->default('fixed');
            $table->boolean('capacity_threshold')->default(false)->notNullable();
            $table->boolean('additional_cost')->default(false)->notNullable();
            $table->boolean('markup')->default(false)->notNullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_models');
    }
};
