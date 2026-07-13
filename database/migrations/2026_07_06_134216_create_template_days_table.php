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
        Schema::create('template_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_plan_id')
            ->constrained()
            ->cascadeOnDelete();
            
            $table->tinyInteger('day_of_week'); 
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('appointment_duration');
            $table->integer('queue_length');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_days');
    }
};
