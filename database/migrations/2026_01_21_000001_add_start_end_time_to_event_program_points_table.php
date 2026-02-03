<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('event_program_points')) {
            return;
        }

        Schema::table('event_program_points', function (Blueprint $table) {
            if (!Schema::hasColumn('event_program_points', 'start_time')) {
                $table->time('start_time')->nullable()->after('duration_minutes');
            }
            if (!Schema::hasColumn('event_program_points', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('event_program_points')) {
            return;
        }

        Schema::table('event_program_points', function (Blueprint $table) {
            if (Schema::hasColumn('event_program_points', 'end_time')) {
                $table->dropColumn('end_time');
            }
            if (Schema::hasColumn('event_program_points', 'start_time')) {
                $table->dropColumn('start_time');
            }
        });
    }
};
