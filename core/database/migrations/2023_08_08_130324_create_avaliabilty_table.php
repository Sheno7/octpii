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
        Schema::create('avaliabilty', function (Blueprint $table) {
            $table->id();
            $table->integer('provider_id')->notNullable();
            $table->integer('area_id')->notNullable();
            $table->date('date')->notNullable();
            $table->string('from')->notNullable();
            $table->string('to')->notNullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avaliabilty');
    }
};
