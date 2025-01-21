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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('created_by')->default(0);
            $table->integer('booking_id')->nullable()->default(0); // can be null
            $table->integer('payment_method_id')->notNullable()->default(0);
            $table->integer('status')->notNullable()->default(0);
            $table->integer('type')->notNullable()->default(0); // for booking payment , refund and for service provider collect , withdraw
            $table->datetime('date')->default(now())->nullable();
            $table->double('amount')->notNullable()->default(0); // retreived from booking tb after applied coupon if exist
            $table->integer('provider_id')->nullable()->default(0); // can be null
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction');
    }
};
