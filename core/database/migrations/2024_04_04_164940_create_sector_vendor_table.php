<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('sector_vendor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sector_id');
            $table->unsignedBigInteger('vendor_id');
            $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->timestamps();
        });

        // Data transfer from vendors.sector_id to sector_vendor
        DB::table('vendors')->orderBy('id')->chunk(100, function ($vendors) {
            foreach ($vendors as $vendor) {
                DB::table('sector_vendor')->insert([
                    'sector_id' => $vendor->sector_id,
                    'vendor_id' => $vendor->id
                ]);
            }
        });

        // Remove the sector_id column from vendors
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('sector_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('vendors', function (Blueprint $table) {
            $table->unsignedBigInteger('sector_id')->after('id')->default(0);
        });

        // Retrieve the first sector for each vendor and restore it to the vendors table
        DB::table('sector_vendor')->select('vendor_id', DB::raw('MIN(created_at) as earliest_created_at'))
            ->groupBy('vendor_id')
            ->orderBy('earliest_created_at', 'asc')
            ->chunk(100, function ($relations) {
                foreach ($relations as $relation) {
                    // Fetch the actual sector_id for this earliest relation
                    $firstSector = DB::table('sector_vendor')
                        ->where('vendor_id', $relation->vendor_id)
                        ->orderBy('created_at', 'asc')
                        ->first();

                    // Update the vendors table with this sector_id
                    DB::table('vendors')
                        ->where('id', $relation->vendor_id)
                        ->update(['sector_id' => $firstSector->sector_id]);
                }
            });

        Schema::dropIfExists('sector_vendor');
    }
};
