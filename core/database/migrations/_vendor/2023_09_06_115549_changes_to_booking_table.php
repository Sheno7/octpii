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
        Schema::table('booking', function (Blueprint $table) {
            $table->integer('created_by')->nullable()->after('id');
            $table->longText('notes')->nullable()->after('total');
            $table->longText('feedback')->nullable()->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('notes');
            $table->dropColumn('feedback');
        });
    }
};
