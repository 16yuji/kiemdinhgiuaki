<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();

            $table->string('room_number');
            $table->string('floor')->nullable();

            // available, occupied, cleaning, maintenance, locked
            $table->string('status')->default('available');

            $table->text('note')->nullable();

            $table->timestamps();

            $table->unique(['hotel_id', 'room_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};