<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_room_types', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained('room_types')->restrictOnDelete();

            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price_per_night', 12, 2);
            $table->unsignedInteger('nights')->default(1);
            $table->decimal('subtotal', 12, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_room_types');
    }
};