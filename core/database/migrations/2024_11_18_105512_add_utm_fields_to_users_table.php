<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('utm_source')->nullable()->after('referral');
            $table->string('utm_medium')->nullable()->after('referral');
            $table->string('utm_campaign')->nullable()->after('referral');
            $table->string('utm_term')->nullable()->after('referral');
            $table->string('utm_content')->nullable()->after('referral');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('utm_source');
            $table->dropColumn('utm_medium');
            $table->dropColumn('utm_campaign');
            $table->dropColumn('utm_term');
            $table->dropColumn('utm_content');
        });
    }
};
