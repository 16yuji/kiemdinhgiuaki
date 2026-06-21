<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();

            // fake, vnpay
            $table->string('method')->default('fake');

            // pending, paid, failed, refunding, refunded, non_refundable
            $table->string('status')->default('pending');

            $table->decimal('amount', 12, 2);

            $table->string('transaction_code')->nullable();
            $table->string('gateway_response_code')->nullable();
            $table->json('gateway_payload')->nullable();

            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->timestamps();

            $table->index(['booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};