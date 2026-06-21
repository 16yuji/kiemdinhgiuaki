<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();

            $table->decimal('gross_amount', 12, 2);
            $table->decimal('platform_fee', 12, 2)->default(0);
            $table->decimal('owner_amount', 12, 2)->default(0);

            // temporary_recorded, waiting_settlement, settled, postponed, adjusted
            $table->string('status')->default('temporary_recorded');

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};