<?php

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_blocks', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Doctor::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Patient::class)->constrained()->cascadeOnDelete();

            // User who created the block (doctor, assistant, admin...)
            $table->foreignIdFor(User::class, 'blocked_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            
            $table->foreignIdFor(User::class, 'unblocked_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('reason')->nullable();

            $table->timestamp('blocked_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('unblocked_at')->nullable();

            $table->timestamps();

            // A patient should only have one active block per doctor.
            $table->unique(['doctor_id', 'patient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_blocks');
    }
};