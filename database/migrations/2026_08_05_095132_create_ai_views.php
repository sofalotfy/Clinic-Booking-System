<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
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

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS ai_users');
    }
};