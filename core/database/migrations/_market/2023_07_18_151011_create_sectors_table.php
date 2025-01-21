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
        Schema::create('sectors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('upid')->default(0);
            $table->string('title_ar')->notNullable();
            $table->string('title_en')->notNullable();
            $table->tinyInteger('status')->default(0); // 1 => active , 0 => inactive
            $table->tinyInteger('customer_rating')->default(0);
            $table->integer('multi_sessions')->default(0);
            $table->string('icon')->notNullable();
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
        Schema::dropIfExists('sectors');
    }
};
