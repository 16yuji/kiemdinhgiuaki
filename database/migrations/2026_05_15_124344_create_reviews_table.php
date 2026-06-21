<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();

            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();

            // visible, hidden
            $table->string('status')->default('visible');

            $table->foreignId('hidden_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('hidden_reason')->nullable();
            $table->timestamp('hidden_at')->nullable();

            $table->timestamps();

            $table->unique('booking_id');
            $table->index(['hotel_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};