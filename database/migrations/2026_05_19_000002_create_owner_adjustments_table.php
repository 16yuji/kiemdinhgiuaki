<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_adjustments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('financial_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('applied_settlement_id')->nullable()->constrained('settlements')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // refund_clawback, manual_adjustment
            $table->string('type')->default('refund_clawback');

            // amount: số tiền gốc cần truy thu Owner.
            // remaining_amount: số tiền còn lại chưa trừ vào các kỳ đối soát sau.
            $table->decimal('amount', 12, 2);
            $table->decimal('remaining_amount', 12, 2);

            // pending_deduction, deducted, cancelled
            $table->string('status')->default('pending_deduction');
            $table->text('reason')->nullable();

            $table->timestamp('deducted_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['owner_id', 'status']);
            $table->index(['booking_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_adjustments');
    }
};
