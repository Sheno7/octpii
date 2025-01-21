<?php

use App\Models\Branch;
use App\Models\ExpenseCategory;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ExpenseCategory::class, 'category_id');
            $table->double('amount');
            $table->date('date')->default(now());
            $table->foreignIdFor(User::class, 'created_by');
            $table->foreignIdFor(Media::class, 'attachment')->nullable();
            $table->text('notes')->nullable();
            $table->foreignIdFor(Branch::class, 'branch_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('expenses');
    }
};
