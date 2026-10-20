<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stamped when each consultation reminder goes out, so a re-run of the
     * daily job never reminds twice. Cleared when the consultation date moves.
     */
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->timestamp('week_reminder_sent_at')->nullable()->after('consultation_date');
            $table->timestamp('day_reminder_sent_at')->nullable()->after('week_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn(['week_reminder_sent_at', 'day_reminder_sent_at']);
        });
    }
};
