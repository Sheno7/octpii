<?php

use App\Models\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignIdFor(Transaction::class, 'transaction_id')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
        });
    }
};
