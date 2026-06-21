<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('ward')->nullable();
            $table->string('address');

            $table->time('checkin_time')->nullable();
            $table->time('checkout_time')->nullable();

            $table->string('thumbnail')->nullable();

            // active, hidden, locked
            $table->string('status')->default('active');

            $table->decimal('average_rating', 3, 2)->default(0);
            $table->unsignedInteger('review_count')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};