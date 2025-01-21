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
        Schema::table('avaliabilty', function (Blueprint $table) {
            //make area_id nullable
            $table->unsignedBigInteger('area_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('avaliabilty', function (Blueprint $table) {
            //make area_id not nullable
            $table->unsignedBigInteger('area_id')->nullable(false)->change();
        });
    }
};
