<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('cities');
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

        DB::statement("
            CREATE FOREIGN TABLE IF NOT EXISTS cities (
                id BIGSERIAL,
                title_ar VARCHAR(255) NOT NULL,
                title_en VARCHAR(255) NOT NULL,
                country_id INTEGER NOT NULL,
                status SMALLINT DEFAULT 0, -- 1 => active, 0 => inactive
                created_at TIMESTAMP(0),
                updated_at TIMESTAMP(0),
                deleted_at TIMESTAMP(0)
            )
            SERVER remote_server
            OPTIONS (schema_name 'public', table_name 'cities');
        ");


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign table
        DB::statement('DROP FOREIGN TABLE IF EXISTS cities;');

        // Optionally drop the foreign server and user mapping
        DB::statement('DROP USER MAPPING IF EXISTS FOR CURRENT_USER SERVER remote_server;');
        DB::statement('DROP SERVER IF EXISTS remote_server;');
    }
};
