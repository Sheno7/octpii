<?php

use App\Models\Branch;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('procurements', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Branch::class, 'branch_id');
            $table->foreignIdFor(Product::class, 'product_id');
            $table->foreignIdFor(Transaction::class, 'transaction_id');
            $table->double('price');
            $table->integer('quantity');
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('procurements');
    }
};
