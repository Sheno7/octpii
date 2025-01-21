<?php

use App\Models\MaVendor;
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
        Schema::create('service_vendor', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(MaVendor::class, 'vendor_id');
            $table->integer('service_id')->notnull();
            $table->integer('ve_service_id')->notnull();
            $table->tinyInteger('status')->default(0)->comment('0 pending , 1 approve , -1 rejected');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_vendor');
    }
};
