<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('event_program_points', function (Blueprint $table) {
            $table->boolean('is_cost')->default(false)->after('active');
        });

        Schema::table('event_costs', function (Blueprint $table) {
            $table->foreignId('event_program_point_id')->nullable()->constrained('event_program_points')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_costs', function (Blueprint $table) {
            $table->dropForeign(['event_program_point_id']);
            $table->dropColumn('event_program_point_id');
        });

        Schema::table('event_program_points', function (Blueprint $table) {
            $table->dropColumn('is_cost');
        });
    }
};
