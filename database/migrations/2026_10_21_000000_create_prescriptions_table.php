<?php

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Lab;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Scan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A prescription is written for one appointment. doctor_id and patient_id
     * are copied from it so a patient's prescription history can be queried
     * directly. consultation_date is the optional follow-up visit.
     */
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Appointment::class)->unique()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Doctor::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Patient::class)->constrained()->cascadeOnDelete();
            $table->text('diagnosis')->nullable();
            $table->text('notes')->nullable();
            $table->date('consultation_date')->nullable()->index();
            $table->timestamps();

            $table->index(['doctor_id', 'patient_id']);
        });

        // Catalog rows are soft deleted, so these links survive catalog edits
        Schema::create('medicine_prescription', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Prescription::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Medicine::class)->constrained();
            $table->string('dose')->nullable();
            $table->string('frequency')->nullable();
            $table->string('duration')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();

            $table->unique(['prescription_id', 'medicine_id']);
        });

        Schema::create('lab_prescription', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Prescription::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Lab::class)->constrained();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['prescription_id', 'lab_id']);
        });

        Schema::create('prescription_scan', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Prescription::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Scan::class)->constrained();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['prescription_id', 'scan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_scan');
        Schema::dropIfExists('lab_prescription');
        Schema::dropIfExists('medicine_prescription');
        Schema::dropIfExists('prescriptions');
    }
};
