<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_review_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('summary');
            $table->json('pros')->nullable();
            $table->json('cons')->nullable();
            $table->unsignedInteger('review_count')->default(0);
            $table->string('reviews_hash', 64)->nullable();
            $table->string('generated_by')->default('fallback');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_review_summaries');
    }
};
