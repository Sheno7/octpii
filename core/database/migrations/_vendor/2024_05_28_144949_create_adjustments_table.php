<?php

use App\Models\Branch;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Branch::class, 'branch_id');
            $table->foreignIdFor(Product::class, 'product_id');
            $table->integer('quantity');
            $table->date('date');
            $table->longText('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('adjustments');
    }
};
