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
        Schema::table('patient_blocks', function (Blueprint $table) {
            // Create a normal index first so foreign keys have an index to use
            $table->index(['doctor_id', 'patient_id']);

            // Now remove the unique constraint
            $table->dropUnique('patient_blocks_doctor_id_patient_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_blocks', function (Blueprint $table) {
            // Add the unique constraint back
            $table->unique(['doctor_id', 'patient_id']);

            // Drop the index we created
            $table->dropIndex(['doctor_id', 'patient_id']);
        });
    }
};
