<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Spatie ships permissions with a (name, guard_name) unique index, so a
     * permission name can only exist once. The same name has to be declarable
     * twice though: once for the platform admins and once for the clinic-scoped
     * assistants and users. `type` was added later but never became part of
     * the key, which is why the admin copy of an existing clinic permission
     * was silently swallowed by firstOrCreate.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropUnique('permissions_name_guard_name_unique');
            $table->unique(['name', 'guard_name', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropUnique(['name', 'guard_name', 'type']);
            $table->unique(['name', 'guard_name']);
        });
    }
};
