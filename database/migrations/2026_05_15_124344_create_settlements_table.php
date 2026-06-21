<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('financial_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();

            $table->decimal('amount', 12, 2);

            // pending, settled, postponed
            $table->string('status')->default('pending');

            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();

            $table->string('transfer_code')->nullable();
            $table->text('note')->nullable();

            $table->timestamp('settled_at')->nullable();

            $table->timestamps();

            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};