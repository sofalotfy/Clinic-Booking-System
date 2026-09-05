<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add user_id as nullable first (no data yet)
        if (! Schema::hasColumn('whats_app_conversations', 'user_id')) {
            Schema::table('whats_app_conversations', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('patient_id')->constrained()->cascadeOnDelete();
            });
        }

        // 2. Backfill user_id from patient_id
        DB::statement('
            UPDATE whats_app_conversations wc
            JOIN patients p ON p.id = wc.patient_id
            SET wc.user_id = p.user_id
            WHERE wc.patient_id IS NOT NULL
        ');

        // 3. Drop the old patient_id FK/column
        if (Schema::hasColumn('whats_app_conversations', 'patient_id')) {
            $foreignKeys = collect(Schema::getForeignKeys('whats_app_conversations'))->pluck('name');

            if ($foreignKeys->contains('whats_app_conversations_patient_id_foreign')) {
                Schema::table('whats_app_conversations', function (Blueprint $table) {
                    $table->dropForeign(['patient_id']);
                });
            }

            Schema::table('whats_app_conversations', function (Blueprint $table) {
                $table->dropColumn('patient_id');
            });
        }

        // 4. Tighten user_id to NOT NULL now that it's backfilled
        Schema::table('whats_app_conversations', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('whats_app_conversations', function (Blueprint $table) {
            $foreignKeys = collect(Schema::getForeignKeys('whats_app_conversations'))->pluck('name');
            if ($foreignKeys->contains('whats_app_conversations_user_id_foreign')) {
                $table->dropForeign(['user_id']);
            }
            if (Schema::hasColumn('whats_app_conversations', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (! Schema::hasColumn('whats_app_conversations', 'patient_id')) {
                $table->foreignId('patient_id')->nullable()->constrained()->cascadeOnDelete();
            }
        });
    }
};