<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // chat, review_summary, room_recommendation
            $table->string('type');

            $table->text('prompt')->nullable();
            $table->longText('response')->nullable();

            $table->json('context_data')->nullable();

            // success, failed
            $table->string('status')->default('success');

            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_logs');
    }
};