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
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('title_ar')->notNullable();
            $table->string('title_en')->notNullable();
            $table->double('lat')->notNullable();
            $table->double('long')->notNullable();
            $table->integer('city_id')->notNullable();
            $table->tinyInteger('status')->default(0); // 1 => active , 0 => inactive
            $table->timestamps();
            $table->softDeletes();
            $table->fullText(['title_ar', 'title_en']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
