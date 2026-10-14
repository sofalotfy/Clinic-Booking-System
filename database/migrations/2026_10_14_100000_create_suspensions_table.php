<?php

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each row is one suspension period of a doctor. A suspension is active
     * while lifted_at is null; lifting it keeps the row as a log entry.
     */
    public function up(): void
    {
        Schema::create('suspensions', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Doctor::class)->constrained()->cascadeOnDelete();

            $table->foreignIdFor(User::class, 'suspended_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignIdFor(User::class, 'lifted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('reason')->nullable();

            $table->timestamp('suspended_at')->useCurrent();
            $table->timestamp('lifted_at')->nullable();

            $table->timestamps();

            $table->index(['doctor_id', 'lifted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suspensions');
    }
};
