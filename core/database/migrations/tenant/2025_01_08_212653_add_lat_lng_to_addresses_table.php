<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (Schema::hasTable('address')) {
            Schema::table('address', function (Blueprint $table) {
                $table->string('lat')->nullable()->after('id');
                $table->string('lng')->nullable()->after('id');
            });
        }
        if (Schema::hasTable('addresses')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table->string('lat')->nullable()->after('id');
                $table->string('lng')->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        if (Schema::hasTable('address')) {
            Schema::table('address', function (Blueprint $table) {
                $table->dropColumn('lat');
                $table->dropColumn('lng');
            });
        }
        if (Schema::hasTable('addresses')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table->dropColumn('lat');
                $table->dropColumn('lng');
            });
        }
    }
};
