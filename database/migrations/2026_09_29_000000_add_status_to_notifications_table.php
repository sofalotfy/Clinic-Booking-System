<?php

use App\Enums\NotificationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('status')
                ->default(NotificationStatus::PENDING->value)
                ->after('viewed');

            $table->text('failure_reason')->nullable()->after('status');
        });

        // Rows that predate this column were all created synchronously by the
        // in-app push, so they were delivered rather than left pending. The
        // column default would otherwise mark the entire history as unsent.
        DB::table('notifications')
            ->where('status', NotificationStatus::PENDING->value)
            ->update(['status' => NotificationStatus::SENT->value]);
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['status', 'failure_reason']);
        });
    }
};
