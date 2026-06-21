<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'refund_amount')) {
                $table->decimal('refund_amount', 12, 2)->default(0)->after('amount');
            }

            if (!Schema::hasColumn('payments', 'refund_reason')) {
                $table->text('refund_reason')->nullable()->after('refund_amount');
            }

            if (!Schema::hasColumn('payments', 'refund_note')) {
                $table->text('refund_note')->nullable()->after('refund_reason');
            }

            if (!Schema::hasColumn('payments', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('refund_note');
            }

            if (!Schema::hasColumn('payments', 'refunded_by')) {
                $table->foreignId('refunded_by')
                    ->nullable()
                    ->after('refunded_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'refunded_by')) {
                $table->dropForeign(['refunded_by']);
            }

            $table->dropColumn([
                'refund_amount',
                'refund_reason',
                'refund_note',
                'refunded_at',
                'refunded_by',
            ]);
        });
    }
};