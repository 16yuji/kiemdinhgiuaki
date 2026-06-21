<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->unsignedInteger('max_guests')->default(1);
            $table->string('bed_type')->nullable();
            $table->decimal('area', 8, 2)->nullable();

            $table->decimal('price_per_night', 12, 2);

            $table->string('thumbnail')->nullable();

            // active, hidden
            $table->string('status')->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};