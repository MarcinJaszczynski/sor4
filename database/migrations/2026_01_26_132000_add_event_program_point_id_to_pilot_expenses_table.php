<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pilot_expenses', function (Blueprint $table) {
            $table->foreignId('event_program_point_id')
                ->nullable()
                ->after('event_id')
                ->constrained('event_program_points')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pilot_expenses', function (Blueprint $table) {
            $table->dropForeign(['event_program_point_id']);
            $table->dropColumn('event_program_point_id');
        });
    }
};
