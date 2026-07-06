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
        Schema::create('appointments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('doctor_id')
          ->constrained('doctors')
          ->cascadeOnDelete();
    $table->foreignId('patient_id')
          ->constrained('patients')
          ->cascadeOnDelete();
    // $table->enum('status', [
    //     'pending',
    //     'confirmed',
    //     'completed',
    //     'cancelled'
    // ]);
    $table->dateTime('date');
    $table->integer('delay')->default(0);
    $table->integer('duration');
    // $table->enum('grade', [
    //     'excellent',
    //     'good',
    //     'average',
    //     'poor'
    // ])->nullable();
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
