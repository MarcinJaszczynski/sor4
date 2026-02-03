<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->index('start_date');
            $table->index('status');
            $table->index('event_template_id');
            $table->index('created_at');
            $table->index('assigned_to');
        });

        // add indexes on related tables used often in filters/search
        Schema::table('event_program_points', function (Blueprint $table) {
            $table->index('event_id');
            $table->index('day');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['start_date']);
            $table->dropIndex(['status']);
            $table->dropIndex(['event_template_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['assigned_to']);
        });

        Schema::table('event_program_points', function (Blueprint $table) {
            $table->dropIndex(['event_id']);
            $table->dropIndex(['day']);
        });
    }
};
