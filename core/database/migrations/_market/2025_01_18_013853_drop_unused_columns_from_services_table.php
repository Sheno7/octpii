<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('duration');
            $table->dropColumn('pricing_model_id');
            $table->dropColumn('capacity');
            $table->dropColumn('capacity_threshold');
            $table->dropColumn('status');
            $table->dropColumn('icon');
            $table->dropColumn('cost');
            $table->dropColumn('markup');
            $table->dropColumn('unit_name');
            $table->dropColumn('base_price');
            $table->dropColumn('visible');
            $table->dropColumn('remote');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('services', function (Blueprint $table) {
            $table->string('duration')->nullable();
            $table->integer('pricing_model_id')->notNullable();
            $table->double('capacity')->nullable();
            $table->double('capacity_threshold')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->string('icon')->notNullable();
            $table->double('cost')->nullable();
            $table->double('markup')->nullable();
            $table->string('unit_name')->notNullable();
            $table->double('base_price')->nullable();
            $table->boolean('visible')->default(true);
            $table->boolean('remote')->default(false);
        });
    }
};
