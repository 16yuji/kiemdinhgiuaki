<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            if (!Schema::hasColumn('hotels', 'cancellation_policy')) {
                $table->text('cancellation_policy')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            if (Schema::hasColumn('hotels', 'cancellation_policy')) {
                $table->dropColumn('cancellation_policy');
            }
        });
    }
};