<?php

use App\Models\Branch;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('booking', function (Blueprint $table) {
            $table->foreignIdFor(Branch::class, 'branch_id')->after('id')->nullable();
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignIdFor(Branch::class, 'branch_id')->after('id')->nullable();
        });
        Schema::table('providers', function (Blueprint $table) {
            $table->foreignIdFor(Branch::class, 'branch_id')->after('id')->nullable();
        });
        Schema::table('working_schedule', function (Blueprint $table) {
            $table->foreignIdFor(Branch::class, 'branch_id')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('booking', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });
        Schema::table('working_schedule', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });
    }
};
