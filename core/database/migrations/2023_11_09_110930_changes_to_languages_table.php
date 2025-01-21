<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('languages', function (Blueprint $table) {
//            $table->integer('key')->change();
            DB::statement('ALTER TABLE languages ALTER COLUMN key TYPE INTEGER USING (key::integer)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
//            $table->string('key')->change();
            DB::statement('ALTER TABLE languages ALTER COLUMN key TYPE VARCHAR(255)');
        });
    }
};
