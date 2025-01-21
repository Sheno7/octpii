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
        Schema::create('providers_action', function (Blueprint $table) {
            $table->id();
            $table->integer('transaction_id')->notNullable();
            $table->integer('provider_id')->notNullable();
            $table->integer('action')->notNullable();// deduct , bounse , collect , payout
            $table->double('amount')->notNullable();
            $table->string('attachment')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers_action');
    }
};
