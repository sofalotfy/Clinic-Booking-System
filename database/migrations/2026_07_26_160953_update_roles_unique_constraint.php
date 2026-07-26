<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Drop the existing unique index
            $table->dropUnique(['name', 'guard_name']);

            // Add the new one
            $table->unique(
                ['doctor_id', 'name', 'guard_name'],
                'roles_doctor_name_guard_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique('roles_doctor_name_guard_unique');

            $table->unique(['name', 'guard_name']);
        });
    }
};