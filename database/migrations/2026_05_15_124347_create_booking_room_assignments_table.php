<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_room_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->restrictOnDelete();

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('released_at')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();

            $table->unique(['booking_id', 'room_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_room_assignments');
    }
};