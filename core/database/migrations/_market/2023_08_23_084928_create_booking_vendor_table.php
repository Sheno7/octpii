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
        Schema::create('booking_vendor', function (Blueprint $table) {
            $table->id();
            $table->integer('booking_id')->notNullable();
            $table->integer('vendor_id')->notNullable();
            $table->tinyInteger('commission_type')->default(0);
            $table->integer('commission_amount')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_provider');
    }
};
