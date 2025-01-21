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
        Schema::table('working_schedule', function (Blueprint $table) {
            $table->unsignedBigInteger('provider_id')->nullable()->change();
        });
        Schema::table('working_schedule', function (Blueprint $table) {
                $table->dropColumn('day');
            });
        Schema::table('working_schedule', function (Blueprint $table) {
            $table->integer('day')->after('id')->nullable();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('working_schedule', function (Blueprint $table) {
            // change provider_id to not nullable
            $table->unsignedBigInteger('provider_id')->nullable(false)->change();
        });
        Schema::table('working_schedule', function (Blueprint $table) {
            $table->dropColumn('day');
        });
    }

};
