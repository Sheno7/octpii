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
        Schema::create('additional_information_metadata', function (Blueprint $table) {
            $table->id();
            $table->integer('additional_info_id')->notNullable();
            $table->integer('customer_id')->notNullable();
            $table->string('key')->notNullable();
            $table->json('value')->notNullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('additional_information_metadata');
    }
};
