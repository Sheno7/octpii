<?php

use App\Models\ServiceCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('ve_services', function (Blueprint $table) {
            $table->foreignIdFor(ServiceCategory::class, 'category_id')->after('id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('ve_services', function (Blueprint $table) {
            $table->dropColumn('category_id');
        });
    }
};
