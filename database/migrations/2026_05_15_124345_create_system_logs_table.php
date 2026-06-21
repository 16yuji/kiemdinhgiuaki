<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('action');
            $table->string('module')->nullable();

            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();

            $table->text('description')->nullable();
            $table->json('data')->nullable();

            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'action']);
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};