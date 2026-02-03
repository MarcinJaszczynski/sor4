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
            if (!Schema::hasColumn('event_program_points', 'assigned_contractor_id')) {
                $table->foreignId('assigned_contractor_id')
                    ->nullable()
                    ->constrained('contractors')
                    ->nullOnDelete()
                    ->after('active');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('event_program_points')) {
            return;
        }

        Schema::table('event_program_points', function (Blueprint $table) {
            if (Schema::hasColumn('event_program_points', 'assigned_contractor_id')) {
                $table->dropForeign(['assigned_contractor_id']);
                $table->dropColumn('assigned_contractor_id');
            }
        });
    }
};
