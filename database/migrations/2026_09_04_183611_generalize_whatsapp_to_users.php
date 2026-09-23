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
        DB::statement("
            UPDATE whats_app_conversations
            SET user_id = (SELECT p.user_id FROM patients p WHERE p.id = whats_app_conversations.patient_id)
            WHERE whats_app_conversations.patient_id IS NOT NULL
        ");

        // 3. Drop the old patient_id FK/column
        if (Schema::hasColumn('whats_app_conversations', 'patient_id')) {
            $hasPatientForeignKey = collect(Schema::getForeignKeys('whats_app_conversations'))
                ->contains(fn ($foreignKey) => in_array('patient_id', $foreignKey['columns'], true));

            if ($hasPatientForeignKey) {
                Schema::table('whats_app_conversations', function (Blueprint $table) {
                    $table->dropForeign(['patient_id']);
                });
            }

            Schema::table('whats_app_conversations', function (Blueprint $table) {
                $table->dropColumn('patient_id');
            });
        }

        // 4. Tighten user_id to NOT NULL now that it's backfilled
        if (Schema::hasColumn('whats_app_conversations', 'user_id')) {
            Schema::table('whats_app_conversations', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('whats_app_conversations', 'user_id')) {
            $hasUserForeignKey = collect(Schema::getForeignKeys('whats_app_conversations'))
                ->contains(fn ($foreignKey) => in_array('user_id', $foreignKey['columns'], true));

            if ($hasUserForeignKey) {
                Schema::table('whats_app_conversations', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                });
            }

            Schema::table('whats_app_conversations', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }

        if (! Schema::hasColumn('whats_app_conversations', 'patient_id')) {
            Schema::table('whats_app_conversations', function (Blueprint $table) {
                $table->foreignId('patient_id')->nullable()->constrained()->cascadeOnDelete();
            });
        }
    }
};