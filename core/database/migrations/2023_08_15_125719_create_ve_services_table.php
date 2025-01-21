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
        Schema::create('ve_services', function (Blueprint $table) {
            $table->id();
            $table->string('title_ar');
            $table->string('title_en');
            $table->string('description_ar')->nullable();
            $table->string('description_en')->nullable();
            $table->string('duration')->nullable();
            $table->integer('pricing_model_id')->notNullable();
            $table->double('capacity')->nullable();
            $table->double('capacity_threshold')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->string('icon')->notNullable();
            $table->double('cost')->nullable();
            $table->double('markup')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ve_services');
    }
};
