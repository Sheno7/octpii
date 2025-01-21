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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->integer('upid')->nullable()->default(0);
            $table->string('title_ar')->notNullable();
            $table->string('title_en')->notNullable();
            $table->integer('country_id')->notNullable();
            $table->tinyInteger('status')->default(0); // 1 => active , 0 => inactive
            $table->timestamps();
            $table->softDeletes();
            $table->fullText(['title_ar', 'title_en']); // add support for full text search from package
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
