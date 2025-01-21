<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('name_ar')->default('')->after('name');
            $table->renameColumn('name', 'name_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('name_ar');
            $table->renameColumn('name_en', 'name');
        });
    }
};
