<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Platform-wide catalog of lab tests that doctors can prescribe. Rows are
     * soft deleted so prescriptions that reference them keep their history.
     */
    public function up(): void
    {
        Schema::create('labs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('name_ar')->nullable();
            $table->string('code')->nullable()->index();
            $table->string('category')->nullable()->index();
            $table->string('sample_type')->nullable();
            $table->boolean('fasting_required')->default(false);
            $table->text('preparation')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labs');
    }
};
