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
        Schema::create('vendor_providers', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id')->notnull();
            $table->integer('provider_id')->notnull();
            $table->tinyInteger('status')->default(0)->comment('0=pending,1=active,2=blocked');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_providers');
    }
};
