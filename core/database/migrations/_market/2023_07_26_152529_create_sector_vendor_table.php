<?php

use App\Models\MaVendor;
use App\Models\Sectors;
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
        Schema::create('sector_vendor', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(MaVendor::class, 'vendor_id');
            $table->foreignIdFor(Sectors::class, 'sector_id');
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
