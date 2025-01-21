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
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->notnull();
            $table->integer('address_id')->notnull();
            $table->string('rank')->nullable();
            $table->string('rating')->nullable();
            $table->date('start_date')->nullable();
            $table->date('resgin_date')->nullable();
            $table->string('salary')->nullable();
            $table->tinyInteger('comission_type')->default(1)->comment('1=fixed,2=percentage');
            $table->integer('comission_amount')->nullable();
            $table->double('balance')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=inactive,1=active,2=blocked');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
