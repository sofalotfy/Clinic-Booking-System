<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ConversationState;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whats_app_conversations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('doctor_whatsapp_account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('patient_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('phone_number', 20)->index();

            $table->string('state')->default(ConversationState::START->value);

            $table->string('step')->nullable();

            $table->json('data')->nullable();

            $table->timestamp('last_activity_at')->nullable();

            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['doctor_whatsapp_account_id', 'phone_number'],
                'wa_conv_doc_phone_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_conversations');
    }
};
