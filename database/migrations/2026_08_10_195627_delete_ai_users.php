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
        DB::statement('DROP VIEW IF EXISTS ai_users');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            CREATE VIEW ai_users AS
            SELECT
                u.id,
                u.name,
                u.email,
                u.phone,
                u.age,
                u.gender,
                u.area,
                u.type
            FROM users u
        ");
    }
};
