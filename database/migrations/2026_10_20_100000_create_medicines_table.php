<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Platform-wide medicine catalog, kept in sync with the uploaded drug
     * price sheet. match_key is the normalized trade name the sheet rows are
     * matched on. Rows are soft deleted so prescriptions keep their history.
     */
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('match_key')->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('active_ingredient')->nullable()->index();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedInteger('major_units')->nullable();
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->unsignedInteger('minor_units')->nullable();
            $table->string('category')->nullable()->index();
            $table->string('company')->nullable();
            $table->string('size')->nullable();
            $table->string('strength')->nullable();
            $table->string('barcode')->nullable()->index();
            $table->string('dosage_form')->nullable()->index();
            $table->string('origin')->nullable();
            $table->string('image_url', 2048)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('name_ar');
        });

        Schema::create('medicine_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_name');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('created_count')->default(0);
            $table->unsignedInteger('updated_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->unsignedInteger('duplicate_count')->default(0);
            $table->json('errors')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_imports');
        Schema::dropIfExists('medicines');
    }
};
