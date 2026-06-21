<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'checkin_note')) {
                $table->text('checkin_note')->nullable()->after('checked_in_at');
            }

            if (!Schema::hasColumn('bookings', 'checkout_note')) {
                $table->text('checkout_note')->nullable()->after('checked_out_at');
            }

            if (!Schema::hasColumn('bookings', 'manual_review_reason')) {
                $table->text('manual_review_reason')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            foreach (['checkin_note', 'checkout_note', 'manual_review_reason'] as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};