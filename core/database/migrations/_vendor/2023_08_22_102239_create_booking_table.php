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
        Schema::create('booking', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->notNullable();
            $table->date('date')->notNullable();
            $table->tinyInteger('gender_prefrence')->default(0);
            $table->tinyInteger('is_favorite')->default(0);
            $table->integer('address_id')->notNullable();
            $table->integer('area_id')->notNullable();
            $table->integer('coupon_id')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('source')->default(0);
            $table->double('total')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking');
    }
};
