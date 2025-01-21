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
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgres_fdw;');

        // Create a foreign server connection
        DB::statement("
            CREATE SERVER IF NOT EXISTS remote_server
            FOREIGN DATA WRAPPER postgres_fdw
            OPTIONS (host '".env('DB_HOST')."', dbname '".env('DB_DATABASE')."', port '".env('DB_PORT')."');
        ");

        // Map the local user to the remote server user
        DB::statement("
            CREATE USER MAPPING IF NOT EXISTS FOR CURRENT_USER
            SERVER remote_server
            OPTIONS (user '".env('DB_USERNAME')."', password '".env('DB_PASSWORD')."');
        ");

        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('title_ar')->notNullable();
            $table->string('title_en')->notNullable();
            $table->double('lat')->notNullable();
            $table->double('long')->notNullable();
            $table->integer('city_id')->notNullable();
            $table->tinyInteger('status')->default(0); // 1 => active , 0 => inactive
            $table->timestamps();
            $table->softDeletes();
            $table->fullText(['title_ar', 'title_en']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
