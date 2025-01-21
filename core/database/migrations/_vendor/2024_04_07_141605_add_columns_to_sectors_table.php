<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('sectors', function (Blueprint $table) {
            $table->unsignedBigInteger('upid')->after('id')->default(0);
            $table->integer('multi_sessions')->default(0)->after('status');
            $table->tinyInteger('customer_rating')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('sectors', function (Blueprint $table) {
            $table->dropColumn('upid');
            $table->dropColumn('multi_sessions');
            $table->dropColumn('customer_rating');
        });
    }
};
