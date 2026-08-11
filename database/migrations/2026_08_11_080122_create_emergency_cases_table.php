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
        Schema::create('emergency_cases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('doctor_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            
            $table->text('context');
            $table->text('symptoms');
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();
            $table->text('place')->nullable();
            $table->text('address')->nullable();
            $table->boolean('in_hospital')->default(false);
            $table->string('hospital_name')->nullable();

            $table->timestamps();
            
            $table->unique(['doctor_id', 'patient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_cases');
    }
};
