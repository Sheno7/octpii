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
        Schema::table('off_days', function (Blueprint $table) {
            //make provider id nullable
            $table->unsignedBigInteger('provider_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('off_days', function (Blueprint $table) {
            // change provider_id to not nullable
            $table->unsignedBigInteger('provider_id')->nullable(false)->change();
        });
    }
};
