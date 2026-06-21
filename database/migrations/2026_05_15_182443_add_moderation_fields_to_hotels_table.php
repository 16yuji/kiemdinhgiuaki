<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            if (!Schema::hasColumn('hotels', 'status_reason')) {
                $table->text('status_reason')->nullable()->after('status');
            }

            if (!Schema::hasColumn('hotels', 'status_changed_at')) {
                $table->timestamp('status_changed_at')->nullable()->after('status_reason');
            }

            if (!Schema::hasColumn('hotels', 'status_changed_by')) {
                $table->foreignId('status_changed_by')
                    ->nullable()
                    ->after('status_changed_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            if (Schema::hasColumn('hotels', 'status_changed_by')) {
                $table->dropForeign(['status_changed_by']);
            }

            $table->dropColumn([
                'status_reason',
                'status_changed_at',
                'status_changed_by',
            ]);
        });
    }
};