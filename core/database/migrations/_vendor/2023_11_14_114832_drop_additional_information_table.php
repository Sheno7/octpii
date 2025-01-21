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
        Schema::dropIfExists('additional_information');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('additional_information', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->notNullable();
            $table->string('key');
            $table->string('value');
            $table->boolean('hasfile')->default(false);
            $table->timestamps();
        });
    }
};
