<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();

            $table->string('booking_code')->unique();

            $table->date('checkin_date');
            $table->date('checkout_date');

            $table->unsignedInteger('guest_count')->default(1);
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->string('contact_email')->nullable();
            $table->text('special_request')->nullable();

            $table->decimal('total_amount', 12, 2)->default(0);

            // pending_payment, payment_expired, payment_failed,
            // confirmed, staying, completed, cancelled, no_show, manual_review
            $table->string('status')->default('pending_payment');

            $table->timestamp('hold_expires_at')->nullable();

            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();

            $table->text('cancel_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->text('no_show_reason')->nullable();
            $table->timestamp('no_show_at')->nullable();

            $table->timestamps();

            $table->index(['hotel_id', 'checkin_date', 'checkout_date']);
            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};