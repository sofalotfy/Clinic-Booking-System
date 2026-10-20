<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Platform-wide catalog of imaging scans that doctors can prescribe. Rows
     * are soft deleted so prescriptions that reference them keep their history.
     */
    public function up(): void
    {
        Schema::create('scans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('name_ar')->nullable();
            $table->string('code')->nullable()->index();
            $table->string('modality')->nullable()->index();
            $table->string('body_part')->nullable();
            $table->boolean('with_contrast')->default(false);
            $table->text('preparation')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scans');
    }
};
