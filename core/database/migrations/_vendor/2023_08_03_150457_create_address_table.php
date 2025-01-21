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
        Schema::create('address', function (Blueprint $table) {
            $table->id();
            $table->integer('owner_id')->notNullable();
            $table->tinyInteger('owner_type')->default(1)->comment('1=customer,2=vendor,3=provider');
            $table->integer('area_id')->notNullable();
            $table->string('location_name')->nullable();
            $table->tinyInteger('unit_type')->default(1)->comment('1=villa,2=appartment');
            $table->string('unit_size')->notNullable();
            $table->string('street_name')->notNullable();
            $table->string('building_number')->notNullable();
            $table->string('floor_number')->nullable();
            $table->string('unit_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address');
    }
};
