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
        Schema::table('providers_action', function (Blueprint $table)
        {
            // this column is added to the providers_action table so we can deny dupliction of the same booking_id in another provider exist in same booking table
            $table->integer('booking_id')->nullable()->after('provider_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('providers_action', function (Blueprint $table) {
            $table->dropColumn('booking_id');
        });
    }
};
